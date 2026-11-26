<?php
/**
 * Plugin Name: DevDiggers Waitlist for WooCommerce
 * Description: Turn sold-out products into sales. Shoppers join a back-in-stock waitlist on any simple or variable product, see their place in line, and get an email the moment stock returns.
 * Plugin URI: https://devdiggers.com/product/woocommerce-waitlist/
 * Author: DevDiggers
 * Author URI: https://devdiggers.com/
 * Version: 1.0.0
 * Text Domain: devdiggers-waitlist-for-woocommerce
 * Domain Path: /i18n
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * WC requires at least: 8.0
 * WC tested up to: 11.2
 * DevDiggersPrefix: ddwcwl
 * Requires Plugins: woocommerce
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package DevDiggers Waitlist for WooCommerce
 */

// ddwcwl: DevDiggers Waitlist for WooCommerce.

use DDWCWaitingList\Includes\DDWCWL_File_Handler;

defined( 'ABSPATH' ) || exit();

/**
 * Point the plugin constants at this directory.
 *
 * @return void
 */
function ddwcwl_free_define_constants() {
	defined( 'DDWCWL_PLUGIN_FILE' ) || define( 'DDWCWL_PLUGIN_FILE', plugin_dir_path( __FILE__ ) );
	defined( 'DDWCWL_PLUGIN_URL' ) || define( 'DDWCWL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	defined( 'DDWCWL_PLUGIN_SLUG' ) || define( 'DDWCWL_PLUGIN_SLUG', 'devdiggers-waitlist-for-woocommerce' );
	defined( 'DDWCWL_DASHBOARD_PAGE' ) || define( 'DDWCWL_DASHBOARD_PAGE', 'ddwcwl-dashboard' );
}

if ( ! class_exists( 'DDWCWL_Free_Init' ) ) {
	/**
	 * Free Init class.
	 */
	final class DDWCWL_Free_Init {
		/**
		 * The single instance of this class.
		 *
		 * @var DDWCWL_Free_Init|null
		 */
		private static $instance = null;

		/**
		 * Class constructor.
		 */
		public function __construct() {
			add_action( 'init', [ $this, 'ddwcwl_init' ], 0 );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'ddwcwl_plugin_settings_link' ] );
			add_filter( 'plugin_row_meta', [ $this, 'ddwcwl_plugin_row_meta' ], 10, 2 );
		}

		/**
		 * Create a plugin instance.
		 *
		 * @return static
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();

				/**
				 * Fires when the main plugin instance is loaded.
				 *
				 * @since 1.0.0
				 */
				do_action( 'ddwcwl_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Init function.
		 *
		 * @return void
		 */
		public function ddwcwl_init() {
			// WordPress.org loads plugin translations automatically.
			if ( ! class_exists( 'WooCommerce' ) ) {
				add_action(
					'admin_notices',
					function () {
						?>
						<div class="notice notice-error">
							<p>
								<?php
								/* translators: %1$s: opening link tag, %2$s: closing link tag */
								printf( esc_html__( 'DevDiggers Waitlist for WooCommerce is activated but not effective. It requires %1$sWooCommerce%2$s in order to work.', 'devdiggers-waitlist-for-woocommerce' ), '<a href="' . esc_url( 'https://wordpress.org/plugins/woocommerce/' ) . '" target="_blank">', '</a>' );
								?>
							</p>
						</div>
						<?php
					}
				);

				return;
			}

			require_once DDWCWL_PLUGIN_FILE . 'autoload/autoload.php';
			new DDWCWL_File_Handler();

			// Only on the plugin's own screens (plus AJAX so the dismiss button works).
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing input.
			$on_plugin_page = is_admin() && isset( $_GET['page'] ) && DDWCWL_DASHBOARD_PAGE === sanitize_key( wp_unslash( $_GET['page'] ) );

			if ( ( $on_plugin_page || wp_doing_ajax() ) && class_exists( '\DevDiggers\Framework\Includes\DDFW_Review_Notice' ) ) {
				new \DevDiggers\Framework\Includes\DDFW_Review_Notice(
					[
						'plugin_name'   => esc_html__( 'DevDiggers Waitlist for WooCommerce', 'devdiggers-waitlist-for-woocommerce' ),
						'plugin_prefix' => 'ddwcwl',
						'review_url'    => 'https://wordpress.org/support/plugin/devdiggers-waitlist-for-woocommerce/reviews/#new-post',
					]
				);
			}
		}

		/**
		 * Plugin settings link.
		 *
		 * @param array $links Links array.
		 * @return array
		 */
		public function ddwcwl_plugin_settings_link( $links ) {
			$base = admin_url( 'admin.php?page=' . DDWCWL_DASHBOARD_PAGE );

			ob_start();
			?>
			<a href="<?php echo esc_url( $base ); ?>"><?php esc_html_e( 'Dashboard', 'devdiggers-waitlist-for-woocommerce' ); ?></a>
			|
			<a href="<?php echo esc_url( $base . '&menu=configuration' ); ?>"><?php esc_html_e( 'Configuration', 'devdiggers-waitlist-for-woocommerce' ); ?></a>
			|
			<a href="<?php echo esc_url( $base . '&setup-wizard=true' ); ?>"><?php esc_html_e( 'Setup Wizard', 'devdiggers-waitlist-for-woocommerce' ); ?></a>
			|
			<a href="<?php echo esc_url( 'https://devdiggers.com/product/woocommerce-waitlist/' ); ?>" style="color: #0256ff; font-weight: bold;" target="_blank"><?php esc_html_e( 'Upgrade to Pro', 'devdiggers-waitlist-for-woocommerce' ); ?></a>
			<?php
			array_unshift( $links, ob_get_clean() );

			return $links;
		}

		/**
		 * Plugin row meta links.
		 *
		 * @param array  $links Links.
		 * @param string $file  Plugin file.
		 * @return array
		 */
		public function ddwcwl_plugin_row_meta( $links, $file ) {
			if ( plugin_basename( __FILE__ ) === $file ) {
				$row_meta = [
					'support'       => '<a href="https://devdiggers.com/contact/" aria-label="' . esc_attr__( 'Support', 'devdiggers-waitlist-for-woocommerce' ) . '">' . esc_html__( 'Support', 'devdiggers-waitlist-for-woocommerce' ) . '</a>',
					'documentation' => '<a href="https://docs.devdiggers.com/woocommerce-waitlist/" aria-label="' . esc_attr__( 'Documentation', 'devdiggers-waitlist-for-woocommerce' ) . '">' . esc_html__( 'Documentation', 'devdiggers-waitlist-for-woocommerce' ) . '</a>',
					'review'        => '<a href="https://wordpress.org/support/plugin/devdiggers-waitlist-for-woocommerce/reviews/#new-post" target="_blank" title="' . esc_attr__( 'Review', 'devdiggers-waitlist-for-woocommerce' ) . '" aria-label="' . esc_attr__( 'Review', 'devdiggers-waitlist-for-woocommerce' ) . '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 32" height="10"><path d="M16 26.534L6.111 32 8 20.422l-8-8.2 11.056-1.688L16 0l4.944 10.534L32 12.223l-8 8.2L25.889 32zm40 0L46.111 32 48 20.422l-8-8.2 11.056-1.688L56 0l4.944 10.534L72 12.223l-8 8.2L65.889 32zm40 0L86.111 32 88 20.422l-8-8.2 11.056-1.688L96 0l4.944 10.534L112 12.223l-8 8.2L105.889 32zm40 0L126.111 32 128 20.422l-8-8.2 11.056-1.688L136 0l4.944 10.534L152 12.223l-8 8.2L145.889 32zm40 0L166.111 32 168 20.422l-8-8.2 11.056-1.688L176 0l4.944 10.534L192 12.223l-8 8.2L185.889 32z" fill="#F5A623" fill-rule="evenodd"/></svg></a>',
				];

				$links = array_merge( $links, $row_meta );
			}

			return $links;
		}
	}
}

