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

            // Step 1: Check if prerequisites plugin are missing
            $plugin = CP_Globals::verify_prerequisites();
            if ($plugin !== true) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. ".$plugin." plugin missing!",
                );
            }

            // Step 2: Validate user
            /* if ( DV_Verification::is_verified() == false ) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Verification issue!",
                );
            } */


            // Step 3: Check if required parameters are passed
            if (!isset($_POST['recipient']) || !isset($_POST['amount'])) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknown!",
                );
            }

            // Step 4: Check if parameters passed are empty
            if (empty($_POST['recipient']) || empty($_POST['amount'])) {
                return array(
                    "status" => "failed",
                    "message" => "Required fields cannot be empty.",
                );
            }

            // Step 5: Validate amount
            if ($_POST['amount'] == '0'  ) {
                return array(
                    "status" => "failed",
                    "message" => "Cannot send a money without money.",
                );
            }

            // Step 6: Valdiate sender and recipient
            if ($_POST['wpid'] === $_POST['recipient']) {
                return array(
                    "status" => "failed",
                    "message" => "Cannot send money in your own wallet.",
                );
            }

            $master_key = DV_Library_Config::dv_get_config('master_key', 123);

            $user = self::catch_post();

            // insering currency
            $check_currency = $wpdb->get_row("SELECT * FROM cp_currencies WHERE hash_id = '{$user["currency"]}'");

            if (empty($check_currency)) {
                return array(
                    "status" => "failed",
                    "message" => "This currencies does not exists.",
                );
            }

            if ($check_currency->status == '0') {
                return array(
                    "status" => "failed",
                    "message" => "This currencies is currently inactive.",
                );
            }

            // Step 7: Start mysql transaction
            $wpdb->query("START TRANSACTION");

            $check_admin = get_user_meta($_POST['wpid'], 'wp_capabilities');

            foreach ($check_admin as $key => $value) {
                foreach ($value as $key => $value) {
                    $verify_role = $key;
                    $verify_role_status = $value;
                }
            }

            // Check if public key is valid
            $check_recipient = $wpdb->get_row($wpdb->prepare( " SELECT public_key FROM cp_wallets WHERE public_key = %s ", $user["recipient"] ));
            if (!$check_recipient) {
                return array(
                    "status" => "failed",
                    "message" => "Unknown public key of recipient."
                );
            }
            // End

            // Step 8: Verify role
            if ( $verify_role == 'administrator' && $verify_role_status == true ) {
                // THIS SCRIPT WILL RUN IF WPID ADMIN

                // Step 9: SELECTING PUBLIC KEY OF USER AND RECIPIENT
                $get_id_sender = $wpdb->get_row($wpdb->prepare( " SELECT public_key FROM cp_wallets WHERE wpid = %d ", $user["sender"] ));
                if (!$get_id_sender  ) {
                    return array(
                        "status" => "failed",
                        "message" => "You must have wallet first.",
                    );
                }


                $send_money = $wpdb->query("INSERT INTO cp_transaction ( `sender`, `recipient`, `amount`, `prevhash`, `curhash`, `currency`) VALUES ( '$get_id_sender->public_key', '{$user["recipient"]}', '{$user["amount"]}', 'xyz', 'wasd', '{$user["currency"]}' )  ");
                $get_money_id = $wpdb->insert_id;

                $get_money_data = $wpdb->get_row("SELECT * FROM cp_transaction WHERE ID = $get_money_id");

                $hash = hash( 'sha256', $get_money_data->sender.$get_money_data->recipient.$get_money_data->amount.$get_money_data->date_created);

                $hash_prevhash = hash( 'sha256', $master_key.$get_money_data->date_created );

                $update_transaction = $wpdb->query("UPDATE cp_transaction SET `curhash` = '$hash', `prevhash` = '$hash_prevhash', `hash_id` = SHA2( '$get_money_id' , 256) WHERE ID = $get_money_id ");




                // Step 10: Check if any queries above failed
                if ($get_money_id < 1 || empty($get_money_data) ||  $update_transaction < 1 ) {
                    $wpdb->query("ROLLBACK");
                    return array(
                        "status" => "failed",
                        "message" => "An error occured while submiting data to server."
                    );
                }else{
                // Step 11: Commit if no errors found
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

                // Step 12: SELECTING PUBLIC KEY OF USER AND RECIPIENT
                $get_id_sender = $wpdb->get_row($wpdb->prepare( " SELECT public_key FROM cp_wallets WHERE wpid = %d ", $user["sender"] ));

                if (!$get_id_sender  ) {
                    return array(
                        "status" => "failed",
                        "message" => "You must have wallet first.",
                    );
                }

                $verify_sender_balance = $wpdb->get_row(" SELECT
                    COALESCE(
                        SUM(COALESCE( CASE WHEN recipient = '$get_id_sender->public_key' THEN amount END , 0 ))  -
                        SUM(COALESCE( CASE WHEN sender = '$get_id_sender->public_key' THEN amount END, 0 ))
                        , 0 ) as total_balance FROM	cp_transaction ");

                if ((int)$verify_sender_balance->total_balance < 0 == true){
                    return array(
                        "status" => "failed",
                        "message" => "You dont have enough balance in your wallet.",
                    );
                }

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

                // Step 13: Executing of transaction
                $send_money = $wpdb->query("INSERT INTO cp_transaction ( `sender`, `recipient`, `amount`, `currency` ) VALUES ( '$get_id_sender->public_key', '{$user["recipient"]}', '{$user["amount"]}', '{$user["currency"]}' )  ");
                $get_money_id = $wpdb->insert_id;

                $get_money_data = $wpdb->get_row("SELECT * FROM cp_transaction WHERE ID = $get_money_id");

                // Step 14: Hash transaction data for curhash
                $hash = hash( 'sha256', $get_money_data->sender.$get_money_data->recipient.$get_money_data->amount.$get_money_data->date_created);

                $hash_prevhash = hash( 'sha256', $master_key. $get_money_data->date_created );

                $update_transaction = $wpdb->query("UPDATE cp_transaction SET `curhash` = '$hash', `prevhash` = '$hash_prevhash', `hash_id` = SHA2( '$get_money_id' , 256) WHERE ID = $get_money_id ");

                // Step 15: Check if any queries above failed
                if ( $send_money < 1 || $get_money_id < 1 || empty($get_money_data) || $update_transaction < 1 ) {
                    $wpdb->query("ROLLBACK");
                    return array(
                        "status" => "failed",
                        "message" => "An error occured while submitting data to server.",
                    );
                }else{
                // Step 16 : Commit if no errors found
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
            $curl_user['currency'] = $_POST['cy'];

            return $curl_user;
        }
    }