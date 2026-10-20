<?php
/**
 * Waiting List onboarding.
 *
 * @package DevDiggers Waitlist for WooCommerce
 */

namespace DDWCWaitingList\Includes\Admin;

use DevDiggers\Framework\Includes\DDFW_Form_Field;
use DevDiggers\Framework\Includes\DDFW_Setup_Wizard;

defined( 'ABSPATH' ) || exit();

/** Onboarding screens for Waiting List. */
class DDWCWL_Setup_Wizard {
	/** Register wizard. */
	public function __construct() {
		new DDFW_Setup_Wizard( $this->get_wizard_config() );
	}

	/** Return framework wizard configuration.
	 *
	 * @return array Wizard configuration.
	 */
	public function get_wizard_config() {
		return [
			'plugin_slug'    => DDWCWL_PLUGIN_SLUG,
			'plugin_file'    => DDWCWL_PLUGIN_SLUG . '/functions.php',
			'dashboard_page' => DDWCWL_DASHBOARD_PAGE,
			'redirect_url'   => admin_url( 'admin.php?page=' . DDWCWL_DASHBOARD_PAGE ),
			'brand'          => [
				'name'        => esc_html__( 'Waitlist', 'devdiggers-waitlist-for-woocommerce' ),
				'description' => esc_html__( 'Turn sold out products into sales.', 'devdiggers-waitlist-for-woocommerce' ),
			],
			'steps'          => [
				'welcome' => [
					'label'         => esc_html__( 'Welcome', 'devdiggers-waitlist-for-woocommerce' ),
					'view_callback' => [ $this, 'welcome_view' ],
				],
				'general' => [
					'label'         => esc_html__( 'General', 'devdiggers-waitlist-for-woocommerce' ),
					'title'         => esc_html__( 'Set up your waiting list', 'devdiggers-waitlist-for-woocommerce' ),
					'view_callback' => [ $this, 'general_view' ],
					'save_callback' => [ $this, 'general_save' ],
				],
				'growth'  => [
					'label'         => esc_html__( 'Growth', 'devdiggers-waitlist-for-woocommerce' ),
					'title'         => esc_html__( 'Build trust at signup', 'devdiggers-waitlist-for-woocommerce' ),
					'view_callback' => [ $this, 'growth_view' ],
					'save_callback' => [ $this, 'growth_save' ],
				],
				'ready'   => [
					'label'             => esc_html__( 'Ready!', 'devdiggers-waitlist-for-woocommerce' ),
					'ready_title'       => esc_html__( 'Your waiting list is ready.', 'devdiggers-waitlist-for-woocommerce' ),
					'ready_description' => esc_html__( 'Out of stock products now show a "Notify Me" form. Track signups and demand from the dashboard.', 'devdiggers-waitlist-for-woocommerce' ),
				],
			],
		];
	}

