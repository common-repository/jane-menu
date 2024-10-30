<?php

namespace IHeartJane\WebMenu;

/**
 * Helper Sitemap functions
 *
 * @since 1.4.0
 */
class SitemapHelper {

	/**
	 * MULTISITE SUPPORT
	 * Full path of the remote sitemap folder.
	 * Has a trailing slash.
	 *
	 *
	 * @since 1.4.0
	 */
	public static function get_custom_sitemap_dir(): string {

		return trailingslashit( wp_upload_dir()['basedir'] ) . 'jane-menu/';
	}

	/**
	 * MULTISITE SUPPORT
	 * Full path of the remote sitemap.
	 *
	 * @since 1.4.0
	 */
	public static function get_custom_sitemap_path(): string {

		return trailingslashit( wp_upload_dir()['basedir'] ) . 'jane-menu/sitemap.xml';
	}

	/**
	 * MULTISITE SUPPORT
	 * Full URL of the remote sitemap.
	 *
	 * @since 1.4.0
	 */
	public static function get_custom_sitemap_url(): string {

		$url = trailingslashit( wp_upload_dir()['baseurl'] ) . 'jane-menu/sitemap.xml';
		if ( is_ssl() ) {
			$url = str_replace( 'http://', 'https://', $url );
		}

		return $url;
	}
}
