=== DevDiggers Waitlist for WooCommerce ===
Contributors: devdiggers
Plugin URI: https://devdiggers.com/product/woocommerce-waitlist/
Author: DevDiggers
Author URI: https://devdiggers.com/
Tags: waitlist, back in stock, out of stock, stock notification, waiting list
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
WC requires at least: 8.0
WC tested up to: 11.2
Stable tag: 1.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Back in stock waitlist for WooCommerce. Shoppers join from sold out products, see their place in line and get an email the moment stock returns.

== Description ==

A sold out product page is a dead end. The shopper leaves, forgets about you, and buys somewhere else when the item is available again. You never find out how many people wanted it.

**DevDiggers Waitlist for WooCommerce** turns that page into a signup. A short "Notify me when it is back" form appears on every out of stock product, simple or variable. Shoppers leave their email, see how many people are waiting and their own place in line, and get a back in stock email as soon as you restock. You see exactly which products people are waiting for, so you know what to reorder first.

Everything runs inside your store. Subscribers are saved in your WordPress database and emails go out through the WooCommerce mailer, so they match your other store emails. No account, no API key and no monthly fee.

= Quick links =

* [Documentation](https://docs.devdiggers.com/woocommerce-waitlist/)
* [Support](https://wordpress.org/support/plugin/devdiggers-waitlist-for-woocommerce/)
* [Upgrade to Pro](https://devdiggers.com/product/woocommerce-waitlist/)

= How the waitlist works =

1. A product sells out. The waitlist form appears on its page automatically, in classic and block themes.
2. A shopper enters their email (logged in customers just click the button) and is told their place in line. A signup confirmation email follows.
3. You add stock in WooCommerce, by hand, through an import, the REST API, a refund or a cancelled order.
4. The plugin emails everyone waiting in the background, first come first served, in batches of 50 so a long list never slows your store.

= What the free plugin does =

=== Signup form on sold out products ===

* Works on simple products and on each variation of a variable product. Pick a sold out size or colour and the form switches to it.
* Guests sign up with their email. Logged in customers join with one click.
* Optionally require login, for B2B or member stores.
* Optionally show the form on backorder products too.
* Exclude individual products or whole categories.
* Shows how many people are waiting ("12 people are waiting for this product").
* Tells each shopper their place in line after they join.
* Every text is editable: title, description, button, placeholder, reassurance note and the confirmation message.
* Button colours, a header icon and your own message style.
* A shortcode to place the form on any page or custom product template.
* The form still works when JavaScript is turned off.

=== Privacy and spam protection ===

* An optional consent checkbox with your own text, so you can link your privacy policy.
* Invisible spam protection on every form.
* A one click unsubscribe link in every customer email.
* WordPress personal data export and erasure (**Tools > Export / Erase Personal Data**) include waitlist entries.

=== Restock emails ===

* Waiting customers are emailed automatically when a product or variation comes back in stock. You can switch this off and send them yourself.
* Three emails, each with its own on/off switch and an editable subject, heading and content: **Back in Stock**, **Signup Confirmation** and an **Admin Alert** for every new signup.
* Placeholders such as {customer_name}, {product_name}, {product_price}, {queue_position} and {waiting_count}.
* Preview any email and send a test copy from the settings screen.
* Emails use your WooCommerce email header, footer and colours, with a product card and a shop button.

=== See the demand ===

* A dashboard with customers waiting now, new signups, emails sent and products in demand, compared with the previous period.
* An activity chart, the most wanted products, in stock products that still have people waiting, and the latest signups.
* A **Subscribers** screen to search, filter by product or status, add a subscriber by hand, send the email again or remove entries.
* A **Products** screen listing every product with a waitlist, with one click to email everyone waiting or clear the list.
* A **Waitlist** column on the WooCommerce Products screen.
* A **My Waiting List** page in My Account where customers see and leave their waitlists.

= Pro features =

[Pro](https://devdiggers.com/product/woocommerce-waitlist/) adds the tools that turn more of those signups into orders. In the free plugin these show as clearly labelled Pro previews and do nothing.

=== Recover more sales ===

* **Unique restock coupons**: a single use discount in every back in stock email, locked to that customer and product.
* **Early access window**: reserve the restocked product for people who waited, for the hours you choose.
* **Reminder email**: a second nudge a few days later for customers who have not bought yet.
* **Notify Me on shop pages**: a button on shop, category and block product grids that opens a quick signup window.

=== Control restocks ===

* **Smart restock rules**: a minimum stock level, a number of customers per unit that comes back, and your own batch size.
* **Expected restock date** and a note per product or variation, shown on the form.
* **Extra fields**: units wanted and phone number.
* **Double opt-in** for guests, with automatic cleanup of unconfirmed signups.

=== Measure and connect ===

* **Recovered revenue analytics**: orders and revenue won back by restock emails, with a sent to clicked to purchased funnel.
* **Email log** of every waitlist email with its delivery result.
* **CSV import and export** of subscribers.
* **Webhooks** for Zapier, Make, n8n or your CRM.
* **Automatic data retention** cleanup.

== Installation ==

1. In your WordPress admin go to **Plugins > Add New Plugin** and search for "DevDiggers Waitlist".
2. Install and activate it. WooCommerce must be active.
3. A short setup wizard opens. Pick your options or skip it.
4. Settings live under **DevDiggers > Waitlist > Configuration**.

== Frequently Asked Questions ==

= Does it work with variable products? =

Yes. Each sold out variation has its own waitlist. When a shopper picks a sold out variation, the form appears for that variation only.

= When are the back in stock emails sent? =

As soon as the product or variation is in stock again, whatever changed the stock: the product editor, an import, the REST API, a refund or a cancelled order. Emails go out in the background through Action Scheduler, 50 at a time, oldest signup first.

= Can I send the emails myself instead? =

Yes. Turn off **Automatic Emails** under **Configuration > Restock Rules**, then use **Email waiting customers** on the Products screen whenever you are ready.

= Can guests join, or only logged in customers? =

Both by default. Turn on **Require Login** under **Configuration > General** to allow logged in customers only.

= Can I edit the emails? =

Yes. Under **Emails**, each email has its own switch, subject, heading and content with placeholders. You can preview it and send yourself a test.

= The form does not show on my product page. =

It shows only while a product or variation is out of stock (or on backorder, if you allow that), and not for excluded products. If your theme builds product pages without the standard WooCommerce hooks or blocks, place the shortcode `[ddwcwl_waiting_list_template_shortcode]` in your template.

= Is it GDPR friendly? =

Subscribers stay in your own database. You can require a consent checkbox, every email has a one click unsubscribe link, and waitlist entries are included in the WordPress personal data export and erase tools.

= Does it work with HPOS and the block checkout? =

Yes. The plugin declares compatibility with High Performance Order Storage and the cart and checkout blocks.

= Will I lose my subscribers if I upgrade to Pro? =

No. Pro uses the same subscribers table and settings, so everything carries over.

== Screenshots ==

1. The waitlist form on a sold out product, with the waiting count.
2. The confirmation with the shopper's place in line.
3. The dashboard with signups, emails sent and the most wanted products.
4. The Subscribers screen.
5. The Products screen with one click restock emails.
6. Editing the Back in Stock email, with preview and test send.
7. Signup form settings.
8. My Waiting List in My Account.

== External services ==

The plugin itself sends no data to any outside service. Subscribers are stored in your WordPress database and emails go out through the WooCommerce mailer. It contains no licence checks, no update checks and no telemetry.

**1. Gravatar (avatar images)**

* What it is: Gravatar, the avatar service operated by Automattic, used through WordPress core's `get_avatar_url()`.
* What it is used for: Showing the logged in administrator's avatar in the plugin dashboard header. This is standard WordPress behaviour.
* When data is sent: When the dashboard is viewed and avatars are enabled under **Settings > Discussion**. Turning avatars off there stops the requests.
* What data is sent: A hash of the administrator's email address inside the image URL, plus the usual request data of the browser such as IP address and user agent.
* Terms of Service: https://automattic.com/terms/
* Privacy Policy: https://automattic.com/privacy/

The bundled DevDiggers framework, which draws the admin screens, connects to the two services below. Both run only inside the WordPress admin.

**2. DevDiggers extensions directory**

* What it is: A read only API on devdiggers.com that returns the public list of DevDiggers extensions.
* What it is used for: Showing available DevDiggers extensions on the Extensions admin page.
* When data is sent: Only when a logged in administrator opens the Extensions admin page. The response is cached for 24 hours.
* What data is sent: A standard outbound HTTP request only, meaning your server's IP address and a plugin user agent string. No personal data and no store data are sent. The page also loads each listed extension's image from devdiggers.com in the administrator's browser.
* Endpoint: https://devdiggers.com/wp-json/ddwcs/v1/plugins

**3. Newsletter subscription (optional)**

* What it is: A contact and newsletter endpoint on devdiggers.com.
* What it is used for: Adding your email address to the DevDiggers newsletter, only if you choose to subscribe.
* When data is sent: Only when an administrator submits the optional newsletter form in the plugin dashboard. Nothing is sent automatically.
* What data is sent: The email address you enter and your site URL.
* Endpoint: https://devdiggers.com/?fluentcrm=1&route=contact

The DevDiggers services are provided by DevDiggers. By using them you agree to the DevDiggers Terms and Conditions (https://devdiggers.com/terms-and-conditions/) and Privacy Policy (https://devdiggers.com/privacy-policy/).

== Source code and build tools ==

The admin and storefront JavaScript and CSS in `assets/` are compiled from the human readable source in the `src/` folder, which ships with the plugin together with the build configuration.

To build the assets from source:

1. Run `npm install` to install the build dependencies listed in `package.json`.
2. Run `npm run build` to compile the production assets into `assets/js` and `assets/css`.

The build configuration is in `webpack.config.js` and `babel.config.js`. Two third party libraries used by the admin screens ship unmodified under the MIT licence inside `devdiggers-framework/assets/js/`: Chart.js (https://www.chartjs.org) and Select2 (https://select2.org).

== Changelog ==

= 1.0.0 =
* New: Waitlist form on out of stock simple products and each sold out variation, in classic and block themes.
* New: Guest and logged in signups, with optional login requirement, backorder support and product or category exclusions.
* New: Waiting count before joining and place in line after joining.
* New: Automatic back in stock emails, sent oldest signup first in background batches of 50.
* New: Editable Back in Stock, Signup Confirmation and Admin Alert emails with preview and test send.
* New: Consent checkbox, invisible spam protection, one click unsubscribe, and WordPress personal data export and erase.
* New: Dashboard, Subscribers and Products screens, a Waitlist column on the Products list and a My Waiting List page in My Account.
* New: Setup wizard and the `[ddwcwl_waiting_list_template_shortcode]` shortcode.
* New: Compatible with HPOS and the cart and checkout blocks.

== Upgrade Notice ==

= 1.0.0 =
First release of DevDiggers Waitlist for WooCommerce.
