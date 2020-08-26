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

            // Step 1: Validate user
            if ( DV_Verification::is_verified() == false ) {
                return rest_ensure_response( 
                    array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Verification Issue!",
                    )
                );
            }

            $plugin = CP_Globals::verify_prerequisites();
            if ($plugin !== true) {
                return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. ".$plugin." plugin missing!",
                );
            }

            if (!isset($_POST['currency'])) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknwon!",
                );
            }

            if (empty($_POST['currency'])) {
                return array(
                    "status" => "failed",
                    "message" => "Required fields cannot be empty.",
                );
            }

            $date = CP_Globals::date_stamp();

            $table_wallet = CP_WALLETS;
            $table_wallet_fields = CP_WALLETS_FIELDS;
            
            $user_id = $_POST['wpid'];
            $currency = $_POST['currency'];

            $wpdb->query("START TRANSACTION");
            $check_user_wallets = $wpdb->get_results("SELECT * FROM cp_wallets WHERE wpid = $user_id ");
            $check = array();
            
            for ($count=0; $count < count($check_user_wallets) ; $count++) { 
                $check[] = $check_user_wallets[$count]->currency;

                if (in_array($check_user_wallets[$count]->currency, (array)$currency ) ) {
                    return array(
                        "status" => "failed",
                        "message" => "This user wallet is already exists.",
                    );
                }
            }

            // Insert wallet
            $user_wallet = $wpdb->query(" INSERT INTO cp_wallets ( wpid, currency) VALUES ( '$user_id', '$currency' )  ");
            $wallet_id = $wpdb->insert_id;

            $public_key = CP_Globals::update_public_key_hash($wallet_id, 'cp_wallets');

            if ($user_wallet < 1 ||  $public_key == false ) {
            $wpdb->query("ROLLBCK");

                return array(
                    "status" => "failed",
                    "message" => "An error occured while submitting data to server."
                );
            }else{
            $wpdb->query("COMMIT");

                return array(
                    "status" => "success",
                    "message" => "Data has been submitted successfully."
                );
            }

        }
    }