<?php
/**
 * One email screen: switch, subject, heading, content with placeholders, preview and test send.
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Templates\Admin\Emails;

use DDWCWaitingList\Includes\DDWCWL_File_Handler;
use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_Email_Configuration_Template' ) ) {
	/**
	 * Email configuration template class.
	 */
	class DDWCWL_Email_Configuration_Template {
		/**
		 * Construct.
		 *
		 * @param string $type Email type.
		 */
		public function __construct( $type ) {
			global $ddwcwl_waiting_list;

			$defaults = DDWCWL_File_Handler::ddwcwl_get_email_defaults();

			if ( ! isset( $defaults[ $type ] ) ) {
				return;
			}

			$settings = $ddwcwl_waiting_list['email_settings'][ $type ];
			$info     = $this->get_info( $type );
			$name     = '_ddwcwl_email_settings[' . $type . ']';
			$fields   = [
				[
					'type'           => 'checkbox',
					'label'          => esc_html__( 'Enable/Disable', 'devdiggers-waitlist-for-woocommerce' ),
					'checkbox_label' => $info['toggle'],
					'name'           => $name . '[enabled]',
					'id'             => 'ddwcwl-email-' . $type . '-enabled',
					'value'          => $settings['enabled'],
				],
			];

			if ( 'admin_signup' === $type ) {
				$fields[] = [
					'type'        => 'text',
					'label'       => esc_html__( 'Recipients', 'devdiggers-waitlist-for-woocommerce' ),
					/* translators: %s: site admin email. */
					'description' => sprintf( esc_html__( 'Comma separated. Leave empty to use the site admin email (%s).', 'devdiggers-waitlist-for-woocommerce' ), get_option( 'admin_email' ) ),
					'name'        => $name . '[recipient]',
					'id'          => 'ddwcwl-email-' . $type . '-recipient',
					'value'       => $settings['recipient'],
					'placeholder' => get_option( 'admin_email' ),
				];
			}

			$fields = array_merge(
				$fields,
				[
					[
						'type'        => 'text',
						'label'       => esc_html__( 'Subject', 'devdiggers-waitlist-for-woocommerce' ),
						'description' => esc_html__( 'Leave empty to restore the default.', 'devdiggers-waitlist-for-woocommerce' ),
						'name'        => $name . '[subject]',
						'id'          => 'ddwcwl-email-' . $type . '-subject',
						'value'       => $settings['subject'],
						'placeholder' => $defaults[ $type ]['subject'],
						'input_class' => [ 'large-text', 'ddwcwl-placeholder-target' ],
					],
					[
						'type'        => 'text',
						'label'       => esc_html__( 'Heading', 'devdiggers-waitlist-for-woocommerce' ),
						'description' => esc_html__( 'The large title at the top of the email.', 'devdiggers-waitlist-for-woocommerce' ),
						'name'        => $name . '[heading]',
						'id'          => 'ddwcwl-email-' . $type . '-heading',
						'value'       => $settings['heading'],
						'placeholder' => $defaults[ $type ]['heading'],
						'input_class' => [ 'large-text', 'ddwcwl-placeholder-target' ],
					],
					[
						'type'          => 'editor',
						'label'         => esc_html__( 'Content', 'devdiggers-waitlist-for-woocommerce' ),
						'description'   => $info['content'],
						'name'          => $name . '[content]',
						'id'            => 'ddwcwl_email_' . $type . '_content',
						'value'         => $settings['content'],
						'textarea_rows' => 9,
						'media_buttons' => false,
					],
					[
						'type'  => 'field_html',
						'label' => esc_html__( 'Placeholders', 'devdiggers-waitlist-for-woocommerce' ),
						'html'  => $this->get_placeholders_html( $info['placeholders'] ),
					],
				]
			);

			$args = [
				[
					'header'            => [
						'heading'     => $info['title'],
						'description' => $info['description'],
					],
					'after_header_html' => $this->get_toolbar_html( $type ),
					'fields'            => $fields,
				],
			];

			( new DDFW_Layout() )->get_form_section_layout( $args, 'ddwcwl-email-' . $type . '-fields' );
		}

		/**
		 * Copy for each email.
		 *
		 * @param string $type Email type.
		 * @return array
		 */
		protected function get_info( $type ) {
			$common = [ '{customer_name}', '{customer_email}', '{product_name}', '{product_price}', '{product_link}', '{site_title}' ];
			$info   = [
				'back_in_stock' => [
					'title'        => esc_html__( 'Back in Stock', 'devdiggers-waitlist-for-woocommerce' ),
					'description'  => esc_html__( 'The email that recovers sales. Sent to waiting customers, first come first served, when the product is back. A product card and a shop button are added below your content.', 'devdiggers-waitlist-for-woocommerce' ),
					'toggle'       => esc_html__( 'Email waiting customers when the product is back in stock', 'devdiggers-waitlist-for-woocommerce' ),
					'content'      => esc_html__( 'Main message of the email. The product card and shop button are added automatically.', 'devdiggers-waitlist-for-woocommerce' ),
					'placeholders' => array_merge( $common, [ '{waiting_count}' ] ),
				],
				'subscribed'    => [
					'title'        => esc_html__( 'Signup Confirmation', 'devdiggers-waitlist-for-woocommerce' ),
					'description'  => esc_html__( 'Sent right after a customer joins, so they know their spot is saved and do not have to keep checking. Includes a one-click leave link.', 'devdiggers-waitlist-for-woocommerce' ),
					'toggle'       => esc_html__( 'Confirm every new signup by email', 'devdiggers-waitlist-for-woocommerce' ),
					'content'      => esc_html__( 'The product card and leave link are added automatically.', 'devdiggers-waitlist-for-woocommerce' ),
					'placeholders' => array_merge( $common, [ '{queue_position}', '{waiting_count}' ] ),
				],
				'admin_signup'  => [
					'title'        => esc_html__( 'Admin Alert', 'devdiggers-waitlist-for-woocommerce' ),
					'description'  => esc_html__( 'Tells your team about every new signup with the current demand, handy for deciding what to reorder. Off by default because busy stores get many signups.', 'devdiggers-waitlist-for-woocommerce' ),
					'toggle'       => esc_html__( 'Email the store team about every new signup', 'devdiggers-waitlist-for-woocommerce' ),
					'content'      => esc_html__( 'The product card and a link to its subscribers are added automatically.', 'devdiggers-waitlist-for-woocommerce' ),
					'placeholders' => array_merge( $common, [ '{waiting_count}' ] ),
				],
			];

			return $info[ $type ];
		}

		/**
		 * Clickable placeholder list.
		 *
		 * @param string[] $placeholders Placeholders.
		 * @return string
		 */
		protected function get_placeholders_html( $placeholders ) {
			$html = '<div class="ddwcwl-placeholders">';

			foreach ( $placeholders as $placeholder ) {
				$html .= '<button type="button" class="ddwcwl-placeholder" data-placeholder="' . esc_attr( $placeholder ) . '">' . esc_html( $placeholder ) . '</button>';
			}

			return $html . '</div><p class="description">' . esc_html__( 'Click a placeholder to insert it where your cursor is, in the subject, heading or content.', 'devdiggers-waitlist-for-woocommerce' ) . '</p>';
		}

		/**
		 * Preview and test toolbar.
		 *
		 * @param string $type Email type.
		 * @return string
		 */
		protected function get_toolbar_html( $type ) {
			$preview_url = wp_nonce_url( admin_url( 'admin-post.php?action=ddwcwl_preview_email&type=' . $type ), 'ddwcwl_email_preview', 'ddwcwl_nonce' );

			ob_start();
			?>
			<div class="ddwcwl-email-toolbar" data-type="<?php echo esc_attr( $type ); ?>">
				<button type="button" class="button ddwcwl-preview-email" data-url="<?php echo esc_url( $preview_url ); ?>"><?php esc_html_e( 'Preview', 'devdiggers-waitlist-for-woocommerce' ); ?></button>
				<span class="ddwcwl-test-email">
					<label class="screen-reader-text" for="ddwcwl-test-<?php echo esc_attr( $type ); ?>"><?php esc_html_e( 'Test recipient', 'devdiggers-waitlist-for-woocommerce' ); ?></label>
					<input type="email" id="ddwcwl-test-<?php echo esc_attr( $type ); ?>" value="<?php echo esc_attr( wp_get_current_user()->user_email ); ?>" />
					<button type="button" class="button ddwcwl-send-test"><?php esc_html_e( 'Send test', 'devdiggers-waitlist-for-woocommerce' ); ?></button>
				</span>
				<span class="ddwcwl-test-result" role="status"></span>
				<p class="description"><?php esc_html_e( 'Preview and test use your latest saved settings and sample data from your store.', 'devdiggers-waitlist-for-woocommerce' ); ?></p>
			</div>
			<?php
			return ob_get_clean();
		}
	}
}
