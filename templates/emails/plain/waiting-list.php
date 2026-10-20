<?php
/**
 * Waiting list email (plain text).
 *
 * Override by copying to yourtheme/ddwc-waiting-list/emails/plain/waiting-list.php.
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 *
 * @var string      $email_heading
 * @var string      $body
 * @var array       $button
 * @var array       $footer_link
 * @var object      $subscriber
 * @var \WC_Product $product
 * @var \WC_Email   $email
 */

defined( 'ABSPATH' ) || exit();

echo "=================================================================\n";
echo esc_html( wp_strip_all_tags( $email_heading ) ) . "\n";
echo "=================================================================\n\n";

echo esc_html( wp_strip_all_tags( wptexturize( $body ) ) ) . "\n\n";

echo esc_html( wp_strip_all_tags( $product->get_name() ) ) . ' - ' . esc_html( wp_strip_all_tags( wc_price( wc_get_price_to_display( $product ) ) ) ) . "\n\n";

if ( ! empty( $button['url'] ) ) {
	echo esc_html( $button['text'] ) . ': ' . esc_url_raw( $button['url'] ) . "\n\n";
}

if ( ! empty( $footer_link['text'] ) ) {
	echo esc_html( $footer_link['text'] ) . ( ! empty( $footer_link['url'] ) ? ': ' . esc_url_raw( $footer_link['url'] ) : '' ) . "\n\n";
}

echo "----------------------------------------\n\n";

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce core filter.
echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
