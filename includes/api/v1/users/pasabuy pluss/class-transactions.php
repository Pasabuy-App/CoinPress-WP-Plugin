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
	class CP_Pasabuy_Pluss_Transaction_Insert {

        public static function listen(){
            return rest_ensure_response(
                self::listen_open()
            );
        }

        public static function catch_post(){
            $curl_user = array();

            $curl_user['stid'] = $_POST['stid'];
            $curl_user['modes'] = $_POST['modes'];
            isset($_POST['trid']) && !empty($_POST['trid'])? $curl_user['store_id'] =  $_POST['trid'] :  $curl_user['store_id'] = null ;
            $curl_user['odid'] = $_POST['odid'];
            $curl_user['wpid'] = $_POST['wpid'];

            return $curl_user;
        }

        public static function listen_open(){

            global $wpdb;

            $tbl_pasabuy_pluss_transaction = CP_PLS_TRANSACTIONS;
            $tbl_pasabuy_pluss_transaction_fileds = CP_PLS_TRANSACTIONS_FIELDS;



        }

    }