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
            $curl_user['title'] = $_POST['title'];
            $curl_user['info'] = $_POST['info'];
            $curl_user['amount'] = $_POST['amount'];
            $curl_user['wpid'] = $_POST['wpid'];
            return $curl_user;
        }

        public static function listen_open(){

            global $wpdb;
            $tbl_pasabuy_mode = CP_PLS_MODES;

            $data =  $wpdb->get_results("SELECT hash_id as ID, title, info, amount, status, created_by, date_created  FROM $tbl_pasabuy_mode");

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