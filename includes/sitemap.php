<?php
/**
 * Robots and sitemaps related functions
 * 
 */

namespace IHeartJane\WebMenu\Sitemap;

use IHeartJane\WebMenu\Constants;
use IHeartJane\WebMenu\SitemapHelper;
use IHeartJane\WebMenu\StoreConfigs;

/**
 * Adds all Store Configs sitemap URLs to robots.txt
 * 
 * @uses     IHeartJane\WebMenu\Constants\OPTION_SITEMAP_ENABLED_NAME       Constants\OPTION_SITEMAP_ENABLED_NAME
 *
 * @see      https://developer.wordpress.org/reference/hooks/robots_txt/    Codex, filter: robots_txt
 * 
 * @param    string    $output    The robots.txt output
 * @param    bool      $public    Whether the site is considered "public"
 * 
 * @since    1.0.0
 * @since    1.3.2     Only the local sitemap index is appended
 * 
 * @return   string    Appended URLs at the end of the robots.txt file content
 */
function append_to_robots_txt( $output, $public ) {

	if ( function_exists( 'get_sites' ) ) {
		$sites = get_sites();
		foreach ( $sites as $site ) {
			$tmpBlogId = $site->blog_id;
			switch_to_blog( $tmpBlogId );
			if ( ! file_exists( SitemapHelper::get_custom_sitemap_path() ) ) {
				update_remote_sitemap();
			}

			if ( get_option( Constants\OPTION_SITEMAP_ENABLED_NAME ) && file_exists( SitemapHelper::get_custom_sitemap_path() ) ) {
				$output .= sprintf( 'Sitemap: %s', SitemapHelper::get_custom_sitemap_url() ) . PHP_EOL;
			}
		}
		restore_current_blog();
	} else {
		if ( ! file_exists( SitemapHelper::get_custom_sitemap_path() ) ) {
			update_remote_sitemap();
		}

		if ( get_option( Constants\OPTION_SITEMAP_ENABLED_NAME ) && file_exists( SitemapHelper::get_custom_sitemap_path() ) ) {
			$output .= sprintf( 'Sitemap: %s', SitemapHelper::get_custom_sitemap_url() ) . PHP_EOL;
		}
	}

	return $output;
}
add_filter( 'robots_txt', __NAMESPACE__ . '\\' . 'append_to_robots_txt', 99, 2 );

/**
 * Creates or updates the sitemap index file
 * 
 * The sitemap index file lists all the remote sitemap URLs from existing Store Configs
 * @
 * @uses     IHeartJane\WebMenu\Constants\OPTION_SITEMAP_ENABLED_NAME   Constants\OPTION_SITEMAP_ENABLED_NAME
 * @uses     IHeartJane\WebMenu\StoreConfigs\get_all_configs()          StoreConfigs\get_all_configs()
 * 
 * @see      https://developer.wordpress.org/reference/functions/wp_upload_dir/ Codex, function: wp_upload_dir
 * @see      https://developer.wordpress.org/reference/functions/wp_mkdir_p/    Codex, function: wp_mkdir_p
 * 
 * @since    1.3.2
 * 
 * @return   bool    True on success, otherwise false
 */
function update_remote_sitemap(){
    
    
    // empty and remove the existing folder
    
    if( is_dir(SitemapHelper::get_custom_sitemap_dir()) ){
        
        $files = array_diff( scandir(SitemapHelper::get_custom_sitemap_dir()), array( '.', '..' ) );

        foreach( $files as $file ){
            
            if( ! is_dir( SitemapHelper::get_custom_sitemap_dir() . $file ) ){
                
                unlink( SitemapHelper::get_custom_sitemap_dir() . $file );
            }
            
        }
        
        rmdir(SitemapHelper::get_custom_sitemap_dir());
    }
    
    
    // if the option is turned on, proceed
    
    if( ! get_option( Constants\OPTION_SITEMAP_ENABLED_NAME ) ){
        
        return false;
    }
    
    
    // if we have anything to write, proceed
    
    $store_configs = StoreConfigs\get_all_configs();
    
    if( empty( $store_configs ) ){
        return false;
    }
    
    
    // attempt to create the folder
    
    $directory_exists = wp_mkdir_p(SitemapHelper::get_custom_sitemap_dir());
    
    if( ! $directory_exists ){
        
        return false;
    }
    
    
    // generate the xml content
    
    $output = [
        '<?xml version="1.0" encoding="UTF-8"?>',
        '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
    ];
    
    foreach( $store_configs as $store_config ){
        
        $sitemap_url = $store_config->sitemap_url;
        
        $output[] = '  <sitemap>';
        $output[] = '    <loc>' . $store_config->sitemap_url . '</loc>';
        $output[] = '  </sitemap>';
    }
    
    $output[] = '</sitemapindex>';
    
    $xml = implode( PHP_EOL, $output );
    
    // Helpers\debug_log( $xml, "sitemap-update_remote_sitemap-xml" );
    
    
    $success = file_put_contents( SitemapHelper::get_custom_sitemap_path(), $xml );
    
    return $success ? true : false;
}
