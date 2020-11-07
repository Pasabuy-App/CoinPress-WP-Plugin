<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
        * @package coinpress-wp-plugin
		* @version 0.1.0
		* This is the primary gateway of all the rest api request.
	*/
?>

<?php

    //Require the USocketNet class which have the core function of this plguin.

    // user/ wallet folder
    require plugin_dir_path(__FILE__) . '/v1/users/wallet/class-create-wallet.php'; // Example
    require plugin_dir_path(__FILE__) . '/v1/users/wallet/class-send-money.php'; // Example
    require plugin_dir_path(__FILE__) . '/v1/users/wallet/class-create-currencies.php'; // Example
    require plugin_dir_path(__FILE__) . '/v1/users/wallet/class-select-balance.php'; // Example
    require plugin_dir_path(__FILE__) . '/v1/users/wallet/class-listing.php'; // Example
    require plugin_dir_path(__FILE__) . '/v1/users/wallet/class-transacs.php'; // Example
    require plugin_dir_path(__FILE__) . '/v1/users/wallet/class-select-wallet.php'; // Example

    // Psasabuy pluss
        // require plugin_dir_path(__FILE__) . '/v1/users/pasabuy pluss/class-transactions.php'; // Example

        // Modes
            require plugin_dir_path(__FILE__) . '/v1/users/pasabuy pluss/modes/class-insert.php'; // Example
            require plugin_dir_path(__FILE__) . '/v1/users/pasabuy pluss/modes/class-listing.php'; // Example
            require plugin_dir_path(__FILE__) . '/v1/users/pasabuy pluss/modes/class-delete.php'; // Example
            require plugin_dir_path(__FILE__) . '/v1/users/pasabuy pluss/modes/class-update.php'; // Example


    // global
    require plugin_dir_path(__FILE__) . '/v1/class-globals.php'; // Example


	// Init check if USocketNet successfully request from wapi.
    function coinpress_route()
    {
        /*
         *  WALLET RESTAPI
        */
            register_rest_route( 'coinpress/v1/user/wallet', 'select', array(
                'methods' => 'POST',
                'callback' => array('CP_Select_wallet','listen'),
            ));

            register_rest_route( 'coinpress/v1/user/wallet', 'send', array(
                'methods' => 'POST',
                'callback' => array('CP_User_Send_Money','listen'),
            ));

            register_rest_route( 'coinpress/v1/user/wallet', 'create', array(
                'methods' => 'POST',
                'callback' => array('CP_Create_User_Wallet','listen'),
            ));

            register_rest_route( 'coinpress/v1/user/wallet', 'currencies', array(
                'methods' => 'POST',
                'callback' => array('CP_Create_Currencies','listen'),
            ));

            register_rest_route( 'coinpress/v1/user/wallet', 'balance', array(
                'methods' => 'POST',
                'callback' => array('CP_Select_Balance','listen'),
            ));

            register_rest_route( 'coinpress/v1/user/wallet', 'list', array(
                'methods' => 'POST',
                'callback' => array('CP_Listing_Wallet','listen'),
            ));

            register_rest_route( 'coinpress/v1/user/transac', 'list', array(
                'methods' => 'POST',
                'callback' => array('CP_Transsaction_Listing','listen'),
            ));
        /*
         *  PASABUY PLUSS RESTAPI
        */
            register_rest_route( 'coinpress/v1/user/pls/mode', 'insert', array(
                'methods' => 'POST',
                'callback' => array('CP_Pasabuy_Pluss_Modes_Insert','listen'),
            ));

            register_rest_route( 'coinpress/v1/user/pls/mode', 'list', array(
                'methods' => 'POST',
                'callback' => array('CP_Pasabuy_Pluss_Modes_Listing','listen'),
            ));

            register_rest_route( 'coinpress/v1/user/pls/mode', 'delete', array(
                'methods' => 'POST',
                'callback' => array('CP_Pasabuy_Pluss_Modes_Delete','listen'),
            ));

            register_rest_route( 'coinpress/v1/user/pls/mode', 'update', array(
                'methods' => 'POST',
                'callback' => array('CP_Pasabuy_Pluss_Modes_Update','listen'),
            ));
    }
    add_action( 'rest_api_init', 'coinpress_route' );

?>