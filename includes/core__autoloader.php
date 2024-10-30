<?php
/**
 * Custom autoloader
 * 
 */

namespace IHeartJane\WebMenu\Core\Autoloader;

/**
 * Autoloads the classes according to PSR-4 recommendation standard
 * 
 * @see      https://www.php.net/spl_autoload_register PHP Manual: spl_autoload_register()
 * 
 * @param    string    $dir             Full path from where the namespaces will have their relative folders
 * @param    array     $namespaces      Array of namespaces with FQCN base as the key and relative path as the value
 * 
 * @since    1.3.0
 * 
 * @return   void
 */
function load_package( $dir, $namespaces ){
    
    foreach( $namespaces as $namespace => $classpaths ){
        
        if( ! is_array( $classpaths ) ){
            
            $classpaths = array( $classpaths );
        }
        
        spl_autoload_register( function( $classname ) use ( $namespace, $classpaths, $dir ){
            
            // Check if the namespace matches the class we are looking for
            if( preg_match( "#^" . preg_quote( $namespace ) . "#", $classname ) ){
                
                // Remove the namespace from the file path since it's psr4
                $classname = str_replace( $namespace, "", $classname );
                $filename  = preg_replace( "#\\\\#", "/", $classname ) . ".php";
                
                foreach( $classpaths as $classpath ){
                    
                    $fullpath = $dir . "/" . $classpath . "/$filename";
                    
                    if( file_exists( $fullpath ) ){
                        
                        include_once $fullpath;
                    }
                }
            }
        });
    }
}
