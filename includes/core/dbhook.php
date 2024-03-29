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

	function cp_dbhook_activate(){

		//Initializing wordpress global variable
		global $wpdb;

		//Passing from global defined variable to local variable

		$tbl_confg = CP_CONFIGS;
		$tbl_revs = CP_REVS;
		$tbl_wallet = CP_WALLETS;
		$tbl_transac = CP_TRANSACTION;
		$tbl_currencies = CP_CURRENCIES;

		//Database table creation for configs
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_confg'" ) != $tbl_confg) {
			$sql = "CREATE TABLE `".$tbl_confg."` (";
				$sql .= " `ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= " `hash_id` varchar(255) NOT NULL COMMENT 'Config Description', ";
				$sql .= " `title` varchar(255) NOT NULL COMMENT 'Config Description', ";
				$sql .= " `info` varchar(255) NOT NULL COMMENT 'Config Description', ";
				$sql .= " `config_key` varchar(50) NOT NULL COMMENT 'Config KEY', ";
				$sql .= " `config_value` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Config VALUES', ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);
			$wpdb->query(" INSERT INTO $tbl_confg  (ID, hash_id, title, info, config_key, config_value) VALUES (1, sha2('1', 256), 'Maximum ammount of money transaction', 'This config is the maximum ammount that user can send money to other user', 'maximum_ammount', '1' );");
			$wpdb->query(" INSERT INTO $tbl_confg  (ID, hash_id, title, info, config_key, config_value) VALUES (2, sha2('2', 256), 'Minimum ammount of money transaction', 'This config is the minimmum ammount that user can send money to other user', 'minimum_ammount', '2' );");
		}

		//Database table creation for wallet
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_wallet'" ) != $tbl_wallet) {
			$sql = "CREATE TABLE `".$tbl_wallet."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= " `hash_id` varchar(255) NOT NULL COMMENT 'Config Description', ";
				$sql .= " `wpid` bigint(20) NOT NULL COMMENT 'User ID of Wallet owner', ";
				$sql .= " `currency` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Currency ID', ";
				$sql .= " `public_key` varchar(255) NOT NULL COMMENT 'Hash ID of this Wallet', ";
				$sql .= " `date_created` datetime DEFAULT current_timestamp() COMMENT 'Date wallet was created', ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB ; ";
			$result = $wpdb->get_results($sql);
		}

		//Database table creation for wallet log
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_transac'" ) != $tbl_transac) {
			$sql = "CREATE TABLE `".$tbl_transac."` (";
				$sql .= "  `ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= "  `hash_id` varchar(255) NOT NULL COMMENT 'Config Description', ";
				$sql .= "  `sender` varchar(255) NOT NULL, ";
				$sql .= "  `recipient` varchar(255) NOT NULL, ";
				$sql .= "  `amount` decimal(20,2) NOT NULL, ";
				$sql .= "  `remarks` varchar(255)  NOT NULL DEFAULT 'None' , ";
				$sql .= "  `prevhash` varchar(255) NOT NULL, ";
				$sql .= "  `currency` varchar(255) NOT NULL, ";
				$sql .= "  `curhash` varchar(255) NOT NULL, ";
				$sql .= "  `date_created` datetime DEFAULT current_timestamp(), ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB ; ";
			$result = $wpdb->get_results($sql);
		}

		//Database table creation for currencies
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_currencies'" ) != $tbl_currencies) {
			$sql = "CREATE TABLE `".$tbl_currencies."` (";
				$sql .= "  `ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= "  `hash_id` varchar(255) NOT NULL COMMENT 'Config Description', ";
				$sql .= "  `title` varchar(50) NOT NULL COMMENT 'Name of Currency', ";
				$sql .= "  `info` varchar(255) NOT NULL COMMENT 'Description', ";
				$sql .= "  `abbrev` varchar(3) NOT NULL DEFAULT 'CPC' COMMENT 'Abbreviation', ";
				$sql .= "   `status` enum('1','0') NOT NULL, ";
				$sql .= "  `exchange` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Exchange rate: CPC=1', ";
				$sql .= "  `created_by` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Created the Currency', ";
				$sql .= " `date_created` datetime DEFAULT current_timestamp() COMMENT 'Date of currency creation', ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB ; ";
			$result = $wpdb->get_results($sql);

			$wpdb->query(" INSERT INTO $tbl_currencies  (ID, hash_id, title, info, abbrev, exchange, created_by) VALUES (1, SHA2( '1' , 256), 'Control', 'Origin', 'CTR', '1', '1' );");
		}

		//Database table creation for revisions
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_revs'" ) != $tbl_revs) {
			$sql = "CREATE TABLE `".$tbl_revs."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= "`hash_id` varchar(255) NOT NULL COMMENT 'Config Description', ";
				$sql .= "`revs_type` enum('none','configs','currencies') NOT NULL COMMENT 'Target table', ";
				$sql .= "`parent_id` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Parent ID of this Revision', ";
				$sql .= "`child_key` varchar(50) NOT NULL COMMENT 'Column name on the table', ";
				$sql .= "`child_val` longtext NOT NULL COMMENT 'Text Value of the row Key.', ";
				$sql .= "`created_by` bigint(20) NOT NULL DEFAULT 0 COMMENT 'User ID created this Revision.', ";
				$sql .= "`date_created` datetime DEFAULT current_timestamp() COMMENT 'The date this Revision is created.', ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);

			$wpdb->query(" INSERT INTO $tbl_revs  (ID, hash_id, revs_type, parent_id, child_key, child_val, created_by) VALUES (ID, sha2(1, 256), 'configs', '1', 'maximum_ammount', '10000', '1' );");
			$wpdb->query(" INSERT INTO $tbl_revs  (ID, hash_id, revs_type, parent_id, child_key, child_val, created_by) VALUES (ID, sha2(2, 256), 'configs', '2', 'minimum_ammount', '25000', '2' );");
		}
	}
	add_action( 'activated_plugin', 'cp_dbhook_activate');