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

            
		$wpdb->query("START TRANSACTION");

		$check_admin = get_user_meta(1, 'wp_capabilities');
		
		foreach ($check_admin as $key => $value) {
		   $verify = $value['administrator'];
		}
		
		if ($verify == true) {
			return "false";
		}
		$wpdb->query("COMMIT");
         
            $date = CP_Globals::date_stamp();

            $table_wallet = CP_WALLETS;
            $table_wallet_fields = CP_WALLETS_FIELDS;

            $hash = hash('sha256', 'The quick brown fox jumped over the lazy dog.');

            return$create_wallet = $wpdb->query("INSERT INTO $table_wallet $table_wallet_fields VALUES ('3', '1', '$hash', '3554', '$date' ) ");

        }
    }