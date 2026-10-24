<?php
/**
 * File handler
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Includes;

use DDWCWaitingList\Includes\Admin;
use DDWCWaitingList\Includes\Front;
use DDWCWaitingList\Includes\Common;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_File_Handler' ) ) {
	/**
	 * File handler class
	 */
	class DDWCWL_File_Handler {
		/**
		 * Construct
		 */
		public function __construct() {
			$this->ddwcwl_set_globals();

			new Common\DDWCWL_Common_Hooks();

			if ( is_admin() ) {
				new DDWCWL_Admin_Dashboard();
				new Admin\DDWCWL_Setup_Wizard();
				new Admin\DDWCWL_Admin_Hooks();
			}

			if ( self::ddwcwl_is_active() ) {
				new Front\DDWCWL_Front_Hooks();
				new Front\DDWCWL_Front_Ajax_Hooks();
			} else {
				global $ddwcwl_waiting_list;

				// Switched off: the shortcode still has to be registered, or pages print its raw tag.
				add_shortcode( ( new Front\DDWCWL_Front_Functions() )->ddwcwl_validate_shortcode( $ddwcwl_waiting_list['waiting_list_template_shortcode'] ), '__return_empty_string' );
			}
		}

		/**
		 * Whether the storefront features should run.
		 *
		 * @return bool
		 */
		public static function ddwcwl_is_active() {
			global $ddwcwl_waiting_list;

			return 'yes' === $ddwcwl_waiting_list['enabled'];
		}

		/**
		 * Default configuration values, keyed by option name without the `_ddwcwl_` prefix.
		 *
		 * @return array
		 */
		public static function ddwcwl_get_defaults() {
			return [
				// General.
				'enabled'                         => 'yes',
				'require_login'                   => '',
				'include_backorders'              => '',
				'excluded_products'               => [],
				'excluded_categories'             => [],
				'waiting_list_endpoint'           => 'waitlist',
				// Signup form.
				'form_title'                      => esc_html__( 'Notify me when it is back', 'devdiggers-waitlist-for-woocommerce' ),
				'form_description'                => esc_html__( 'This item is sold out right now. We will let you know the moment it is back.', 'devdiggers-waitlist-for-woocommerce' ),
				'form_note'                       => esc_html__( 'One email when it is back. No spam, unsubscribe anytime.', 'devdiggers-waitlist-for-woocommerce' ),
				'join_button_text'                => esc_html__( 'Notify Me', 'devdiggers-waitlist-for-woocommerce' ),
				'email_placeholder'               => esc_html__( 'Your email address', 'devdiggers-waitlist-for-woocommerce' ),
				'leave_button_text'               => esc_html__( 'Leave waiting list', 'devdiggers-waitlist-for-woocommerce' ),
				'joined_title'                    => esc_html__( 'You are on the list', 'devdiggers-waitlist-for-woocommerce' ),
				/* translators: {position} is replaced with the customer's place in line. */
				'joined_message'                  => esc_html__( 'You are number {position} in line. We will email you the moment it is back.', 'devdiggers-waitlist-for-woocommerce' ),
				'show_form_icon'                  => 'yes',
				'show_subscriber_count'           => 'yes',
				'show_queue_position'             => 'yes',
				'button_bg_color'                 => '#0256ff',
				'button_text_color'               => '#ffffff',
				'use_custom_notices'              => '',
				'notice_bg_color'                 => '#ffffff',
				'notice_color'                    => '#000000',
				'notice_font_size'                => '16px',
				'notice_border_radius'            => '6px',
				// Privacy.
				'privacy_consent'                 => '',
				'privacy_consent_text'            => esc_html__( 'I agree to receive an email when this product is back in stock.', 'devdiggers-waitlist-for-woocommerce' ),
				// Restock.
				'mail_automatically'              => 'yes',
				// Emails.
				'email_button_text'               => esc_html__( 'Shop Now', 'devdiggers-waitlist-for-woocommerce' ),
				'email_button_color'              => '#0256ff',
				'email_settings'                  => self::ddwcwl_get_email_defaults(),
				// Shortcodes.
				'waiting_list_template_shortcode' => '[ddwcwl_waiting_list_template_shortcode]',
			];
		}

		/**
		 * Default content of every waiting list email, keyed by email type.
		 *
		 * @return array
		 */
		public static function ddwcwl_get_email_defaults() {
			return [
				'back_in_stock' => [
					'enabled' => 'yes',
					'subject' => esc_html__( 'Good news! {product_name} is back in stock', 'devdiggers-waitlist-for-woocommerce' ),
					'heading' => esc_html__( 'It is back in stock', 'devdiggers-waitlist-for-woocommerce' ),
					'content' => '<p>' . esc_html__( 'Hi {customer_name},', 'devdiggers-waitlist-for-woocommerce' ) . '</p><p>' . esc_html__( 'Great news! {product_name} is back in stock and ready to order. You asked us to let you know, so you are among the first to hear.', 'devdiggers-waitlist-for-woocommerce' ) . '</p><p>' . esc_html__( 'Stock is limited and other customers are waiting too, so grab yours before it sells out again.', 'devdiggers-waitlist-for-woocommerce' ) . '</p><p>' . esc_html__( 'Thanks for waiting,', 'devdiggers-waitlist-for-woocommerce' ) . '<br />{site_title}</p>',
				],
				'subscribed'    => [
					'enabled' => 'yes',
					'subject' => esc_html__( 'You are on the waiting list for {product_name}', 'devdiggers-waitlist-for-woocommerce' ),
					'heading' => esc_html__( 'You are on the list', 'devdiggers-waitlist-for-woocommerce' ),
					'content' => '<p>' . esc_html__( 'Hi {customer_name},', 'devdiggers-waitlist-for-woocommerce' ) . '</p><p>' . esc_html__( 'Thanks for your interest in {product_name}. You are number {queue_position} in line, and we will email you as soon as it is back in stock. No need to keep checking.', 'devdiggers-waitlist-for-woocommerce' ) . '</p><p>' . esc_html__( 'Thank you,', 'devdiggers-waitlist-for-woocommerce' ) . '<br />{site_title}</p>',
				],
				'admin_signup'  => [
					'enabled'   => '',
					'recipient' => '',
					'subject'   => esc_html__( '[{site_title}] New waiting list signup for {product_name}', 'devdiggers-waitlist-for-woocommerce' ),
					'heading'   => esc_html__( 'New waiting list signup', 'devdiggers-waitlist-for-woocommerce' ),
					'content'   => '<p>' . esc_html__( '{customer_email} joined the waiting list for {product_name}. {waiting_count} customers are now waiting for this product.', 'devdiggers-waitlist-for-woocommerce' ) . '</p>',
				],
			];
		}

		/**
		 * Load configuration into the `$ddwcwl_waiting_list` global.
		 *
		 * @return void
		 */
		public function ddwcwl_set_globals() {
			global $ddwcwl_waiting_list;

			$ddwcwl_waiting_list = [];

			foreach ( self::ddwcwl_get_defaults() as $key => $default ) {
				$value = get_option( '_ddwcwl_' . $key, $default );

				// Empty text settings fall back to defaults; checkboxes keep their empty "off" value.
				if ( '' === $value && ! is_array( $default ) && ! in_array( $default, [ '', 'yes' ], true ) ) {
					$value = $default;
				}

				if ( 'email_settings' === $key ) {
					// Merge per email, so a field added in a later version always has a default.
					$saved = is_array( $value ) ? $value : [];

					foreach ( $default as $type => $fields ) {
						$default[ $type ] = array_merge(
							$fields,
							array_filter(
								(array) ( $saved[ $type ] ?? [] ),
								function ( $field_value, $field ) {
									// An emptied text falls back to its default, an unticked switch stays off.
									return is_string( $field_value ) && ( '' !== $field_value || in_array( $field, [ 'enabled', 'recipient' ], true ) );
								},
								ARRAY_FILTER_USE_BOTH
							)
						);
					}

					$value = $default;
				} elseif ( is_array( $default ) ) {
					$value = array_values( array_filter( array_map( 'absint', (array) $value ) ) );
				}

				$ddwcwl_waiting_list[ $key ] = $value;
			}
		}
	}
}
