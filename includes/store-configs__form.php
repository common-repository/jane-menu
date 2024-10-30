<?php

/**
 * Handle the form submissions
 */

namespace IHeartJane\WebMenu\StoreConfigs\Form;

use IHeartJane\WebMenu\Constants;
use IHeartJane\WebMenu\StoreConfigs;
use IHeartJane\WebMenu\Sitemap;
use IHeartJane\WebMenu\Helpers;

/**
 * Handles the form submission on the wp-admin custom page
 *
 * It handles both the settings Save button or the New/Edit Config process.
 *
 * @uses     IHeartJane\WebMenu\Constants\OPTION_SITEMAP_ENABLED_NAME   Constants\OPTION_SITEMAP_ENABLED_NAME
 * @uses     IHeartJane\WebMenu\Constants\ADMIN_PAGE_NAME               Constants\ADMIN_PAGE_NAME
 * @uses     IHeartJane\WebMenu\StoreConfigs\get_all_configs()          StoreConfigs\get_all_configs()
 * @uses     IHeartJane\WebMenu\StoreConfigs\insert_config()            StoreConfigs\insert_config()
 * @uses     IHeartJane\WebMenu\Sitemap\update_remote_sitemap()         Sitemap\update_remote_sitemap()
 *
 * @see      https://developer.wordpress.org/reference/hooks/admin_init/ Codex, action: admin_init
 *
 * @since    1.0.0
 * @since    1.3.5  Added the Post Type support
 *
 * @return   \WP_Error|void   The result is handled through the add_settings_error() or wp_redirect()
 */
function handle_form()
{

    if (isset($_POST['save_menu_options'])) {

        update_option(Constants\OPTION_SITEMAP_ENABLED_NAME, isset($_POST['jane_sitemap_enabled']));
        add_settings_error('jane_store_config_error', 'jane_store_config_error', "Settings Saved.", "success");

        return;
    }

    if (!isset($_POST['save_changes'])) {
        return;
    }

    $errors   = [];
    $page_url = admin_url('admin.php?page=' . Constants\ADMIN_PAGE_NAME);
    $field_id = isset($_POST['field_id']) ? intval($_POST['field_id']) : 0;

    $page_id  = isset($_POST['page_id']) ? intval($_POST['page_id'])  : 0;

    $proxy_url   = isset($_POST['proxy_url']) ? sanitize_url($_POST['proxy_url']) : '';
    $sitemap_url = isset($_POST['sitemap_url']) ? sanitize_url($_POST['sitemap_url']) : '';

    $proxy_url   = wp_http_validate_url($proxy_url);
    $sitemap_url = wp_http_validate_url($sitemap_url);


    // basic validation

    if (!$proxy_url) {

        $errors[] = __('Error: Valid Proxy URL is required', 'iheartjane');
    }

    if (!$sitemap_url) {

        $errors[] = __('Error: Valid Sitemap URL is required', 'iheartjane');
    }

    if (!$page_id) {

        $post_type_name = "Page";

        if (!empty($_POST['post_type'])) {

            $post_type_object = get_post_type_object($_POST['post_type']);

            if ($post_type_object) {

                $post_type_name = $post_type_object->labels->singular_name;
            }
        }

        $errors[] = __('Error: A specific ' . $post_type_name . ' (or some other post type item) needs to be selected', 'iheartjane');
    } else {

        $page = get_post($page_id);

        if (empty($page)) {

            $errors[] = __('Error: Page does not exist', 'iheartjane');
        } else {

            $store_path = Helpers\get_page_relative_path($page->ID);
        }
    }

    // duplicate fields?

    $store_configs = StoreConfigs\get_all_configs();

    if ($field_id) {

        // don't compare the current store config
        unset($store_configs[$field_id]);
    }

    if (in_array($proxy_url, wp_list_pluck($store_configs, "proxy_url"))) {

        $errors[] = __('Error: Proxy URL is already in use', 'iheartjane');
    }

    if (in_array($sitemap_url, wp_list_pluck($store_configs, "sitemap_url"))) {

        $errors[] = __('Error: Sitemap URL is already in use', 'iheartjane');
    }

    if (in_array($page_id, wp_list_pluck($store_configs, "page_id"))) {

        $errors[] = __('Error: Page is already in use', 'iheartjane');
    }


    // bail out if an error is found

    if ($errors) {

        $first_error = reset($errors);
        add_settings_error('jane_store_config_error', 'jane_store_config_error', $first_error);

        return;
    }



    // Prepare the data for insert/update

    $fields = array(
        'page_id'       => $page_id,
        'proxy_url'     => $proxy_url,
        'sitemap_url'   => $sitemap_url,
        'store_path'    => $store_path,
    );

    // New or edit?
    if ($field_id) {

        $fields['id'] = $field_id;
    }

    $success = StoreConfigs\insert_config($fields);


    // redirect if success

    if (!empty($success) && !is_wp_error($success)) {

        Sitemap\update_remote_sitemap();
        $result = verify_configuration($page_id);
        if ($result instanceof \WP_Error) {
            return $result;
        }

        $redirect_to = add_query_arg(array('message' => 'success'), $page_url);

        wp_safe_redirect($redirect_to);
        exit;
    }

    if (is_wp_error($success)) {

        add_settings_error('jane_store_config_error', 'jane_store_config_error', $success->get_error_message());
    } else {

        add_settings_error('jane_store_config_error', 'jane_store_config_error', "Error: Something went wrong");
    }


    return;
}
add_action('admin_init', __NAMESPACE__ . '\\' . 'handle_form');

/**
 * Execute curl request on the passed page and checks if jane config is injected
 *
 * @since 1.4.0
 *
 * @param $pageId
 * @return bool|\WP_Error
 */
function verify_configuration($pageId)
{
    $errorMessage = 'There is an error with your configuration. Please check the Proxy URL to make sure that it matches the URL provided in the business admin.';
    $page = get_post($pageId);
    if (empty($page)) {
        add_settings_error('jane_store_config_error', 'jane_store_config_error', "Error: Page does not exist");
        return new \WP_Error();
    }
    $pageUrl = get_permalink($pageId);

    $args = [];
    if (isset($_ENV['LOCAL_MACHINE']) && (bool) $_ENV['LOCAL_MACHINE'] === true) {
        /** disable ssl verification on local machine */
        $args['sslverify'] = false;
    }
    $response = wp_safe_remote_get($pageUrl, $args);
    $body = wp_remote_retrieve_body($response);
    $code = wp_remote_retrieve_response_code($response);
    if (is_wp_error($response) || $code !== 200) {
        add_settings_error('jane_store_config_error', 'jane_store_config_error', $errorMessage);
        return new \WP_Error();
    }

    $doc = new \DOMDocument();
    @$doc->loadHTML($body);
    $xpath = new \DOMXPath($doc);
    $tags = $xpath->query('//script[@id="jane_frameless_embed_runtime_config"]');
    if ($tags->length > 0) {
        return true;
    }

    add_settings_error('jane_store_config_error', 'jane_store_config_error', $errorMessage);
    return new \WP_Error();
}