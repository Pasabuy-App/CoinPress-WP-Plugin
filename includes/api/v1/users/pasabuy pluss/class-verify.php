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

        public static function listen(){
            return rest_ensure_response(
                self::verify_pls_store()
            );
        }

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

                    $user_wallet = $wpdb->query(" INSERT INTO $tbl_wallet ( wpid, currency) VALUES ( '{$user["wpid"]}', '$currency->ID' )  ");
                    $wallet_id = $wpdb->insert_id;

                    $public_key = CP_Globals::update_public_key_hash($wallet_id, $tbl_wallet);
                    $update_hash_id = $wpdb->query("UPDATE $tbl_wallet SET hash_id = SHA2( '$wallet_id' , 256)  WHERE ID = '$wallet_id'  ");

                    $wallet = $wpdb->get_row("SELECT * FROM $tbl_wallet WHERE ID = '$wallet_id'");

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
                        $value->amount = unserialize($value->amount)["amount"];

                        $smp = $value;
                    }
                }
            }

            return array(
                "status" => "success",
                "data" => $smp
            );
        }
    }