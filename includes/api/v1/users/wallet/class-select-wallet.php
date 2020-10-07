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
	class CP_Select_wallet {

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

            if (!isset($_POST['pkey'])) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknown!",
                );
            }

            // Step 4: Check if parameters passed are empty
            if (empty($_POST['pkey'])) {
                return array(
                    "status" => "failed",
                    "message" => "Required fields cannot be empty.",
                );
            }

            $key = $_POST['pkey'];

            $data = $wpdb->get_row("SELECT
                    cw.public_key,
                    (SELECT meta_value FROM wp_usermeta WHERE meta_key = 'avatar' AND `user_id` = cw.wpid ) as avatar,
                    (SELECT display_name FROM wp_users WHERE ID = cw.wpid) as `name`,
                    (SELECT title FROM cp_currencies WHERE ID = cw.currency) as currency
                FROM
                    cp_wallets cw
                WHERE
                    public_key = '$key'");

            return array(
                "status" => "success",
                "data" => array(array(
                    'public_key' => $data->public_key,
                    'avatar' => $data->avatar == null? SP_PLUGIN_URL . "assets/default-avatar.png" : $data->avatar ,
                    'name' => $data->name,
                    'currency' => $data->currency,
                ))
            );
        }
    }