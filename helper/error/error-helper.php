<?php
/**
 * Notice printing trait.
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Helper\Error;

defined( 'ABSPATH' ) || exit();

if ( ! trait_exists( 'DDWCWL_Error_Helper' ) ) {
	/**
	 * Notice printing trait.
	 */
	trait DDWCWL_Error_Helper {
		/**
		 * Print a notice: admin notice in wp-admin, WooCommerce or custom styled notice on the storefront.
		 *
		 * @param string $message    Message.
		 * @param string $error_type success|error|notice|info|warning.
		 * @return void
		 */
		public function ddwcwl_print_notification( $message, $error_type = 'success' ) {
			if ( is_admin() && ! wp_doing_ajax() ) {
				?>
				<div class="notice notice-<?php echo esc_attr( $error_type ); ?> is-dismissible">
					<p><?php echo esc_html( $message ); ?></p>
				</div>
				<?php
				return;
			}

			global $ddwcwl_waiting_list;

			if ( 'yes' === $ddwcwl_waiting_list['use_custom_notices'] ) {
				?>
				<div class="ddwcwl-custom-notice ddwcwl-custom-notice-<?php echo esc_attr( $error_type ); ?>" role="status" style="<?php echo esc_attr( self::ddwcwl_get_custom_notice_style() ); ?>">
					<?php echo esc_html( $message ); ?>
				</div>
				<?php
				return;
			}

			wc_print_notice( esc_html( $message ), 'success' === $error_type ? 'success' : ( 'error' === $error_type ? 'error' : 'notice' ) );
		}

		/**
		 * Inline style for custom notices.
		 *
		 * @return string
		 */
		public static function ddwcwl_get_custom_notice_style() {
			global $ddwcwl_waiting_list;

			return sprintf(
				'background-color:%1$s;color:%2$s;font-size:%3$s;border-radius:%4$s;',
				sanitize_hex_color( $ddwcwl_waiting_list['notice_bg_color'] ),
				sanitize_hex_color( $ddwcwl_waiting_list['notice_color'] ),
				preg_replace( '/[^0-9a-z.%]/i', '', $ddwcwl_waiting_list['notice_font_size'] ),
				preg_replace( '/[^0-9a-z.%]/i', '', $ddwcwl_waiting_list['notice_border_radius'] )
			);
		}
	}
}
