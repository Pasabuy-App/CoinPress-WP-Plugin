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

	class CP_Create_User_Wallet {

        public static function listen(){
            return rest_ensure_response(
                self::listen_open()
            );
        }

        public static function listen_open(){

            global $wpdb;
            $table_wallet = CP_WALLETS;
            $table_wallet_fields = CP_WALLETS_FIELDS;

            // Step 1: Check if prerequisites plugin are missing
            $plugin = CP_Globals::verify_prerequisites();
            if ($plugin !== true) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. ".$plugin." plugin missing!",
                );
            }

            // Step 2: Validate user
            if ( DV_Verification::is_verified() == false ) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Verification issue!",
                );
            }

            // Step 3: Check if required parameters are passed
            if (!isset($_POST['query'])) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknwon!",
                );
            }

            // Step 4: Check if parameters passed are empty
            if (empty($_POST['query'])) {
                return array(
                    "status" => "failed",
                    "message" => "Required fields cannot be empty.",
                );
            }


            $date = CP_Globals::date_stamp();

            $table_wallet = CP_WALLETS;
            $table_wallet_fields = CP_WALLETS_FIELDS;
            $user_id = $_POST['wpid'];
            $currency = $_POST['query'];

            // Step 6: Start mysql transaction
            $wpdb->query("START TRANSACTION");

                // Step 5: Validate currency
                switch ($currency) {
                    case strlen($currency) == 3:
                        if (strlen($currency) !== 3) {
                            return array(
                                "status" => "failed",
                                "message" => "Invalid value of abbreviation."
                            );
                        }
                        if ($currency == 'CTR') {
                            return array(
                                "status" => "failed",
                                "message" => "This currency is unavailable."
                            );
                        }
                        $check_currency = $wpdb->get_row("SELECT ID FROM cp_currencies WHERE abbrev = '$currency' ");
                        break;

                    default:
                        $check_currency = $wpdb->get_row("SELECT ID FROM cp_currencies WHERE hash_id = '$currency' ");
                        break;

                }

                if (!$check_currency) {
                    return array(
                        "status" => "failed",
                        "message" => "This currencies does not exists.",
                    );
                }
                return $check_currency;

                $check_user_wallets = $wpdb->get_results("SELECT * FROM cp_wallets WHERE wpid = $user_id ");
                $check = array();

                for ($count=0; $count < count($check_user_wallets) ; $count++) {
                    $check[] = $check_user_wallets[$count]->currency;

                    if (in_array($check_user_wallets[$count]->currency, (array)$currency ) ) {
                        return array(
                            "status" => "exists",
                            "message" => "This user wallet currency is already exists.",
                            "data" => $check_user_wallets[$count]->public_key
                        );
                    }
                }

            // Step 7: Insert wallet
            $user_wallet = $wpdb->query(" INSERT INTO cp_wallets ( wpid, currency) VALUES ( '$user_id', '$currency' )  ");
            $wallet_id = $wpdb->insert_id;

            $public_key = CP_Globals::update_public_key_hash($wallet_id, 'cp_wallets');

            $update_hash_id = $wpdb->query("UPDATE cp_wallets SET hash_id = SHA2( '$wallet_id' , 256)  WHERE ID =  $wallet_id  ");

            // Step 8: Check if any queries above failed
            if ($user_wallet < 1 ||  $public_key == false || $update_hash_id < 1 ) {
                $wpdb->query("ROLLBCK");
                return array(
                    "status" => "failed",
                    "message" => "An error occured while submitting data to server."
                );
            }else{
            // Step 8: Commit if no errors found
                $wpdb->query("COMMIT");
                return array(
                    "status" => "success",
                    "message" => "Data has been submitted successfully."
                );
            }
        }
    }