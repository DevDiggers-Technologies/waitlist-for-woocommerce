<?php
/**
 * Install routine.
 *
 * @package DevDiggers Waitlist for WooCommerce
 */

namespace DDWCWaitingList\Includes;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_Install' ) ) {
	/**
	 * Install class.
	 */
	class DDWCWL_Install {
		/**
		 * Get subscribers table name.
		 *
		 * @return string
		 */
		public static function get_table_name() {
			global $wpdb;
			return $wpdb->prefix . 'ddwcwl_subscribers';
		}

		/**
		 * Create the subscribers table. dbDelta leaves an existing table and its rows alone.
		 *
		 * @return void
		 */
		public static function install() {
			global $wpdb;

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$table   = self::get_table_name();
			$collate = $wpdb->get_charset_collate();

			// Email is indexed at 190 chars so the unique key fits utf8mb4 on older MySQL.
			dbDelta(
				"CREATE TABLE {$table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				product_id bigint(20) unsigned NOT NULL,
				parent_id bigint(20) unsigned NOT NULL DEFAULT 0,
				email varchar(190) NOT NULL,
				user_id bigint(20) unsigned NOT NULL DEFAULT 0,
				status varchar(20) NOT NULL DEFAULT 'waiting',
				token varchar(64) NOT NULL DEFAULT '',
				created_at datetime NOT NULL,
				notified_at datetime DEFAULT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY product_email (product_id,email),
				KEY parent_id (parent_id),
				KEY status (status),
				KEY email (email),
				KEY token (token),
				KEY created_at (created_at)
				) {$collate};"
			);
		}
	}
}
