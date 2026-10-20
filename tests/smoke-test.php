<?php
/**
 * End-to-end smoke test: signup, queue position, duplicate, leave, restock, background batch,
 * emails, unsubscribe token, privacy eraser, product delete.
 *
 * Run: wp eval-file wp-content/plugins/devdiggers-waitlist-for-woocommerce/tests/smoke-test.php
 * Creates and deletes its own products. No email leaves the site.
 *
 * @package DevDiggers Waitlist for WooCommerce
 */

use DDWCWaitingList\Helper\Products\DDWCWL_Products_Helper;
use DDWCWaitingList\Helper\Subscribers\DDWCWL_Subscribers_Helper;
use DDWCWaitingList\Includes\Front\DDWCWL_Front_Functions;
use DDWCWaitingList\Includes\DDWCWL_Notifier;

defined( 'ABSPATH' ) || exit();

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.WP.GlobalVariablesOverride -- CLI test script run through wp eval-file.

$sent = [];
add_filter(
	'pre_wp_mail',
	function ( $result, $atts ) use ( &$sent ) {
		$sent[] = [ $atts['to'], $atts['subject'] ];
		return true;
	},
	10,
	2
);

$check = function ( $ok, $label ) {
	echo ( $ok ? 'PASS ' : 'FAIL ' ) . $label . "\n";
	if ( ! $ok ) {
		$GLOBALS['ddwcwl_failed'] = true;
	}
};

// Run against defaults, whatever the store has configured.
global $ddwcwl_waiting_list;
$ddwcwl_waiting_list = array_merge( $ddwcwl_waiting_list, [ 'privacy_consent' => '', 'require_login' => '', 'mail_automatically' => 'yes', 'excluded_products' => [], 'excluded_categories' => [] ] );

$helper = new DDWCWL_Subscribers_Helper();
$front  = new DDWCWL_Front_Functions();

$product = new WC_Product_Simple();
$product->set_name( 'Waitlist smoke product' );
$product->set_regular_price( '10' );
$product->set_manage_stock( true );
$product->set_stock_quantity( 0 );
$product->set_status( 'publish' );
$product->save();
$id = $product->get_id();

$check( DDWCWL_Products_Helper::is_eligible( wc_get_product( $id ) ), 'out of stock product is eligible' );

$join = function ( $email ) use ( $front, $id ) {
	return $front->ddwcwl_process_join(
		[
			'ddwcwl_product_id' => $id,
			'ddwcwl_user_email' => $email,
		]
	);
};

$a = $join( 'a@example.com' );
$b = $join( 'b@example.com' );
$check( 'added' === $a['code'] && 1 === $a['position'], 'first guest joins at position 1' );
$check( 2 === $b['position'] && 2 === $b['count'], 'second guest at position 2, count 2' );
$check( 'exists' === $join( 'a@example.com' )['code'], 'duplicate signup is rejected' );
$check( 'invalid_email' === $join( 'nope' )['code'], 'invalid email is rejected' );
$check( 'added' === $front->ddwcwl_process_join( [ 'ddwcwl_product_id' => $id, 'ddwcwl_user_email' => 'bot@example.com', 'ddwcwl_website' => 'x' ] )['code'] && ! $helper->get_by_product_email( $id, 'bot@example.com' ), 'honeypot pretends success and stores nothing' );
$check( 2 === count( $sent ), 'signup confirmation sent to each guest, admin alert off by default' );

$sent = [];
$product = wc_get_product( $id );
$product->set_stock_quantity( 5 );
$product->save();

// Run whatever Action Scheduler queued.
if ( class_exists( 'ActionScheduler_QueueRunner' ) ) {
	foreach ( as_get_scheduled_actions( [ 'hook' => DDWCWL_Notifier::BATCH_HOOK, 'status' => 'pending', 'group' => 'ddwcwl' ], 'ids' ) as $action_id ) {
		ActionScheduler::runner()->process_action( $action_id );
	}
}

$row_a = $helper->get_by_product_email( $id, 'a@example.com' );
$check( 'notified' === $row_a->status, 'restock notifies waiting customers' );
$check( 2 === count( $sent ) && false !== strpos( $sent[0][1], 'back in stock' ), 'two back-in-stock emails sent' );
$check( ! DDWCWL_Products_Helper::is_eligible( wc_get_product( $id ) ), 'in stock product no longer eligible' );

$_GET = [ 'ddwcwl_action' => 'unsubscribe', 'ddwcwl_token' => $row_a->token ];
$check( $helper->unsubscribe( $helper->get_by_token( $row_a->token ) ), 'unsubscribe token resolves and unsubscribes' );
$_GET = [];

$eraser = ( new DDWCWaitingList\Includes\Common\DDWCWL_Common_Functions() )->ddwcwl_privacy_erase( 'b@example.com' );
$check( $eraser['items_removed'] && ! $helper->get_by_product_email( $id, 'b@example.com' ), 'privacy eraser deletes rows' );

wp_delete_post( $id, true );
$check( ! $helper->get_by_product_email( $id, 'a@example.com' ), 'deleting a product deletes its subscribers' );

// Variable product: only the sold out variation accepts signups.
$parent = new WC_Product_Variable();
$parent->set_name( 'Waitlist smoke variable' );
$parent->set_status( 'publish' );
$parent->save();
$vars = [];
foreach ( [ 'instock', 'outofstock' ] as $status ) {
	$v = new WC_Product_Variation();
	$v->set_parent_id( $parent->get_id() );
	$v->set_regular_price( '5' );
	$v->set_stock_status( $status );
	$v->save();
	$vars[ $status ] = $v->get_id();
}
WC_Product_Variable::sync( $parent->get_id() );
$eligible = array_map(
	function ( $variation ) {
		return $variation->get_id();
	},
	DDWCWL_Products_Helper::get_eligible_variations( wc_get_product( $parent->get_id() ) )
);
$check( [ $vars['outofstock'] ] === $eligible, 'only the sold out variation is eligible' );
$check( 'added' === $front->ddwcwl_process_join( [ 'ddwcwl_product_id' => $vars['outofstock'], 'ddwcwl_user_email' => 'v@example.com' ] )['code'], 'guest joins a variation' );
$check( 'unavailable' === $front->ddwcwl_process_join( [ 'ddwcwl_product_id' => $vars['instock'], 'ddwcwl_user_email' => 'v@example.com' ] )['code'], 'in stock variation rejects signups' );
foreach ( $vars as $vid ) {
	wp_delete_post( $vid, true );
}
wp_delete_post( $parent->get_id(), true );

echo empty( $GLOBALS['ddwcwl_failed'] ) ? "ALL PASSED\n" : "FAILURES\n";
