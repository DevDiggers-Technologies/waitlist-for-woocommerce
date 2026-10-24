<?php
/**
 * Self-check for the Free/Pro boundary.
 *
 * Every Pro feature is meant to be physically absent from this plugin, not switched off.
 * The easy ways to break that are a rebuild from a stale source tree or a file copied
 * across from the Pro plugin. This asserts that neither has happened.
 *
 *   php bin/test-free-boundary.php
 *
 * @package DevDiggers Waitlist for WooCommerce
 */

$root   = dirname( __DIR__ ) . '/';
$errors = [];

/**
 * Read a file, or return '' when it is absent.
 *
 * @param string $path Absolute path.
 * @return string
 */
function ddwcwl_read( $path ) {
	return file_exists( $path ) ? (string) file_get_contents( $path ) : '';
}

/**
 * Every PHP file that belongs to this plugin, excluding the bundled framework and tooling.
 *
 * @param string $root Plugin root.
 * @return string[]
 */
function ddwcwl_plugin_php( $root ) {
	$files    = [];
	$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) );

	foreach ( $iterator as $file ) {
		$path = $file->getPathname();

		if ( 'php' !== strtolower( $file->getExtension() ) ) {
			continue;
		}

		foreach ( [ '/node_modules/', '/devdiggers-framework/', '/bin/', '/build/', '/tests/' ] as $skip ) {
			if ( false !== strpos( $path, $skip ) ) {
				continue 2;
			}
		}

		$files[ str_replace( $root, '', $path ) ] = ddwcwl_read( $path );
	}

	return $files;
}

$php = ddwcwl_plugin_php( $root );

// 1. Pro-only files are gone, not merely unhooked.
foreach ( [
	'helper/email-log/email-log-helper.php',
	'includes/emails/reminder-email.php',
	'includes/emails/optin-email.php',
	'src/admin/import.js',
	'assets/sample/waiting-list-subscribers.csv',
	'templates/admin/subscribers/import-template.php',
	'templates/admin/emails/email-log-list-template.php',
	'templates/admin/configuration/license-configuration-template.php',
	'templates/admin/configuration/coupons-configuration-template.php',
	'templates/admin/configuration/integrations-configuration-template.php',
] as $file ) {
	if ( file_exists( $root . $file ) ) {
		$errors[] = "Pro file {$file} is shipped.";
	}
}

// 2. No licensing, updater or remote notifications. WordPress.org rejects a free plugin
// that phones home for a key, reachable or not.
foreach ( $php as $relative => $contents ) {
	foreach ( [ 'ddfw_is_license_activated', 'ddfw_register_plugin_for_updates', '_ddwcwl_purchase_code', '_ddwcwl_license_activated', 'DevDiggers_Notifications', 'DDFW_Plugin_Updater' ] as $needle ) {
		if ( false !== strpos( $contents, $needle ) ) {
			$errors[] = "Licensing reference '{$needle}' is present in {$relative}.";
		}
	}
}

foreach ( [ 'includes/class-ddfw-plugin-updater.php', 'includes/class-devdiggers-notifications.php', 'includes/class-ddfw-plugin-dependency.php', 'templates/layout/license.php', 'assets/js/notifications.js' ] as $file ) {
	if ( file_exists( $root . 'devdiggers-framework/' . $file ) ) {
		$errors[] = "Bundled framework ships {$file}.";
	}
}

// 3. No outbound calls, cron, exports or Pro AJAX. Restock emails go out through Action Scheduler only.
$registrations = [
	'wp_remote_',
	'wp_safe_remote_',
	'register_rest_route',
	'wp_schedule_event',
	'wp_schedule_single_event',
	'wp_unschedule_hook',
	'fputcsv',
	'admin_post_ddwcwl_export_subscribers',
	'wp_ajax_ddwcwl_import_batch',
	'wp_ajax_ddwcwl_get_form',
	'wp_ajax_nopriv_ddwcwl_get_form',
	'ddwcwl_hourly_tasks',
	'woocommerce_order_status_processing',
	'woocommerce_order_status_completed',
	'woocommerce_is_purchasable',
	'woocommerce_loop_add_to_cart_link',
	'render_block_woocommerce/product-button',
	'woocommerce_product_options_stock_status',
	'woocommerce_variation_options_inventory',
];

foreach ( $php as $relative => $contents ) {
	foreach ( $registrations as $needle ) {
		if ( false !== strpos( $contents, $needle ) ) {
			$errors[] = "Pro registration {$needle} is present in {$relative}.";
		}
	}
}

