<?php

namespace IHeartJane\WebMenu\Providers;

use WPSEO_Sitemap_Provider;

/**
 * Adding sitemap url from CurrentStoreConfig to the sitemap.xml if Yoast plugin is installed
 *
 * @since 1.4.0
 */
class YoastJaneSitemapProvider extends JaneSitemapProvider implements WPSEO_Sitemap_Provider{

    /**
     * @inheritDoc
     */
    public function handles_type($type)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function get_index_links($max_entries)
    {
        return $this->get_sitemap_entries();
    }

    /**
     * @inheritDoc
     */
    public function get_sitemap_links($type, $max_entries, $current_page)
    {
        return [];
    }
}

