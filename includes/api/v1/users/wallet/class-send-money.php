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

	class CP_User_Send_Money {
        public static function listen(){
            return rest_ensure_response( 
                self::listen_open()
            );
        }
    
        public static function listen_open(){
            global $wpdb;

            // Step 1: Validate user
            // if ( DV_Verification::is_verified() == false ) {
            //     return rest_ensure_response( 
            //         array(
            //             "status" => "unknown",
            //             "message" => "Please contact your administrator. Verification Issue!",
            //         )
            //     );
            // }

            $plugin = CP_Globals::verify_prerequisites();
            if ($plugin !== true) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. ".$plugin." plugin missing!",
                );
            }

            $user = self::catch_post();
            $wpdb->query("START TRANSACTION");

            $check_admin = get_user_meta($_POST['wpid'], 'wp_capabilities');
		
            foreach ($check_admin as $key => $value) {
                foreach ($value as $key => $value) {
                    $verify_role = $key;
                    $verify_role_status = $value;
                }
            }

            if ( $verify_role == 'administrator' && $verify_role_status == true ) {
                // THIS SCRIPT WILL RUN IF WPID ADMIN
                $send_money = $wpdb->query("INSERT INTO cp_transaction ( `sender`, `recipient`, `amount`, `prevhash`, `curhash` ) VALUES ( '{$user["sender"]}', '{$user["recipient"]}', '{$user["amount"]}', 'xyz', 'wasd' )  ");
                $get_money_id = $wpdb->insert_id;

                $get_money_data = $wpdb->get_row("SELECT * FROM cp_transaction WHERE ID = $get_money_id");

                $hash = hash( 'sha256', $get_money_data->sender.$get_money_data->recipient.$get_money_data->amount.$get_money_data->date_created);
                
                $update_transaction = $wpdb->query("UPDATE cp_transaction SET `curhash` = '$hash' WHERE ID = $get_money_id ");

                if ($send_money_id < 1 || empty($get_money_data) ||  $update_transaction < 1 ) {
                    $wpdb->query("ROLLBACK");
            
                    return array(
                        "status" => "failed",
                        "message" => "An error occured while submiting data to server."
                    );
                }else{
                    $wpdb->query("COMMIT");

                    return array(
                        "status" => "success",
                        "message" => "Data has been added successfully."
                    );
                }
            }else{
                // THIS SCRIPT WILL RUN IF WPID IS NOT USER

                /**
                    Verifying Balance of user before executing transaction                    
                */

                $verify_sender_balance = $wpdb->get_row(" SELECT COALESCE(  SUM(COALESCE( CASE WHEN recipient = '{$user["sender"]}' THEN amount END , 0 ))  -  SUM(COALESCE( CASE WHEN sender = '{$user["sender"]}' THEN amount END, 0 )), 0 ) as total_balance FROM	cp_transaction ");
                
                if ((int)$verify_sender_balance->total_balance == 0) {
                    return array(
                        "status" => "failed",
                        "message" => "You dont have enough balance in your wallet.",
                    );
                }
            
                if ((int)$user['amount'] > (int)$verify_sender_balance->total_balance  ){
                    return array(
                        "status" => "failed",
                        "message" => "Your balance is lower than the amount that your sending",
                    );
                }

                // Executing of transaction                     
                $send_money = $wpdb->query("INSERT INTO cp_transaction ( `sender`, `recipient`, `amount` ) VALUES ( '{$user["sender"]}', '{$user["recipient"]}', '{$user["amount"]}' )  ");
                $get_money_id = $wpdb->insert_id;

                $get_money_data = $wpdb->get_row("SELECT * FROM cp_transaction WHERE ID = $get_money_id");
                
                // Hash transaction data for curhash
                $hash = hash( 'sha256', $get_money_data->sender.$get_money_data->recipient.$get_money_data->amount.$get_money_data->date_created);
                
                $update_transaction = $wpdb->query("UPDATE cp_transaction SET `curhash` = '$hash' WHERE ID = $get_money_id ");

                if ($get_money_id < 1 || empty($get_money_data) || $update_transaction < 1 ) {
                    $wpdb->query("ROLLBACK");
                    return array(
                        "status" => "failed",
                        "message" => "An error occured while submitting data to server.",
                    );

                }else{
                    $wpdb->query("COMMIT");
                    return array(
                        "status" => "success",
                        "message" => "Data has been added successfully.",
                    );
                }
            }
        }

        public static function catch_post(){
            $curl_user = array();

            $curl_user['sender'] = $_POST['wpid'];
            $curl_user['recipient'] = $_POST['recipient'];
            $curl_user['amount'] = $_POST['amount'];

            return $curl_user;

        }
    }