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
	class CP_Transsaction_Listing {

        public static function listen(){
            return rest_ensure_response(
                self::listen_open()
            );
        }

        public static function catch_post(){
            $curl_user = array();

            $curl_user['sender'] = $_POST['wpid'];
            $curl_user['query'] = $_POST['query'];
            $curl_user['receive'] = $_POST['receive'];

            return $curl_user;
        }

        public static function listen_open(){

            global $wpdb;

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
                    "message" => "Please contact your administrator. ".$plugin." plugin missing!",
                );
            }

            // Get public key of current user
            $get_key = $wpdb->get_row($wpdb->prepare("SELECT public_key FROM cp_wallets WHERE wpid = %d", $_POST['wpid']));

            if (!$get_key) {
                return array(
                    "status" => "failed",
                    "message" => "You must have wallet first."
                );
            }

            $sql = "";

            if (isset($_POST['query'])) {
                if (empty($_POST['query'])) {
                    return array(
                        "status" => "failed",
                        "message" => "Required fields cannot be empty."
                    );
                }

                if ($_POST['query'] !== 'send' && $_POST['query'] !== 'receive' ) {
                    return array(
                        "status" => "failed",
                        "message" => "Unknown value of query."
                    );
                }

                $query = $_POST['query'];

                switch ($_POST['query']) {
                    case 'send':
                        $sql = " SELECT hash_id, sender, recipient, amount, date_created, currency FROM cp_transaction WHERE sender = '$get_key->public_key'  ";
                        break;

                    case 'receive':
                        $sql = " SELECT hash_id, sender, recipient, amount, date_created, currency FROM cp_transaction WHERE recipient = '$get_key->public_key'  ";

                        break;
                }

            }else if(isset($_POST['receive'])){

                if (empty($_POST['receive'])) {
                    return array(
                        "status" => "failed",
                        "message" => "Required fields cannot be empty."
                    );
                }

                // Get public key of current user
                $get_key = $wpdb->get_row($wpdb->prepare("SELECT public_key FROM cp_wallets WHERE wpid = %d", $_POST['receive']));

                if (!$get_key) {
                    return array(
                        "status" => "failed",
                        "message" => "This user must have wallet first."
                    );
                }

                $sql = " SELECT hash_id, sender, recipient, amount, date_created, currency FROM cp_transaction WHERE sender = '$get_key->public_key' OR  recipient = '$get_key->public_key'  ";

            }else{

                $sql = " SELECT hash_id, sender, recipient, amount, date_created, currency FROM cp_transaction  ";

            }

            $results = $wpdb->get_results($sql);

            return array(
                "status" => "success",
                "data" => $results
            );


        }
    }