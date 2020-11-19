<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
	 * @package coinpress-wp-plugin
     * @version 0.1.0
     * This is where you provide all the constant config.
	*/

	//Defining Global Variables

	define('CP_PREFIX', 'cp_');
	define('CP_CONFIGS', CP_PREFIX.'configs');

	define('CP_CURRENCIES', CP_PREFIX.'currencies');
	define('CP_CURRENCIES_FIELDS', '(`title`, `info`, `abbrev`, `exchange`, `created_by`)');

	define('CP_REVS', CP_PREFIX.'revisions');
	define('CP_REVS_FIELD', 'hash_id, revs_type, parent_id, child_key, child_val, created_by' );

	define('CP_WALLETS', CP_PREFIX.'wallets');
	define('CP_WALLETS_FIELDS', '( `wpid`, `currency`, `curhash`, `public_key`, `date_created` )');

	define('CP_TRANSACTION', CP_PREFIX.'transaction');
	define('CP_TRANSACTION_FIELDS', '(`sender`, `recipient`, `amount`, `prevhash`, `curhash`, `date_created` )');

	define('CP_PLS_TRANSACTIONS', CP_PREFIX.'pls_transaction');
	define('CP_PLS_TRANSACTIONS_FIELDS', '`wpid`, `stid`, `modes`, `tid`, `odid`, `created_by`');

	define('CP_PLS_MODES', CP_PREFIX.'pls_modes');
	define('CP_PLS_MODES_FIELDS', '`title`, `info`, `amount`, `action`, `limit`, `trigger`, `created_by`');
