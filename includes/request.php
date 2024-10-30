<?php

/**
 * Functions triggered while parsing the request for the store
 */

namespace IHeartJane\WebMenu\Request;

use IHeartJane\WebMenu\Constants;
use IHeartJane\WebMenu\StoreConfigs;
use IHeartJane\WebMenu\Helpers;

/**
 * Creates the global variable with current Store Config data
 *
 * Allows us to perform the database check once and reuse the results in several places
 *
 * @uses     \IHeartJane\WebMenu\StoreConfigs\Current_Store_Config::get_instance()  StoreConfigs\Current_Store_Config::get_instance()
 *
 * @see      https://developer.wordpress.org/reference/hooks/init/                  Codex, action: init
 *
 * @since    1.3.0
 *
 * @return   void
 */
function initial_check()
{

    global $jane__current_config;

    $jane__current_config = StoreConfigs\Current_Store_Config::get_instance();
}
add_action('init', __NAMESPACE__ . '\\' . 'initial_check');

/**
 * Alter the WordPress request results
 *
 * When a fictive subpage of a Jane Menu is requested, we have to simulate that the Jane Menu page was called.
 *
 * @see      https://developer.wordpress.org/reference/hooks/parse_request/     Codex, action: parse_request
 *
 * @param    \WP        $wp    Current WordPress environment instance (passed by reference)
 *
 * @since    1.3.0
 *
 * @return   void       The $wp is passed by reference
 */
function after_parse_request($wp)
{

    global $jane__current_config;

    if (!$jane__current_config->get_id()) {

        return;
    }

    $post_type              = $jane__current_config->get_config()->post_type;
    $post_slug              = $jane__current_config->get_config()->post_slug;
    $relative_slug          = $jane__current_config->get_config()->relative_slug;
    $store_path             = Helpers\normalize_store_path($jane__current_config->get_config()->store_path);
    $all_custom_post_types  = get_post_types(['public' => true, '_builtin' => false], 'names', 'and');

    $query_vars['page'] = null;
    if ($post_type == 'page') {

        $query_vars['pagename'] = $store_path;
        $matched_rule = '(.?.+?)(?:/([0-9]+))?/?$';
        $matched_query = 'pagename=' . urlencode($store_path) . '&page=';
    } elseif ($post_type == 'post') {

        $query_vars['name'] = $store_path;
        $matched_rule = '([^/]+)(?:/([0-9]+))?/?$';
        $matched_query = 'name=' . urlencode($store_path) . '&page=';
    } elseif (in_array($post_type, $all_custom_post_types)) {

	    $query_vars['post_type'] = $post_type;
	    $query_vars['do_not_redirect'] = true;
	    $query_vars['page_id'] = $jane__current_config->get_page_id();
	    $matched_rule = $post_type . '/(.+?)(?:/([0-9]+))?/?$';
	    $matched_query = urlencode( $post_type ) . '=' . urlencode( $post_slug ) . '&page=';
    } else {

        $query_vars[$post_type] = $relative_slug;
        $query_vars['post_type'] = $post_type;
        $query_vars['name'] = $relative_slug;
        $matched_rule = $post_type . '/(.+?)(?:/([0-9]+))?/?$';
        $matched_query = urlencode($post_type) . '=' . urlencode($relative_slug) . '&page=';
    }

    $wp->query_vars = $query_vars;
    $wp->matched_rule  = $matched_rule;
    $wp->matched_query = $matched_query;
}
add_action('parse_request', __NAMESPACE__ . '\\' . 'after_parse_request', 10, 1);

/**
 * Append our code in the <head> tag
 *
 * @see      https://developer.wordpress.org/reference/hooks/wp_head/     Codex, action: wp_head
 *
 * @since    1.3.0
 * @since    1.3.7      Wrap the <head> tag with Jane Menu Plugin
 *
 * @return   void       The code is echoed
 */
function wrap_template_head_with_plugin_info()
{

    global $jane__current_config;

    $plugin_header = PHP_EOL . '<!-- Jane Menu Plugin Header -->' . PHP_EOL;
    $plugin_footer = PHP_EOL . '<!-- /Jane Menu Plugin Header -->' . PHP_EOL;

    if (!empty($jane__current_config->get_template_html_head())) {

        echo $plugin_header . $jane__current_config->get_template_html_head() . $plugin_footer;
    } else {

        echo $plugin_header . $plugin_footer;
    }
}
add_action('wp_head', __NAMESPACE__ . '\\' . 'wrap_template_head_with_plugin_info');

/**
 * Inject <meta name="jane:version" content="1.3.0" /> into the <head> tag
 *
 * @see      https://developer.wordpress.org/reference/hooks/wp_head/     Codex, action: wp_head
 *
 * @since    1.3.0
 * @since    1.3.8      Inject a <meta> tag with the plugin version into the <head> tag
 *
 * @return   void       The code is echoed
 */
function inject_plugin_version_meta_tag()
{

    $plugin_version = Constants\PLUGIN_VER;
    echo '<meta name="jane:version" content="' . $plugin_version . '" />' . PHP_EOL;
}
add_action('wp_head', __NAMESPACE__ . '\\' . 'inject_plugin_version_meta_tag');
