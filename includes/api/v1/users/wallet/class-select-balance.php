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

           return $wpdb->get_row($wpdb->prepare(" SELECT COALESCE(  SUM(COALESCE( CASE WHEN recipient = %d THEN amount END , 0 ))  -  SUM(COALESCE( CASE WHEN sender = '%d' THEN amount END, 0 )), 0 ) as total_balance FROM	cp_transaction", 3, 3));

            return "HAHAHAHA";
        }
    }