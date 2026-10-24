<?php
/**
 * Admin functions class
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Includes\Admin;

use DDWCWaitingList\Helper\Subscribers\DDWCWL_Subscribers_Helper;
use DDWCWaitingList\Includes\DDWCWL_File_Handler;
use DDWCWaitingList\Includes\DDWCWL_Notifier;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_Admin_Functions' ) ) {
	/**
	 * Admin functions
	 */
	class DDWCWL_Admin_Functions {
		/**
		 * Capability required to manage the waiting list.
		 *
		 * @return string
		 */
		public static function ddwcwl_get_capability() {
			return apply_filters( 'ddwcwl_modify_menu_capability', 'manage_woocommerce' );
		}

		/**
		 * Settings grouped by configuration tab: option key => sanitize type.
		 *
		 * @return array
		 */
		public static function ddwcwl_get_settings_schema() {
			$schema = [
				'ddwcwl-general-configuration-fields'    => [
					'enabled'               => 'checkbox',
					'require_login'         => 'checkbox',
					'include_backorders'    => 'checkbox',
					'excluded_products'     => 'ids',
					'excluded_categories'   => 'ids',
					'waiting_list_endpoint' => 'slug',
				],
				'ddwcwl-display-configuration-fields'    => [
					'form_title'            => 'text',
					'form_description'      => 'text',
					'form_note'             => 'text',
					'join_button_text'      => 'text',
					'email_placeholder'     => 'text',
					'leave_button_text'     => 'text',
					'joined_title'          => 'text',
					'joined_message'        => 'text',
					'show_form_icon'        => 'checkbox',
					'show_subscriber_count' => 'checkbox',
					'show_queue_position'   => 'checkbox',
					'button_bg_color'       => 'color',
					'button_text_color'     => 'color',
					'privacy_consent'       => 'checkbox',
					'privacy_consent_text'  => 'html',
					'use_custom_notices'    => 'checkbox',
					'notice_bg_color'       => 'color',
					'notice_color'          => 'color',
					'notice_font_size'      => 'css_size',
					'notice_border_radius'  => 'css_size',
				],
				'ddwcwl-restock-configuration-fields'    => [
					'mail_automatically' => 'checkbox',
				],
				'ddwcwl-email-settings-fields'           => [
					'email_button_text'  => 'text',
					'email_button_color' => 'color',
				],
				'ddwcwl-shortcodes-configuration-fields' => [
					'waiting_list_template_shortcode' => 'text',
				],
			];

			// Each email screen saves only its own slice of the shared email settings option.
			foreach ( array_keys( DDWCWL_File_Handler::ddwcwl_get_email_defaults() ) as $type ) {
				$schema[ 'ddwcwl-email-' . $type . '-fields' ] = [ 'email_settings' => 'email_settings' ];
			}

			return $schema;
		}

		/**
		 * Register every setting with a type-specific sanitizer.
		 *
		 * @return void
		 */
		public function ddwcwl_register_settings() {
			$sanitizers = [
				'checkbox'       => function ( $value ) {
					return 'yes' === $value ? 'yes' : '';
				},
				'ids'            => function ( $ids ) {
					return array_values( array_filter( array_map( 'absint', (array) $ids ) ) );
				},
				'slug'           => function ( $value ) {
					$value = sanitize_title( $value );
					return $value ? $value : 'waitlist';
				},
				'text'           => 'sanitize_text_field',
				'html'           => 'wp_kses_post',
				'color'          => function ( $value ) {
					return (string) sanitize_hex_color( $value );
				},
				'css_size'       => function ( $value ) {
					return preg_match( '/^\d+(\.\d+)?(px|em|rem|%)$/', trim( (string) $value ) ) ? trim( $value ) : '';
				},
				'email_settings' => [ $this, 'ddwcwl_sanitize_email_settings' ],
			];

			foreach ( self::ddwcwl_get_settings_schema() as $group => $fields ) {
				foreach ( $fields as $key => $type ) {
					register_setting( $group, '_ddwcwl_' . $key, [ 'sanitize_callback' => $sanitizers[ $type ] ] );
				}
			}
		}

		/**
		 * Sanitize the email settings submitted by one email screen and merge them into the saved ones.
		 *
		 * @param array $value Submitted settings, keyed by email type.
		 * @return array
		 */
		public function ddwcwl_sanitize_email_settings( $value ) {
			$saved    = get_option( '_ddwcwl_email_settings', [] );
			$saved    = is_array( $saved ) ? $saved : [];
			$defaults = DDWCWL_File_Handler::ddwcwl_get_email_defaults();

			foreach ( (array) $value as $type => $fields ) {
				if ( ! isset( $defaults[ $type ] ) || ! is_array( $fields ) ) {
					continue;
				}

				$clean = [];

				foreach ( $fields as $field => $field_value ) {
					switch ( $field ) {
						case 'enabled':
							$clean[ $field ] = 'yes' === $field_value ? 'yes' : '';
							break;
						case 'content':
							$clean[ $field ] = wp_kses_post( $field_value );
							break;
						case 'recipient':
							$clean[ $field ] = implode( ', ', array_filter( array_map( 'sanitize_email', explode( ',', (string) $field_value ) ), 'is_email' ) );
							break;
						case 'subject':
						case 'heading':
							$clean[ $field ] = sanitize_text_field( $field_value );
							break;
					}
				}

				$saved[ $type ] = array_merge( $saved[ $type ] ?? [], $clean );
			}

			return $saved;
		}

		/**
		 * Add the Waitlist column after the stock column.
		 *
		 * @param array $columns Columns.
		 * @return array
		 */
		public function ddwcwl_add_product_column( $columns ) {
			$new = [];

			foreach ( $columns as $key => $label ) {
				$new[ $key ] = $label;

				if ( 'is_in_stock' === $key ) {
					$new['ddwcwl_waitlist'] = esc_html__( 'Waitlist', 'devdiggers-waitlist-for-woocommerce' );
				}
			}

			if ( ! isset( $new['ddwcwl_waitlist'] ) ) {
				$new['ddwcwl_waitlist'] = esc_html__( 'Waitlist', 'devdiggers-waitlist-for-woocommerce' );
			}

			return $new;
		}

		/**
		 * Column width on the WooCommerce Products screen.
		 *
		 * @return void
		 */
		public function ddwcwl_product_column_style() {
			$screen = get_current_screen();

			if ( $screen && 'edit-product' === $screen->id ) {
				wp_add_inline_style( 'woocommerce_admin_styles', '.wp-list-table .column-ddwcwl_waitlist{width:70px;text-align:center}' );
			}
		}

		/**
		 * Render the Waitlist column. Counts for the whole screen load in one query.
		 *
		 * @param string $column  Column.
		 * @param int    $post_id Product ID.
		 * @return void
		 */
		public function ddwcwl_render_product_column( $column, $post_id ) {
			static $counts = null;

			if ( 'ddwcwl_waitlist' !== $column ) {
				return;
			}

			if ( null === $counts ) {
				global $wp_query;

				$counts = ( new DDWCWL_Subscribers_Helper() )->count_waiting_by_parents( wp_list_pluck( (array) $wp_query->posts, 'ID' ) );
			}

			$count = absint( $counts[ $post_id ] ?? 0 );

			if ( ! $count ) {
				echo '<span aria-hidden="true">&ndash;</span>';
				return;
			}

			printf(
				'<a href="%1$s"><strong>%2$s</strong></a>',
				esc_url( admin_url( 'admin.php?page=' . DDWCWL_DASHBOARD_PAGE . '&menu=subscribers&status=waiting&product_id=' . $post_id ) ),
				esc_html( number_format_i18n( $count ) )
			);
		}

		/**
		 * Handle subscriber/product list actions (row links and bulk actions), then redirect.
		 *
		 * @return void
		 */
		public function ddwcwl_handle_list_actions() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Routing only; the nonce is verified below before anything changes.
			$page   = isset( $_REQUEST['page'] ) ? sanitize_key( wp_unslash( $_REQUEST['page'] ) ) : '';
			$action = '';

			foreach ( [ 'ddwcwl_action', 'action', 'action2' ] as $param ) {
				if ( ! empty( $_REQUEST[ $param ] ) && '-1' !== $_REQUEST[ $param ] ) {
					$action = sanitize_key( wp_unslash( $_REQUEST[ $param ] ) );
					break;
				}
			}
			// phpcs:enable WordPress.Security.NonceVerification.Recommended

			$actions = [ 'notify', 'reset', 'delete', 'add_subscriber', 'notify_all', 'clear' ];

			if ( DDWCWL_DASHBOARD_PAGE !== $page || ! in_array( $action, $actions, true ) ) {
				return;
			}

			if ( ! current_user_can( self::ddwcwl_get_capability() ) ) {
				wp_die( esc_html__( 'You do not have permission to manage the waiting list.', 'devdiggers-waitlist-for-woocommerce' ) );
			}

			check_admin_referer( 'ddwcwl_list_action', 'ddwcwl_nonce' );

			$helper   = new DDWCWL_Subscribers_Helper();
			$notifier = new DDWCWL_Notifier();
			$done     = 0;
			$message  = $action;

			$ids         = isset( $_REQUEST['subscriber'] ) ? array_map( 'absint', (array) wp_unslash( $_REQUEST['subscriber'] ) ) : [];
			$product_ids = isset( $_REQUEST['product'] ) ? array_map( 'absint', (array) wp_unslash( $_REQUEST['product'] ) ) : [];

			switch ( $action ) {
				case 'notify':
					foreach ( $ids as $id ) {
						$subscriber = $helper->get( $id );
						$product    = $subscriber ? wc_get_product( $subscriber->product_id ) : null;

						// A back in stock email for a product that is still sold out would send the customer to a dead end.
						$done += ( $product && $product->is_in_stock() && $notifier->notify( $subscriber, $product ) ) ? 1 : 0;
					}
					break;

				case 'reset':
					foreach ( $ids as $id ) {
						$done += $helper->update(
							$id,
							[
								'status'      => 'waiting',
								'notified_at' => null,
							]
						) ? 1 : 0;
					}
					break;

				case 'delete':
					$done = $helper->delete( $ids );
					break;

				case 'add_subscriber':
					$email      = isset( $_REQUEST['ddwcwl_email'] ) ? sanitize_email( wp_unslash( $_REQUEST['ddwcwl_email'] ) ) : '';
					$product_id = isset( $_REQUEST['ddwcwl_product'] ) ? absint( $_REQUEST['ddwcwl_product'] ) : 0;
					$user       = get_user_by( 'email', $email );
					$result     = $helper->add( $product_id, $email, $user ? $user->ID : 0 );
					$done       = 'added' === $result['status'] ? 1 : 0;
					$message    = 'add_' . $result['status'];
					break;

				case 'notify_all':
					foreach ( $product_ids as $product_id ) {
						$done += $notifier->maybe_queue( $product_id, true ) ? 1 : 0;
					}
					break;

				case 'clear':
					$done = $helper->delete_by_products( $product_ids );
					break;
			}

			$redirect = remove_query_arg( [ 'ddwcwl_action', 'action', 'action2', 'subscriber', 'product', 'ddwcwl_nonce', '_wp_http_referer', 'ddwcwl_email', 'ddwcwl_product', 'ddwcwl_done', 'ddwcwl_message' ], wp_get_raw_referer() ? wp_get_raw_referer() : admin_url( 'admin.php?page=' . DDWCWL_DASHBOARD_PAGE ) );

			wp_safe_redirect(
				add_query_arg(
					[
						'ddwcwl_message' => $message,
						'ddwcwl_done'    => $done,
					],
					$redirect
				)
			);
			exit();
		}

		/**
		 * Sample subscriber and product for previews and test emails: the newest real
		 * subscriber when there is one, otherwise a made-up row on the latest product.
		 *
		 * @param string $email Recipient for the sample row.
		 * @return array { 0: object, 1: \WC_Product|null }
		 */
		protected function ddwcwl_get_sample_data( $email ) {
			$recent     = ( new DDWCWL_Subscribers_Helper() )->get_recent( 1 );
			$subscriber = $recent ? clone $recent[0] : null;
			$product    = $subscriber ? wc_get_product( $subscriber->product_id ) : null;

			if ( ! $product ) {
				$products = wc_get_products(
					[
						'limit'   => 1,
						'status'  => 'publish',
						'orderby' => 'date',
					]
				);
				$product  = $products ? $products[0] : null;
			}

			if ( ! $subscriber ) {
				$subscriber = (object) [
					'id'         => 0,
					'product_id' => $product ? $product->get_id() : 0,
					'email'      => $email,
					'user_id'    => get_current_user_id(),
					'status'     => 'waiting',
					'token'      => str_repeat( 'x', 32 ),
					'created_at' => current_time( 'mysql' ),
				];
			}

			$subscriber->email   = $email;
			$subscriber->user_id = get_current_user_id();

			return [ $subscriber, $product ];
		}

		/**
		 * Email object for a settings type.
		 *
		 * @param string $type Email type.
		 * @return \DDWCWaitingList\Includes\Emails\DDWCWL_Email_Base|null
		 */
		protected function ddwcwl_get_email( $type ) {
			foreach ( WC()->mailer()->get_emails() as $email ) {
				if ( isset( $email->ddwcwl_type ) && $email->ddwcwl_type === $type ) {
					return $email;
				}
			}

			return null;
		}

		/**
		 * Render an email preview inside the Emails screen iframe.
		 *
		 * @return void
		 */
		public function ddwcwl_preview_email() {
			if ( ! current_user_can( self::ddwcwl_get_capability() ) ) {
				wp_die( esc_html__( 'You do not have permission to preview emails.', 'devdiggers-waitlist-for-woocommerce' ) );
			}

			check_admin_referer( 'ddwcwl_email_preview', 'ddwcwl_nonce' );

			$email = $this->ddwcwl_get_email( isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : '' );

			list( $subscriber, $product ) = $this->ddwcwl_get_sample_data( wp_get_current_user()->user_email );

			if ( ! $email || ! $product ) {
				wp_die( esc_html__( 'Add at least one product to preview waiting list emails.', 'devdiggers-waitlist-for-woocommerce' ) );
			}

			// The template escapes its own output; style_inline() only moves CSS into style attributes.
			echo $email->get_preview_html( $subscriber, $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			exit;
		}

		/**
		 * Send a test copy of an email.
		 *
		 * @return void
		 */
		public function ddwcwl_send_test_email() {
			if ( ! current_user_can( self::ddwcwl_get_capability() ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'You do not have permission to send test emails.', 'devdiggers-waitlist-for-woocommerce' ) ] );
			}

			check_ajax_referer( 'ddwcwl_email_preview', 'nonce' );

			$recipient = isset( $_POST['recipient'] ) ? sanitize_email( wp_unslash( $_POST['recipient'] ) ) : '';
			$email     = $this->ddwcwl_get_email( isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : '' );

			list( $subscriber, $product ) = $this->ddwcwl_get_sample_data( $recipient );

			if ( ! is_email( $recipient ) || ! $email || ! $product ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Enter a valid email address.', 'devdiggers-waitlist-for-woocommerce' ) ] );
			}

			if ( ! $email->send_test( $subscriber, $product, $recipient ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'The mail server did not accept the email. Check your SMTP settings, then try again.', 'devdiggers-waitlist-for-woocommerce' ) ] );
			}

			/* translators: %s: recipient email. */
			wp_send_json_success( [ 'message' => sprintf( esc_html__( 'Test email sent to %s.', 'devdiggers-waitlist-for-woocommerce' ), $recipient ) ] );
		}
	}
}
