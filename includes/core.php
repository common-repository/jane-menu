<?php
/**
 * Core
 */

namespace IHeartJane\WebMenu\Core;

use IHeartJane\WebMenu\Constants;
use IHeartJane\WebMenu\DB;
use IHeartJane\WebMenu\Providers\JaneSitemapProvider;
use IHeartJane\WebMenu\Providers\YoastJaneSitemapProvider;
use IHeartJane\WebMenu\StoreConfigs;
use IHeartJane\WebMenu\Sitemap;
use IHeartJane\WebMenu\Helpers;

/**
 * Triggers the database table update for old structure versions
 * 
 * @uses     IHeartJane\WebMenu\Constants\PLUGIN_VER                        Constants\PLUGIN_VER
 * @uses     IHeartJane\WebMenu\DB\create_or_update_config_table()          DB\create_or_update_config_table()
 * @uses     IHeartJane\WebMenu\Sitemap\update_remote_sitemap()             Sitemap\update_remote_sitemap()
 * 
 * @see      https://developer.wordpress.org/reference/hooks/admin_init/    Codex, action: admin_init
 * 
 * @since    1.3.0
 * 
 * @return   void
 */
function check_db_version(){
    
    $db_ver = get_option( 'jane_web_db_version', '0.0.1' );
    
    // Helpers\debug_log( $db_ver, "db-check_db_version-running" );
    
    if( version_compare( $db_ver, Constants\PLUGIN_VER, '<' ) ){
        
        // Helpers\debug_log( Constants\PLUGIN_VER, "db-check_db_version-updating" );
        
        DB\create_or_update_config_table();
        Sitemap\update_remote_sitemap();
    }
}
add_action( 'admin_init', __NAMESPACE__ . '\\' . 'check_db_version' );

/**
 * Runs on activation of the plugin
 * 
 * @uses     IHeartJane\WebMenu\Constants\PLUGIN_ID                     Constants\PLUGIN_ID
 * @uses     IHeartJane\WebMenu\Constants\OPTION_SITEMAP_ENABLED_NAME   Constants\OPTION_SITEMAP_ENABLED_NAME
 * 
 * @see      https://developer.wordpress.org/reference/functions/add_option/    Codex, function: add_option
 * 
 * @since    1.0.0
 * @since    1.3.0  Cleaned
 * 
 * @return   void
 */
function activate_plugin(){
    
    // add_option does not update existing options
    add_option( Constants\OPTION_SITEMAP_ENABLED_NAME, true );
    
}
register_activation_hook( Constants\PLUGIN_ID, __NAMESPACE__ . '\\' . 'activate_plugin' );

/**
 * Runs on deactivation of the plugin
 * 
 * @uses     IHeartJane\WebMenu\Constants\PLUGIN_ID                     Constants\PLUGIN_ID
 * 
 * @since    1.0.0
 * @since    1.3.0  Not actually used
 * 
 * @return   void
 */
function deactivate_plugin(){
    
}
register_deactivation_hook( Constants\PLUGIN_ID, __NAMESPACE__ . '\\' . 'deactivate_plugin' );

/**
 * Runs on uninstallation of the plugin
 * 
 * @uses     IHeartJane\WebMenu\Constants\PLUGIN_ID                     Constants\PLUGIN_ID
 * @uses     IHeartJane\WebMenu\Constants\OPTION_SITEMAP_ENABLED_NAME   Constants\OPTION_SITEMAP_ENABLED_NAME
 * @uses     IHeartJane\WebMenu\DB\drop_config_table()                  DB\drop_config_table()
 * 
 * @since    1.3.0
 * 
 * @return   void
 */
function uninstall_plugin(){
    
    delete_option( 'jane_web_db_version' );
    delete_option( Constants\OPTION_SITEMAP_ENABLED_NAME );
    
    DB\drop_config_table();
}
register_uninstall_hook( Constants\PLUGIN_ID, __NAMESPACE__ . '\\' . 'uninstall_plugin' );