// 4. No Pro business logic left behind in shared files: coupons, early access, smart restock rules,
// reminders, double opt-in, webhooks, conversion tracking, email log, extra fields, restock dates.
$pro_logic = [
	'WC_Coupon',
	'maybe_create_coupon',
	'coupon_code',
	'_ddwcwl_reserved_until',
	'get_reserved_until',
	'has_reserved_access',
	'ddwcwl_access',
	'_ddwcwl_allowance',
	"\$ddwcwl_waiting_list['notify_ratio']",
	"\$ddwcwl_waiting_list['min_stock_threshold']",
	"\$ddwcwl_waiting_list['batch_size']",
	'send_reminders',
	'get_reminders_due',
	'DDWCWL_Reminder_Email',
	'DDWCWL_Optin_Email',
	"'pending'",
	'purge_unconfirmed',
	'ddwcwl_send_webhook',
	'webhook_url',
	'hash_hmac',
	'ddwcwl_track_conversion',
	'converted_at',
	'->revenue',
	'SUM(s.revenue)',
	'clicked_at',
	'DDWCWL_Email_Log_Helper',
	'ddwcwl_email_log',
	'purge_closed_older_than',
	'ddwcwl_quantity',
	'ddwcwl_phone',
	'_ddwcwl_restock_date',
	'get_restock_info',
	'ddwcwl_print_modal',
	'ddwcwl_loop_button',
];

foreach ( $php as $relative => $contents ) {
	foreach ( $pro_logic as $needle ) {
		if ( false !== strpos( $contents, stripslashes( $needle ) ) ) {
			$errors[] = "Pro logic '{$needle}' is present in {$relative}.";
		}
	}
}

// 5. No Pro-only setting is registered, so a crafted options.php or wizard POST has nothing to write.
$admin_functions = ddwcwl_read( $root . 'includes/admin/admin-functions.php' );

foreach ( [ 'retention_days', 'form_style', 'show_on_archive', 'show_restock_date', 'ask_quantity', 'ask_phone', 'double_optin', 'optin_expiry_days', 'min_stock_threshold', 'notify_ratio', 'batch_size', 'reserve_hours', 'enable_coupon', 'coupon_type', 'coupon_amount', 'coupon_expiry_days', 'webhook_url', 'webhook_secret', 'webhook_events', 'reminder_delay_days', 'email_log_days' ] as $needle ) {
	if ( false !== strpos( $admin_functions, "'{$needle}'" ) ) {
		$errors[] = "Pro setting '{$needle}' is registered in includes/admin/admin-functions.php.";
	}
}

// 6. The built bundles carry no Pro flow, and still do the Free job.
$admin_bundle = ddwcwl_read( $root . 'assets/js/admin.js' );
$front_bundle = ddwcwl_read( $root . 'assets/js/front.js' );

if ( '' === $admin_bundle || '' === $front_bundle ) {
	$errors[] = 'Built bundles are missing. Run `npm run build`.';
}

foreach ( [ 'ddwcwl_get_form', 'ddwcwl_import_batch', 'ddwcwl-modal', 'reserved', 'restock', 'devdiggers.com', 'console.log', 'document.cookie', 'localStorage' ] as $needle ) {
	foreach ( [ 'assets/js/admin.js' => $admin_bundle, 'assets/js/front.js' => $front_bundle ] as $bundle => $contents ) {
		if ( false !== strpos( $contents, $needle ) ) {
			$errors[] = "Pro identifier '{$needle}' is present in {$bundle}.";
		}
	}
}

foreach ( [ 'ddwcwl_join_waiting_list', 'ddwcwl_leave_waiting_list', 'found_variation' ] as $needle ) {
	if ( '' !== $front_bundle && false === strpos( $front_bundle, $needle ) ) {
		$errors[] = "Free behaviour '{$needle}' is missing from assets/js/front.js.";
	}
}

// 7. Every built asset maps to a webpack entry.
$webpack = ddwcwl_read( $root . 'webpack.config.js' );

foreach ( [ 'assets/js', 'assets/css' ] as $dir ) {
	foreach ( (array) glob( $root . $dir . '/*' ) as $path ) {
		$name = basename( $path );

		if ( 'index.php' === $name ) {
			continue;
		}

		if ( false === strpos( $webpack, '"' . pathinfo( $name, PATHINFO_FILENAME ) . '"' ) ) {
			$errors[] = "{$dir}/{$name} has no webpack entry.";
		}
	}
}

// 8. The Free bootstrap yields to Pro and loads the framework on its own pages.
$bootstrap = ddwcwl_read( $root . 'functions.php' );

foreach ( [ "class_exists( 'DDWCWL_Init' )", "'ddwcwl' !== \$prefix", 'DDWCWL_Free_Init' ] as $needle ) {
	if ( false === strpos( $bootstrap, $needle ) ) {
		$errors[] = "Bootstrap guard '{$needle}' is missing from functions.php.";
	}
}

// 9. No constants or upgrade routine a first release has no use for. Asset versions come
// from filemtime(), and the Pro link is written where it is used.
foreach ( $php as $relative => $contents ) {
	foreach ( [ 'DDWCWL_VERSION', 'DDWCWL_PRO_URL', 'maybe_upgrade', '_ddwcwl_db_version', 'migrate_legacy_meta', 'utm_source' ] as $needle ) {
		if ( false !== strpos( $contents, $needle ) ) {
			$errors[] = "Unneeded '{$needle}' is present in {$relative}.";
		}
	}
}

if ( $errors ) {
	echo "Free/Pro boundary check FAILED:\n";

	foreach ( $errors as $error ) {
		echo ' - ' . $error . "\n";
	}

	exit( 1 );
}

echo "Free/Pro boundary check passed.\n";
