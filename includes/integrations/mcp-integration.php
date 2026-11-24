<?php
/**
 * MCP Integration
 *
 * Puts the waitlist in front of AI assistants (Claude, Cursor, VS Code and any MCP client). Every tool is a
 * WordPress Ability (WordPress 6.9+) announced to the DevDiggers AI & MCP hub (DevDiggers Plugins > AI & MCP),
 * which lists the tools on the shared DevDiggers MCP server, turns them on or off, logs calls and offers a
 * playground.
 *
 * Text here goes to AI clients as JSON, so it is translated but not HTML-escaped.
 *
 * @package DevDiggers Waitlist for WooCommerce
 */

namespace DDWCWaitingList\Includes\Integrations;

use DDWCWaitingList\Helper\Products\DDWCWL_Products_Helper;
use DDWCWaitingList\Helper\Subscribers\DDWCWL_Subscribers_Helper;
use DDWCWaitingList\Includes\Admin\DDWCWL_Admin_Functions;
use DDWCWaitingList\Includes\DDWCWL_Admin_Dashboard;
use DDWCWaitingList\Includes\DDWCWL_Notifier;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_MCP_Integration' ) ) {
	/**
	 * MCP Integration class.
	 */
	class DDWCWL_MCP_Integration {
		/**
		 * Ability category and name prefix.
		 */
		const CATEGORY = 'waitlist';

		/**
		 * Subscribers helper.
		 *
		 * @var DDWCWL_Subscribers_Helper
		 */
		protected $subscribers;

		/**
		 * Construct.
		 */
		public function __construct() {
			// The Abilities API arrived in WordPress 6.9.
			if ( ! function_exists( 'wp_register_ability' ) ) {
				return;
			}

			$this->subscribers = new DDWCWL_Subscribers_Helper();

			add_action( 'wp_abilities_api_categories_init', [ $this, 'ddwcwl_register_category' ] );
			add_action( 'wp_abilities_api_init', [ $this, 'ddwcwl_register_abilities' ] );
			add_filter( 'ddfw_mcp_plugins', [ $this, 'ddwcwl_add_to_hub' ] );
		}

		/**
		 * Announce the waitlist to the DevDiggers AI & MCP hub.
		 *
		 * @param array $plugins Hub plugins.
		 * @return array
		 */
		public function ddwcwl_add_to_hub( $plugins ) {
			$plugins[ self::CATEGORY ] = [
				'name'        => __( 'Waitlist', 'devdiggers-waitlist-for-woocommerce' ),
				'description' => __( 'Ask which sold out products customers are waiting for, who is on a product\'s waitlist, which lists a customer joined and which restocked products still have people to email.', 'devdiggers-waitlist-for-woocommerce' ),
				'category'    => self::CATEGORY,
				'icon'        => DDWCWL_Admin_Dashboard::get_plugin_icon(),
				'url'         => admin_url( 'admin.php?page=' . DDWCWL_DASHBOARD_PAGE ),
			];

			return $plugins;
		}

		/**
		 * Category for every waitlist ability.
		 *
		 * @return void
		 */
		public function ddwcwl_register_category() {
			if ( wp_has_ability_category( self::CATEGORY ) ) {
				return;
			}

			wp_register_ability_category(
				self::CATEGORY,
				[
					'label'       => __( 'Waitlist', 'devdiggers-waitlist-for-woocommerce' ),
					'description' => __( 'Back in stock waitlists: demand per product, subscribers and restock emails.', 'devdiggers-waitlist-for-woocommerce' ),
				]
			);
		}

		/**
		 * Register every ability.
		 *
		 * @return void
		 */
		public function ddwcwl_register_abilities() {
			foreach ( $this->ddwcwl_get_abilities() as $name => $args ) {
				$name = self::CATEGORY . '/' . $name;

				if ( wp_has_ability( $name ) ) {
					continue;
				}

				$readonly = ! empty( $args['readonly'] );

				wp_register_ability(
					$name,
					[
						'label'               => $args['label'],
						'description'         => $args['description'],
						'category'            => self::CATEGORY,
						'input_schema'        => $args['input'],
						'output_schema'       => $args['output'],
						'execute_callback'    => $args['callback'],
						'permission_callback' => [ $this, 'ddwcwl_can_manage' ],
						'meta'                => [
							'show_in_rest' => true,
							'mcp'          => [
								'public' => true,
								'type'   => isset( $args['type'] ) ? $args['type'] : 'tool',
							],
							'annotations'  => [
								'readonly'        => $readonly,
								'destructive'     => ! $readonly,
								'idempotent'      => $readonly,
								'title'           => $args['label'],
								'readOnlyHint'    => $readonly,
								'destructiveHint' => ! $readonly,
								'idempotentHint'  => $readonly,
								'openWorldHint'   => false,
							],
						],
					]
				);
			}
		}

		/**
		 * Who may use the tools: the people who may open the waitlist screens (and act on them there).
		 *
		 * @return bool
		 */
		public function ddwcwl_can_manage() {
			return is_user_logged_in() && current_user_can( DDWCWL_Admin_Functions::ddwcwl_get_capability() ); // phpcs:ignore WordPress.WP.Capabilities.Undetermined -- Filtered capability, manage_woocommerce by default.
		}

		/**
		 * Ability definitions.
		 *
		 * @return array
		 */
		protected function ddwcwl_get_abilities() {
			$date     = [
				'type'    => 'string',
				'pattern' => '^\d{4}-\d{2}-\d{2}$',
			];
			$id       = [
				'type'    => 'integer',
				'minimum' => 1,
			];
			$page     = [
				'type'    => 'integer',
				'minimum' => 1,
				'default' => 1,
			];
			$per_page = [
				'type'    => 'integer',
				'minimum' => 1,
				'maximum' => 100,
				'default' => 20,
			];

			$abilities = [
				'overview'           => [
					'label'       => __( 'Waitlist overview', 'devdiggers-waitlist-for-woocommerce' ),
					'description' => __( 'How the waitlist is doing, the same numbers as the Waitlist dashboard: customers waiting now, products with waiting customers, signups and back in stock emails sent in a date range compared with the previous period, the most wanted products, and products already back in stock that still have customers waiting (ready to notify). Start here.', 'devdiggers-waitlist-for-woocommerce' ),
					'readonly'    => true,
					'input'       => $this->ddwcwl_input_schema(
						[
							'after'  => $date + [ 'description' => __( 'First day, Y-m-d in the store timezone. Defaults to 29 days before "before".', 'devdiggers-waitlist-for-woocommerce' ) ],
							'before' => $date + [ 'description' => __( 'Last day, Y-m-d. Defaults to today.', 'devdiggers-waitlist-for-woocommerce' ) ],
						]
					),
					'output'      => $this->ddwcwl_object_schema(
						[
							'period'                => 'object',
							'status_counts'         => 'object',
							'products_with_waiting' => 'integer',
							'totals'                => 'object',
							'most_wanted'           => 'array',
							'ready_to_notify'       => 'array',
						]
					),
					'callback'    => [ $this, 'ddwcwl_overview' ],
				],
				'list-products'      => [
					'label'       => __( 'List waitlisted products', 'devdiggers-waitlist-for-woocommerce' ),
					'description' => __( 'Products and variations that have a waitlist, most wanted first (as on the Waitlist > Products screen): customers waiting, already notified, total signups, last signup, and the current stock status. Use stock "out_of_stock" for "what should I reorder" and "in_stock" for restocked products that still have customers waiting. Variations are listed on their own with their own ID.', 'devdiggers-waitlist-for-woocommerce' ),
					'readonly'    => true,
					'input'       => $this->ddwcwl_input_schema(
						[
							'search'   => [
								'type'        => 'string',
								'description' => __( 'Part of the product name.', 'devdiggers-waitlist-for-woocommerce' ),
							],
							'stock'    => [
								'type'    => 'string',
								'enum'    => [ 'any', 'out_of_stock', 'in_stock' ],
								'default' => 'any',
							],
							'orderby'  => [
								'type'    => 'string',
								'enum'    => $this->ddwcwl_product_orderby(),
								'default' => 'waiting',
							],
							'page'     => $page,
							'per_page' => $per_page,
						]
					),
					'output'      => $this->ddwcwl_list_schema( 'products' ),
					'callback'    => [ $this, 'ddwcwl_list_products' ],
				],
				'get-product'        => [
					'label'       => __( 'Get a product\'s waitlist', 'devdiggers-waitlist-for-woocommerce' ),
					'description' => __( 'The waitlist of one product or variation: stock, whether it accepts signups now (and why not), subscriber counts per status, waiting customers per variation for a variable product, and the next customers in line (oldest first). Product IDs come from waitlist-list-products.', 'devdiggers-waitlist-for-woocommerce' ),
					'readonly'    => true,
					'input'       => $this->ddwcwl_input_schema( [ 'product_id' => $id + [ 'description' => __( 'Product or variation ID.', 'devdiggers-waitlist-for-woocommerce' ) ] ], [ 'product_id' ] ),
					'output'      => $this->ddwcwl_object_schema(
						[
							'product'         => 'object',
							'status_counts'   => 'object',
							'variations'      => 'array',
							'next_in_line'    => 'array',
							'subscribers_url' => 'string',
						]
					),
					'callback'    => [ $this, 'ddwcwl_get_product' ],
				],
				'list-subscribers'   => [
					'label'       => __( 'List subscribers', 'devdiggers-waitlist-for-woocommerce' ),
					'description' => __( 'Waitlist entries, newest first (as on the Waitlist > Subscribers screen): product, customer email and name, status, place in line while waiting, date joined and date emailed. Filter by product (a parent product ID includes its variations), status, a customer\'s email or user ID (every list that customer joined), or search an email or product name.', 'devdiggers-waitlist-for-woocommerce' ),
					'readonly'    => true,
					'input'       => $this->ddwcwl_input_schema(
						[
							'product_id'  => $id,
							'status'      => [
								'type'    => 'string',
								'enum'    => array_merge( [ 'any' ], DDWCWL_Subscribers_Helper::STATUSES ),
								'default' => 'any',
							],
							'email'       => [
								'type'        => 'string',
								'description' => __( 'Exact customer email.', 'devdiggers-waitlist-for-woocommerce' ),
							],
							'customer_id' => $id + [ 'description' => __( 'WordPress user ID of the customer; their account email is used.', 'devdiggers-waitlist-for-woocommerce' ) ],
							'search'      => [
								'type'        => 'string',
								'description' => __( 'Part of an email or product name.', 'devdiggers-waitlist-for-woocommerce' ),
							],
							'page'        => $page,
							'per_page'    => $per_page,
						]
					),
					'output'      => $this->ddwcwl_list_schema( 'subscribers' ),
					'callback'    => [ $this, 'ddwcwl_list_subscribers' ],
				],
				'settings'           => [
					'label'       => __( 'Waitlist settings', 'devdiggers-waitlist-for-woocommerce' ),
					'description' => __( 'The waitlist settings that decide who can join and when emails go out: on / off, login required, backorders, excluded products and categories, automatic restock emails and which emails are switched on. Use it to explain why a form does not show or why no email went out.', 'devdiggers-waitlist-for-woocommerce' ),
					'readonly'    => true,
					'input'       => $this->ddwcwl_input_schema( [] ),
					'output'      => $this->ddwcwl_object_schema( [ 'settings' => 'object' ] ),
					'callback'    => [ $this, 'ddwcwl_settings' ],
				],
				'restock-priorities' => [
					'type'        => 'prompt',
					'label'       => __( 'Restock priorities report', 'devdiggers-waitlist-for-woocommerce' ),
					'description' => __( 'Which sold out products to reorder first based on waitlist demand, which restocked products still have customers to email, and how signups are trending.', 'devdiggers-waitlist-for-woocommerce' ),
					'readonly'    => true,
					'input'       => $this->ddwcwl_input_schema(
						[
							'limit' => [
								'type'        => 'string',
								'description' => __( 'How many products to rank. Defaults to 10.', 'devdiggers-waitlist-for-woocommerce' ),
							],
						]
					),
					'output'      => $this->ddwcwl_object_schema( [ 'messages' => 'array' ] ),
					'callback'    => [ $this, 'ddwcwl_prompt_restock_priorities' ],
				],
			];

			// The hub keeps write tools off until the owner turns them on; without the hub there is no switch, so no write tool.
			if ( class_exists( 'DDFW_MCP' ) ) {
				$abilities['notify-product'] = [
					'label'       => __( 'Send back in stock emails', 'devdiggers-waitlist-for-woocommerce' ),
					'description' => __( 'Email everyone waiting for a product or variation that is back in stock, oldest signup first, in background batches: the "Email all" action on the Waitlist > Products screen. Only works when the product is published and in stock and has waiting customers; a variable product needs the variation ID. Customers who are emailed move to "notified".', 'devdiggers-waitlist-for-woocommerce' ),
					'readonly'    => false,
					'input'       => $this->ddwcwl_input_schema( [ 'product_id' => $id + [ 'description' => __( 'Product or variation ID from waitlist-list-products.', 'devdiggers-waitlist-for-woocommerce' ) ] ], [ 'product_id' ] ),
					'output'      => $this->ddwcwl_object_schema(
						[
							'product' => 'object',
							'queued'  => 'integer',
						]
					),
					'callback'    => [ $this, 'ddwcwl_notify_product' ],
				];

				$abilities['remove-subscriber'] = [
					'label'       => __( 'Remove a subscriber', 'devdiggers-waitlist-for-woocommerce' ),
					'description' => __( 'Take a customer off a product\'s waitlist, as their own unsubscribe link does: the entry is marked "unsubscribed", gets no more emails and leaves the queue, and stays in the history. The subscriber ID comes from waitlist-list-subscribers.', 'devdiggers-waitlist-for-woocommerce' ),
					'readonly'    => false,
					'input'       => $this->ddwcwl_input_schema( [ 'subscriber_id' => $id ], [ 'subscriber_id' ] ),
					'output'      => $this->ddwcwl_object_schema( [ 'subscriber' => 'object' ] ),
					'callback'    => [ $this, 'ddwcwl_remove_subscriber' ],
				];
			}

			/**
			 * Waitlist abilities for AI assistants (MCP). Add, change or remove one; names get the "waitlist/" prefix.
			 *
			 * @param array $abilities Ability definitions keyed by name.
			 */
			return apply_filters( 'ddwcwl_mcp_abilities', $abilities );
		}

		/**
		 * Input schema of an object; an empty call is allowed unless something is required.
		 *
		 * @param array $properties Properties.
		 * @param array $required   Required properties.
		 * @return array
		 */
		protected function ddwcwl_input_schema( $properties, $required = [] ) {
			$schema = [
				'type'                 => 'object',
				'additionalProperties' => false,
				// An object in JSON ( {} ), so MCP clients read it as "no arguments needed".
				'default'              => (object) [],
			];

			if ( $properties ) {
				$schema['properties'] = $properties;
			}

			if ( $required ) {
				$schema['required'] = $required;
				unset( $schema['default'] );
			}

			return $schema;
		}

		/**
		 * Output schema: an object with the given top-level property types.
		 *
		 * @param array $types Property => JSON type.
		 * @return array
		 */
		protected function ddwcwl_object_schema( $types ) {
			$properties = [];

			foreach ( $types as $key => $type ) {
				$properties[ $key ] = [ 'type' => $type ];
			}

			return [
				'type'       => 'object',
				'properties' => $properties,
			];
		}

		/**
		 * Output schema of a paged list.
		 *
		 * @param string $key List key.
		 * @return array
		 */
		protected function ddwcwl_list_schema( $key ) {
			return $this->ddwcwl_object_schema(
				[
					$key          => 'array',
					'total'       => 'integer',
					'total_pages' => 'integer',
					'page'        => 'integer',
				]
			);
		}

		/**
		 * Sort orders of the Products screen.
		 *
		 * @return string[]
		 */
		protected function ddwcwl_product_orderby() {
			return [ 'waiting', 'notified', 'total', 'last_signup' ];
		}

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Shared helpers.
		 * ---------------------------------------------------------------------------------------------
		 */

		/**
		 * A Y-m-d date from input, or the fallback.
		 *
		 * @param array  $input    Input.
		 * @param string $key      Key.
		 * @param string $fallback Fallback.
		 * @return string
		 */
		protected function ddwcwl_date( $input, $key, $fallback ) {
			$value = isset( $input[ $key ] ) ? (string) $input[ $key ] : '';
			$date  = \DateTime::createFromFormat( '!Y-m-d', $value );

			return $date && $date->format( 'Y-m-d' ) === $value ? $value : $fallback;
		}

		/**
		 * Page and per-page from input.
		 *
		 * @param array $input Input.
		 * @return array [ page, per_page ]
		 */
		protected function ddwcwl_paging( $input ) {
			return [
				max( 1, isset( $input['page'] ) ? absint( $input['page'] ) : 1 ),
				min( 100, max( 1, isset( $input['per_page'] ) ? absint( $input['per_page'] ) : 20 ) ),
			];
		}

		/**
		 * List response with paging numbers.
		 *
		 * @param string $key      List key.
		 * @param array  $items    Items.
		 * @param int    $total    Total items.
		 * @param int    $page     Page.
		 * @param int    $per_page Per page.
		 * @return array
		 */
		protected function ddwcwl_list_response( $key, $items, $total, $page, $per_page ) {
			return [
				$key          => array_values( $items ),
				'total'       => (int) $total,
				'total_pages' => (int) ceil( $total / $per_page ),
				'page'        => (int) $page,
			];
		}

		/**
		 * Percent change, null when the previous period had nothing.
		 *
		 * @param float $current  Current.
		 * @param float $previous Previous.
		 * @return float|null
		 */
		protected function ddwcwl_change( $current, $previous ) {
			return $previous > 0 ? round( ( $current - $previous ) / $previous * 100, 1 ) : null;
		}

		/**
		 * Whether a product is back in stock enough to email its waitlist (the notifier's own rule).
		 *
		 * @param \WC_Product $product Product.
		 * @return bool
		 */
		protected function ddwcwl_is_restocked( $product ) {
			return ( new DDWCWL_Notifier() )->is_restocked( $product );
		}

		/**
		 * Short product row.
		 *
		 * @param \WC_Product $product Product.
		 * @return array
		 */
		protected function ddwcwl_product_row( $product ) {
			$parent_id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();

			return [
				'id'             => $product->get_id(),
				'parent_id'      => $product->get_parent_id(),
				'name'           => $product->get_name(),
				'type'           => $product->get_type(),
				'sku'            => $product->get_sku(),
				'stock_status'   => $product->get_stock_status(),
				'stock_quantity' => $product->managing_stock() ? (int) $product->get_stock_quantity() : null,
				'edit_url'       => admin_url( 'post.php?post=' . $parent_id . '&action=edit' ),
			];
		}

		/**
		 * One subscriber row for AI clients. No token, IP or phone.
		 *
		 * @param object $row Subscriber row.
		 * @return array
		 */
		protected function ddwcwl_subscriber_row( $row ) {
			$product = wc_get_product( $row->product_id );
			$user    = $row->user_id ? get_userdata( $row->user_id ) : false;

			return [
				'id'             => (int) $row->id,
				'product_id'     => (int) $row->product_id,
				'product_name'   => $product ? $product->get_name() : '#' . $row->product_id,
				'email'          => $row->email,
				'customer_id'    => (int) $row->user_id,
				'customer_name'  => $user ? $user->display_name : '',
				'status'         => $row->status,
				'queue_position' => $this->subscribers->get_queue_position( $row ),
				'joined_at'      => $row->created_at,
				'notified_at'    => $row->notified_at,
			];
		}

		/**
		 * A product, or an error naming the tool that lists them.
		 *
		 * @param array $input Input.
		 * @return \WC_Product|\WP_Error
		 */
		protected function ddwcwl_find_product( $input ) {
			$product = ! empty( $input['product_id'] ) ? wc_get_product( absint( $input['product_id'] ) ) : false;

			if ( ! $product ) {
				return new \WP_Error( 'ddwcwl_product_not_found', __( 'Product not found. Use waitlist-list-products to find the product or variation ID.', 'devdiggers-waitlist-for-woocommerce' ), [ 'status' => 404 ] );
			}

			return $product;
		}

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Abilities.
		 * ---------------------------------------------------------------------------------------------
		 */

		/**
		 * Waitlist overview.
		 *
		 * @param array $input Input.
		 * @return array|\WP_Error
		 */
		public function ddwcwl_overview( $input = [] ) {
			$input  = (array) $input;
			$before = $this->ddwcwl_date( $input, 'before', current_time( 'Y-m-d' ) );
			$after  = $this->ddwcwl_date( $input, 'after', gmdate( 'Y-m-d', strtotime( $before . ' -29 days' ) ) );

			if ( $after > $before ) {
				return new \WP_Error( 'ddwcwl_invalid_range', __( 'The start date must be before the end date.', 'devdiggers-waitlist-for-woocommerce' ), [ 'status' => 400 ] );
			}

			// Same-length period right before, as on the dashboard.
			$days      = (int) round( ( strtotime( $before ) - strtotime( $after ) ) / DAY_IN_SECONDS ) + 1;
			$prev_to   = gmdate( 'Y-m-d', strtotime( $after . ' -1 day' ) );
			$prev_from = gmdate( 'Y-m-d', strtotime( $after . ' -' . $days . ' days' ) );
			$totals    = [];

			foreach ( [
				'signups'  => 'created_at',
				'notified' => 'notified_at',
			] as $key => $column ) {
				$current  = $this->subscribers->get_range_total( $column, $after, $before );
				$previous = $this->subscribers->get_range_total( $column, $prev_from, $prev_to );

				$totals[ $key ] = [
					'value'          => $current,
					'previous'       => $previous,
					'change_percent' => $this->ddwcwl_change( $current, $previous ),
				];
			}

			$most_wanted = [];

			foreach ( $this->subscribers->get_top_demanded( 5 ) as $row ) {
				$product = wc_get_product( $row->product_id );

				if ( $product ) {
					$most_wanted[] = $this->ddwcwl_product_row( $product ) + [ 'waiting' => (int) $row->waiting ];
				}
			}

			$ready    = [];
			$products = $this->subscribers->get_products_with_waiting();

			// ponytail: loads every waitlisted product once; fine for hundreds, join the stock lookup table for thousands.
			foreach ( $products as $product_id ) {
				$product = wc_get_product( $product_id );

				if ( $product && $this->ddwcwl_is_restocked( $product ) ) {
					$ready[] = $this->ddwcwl_product_row( $product ) + [ 'waiting' => $this->subscribers->count_waiting( $product_id ) ];
				}
			}

			return [
				'period'                => [
					'after'           => $after,
					'before'          => $before,
					'previous_after'  => $prev_from,
					'previous_before' => $prev_to,
				],
				'status_counts'         => $this->subscribers->get_status_counts(),
				'products_with_waiting' => count( $products ),
				'totals'                => $totals,
				'most_wanted'           => $most_wanted,
				'ready_to_notify'       => $ready,
			];
		}

		/**
		 * Waitlisted products.
		 *
		 * @param array $input Input.
		 * @return array
		 */
		public function ddwcwl_list_products( $input = [] ) {
			$input                   = (array) $input;
			list( $page, $per_page ) = $this->ddwcwl_paging( $input );
			$stock                   = isset( $input['stock'] ) && in_array( $input['stock'], [ 'out_of_stock', 'in_stock' ], true ) ? $input['stock'] : 'any';
			$args                    = [
				'search'  => isset( $input['search'] ) ? sanitize_text_field( $input['search'] ) : '',
				'orderby' => isset( $input['orderby'] ) && in_array( $input['orderby'], $this->ddwcwl_product_orderby(), true ) ? $input['orderby'] : 'waiting',
			];

			if ( 'any' === $stock ) {
				$result = $this->subscribers->get_product_summaries( $args + compact( 'page', 'per_page' ) );
				$rows   = $result['items'];
				$total  = $result['total'];
			} else {
				// ponytail: stock lives on the product, so filter the first 500 waitlisted products here; page in SQL with the stock lookup table if a store outgrows that.
				$rows  = array_filter(
					$this->subscribers->get_product_summaries( $args + [ 'per_page' => 500 ] )['items'],
					function ( $row ) use ( $stock ) {
						$product = wc_get_product( $row->product_id );

						return $product && ( 'in_stock' === $stock ) === $this->ddwcwl_is_restocked( $product );
					}
				);
				$total = count( $rows );
				$rows  = array_slice( $rows, ( $page - 1 ) * $per_page, $per_page );
			}

			$items = [];

			foreach ( $rows as $row ) {
				$product = wc_get_product( $row->product_id );
				$items[] = ( $product ? $this->ddwcwl_product_row( $product ) : [
					'id'   => (int) $row->product_id,
					'name' => __( '(deleted product)', 'devdiggers-waitlist-for-woocommerce' ),
				] ) + [
					'waiting'     => (int) $row->waiting,
					'notified'    => (int) $row->notified,
					'total'       => (int) $row->total,
					'last_signup' => $row->last_signup,
				];
			}

			return $this->ddwcwl_list_response( 'products', $items, $total, $page, $per_page );
		}

		/**
		 * One product's waitlist.
		 *
		 * @param array $input Input.
		 * @return array|\WP_Error
		 */
		public function ddwcwl_get_product( $input = [] ) {
			$product = $this->ddwcwl_find_product( (array) $input );

			if ( is_wp_error( $product ) ) {
				return $product;
			}

			$row = $this->ddwcwl_product_row( $product ) + [
				'accepts_signups' => $product->is_type( 'variable' ) ? (bool) DDWCWL_Products_Helper::get_eligible_variations( $product ) : DDWCWL_Products_Helper::is_eligible( $product ),
				'excluded'        => DDWCWL_Products_Helper::is_excluded( $product ),
				'ready_to_notify' => false,
			];

			$variations = [];
			$next       = [];

			if ( $product->is_type( 'variable' ) ) {
				foreach ( $product->get_children() as $child_id ) {
					$variation = wc_get_product( $child_id );

					if ( $variation ) {
						$counts       = $this->subscribers->get_status_counts( [ 'product_id' => $child_id ] );
						$variations[] = $this->ddwcwl_product_row( $variation ) + [
							'accepts_signups' => DDWCWL_Products_Helper::is_eligible( $variation ),
							'waiting'         => $counts['waiting'],
							'notified'        => $counts['notified'],
							'ready_to_notify' => $counts['waiting'] && $this->ddwcwl_is_restocked( $variation ),
						];
					}
				}
			} else {
				$row['ready_to_notify'] = $this->subscribers->count_waiting( $product->get_id() ) && $this->ddwcwl_is_restocked( $product );

				foreach ( $this->subscribers->get_waiting_batch( $product->get_id(), 10 ) as $subscriber ) {
					$next[] = $this->ddwcwl_subscriber_row( $subscriber );
				}
			}

			return [
				'product'         => $row,
				'status_counts'   => $this->subscribers->get_status_counts( [ 'product_id' => $product->get_id() ] ),
				'variations'      => $variations,
				'next_in_line'    => $next,
				'subscribers_url' => admin_url( 'admin.php?page=' . DDWCWL_DASHBOARD_PAGE . '&menu=subscribers&product_id=' . $product->get_id() ),
			];
		}

		/**
		 * Subscribers.
		 *
		 * @param array $input Input.
		 * @return array|\WP_Error
		 */
		public function ddwcwl_list_subscribers( $input = [] ) {
			$input                   = (array) $input;
			list( $page, $per_page ) = $this->ddwcwl_paging( $input );
			$email                   = isset( $input['email'] ) ? sanitize_email( $input['email'] ) : '';

			if ( ! empty( $input['customer_id'] ) ) {
				$user = get_userdata( absint( $input['customer_id'] ) );

				if ( ! $user ) {
					return new \WP_Error( 'ddwcwl_customer_not_found', __( 'Customer not found. Pass their email instead, or look the user ID up with the WooCommerce customer tools.', 'devdiggers-waitlist-for-woocommerce' ), [ 'status' => 404 ] );
				}

				$email = $user->user_email;
			}

			$result = $this->subscribers->query(
				[
					'status'     => isset( $input['status'] ) && 'any' !== $input['status'] ? sanitize_key( $input['status'] ) : '',
					'product_id' => isset( $input['product_id'] ) ? absint( $input['product_id'] ) : 0,
					'email'      => $email,
					'search'     => isset( $input['search'] ) ? sanitize_text_field( $input['search'] ) : '',
					'page'       => $page,
					'per_page'   => $per_page,
				]
			);

			return $this->ddwcwl_list_response( 'subscribers', array_map( [ $this, 'ddwcwl_subscriber_row' ], $result['items'] ), $result['total'], $page, $per_page );
		}

		/**
		 * Safe settings summary: an allow-list, never a dump of options.
		 *
		 * @return array
		 */
		public function ddwcwl_settings() {
			global $ddwcwl_waiting_list;

			$settings = [];

			foreach ( [ 'enabled', 'require_login', 'include_backorders', 'excluded_products', 'excluded_categories', 'mail_automatically', 'privacy_consent', 'show_subscriber_count', 'show_queue_position' ] as $key ) {
				$value            = $ddwcwl_waiting_list[ $key ] ?? '';
				$settings[ $key ] = 'yes' === $value ? true : ( '' === $value ? false : $value );
			}

			$settings['emails'] = [];

			foreach ( $ddwcwl_waiting_list['email_settings'] as $type => $email ) {
				$settings['emails'][ $type ] = 'yes' === $email['enabled'];
			}

			return [ 'settings' => $settings ];
		}

		/**
		 * Email everyone waiting for a restocked product: the "Email all" action of the Products screen.
		 *
		 * @param array $input Input.
		 * @return array|\WP_Error
		 */
		public function ddwcwl_notify_product( $input = [] ) {
			$product = $this->ddwcwl_find_product( (array) $input );

			if ( is_wp_error( $product ) ) {
				return $product;
			}

			$waiting = $this->subscribers->count_waiting( $product->get_id() );

			if ( $product->is_type( 'variable' ) ) {
				return new \WP_Error(
					'ddwcwl_variable_product',
					/* translators: %s: variation IDs. */
					sprintf( __( 'Customers wait for a variation. Use one of these variation IDs: %s', 'devdiggers-waitlist-for-woocommerce' ), implode( ', ', $product->get_children() ) ),
					[ 'status' => 400 ]
				);
			}

			if ( ! $waiting ) {
				return new \WP_Error( 'ddwcwl_nobody_waiting', __( 'Nobody is waiting for this product.', 'devdiggers-waitlist-for-woocommerce' ), [ 'status' => 400 ] );
			}

			if ( ! $this->ddwcwl_is_restocked( $product ) ) {
				return new \WP_Error( 'ddwcwl_not_in_stock', __( 'This product is not published and in stock, so a back in stock email would send customers to a sold out page. Restock it first.', 'devdiggers-waitlist-for-woocommerce' ), [ 'status' => 400 ] );
			}

			// Same call as "Email all" on the Products screen.
			if ( ! ( new DDWCWL_Notifier() )->maybe_queue( $product, true ) ) {
				return new \WP_Error( 'ddwcwl_not_queued', __( 'The emails could not be queued. Try "Email all" on the Waitlist > Products screen.', 'devdiggers-waitlist-for-woocommerce' ), [ 'status' => 500 ] );
			}

			// AI made changes leave a trail under WooCommerce > Status > Logs.
			wc_get_logger()->info(
				sprintf( 'Back in stock emails queued for %1$d waiting customers of product #%2$d by user #%3$d through MCP.', $waiting, $product->get_id(), get_current_user_id() ),
				[ 'source' => 'ddwcwl-mcp' ]
			);

			return [
				'product' => $this->ddwcwl_product_row( $product ),
				'queued'  => $waiting,
			];
		}

		/**
		 * Take a customer off a waitlist, as their unsubscribe link does.
		 *
		 * @param array $input Input.
		 * @return array|\WP_Error
		 */
		public function ddwcwl_remove_subscriber( $input = [] ) {
			$input      = (array) $input;
			$subscriber = $this->subscribers->get( isset( $input['subscriber_id'] ) ? absint( $input['subscriber_id'] ) : 0 );

			if ( ! $subscriber ) {
				return new \WP_Error( 'ddwcwl_subscriber_not_found', __( 'Subscriber not found. Use waitlist-list-subscribers to find the ID.', 'devdiggers-waitlist-for-woocommerce' ), [ 'status' => 404 ] );
			}

			$previous = $subscriber->status;

			if ( ! $this->subscribers->unsubscribe( $subscriber ) ) {
				/* translators: %s: subscriber status. */
				return new \WP_Error( 'ddwcwl_not_open', sprintf( __( 'This entry is already closed (status "%s"), so there is nothing to remove.', 'devdiggers-waitlist-for-woocommerce' ), $previous ), [ 'status' => 400 ] );
			}

			wc_get_logger()->info(
				sprintf( 'Waitlist entry #%1$d (product #%2$d) changed from %3$s to unsubscribed by user #%4$d through MCP.', $subscriber->id, $subscriber->product_id, $previous, get_current_user_id() ),
				[ 'source' => 'ddwcwl-mcp' ]
			);

			return [ 'subscriber' => $this->ddwcwl_subscriber_row( $this->subscribers->get( $subscriber->id ) ) ];
		}

		/**
		 * A prompt: one user message.
		 *
		 * @param string $description Description.
		 * @param string $text        Message.
		 * @return array
		 */
		protected function ddwcwl_prompt( $description, $text ) {
			return [
				'description' => $description,
				'messages'    => [
					[
						'role'    => 'user',
						'content' => [
							'type' => 'text',
							'text' => $text,
						],
					],
				],
			];
		}

		/**
		 * Restock priorities prompt.
		 *
		 * @param array $input Input.
		 * @return array
		 */
		public function ddwcwl_prompt_restock_priorities( $input = [] ) {
			$input = (array) $input;
			$limit = ! empty( $input['limit'] ) ? min( 50, max( 1, absint( $input['limit'] ) ) ) : 10;

			$text = sprintf(
				/* translators: %d: number of products. */
				__(
					'Write my restock priorities report from the waitlist.

Use the Waitlist tools:
1. waitlist-overview for the last 30 days: customers waiting, signups and emails against the previous period, most wanted products and products ready to notify.
2. waitlist-list-products with stock "out_of_stock", orderby "waiting", per_page %d.
3. waitlist-list-products with stock "in_stock" for restocked products that still have customers waiting.
4. waitlist-get-product for the top 3 sold out products, to see which variations people want.

Then write a short report for the store owner:
- Headline: customers waiting now and how signups are trending.
- Reorder first: the sold out products ranked by customers waiting, with variation detail where it matters.
- Waiting on you: products already back in stock with customers still waiting; suggest sending the back in stock emails (waitlist-notify-product, only if the owner agrees).
- Up to three actions for this week.
Keep it under 250 words, use the product names and IDs the tools return, and say plainly when nobody is waiting.',
					'devdiggers-waitlist-for-woocommerce'
				),
				$limit
			);

			return $this->ddwcwl_prompt( __( 'Restock priorities report', 'devdiggers-waitlist-for-woocommerce' ), $text );
		}
	}
}
