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

	class CP_Order_Payment_Transactions {



        public static function mover_accept_order( string $vhid,int $amount, string $store_wallet,  string $abbrev = 'CTC' ){

            global $wpdb;
            $tbl_currency = CP_CURRENCIES;
            $table_mover = HP_MOVERS_v2;
            $tbl_vehicle = HP_VEHICLES_v2;
            $tbl_transaction = CP_TRANSACTION;
            $master_key = DV_Library_Config::dv_get_config('master_key', 123);

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

            if (empty($vhid) || empty($amount) || empty($store_wallet)) {
                return array(
                    "status" => "failed",
                    "message" => "Required fileds cannot be empty."
                );
            }


            /* Check if Mover exists */
                $verify_vehicle = $wpdb->get_row("SELECT mvid FROM $tbl_vehicle WHERE hsid = '$vhid' AND `status` = 'active' AND id IN ( SELECT MAX( id ) FROM $tbl_vehicle v WHERE v.hsid = hsid  GROUP BY hsid ) ");
                $check_mover = $wpdb->get_row("SELECT wpid FROM $table_mover WHERE pubkey = '$verify_vehicle->mvid' AND `status` = 'active' AND id IN ( SELECT MAX( id ) FROM $table_mover v WHERE v.hsid = hsid  GROUP BY hsid )  ");
                if (empty($check_mover)) {
                    return array(
                        "status" => "failed",
                        "message" => "This mover does not exists."
                    );
                }
            /* End */

            /* Check Mover Balance */
                // Get currency
                    $get_currency = $wpdb->get_row($wpdb->prepare(" SELECT ID, hash_id, title FROM $tbl_currency WHERE abbrev = '%s' ", $abbrev ));
                    if (empty($get_currency)) {
                        return array(
                            "status" => "failed",
                            "message" => "Please contact  your administrator. Credit currency is missing." );
                    }

                // Get Mover wallet
                    $get_wallet = $wpdb->get_row($wpdb->prepare("SELECT public_key, currency FROM cp_wallets WHERE wpid = %d AND currency = '%s' ", $check_mover->wpid, $get_currency->ID));
                    if (empty($get_wallet)) {
                        return array(
                            "status" => "failed",
                            "message" => "This mover does not have an wallet."
                        );
                    }

                // Get mover balance
                    $balance = $wpdb->get_row(
                        $wpdb->prepare(" SELECT
                            COALESCE(
                                SUM(COALESCE( CASE WHEN recipient = '%s' THEN amount END , 0 ))  -
                                SUM(COALESCE( CASE WHEN sender = '%s' THEN amount END, 0 ))
                                , 0 ) as balance
                                FROM $tbl_transaction ", $get_wallet->public_key, $get_wallet->public_key));

                    if ($balance->balance < $amount ) {
                        return array(
                            "status" => "failed",
                            "message" => "Sorry you dont have enough balance in your credit wallet for this order.",
                        );
                    }
            /* End */

            /* Send Money */
                $send_money = $wpdb->query("INSERT INTO $tbl_transaction ( `sender`, `recipient`, `amount`, `currency` ) VALUES ( '$get_wallet->public_key', '$store_wallet', $amount, '$get_currency->hash_id' )  ");
                $get_money_id = $wpdb->insert_id;

                $get_money_data = $wpdb->get_row("SELECT * FROM $tbl_transaction WHERE ID = $get_money_id");

                $hash = hash( 'sha256', $get_money_data->sender.$get_money_data->recipient.$get_money_data->amount.$get_money_data->date_created);

                $hash_prevhash = hash( 'sha256', $master_key. $get_money_data->date_created );

                $update_transaction = $wpdb->query("UPDATE $tbl_transaction SET `curhash` = '$hash', `prevhash` = '$hash_prevhash', `hash_id` = SHA2( '$get_money_id' , 256) WHERE ID = '$get_money_id' ");

                if ( $send_money < 1 || empty($get_money_data) || $update_transaction < 1 ) {
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

            /* End */
        }

        public static function commission_computation(int $amount, string $stid){

            global $wpdb;
            $tbl_store = TP_STORES_v2;

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

            if (empty($amount) || empty($stid)) {
                return array(
                    "status" => "failed",
                    "message" => "Required fields cannot be empty.",
                );
            }

            /* Get Store Data */
                $store_data = $wpdb->get_row("SELECT commision FROM $tbl_store WHERE `status` = 'active' AND hsid = '$stid' AND id IN ( SELECT MAX( id ) FROM $tbl_store s WHERE s.hsid = hsid  GROUP BY hsid ) ");
                if (empty($store_data)) {
                    return array(
                        "status" => "failed",
                        "message" => "This store does not exists",
                    );
                }
            /* End */

            /* Compute commision */
                $total_commision = $amount * (int)$store_data->commision / 100;
                $total_price = $amount - $total_commision;

                return array(
                    "status" => "success",
                    "data" => array(
                        "total_price" => $total_price,
                        "total_commision" => $total_commision
                    )
                );
            /* End */

        }
    }