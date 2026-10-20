<?php
/**
 * Shared base for waiting list emails. Subject, heading, content and on/off switch are
 * managed on the plugin's Emails screen; WooCommerce still owns the layout and email type.
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Includes\Emails;

use DDWCWaitingList\Helper\Subscribers\DDWCWL_Subscribers_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_Email_Base' ) ) {
	/**
	 * Email base class.
	 */
	abstract class DDWCWL_Email_Base extends \WC_Email {
		/**
		 * Settings key on the Emails screen, e.g. "back_in_stock".
		 *
		 * @var string
		 */
		public $ddwcwl_type = '';

		/**
		 * Subscriber row.
		 *
		 * @var object|null
		 */
		public $subscriber = null;

		/**
		 * Whether the current send is a test from the Emails screen.
		 *
		 * @var bool
		 */
		public $is_test = false;

		/**
		 * Construct.
		 */
		public function __construct() {
			$this->template_html  = 'emails/waiting-list.php';
			$this->template_plain = 'emails/plain/waiting-list.php';
			$this->template_base  = DDWCWL_PLUGIN_FILE . 'templates/';
			$this->placeholders   = [
				'{customer_name}'  => '',
				'{customer_email}' => '',
				'{product_name}'   => '',
				'{product_link}'   => '',
				'{product_price}'  => '',
				'{queue_position}' => '',
				'{waiting_count}'  => '',
			];

			parent::__construct();
		}

		/**
		 * One field of this email's settings.
		 *
		 * @param string $key enabled, subject, heading, content, recipient.
		 * @return string
		 */
		public function get_setting( $key ) {
			global $ddwcwl_waiting_list;

			return (string) ( $ddwcwl_waiting_list['email_settings'][ $this->ddwcwl_type ][ $key ] ?? '' );
		}

		/**
		 * WooCommerce settings: only the email format, the rest lives on the plugin Emails screen.
		 *
		 * @return void
		 */
		public function init_form_fields() {
			parent::init_form_fields();

			$this->form_fields = [
				'ddwcwl_notice' => [
					'title'       => esc_html__( 'Content', 'devdiggers-waitlist-for-woocommerce' ),
					'type'        => 'title',
					/* translators: %s: link to the plugin Emails screen. */
					'description' => sprintf( esc_html__( 'Turn this email on or off and edit its subject, heading and content under %s.', 'devdiggers-waitlist-for-woocommerce' ), '<a href="' . esc_url( admin_url( 'admin.php?page=' . DDWCWL_DASHBOARD_PAGE . '&menu=emails&tab=' . str_replace( '_', '-', $this->ddwcwl_type ) ) ) . '">' . esc_html__( 'Waiting List > Emails', 'devdiggers-waitlist-for-woocommerce' ) . '</a>' ),
				],
				'email_type'    => $this->form_fields['email_type'],
			];
		}

		/**
		 * Enabled on the plugin Emails screen.
		 *
		 * @return bool
		 */
		public function is_enabled() {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce core email filter.
			return (bool) apply_filters( 'woocommerce_email_enabled_' . $this->id, 'yes' === $this->get_setting( 'enabled' ), $this->object, $this );
		}

		/**
		 * Subject.
		 *
		 * @return string
		 */
		public function get_subject() {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce core email filter.
			return apply_filters( 'woocommerce_email_subject_' . $this->id, $this->format_string( $this->get_setting( 'subject' ) ), $this->object, $this );
		}

		/**
		 * Heading.
		 *
		 * @return string
		 */
		public function get_heading() {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce core email filter.
			return apply_filters( 'woocommerce_email_heading_' . $this->id, $this->format_string( $this->get_setting( 'heading' ) ), $this->object, $this );
		}

		/**
		 * Body content with placeholders replaced.
		 *
		 * @return string
		 */
		public function get_body() {
			return wpautop( wptexturize( $this->format_string( $this->get_setting( 'content' ) ) ) );
		}

		/**
		 * Call to action button, empty for none.
		 *
		 * @return array { text: string, url: string }
		 */
		public function get_button() {
			return [
				'text' => '',
				'url'  => '',
			];
		}

		/**
		 * Small print link under the email.
		 *
		 * @return array { text: string, url: string }
		 */
		public function get_footer_link() {
			return [
				'text' => esc_html__( 'Unsubscribe', 'devdiggers-waitlist-for-woocommerce' ),
				'url'  => DDWCWL_Subscribers_Helper::get_unsubscribe_url( $this->subscriber ),
			];
		}

		/**
		 * Prepare object, recipient and placeholders.
		 *
		 * @param object      $subscriber Subscriber row.
		 * @param \WC_Product $product    Product.
		 * @return void
		 */
		protected function setup( $subscriber, $product ) {
			$this->setup_locale();

			$helper = new DDWCWL_Subscribers_Helper();
			$user   = ! empty( $subscriber->user_id ) ? get_userdata( $subscriber->user_id ) : false;

			$this->subscriber = $subscriber;
			$this->object     = $product;
			$this->recipient  = $subscriber->email;

			$this->placeholders = array_merge(
				$this->placeholders,
				[
					'{customer_name}'  => $user ? ( $user->first_name ? $user->first_name : $user->display_name ) : esc_html__( 'there', 'devdiggers-waitlist-for-woocommerce' ),
					'{customer_email}' => $subscriber->email,
					'{product_name}'   => wp_strip_all_tags( $product->get_name() ),
					'{product_link}'   => $product->get_permalink(),
					'{product_price}'  => wp_strip_all_tags( wc_price( wc_get_price_to_display( $product ) ) ),
					'{queue_position}' => number_format_i18n( max( 1, $helper->get_queue_position( $subscriber ) ) ),
					'{waiting_count}'  => number_format_i18n( $helper->count_waiting( $product->get_id() ) ),
				]
			);
		}

		/**
		 * Send, then restore locale.
		 *
		 * @return bool Whether the mail was accepted.
		 */
		protected function send_now() {
			$sent = false;

			if ( ( $this->is_test || $this->is_enabled() ) && $this->get_recipient() ) {
				$sent = $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}

			$this->restore_locale();

			return (bool) $sent;
		}

		/**
		 * Send to the subscriber.
		 *
		 * @param object      $subscriber Subscriber row.
		 * @param \WC_Product $product    Product.
		 * @return bool
		 */
		public function trigger( $subscriber, $product ) {
			if ( empty( $subscriber ) || ! $product instanceof \WC_Product ) {
				return false;
			}

			$this->setup( $subscriber, $product );

			return $this->send_now();
		}

		/**
		 * Send a test copy with sample data, even when the email is switched off.
		 *
		 * @param object      $subscriber Sample subscriber.
		 * @param \WC_Product $product    Sample product.
		 * @param string      $recipient  Test recipient.
		 * @return bool
		 */
		public function send_test( $subscriber, $product, $recipient ) {
			$this->is_test = true;
			$this->setup( $subscriber, $product );
			$this->recipient = $recipient;
			$sent            = $this->send_now();
			$this->is_test   = false;

			return $sent;
		}

		/**
		 * Inline-styled HTML for the preview frame.
		 *
		 * @param object      $subscriber Sample subscriber.
		 * @param \WC_Product $product    Sample product.
		 * @return string
		 */
		public function get_preview_html( $subscriber, $product ) {
			$this->setup( $subscriber, $product );
			$html = $this->style_inline( $this->get_content_html() );
			$this->restore_locale();

			return $html;
		}

		/**
		 * Template arguments shared by HTML and plain versions.
		 *
		 * @param bool $plain_text Plain text.
		 * @return array
		 */
		protected function get_template_args( $plain_text ) {
			global $ddwcwl_waiting_list;

			return [
				'email_heading' => $this->get_heading(),
				'body'          => $this->get_body(),
				'button'        => $this->get_button(),
				'footer_link'   => $this->get_footer_link(),
				'accent_color'  => sanitize_hex_color( $ddwcwl_waiting_list['email_button_color'] ) ? sanitize_hex_color( $ddwcwl_waiting_list['email_button_color'] ) : '#0256ff',
				'subscriber'    => $this->subscriber,
				'product'       => $this->object,
				'sent_to_admin' => ! $this->is_customer_email(),
				'plain_text'    => $plain_text,
				'email'         => $this,
			];
		}

		/**
		 * HTML content.
		 *
		 * @return string
		 */
		public function get_content_html() {
			return wc_get_template_html( $this->template_html, $this->get_template_args( false ), 'ddwc-waiting-list/', $this->template_base );
		}

		/**
		 * Plain content.
		 *
		 * @return string
		 */
		public function get_content_plain() {
			return wc_get_template_html( $this->template_plain, $this->get_template_args( true ), 'ddwc-waiting-list/', $this->template_base );
		}
	}
}
