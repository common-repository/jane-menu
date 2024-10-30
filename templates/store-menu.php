<?php
/**
 * Template that receives the HTML from the React App and outputs it
 */

use IHeartJane\WebMenu\StoreConfigs;


global $jane__current_config;

if( $jane__current_config->get_template_response_code() !== 200 ){
    
    status_header( 404 );
    
    return;
}

status_header( 200 );


echo '<html>';
    echo '<head>';

        echo $jane__current_config->get_template_html_head();

    echo '</head>';
    echo '<body>';

        echo $jane__current_config->get_template_html_body();

    echo '</body>';
echo '</html>';


