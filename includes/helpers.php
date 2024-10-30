<?php

/**
 * Helper functions
 */

namespace IHeartJane\WebMenu\Helpers;

use IHeartJane\WebMenu\Constants;

/**
 * Escape for HTML
 *
 * @param string Text that should be escaped
 *
 * @since 1.0.0
 *
 * @return string Escaped text
 */
function escape_for_html($string)
{

    return str_replace("\0", "&#0;", htmlspecialchars($string, ENT_QUOTES, 'utf-8'));
}

/**
 * String last replace
 *
 * Replaces the last occurence of a string with another string
 *
 * @param    string   $search     Search string, needle
 * @param    string   $replace    Replace string
 * @param    string   $subject    Subject string, haystack
 *
 * @since    1.0.0
 *
 * @return   string   Altered string, in case the search string is not found - original string
 */
function str_lreplace($search, $replace, $subject)
{

    $pos = strrpos($subject, $search);

    if ($pos !== false) {

        $subject = substr_replace($subject, $replace, $pos, strlen($search));
    }

    return $subject;
}

/**
 * Gets the relative path of a Page
 *
 * It will return the relative path of a page, together with all their parent pages in the path.
 *
 * The returned relative path does not have a starting or trailing slash.
 *
 * @param    int    $page_id    Page ID
 *
 * @since 1.3.0
 *
 * @return   string|false     Relative path or false if page does not exist
 */
function get_page_relative_path($page_id)
{

    $path = get_permalink($page_id);

    if (!$path) {

        return false;
    }

    $path = wp_make_link_relative($path);
    $path = substr($path, 1); // remove starting slash
    $path = untrailingslashit($path); // remove trailing slash

    return $path;
}


/**
 * Returns the HTML for the post type select field
 *
 * Returns the label as well.
 * Produces slightly different HTML for hierarchical and non-hierarchical post types
 *
 * @param    string    $post_type    Post type slug
 * @param    int       $page_id      Post ID (optional)
 *
 * @since    1.3.5
 *
 * @return   string    HTML
 */
function get_page_dropdown_html($post_type, $page_id = false)
{

    if (!$post_type) {

        return '<br/><span style="color:red;">Please select a Post Type</span>';
    }

    $post_type_object = get_post_type_object($post_type);

    if (!$post_type_object) {

        return '<span style="color:red;">Desired post type has not been found: ' . $post_type . '</span>';
    }

    if (!$post_type_object->public) {

        return '<span style="color:red;">Desired post type is not public: ' . $post_type . '</span>';
    }

    $label = $post_type_object->labels->singular_name;



    if ($post_type_object->hierarchical) {

        $args = [
            "post_type"         => $post_type,
            'nopaging'          => true,
            'posts_per_page'    => -1,
        ];

        $pages = get_posts($args);

        if (count($pages) > 0) {

            $args = [
                "post_type"         => $post_type,
                "name"              => "page_id",
                "id"                => "page_id",
                "class"             => "regular-text",
                "show_option_none"  => "Select one:",
                "option_none_value" => 0,
                "echo"              => false,
            ];

            if ($page_id) {

                $args['selected'] = $page_id;
            }

            $page_options = wp_dropdown_pages($args);
        } else {

            $page_options = "No items found for this post type.";
        }
    } else {

        $args = [
            "post_type"         => $post_type,
            'order'             => 'ASC',
            'orderby'           => 'title',
            'nopaging'          => true,
            'posts_per_page'    => -1,
        ];

        $posts = get_posts($args);

        // debug_log( $posts, 'get_page_dropdown_html-posts' );

        if (count($posts) > 0) {

            $page_options = '<select id="page_id" name="page_id">';

            $selected = empty($page_id) ? ' selected="selected"' : '';

            $page_options .=     '<option value="0"' . $selected . '>' . 'Select one:' . '</option>';

            foreach ($posts as $post) {

                $selected = $page_id == $post->ID ? ' selected="selected"' : '';

                $page_options .= '<option value="' . esc_attr($post->ID) . '"' . $selected . '>' . $post->post_title . '</option>';
            }

            $page_options .= '</select>';
        } else {

            $page_options = "No items found for this post type.";
        }
    }

    $html  = '<label>' . $label . '<span> * </span></label>';
    $html .= '<br />';
    $html .= $page_options;

    return $html;
}

/**
 * Removes the multisite prefix from the store path (if needed)
 * 
 * @param   string  $store_path     The store path
 * 
 * @since   1.4.0
 * 
 * @return  string  normalized path
 */
function normalize_store_path($store_path)
{

    if (is_multisite()) {
        return substr($store_path, strlen(get_blog_details()->path) - 1);
    }

    return $store_path;
}

/**
 * Returns full store path with the multisite prefix removed (if needed)
 *
 * @param   string  $store_path     The store path
 *
 * @since   1.4.0
 *
 * @return  string  path
 */
function get_full_store_url($store_path)
{

    return site_url() . '/' . normalize_store_path($store_path) . '/';
}

/**
 * Returns the HTML for all the public post types
 *
 * @param    int       $page_id      Post ID (optional)
 *
 * @since    1.3.5
 *
 * @return   string    HTML
 */