// Step aside when the premium edition of this plugin is active.
add_action(
	'plugins_loaded',
	function () {
		if ( class_exists( 'DDWCWL_Init' ) ) {
			return;
		}

		ddwcwl_free_define_constants();

		DDWCWL_Free_Init::get_instance();

		// Load DevDiggers Framework if not loaded already.
		if ( ! defined( 'DDFW_LOADED' ) && file_exists( DDWCWL_PLUGIN_FILE . 'devdiggers-framework/init.php' ) ) {
			$should_load = true;

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing input.
			if ( ! empty( $_GET['page'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing input.
				$current_page = sanitize_text_field( wp_unslash( $_GET['page'] ) );
				$prefix       = explode( '-', $current_page )[0];

				if ( 0 === strpos( $prefix, 'ddwc' ) || 0 === strpos( $prefix, 'ddwp' ) ) {
					$pro_class  = strtoupper( $prefix ) . '_Init';
					$free_class = strtoupper( $prefix ) . '_Free_Init';

					// Yield to a sibling DevDiggers plugin except on this plugin's own pages.
					if ( class_exists( $free_class ) && ! class_exists( $pro_class ) && 'ddwcwl' !== $prefix ) {
						$should_load = false;
					}
				}
			}

			if ( $should_load ) {
				require DDWCWL_PLUGIN_FILE . 'devdiggers-framework/init.php';
			}
		}
	},
	10
);

register_activation_hook(
	__FILE__,
	function () {
		require_once __DIR__ . '/includes/install.php';
		\DDWCWaitingList\Includes\DDWCWL_Install::install();

		// The review notice counts its delay from here.
		add_option( 'ddwcwl_installed_at', time() );

		// A store that already ran this plugin (or Pro) keeps its settings and skips the wizard.
		$existing_store = false !== get_option( '_ddwcwl_enabled', false );

		// Saved, not only defaulted, so the My Account URL never changes between Free and Pro.
		// Stores that ran Pro before keep its original default slug.
		add_option( '_ddwcwl_waiting_list_endpoint', $existing_store ? 'ddwc-waiting-list' : 'waitlist' );

		if ( $existing_store ) {
			update_option( 'ddfw_setup_wizard_completed_devdiggers-waitlist-for-woocommerce', true );
			return;
		}

		set_transient( 'ddfw_activation_redirect_devdiggers-waitlist-for-woocommerce', true, 30 );
	}
);

register_deactivation_hook(
	__FILE__,
	function () {
		// By group name, not a class method: the autoloader may not be loaded when this runs.
		if ( function_exists( 'as_unschedule_all_actions' ) ) {
			as_unschedule_all_actions( '', [], 'ddwcwl' );
		}
	}
);

// HPOS and block checkout compatibility.
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
		}
	}
);