/**
 * Creates new sidebar menu page and subpages
 * 
 * @uses     IHeartJane\WebMenu\Constants\ADMIN_PAGE_NAME               Constants\ADMIN_PAGE_NAME
 * @uses     IHeartJane\WebMenu\Core\load_plugin_page()                 Core\load_plugin_page()
 * 
 * @see      https://developer.wordpress.org/reference/hooks/admin_menu/    Codex, action: admin_menu
 * 
 * @since    1.0.0
 * 
 * @return   void
 */
function admin_menu(){
    
    add_menu_page( 
        __( 'Jane Settings', 'iheartjane' ),
        __( 'Jane Settings', 'iheartjane' ),
        'manage_options',
        Constants\ADMIN_PAGE_NAME,
        __NAMESPACE__ . '\\' . 'load_plugin_page'
    );
    
    add_submenu_page(
        Constants\ADMIN_PAGE_NAME,
        __( 'Store Configs', 'iheartjane' ),
        __( 'Store Configs', 'iheartjane' ),
        'manage_options',
        Constants\ADMIN_PAGE_NAME,
        __NAMESPACE__ . '\\' . 'load_plugin_page'
    );
    
    add_submenu_page(
        Constants\ADMIN_PAGE_NAME,
        __( 'Add New', 'iheartjane' ),
        __( 'Add New', 'iheartjane' ),
        'manage_options',
        Constants\ADMIN_PAGE_NAME . '&action=new',
        __NAMESPACE__ . '\\' . 'load_plugin_page'
    );
    
}
add_action( 'admin_menu', __NAMESPACE__ . '\\' . 'admin_menu' );

/**
 * Handles the template loading for our admin pages
 * 
 * @uses     IHeartJane\WebMenu\Constants\TEMPLATES_DIR                 Constants\TEMPLATES_DIR
 * 
 * @since    1.0.0
 * 
 * @return   void
 */
