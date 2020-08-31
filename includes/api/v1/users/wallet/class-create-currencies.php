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
	class CP_Create_Currencies {

        public static function listen(){
            return rest_ensure_response( 
                self::listen_open()
            );
        }
    
        public static function listen_open(){

            global $wpdb;
            $tbl_currencies_field = CP_CURRENCIES_FIELDS;

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
            if (!isset($_POST['title']) || !isset($_POST['info']) || !isset($_POST['abbrev']) || !isset($_POST['exchange']) ) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknown!",
                );
            }

            // Step 4: Check if parameters passed are empty
            if (empty($_POST['title']) || empty($_POST['info']) || empty($_POST['abbrev']) || empty($_POST['exchange']) ) {
                return array(
                    "status" => "failed",
                    "message" => "Required fields cannot be empty.",
                );
            }

            // Step 5: Validate abbrevation length
            if (strlen($_POST['abbrev']) != 3) {
                return array(
                    "status" =>  "failed",
                    "message" => "Abbreviation must have 3 characters only."
                );
            }

            $user = self::catch_post();

            $title      = $user['title'];
            $info       = $user['info'];
            $abbrev     = $user['abbrev'];
            $exchange   = $user['exchange'];
            $wpid       = $user['wpid'];

            /**
               Step 6: Validating title if already existed 
            */
            $check_title_if_existed = $wpdb->query(" SELECT title  FROM cp_currencies WHERE title REGEXP '^$title' ");
            if ($check_title_if_existed > 0) {
                return array(
                    "status" => "success",
                    "message" => "Title is already exists."
                );
            }

            $check_abbrev_if_existed = $wpdb->query(" SELECT abbrev FROM cp_currencies WHERE abbrev REGEXP '^$abbrev' ");
            if ($check_abbrev_if_existed > 0 ) {
                return array(
                    "status" => "success",
                    "message" => "Abbrev is already exists."
                );
            }

            /**
               Step 7: Start mysql transaction
            */
            $wpdb->query("START TRANSACTION");

                $result = $wpdb->query($wpdb->prepare(" INSERT INTO cp_currencies $tbl_currencies_field VALUES ('%s', '%s', '%s', '%s', %d )", $title, $info, $abbrev, $exchange, $wpid  ));
                $result_id = $wpdb->insert_id;

                $update_hash_id = $wpdb->query("UPDATE cp_currencies SET hash_id = SHA2( '$result_id' , 256) WHERE ID = $result_id");

            // Step 8: Check if any queries above failed
            if ($result < 1 || $update_hash_id < 1) {
                $wpdb->query("ROLLBACK");
                return array(
                    "status" => "failed",
                    "message" => "An error occured while submitting data to server."
                );
            }else{
            // Step 9: Commit if no errors found
                $wpdb->query("COMMIT");
                return array(
                    "status" => "success",
                    "message" => "Data has been added successfully ."
                );
            }

        }

        public static function catch_post(){
            $curl_user = array();

            $curl_user['wpid'] = $_POST['wpid'];
            $curl_user['title'] = $_POST['title'];
            $curl_user['info'] = $_POST['info'];
            $curl_user['abbrev'] = $_POST['abbrev'];
            $curl_user['exchange'] = $_POST['exchange'];

            return $curl_user;
        }
    }