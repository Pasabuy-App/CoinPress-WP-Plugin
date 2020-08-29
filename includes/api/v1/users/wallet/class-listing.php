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
	class CP_Listing_Wallet {

        public static function listen(){
            return rest_ensure_response( 
                self::listen_open()
            );
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
                    "message" => "Please contact your administrator. Verification issue!",
                );
            }
            
            isset($_POST['type'])? $type = $_POST['type']:  $type =  NULL ;
            
            $type == '0'? $abbrev = NULL: $abbrev = $type;
            
            // Step 3: Start mysql transaction
            $sql = "SELECT 
                wall.hash_id,
                wall.wpid,
                cur.title,
                cur.info
            FROM 
                cp_wallets wall
            INNER JOIN 
                cp_currencies cur ON cur.ID = wall.currency WHERE wpid = %d
            ";

            if (isset($_POST['type'])) {
                if ($abbrev  !== NULL) {
                    $sql .= " AND cur.abbrev = '$abbrev ' ";
                }
            }

            // Step 4: Return result
            $result =  $wpdb->get_results($wpdb->prepare($sql, $_POST['wpid']) );
            return array(
                "status" => "success",
                "data" => $result
            );
        }
    }