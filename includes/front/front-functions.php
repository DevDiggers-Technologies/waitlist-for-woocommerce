<?php
/**
 * Front functions
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Includes\Front;

use DDWCWaitingList\Helper\Products\DDWCWL_Products_Helper;
use DDWCWaitingList\Helper\Subscribers\DDWCWL_Subscribers_Helper;
use DDWCWaitingList\Templates\Front;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_Front_Functions' ) ) {
	/**
	 * Front functions class
	 */
	class DDWCWL_Front_Functions {
		use \DDWCWaitingList\Helper\Error\DDWCWL_Error_Helper;

		/**
		 * Whether the product page form was already printed, so block and classic hooks never both print it.
		 *
		 * @var bool
		 */
		protected $rendered = false;

		/**
		 * Customer facing messages keyed by result code.
		 *
		 * @param string $code Result code.
		 * @return string
		 */
		public static function ddwcwl_get_message( $code ) {
			$messages = [
				'added'          => esc_html__( 'You are on the list! We will email you as soon as this product is back in stock.', 'devdiggers-waitlist-for-woocommerce' ),
				'exists'         => esc_html__( 'You are already on the waiting list for this product.', 'devdiggers-waitlist-for-woocommerce' ),
				'invalid_email'  => esc_html__( 'Please enter a valid email address.', 'devdiggers-waitlist-for-woocommerce' ),
				'consent'        => esc_html__( 'Please accept the consent checkbox to join the waiting list.', 'devdiggers-waitlist-for-woocommerce' ),
				'login_required' => esc_html__( 'Please log in to join the waiting list.', 'devdiggers-waitlist-for-woocommerce' ),
				'unavailable'    => esc_html__( 'This product does not accept waiting list signups right now.', 'devdiggers-waitlist-for-woocommerce' ),
				'left'           => esc_html__( 'You have left the waiting list.', 'devdiggers-waitlist-for-woocommerce' ),
				'unsubscribed'   => esc_html__( 'You have been unsubscribed from this waiting list.', 'devdiggers-waitlist-for-woocommerce' ),
				'security'       => esc_html__( 'Your session has expired. Please refresh the page and try again.', 'devdiggers-waitlist-for-woocommerce' ),
			];

			/**
			 * Filters a customer facing waiting list message.
			 *
			 * @since 1.0.0
			 *
			 * @param string $message Message.
			 * @param string $code    Result code.
			 */
			return apply_filters( 'ddwcwl_message', $messages[ $code ] ?? '', $code );
		}

		/**
		 * Join a waiting list. Shared by the AJAX endpoint and the no-JS form post.
		 *
		 * @param array $request Unslashed request data.
		 * @return array { success: bool, code: string, title: string, message: string, count: int, position: int }
		 */
		public function ddwcwl_process_join( $request ) {
			global $ddwcwl_waiting_list;

			$product = wc_get_product( absint( $request['ddwcwl_product_id'] ?? 0 ) );
			$helper  = new DDWCWL_Subscribers_Helper();
			$code    = '';
			$id      = 0;

			if ( ! empty( $request['ddwcwl_website'] ) ) {
				// Honeypot filled: a bot. Pretend success, store nothing.
				$code = 'added';
			} elseif ( ! $product || ! DDWCWL_Products_Helper::is_eligible( $product ) ) {
				$code = 'unavailable';
			} elseif ( 'yes' === $ddwcwl_waiting_list['require_login'] && ! is_user_logged_in() ) {
				$code = 'login_required';
			} elseif ( 'yes' === $ddwcwl_waiting_list['privacy_consent'] && empty( $request['ddwcwl_consent'] ) ) {
				$code = 'consent';
			} else {
				$email = is_user_logged_in() ? wp_get_current_user()->user_email : sanitize_email( $request['ddwcwl_user_email'] ?? '' );

				if ( ! is_email( $email ) ) {
					$code = 'invalid_email';
				} else {
					$result = $helper->add( $product->get_id(), $email, get_current_user_id() );
					$id     = $result['id'];
					$code   = 'invalid' === $result['status'] ? 'invalid_email' : $result['status'];
				}
			}

			$row    = $id ? $helper->get( $id ) : null;
			$titles = [
				'added'  => $ddwcwl_waiting_list['joined_title'],
				'exists' => esc_html__( 'You are already on the list', 'devdiggers-waitlist-for-woocommerce' ),
			];

			return [
				'success'  => isset( $titles[ $code ] ),
				'code'     => $code,
				'title'    => $titles[ $code ] ?? '',
				'message'  => self::ddwcwl_get_message( $code ),
				'count'    => $product ? $helper->count_waiting( $product->get_id() ) : 0,
				'position' => $row && 'waiting' === $row->status ? $helper->get_queue_position( $row ) : 0,
			];
		}

		/**
		 * Leave a waiting list as the logged-in customer.
		 *
		 * @param int $product_id Product ID.
		 * @return bool
		 */
		public function ddwcwl_process_leave( $product_id ) {
			if ( ! is_user_logged_in() ) {
				return false;
			}

			$helper = new DDWCWL_Subscribers_Helper();
			$row    = $helper->get_by_product_email( absint( $product_id ), strtolower( wp_get_current_user()->user_email ) );

			return $helper->unsubscribe( $row && 'waiting' === $row->status ? $row : null );
		}

		/**
		 * Add the My Account menu item before logout.
		 *
		 * @param array $items Menu items.
		 * @return array
		 */
		public function ddwcwl_add_woocommerce_menu( $items ) {
			global $ddwcwl_waiting_list;

			$logout = $items['customer-logout'] ?? '';
			unset( $items['customer-logout'] );

			$items[ $ddwcwl_waiting_list['waiting_list_endpoint'] ] = $this->ddwcwl_get_endpoint_title();

			if ( $logout ) {
				$items['customer-logout'] = $logout;
			}

			return $items;
		}

		/**
		 * My Account endpoint title.
		 *
		 * @return string
		 */
		public function ddwcwl_get_endpoint_title() {
			return esc_html__( 'My Waiting List', 'devdiggers-waitlist-for-woocommerce' );
		}

		/**
		 * My Account endpoint content.
		 *
		 * @param string $current_page Endpoint value (page number).
		 * @return void
		 */
		public function ddwcwl_add_waiting_list_content_on_my_account_page( $current_page = '' ) {
			new Front\MyAccount\DDWCWL_Waiting_List_Template( max( 1, absint( $current_page ) ) );
		}

		/**
		 * Render the form on single product pages.
		 *
		 * @return void
		 */
		public function ddwcwl_add_content_in_single_product_page() {
			global $product;

			if ( $this->rendered ) {
				return;
			}

			$this->rendered = true;

			ob_start();
			( new Front\Product\DDWCWL_Join_Waiting_List_Template() )->ddwcwl_get_join_waiting_list_template( $product instanceof \WC_Product ? $product : wc_get_product( get_queried_object_id() ) );
			$html = (string) ob_get_clean();

			// No form (the product is back in stock): still confirm what an email link just did, such as an unsubscribe.
			if ( '' === trim( $html ) ) {
				$html = $this->ddwcwl_get_notice_only_html();
			}

			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped template output.
		}

		/**
		 * The message of a `ddwcwl_notice` redirect, on a product page that shows no form.
		 *
		 * @return string
		 */
		protected function ddwcwl_get_notice_only_html() {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only notice code set by our own redirect.
			$notice  = ! empty( $_GET['ddwcwl_notice'] ) ? sanitize_key( wp_unslash( $_GET['ddwcwl_notice'] ) ) : '';
			$message = $notice ? self::ddwcwl_get_message( $notice ) : '';

			if ( ! $message ) {
				return '';
			}

			wp_enqueue_style( 'ddwcwl-front-style' );

			ob_start();
			echo '<div class="ddwcwl-waiting-list ddwcwl-notice-only">';
			$this->ddwcwl_print_notification( $message, in_array( $notice, [ 'added', 'left', 'unsubscribed' ], true ) ? 'success' : 'error' );
			echo '</div>';

			return (string) ob_get_clean();
		}

		/**
		 * Render the form under the Add to Cart block of block based product templates.
		 *
		 * @param string $content Block HTML.
		 * @return string
		 */
		public function ddwcwl_append_to_add_to_cart_block( $content ) {
			if ( ! is_product() ) {
				return $content;
			}

			ob_start();
			$this->ddwcwl_add_content_in_single_product_page();

			return $content . ob_get_clean();
		}

		/**
		 * Register front assets. Templates enqueue them only where a form is rendered.
		 *
		 * @return void
		 */
		public function ddwcwl_register_front_scripts() {
			wp_register_style( 'ddwcwl-front-style', DDWCWL_PLUGIN_URL . 'assets/css/front.css', [], filemtime( DDWCWL_PLUGIN_FILE . 'assets/css/front.css' ) );
			wp_register_script( 'ddwcwl-front-script', DDWCWL_PLUGIN_URL . 'assets/js/front.js', [ 'jquery' ], filemtime( DDWCWL_PLUGIN_FILE . 'assets/js/front.js' ), true );

			wp_localize_script(
				'ddwcwl-front-script',
				'ddwcwlFrontObj',
				[
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'ddwcwl-nonce' ),
					'i18n'    => [
						/* translators: %s: number of waiting customers. */
						'countSingular' => esc_html__( '%s person is waiting for this product.', 'devdiggers-waitlist-for-woocommerce' ),
						/* translators: %s: number of waiting customers. */
						'countPlural'   => esc_html__( '%s people are waiting for this product.', 'devdiggers-waitlist-for-woocommerce' ),
						'error'         => esc_html__( 'Something went wrong. Please try again.', 'devdiggers-waitlist-for-woocommerce' ),
						'joined'        => esc_html__( 'You are on the list', 'devdiggers-waitlist-for-woocommerce' ),
					],
				]
			);
		}

		/**
		 * Handle the no-JS join/leave form post.
		 *
		 * @return void
		 */
		public function ddwcwl_handle_form_post() {
			// AJAX posts carry the same fields; admin-ajax handles those.
			if ( wp_doing_ajax() || empty( $_POST['ddwcwl_form_action'] ) || empty( $_POST['ddwcwl_nonce'] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ddwcwl_nonce'] ) ), 'ddwcwl-nonce' ) ) {
				$code = 'security';
			} elseif ( 'leave' === sanitize_key( wp_unslash( $_POST['ddwcwl_form_action'] ) ) ) {
				$code = $this->ddwcwl_process_leave( absint( $_POST['ddwcwl_product_id'] ?? 0 ) ) ? 'left' : 'unavailable';
			} else {
				$request = [
					'ddwcwl_product_id' => absint( $_POST['ddwcwl_product_id'] ?? 0 ),
					'ddwcwl_user_email' => sanitize_email( wp_unslash( $_POST['ddwcwl_user_email'] ?? '' ) ),
					'ddwcwl_consent'    => ! empty( $_POST['ddwcwl_consent'] ),
					'ddwcwl_website'    => sanitize_text_field( wp_unslash( $_POST['ddwcwl_website'] ?? '' ) ),
				];
				$code    = $this->ddwcwl_process_join( $request )['code'];
			}

			$redirect = wp_get_raw_referer() ? wp_get_raw_referer() : home_url( '/' );

			wp_safe_redirect( add_query_arg( 'ddwcwl_notice', $code, remove_query_arg( 'ddwcwl_notice', $redirect ) ) );
			exit();
		}

		/**
		 * Handle one-click unsubscribe links from emails.
		 *
		 * @return void
		 */
		public function ddwcwl_handle_email_action() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Email links are authorised by a random per-subscriber token instead of a nonce.
			if ( empty( $_GET['ddwcwl_action'] ) || 'unsubscribe' !== $_GET['ddwcwl_action'] || empty( $_GET['ddwcwl_token'] ) ) {
				return;
			}

			$token = sanitize_text_field( wp_unslash( $_GET['ddwcwl_token'] ) );
			// phpcs:enable WordPress.Security.NonceVerification.Recommended

			$helper     = new DDWCWL_Subscribers_Helper();
			$subscriber = $helper->get_by_token( $token );

			if ( ! $subscriber ) {
				wp_safe_redirect( wc_get_page_permalink( 'shop' ) );
				exit();
			}

			$product = wc_get_product( $subscriber->product_id );
			$url     = $product ? $product->get_permalink() : wc_get_page_permalink( 'shop' );

			$helper->unsubscribe( $subscriber );

			wp_safe_redirect( add_query_arg( 'ddwcwl_notice', 'unsubscribed', $url ) );
			exit();
		}

		/**
		 * After the My Account login or registration form, return to the product whose
		 * "Log in to get notified" button the shopper followed. WooCommerce itself only reads
		 * a posted `redirect` field, so the `redirect_to` on our link would otherwise be lost
		 * and the shopper would land on the account dashboard.
		 *
		 * @param string $redirect Redirect URL chosen by WooCommerce.
		 * @return string
		 */
		public function ddwcwl_login_redirect( $redirect ) {
			$referer = wc_get_raw_referer();
			$query   = $referer ? wp_parse_url( $referer, PHP_URL_QUERY ) : '';

			if ( ! $query ) {
				return $redirect;
			}

			parse_str( $query, $args );

			return empty( $args['redirect_to'] ) || ! is_string( $args['redirect_to'] ) ? $redirect : wp_validate_redirect( $args['redirect_to'], $redirect );
		}

		/**
		 * Sanitize the configured shortcode tag.
		 *
		 * @param string $shortcode Shortcode as saved, with or without brackets.
		 * @return string
		 */
		public function ddwcwl_validate_shortcode( $shortcode ) {
			$tag = sanitize_key( str_replace( [ '[', ']', ' ' ], '', $shortcode ) );

			return $tag ? $tag : 'ddwcwl_waiting_list_template_shortcode';
		}

		/**
		 * Shortcode output. Accepts product_id, defaults to the current product.
		 *
		 * @param array $args Shortcode attributes.
		 * @return string
		 */
		public function ddwcwl_get_waiting_list_template_shortcode_content( $args ) {
			global $product;

			$args           = shortcode_atts( [ 'product_id' => 0 ], $args );
			$target_product = $args['product_id'] ? wc_get_product( absint( $args['product_id'] ) ) : $product;

			if ( ! $target_product instanceof \WC_Product ) {
				return '';
			}

			// Away from its own product page a variable product has no variations form for the waitlist to follow.
			$own_page = is_product() && get_queried_object_id() === ( $target_product->get_parent_id() ? $target_product->get_parent_id() : $target_product->get_id() );
			$context  = $target_product->is_type( 'variable' ) && ! $own_page ? 'shortcode' : 'product';

			ob_start();
			( new Front\Product\DDWCWL_Join_Waiting_List_Template() )->ddwcwl_get_join_waiting_list_template( $target_product, $context );
			return ob_get_clean();
		}
	}
}
