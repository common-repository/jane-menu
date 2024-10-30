<?php

/**
 * Compatibility functions
 */

namespace IHeartJane\WebMenu\Compatibility;

/**
 * Gets the name of the callback function
 *
 * @param    array        $callback     Callback from $global->wp_filter;
 *
 * @since    1.3.6
 *
 * @return   string       The name of the callback function, ex. jane_canonical
 */
function get_callback_name($callback)
{
    $function_definition = array_values($callback)[0];
    $callback_name = array_values($function_definition)[0];
    return $callback_name;
}

/**
 * Show warning in admin if compatibility issues with other plugins are detected.
 *
 * @since    1.3.6
 *
 * @return   void
 */
function jane_admin_notice__warning()
{
    $jane_filter_callback_name = __NAMESPACE__ . '\\' . 'get_canonical_url';

    try {
        //check if there are any hooks registered for canonical that dont match this plugins namespace?
        global $wp_filter;

        $hook = 'get_canonical_url';
        $found_hooks = $wp_filter[$hook];
        $callbacks = $found_hooks ? $found_hooks->callbacks : null;

        $other_filters_exist = false;

        // ex: callbacks => [  10 => [ cb1, cb2 ], 20 => [cb1]  ]
        // we want to check if there are more than 1 callback priority groups
        // or within a single priority group, multiple callback functions
        // if there is just 1, but its not "ours" then we should warn the user

        if (count($callbacks) > 1) {
            $other_filters_exist = true;
        } else {
            foreach ($callbacks as $priority => $callback) {
                $callback_name = get_callback_name($callback);
                $callback_count = count($callback);

                //since "our" plugin only adds 1 filter, we can assume another plugin is interfering
                if ($callback_count > 1) {
                    $other_filters_exist = true;
                    break;
                }

                if ($callback_count === 1) {
                    if ($callback_name !== $jane_filter_callback_name) {
                        $other_filters_exist = true;
                    }
                }
            }
        }

        if (!$other_filters_exist) {
            return;
        }

        echo '<div class="notice notice-warning"><b>WARNING:</b> Jane Menu detected other plugin(s) that might interfere with the ability to generate accurate canonical urls.</div>';
        return;
    } catch (Exception $e) {
    } finally {
        return;
    }
}
add_action('admin_notices', __NAMESPACE__ . '\\' . 'jane_admin_notice__warning');

/**
 * Checks if URL path is a jane menu path
 *
 * @param    string     $url  The url path to check
 *
 * @since    1.3.12
 *
 * @return   bool       Returns true if $path is a jane menu url path and false otherwise
 */
function is_jane_menu_path($path) {
    global $jane__current_config;

    if (!$jane__current_config->get_id()) {
        return false;
    }

    if (str_starts_with($path, '/' . $jane__current_config->get_config()->store_path)) {
        return true;
    }

    return false;
}

/**
 * Checks if URL path is a jane menu product path
 *
 * @param    string     $url  The url path to check
 *
 * @since    1.3.12
 *
 * @return   bool       Returns true if $path is a jane menu product url path and false otherwise
 */
function is_jane_menu_product_path($path) {
    global $jane__current_config;

    if (!$jane__current_config->get_id()) {
        return false;
    }

    if (str_starts_with($path, '/' . $jane__current_config->get_config()->store_path . '/products')) {
        return true;
    }

    return false;
}

/**
 * Custom filter added to the WP 'get_canonical_url' filter.
 *
 * @param    string     $canonical_url  The current page's generated canonical URL.
 *
 * @since    1.3.6
 * @since    1.3.12     Strip query string from pdp canonical URL
 *
 * @return   string     If current page/post is a jane menu, returns the full canonical_url, otherwise let wordpress handle.
 */
function get_canonical_url($canonical_url)
{
    try {
        $parsed_canonical_url = wp_parse_url($canonical_url);

        if (is_jane_menu_path($parsed_canonical_url['path'])) {
            $parsed_request_uri = $_SERVER['REQUEST_URI'];
            // strip query string from pdp path
            if(is_jane_menu_product_path($_SERVER['REQUEST_URI'])) $parsed_request_uri = strtok($_SERVER['REQUEST_URI'],'?');
            $canonical_url = str_replace($parsed_canonical_url['path'], $parsed_request_uri, $canonical_url);
        }

        return $canonical_url;
    } catch (Exception $e) {
    } finally {
        return $canonical_url;
    }
}
add_filter('get_canonical_url', __NAMESPACE__ . '\\' . 'get_canonical_url', 10, 2);

/**
 * Yoast specific canonical filter
 * https://developer.yoast.com/features/seo-tags/canonical-urls/api/
 *
 * @since    1.3.10
 *
 */
add_filter('wpseo_canonical', __NAMESPACE__ . '\\' . 'get_canonical_url');

/**
 * Rank Math specific canonical filter
 * https://rankmath.com/kb/filters-hooks-api-developer/#change-canonical-url
 *
 * @since    1.3.11
 *
 */
add_filter('rank_math/frontend/canonical', __NAMESPACE__ . '\\' . 'get_canonical_url');

/**
 * Squirrly SEO specific canonical filter
 * https://howto12.squirrly.co/kb/add-custom-canonical-link/
 *
 * @since    1.3.12
 *
 */
add_filter('sq_canonical', __NAMESPACE__ . '\\' . 'get_canonical_url', 11);

/**
 * SEO Press specific canonical filter
 * https://www.seopress.org/support/hooks/filter-canonical-url/
 * SEO Press hook uses full html of the tag instead of just the url
 *
 * @since    1.3.11
 * @since    1.3.12     Strip query string from pdp canonical URL
 *
 */
function sp_titles_canonical($html) {
    try {
        global $jane__current_config;

        if (!$jane__current_config->get_id()) {
            return $html;
        }

        if (is_jane_menu_path($_SERVER['REQUEST_URI'])) {
            $parsed_request_uri = $_SERVER['REQUEST_URI'];
            // strip query string from pdp path
            if(is_jane_menu_product_path($_SERVER['REQUEST_URI'])) $parsed_request_uri = strtok($_SERVER['REQUEST_URI'],'?');
            $html = str_replace('/' . $jane__current_config->get_config()->store_path . '/', $parsed_request_uri, $html);
        }

        return $html;
    } catch (Exception $e) {
    } finally {
        return $html;
    }
}
add_filter('seopress_titles_canonical', __NAMESPACE__ . '\\' . 'sp_titles_canonical');