function load_plugin_page(){
    
    $id = ! empty( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
    
    if( ! empty( $_GET['action'] ) && in_array( $_GET['action'], [ 'view', 'edit', 'new' ] ) ){
        
        include Constants\TEMPLATES_DIR . 'store-config-edit.php';
        
    } else {
        
        include Constants\TEMPLATES_DIR . 'store-config-list.php';
    }
    
}

/**
 * Enqueues JS and CSS files in the admin area
 * 
 * @uses     IHeartJane\WebMenu\Helpers\enqueue_CSS() Helpers\enqueue_CSS()
 * @uses     IHeartJane\WebMenu\Helpers\enqueue_JS()  Helpers\enqueue_JS()
 * 
 * @since    1.3.0
 * 
 * @return   void
 */
function enqueue_scripts(){
    
    $screen = get_current_screen();
    
    // Helpers\debug_log( $screen, "enqueue_scripts-debug_screen" );
    
    if( $screen->base === 'toplevel_page_jane-store-configs' ){
        
        $jane_object = [
            'admin_url' => admin_url(),
            'ajax_url'  => admin_url('admin-ajax.php'),
        ];
        
        if( ! empty( $_GET['action'] ) && in_array( $_GET['action'], [ 'view', 'edit', 'new' ] ) ){
            
            Helpers\enqueue_CSS( 'jane-style',  'css/store_config-edit.css' );
            Helpers\enqueue_JS(  'jane-script', 'js/store_config-edit.js'   );
        
            wp_localize_script(  'jane-script', 'jane_object', $jane_object );
            
        } else {
            
            Helpers\enqueue_CSS( 'jane-style',  'css/store_config-list.css' );
            Helpers\enqueue_JS(  'jane-script', 'js/store_config-list.js'   );
        }
        
    }
    
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\' . 'enqueue_scripts' );

function jane_register_sitemap_providers(){

    $provider = new JaneSitemapProvider();

    wp_register_sitemap_provider( $provider->name, $provider );
}
add_filter( 'init', __NAMESPACE__ . '\\' . 'jane_register_sitemap_providers' );

function add_jane_sitemap_provider_to_yoast($providers){

    $providers[] = new YoastJaneSitemapProvider();

    return $providers;
}
add_filter( 'wpseo_sitemaps_providers', __NAMESPACE__ . '\\' . 'add_jane_sitemap_provider_to_yoast');

/**
 * Removes output from Yoast SEO and SmartCrawl on the frontend
 *
 * @since 1.4.0
 *
 * @return void
 */
function remove_wpseo(): void
{

	if(function_exists('YoastSEO')) {

		execute_code_block_on_products_page(function() {
			// Removing yoast seo metatags on jane-menu product pages
			$front_end = YoastSEO()->classes->get( \Yoast\WP\SEO\Integrations\Front_End_Integration::class );
			remove_action( 'wpseo_head', [ $front_end, 'present_head' ], -9999 );
		});
	}

	if(class_exists('\SmartCrawl\Controllers\OnPage')) {

		execute_code_block_on_products_page(function(){
			// Removing SmartCrawl seo metatags on jane-menu product pages
			$smartCrawl = \SmartCrawl\Controllers\OnPage::get();
			remove_action( 'wp_head', [ $smartCrawl, 'smartcrawl_head' ], 10 );
		});
	}
}
add_action( 'template_redirect', __NAMESPACE__ . '\\' . 'remove_wpseo' );

/**
 * Execute callback on products page
 *
 * @since 1.4.4
 */
function execute_code_block_on_products_page($callback) {

	$current_store = StoreConfigs\Current_Store_Config::get_instance();
	if ($current_store->get_config() && isset($current_store->get_config()->store_path)) {

		// Checking if product url starting with configured store_path from admin panel
		$search_path = sprintf('/%s/products/', $current_store->get_config()->store_path);
		if (strpos($_SERVER['REQUEST_URI'], $search_path) === 0) {
			$callback();
		}
	}
}
add_action( 'template_redirect', __NAMESPACE__ . '\\' . 'remove_wpseo' );

/**
 * DISABLE URL OVERRIDE IF CONFIG IS SET ON THAT PAGE ID
 *
 * @since 1.4.3
 *
 * @return mixed
 */
function remove_store_configs_url_override($urls) {

	$configs = \IHeartJane\WebMenu\StoreConfigs\get_all_configs();
	$ids = array_column($configs, 'page_id');
	foreach ($ids as $id) {
		unset($urls[$id]);
	}

	return $urls;
}

add_filter( 'permalink_manager_uris',  __NAMESPACE__ . '\\' . 'remove_store_configs_url_override', 100 );

/**
 * Override permalink manager URL override only on jane config page via ajax
 *
 * @since 1.4.3
 *
 * @return void
 */
function remove_custom_url_filters() {

	$action = $_REQUEST['action'] ?? '';
	$is_jane_ajax = $action == 'jane_get_page_path';

	$page = $_REQUEST['page'] ?? '';
	$is_jane_page = $page == 'jane-store-configs';

	if(!$is_jane_page && !$is_jane_ajax) {
		return;
	}

	$filters = $GLOBALS['wp_filter'];

	$custom_filters_to_remove = [
		'_get_page_link',
		'page_link',
		'post_link',
		'post_type_link',
		'attachment_link',
	];

	$priority = 99;
	foreach ( $custom_filters_to_remove as $custom_filter ) {
		try {
            if (!isset($filters[ $custom_filter ])) continue;
            if (!isset($filters[ $custom_filter ]->callbacks[$priority])) continue;
			$custom_hook = reset( $filters[ $custom_filter ]->callbacks[$priority] )['function'];
			remove_filter( $custom_filter, [ $custom_hook[0], 'custom_post_permalinks' ], $priority );
		} catch ( \Exception $e ) {
		}
	}
}

add_action( 'init', __NAMESPACE__ . '\\' . 'remove_custom_url_filters' );
