<?php
/**
 * Products helper. Decides which products accept waiting list signups.
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Helper\Products;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_Products_Helper' ) ) {
	/**
	 * Products helper class.
	 */
	class DDWCWL_Products_Helper {
		/**
		 * Whether customers can join the waiting list for a product or variation.
		 *
		 * @param \WC_Product $product Product (simple, variation, etc.). Variable parents return false.
		 * @return bool
		 */
		public static function is_eligible( $product ) {
			global $ddwcwl_waiting_list;

			if ( ! $product instanceof \WC_Product || $product->is_type( [ 'variable', 'grouped', 'external' ] ) ) {
				return false;
			}

			$status   = $product->get_stock_status();
			$eligible = 'outofstock' === $status || ( 'onbackorder' === $status && 'yes' === $ddwcwl_waiting_list['include_backorders'] );

			if ( $eligible && self::is_excluded( $product ) ) {
				$eligible = false;
			}

			/**
			 * Filters whether a product accepts waiting list signups.
			 *
			 * @since 1.0.0
			 *
			 * @param bool        $eligible Whether signups are accepted.
			 * @param \WC_Product $product  Product.
			 */
			return (bool) apply_filters( 'ddwcwl_is_product_eligible', $eligible, $product );
		}

		/**
		 * Whether a product, its parent or one of its categories is excluded in settings.
		 *
		 * @param \WC_Product $product Product.
		 * @return bool
		 */
		public static function is_excluded( $product ) {
			global $ddwcwl_waiting_list;

			$ids = array_filter( [ $product->get_id(), $product->get_parent_id() ] );

			if ( array_intersect( $ids, $ddwcwl_waiting_list['excluded_products'] ) ) {
				return true;
			}

			if ( ! empty( $ddwcwl_waiting_list['excluded_categories'] ) ) {
				$parent_id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
				$terms     = wc_get_product_term_ids( $parent_id, 'product_cat' );

				if ( array_intersect( $terms, $ddwcwl_waiting_list['excluded_categories'] ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Eligible variations of a variable product.
		 *
		 * @param \WC_Product_Variable $product Variable product.
		 * @return \WC_Product_Variation[]
		 */
		public static function get_eligible_variations( $product ) {
			$variations = [];

			foreach ( $product->get_children() as $child_id ) {
				$variation = wc_get_product( $child_id );

				if ( $variation && self::is_eligible( $variation ) ) {
					$variations[] = $variation;
				}
			}

			return $variations;
		}
	}
}
