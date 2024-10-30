<?php

/**
 * Core - Config
 *
 * @since    1.3.0
 *
 */

namespace IHeartJane\WebMenu\StoreConfigs;

use IHeartJane\WebMenu\Constants;
use IHeartJane\WebMenu\Helpers;

/**
 * Everything related to the Store Config from the current URL request
 *
 * @since    1.3.0
 *
 */
class Current_Store_Config
{

    /**
     * The static instance will be saved here
     *
     * @since    1.3.0
     *
     * @var Current_Store_Config
     */
    private static $instance = null;

    /**
     * ID of the current Store Config
     *
     * @since    1.3.0
     *
     * @var int|null    Store Config ID if found, otherwise null
     */
    private $id = null;

    /**
     * Store Config
     *
     * @since    1.3.0
     *
     * @var int|null    Store Config, otherwise null
     */
    private $config = null;

    /**
     * Template location for the current request
     *
     * @since    1.3.0
     *
     * @var string      Full path
     */
    private $template_path = "";

    /**
     * Template response code
     *
     * @since    1.3.0
     *
     * @var int         Response code like 200 or 404
     */
    private $template_response_code = null;

    /**
     * Template content that should go into <head>
     *
     * @since    1.3.0
     *
     * @var array      The content for the head
     */
    private $template_head = [];

    /**
     * Template content that should go into <body>
     *
     * @since    1.3.0
     *
     * @var array      The content for the body
     */
    private $template_body = [];


    /**
     * Algolia URL
     *
     * @since 1.4.0
     *
     * @var string|null
     */
    private $algolia_url = null;

    /**
     * External config set on proxy url
     *
     * @since 1.4.0
     *
     * @var null|array
     */
    private $external_configs = null;

    /**
     * Main constructor
     *
     * @uses     IHeartJane\WebMenu\StoreConfigs\Current_Store_Config\_get_config_from_request_url() Current_Store_Config\_get_config_from_request_url()
     * @uses     IHeartJane\WebMenu\StoreConfigs\Current_Store_Config\_get_template_path()           Current_Store_Config\_get_template_path()
     *
     * @since    1.3.0
     *
     * @return   void
     */
    private function __construct()
    {

        $valid_request_types = [
            'GET',
            'HEAD',
        ];

        if (!wp_doing_ajax() && in_array($_SERVER['REQUEST_METHOD'], $valid_request_types)) {

            $this->id = $this->_get_config_from_request_url();
            if (isset($this->config->proxy_url)) {
                $index = is_int(strpos($this->config->proxy_url, 'staging')) ? 'staging' : 'production';
                $this->algolia_url = sprintf(Constants\ALGOLIA_URL_TEMPLATE, $index);
            }
        }

        if ($this->id) {

            $this->template_path = $this->_get_template_path();
        }
    }

    /**
     * Returns the singleton instance
     *
     * @since    1.3.0
     *
     * @return   Current_Store_Config
     */
    public static function get_instance()
    {

        if (is_null(self::$instance)) {

            self::$instance = new self;
        }

        return self::$instance;
    }


