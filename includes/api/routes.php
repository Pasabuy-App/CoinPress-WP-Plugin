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
    require plugin_dir_path(__FILE__) . '/v1/users/class-auth.php'; // Example

    // user/ wallet folder
    require plugin_dir_path(__FILE__) . '/v1/users/wallet/class-create-wallet.php'; // Example
    require plugin_dir_path(__FILE__) . '/v1/users/wallet/class-send-money.php'; // Example
    require plugin_dir_path(__FILE__) . '/v1/users/wallet/class-create-currencies.php'; // Example
    require plugin_dir_path(__FILE__) . '/v1/users/wallet/class-select-balance.php'; // Example


    // global 
    require plugin_dir_path(__FILE__) . '/v1/class-globals.php'; // Example

	
	// Init check if USocketNet successfully request from wapi.
    function coinpress_route()
    {
        // Example
            register_rest_route( 'coinpress/v1/user', 'auth', array(
                'methods' => 'POST',
                'callback' => array('CP_Authenticate','listen'),
            ));     
        /*
         *  WALLET RESTAPI
        */
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
            

    }
    add_action( 'rest_api_init', 'coinpress_route' );

?>