	/** Render welcome step. */
	public function welcome_view() {
		?>
		<div class="ddfw-setup-wizard-ready ddfw-setup-wizard-onboarding">
			<div class="ddfw-success-icon-wrap"><?php echo wp_kses( \DDWCWaitingList\Includes\DDWCWL_Admin_Dashboard::get_plugin_icon( 100, 'ddfw-success-svg' ), ddfw_kses_allowed_svg_tags() ); ?></div>
			<h2 class="ddfw-setup-wizard-ready-title"><?php esc_html_e( 'Welcome to Waitlist!', 'devdiggers-waitlist-for-woocommerce' ); ?></h2>
			<p class="ddfw-setup-wizard-ready-desc"><?php esc_html_e( 'Let us set up your waiting list in a minute: choose who can join and how restock emails go out, so every sold out product keeps collecting buyers.', 'devdiggers-waitlist-for-woocommerce' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Render a list of framework fields.
	 *
	 * @param array $fields Fields.
	 * @return void
	 */
	protected function render_fields( $fields ) {
		?>
		<div class="ddfw-fields-section"><table class="form-table"><tbody>
		<?php
		foreach ( $fields as $field ) {
			DDFW_Form_Field::display_form_field( $field );
		}
		?>
		</tbody></table></div>
		<?php
	}

	/** Render key general settings. */
	public function general_view() {
		global $ddwcwl_waiting_list;

		$this->render_fields(
			[
				[
					'type'           => 'checkbox',
					'label'          => esc_html__( 'Status', 'devdiggers-waitlist-for-woocommerce' ),
					'checkbox_label' => esc_html__( 'Enable the waitlist', 'devdiggers-waitlist-for-woocommerce' ),
					'name'           => '_ddwcwl_enabled',
					'value'          => $ddwcwl_waiting_list['enabled'],
				],
				[
					'type'  => 'text',
					'label' => esc_html__( 'Join Button Text', 'devdiggers-waitlist-for-woocommerce' ),
					'name'  => '_ddwcwl_join_button_text',
					'value' => $ddwcwl_waiting_list['join_button_text'],
				],
				[
					'type'           => 'checkbox',
					'label'          => esc_html__( 'Restock Emails', 'devdiggers-waitlist-for-woocommerce' ),
					'checkbox_label' => esc_html__( 'Automatically email waiting customers when stock returns', 'devdiggers-waitlist-for-woocommerce' ),
					'name'           => '_ddwcwl_mail_automatically',
					'value'          => $ddwcwl_waiting_list['mail_automatically'],
				],
				[
					'type'           => 'checkbox',
					'label'          => esc_html__( 'Backorders', 'devdiggers-waitlist-for-woocommerce' ),
					'checkbox_label' => esc_html__( 'Also show the form on backorder products', 'devdiggers-waitlist-for-woocommerce' ),
					'name'           => '_ddwcwl_include_backorders',
					'value'          => $ddwcwl_waiting_list['include_backorders'],
				],
			]
		);
	}

	/** Render growth settings. */
	public function growth_view() {
		global $ddwcwl_waiting_list;

		$this->render_fields(
			[
				[
					'type'           => 'checkbox',
					'label'          => esc_html__( 'Social Proof', 'devdiggers-waitlist-for-woocommerce' ),
					'checkbox_label' => esc_html__( 'Show how many people are waiting', 'devdiggers-waitlist-for-woocommerce' ),
					'name'           => '_ddwcwl_show_subscriber_count',
					'value'          => $ddwcwl_waiting_list['show_subscriber_count'],
				],
				[
					'type'           => 'checkbox',
					'label'          => esc_html__( 'Place in Line', 'devdiggers-waitlist-for-woocommerce' ),
					'checkbox_label' => esc_html__( 'Tell customers their place in line after they join', 'devdiggers-waitlist-for-woocommerce' ),
					'name'           => '_ddwcwl_show_queue_position',
					'value'          => $ddwcwl_waiting_list['show_queue_position'],
				],
				[
					'type'           => 'checkbox',
					'label'          => esc_html__( 'Consent Checkbox', 'devdiggers-waitlist-for-woocommerce' ),
					'checkbox_label' => esc_html__( 'Require a consent checkbox to join (recommended for EU and UK stores)', 'devdiggers-waitlist-for-woocommerce' ),
					'name'           => '_ddwcwl_privacy_consent',
					'value'          => $ddwcwl_waiting_list['privacy_consent'],
				],
			]
		);
	}

	/**
	 * Save the general step.
	 *
	 * @param array $form_data Submitted fields.
	 * @return true
	 */
	public function general_save( $form_data ) {
		return $this->save_fields( $form_data, [ '_ddwcwl_enabled', '_ddwcwl_join_button_text', '_ddwcwl_mail_automatically', '_ddwcwl_include_backorders' ] );
	}

	/**
	 * Save the growth step.
	 *
	 * @param array $form_data Submitted fields.
	 * @return true
	 */
	public function growth_save( $form_data ) {
		return $this->save_fields( $form_data, [ '_ddwcwl_show_subscriber_count', '_ddwcwl_show_queue_position', '_ddwcwl_privacy_consent' ] );
	}

	/**
	 * Save a step's options. Missing fields are unchecked checkboxes, saved as off.
	 * update_option runs the register_setting sanitizers.
	 *
	 * @param array    $form_data Submitted fields as name/value pairs.
	 * @param string[] $options   Options owned by the step.
	 * @return true
	 */
	protected function save_fields( $form_data, $options ) {
		$submitted = wp_list_pluck( (array) $form_data, 'value', 'name' );

		foreach ( $options as $option ) {
			update_option( $option, sanitize_text_field( $submitted[ $option ] ?? '' ) );
		}

		return true;
	}
}
