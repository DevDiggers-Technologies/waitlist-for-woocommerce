<?php
/**
 * Signup form configuration template.
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Templates\Admin\Configuration;

use DDWCWaitingList\Includes\DDWCWL_Admin_Dashboard;
use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_Display_Configuration_Template' ) ) {
	/**
	 * Signup form configuration template class.
	 */
	class DDWCWL_Display_Configuration_Template {
		/**
		 * Construct.
		 */
		public function __construct() {
			global $ddwcwl_waiting_list;

			$config = $ddwcwl_waiting_list;
			$args   = [
				[
					'header' => [
						'heading'     => esc_html__( 'Form Content', 'devdiggers-waitlist-for-woocommerce' ),
						'description' => esc_html__( 'The words customers see on sold out products. Clear, short copy converts best.', 'devdiggers-waitlist-for-woocommerce' ),
					],
					'fields' => [
						$this->text( 'form_title', esc_html__( 'Title', 'devdiggers-waitlist-for-woocommerce' ), esc_html__( 'Bold line at the top of the form.', 'devdiggers-waitlist-for-woocommerce' ) ),
						$this->text( 'form_description', esc_html__( 'Description', 'devdiggers-waitlist-for-woocommerce' ), esc_html__( 'One short sentence under the title. Leave empty to hide it.', 'devdiggers-waitlist-for-woocommerce' ) ),
						$this->text( 'join_button_text', esc_html__( 'Button Text', 'devdiggers-waitlist-for-woocommerce' ), esc_html__( 'Default: Notify Me.', 'devdiggers-waitlist-for-woocommerce' ) ),
						$this->text( 'form_note', esc_html__( 'Reassurance Note', 'devdiggers-waitlist-for-woocommerce' ), esc_html__( 'Small line under the email field that tells guests what to expect. Leave empty to hide it.', 'devdiggers-waitlist-for-woocommerce' ) ),
						$this->text( 'email_placeholder', esc_html__( 'Email Placeholder', 'devdiggers-waitlist-for-woocommerce' ), esc_html__( 'Hint inside the email field shown to guests.', 'devdiggers-waitlist-for-woocommerce' ) ),
						$this->text( 'leave_button_text', esc_html__( 'Leave Text', 'devdiggers-waitlist-for-woocommerce' ), esc_html__( 'Link shown to logged-in customers who already joined, and on My Account.', 'devdiggers-waitlist-for-woocommerce' ) ),
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'After Joining', 'devdiggers-waitlist-for-woocommerce' ),
						'description' => esc_html__( 'The form turns into a short confirmation once the customer is on the list.', 'devdiggers-waitlist-for-woocommerce' ),
					],
					'fields' => [
						$this->text( 'joined_title', esc_html__( 'Confirmation Title', 'devdiggers-waitlist-for-woocommerce' ), esc_html__( 'Default: You are on the list.', 'devdiggers-waitlist-for-woocommerce' ) ),
						$this->text( 'joined_message', esc_html__( 'Confirmation Message', 'devdiggers-waitlist-for-woocommerce' ), esc_html__( 'Use {position} for the customer\'s place in line. Shown when "Place in Line" is on.', 'devdiggers-waitlist-for-woocommerce' ) ),
					],
				],
				[
					'header'            => [
						'heading'     => esc_html__( 'Where It Shows', 'devdiggers-waitlist-for-woocommerce' ),
						'description' => esc_html__( 'The form shows on sold out product pages and anywhere you place the shortcode.', 'devdiggers-waitlist-for-woocommerce' ),
					],
					'after_header_html' => ddfw_get_upgrade_to_pro_section(
						DDWCWL_Admin_Dashboard::ddwcwl_get_upgrade_args(
							'shop-pages',
							esc_html__( 'Capture demand on shop and category pages', 'devdiggers-waitlist-for-woocommerce' ),
							esc_html__( 'Pro swaps "Read more" for a "Notify Me" button on every product grid. It opens a quick signup window, so customers join without leaving the page.', 'devdiggers-waitlist-for-woocommerce' ),
							[
								esc_html__( 'Classic shop loops and WooCommerce block grids', 'devdiggers-waitlist-for-woocommerce' ),
								esc_html__( 'Variation picker inside the window', 'devdiggers-waitlist-for-woocommerce' ),
								esc_html__( 'Works with full page caching', 'devdiggers-waitlist-for-woocommerce' ),
							]
						)
					),
				],
				[
					'header'            => [
						'heading'     => esc_html__( 'Extra Fields', 'devdiggers-waitlist-for-woocommerce' ),
						'description' => esc_html__( 'Optional details that help you plan restocks.', 'devdiggers-waitlist-for-woocommerce' ),
					],
					'after_header_html' => ddfw_get_upgrade_to_pro_section(
						DDWCWL_Admin_Dashboard::ddwcwl_get_upgrade_args(
							'extra-fields',
							esc_html__( 'Know how many units to reorder', 'devdiggers-waitlist-for-woocommerce' ),
							esc_html__( 'Pro asks customers how many units they want and, if you like, their phone number for SMS tools.', 'devdiggers-waitlist-for-woocommerce' ),
							[
								esc_html__( '"Units wanted" totals on the Products screen', 'devdiggers-waitlist-for-woocommerce' ),
								esc_html__( 'Optional or required phone field', 'devdiggers-waitlist-for-woocommerce' ),
							]
						)
					),
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Social Proof & Expectations', 'devdiggers-waitlist-for-woocommerce' ),
						'description' => esc_html__( 'Show demand and set expectations. Both build urgency and trust.', 'devdiggers-waitlist-for-woocommerce' ),
					],
					'fields' => [
						$this->checkbox( 'show_subscriber_count', esc_html__( 'Waiting Count', 'devdiggers-waitlist-for-woocommerce' ), esc_html__( 'Show how many people are waiting', 'devdiggers-waitlist-for-woocommerce' ), esc_html__( 'Displays "12 people are waiting for this product." Hidden while nobody is waiting.', 'devdiggers-waitlist-for-woocommerce' ) ),
						$this->checkbox( 'show_queue_position', esc_html__( 'Place in Line', 'devdiggers-waitlist-for-woocommerce' ), esc_html__( 'Tell customers their place in line after they join', 'devdiggers-waitlist-for-woocommerce' ), esc_html__( 'Customers are emailed first come first served, so their number shows they are not forgotten.', 'devdiggers-waitlist-for-woocommerce' ) ),
						ddfw_locked_field( $this->checkbox( 'show_restock_date', esc_html__( 'Restock Date', 'devdiggers-waitlist-for-woocommerce' ), esc_html__( 'Show the expected restock date and note', 'devdiggers-waitlist-for-woocommerce' ), esc_html__( 'Set a date and a short note per product or variation. Past dates are hidden automatically.', 'devdiggers-waitlist-for-woocommerce' ) ), 'ddwcwl' ),
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Privacy & Spam Protection', 'devdiggers-waitlist-for-woocommerce' ),
						'description' => esc_html__( 'Every form has invisible spam protection. Add these for GDPR stores or if you see fake signups.', 'devdiggers-waitlist-for-woocommerce' ),
					],
					'fields' => [
						ddfw_locked_field( $this->checkbox( 'double_optin', esc_html__( 'Double Opt-in', 'devdiggers-waitlist-for-woocommerce' ), esc_html__( 'Guests confirm their email before joining', 'devdiggers-waitlist-for-woocommerce' ), esc_html__( 'Stops mistyped and fake emails. Logged-in customers skip this step.', 'devdiggers-waitlist-for-woocommerce' ) ), 'ddwcwl' ),
						$this->checkbox( 'privacy_consent', esc_html__( 'Consent Checkbox', 'devdiggers-waitlist-for-woocommerce' ), esc_html__( 'Require a consent checkbox to join', 'devdiggers-waitlist-for-woocommerce' ), esc_html__( 'Customers must tick the box below the form before they can join. Recommended for stores selling in the EU and UK.', 'devdiggers-waitlist-for-woocommerce' ) ),
						[
							'type'        => 'textarea',
							'label'       => esc_html__( 'Consent Text', 'devdiggers-waitlist-for-woocommerce' ),
							'description' => esc_html__( 'Basic HTML is allowed, so you can link your privacy policy.', 'devdiggers-waitlist-for-woocommerce' ),
							'id'          => 'ddwcwl-privacy-consent-text',
							'value'       => $config['privacy_consent_text'],
						],
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Style', 'devdiggers-waitlist-for-woocommerce' ),
						'description' => esc_html__( 'The form adapts to your theme colors. Pick a button color to match your brand.', 'devdiggers-waitlist-for-woocommerce' ),
					],
					'fields' => [
						ddfw_locked_field(
							[
								'type'        => 'select',
								'label'       => esc_html__( 'Form Style', 'devdiggers-waitlist-for-woocommerce' ),
								'description' => esc_html__( 'Card adds a soft background and border. Minimal blends into the product summary.', 'devdiggers-waitlist-for-woocommerce' ),
								'id'          => 'form-style',
								'value'       => 'card',
								'options'     => [
									'card'    => esc_html__( 'Card', 'devdiggers-waitlist-for-woocommerce' ),
									'minimal' => esc_html__( 'Minimal', 'devdiggers-waitlist-for-woocommerce' ),
								],
							],
							'ddwcwl'
						),
						$this->checkbox( 'show_form_icon', esc_html__( 'Header Icon', 'devdiggers-waitlist-for-woocommerce' ), esc_html__( 'Show the bell icon next to the title', 'devdiggers-waitlist-for-woocommerce' ), esc_html__( 'A thin line icon drawn in your button color.', 'devdiggers-waitlist-for-woocommerce' ) ),
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Button Color', 'devdiggers-waitlist-for-woocommerce' ),
							'description' => esc_html__( 'Background of the Notify Me buttons.', 'devdiggers-waitlist-for-woocommerce' ),
							'id'          => 'ddwcwl-button-bg-color',
							'value'       => $config['button_bg_color'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Button Text Color', 'devdiggers-waitlist-for-woocommerce' ),
							'description' => esc_html__( 'Text and icon color on the Notify Me buttons.', 'devdiggers-waitlist-for-woocommerce' ),
							'id'          => 'ddwcwl-button-text-color',
							'value'       => $config['button_text_color'],
						],
						$this->checkbox( 'use_custom_notices', esc_html__( 'Custom Messages', 'devdiggers-waitlist-for-woocommerce' ), esc_html__( 'Use your own style for form messages', 'devdiggers-waitlist-for-woocommerce' ), esc_html__( 'Replaces the built-in success and error message style.', 'devdiggers-waitlist-for-woocommerce' ) ),
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Message Background', 'devdiggers-waitlist-for-woocommerce' ),
							'description' => esc_html__( 'Background of success and error messages.', 'devdiggers-waitlist-for-woocommerce' ),
							'id'          => 'ddwcwl-notice-bg-color',
							'value'       => $config['notice_bg_color'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Message Text Color', 'devdiggers-waitlist-for-woocommerce' ),
							'description' => esc_html__( 'Text color of the messages.', 'devdiggers-waitlist-for-woocommerce' ),
							'id'          => 'ddwcwl-notice-color',
							'value'       => $config['notice_color'],
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Message Font Size', 'devdiggers-waitlist-for-woocommerce' ),
							'description' => esc_html__( 'A CSS size such as 16px or 1em.', 'devdiggers-waitlist-for-woocommerce' ),
							'id'          => 'ddwcwl-notice-font-size',
							'value'       => $config['notice_font_size'],
							'placeholder' => '16px',
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Message Corner Radius', 'devdiggers-waitlist-for-woocommerce' ),
							'description' => esc_html__( 'A CSS size such as 6px.', 'devdiggers-waitlist-for-woocommerce' ),
							'id'          => 'ddwcwl-notice-border-radius',
							'value'       => $config['notice_border_radius'],
							'placeholder' => '6px',
						],
					],
				],
			];

			( new DDFW_Layout() )->get_form_section_layout( $args, 'ddwcwl-display-configuration-fields' );
		}

		/**
		 * Text field.
		 *
		 * @param string $key         Setting key.
		 * @param string $label       Label.
		 * @param string $description Description.
		 * @return array
		 */
		protected function text( $key, $label, $description ) {
			global $ddwcwl_waiting_list;

			return [
				'type'        => 'text',
				'label'       => $label,
				'description' => $description,
				'id'          => 'ddwcwl-' . str_replace( '_', '-', $key ),
				'value'       => $ddwcwl_waiting_list[ $key ],
			];
		}

		/**
		 * Checkbox field.
		 *
		 * @param string $key            Setting key.
		 * @param string $label          Label.
		 * @param string $checkbox_label Checkbox label.
		 * @param string $description    Description.
		 * @return array
		 */
		protected function checkbox( $key, $label, $checkbox_label, $description = '' ) {
			global $ddwcwl_waiting_list;

			return [
				'type'           => 'checkbox',
				'label'          => $label,
				'checkbox_label' => $checkbox_label,
				'description'    => $description,
				'id'             => 'ddwcwl-' . str_replace( '_', '-', $key ),
				'value'          => $ddwcwl_waiting_list[ $key ] ?? '',
			];
		}
	}
}
