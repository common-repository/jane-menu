jQuery( document ).ready( function($){
    'use strict';
	console.log( "Ready: jQuery " + $.fn.jquery );
    
    $('#jane_configuration_control').on('click', function(){
        
        $('#jane_configuration_content').slideToggle();
    });
    
    $('#jane_sitemap_enabled').on('change', function(){
        
        let selected = $(this).prop('checked');
        
        $('.row-sitemap-index').toggle( selected );
    });
    
    
    $('#jane_sitemap_index_location').css( 'width', ( $('#jane_sitemap_index_location').val().length + 1 ) + "ch" );
    
    $('#jane_sitemap_index_location').on('click', function(){
        
        console.log( 'sitemap location clicked' );
        
        let el = $(this)[0];
        
        el.setSelectionRange( 0, el.value.length );
        
    });
});