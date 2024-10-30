<?php

/**
 * Ajax related functions
 *
 */

namespace IHeartJane\WebMenu\Ajax;

use IHeartJane\WebMenu\Constants;
use IHeartJane\WebMenu\Helpers;

/**
 * Returns the relative path for a specific Page ID
 *
 * @uses    IHeartJane\WebMenu\Helpers\get_page_relative_path() Helpers\get_page_relative_path()
 *
 * @since   1.3.0
 *
 * @return  void    Uses wp_send_json_success() to return a string or wp_send_json_error() to return an array
 */
function get_page_relative_path()
{

    $data = $_POST;

    // Helpers\debug_log( $data, "get_page_relative_path-data" );

    if (empty($data["page_id"])) {
        $return = ["message" => "Error: somehow the Page ID did not come through."];
        wp_send_json_error($return);
    }

    $page_path = Helpers\get_page_relative_path($data["page_id"]);

    if (empty($page_path)) {
        $return = ["message" => "Error: the page with this Page ID does not exist."];
        wp_send_json_error($return);
    }

    $page_path = site_url("/" . $page_path . "/");

    wp_send_json_success($page_path);
}
add_action('wp_ajax_' . 'jane_' . 'get_page_relative_path', __NAMESPACE__ . '\\' . 'get_page_relative_path');

/**
 * Returns the full path for a specific Page ID
 *
 * @since   1.3.0
 *
 * @return  void    Uses wp_send_json_success() to return a string or wp_send_json_error() to return an array
 */
function get_page_path()
{

    $data = $_POST;

    // Helpers\debug_log( $data, "get_page_path-data" );

    if (empty($data["page_id"])) {
        $return = ["message" => "Error: somehow the Page ID did not come through."];
        wp_send_json_error($return);
    }

    $page_path = get_permalink($data["page_id"]);

    if (empty($page_path)) {
        $return = ["message" => "Error: the page with this Page ID does not exist."];
        wp_send_json_error($return);
    }

    wp_send_json_success($page_path);
}
add_action('wp_ajax_' . 'jane_' . 'get_page_path', __NAMESPACE__ . '\\' . 'get_page_path');

/**
 * Returns the HTML for the pages or other post type items
 *
 * @since   1.3.5
 *
 * @return  void    Uses wp_send_json_success() to return a string or wp_send_json_error() to return an array
 */
function get_post_type_items()
{

    $data = $_POST;

    // Helpers\debug_log( $data, "get_page_path-data" );

    $html = Helpers\get_page_dropdown_html($data["post_type"], $data["page_id"]);

    if (empty($html)) {
        $return = ["message" => "Error: we couldn't create the HTML for this field. ( post_type = " . $data["post_type"] . ", page_id = " . $data["page_id"] . " )"];
        wp_send_json_error($return);
    }

    wp_send_json_success($html);
}
add_action('wp_ajax_' . 'jane_' . 'get_post_type_items', __NAMESPACE__ . '\\' . 'get_post_type_items');

/**
 * Checks if store path is same as on proxy url
 *
 * @since   1.4.0
 *
 * @return  void    Returns json {valid: bool}
 */
function verify_store_path()
{

    $data = $_POST;

    $proxyUrl = $data['proxy_url'];
    $storePathAttributes = parse_url($data['store_path']);
    $storePath = rtrim($storePathAttributes['path'], '/');

    wp_send_json_success(Helpers\verify_store_path($proxyUrl, $storePath));
}
add_action('wp_ajax_' . 'jane_' . 'verify_store_path', __NAMESPACE__ . '\\' . 'verify_store_path');
