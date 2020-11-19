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
	class CP_Pasabuy_Pluss_Verify {

        public static function catch_post(){
            $curl_user = array();
            $curl_user['wpid'] = $_POST['wpid'];
            $curl_user['stid'] = $_POST['stid'];
            return $curl_user;
        }

        public static function verify_pls_store(){

            global $wpdb;
            $tbl_wallet =  CP_WALLETS;
            $tbl_currency = CP_CURRENCIES;
            $tbl_pls_mode = CP_PLS_MODES;
            $tbl_cp_transaction = CP_TRANSACTION;
            $tbl_pls_mode_transaction = CP_PLS_TRANSACTIONS;
            $time = time();
            $year = date("Y");
            $day = lcfirst(date('D', $time));
            $smp = array();
            $data = array();

            // Step 2: Validate user
            if ( DV_Verification::is_verified() == false ) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Verification issue!",
                );
            }

            $user = self::catch_post();

            // Get currency
                $currency = $wpdb->get_row("SELECT ID, hash_id  FROM $tbl_currency WHERE abbrev = 'PLS' ");
                if (empty($currency)) {
                    return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Pasabuy PLUS currency is missing.!",
                    );
                }
            // End

            // Get user wallet
                $wallet = $wpdb->get_row("SELECT * FROM $tbl_wallet WHERE wpid = '{$user["wpid"]}' AND  currency = '$currency->ID'   ");
                if (empty($wallet)) {
                    return array(
                        "status" => "failed",
                        "message" => "You must have wallet first."
                    );
                }
            // End

            $get_mode = $wpdb->get_results("SELECT * FROM $tbl_pls_mode ");

            if (empty($get_mode)) {
                return array(
                    "status" => "failed",
                    "message" => "Please contact your administrator. PLS Mode empty"
                );
            }

            foreach ($get_mode as $key => $value) {
                $get_transaction = $wpdb->get_row("SELECT COUNT(ID) as `count` FROM $tbl_pls_mode_transaction WHERE stid = '{$user["stid"]}' AND mode = '$value->hash_id' AND wpid = '{$user["wpid"]}' ");
                if (!empty($get_transaction) ){
                    if ($get_transaction->count >= $value->limit) {
                        unset($get_mode[$key]);
                        continue;
                    }else {
                        $smp = $value;
                    }
                }
            }

            foreach ($smp as $key => $value) {
                $value->amount = unserialize($value->amount)["amount"];
            }
            return array(
                "status" => "success",
                "data" => $smp
            );
        }
    }