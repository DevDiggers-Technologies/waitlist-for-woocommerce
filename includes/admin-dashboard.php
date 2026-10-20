<?php
/**
 * Admin dashboard shell, built on the DevDiggers framework.
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Includes;

use DDWCWaitingList\Helper\Error\DDWCWL_Error_Helper;
use DDWCWaitingList\Includes\Admin\DDWCWL_Admin_Functions;
use DDWCWaitingList\Templates\Admin;
use DDWCWaitingList\Templates\Admin\Configuration;
use DevDiggers\Framework\Includes\DDFW_Assets;
use DevDiggers\Framework\Includes\DDFW_Layout;
use DevDiggers\Framework\Includes\DDFW_Plugin_Dashboard;
use DevDiggers\Framework\Includes\DDFW_SVG;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_Admin_Dashboard' ) ) {
	/**
	 * Admin dashboard class.
	 */
	class DDWCWL_Admin_Dashboard {
		use DDWCWL_Error_Helper;

		/**
		 * Dashboard instance.
		 *
		 * @var DDFW_Plugin_Dashboard
		 */
		protected $dashboard;

		/**
		 * Construct.
		 */
		public function __construct() {
			add_filter( 'set-screen-option', [ $this, 'ddwcwl_set_options' ], 10, 3 );
			add_action( 'admin_enqueue_scripts', [ $this, 'ddwcwl_enqueue_admin_scripts' ] );
			add_filter( 'admin_footer_text', [ $this, 'ddwcwl_set_admin_footer_text' ], 99 );

			$this->register_dashboard();
		}

		/**
		 * Plugin icon, shared by the header, the setup wizard logo and its welcome screen.
		 *
		 * @param int    $size  Size in pixels.
		 * @param string $css_class Optional class.
		 * @return string
		 */
		public static function get_plugin_icon( $size = 32, $css_class = '' ) {
			return '<svg' . ( $css_class ? ' class="' . esc_attr( $css_class ) . '"' : '' ) . ' width="' . absint( $size ) . '" height="' . absint( $size ) . '" viewBox="0 0 30 30" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M10 19.5V14.25a5 5 0 0 1 10 0v5.25l1.5 1.5h-13l1.5-1.5Z" fill="none" stroke="var(--ddfw-primary-color)" stroke-width="1.2" stroke-linejoin="round"/><path d="M13.25 22.75a1.75 1.75 0 0 0 3.5 0" stroke="var(--ddfw-primary-color)" stroke-width="1.2" stroke-linecap="round"/><path d="M15 7.5v1.25" stroke="var(--ddfw-primary-color)" stroke-width="1.2" stroke-linecap="round"/><circle cx="20.5" cy="9.5" r="2.5" fill="var(--ddfw-primary-color)"/></svg>';
		}

		/**
		 * Email screens: settings key => [ tab slug, label, icon ].
		 *
		 * @return array
		 */
		public static function get_email_tabs() {
			return [
				'back_in_stock' => [ 'back-in-stock', esc_html__( 'Back in Stock', 'devdiggers-waitlist-for-woocommerce' ), 'mail-check' ],
				'subscribed'    => [ 'subscribed', esc_html__( 'Signup Confirmation', 'devdiggers-waitlist-for-woocommerce' ), 'user-check' ],
				'admin_signup'  => [ 'admin-signup', esc_html__( 'Admin Alert', 'devdiggers-waitlist-for-woocommerce' ), 'store' ],
			];
		}

		/**
		 * Register the dashboard with the framework.
		 *
		 * @return void
		 */
		protected function register_dashboard() {
			$tab_icon = function ( $icon ) {
				return DDFW_SVG::get_svg_icon( $icon, true, [ 'size' => 18 ] );
			};

			$email_tabs = [];

			foreach ( self::get_email_tabs() as $type => $tab ) {
				$email_tabs[ $tab[0] ] = [
					'label'    => $tab[1],
					'icon'     => $tab_icon( $tab[2] ),
					'callback' => function () use ( $type ) {
						new Admin\Emails\DDWCWL_Email_Configuration_Template( $type );
					},
				];
			}

			$email_tabs['reminder'] = [
				'label'    => esc_html__( 'Reminder', 'devdiggers-waitlist-for-woocommerce' ),
				'icon'     => $tab_icon( 'bell-ring' ),
				'callback' => [ $this, 'ddwcwl_get_reminder_email_template' ],
			];
			$email_tabs['optin']    = [
				'label'    => esc_html__( 'Email Confirmation', 'devdiggers-waitlist-for-woocommerce' ),
				'icon'     => $tab_icon( 'mail-question' ),
				'callback' => [ $this, 'ddwcwl_get_optin_email_template' ],
			];
			$email_tabs['settings'] = [
				'label'    => esc_html__( 'Email Settings', 'devdiggers-waitlist-for-woocommerce' ),
				'icon'     => $tab_icon( 'palette' ),
				'callback' => function () {
					new Admin\Emails\DDWCWL_Email_Settings_Template();
				},
			];
			$email_tabs['log']      = [
				'label'    => esc_html__( 'Email Log', 'devdiggers-waitlist-for-woocommerce' ),
				'icon'     => $tab_icon( 'log' ),
				'callback' => [ $this, 'ddwcwl_get_email_log' ],
			];

			$config_tabs = [
				'general'      => [ esc_html__( 'General', 'devdiggers-waitlist-for-woocommerce' ), 'general', Configuration\DDWCWL_General_Configuration_Template::class ],
				'form'         => [ esc_html__( 'Signup Form', 'devdiggers-waitlist-for-woocommerce' ), 'layout', Configuration\DDWCWL_Display_Configuration_Template::class ],
				'restock'      => [ esc_html__( 'Restock Rules', 'devdiggers-waitlist-for-woocommerce' ), 'restock', [ $this, 'ddwcwl_get_restock_configuration_template' ] ],
				'coupons'      => [ esc_html__( 'Coupons', 'devdiggers-waitlist-for-woocommerce' ), 'coupon', [ $this, 'ddwcwl_get_coupons_configuration_template' ] ],
				'integrations' => [ esc_html__( 'Integrations', 'devdiggers-waitlist-for-woocommerce' ), 'webhook', [ $this, 'ddwcwl_get_integrations_configuration_template' ] ],
				'shortcodes'   => [ esc_html__( 'Shortcodes', 'devdiggers-waitlist-for-woocommerce' ), 'shortcodes', Configuration\DDWCWL_Shortcodes_Configuration_Template::class ],
			];

			foreach ( $config_tabs as $key => $tab ) {
				$callback            = $tab[2];
				$config_tabs[ $key ] = [
					'label'    => $tab[0],
					'icon'     => $tab_icon( $tab[1] ),
					'callback' => is_array( $callback ) ? $callback : function () use ( $callback ) {
						new $callback();
					},
				];
			}

			$this->dashboard = new DDFW_Plugin_Dashboard(
				[
					'page_title'              => esc_html__( 'Waitlist', 'devdiggers-waitlist-for-woocommerce' ),
					'menu_title'              => esc_html__( 'Waitlist', 'devdiggers-waitlist-for-woocommerce' ),
					'slug'                    => DDWCWL_DASHBOARD_PAGE,
					'plugin_name'             => self::get_plugin_icon() . esc_html__( 'Waitlist', 'devdiggers-waitlist-for-woocommerce' ),
					'upgrade_url'             => 'https://devdiggers.com/product/woocommerce-waitlist/',
					'capability'              => DDWCWL_Admin_Functions::ddwcwl_get_capability(),
					'screen_options_callback' => [ $this, 'ddwcwl_add_screen_options' ],
					'menus'                   => [
						'dashboard'     => [
							'label'    => esc_html__( 'Dashboard', 'devdiggers-waitlist-for-woocommerce' ),
							'layout'   => 'full-width',
							'callback' => [ $this, 'ddwcwl_get_dashboard_template' ],
						],
						'subscribers'   => [
							'label'    => esc_html__( 'Subscribers', 'devdiggers-waitlist-for-woocommerce' ),
							'layout'   => 'full-width',
							'callback' => [ $this, 'ddwcwl_get_subscribers_list' ],
						],
						'products'      => [
							'label'    => esc_html__( 'Products', 'devdiggers-waitlist-for-woocommerce' ),
							'layout'   => 'full-width',
							'callback' => [ $this, 'ddwcwl_get_products_list' ],
						],
						'emails'        => [
							'label'  => esc_html__( 'Emails', 'devdiggers-waitlist-for-woocommerce' ),
							'layout' => 'sidebar',
							'tabs'   => $email_tabs,
						],
						'configuration' => [
							'label'  => esc_html__( 'Configuration', 'devdiggers-waitlist-for-woocommerce' ),
							'layout' => 'sidebar',
							'tabs'   => $config_tabs,
						],
					],
				]
			);
		}

		/**
		 * Screenshot for an upgrade card, or an empty string when none is bundled.
		 *
		 * An empty string makes the card render on its own, so a screenshot can be dropped
		 * into assets/images/pro/ later without a code change.
		 *
		 * @param string $name File name without the extension.
		 * @return string
		 */
		public static function ddwcwl_get_pro_image( $name ) {
			$file = 'assets/images/pro/' . sanitize_file_name( $name ) . '.webp';

			return file_exists( DDWCWL_PLUGIN_FILE . $file ) ? DDWCWL_PLUGIN_URL . $file : '';
		}

		/**
		 * Upgrade card arguments, shared by full-page and in-section cards.
		 *
		 * @param string $image    Screenshot name.
		 * @param string $heading  Heading.
		 * @param string $text     Description.
		 * @param array  $features Feature bullets.
		 * @return array
		 */
		public static function ddwcwl_get_upgrade_args( $image, $heading, $text, $features ) {
			return [
				'image_url'     => self::ddwcwl_get_pro_image( $image ),
				'heading'       => $heading,
				'description'   => $text,
				'list_features' => $features,
				'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-waitlist/',
			];
		}

		/**
		 * A Pro-only tab: a section header with the upgrade card under it.
		 *
		 * @param string $title Section heading.
		 * @param string $intro Section description.
		 * @param array  $card  Card arguments from ddwcwl_get_upgrade_args().
		 * @return void
		 */
		protected function ddwcwl_pro_tab_card( $title, $intro, $card ) {
			( new DDFW_Layout() )->get_form_section_layout(
				[
					[
						'header'            => [
							'heading'     => $title,
							'description' => $intro,
						],
						'after_header_html' => ddfw_get_upgrade_to_pro_section( $card ),
					],
				]
			);
		}

		/**
		 * Email Log tab: a Pro feature.
		 *
		 * @return void
		 */
		public function ddwcwl_get_email_log() {
			$this->ddwcwl_pro_tab_card(
				esc_html__( 'Email Log', 'devdiggers-waitlist-for-woocommerce' ),
				esc_html__( 'Every waiting list email the store sent, so you can confirm a customer was emailed or spot mail server problems early.', 'devdiggers-waitlist-for-woocommerce' ),
				self::ddwcwl_get_upgrade_args(
					'email-log',
					esc_html__( 'Prove every restock email went out', 'devdiggers-waitlist-for-woocommerce' ),
					esc_html__( 'Pro records each waiting list email with its recipient, subject and delivery result, so a "I never got the email" ticket takes seconds to answer.', 'devdiggers-waitlist-for-woocommerce' ),
					[
						esc_html__( 'Sent and failed emails, filterable by type', 'devdiggers-waitlist-for-woocommerce' ),
						esc_html__( 'Search by customer email or subject', 'devdiggers-waitlist-for-woocommerce' ),
						esc_html__( 'Automatic cleanup after the number of days you choose', 'devdiggers-waitlist-for-woocommerce' ),
					]
				)
			);
		}

		/**
		 * Reminder email tab: a Pro feature.
		 *
		 * @return void
		 */
		public function ddwcwl_get_reminder_email_template() {
			$this->ddwcwl_pro_tab_card(
				esc_html__( 'Reminder', 'devdiggers-waitlist-for-woocommerce' ),
				esc_html__( 'A second nudge for customers who were told the product is back but have not bought yet.', 'devdiggers-waitlist-for-woocommerce' ),
				self::ddwcwl_get_upgrade_args(
					'reminder-email',
					esc_html__( 'Win back customers who forgot to buy', 'devdiggers-waitlist-for-woocommerce' ),
					esc_html__( 'Pro emails notified customers again a few days later while the product is still in stock, and stops the moment they order.', 'devdiggers-waitlist-for-woocommerce' ),
					[
						esc_html__( 'Send after the number of days you choose', 'devdiggers-waitlist-for-woocommerce' ),
						esc_html__( 'Skipped for customers who already bought or when stock ran out', 'devdiggers-waitlist-for-woocommerce' ),
						esc_html__( 'Fully editable subject, heading and content', 'devdiggers-waitlist-for-woocommerce' ),
					]
				)
			);
		}

		/**
		 * Email confirmation (double opt-in) tab: a Pro feature.
		 *
		 * @return void
		 */
		public function ddwcwl_get_optin_email_template() {
			$this->ddwcwl_pro_tab_card(
				esc_html__( 'Email Confirmation', 'devdiggers-waitlist-for-woocommerce' ),
				esc_html__( 'Ask guests to confirm their address before they join the waiting list.', 'devdiggers-waitlist-for-woocommerce' ),
				self::ddwcwl_get_upgrade_args(
					'double-optin',
					esc_html__( 'Keep your list clean with double opt-in', 'devdiggers-waitlist-for-woocommerce' ),
					esc_html__( 'Pro sends guests a confirmation link and only saves their place once they click it, so mistyped and fake addresses never reach your list.', 'devdiggers-waitlist-for-woocommerce' ),
					[
						esc_html__( 'Logged-in customers skip the step automatically', 'devdiggers-waitlist-for-woocommerce' ),
						esc_html__( 'Unconfirmed signups removed after the days you set', 'devdiggers-waitlist-for-woocommerce' ),
						esc_html__( 'Editable confirmation email', 'devdiggers-waitlist-for-woocommerce' ),
					]
				)
			);
		}

		/**
		 * Restock Rules tab: the Free switch plus the Pro rules.
		 *
		 * @return void
		 */
		public function ddwcwl_get_restock_configuration_template() {
			new Configuration\DDWCWL_Restock_Configuration_Template();
		}

		/**
		 * Coupons tab: a Pro feature.
		 *
		 * @return void
		 */
		public function ddwcwl_get_coupons_configuration_template() {
			$this->ddwcwl_pro_tab_card(
				esc_html__( 'Coupons', 'devdiggers-waitlist-for-woocommerce' ),
				esc_html__( 'Thank customers for waiting with a discount that is theirs alone.', 'devdiggers-waitlist-for-woocommerce' ),
				self::ddwcwl_get_upgrade_args(
					'coupons',
					esc_html__( 'Turn restock emails into orders with a unique coupon', 'devdiggers-waitlist-for-woocommerce' ),
					esc_html__( 'Pro adds a single-use coupon to every back-in-stock email, locked to that customer and product, so it cannot be shared or reused.', 'devdiggers-waitlist-for-woocommerce' ),
					[
						esc_html__( 'Percentage or fixed amount discount', 'devdiggers-waitlist-for-woocommerce' ),
						esc_html__( 'Expires after the number of days you choose', 'devdiggers-waitlist-for-woocommerce' ),
						esc_html__( 'Shown as a highlighted code in the email', 'devdiggers-waitlist-for-woocommerce' ),
					]
				)
			);
		}

		/**
		 * Integrations tab: a Pro feature.
		 *
		 * @return void
		 */
		public function ddwcwl_get_integrations_configuration_template() {
			$this->ddwcwl_pro_tab_card(
				esc_html__( 'Integrations', 'devdiggers-waitlist-for-woocommerce' ),
				esc_html__( 'Send waiting list events to the tools you already use.', 'devdiggers-waitlist-for-woocommerce' ),
				self::ddwcwl_get_upgrade_args(
					'integrations',
					esc_html__( 'Sync every signup to Zapier, Make, n8n or your CRM', 'devdiggers-waitlist-for-woocommerce' ),
					esc_html__( 'Pro posts a signed webhook each time a customer joins, is notified, buys or leaves, so your email and SMS tools stay in sync.', 'devdiggers-waitlist-for-woocommerce' ),
					[
						esc_html__( 'Choose which events are sent', 'devdiggers-waitlist-for-woocommerce' ),
						esc_html__( 'HMAC-SHA256 signature with your own secret', 'devdiggers-waitlist-for-woocommerce' ),
						esc_html__( 'Customer, product and order details in every payload', 'devdiggers-waitlist-for-woocommerce' ),
					]
				)
			);
		}

		/**
		 * Dashboard menu.
		 *
		 * @return void
		 */
		public function ddwcwl_get_dashboard_template() {
			$this->print_action_notice();
			new Admin\Dashboard\DDWCWL_Dashboard_Template();
		}

		/**
		 * Print the result notice of a list action.
		 *
		 * @return void
		 */
		protected function print_action_notice() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only result flags set by our own redirect.
			$message = isset( $_GET['ddwcwl_message'] ) ? sanitize_key( wp_unslash( $_GET['ddwcwl_message'] ) ) : '';
			$done    = isset( $_GET['ddwcwl_done'] ) ? absint( $_GET['ddwcwl_done'] ) : 0;
			// phpcs:enable WordPress.Security.NonceVerification.Recommended

			if ( ! $message ) {
				return;
			}

			$messages = [
				/* translators: %d: number of subscribers. */
				'notify'      => _n( '%d customer was emailed.', '%d customers were emailed.', $done, 'devdiggers-waitlist-for-woocommerce' ),
				/* translators: %d: number of subscribers. */
				'reset'       => _n( '%d subscriber moved back to waiting.', '%d subscribers moved back to waiting.', $done, 'devdiggers-waitlist-for-woocommerce' ),
				/* translators: %d: number of subscribers. */
				'delete'      => _n( '%d subscriber deleted.', '%d subscribers deleted.', $done, 'devdiggers-waitlist-for-woocommerce' ),
				/* translators: %d: number of products. */
				'notify_all'  => _n( 'Restock emails queued for %d product. They are sent in the background.', 'Restock emails queued for %d products. They are sent in the background.', $done, 'devdiggers-waitlist-for-woocommerce' ),
				/* translators: %d: number of subscribers. */
				'clear'       => _n( '%d waiting list entry removed.', '%d waiting list entries removed.', $done, 'devdiggers-waitlist-for-woocommerce' ),
				'add_added'   => __( 'Subscriber added to the waiting list.', 'devdiggers-waitlist-for-woocommerce' ),
				'add_exists'  => __( 'This email is already waiting for that product.', 'devdiggers-waitlist-for-woocommerce' ),
				'add_invalid' => __( 'Please choose a product and enter a valid email address.', 'devdiggers-waitlist-for-woocommerce' ),
			];

			if ( empty( $messages[ $message ] ) ) {
				return;
			}

			$is_error = in_array( $message, [ 'add_exists', 'add_invalid' ], true ) || ( 'notify_all' === $message && ! $done );
			$text     = 'notify_all' === $message && ! $done ? __( 'Nothing queued. Restock emails are only sent for in-stock products that have waiting customers.', 'devdiggers-waitlist-for-woocommerce' ) : sprintf( $messages[ $message ], $done );

			$this->ddwcwl_print_notification( $text, $is_error ? 'error' : 'success' );
		}

		/**
		 * Subscribers menu.
		 *
		 * @return void
		 */
		public function ddwcwl_get_subscribers_list() {
			$list = new Admin\Subscribers\DDWCWL_Subscribers_List_Template();
			$list->prepare_items();

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters carried into the list form.
			$filters = array_intersect_key( wp_unslash( $_GET ), array_flip( [ 'status', 'product_id' ] ) );
			?>
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Subscribers', 'devdiggers-waitlist-for-woocommerce' ); ?></h1>
			<a href="#ddwcwl-add-subscriber" class="page-title-action button-primary button ddwcwl-toggle-add"><?php esc_html_e( 'Add Subscriber', 'devdiggers-waitlist-for-woocommerce' ); ?></a>
			<span class="ddfw-upgrade-to-pro-tag-wrapper ddwcwl-locked-action"><button type="button" class="page-title-action button" disabled><?php esc_html_e( 'Import CSV', 'devdiggers-waitlist-for-woocommerce' ); ?></button><?php echo wp_kses_post( ddfw_get_pro_tag() ); ?></span>
			<span class="ddfw-upgrade-to-pro-tag-wrapper ddwcwl-locked-action"><button type="button" class="page-title-action button" disabled><?php esc_html_e( 'Export CSV', 'devdiggers-waitlist-for-woocommerce' ); ?></button><?php echo wp_kses_post( ddfw_get_pro_tag() ); ?></span>
			<hr class="wp-header-end" />

			<?php $this->print_action_notice(); ?>

			<form method="post" id="ddwcwl-add-subscriber" class="ddwcwl-add-subscriber" hidden>
				<input type="hidden" name="page" value="<?php echo esc_attr( DDWCWL_DASHBOARD_PAGE ); ?>" />
				<input type="hidden" name="ddwcwl_action" value="add_subscriber" />
				<?php wp_nonce_field( 'ddwcwl_list_action', 'ddwcwl_nonce' ); ?>
				<label class="screen-reader-text" for="ddwcwl-add-email"><?php esc_html_e( 'Email', 'devdiggers-waitlist-for-woocommerce' ); ?></label>
				<input type="email" id="ddwcwl-add-email" name="ddwcwl_email" placeholder="<?php esc_attr_e( 'customer@example.com', 'devdiggers-waitlist-for-woocommerce' ); ?>" required />
				<label class="screen-reader-text" for="ddwcwl-add-product"><?php esc_html_e( 'Product', 'devdiggers-waitlist-for-woocommerce' ); ?></label>
				<select id="ddwcwl-add-product" name="ddwcwl_product" class="ddfw-products" data-placeholder="<?php esc_attr_e( 'Search for a product or variation', 'devdiggers-waitlist-for-woocommerce' ); ?>" required></select>
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Add to Waiting List', 'devdiggers-waitlist-for-woocommerce' ); ?></button>
			</form>

			<?php $list->views(); ?>
			<form method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( DDWCWL_DASHBOARD_PAGE ); ?>" />
				<input type="hidden" name="menu" value="subscribers" />
				<?php
				foreach ( [ 'status', 'product_id' ] as $key ) {
					if ( ! empty( $filters[ $key ] ) ) {
						printf( '<input type="hidden" name="%1$s" value="%2$s" />', esc_attr( $key ), esc_attr( sanitize_text_field( $filters[ $key ] ) ) );
					}
				}

				wp_nonce_field( 'ddwcwl_list_action', 'ddwcwl_nonce', false );
				$list->search_box( esc_html__( 'Search', 'devdiggers-waitlist-for-woocommerce' ), 'ddwcwl-search' );
				$list->display();
				?>
			</form>
			<?php
		}

		/**
		 * Products menu.
		 *
		 * @return void
		 */
		public function ddwcwl_get_products_list() {
			$list = new Admin\Products\DDWCWL_Products_List_Template();
			$list->prepare_items();
			?>
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Products With Waiting Customers', 'devdiggers-waitlist-for-woocommerce' ); ?></h1>
			<hr class="wp-header-end" />

			<?php $this->print_action_notice(); ?>

			<form method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( DDWCWL_DASHBOARD_PAGE ); ?>" />
				<input type="hidden" name="menu" value="products" />
				<?php
				wp_nonce_field( 'ddwcwl_list_action', 'ddwcwl_nonce', false );
				$list->search_box( esc_html__( 'Search Products', 'devdiggers-waitlist-for-woocommerce' ), 'ddwcwl-search' );
				$list->display();
				?>
			</form>
			<?php
		}

		/**
		 * Screen options for the list menus.
		 *
		 * @return void
		 */
		public function ddwcwl_add_screen_options() {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing parameter.
			$menu = ! empty( $_GET['menu'] ) ? sanitize_key( wp_unslash( $_GET['menu'] ) ) : 'dashboard';

			if ( 'subscribers' === $menu ) {
				add_screen_option(
					'per_page',
					[
						'label'   => esc_html__( 'Subscribers per page', 'devdiggers-waitlist-for-woocommerce' ),
						'default' => 20,
						'option'  => 'ddwcwl_subscribers_per_page',
					]
				);
			} elseif ( 'products' === $menu ) {
				add_screen_option(
					'per_page',
					[
						'label'   => esc_html__( 'Products per page', 'devdiggers-waitlist-for-woocommerce' ),
						'default' => 20,
						'option'  => 'ddwcwl_products_per_page',
					]
				);
			}
		}

		/**
		 * Save screen options.
		 *
		 * @param mixed  $status Status.
		 * @param string $option Option.
		 * @param mixed  $value  Value.
		 * @return mixed
		 */
		public function ddwcwl_set_options( $status, $option, $value ) {
			return in_array( $option, [ 'ddwcwl_subscribers_per_page', 'ddwcwl_products_per_page' ], true ) ? absint( $value ) : $status;
		}

		/**
		 * Enqueue admin assets on the plugin pages only.
		 *
		 * @return void
		 */
		public function ddwcwl_enqueue_admin_scripts() {
			if ( ! $this->dashboard || ! $this->dashboard->is_a_plugin_page() ) {
				return;
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing parameter.
			$menu = ! empty( $_GET['menu'] ) ? sanitize_key( wp_unslash( $_GET['menu'] ) ) : 'dashboard';

			wp_enqueue_style( 'ddwcwl-admin-style', DDWCWL_PLUGIN_URL . 'assets/css/admin.css', [ DDFW_Assets::$framework_css_handle ], filemtime( DDWCWL_PLUGIN_FILE . 'assets/css/admin.css' ) );

			if ( 'dashboard' !== $menu ) {
				wp_enqueue_script( 'ddwcwl-admin-script', DDWCWL_PLUGIN_URL . 'assets/js/admin.js', [ 'jquery', DDFW_Assets::$framework_js_handle ], filemtime( DDWCWL_PLUGIN_FILE . 'assets/js/admin.js' ), true );
				wp_localize_script(
					'ddwcwl-admin-script',
					'ddwcwlAdminObj',
					[
						'confirmDelete' => esc_html__( 'Delete the selected waiting list entries? This cannot be undone.', 'devdiggers-waitlist-for-woocommerce' ),
						'confirmNotify' => esc_html__( 'Email the selected customers now?', 'devdiggers-waitlist-for-woocommerce' ),
						'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
						'previewNonce'  => wp_create_nonce( 'ddwcwl_email_preview' ),
						'previewTitle'  => esc_html__( 'Email preview', 'devdiggers-waitlist-for-woocommerce' ),
						'close'         => esc_html__( 'Close', 'devdiggers-waitlist-for-woocommerce' ),
						'sending'       => esc_html__( 'Sending...', 'devdiggers-waitlist-for-woocommerce' ),
						'error'         => esc_html__( 'Something went wrong. Please try again.', 'devdiggers-waitlist-for-woocommerce' ),
					]
				);
			}
		}

		/**
		 * Footer review text on plugin pages.
		 *
		 * @param string $footer_text Footer text.
		 * @return string
		 */
		public function ddwcwl_set_admin_footer_text( $footer_text ) {
			if ( ! $this->dashboard || ! $this->dashboard->is_a_plugin_page() ) {
				return $footer_text;
			}

			return sprintf(
				/* translators: 1: plugin name, 2: five-star rating link. */
				esc_html__( 'Thank you for using %1$s. If you like it, please leave us a %2$s rating. It means a lot to us!', 'devdiggers-waitlist-for-woocommerce' ),
				'<strong>' . esc_html__( 'DevDiggers Waitlist for WooCommerce', 'devdiggers-waitlist-for-woocommerce' ) . '</strong>',
				'<a href="https://wordpress.org/support/plugin/devdiggers-waitlist-for-woocommerce/reviews/#new-post" target="_blank" aria-label="' . esc_attr__( 'five star', 'devdiggers-waitlist-for-woocommerce' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
			);
		}
	}
}
