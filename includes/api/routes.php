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
         *  LOCATION RESTAPI
        */
            register_rest_route( 'coinpress/v1/user/wallet', 'create', array(
                'methods' => 'POST',
                'callback' => array('CP_Create_User_Wallet','listen'),
            ));

    }
    add_action( 'rest_api_init', 'coinpress_route' );

?>