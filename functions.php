<?php

    /*
        Plugin Name: CoinPress WP Plugin
        Plugin URI: http://www.bytescrafter.net/projects/coinpress
        Description: Plugin for WordPress currency using blockchain technology.
        Version: 0.1.0
        Author: Bytes Crafter
        Author URI:   https://www.bytescrafter.net/about-us
        Text Domain:  coinpress-wp-plugin

        * @package      coinpress-wp-plugin
        * @author       Bytes Crafter

        * @copyright    2020 Bytes Crafter
        * @version      0.1.0

        * @wordpress-plugin
        * WC requires at least: 2.5.0
        * WC tested up to: 5.4.2
    */

    #region WP Recommendation - Prevent direct initilization of the plugin.
    if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

    if ( ! function_exists( 'is_plugin_active' ) )
    {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    #endregion

    define("CP_PLUGIN_PATH", plugin_dir_path( __FILE__ ) );
    define("CP_PLUGIN_URL", plugin_dir_url( __FILE__ ) );

    //Important config files and plugin updates.
    include_once ( plugin_dir_path( __FILE__ ) . '/includes/core/config.php' );
    include_once ( plugin_dir_path( __FILE__ ) . '/includes/core/update.php' );


    include_once ( CP_PLUGIN_PATH . '/includes/core/library.php' );


     //Make sure to create required mysql tables.
    include_once ( plugin_dir_path( __FILE__ ) . '/includes/core/dbhook.php' );

    //Includes assets if page is defined.
    include_once ( plugin_dir_path( __FILE__ ) . '/includes/core/assets.php' );

    //Include the REST API of USocketNet to be accessible.
    include_once ( plugin_dir_path( __FILE__ ) . '/includes/api/routes.php' );

?>