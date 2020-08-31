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

	class CP_Select_Balance {
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

            if (!isset($_POST['type'])) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknown!",
                );
            }

            if (empty($_POST['type'])) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknown!",
                );
            }
            
            if (strlen($_POST['type']) != 3) {
                return array(
                    "status" =>  "failed",
                    "message" => "Abbreviation must have 3 characters only."
                );
            }

            $get_key = $wpdb->get_row($wpdb->prepare("SELECT public_key, currency FROM cp_wallets WHERE wpid = %d  ", $_POST['wpid']));

            $get_currency = $wpdb->get_row($wpdb->prepare(" SELECT ID, title FROM cp_currencies WHERE abbrev = '%s'", $_POST['type']));

            if (!$get_currency) {
                return array(
                    "status" => "failed",
                    "message" => "This currency does not exists.",
                );
            }

            if ( $get_key->currency !==  $get_currency->ID   ) {
                return array(
                    "status" => "failed",
                    "message" => "You dont have ".$get_currency->title." wallet.",
                );
            }

            $result = $wpdb->get_row(
                $wpdb->prepare(" SELECT 
                    COALESCE(  
                        SUM(COALESCE( CASE WHEN recipient = '%s' THEN amount END , 0 ))  -  
                        SUM(COALESCE( CASE WHEN sender = '%s' THEN amount END, 0 ))
                        , 0 ) as balance 
                        FROM	cp_transaction", $get_key->public_key, $get_key->public_key));

            if (!$result) {
                return array(
                    "status"  => "failed",
                    "message"  => "An error occured while fetching data to server"
                );

            }else{
                return array(
                    "status"  => "success",
                    "data" => $result
                );
            }
        }
    }