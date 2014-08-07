=== Cart66 Cloud :: Ecommerce with security ===
Contributors: reality66,
Donate link: http://cart66.com
Tags: ecommerce, e-commerce, shopping, cart, store, cart66, download, digital, downloadable, sell, inventory, shipping, tax, donations products, sales, shopping cart, cloud, sky
Requires at least: 3.2
Tested up to: 3.9.2
Stable tag: 1.7.3
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Cart66 Cloud gives you everything you need for a secure and PCI compliant store including recurring billing. You don't even need an SSL cert.

== Description ==

Cart66 Cloud let's you sell anything and handles all the details of keeping your store secure. Sell digital files, ship products, and even sell memberships and subscriptions. Cart66 Cloud includes a built-in recurring billing engine and support for 50 payment gateways. Membership management features to restrict access to content on your site is included too! Unlike other e-commerce plugins where you have to buy add-ons and worry about SSL certificates and security scans, Cart66 Cloud gives you everything you need all in one easy to use package. We even handle all of the [requirements for PCI compliance](http://cart66.com/blog/what-you-need-to-know-about-pci-compliance/ "PCI Compliance") for you so you don't have to do anything but start selling.

[vimeo http://vimeo.com/83884705]

= Features =

Cart66 Cloud is the most secure way to sell with WordPress and provides a full set of e-commerce features including:

- Built-in recurring billing engine that can be used with all supported gateways
- Customer accounts with order history, stored credit cards, and address book
- Built-in HTML email center to send follow up emails to your customers
- Sell digital files and we'll securely host and deliver them for you
- Sell physical products
- Create products with unlimited variations
- Tons of payment gateways included (50 gateways in 71 countries and counting...)
- Your secure pages are skinned with your WordPress theme so they look exactly like the rest of your WordPress site
- Taxes
- Shipping
- Coupons
- Promotions
- Order management and fulfillment
- All the security you need (SSL, PCI, etc.)
- and more...

== Screenshots ==
1. Products: No matter what type of products you want to sell, we’ve got you covered. No extra extensions or add-ons needed.
2. Product details: unlimited variations, attach files, set custom text for receipts and much more...
3. Digital products: Upload and attach one or more files to a product. We'll securely store and deliver your files too!
4. Live carts: Watch people shop in real-time
5. Subscription management: Get a quick look at which memberships and subscriptions are expiring or past due. Edit orders, resend email receipts and more!
6. Customer communication: Drip content, send follow up emails, send special coupons after buying specific products all with beautiful HTML emails!
7. Customer portal: Allow your customers to sign in and see their order history, manage their subscriptions and update their saved credit cards.

== Installation ==

= Minimum Requirements =

* WordPress 3.2 or greater
* PHP version 5.2.4 or greater
* MySQL version 5.0 or greater

= Automatic installation =

You can install Cart66 Cloud directly from the WordPress.org plugin directory without having to worry with manually transferring files to your server. Automatic installation is the easiest option for installation. Let WordPress install Cart66 Cloud for you.

To install Cart66 Cloud, log in to your WordPress admin panel, go to the Plugins menu and click the "Add New" link.

Type "Cart66 Cloud" in the search field then click the "Search Plugins" button. You will see the Cart66 Cloud plugin in the search results. To install Cart66 Cloud simply click the "Install Now" link. After confirming that you do want to install the plugin, WordPress will automatically download and install Cart66 Cloud directly on your WordPress website.

Once the Cart66 Cloud plugin is installed, you may want to visit [our tutorials](http://cart66.com/cloud-docs/) for more information on getting started.

= Manual installation =

If you would prefer to manually install Cart66 Cloud, you will need to download the Cart66 Cloud plugin and upload it to your web server via FTP. Here are the steps to follow:

1. Download and unzip the plugin file on your computer
2. Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to the plugin directory on your WordPress site (wp-content/plugins/<cart66-cloud>).
3. Activate the Cart66 Cloud plugin from the "Plugins" menu in your WordPress admin panel.

= Upgrading =

When new versions of Cart66 Cloud are released, WordPress will notify you about the available update. You can update to the latest version of the Cart66 Cloud plugin by selecting to do an automatic updates. It is always a good idea to backup your site before doing any updates of any kind including updating plugins.

Cart66 Cloud will remember all of your settings and products after updating so you will not have to re-enter your product information or anything else.

== Frequently Asked Questions ==

= Where can I find Cart66 Cloud documentation? =

You will find detailed instructions in our [Cart66 Cloud documentation](http://cart66.com/cloud-docs). We also have a growing list of [video tutorials](http://cart66.com/cloud-videos/). Checkout [our blog](http://cart66.com/blog) for news and other updates.

= What is PageSlurp? =

PageSlurp is the technology that we invented to provide a secure and PCI compliant way for us to run your WordPress them on our secure servers so that all your secure pages look just like the rest of your website. Basically, we invented an easy, one-click way to let you skin your secure pages with your WordPress theme.

= What is PCI compliance? =

PCI Compliance is a set of requirements that your business needs to meet in order to accept credit card payments. If you are using Cart66 Cloud for your e-commerce, then your website is PCI compliant. If you are not using Cart66 Cloud then you need to handle all of the PCI requirements on your own. Becoming PCI compliant on your own is very expensive and challenging. For more information about PCI compliance, see [What you need to know about PCI compliance](http://cart66.com/blog/what-you-need-to-know-about-pci-compliance/ "PCI Compliance").

== Changelog ==

= 1.7.3 - 8/7/2014 =

- New: Searchable product drop down menu
- New: Secure console admin menu
- Updated: Views to utilize classes instead of IDs
- Fixed: Intermittent issue with NGINX servers
- Fixed: Remote get timeout for API calls

= 1.7.2 - 2/12/2014 =

- Fixed: Ability to synchronize the secure subdomain in the Cart66 Cloud plugin settings page

= 1.7.1 - 2/11/2014 =

- New: Significant performance improvements
- New: Added hook for for loading the receipt page
- New: Added PHP helper function to programmatically retrieve order data
- New: Embedded the secure Cart66 Cloud console in the WordPress plugin so you can do everything without ever leaving WordPress.
- Updated: Logging in to the secure customer portal now redirects the visitor to the member home pages as defined in the WordPress plugin settings.
- Updated: Improved synchronization between the logged in status between the secure cloud and WordPress.
- Fixed: Stop duplicating physical page slurp template when changing page slurp mode back to virtual page

= 1.7.0 - 2/4/2014 =

- New: Added physical page slurp mode to improve compatibility with more plugins and themes
- New: Added new PHP [API functions](https://cart66.zendesk.com/entries/26435755-PHP-Helper-Functions) to get user data for the logged in visitor
- New: Added new [shortcodes](https://cart66.zendesk.com/entries/23447965-Cart66-Cloud-Shortcodes) to access data about the logged in visitor

= 1.6.9 - 1/31/2014 =

- Fixed: Receipt URL redirection failing when WordPress is installed in a subdirectory
- Updated: Improved sign in and sign out links for cases where the WordPress Address and Site Address URLs are different
- Updated: Improvements to keep the logged in status of members in sync between the secure cloud and WordPress
- Updated: Performance improvements to reduce the number of empty shopping carts that get created
- Updated: New CSS to improve the display of the "product added to cart" notification

= 1.6.8 - 1/14/2014 =

- Fixed: Custom page slurp templates can now be found in child themes

= 1.6.7 - 12/31/2013 =

- New: Added cc_product_price shortcode
- Fixed: Dynamic price updates work with server side loaded as well as client side loaded product forms
- Fixed: Add to cart links generating PHP notices prevent redirect to checkout page
- Fixed: Text field variation values that include speical characters like quotes are no longer escaped with slashes

= 1.6.6 - 10/24/2013 =

- New: Added debugging tool to test for server's PHP settings for cURL requests
- Updated: Changed the default colors for the AJAX success message after adding a product to the cart to gray instead of mint green
- Updated: Added "Not set" status message when the store subdomain is not yet cached in the WordPress database
- Updated: Improve javascript compatability for IE8
- Updated: Adding additional translation echo functions
- Updated: Cleaned up PHP notices

= 1.6.5 - 9/9/2013 =

- New: Added API call to retrieve all memberships and subscriptions along with their corresponding statuses (active, expired, canceled)
- New: CSS classes added to secure cloud content for easier optimization of secure pages
- Fixed: Canceling a membership now immediately denies access to content that requires that membership

= 1.6.4 - 9/4/2013 =

- Updated: API call optimization dramatically reducing the number of API calls per page load
- Updated: Performance improvements and speed enhancements

= 1.6.3 - 8/30/2013 =

- Updated: Improved perfomance by storing custom subdomain in the WordPress database
- Updated: Option to use server side or client side product loading. Client side product loading is faster but requires javascript.

= 1.6.2 - 8/26/2013 =

- Fixed: One-time snippets failing to load for some sites.

= 1.6.1 - 8/15/2013 =

- Updated: Product shortcodes can be nested in cc_show_to and cc_hide_from shortcodes
- Fixed: Problem with product order forms not loading in IE <= 9
- Fixed: Product order form validation errors not always showing up depending on add to cart redirect mode


= 1.6.0 - 8/6/2013 =

- New: [Post category restriction](http://cart66.com/cloud-docs/restrict-access-to-post-categories/)
- New: Set [access denied page for restricted Pages](http://cart66.com/cloud-docs/content-restriction-for-pages-vs-posts/)
- Update: Major speed enhancements making pages load over 10x faster especially when multiple products are on the same page
- Update: Speed improvements by significantly reducing in the number of callbacks to the Cart66 Cloud 
- Fixed: Cleaning up a bunch of PHP notices when running in debug/strict mode

= 1.5.3 - 7/3/2013 =

- New: Added support for Cart66 Sky Managed WordPress e-commerce hosting platform
- Fixed: Content restriction short codes updated to work with longer visitor tokens
- Update: Refresh the shopping cart after a successful sale
- Update: Code organization improvements

= 1.1.1 - 2/21/2013 =

- Fixing WP_Error during page slurp caused by some themes attempting to discover the post category for the page slurp template

= 1.1 - 2/8/2013 =

- Adding support for custom subdomains
- Updating CSS for more control over styling
- Adding additional internal features to support theme development

= 1.0.6 - 1/7/2013 =

- The AJAX add to cart notification now contains a view cart button
- The AJAX add to cart notification now says the name of the product that was added to the cart

= 1.0.5 - 12/30/2012 =

- Sending location data when creating carts. When viewing live carts in your Cart66 Cloud account you can now see where your customers are located in the world.

= 1.0.4 - 12/04/2012 =

- Fixing PHP warnings on Cart66 Cloud settings page caused by failure to retrieve alternate page templates

= 1.0.3 - 11/30/2012 =

- Adding the ability to add products to the cart via AJAX
- Adding setting for turning debug logging on and off
- Fixing error message about SSL certificate validation failing on some servers

= 1.0.2 - 11/27/2012 =

- Fixing problem where SSL verification would fail on some servers preventing successful api communication

= 1.0.1 - 11/26/2012 =

- Updating Cart66 Cloud API URLs to use SSL

= 1.0 - 11/01/2012 =

- Initial release of the Cart66 Cloud plugin for WordPress e-commerce

== Upgrade Notice ==

= 1.0 =

1.0 is the initial release of the Cart66 Cloud plugin for WordPress e-commerce
