<?php

/**
 * Extended WP_List_Table class that displays all the Store Configs
 */

namespace IHeartJane\WebMenu\StoreConfigs;

use IHeartJane\WebMenu\Sitemap;
use IHeartJane\WebMenu\Constants;
use IHeartJane\WebMenu\Helpers;

if (!class_exists('\WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List Store Configs table
 */
class Table_List extends \WP_List_Table
{

    /**
     * Main constructor
     *
     * @since    1.0.0
     *
     * @return   void
     */
    function __construct()
    {

        parent::__construct(array(
            'singular' => 'Store Config',
            'plural'   => 'Store Configs',
            'ajax'     => false
        ));
    }

    /**
     * Gets CSS classes for the table
     *
     * @return array
     */
    function get_table_classes()
    {

        return ['widefat', 'fixed', 'striped', $this->_args['plural']];
    }

    /**
     * Message to show if no designation found
     *
     * @since    1.0.0
     *
     * @return void
     */
    function no_items()
    {

        _e('No Items Found', 'iheartjane');
    }

    /**
     * Default column values if no callback found
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @since    1.0.0
     * @since    1.3.0  Added the Page column
     * @since    1.3.5  Added the Post Type column
     *
     * @return string
     */
    function column_default($item, $column_name)
    {

        switch ($column_name) {

            case 'page':
                return $item->page_id;

            case 'post_type':
                $post_type_name = get_post_type($item->page_id);
                $post_type      = get_post_type_object($post_type_name);

                if (!$post_type) {
                    return '<span style="color: red">Error: Missing post type - ' . $post_type_name . '!</span>';
                }

                return $post_type->labels->singular_name;

            case 'store_path':
                return Helpers\get_full_store_url($item->store_path);

            case 'proxy_url':
                return !empty($item->proxy_url) ? $item->proxy_url : '<span style="color: red">Error: Proxy URL is missing!</span>';

            case 'sitemap_url':
                return !empty($item->sitemap_url) ? $item->sitemap_url : '<span style="color: red">Error: Sitemap URL is missing!</span>';

            default:
                return isset($item->$column_name) ? $item->$column_name : '';
        }
    }

    /**
     * Gets the column names
     *
     * @since    1.0.0
     * @since    1.3.0  Added the Page column
     * @since    1.3.5  Added the Post Type column
     *
     * @return array
     */
    function get_columns()
    {

        return [
            'cb'          => '<input type="checkbox" />',
            'post_type'   => __('Post Type',   'iheartjane'),
            'page'        => __('Title',       'iheartjane'),
            'store_path'  => __('Store Path',  'iheartjane'),
            'proxy_url'   => __('Proxy URL',   'iheartjane'),
            'sitemap_url' => __('Sitemap URL', 'iheartjane'),
        ];
    }

    /**
     * Render action items
     *
     * @uses     IHeartJane\WebMenu\Constants\ADMIN_PAGE_NAME                Constants\ADMIN_PAGE_NAME
     *
     * @param    object     $item   The item that we have in our row (Store Config)
     *
     * @since    1.0.0
     * @since    1.3.0  The main column is the Page column
     *
     * @return   string     Item title and row actions HTML
     */
    function column_page($item)
    {

        $url_view = Helpers\get_full_store_url($item->store_path);
        $url_edit = admin_url('admin.php?page=' . Constants\ADMIN_PAGE_NAME . '&action=edit&id='   . $item->id);
        $url_del  = admin_url('admin.php?page=' . Constants\ADMIN_PAGE_NAME . '&action=delete&id=' . $item->id);

        $page = !empty($item->page_id) ? get_post($item->page_id) : false;
        // Handles the page title if the page does not exist, or it does, but it does not have a title
        $page_title = !empty($page) ? (!empty($page->post_title) ? $page->post_title : "#" . $page->ID . " (no title)") : '<span style="color: red">Error: Page is missing!</span>';
        $title_html   = sprintf('<a href="%1$s"><strong>%2$s</strong></a> ', esc_url($url_edit), $page_title);

        $actions = [
            'view'   => sprintf('<a target="_blank" href="%s" data-id="%d" title="%s">%s</a>',      esc_url($url_view), $item->id, __('View Store Menu',  'iheartjane'), __('View',   'iheartjane')),
            'edit'   => sprintf('<a href="%s" data-id="%d" title="%s">%s</a>',                      esc_url($url_edit), $item->id, __('Edit this item',   'iheartjane'), __('Edit',   'iheartjane')),
            'delete' => sprintf('<a href="%s" class="submitdelete" data-id="%d" title="%s">%s</a>', esc_url($url_del), $item->id, __('Delete this item', 'iheartjane'), __('Delete', 'iheartjane')),
        ];
        $actions_html = $this->row_actions($actions);

        return $title_html . $actions_html;
    }

    /**
     * Gets sortable columns
     *
     * @since    1.0.0
     *
     * @return array
     */
    function get_sortable_columns()
    {

        return [
            'proxy_url'     => ['proxy_url',   false],
            'sitemap_url'   => ['sitemap_url', false],
            'store_path'    => ['store_path',  false],
        ];
    }

    /**
     * Set the bulk actions
     *
     * @since    1.0.0
     *
     * @return array
     */
    function get_bulk_actions()
    {

        return [
            'trash'  => __('Move to Trash', 'iheartjane'),
        ];
    }

    /**
     * Render the checkbox column
     *
     * @param  object  $item
     *
     * @since    1.0.0
     *
     * @return string
     */
    function column_cb($item)
    {

        return sprintf('<input type="checkbox" name="ids[]" value="%d" />', $item->id);
    }

    /**
     * Prepare the class items
     *
     * @uses     IHeartJane\WebMenu\Constants\ADMIN_PAGE_NAME               Constants\ADMIN_PAGE_NAME
     * @uses     IHeartJane\WebMenu\StoreConfigs\delete_config()            StoreConfigs\delete_config()
     * @uses     IHeartJane\WebMenu\StoreConfigs\get_filtered_configs()     StoreConfigs\get_filtered_configs()
     * @uses     IHeartJane\WebMenu\StoreConfigs\get_total_configs_count()  StoreConfigs\get_total_configs_count()
     * @uses     IHeartJane\WebMenu\Sitemap\update_remote_sitemap()         Sitemap\update_remote_sitemap()
     *
     * @since    1.0.0
     * @since    1.3.2  Updates remote sitemap if anything is deleted
     *
     * @return void
     */
    function prepare_items()
    {

        $delete = false;

        // single delete
        if (isset($_GET['action']) && $_GET['page'] == Constants\ADMIN_PAGE_NAME && $_GET['action'] == "delete") {

            $store_config_id = intval($_GET['id']);

            delete_config([$store_config_id]);

            $delete = true;
        }

        // bulk delete
        if (isset($_POST['action']) && $_POST['page'] == Constants\ADMIN_PAGE_NAME && $_POST['action'] == "trash") {

            $store_config_ids = array_map('absint', $_POST['ids']);

            delete_config($store_config_ids);

            $delete = true;
        }

        if ($delete) {

            Sitemap\update_remote_sitemap();
        }


        $search = isset($_REQUEST['s']) ? wp_unslash(trim(sanitize_text_field($_REQUEST['s']))) : '';


        $columns               = $this->get_columns();
        $hidden                = [];
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];

        $per_page              = 20;
        $current_page          = $this->get_pagenum();
        $offset                = ($current_page - 1) * $per_page;

        $args = [
            'offset' => $offset,
            'number' => $per_page,
            'search' => $search
        ];

        if (isset($_REQUEST['orderby']) && isset($_REQUEST['order'])) {

            $args['orderby'] = sanitize_key($_REQUEST['orderby']);
            $args['order']   = sanitize_key($_REQUEST['order']);
        }

        $this->items = get_filtered_configs($args);

        $this->set_pagination_args([
            'total_items' => get_total_configs_count(),
            'per_page'    => $per_page
        ]);
    }
}
