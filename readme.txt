=== Jane Web-Menu ===
Contributors: danaatiheartjane, andrija
Tags: jane, catalog, pos
Stable tag: 1.4.6
Requires at least: 5.9
Tested up to: 6.4.2
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin allows Jane customers to easily deploy Jane store menus into their websites.

== Description ==

[Jane](https://www.iheartjane.com) is an eCommerce company that operates in the US and Canada.

Our customers are stores, and our software connects to their POS systems, syncs in their product catalog, and creates an online storefront where end users can shop for products.
When a user makes a purchase, we push data back to the POS system so that the customer can either pick up the order in-store or get their order delivered.

This plugin allows our customers to easily deploy Jane menus into their websites

== Changelog ==

= 1.4.6 =
* Fixed: Permalink manager bug preventing editing menu configs

= 1.4.5 =
* Added: Smartcrawl support
* Added: Support hierarchical content types
* Added: Overwrite permalink manager
* Fixed: Bug with WP_Sitemaps_Provider implementation

= 1.4.4 =
* Intentionally skipped due to partner modifications

= 1.4.3 =
* Fixed: PHP 7.4 compatibility

= 1.4.2 =
* Added: Additional sitemap compatibility for Yoast plugin
* Added: Product meta data in header
* Fixed: Output from Yoast SEO removed

= 1.4.1 =
* Intentionally skipped due to partner modifications

= 1.4.0 =
* Intentionally skipped due to partner modifications

= 1.3.13 =
* Fixed: Don't add custom content to the page if it has already been added with shortcode

= 1.3.12 =
* Fixed: Squirrly SEO Plugin canonical url compatibility
* Fixed: Unnecessary query parameters removed from product canonical url
* Fixed: Shortcode only gets injected once

= 1.3.11 =
* Fixed: Rank Math Plugin canonical url compatibility
* Fixed: SEO Press Plugin canonical url compatibility

= 1.3.10 =
* Fixed: Fallback to the replacing the entire content if the shortcode is not present
* Fixed: Yoast specific canonical filter set

= 1.3.9 =
* Fixed: Synchronize deployment discrepancy

= 1.3.8 =
* Added: Inject a <meta> tag with the plugin version into the <head> tag

= 1.3.7 =
* Added: Wrap the <head> tag with Jane Menu Plugin

= 1.3.6 =
* Added: Warning message is now displayed in the admin, if other plugins are detected to be filtering 'get_canonical_url'
* Fixed: If current page/post is a Jane Menu, the full canonical_url is properly set, otherwise WordPress handles.

= 1.3.5 =
* Added: Support for any Custom Post Type
* Fixed: The shortcode [jane_menu_shortcode] is now the only way to output the body HTML, but can be used outside of the_content(), eg. with do_shortcode()

= 1.3.4 =
* Intentionally skipped due to partner modifications

= 1.3.3 =
* Added: The Sitemap index location in Settings
* Fixed: The Sitemap index gets generated even if the plugin is updated

= 1.3.2 =
* Added: Sitemap index with remote sitemap URLs generated in the uploads folder
* Added: The compatibility feature for Yoast where canonical URLs are not used on pages with store menus
* Fixed: The issue where sites with Elementor wouldn't display the menu

= 1.3.1 =
* Added: Store menu gets injected into the existing page instead of overwriting it
* Added: The shortcode [jane_menu_shortcode]

= 1.3.0 =
* First published version on WordPress.org
* Old plugin rewritten from scratch with a new structure encapsulated in namespaces
* Store paths are defined by selecting and existing Page instead of custom relative paths
