# DevDiggers Waitlist for WooCommerce

| | |
|---|---|
| Version | 1.0.0 |
| Requires | WordPress 6.9+, WooCommerce 8.0+, PHP 7.4+ |
| Tested up to | WordPress 7.1, WooCommerce 11.2 |
| License | GPLv3 or later |
| Pro | https://devdiggers.com/product/woocommerce-waitlist/ |
| Docs | https://docs.devdiggers.com/woocommerce-waitlist/ |

A back in stock waitlist for WooCommerce. Shoppers join from sold out products (simple or variable), see their place in line, and get an email when stock returns. Subscribers live in a custom table in your own database; emails go through the WooCommerce mailer.

## Features

- Signup form on out of stock products and variations, classic and block themes, plus a shortcode.
- Guests or logged in customers, optional login requirement, backorder support, product and category exclusions.
- Waiting count and place in line on the form.
- Consent checkbox, honeypot spam protection, one click unsubscribe, WordPress personal data export and erase.
- Automatic restock emails on any stock change, sent in background batches of 50 through Action Scheduler.
- Editable Back in Stock, Signup Confirmation and Admin Alert emails with preview and test send.
- Dashboard, Subscribers and Products screens, a Waitlist column on the Products list, and a My Account page.

### AI assistants (MCP)

Ask Claude, ChatGPT, Cursor, VS Code or any MCP client about your waitlists in plain words: "Which sold out products should I reorder first?", "Who is waiting for this shirt in size M?", "What waitlists did this customer join?"

- Tools for the waitlist overview (customers waiting, signups and emails against the previous period, most wanted products, restocked products with people still waiting), waitlisted products with stock status, one product's waitlist per variation, subscribers filtered by product, status or customer, and a safe settings summary
- A Restock priorities report prompt
- Optional: let the assistant send the back in stock emails for a restocked product or take a customer off a waitlist, logged under WooCommerce > Status > Logs (`ddwcwl-mcp`)
- Same access as the Waitlist screens (`manage_woocommerce`); customers and visitors are refused
- Managed in DevDiggers Plugins > AI & MCP: one MCP server for every DevDiggers plugin (`/wp-json/devdiggers/v1/mcp`), sign-in with OAuth (you click Allow in WordPress, nothing to copy), an on / off switch per plugin and per write tool, a Try panel on every tool and an activity log
- Needs WordPress 6.9+ (Abilities API) and the MCP Adapter, which WooCommerce 10.3+ bundles

Pro adds restock coupons, an early access window, reminder emails, shop page buttons, smart restock rules, restock dates, extra fields, double opt-in, revenue analytics, an email log, CSV import and export, webhooks and data retention. The free plugin contains none of that code; it only shows labelled previews.

## Setup

1. Activate WooCommerce, then this plugin.
2. Follow the setup wizard, or open **DevDiggers > Waitlist > Configuration**.

## Shortcode

```
[ddwcwl_waiting_list_template_shortcode]
[ddwcwl_waiting_list_template_shortcode product_id="123"]
```

## Hooks

| Hook | Type | Purpose |
|---|---|---|
| `ddwcwl_subscriber_added` | action | A customer joined a waitlist. Args: subscriber ID, product, email. |
| `ddwcwl_subscriber_notified` | action | A back in stock email was sent. Args: subscriber row, product. |
| `ddwcwl_is_product_eligible` | filter | Whether a product accepts signups. |
| `ddwcwl_message` | filter | Customer facing form messages. |
| `ddwcwl_modify_menu_capability` | filter | Capability for the admin screens (default `manage_woocommerce`). |
| `ddwcwl_mcp_abilities` | filter | Add, change or remove the AI assistant (MCP) tools, defined in `includes/integrations/mcp-integration.php` under `waitlist/`. |

Email templates can be overridden by copying `templates/emails/` to `yourtheme/ddwc-waiting-list/emails/`.

## Development

```bash
npm install
npm run build                 # webpack + text domain + POT
php bin/test-free-boundary.php
wp eval-file wp-content/plugins/devdiggers-waitlist-for-woocommerce/tests/smoke-test.php
npm run build:release         # zip without dev files (.distignore)
```

Source lives in `src/`, built files in `assets/`.

## Changelog

### 1.0.0
First release.

- Waitlist form on out of stock simple products and each sold out variation, in classic and block themes.
- Guest and logged in signups, with optional login requirement, backorder support and product or category exclusions.
- Waiting count before joining and place in line after joining.
- Automatic back in stock emails, sent oldest signup first in background batches of 50.
- Editable Back in Stock, Signup Confirmation and Admin Alert emails with preview and test send.
- Consent checkbox, invisible spam protection, one click unsubscribe, and WordPress personal data export and erase.
- Dashboard, Subscribers and Products screens, a Waitlist column on the Products list and a My Waiting List page in My Account.
- Setup wizard and the `[ddwcwl_waiting_list_template_shortcode]` shortcode.
- AI assistants (MCP): ask Claude, ChatGPT, Cursor, VS Code or any MCP client about your waitlists, with optional write tools that stay off until allowed.
- DevDiggers Plugins > AI & MCP page to connect clients, with per plugin and per tool switches and an activity log.
- Compatible with HPOS and the cart and checkout blocks.
