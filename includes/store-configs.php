<?php
/**
 * Store config related functions
 * 
 */

namespace IHeartJane\WebMenu\StoreConfigs;

use IHeartJane\WebMenu\Constants;
use IHeartJane\WebMenu\Helpers;

/**
 * Inserts or updates a new Store Config
 * 
 * @uses    IHeartJane\WebMenu\Constants\DB_TABLE_NAME Constants\DB_TABLE_NAME
 * 
 * @param   array       $args     Array of Store Config properties, if ID is set, it will get updated, otherwise inserted
 * 
 * @since   1.0.0
 * 
 * @return  int|\WP_Error|false   The Store Config ID if it's inserted or updated, \WP_Error if initial validation fails, or false on error
 */
function insert_config( $args = [] ){
    
    global $wpdb;
    
    $defaults = array(
        'id'            => null,
        'page_id'       => 0,
        'proxy_url'     => '',
        'sitemap_url'   => '',
        'store_path'    => '',
        'header'        => '',
        'footer'        => '',
        'head_content'  => '',
    );

    $args       = wp_parse_args( $args, $defaults );
    
    // $args["page_id"] = intval( $args["page_id"] );
    
    $table_name = $wpdb->prefix . Constants\DB_TABLE_NAME;
    
    // some basic validation
    if( empty( $args['page_id'] ) ){
        
        return new WP_Error( 'no-page_id', __( 'No Page provided.', 'iheartjane' ) );
    }
    
    if( empty( $args['proxy_url'] ) ){
        
        return new WP_Error( 'no-proxy_url', __( 'No Proxy URL provided.', 'iheartjane' ) );
    }
    
    if( empty( $args['sitemap_url'] ) ){
        
        return new WP_Error( 'no-sitemap_url', __( 'No Sitemap URL provided.', 'iheartjane' ) );
    }
    
    if( empty( $args['store_path'] ) ){
        
        return new WP_Error( 'no-store_path', __( 'No Store Path provided.', 'iheartjane' ) );
    }
    
    // remove row id to determine if new or update
    $row_id = (int) $args['id'];
    unset( $args['id'] );
    
    if( ! $row_id ){
        
        // insert a new
        if( $wpdb->insert( $table_name, $args ) ) {
            return $wpdb->insert_id;
        }
        
    } else {
        
        // do update method here
        $result = $wpdb->update( $table_name, $args, array( 'id' => $row_id ) );
    
        if( is_int( $result ) ){
            
            return $row_id;
        }
    }
    
    return false;
}

/**
 * Deletes a Store Config from database
 *
 * @uses    IHeartJane\WebMenu\Constants\DB_TABLE_NAME Constants\DB_TABLE_NAME
 * 
 * @param   int[]   $ids    Array of Store Config IDs
 *
 * @since   1.0.0
 * 
 * @return  void    The query simply deletes the rows of found IDs, if any
 */
function delete_config( $ids = [] ){
    
    global $wpdb;
    
    $table_name = $wpdb->prefix . Constants\DB_TABLE_NAME;
    
    $ids = implode( ',', array_map( 'absint', $ids ) );
    
    $wpdb->query( "DELETE FROM $table_name WHERE ID IN( $ids )" );
}


/**
 * Gets a single Store Config from database
 *
 * @uses    IHeartJane\WebMenu\Constants\DB_TABLE_NAME Constants\DB_TABLE_NAME
 * 
 * @param   int   $config_id     Store Config ID
 *
 * @since   1.0.0
 * 
 * @return  object|null  Store Config database row as an object, null if not found
 */
function get_config( $config_id = 0 ){
    
    global $wpdb;
    
    $table_name = $wpdb->prefix . Constants\DB_TABLE_NAME;
    
    return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ". $table_name . " WHERE id = %d", $config_id ) );
}

/**
 * Gets Store Configs by arguments
 * 
 * Arguments include LIKE search proxy_url or store_path,
 * offset (default: 0) and number of returned results (default: 20),
 * ordering by database column (default: id) and ordering direction (default: ASC)
 *
 * @uses    IHeartJane\WebMenu\Constants\DB_TABLE_NAME Constants\DB_TABLE_NAME
 * 
 * @param   array   $args   Optional array of optional arguments
 *
 * @since   1.0.0
 * 
 * @return  object[]|null   Array of Store Config database rows as objects, null if not found
 */
function get_filtered_configs( $args = [] ){
    
    global $wpdb;

    $defaults = array(
        'search'     => '',
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'id',
        'order'      => 'ASC',
    );

    $args = wp_parse_args( $args, $defaults );
    
    $table_name = $wpdb->prefix . Constants\DB_TABLE_NAME;
    
    
    
    $query = "
        SELECT      *
        FROM        $table_name
    ";
    
    
    $search = "";
    
    if( ! empty( $args['search'] ) ){
        
        $search .= "
            WHERE   proxy_url  LIKE %s
                OR  store_path LIKE %s
        ";
        
        $search = $wpdb->prepare(
            $search,
            "%" . $args['search'] . "%",
            "%" . $args['search'] . "%"
        );
    }
    
    
    $order_and_offset = "
        ORDER BY    %s %s
        LIMIT       %d, %d
    ";
    
    $order_and_offset = $wpdb->prepare(
        $order_and_offset,
        [
            $args['orderby'],
            $args['order'],
            $args['offset'],
            $args['number'],
        ]
    );
    
    
    $items = $wpdb->get_results( $query . $search . $order_and_offset );

    return $items;
}

/**
 * Get all Store Configs
 *
 * @uses    IHeartJane\WebMenu\Constants\DB_TABLE_NAME Constants\DB_TABLE_NAME
 * 
 * @since   1.0.0
 * 
 * @return  object[]|array   Array of Store Config database rows as objects, empty array if none found. Array keys are Store Config ID
 */
function get_all_configs(){
    
    global $wpdb;
    
    $table_name = $wpdb->prefix . Constants\DB_TABLE_NAME;
    
    $items = $wpdb->get_results( "SELECT * FROM " . $table_name, OBJECT_K );
    
    return ! empty( $items ) ? $items : [];
}

/**
 * Gets the total count of Store Configs
 *
 * @uses     IHeartJane\WebMenu\Constants\DB_TABLE_NAME Constants\DB_TABLE_NAME
 * 
 * @since    1.0.0
 * 
 * @return   int     Returns the count of Store Config IDs
 */
function get_total_configs_count(){
    
    global $wpdb;
    
    $table_name = $wpdb->prefix . Constants\DB_TABLE_NAME;
    
    return (int) $wpdb->get_var( "SELECT COUNT(*) FROM " . $table_name );
}
