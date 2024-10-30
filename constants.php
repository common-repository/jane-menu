<?php
/**
 * All the constants used in the plugin are defined here.
 */

/**
 * The slug for the main admin page.
 * Store config list can be found there.
 * 
 * @since 1.0.0
 * 
 * @var string
 */
define( 'IHeartJane\WebMenu\Constants\ADMIN_PAGE_NAME', "jane-store-configs" );

/**
 * The custom database table name.
 * The table is used to store the configs.
 * 
 * @since 1.0.0
 * 
 * @var string
 */
define( 'IHeartJane\WebMenu\Constants\DB_TABLE_NAME', "jane_store_menu_config" );

/**
 * Custom option name for the Sitemap Enabled option.
 * 
 * @since 1.0.0
 * 
 * @var string
 */
define( 'IHeartJane\WebMenu\Constants\OPTION_SITEMAP_ENABLED_NAME', "jane_sitemap_enabled" );

/**
 * Full URL of the main plugin folder.
 * Has a trailing slash.
 * 
 * @since 1.0.0
 * 
 * @var string
 */
define( 'IHeartJane\WebMenu\Constants\PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Full path of the main plugin folder.
 * Has a trailing slash.
 * 
 * @since 1.0.0
 * 
 * @var string
 */
define( 'IHeartJane\WebMenu\Constants\PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Full path of the templates folder.
 * Has a trailing slash.
 * 
 * @since 1.3.0
 * 
 * @var string
 */
define( 'IHeartJane\WebMenu\Constants\TEMPLATES_DIR', plugin_dir_path( __FILE__ ) . 'templates/' );

/**
 * Algolia URL
 *
 * @since 1.4.0
 *
 * @var string
 */
define( 'IHeartJane\WebMenu\Constants\ALGOLIA_URL_TEMPLATE', 'https://VFM4X0N23A-dsn.algolia.net/1/indexes/menu-products-%s/query' );

/**
 * Algolia API ID
 *
 * @since 1.4.0
 *
 * @var string
 */
define( 'IHeartJane\WebMenu\Constants\ALGOLIA_API_ID', 'VFM4X0N23A' );

/**
 * Algolia API KEY
 *
 * @since 1.4.0
 *
 * @var string
 */
define( 'IHeartJane\WebMenu\Constants\ALGOLIA_API_KEY', 'cc7e0d77e8662c720e0fc48106fb6342' );
