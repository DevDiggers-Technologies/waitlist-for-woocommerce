<?php
/**
 * Waiting list email (HTML). Shared by every waiting list email.
 *
 * Override by copying to yourtheme/ddwc-waiting-list/emails/waiting-list.php.
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 *
 * @var string      $email_heading
 * @var string      $body         Content from the Emails screen, placeholders replaced.
 * @var array       $button       { text, url }.
 * @var array       $footer_link  { text, url }.
 * @var string      $accent_color
 * @var object      $subscriber
 * @var \WC_Product $product
 * @var \WC_Email   $email
 */

defined( 'ABSPATH' ) || exit();

$ddwcwl_image_id = $product->get_image_id() ? $product->get_image_id() : ( $product->get_parent_id() ? get_post_thumbnail_id( $product->get_parent_id() ) : 0 );

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce core email hook.
do_action( 'woocommerce_email_header', $email_heading, $email );

echo wp_kses_post( $body );
?>

<table cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 24px 0; border: 1px solid #e5e7eb; border-radius: 10px;">
	<tr>
		<?php if ( $ddwcwl_image_id ) : ?>
			<td width="112" style="padding: 16px 0 16px 16px; vertical-align: middle;">
				<img src="<?php echo esc_url( wp_get_attachment_image_url( $ddwcwl_image_id, 'woocommerce_thumbnail' ) ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>" width="96" style="display: block; width: 96px; max-width: 96px; height: auto; border-radius: 8px;" />
			</td>
		<?php endif; ?>
		<td style="padding: 16px; vertical-align: middle;">
			<p style="margin: 0 0 4px; font-size: 16px; font-weight: 600;"><?php echo esc_html( $product->get_name() ); ?></p>
			<p style="margin: 0; color: #6b7280;"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>
		</td>
	</tr>
</table>

<?php if ( ! empty( $button['url'] ) && ! empty( $button['text'] ) ) : ?>
	<p style="margin: 0 0 24px; text-align: center;">
		<a href="<?php echo esc_url( $button['url'] ); ?>" style="display: inline-block; padding: 13px 30px; background-color: <?php echo esc_attr( $accent_color ); ?>; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600;"><?php echo esc_html( $button['text'] ); ?></a>
	</p>
<?php endif; ?>

<?php if ( ! empty( $footer_link['text'] ) ) : ?>
	<p style="margin: 0; font-size: 12px; color: #9ca3af; text-align: center;">
		<?php if ( ! empty( $footer_link['url'] ) ) : ?>
			<a href="<?php echo esc_url( $footer_link['url'] ); ?>" style="color: #9ca3af;"><?php echo esc_html( $footer_link['text'] ); ?></a>
		<?php else : ?>
			<?php echo esc_html( $footer_link['text'] ); ?>
		<?php endif; ?>
	</p>
<?php endif; ?>

<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce core email hook.
do_action( 'woocommerce_email_footer', $email );
