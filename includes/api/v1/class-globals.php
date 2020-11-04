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


		public static function verify_prerequisites(){

            if(!class_exists('DV_Verification') ){
                return 'DataVice';
            }

			return true;

		}

        public static function date_stamp(){
            date_default_timezone_set('Asia/Manila');
            return date("Y-m-d h:i:s");
		}

		public static function update_public_key_hash($primary_key, $table_name){
			global $wpdb;

			$results = $wpdb->query("UPDATE  $table_name SET public_key = concat(
							substring('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed:=round(rand($primary_key)*4294967296))*36+1, 1),
							substring('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed:=round(rand(@seed)*4294967296))*36+1, 1),
							substring('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed:=round(rand(@seed)*4294967296))*36+1, 1),
							substring('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed:=round(rand(@seed)*4294967296))*36+1, 1),
							substring('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed:=round(rand(@seed)*4294967296))*36+1, 1),
							substring('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed:=round(rand(@seed)*4294967296))*36+1, 1),
							substring('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed:=round(rand(@seed)*4294967296))*36+1, 1),
							substring('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed:=round(rand(@seed)*4294967296))*36+1, 1),
							substring('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed)*36+1, 1)
						)
						WHERE ID = $primary_key;");
			if ($results < 1) {
				return false;
			}else{
				if ($results == 1) {
					return true;
				}
			}
		}


		 /**
		 * GENERATING HSID
		 * @param primary_key = primary key
		 * @param table_name = table name
		 * @param column_name = Column name to be updated
		 */
		public static function generating_pubkey($primary_key, $table_name, $column_name, $get_key = false, $lenght = 64){
            global $wpdb;

            $sql = "UPDATE  $table_name SET $column_name = concat(
                substring('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed:=round(rand($primary_key)*4294967296))*36+1, 1), ";

            for ($i=0; $i < $lenght ; $i++) {
                $sql .= "substring('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed:=round(rand(@seed)*4294967296))*36+1, 1),";
            }

            $sql .=" substring('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed)*36+1, 1)
            )
            WHERE ID = $primary_key;";


            $results = $wpdb->query($sql);

            if ($get_key = true) {
                $key  = $wpdb->get_row("SELECT `$column_name` as `key` FROM $table_name WHERE ID = '$primary_key' ");
                return $key->key;
            }

            if ($results < 1) {
				return false;
			}else{
				if ($results == 1) {
					return true;
				}
			}
        }

    }