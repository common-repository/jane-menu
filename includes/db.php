<?php
/**
 * Database related functions
 * 
 */

namespace IHeartJane\WebMenu\DB;

use IHeartJane\WebMenu\Constants;
use IHeartJane\WebMenu\Helpers;

/**
 * Creates Store Config table
 * 
 * Also updates the table if it's using an old table structure.
 * 
 * @see     https://codex.wordpress.org/Creating_Tables_with_Plugins#Adding_an_Upgrade_Function     Codex, function: dbDelta() # Upgrade Function
 *
 * @uses    IHeartJane\WebMenu\Constants\DB_TABLE_NAME Constants\DB_TABLE_NAME
 * 
 * @since   1.0.0
 * @since   1.3.0   New structure
 * 
 * @return  string[]  Array of strings containing the changes, format table_name.column_name => description of the performed action
 */
function create_or_update_config_table(){
    
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $table_name = $wpdb->prefix . Constants\DB_TABLE_NAME;
    
    
    // alter the table structure or create the table
    
    $query = "
    
        CREATE TABLE $table_name (
            id mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
            page_id bigint(20) unsigned NOT NULL,
            proxy_url varchar(255) NOT NULL,
            sitemap_url varchar(255) NOT NULL,
            store_path varchar(255) NOT NULL,
            header longtext,
            footer longtext,
            head_content longtext,
            PRIMARY KEY  (id)
        ) $charset_collate;
    ";
    
    
    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
    
    $result = dbDelta( $query );
    
    // Helpers\debug_log( $result, "db-create_or_update_config_table-result" );
    
    
    
    // dbDelta() does not drop columns!
    
    $query = "DESCRIBE $table_name";
    
    $columns = $wpdb->get_results( $query, OBJECT_K );
    
    if( ! empty( $columns["store_id"] ) ){
        
        $query = "ALTER TABLE $table_name DROP COLUMN store_id";
        
        $result = $wpdb->query( $query );
        
        // Helpers\debug_log( $result, "db-create_or_update_config_table-queries-result" );
        
    }
    
    
    
    // update the current version of the db structure
    update_option( 'jane_web_db_version', Constants\PLUGIN_VER );
    
    return $result;
}

/**
 * Deletes Store Config table
 *
 * @uses    IHeartJane\WebMenu\Constants\DB_TABLE_NAME Constants\DB_TABLE_NAME
 * 
 * @since   1.1.0
 * 
 * @return  void
 */
function drop_config_table(){
    
    global $wpdb;
    
    $table_name = $wpdb->prefix . Constants\DB_TABLE_NAME;
    
    $query = "DROP TABLE IF EXISTS $table_name";
    
    $wpdb->query( $query );
}
