<?php

namespace IHeartJane\WebMenu\Providers;

use WP_Sitemaps_Provider;
use function IHeartJane\WebMenu\StoreConfigs\get_all_configs;

/**
 * Adding sitemap url from CurrentStoreConfig to the sitemap.xml
 *
 * @since 1.4.0
 */
class JaneSitemapProvider extends WP_Sitemaps_Provider {

    // make visibility not protected
    public $name = 'JaneSitemapProvider';

    /**
     * @inheritdoc
     */
    public function get_sitemap_entries() {
        $sitemaps = array();

        foreach ( get_all_configs() as $config ) {
            $sitemap_entry = array(
                'loc' => $config->sitemap_url,
                'lastmod' => (new \DateTime())->format(\DateTimeInterface::ATOM),
            );

            $sitemaps[] = $sitemap_entry;
        }

        return $sitemaps;
    }

    /**
     * Not used in our class
     */
    public function get_url_list( $page_num, $object_subtype = '' ) {
        return [];
    }

    /**
     * Not used in our class
     */
    public function get_max_num_pages( $object_subtype = '' ) {
        return 0;
    }
}

