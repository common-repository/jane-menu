<?php

/**
 * Shortcode functions
 *
 */

namespace IHeartJane\WebMenu\Shortcode;

use IHeartJane\WebMenu\Helpers;

/**
 * Returns the wrapped Menu body HTML
 *
 * @param    string       $body     HTML body
 *
 * @since    1.3.13
 *
 * @return   string       Wrapped HTML body
 */
function get_wrapped_menu_body_html($html)
{

    static $already_run = false;
    if ($already_run) {

        return '';
    }

    $header = PHP_EOL . '<!-- Jane Menu Plugin Body -->' . PHP_EOL;
    $footer = PHP_EOL . '<!-- /Jane Menu Plugin Body -->' . PHP_EOL;
    $wrapped_html = $header . $html . $footer;

    $already_run = true;

    return $wrapped_html;
}

/**
 * Returns the Menu body HTML
 *
 * @param    array        $atts         Shortcode attributes
 * @param    string       $content      Shortcode content
 * @param    string       $shortcode    Full shortcode
 *
 * @since    1.3.5
 *
 * @return   string       The HTML
 */
function get_menu_body_html($atts, $content, $shortcode)
{

    global $jane__current_config;

    $html = '';
    if (!empty($jane__current_config->get_template_html_body())) {

        $html .= get_wrapped_menu_body_html($jane__current_config->get_template_html_body());
    }

    return $html;
}
add_shortcode('jane_menu_shortcode', __NAMESPACE__ . '\\' . 'get_menu_body_html');

/**
 * Alter the page content
 *
 * If it is a Jane Menu page and the shortcode is not present, inject the Menu body HTML at the end of the page content
 *
 * @see      https://developer.wordpress.org/reference/hooks/the_content/     Codex, filter: the_content
 *
 * @param    string     $content   The original content of the page
 *
 * @since    1.3.10     Fallback to the replacing the entire content if the shortcode is not present
 *
 * @return   string     Altered content
 */
function inject_template_if_no_shortcode($content)
{

    global $jane__current_config;

    $is_jane_menu_page = is_singular() && in_the_loop() && is_main_query() && !empty($jane__current_config->get_template_html_body());
    if ($is_jane_menu_page && strpos($content, '<div id="app" class="app"></div>') === false) {

        $content .= get_wrapped_menu_body_html($jane__current_config->get_template_html_body());
    }

    return $content;
}
add_filter('the_content', __NAMESPACE__ . '\\' . 'inject_template_if_no_shortcode', 11);