    /**
     * Gets the Store Config of the current request
     *
     * Finds the longest matching path in the database.
     *
     * @uses     IHeartJane\WebMenu\Constants\DB_TABLE_NAME Constants\DB_TABLE_NAME
     *
     * @since    1.3.0
     * @since    1.3.5  Added the Post Type support
     *
     * @return   object|null     Returns the Store Config if found, otherwise null
     */
    private function _get_config_from_request_url()
    {

        global $wpdb;

        $table_name = $wpdb->prefix . Constants\DB_TABLE_NAME;
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) !== $table_name) {

            return null;
        }

        $query = "
            SELECT      *
            FROM        $table_name
            WHERE       %s LIKE CONCAT( '/', store_path, '%' )
            ORDER BY    LENGTH( store_path ) DESC
            LIMIT       1
        ";
        $query = $wpdb->prepare($query, $_SERVER['REQUEST_URI']);
        $this->config = $wpdb->get_row($query);

        if (!$this->config) {

            return null;
        }

        $this->config->post_type = get_post_type($this->config->page_id);
        $this->config->post_slug = get_post_field('post_name', $this->config->page_id);
        $this->config->relative_slug = false;

        $remove = $this->config->post_type . '/';
        if (substr($this->config->store_path, 0, strlen($remove)) == $remove) {

            $this->config->relative_slug = substr($this->config->store_path, strlen($remove));
        }

        return $this->config->id;
    }

    /**
     * Gets the template for the current request
     *
     * The template can either be for the sitemap or the front end page.
     *
     * @uses     IHeartJane\WebMenu\Constants\TEMPLATES_DIR             Constants\TEMPLATES_DIR
     *
     * @uses     IHeartJane\WebMenu\StoreConfigs\Current_Store_Config\_parse_template() Current_Store_Config\_parse_template()
     *
     * @since    1.3.0
     * @since    1.3.2      The sitemap template processing was abandoned
     *
     * @return   string     Full path for the template file, empty string if no current Store Config
     */
    private function _get_template_path()
    {

        if (empty($this->id)) {
            return "";
        }

        $this->_parse_template();

        return Constants\TEMPLATES_DIR . 'store-menu.php';
    }

    /**
     * Sets the template parts
     *
     * The template parts come from an external request and are parsed into head and body properties
     *
     * @uses     IHeartJane\WebMenu\StoreConfigs\Current_Store_Config\_purge_head_html_elements() Current_Store_Config\_purge_head_html_elements()
     *
     * @see      https://developer.wordpress.org/reference/classes/WP_Http/request/
     *
     * @since    1.3.0
     *
     * @return   void       The results are saved in the class properties
     */
    private function _parse_template()
    {

        $response = wp_safe_remote_get($this->config->proxy_url, ["timeout" => 30]);
        $html     = wp_remote_retrieve_body($response);

        $this->template_response_code = wp_remote_retrieve_response_code($response);

        if (is_wp_error($response) || $this->template_response_code !== 200) {
            return;
        }

        // Helpers\debug_log( $html, "Current_Store_Config-_get_templates_parts-html" );

        $this->template_head = Helpers\get_html_child_elements($html, 'head');

        if (is_int(strpos($_SERVER['REQUEST_URI'], 'products'))) {
            $this->_append_product_meta_data();
        }

        $this->template_body = Helpers\get_html_child_elements($html, 'body');

        $this->_purge_head_html_elements();


        // array_unshift( $this->template_head, PHP_EOL . '<!-- Jane Menu head HTML starts here -->' . PHP_EOL );
        // array_unshift( $this->template_body, PHP_EOL . '<!-- Jane Menu body HTML starts here -->' . PHP_EOL );

        // $this->template_head[] = '<!-- Jane Menu head HTML ends here -->' . PHP_EOL . PHP_EOL;
        // $this->template_body[] = '<!-- Jane Menu body HTML ends here -->' . PHP_EOL . PHP_EOL;

        // Helpers\debug_log( $this->template_head, "Current_Store_Config-_get_templates_parts-template_head" );
        // Helpers\debug_log( $this->template_body, "Current_Store_Config-_get_templates_parts-template_body" );

    }

    /**
     * Removes unneeded HTML elements from the <head>
     *
     * @since    1.3.0
     *
     * @return   void     It just alters the $this->template_head property
     */
    private function _purge_head_html_elements()
    {

        $search = [
            'charset="',
            'http-equiv',
            'name="viewport',
            '<title',
            'mobile-web-app-capable',

            // TODO Do we need GTM?
            'googletagmanager',
            'window.dataLayer',

            // TODO Do we need fonts? Seems the same without those, at least on the test site
            // 'fonts.googleapis',
        ];

        foreach ($this->template_head as $index => $element) {

            foreach ($search as $search_phrase) {

                if (strpos($element, $search_phrase) !== false) {

                    unset($this->template_head[$index]);
                    break;
                }
            }
        }

        $this->template_head = array_values($this->template_head);
    }


    /**
     * Returns the ID of the current Store Config
     *
     * @since    1.3.0
     *
     * @return   int|null    Store Config ID if found, otherwise null
     */
    public function get_id()
    {

        return $this->id;
    }

    /**
     * Returns the current Store Config object
     *
     * @since    1.3.0
     *
     * @return   object|null    Store Config object, otherwise null
     */
    public function get_config()
    {

        return $this->config;
    }

    /**
     * Returns the current Store Config Page ID
     *
     * @since    1.3.0
     *
     * @return   int|null    Store Config Page ID, otherwise null
     */
    public function get_page_id()
    {

        return !empty($this->config->page_id) ? $this->config->page_id : null;
    }


    /**
     * Returns the current template path if it will need to be replaced
     *
     * @since    1.3.0
     *
     * @return   string     Full path for the template file, empty string if no current Store Config
     */
    public function get_template_path()
    {

        return $this->template_path;
    }

    /**
     * Returns the current template response code
     *
     * @since    1.3.0
     *
     * @return   int        Response code like 200 or 404
     */
    public function get_template_response_code()
    {

        return $this->template_response_code;
    }

    /**
     * Returns the current template head elements for injection
     *
     * @since    1.3.0
     *
     * @return   string     HTML
     */
    public function get_template_html_head()
    {

        return implode(PHP_EOL, $this->template_head);
    }

    /**
     * Returns the current template body elements for injection
     *
     * @since    1.3.0
     *
     * @return   string     HTML
     */
    public function get_template_html_body()
    {

        return implode(PHP_EOL, $this->template_body);
    }

    /**
     * Gets information from external API
     *
     * @since 1.4.0
     *
     * @return mixed
     */
    private function _get_product_metadata(): array
    {

        $storeId = $this->_get_store_id();
        $productId = $this->_get_product_id_from_url();
        if (is_null($storeId) || is_null($productId)) {
            return [];
        }

        $args = [
            'body' => json_encode([
                'params' => "filters=store_id = $storeId AND product_id = $productId",
                'hitsPerPage' => 1
            ]),
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Algolia-API-Key' => Constants\ALGOLIA_API_KEY,
                'X-Algolia-Application-Id' => Constants\ALGOLIA_API_ID,
                'timeout' => 30
            ]
        ];
        $response = wp_safe_remote_post($this->algolia_url, $args);
        $html = wp_remote_retrieve_body($response);
        $responseCode = wp_remote_retrieve_response_code($response);

        if (is_wp_error($response) || $responseCode !== 200) {
            return [];
        }

		try {
	        $jsonResponse = json_decode($html, true);

	        if ((int)$jsonResponse['nbHits'] === 0) {
	            return [];
	        }

	        return $jsonResponse['hits'][0];
		} catch (\Exception $e) {
			return [];
		}
    }

    /**
     * Creates store configs external url based on proxy_url
     *
     * @since 1.4.0
     *
     * @return false|string
     */
    private function _get_external_configs_url()
    {

        if (!isset($this->config->proxy_url)) {
            return false;
        }

        $info = parse_url($this->config->proxy_url);
        $exploaded = explode('/', $info['path']);
        $storeId = $this->_get_store_id();

        return sprintf('%s://%s/%s/stores/%d', $info['scheme'], $info['host'], $exploaded[1], $storeId);
    }

    /**
     * Gets store configs from external API
     *
     * @since 1.4.0
     *
     * @return array
     */
    private function _get_external_configs($fresh = false): array
    {

        $storeId = $this->_get_store_id();
        if (is_null($storeId)) {
            return [];
        }

        if(isset($this->external_configs) && !$fresh) {
            return $this->external_configs;
        }

        $this->external_configs = [];
        $url = $this->_get_external_configs_url();

        if (!$url) {
            return [];
        }

        $args = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'timeout' => 30
            ]
        ];
        $response = wp_safe_remote_get($url, $args);
        $html = wp_remote_retrieve_body($response);
        $responseCode = wp_remote_retrieve_response_code($response);

        if (is_wp_error($response) || $responseCode !== 200) {
            return $this->external_configs;
        }

		try {
	        $jsonResponse = json_decode($html, true);

	        return $this->external_configs = $jsonResponse['store'] ?? [];
		} catch (\Exception $e) {
			return [];
		}
    }

    /**
     * Embeds product meta data in header
     *
     * @since 1.4.0
     *
     * @return void
     */
    private function _append_product_meta_data(): void
    {

        $product = $this->_get_product_metadata();

        $title = sprintf('%s | %s | %s', $product['name'] ?? '', $product['brand'] ?? '', $product['brand_subtype'] ?? '');
        $imageUrl = $product['photos'][0]['urls']['small'] ?? $product['image_urls'][0] ?? '';

        $this->template_head[] = sprintf('<link rel="canonical" href="%s" />', $this->_get_product_url());
        $this->template_head[] = sprintf('<meta name="og:title" content="%s">', $title);
        $this->template_head[] = sprintf('<meta name="og:description" content="%s">', $product['description'] ?? '');
        $this->template_head[] = sprintf('<meta name="og:url" content="%s">', $this->_get_product_url());
        $this->template_head[] = sprintf('<meta name="og:image" content="%s">', $imageUrl);
        $this->template_head[] = sprintf('<meta name="og:site_name" content="%s">', $this->_get_external_site_name());

        $this->template_head[] = '<meta name="twitter:card" content="summary_large_image">';
        $this->template_head[] = sprintf('<meta name="twitter:title" content="%s">', $title);
        $this->template_head[] = sprintf('<meta name="twitter:description" content="%s">', $product['description'] ?? '');
        $this->template_head[] = sprintf('<meta name="twitter:image" content="%s">', $imageUrl);
    }

    /**
     * Gets store id from template_head
     *
     * @since 1.4.0
     *
     * @return int|null
     */
    private function _get_store_id(): ?int
    {

        $storeId = null;
        foreach ($this->template_head as $element) {
            if (strpos($element, 'jane_frameless_embed_runtime_config')) {

                $regex = '/"storeId":(\d+)/';
                preg_match($regex, $element, $matched);
                $storeId = isset($matched[1]) ? (int)$matched[1] : null;
            }
        }

        return $storeId;
    }

    /**
     * Gets product id from URL
     *
     * @since 1.4.0
     *
     * @return int|null
     */
    private function _get_product_id_from_url(): ?int
    {

        preg_match('/\/products\/(\d+)\//', $_SERVER['REQUEST_URI'], $matched);

        return isset($matched[1]) ? (int)$matched[1] : null;
    }

    /**
     * Creates product url for current product path
     *
     * @since 1.4.0
     *
     * @return string
     */
	private function _get_product_url(): string
	{

		$product_url = home_url( $_SERVER['REQUEST_URI'] );
		if ( is_multisite() && strlen( home_url( '', 'relative' ) ) > 0 ) {
			// strip sub directory path from url because it shows up twice in url
			$exploded_uri = explode( '/', $_SERVER['REQUEST_URI'] );
			array_splice( $exploded_uri, 1, 1 );
			$product_path = implode( '/', $exploded_uri );
			$product_url  = home_url( $product_path );
		}

		return $product_url;
	}

    /**
     * Returns site name from external config
     *
     * @since 1.4.0
     *
     * @return string
     */
    private function _get_external_site_name(): string
    {

        $configs = $this->_get_external_configs();

        return $configs['name'] ?? '';
    }
}