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
	class CP_Pasabuy_Pluss_Modes_Update {

        public static function listen(){
            return rest_ensure_response(
                self::listen_open()
            );
        }

        public static function catch_post(){
            $curl_user = array();
            $curl_user['wpid'] = $_POST['wpid'];
            $curl_user['psl_mode_id'] = $_POST['psl_mode_id'];
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

            if ( !isset($_POST['psl_mode_id']) ) {
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
            $wpdb->query("START TRANSACTION");
            // Verify if mode is existed
                $get_mode = $wpdb->get_row("SELECT * FROM $tbl_pasabuy_pluss_mode WHERE hash_id = '{$user["psl_mode_id"]}' AND `status` = 'active' AND id IN ( SELECT MAX( id ) FROM $tbl_pasabuy_pluss_mode ct WHERE ct.hash_id = hash_id  GROUP BY hash_id )  ");
                if(empty($get_mode)){
                    return array(
                        "status" => "failed",
                        "message" => "This Pasabuy Pluss mode does not exists."
                    );
                }
            // End

            isset($_POST['title']) && !empty($_POST['title'])? $user['title'] =  $_POST['title'] :  $user['title'] = $get_mode->title ;
            isset($_POST['info']) && !empty($_POST['info'])? $user['info'] =  $_POST['info'] :  $user['info'] = $get_mode->info ;
            isset($_POST['amount']) && !empty($_POST['amount'])? $user['amount'] = serialize( array( 'amount' => $_POST['amount'] ) ) : $user['amount'] = $get_mode->amount ;

            $import = $wpdb->query("INSERT INTO
                $tbl_pasabuy_pluss_mode
                    (`hash_id`, $tbl_pasabuy_pluss_mode_fields, `status`)
                VALUES
                    ('$get_mode->hash_id', '{$user["title"]}', '{$user["info"]}', '{$user["amount"]}', '{$user["wpid"]}', '$get_mode->status' ) ");

            $import_id = $wpdb->insert_id;

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