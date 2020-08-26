<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/** 
        * @package sociopress-wp-plugin
		* @version 0.1.0
		* This is the primary gateway of all the rest api request.
	*/

  	class CP_Globals {

        public static function date_stamp(){
            date_default_timezone_set('Asia/Manila');
            return date("Y-m-d h:i:s");
		}
        
    }