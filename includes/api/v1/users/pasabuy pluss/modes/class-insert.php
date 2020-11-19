<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
	 * @package coinpress-wp-plugin
     * @version 0.1.0
     * Here is where you add hook to WP to create our custom database if not found.
	*/
	class CP_Pasabuy_Pluss_Modes_Insert {

        public static function listen(){
            return rest_ensure_response(
                self::listen_open()
            );
        }

        public static function catch_post(){
            $curl_user = array();
            $curl_user['title'] = $_POST['title'];
            $curl_user['action'] = $_POST['action'];
            $curl_user['limit'] = $_POST['limit'];
            $curl_user['amount'] = serialize(array( 'amount' => $_POST['amount']));
            $curl_user['wpid'] = $_POST['wpid'];
            return $curl_user;
        }

        public static function listen_open(){

            global $wpdb;
            $tbl_pasabuy_pluss_mode = CP_PLS_MODES;
            $tbl_pasabuy_pluss_mode_fields = CP_PLS_MODES_FIELDS;

            // Step 1: Check if prerequisites plugin are missing
            $plugin = CP_Globals::verify_prerequisites();
            if ($plugin !== true) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. ".$plugin." plugin missing!",
                );
            }

            // Step 2: Validate user
            if (DV_Verification::is_verified() == false) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Verification Issues!",
                );
            }

            if ( !isset($_POST['title']) || !isset($_POST['amount']) || !isset($_POST['action']) || !isset($_POST['limit']) ) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknown"
                );
            }

            $user = self::catch_post();

            $validate = TP_Globals_v2::check_listener($user);
            if ($validate !== true) {
                return array(
                    "status" => "failed",
                    "message" => "Required fileds cannot be empty "."'".ucfirst($validate)."'"."."
                );
            }

            isset($_POST['info']) && !empty($_POST['info'])? $user['info'] =  $_POST['info'] :  $user['info'] = null ;

            if ($user["action"] != "free_ship" && $user["action"] != "discount" && $user["action"] != "min_spend"  && $user["action"] != "less" )  {
                return array(
                    "status" => "failed",
                    "message" => "Invalid value of action."
                );
            }

            // get data
                $check_modes = $wpdb->get_row("SELECT MAX(`trigger`) as `trigger` FROM $tbl_pasabuy_pluss_mode  ");
                if (!empty($check_modes)) {
                    $trigger = $check_modes->trigger + 1;
                }else{
                    $trigger = 1;
                }
            // End

            $wpdb->query("START TRANSACTION");
            // Verify if mode is existed
                $check_mode = $wpdb->get_row("SELECT title FROM $tbl_pasabuy_pluss_mode WHERE title LIKE '%{$user["title"]}%' AND `status` = 'active' AND id IN ( SELECT MAX( id ) FROM $tbl_pasabuy_pluss_mode ct WHERE ct.hash_id = hash_id  GROUP BY hash_id )  ");
                if(!empty($check_mode)){
                    return array(
                        "status" => "failed",
                        "message" => "This Pasabuy Pluss mode is already exists. $check_mode->title "
                    );
                }
            // End

            $import = $wpdb->query("INSERT INTO
                $tbl_pasabuy_pluss_mode
                    ($tbl_pasabuy_pluss_mode_fields)
                VALUES
                    ( '{$user["title"]}', '{$user["info"]}', '{$user["amount"]}', '{$user["action"]}',  '{$user["limit"]}', '$trigger', '{$user["wpid"]}' ) ");
            $import_id = $wpdb->insert_id;

            $hsid = CP_Globals::generating_pubkey($import_id, $tbl_pasabuy_pluss_mode, 'hash_id');

            if ($import < 1) {
                $wpdb->query("ROLLBACK");
                return array(
                    "status" => "failed",
                    "message" => "An error occured while submitting data to server."
                );
            }else{
                $wpdb->query("COMMIT");
                return array(
                    "status" => "success",
                    "message" => "Data has been added successfully."
                );
            }
        }
    }