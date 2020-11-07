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
	class CP_Pasabuy_Pluss_Modes_Listing {

        public static function listen(){
            return rest_ensure_response(
                self::listen_open()
            );
        }

        public static function catch_post(){
            $curl_user = array();
            isset($_POST['title']) && !empty($_POST['title'])? $curl_user['title'] =  $_POST['title'] :  $curl_user['title'] = null ;
            return $curl_user;
        }

        public static function listen_open(){

            global $wpdb;
            $tbl_pasabuy_mode = CP_PLS_MODES;

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

            $user = self::catch_post();

            $sql = "SELECT hash_id as ID, title, info, amount, status, created_by, date_created  FROM $tbl_pasabuy_mode
                WHERE id IN ( SELECT MAX( id ) FROM $tbl_pasabuy_mode ct WHERE ct.hash_id = hash_id  GROUP BY hash_id ) ";

            if ($user['title'] != null) {
                $sql .= " AND title LIKE '%{$user["title"]}%' ";
            }

            $data =  $wpdb->get_results($sql);

            foreach ($data as $key => $value) {
                $value->status = ucfirst($value->status);
                $value->amount = unserialize($value->amount)['amount'];
            }

            return array(
                "status" => "success",
                "data" => $data
            );
        }
    }