function get_post_types_dropdown_html($page_id = false)
{

    $args = [
        'public'    => true,
    ];

    $selected_type  = $page_id ? get_post_type($page_id) : 'page';
    $post_types     = get_post_types($args, 'objects');

    unset($post_types['attachment']);
    unset($post_types['e-landing-page']);
    unset($post_types['elementor_library']);

    // debug_log( array_keys( $post_types ), "Helpers-get_post_types_dropdown_html-post_types" );

    $html  = '<label>' . 'Post type' . '<span> * </span></label>';
    $html .= '<br />';


    if ($post_types) {

        $selected = !$selected_type ? ' selected="selected"' : '';

        $html .= '<select id="post_type" name="post_type" data-page_id="' . ($page_id ? esc_attr($page_id) : '') . '">';
        $html .=     '<option value="0"' . $selected . '>' . 'Select one:' . '</option>';

        foreach ($post_types  as $post_type) {

            $selected = $selected_type == $post_type->name ? ' selected="selected"' : '';

            $html .= '<option value="' . $post_type->name . '"' . $selected . '>' . $post_type->label . '</option>';
        }

        $html .= '</select>';
    }


    return $html;
}


/**
 * Main logging function
 *
 * Logs a variable into WP_CONTENT_DIR/debug.log
 *
 * @param    mixed    $log       Anything you want to log
 * @param    string   $text      Any descriptive text that helps you find your log
 * @param    bool     $delete    Append or overwrite the file, default: false (don't delete)
 *
 * @since 1.3.0
 *
 * @return   void
 */
function debug_log($log, $text = "debug_log: ", $delete = false)
{

    if ($delete) {
        unlink(WP_CONTENT_DIR . '/debug.log');
    }

    if (is_array($log) || is_object($log)) {
        error_log($text . PHP_EOL . print_r($log, true) . PHP_EOL, 3, WP_CONTENT_DIR . '/debug.log');
    } else {
        error_log($text . PHP_EOL . $log . PHP_EOL, 3, WP_CONTENT_DIR . '/debug.log');
    }
}


/**
 * Enquques a JS script
 *
 * The enqueued JS gets it's version generated from the last changed date and time of the file,
 * which avoids cache only if the file has been changed since the cached version.
 *
 * @uses     \IHeartJane\WebMenu\Constants\PLUGIN_DIR PLUGIN_DIR
 * @uses     \IHeartJane\WebMenu\Constants\PLUGIN_URL PLUGIN_URL
 *
 * @param    string    $scriptname    script handle
 * @param    string    $filename      relative path of the JS file
 * @param    string[]  $dependency    list of dependency handles, default: empty array
 * @param    bool      $is_footer     should the script get loaded in the footer, default: true
 *
 * @since 1.3.0
 *
 * @return   void
 */
function enqueue_JS($scriptname, $filename, $dependency = array(), $is_footer = true)
{

    $js_ver  = date("ymd-Gis", filemtime(Constants\PLUGIN_DIR . $filename));
    wp_enqueue_script($scriptname,         Constants\PLUGIN_URL . $filename, $dependency, $js_ver, $is_footer);
}

/**
 * Enquques a CSS file
 *
 * The enqueued CSS gets it's version generated from the last changed date and time of the file,
 * which avoids cache only if the file has been changed since the cached version.
 *
 * @uses     \IHeartJane\WebMenu\Constants\PLUGIN_DIR PLUGIN_DIR
 * @uses     \IHeartJane\WebMenu\Constants\PLUGIN_URL PLUGIN_URL
 *
 * @param    string    $scriptname    Style handle
 * @param    string    $filename      relative path of the CSS file
 * @param    string[]  $dependency    list of dependency handles, default: empty array
 *
 * @since 1.3.0
 *
 * @return   void
 */
function enqueue_CSS($scriptname, $filename, $dependency = array())
{

    $css_ver = date("ymd-Gis", filemtime(Constants\PLUGIN_DIR . $filename));
    wp_register_style($scriptname,         Constants\PLUGIN_URL . $filename, $dependency, $css_ver);
    wp_enqueue_style($scriptname);
}

/**
 * Gets the child elements of an HTML element as an array of strings
 *
 * @param    string    $html        String of HTML code
 * @param    string    $html_tag    Parent element HTML tag
 *
 * @since    1.3.0
 *
 * @return   string[]     List of HTML elements
 */
function get_html_child_elements($html, $html_tag = 'head')
{

    $elements = [];

    $dom = new \DomDocument();
    @$dom->loadHTML($html);

    $parent = $dom->getElementsByTagName($html_tag)->item(0);

    foreach ($parent->childNodes as $element) {

        $elements[] = $dom->saveHTML($element);
    }

    return $elements;
}

/**
 * Checks if store path is same as on proxy url
 *
 * @since   1.4.0
 *
 * @return  array    Returns json {valid: bool}
 */
function verify_store_path($proxyUrl, $storePath): array
{

    $response = wp_safe_remote_get($proxyUrl, ["timeout" => 30]);
    $html     = wp_remote_retrieve_body($response);

    $responseCode = wp_remote_retrieve_response_code($response);

    if (is_wp_error($response) || $responseCode !== 200) {
        return ['valid' => false];
    }

    $headElements = get_html_child_elements($html);

    $partnerHostedPath = null;
    foreach ($headElements as $element) {
        if (strpos($element, 'jane_frameless_embed_runtime_config')) {

            $regex = '/\\"partnerHostedPath\\":([^,]+)/';
            preg_match($regex, $element, $matched);
            $partnerHostedPath = isset($matched[1]) ? trim($matched[1], '\\"') : null;
            break;
        }
    }

    return [
        'valid' => $storePath === $partnerHostedPath,
    ];
}