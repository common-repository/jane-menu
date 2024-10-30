jQuery( document ).ready( function($){
    'use strict';
	console.log( "Ready: jQuery " + $.fn.jquery );
    var dirtyFields = false;
    var proxyTimer = 0;
    function getErrorMessage(storePath) {
        return `The path for this Wordpress page does not match the path configured in the Jane business admin. To correct this, please set the URL in the business admin to '<b>${storePath}</b>'. For more information, please see our documentation <a href="https://docs.iheartjane.com/jane-boost/setting-up-jane-boost-with-wordpress#creating-an-embed-config" target="_blank">here</a>.`;
    }

    $('body').on('change', '#post_type', function(){
        dirtyFields = true;
        let post_type = $(this).val();
        let page_id   = $(this).data('page_id');
        disableSubmit();
        // console.log( "post_type: ", post_type );
        // console.log( "Page ID: ",   page_id   );
        
        
        $('#page_options').html('Please wait...');
        
        
        let data = {
            'post_type' : post_type,
            'page_id'   : page_id,
        };
        
        data.action = 'jane_' + 'get_post_type_items';
        
        $.post( jane_object.ajax_url, data, function( response ){
            
            // console.log( "Ajax response:", response );
            // console.log( "Ajax data:", response.data );
            
            if( response.success === true ){
                // console.clear();
                // console.log( response.data );
                
                $('#page_options').html( response.data );
                $('#page_id').trigger('change');
                    
            } else {
                
                if( response.data.message ){
                    
                    $('#page_options').html( response.data.message );
                    $('#page_id').trigger('change');
                    
                } else {
                    
                    $('#page_options').html( 'An unknown error has occured, check the console log.' );
                    $('#page_id').trigger('change');

                    console.log( "Failure:", response.data );
                }
            }
            verifyFields();
        }, "json");
        
    });
    
    $('body').on('change', '#page_id', function(){
        dirtyFields = true;
        let page_id = $(this).val();
        disableSubmit();
        // console.log( "Page ID: ", page_id );
        
        
        $('#store_path').val('Please wait...');
        
        
        let data = {
            'page_id'   : page_id,
        };
        
        data.action = 'jane_' + 'get_page_path';
        
        $.post( jane_object.ajax_url, data, function( response ){
            
            // console.log( "Ajax response:", response );
            // console.log( "Ajax data:", response.data );
            
            if( response.success === true ){
                // console.clear();
                // console.log( response.data );
                
                $('#store_path').val( response.data );
                    
            } else {
                
                if( response.data.message ){
                    
                    $('#store_path').val( response.data.message );
                    
                } else {
                    
                    $('#store_path').val( 'An unknown error has occured, check the console log.' );
                    
                    console.log( "Failure:", response.data );
                }
            }

            verifyFields();
        }, "json");
        
    });

    $('body').on('keyup', '#proxy_url', function(){
        disableSubmit();
        dirtyFields = true;
        if (proxyTimer) {
            clearTimeout(proxyTimer);
        }
        proxyTimer = setTimeout(verifyFields, 400);
    });

    function verifyFields() {
        var proxyUrl = $('#proxy_url').val();
        var storePath = $('#store_path').val();
        var postType = $('#post_type').val();
        var pageId = $('#page_id').val();

        if (!dirtyFields) {
            return;
        }

        if (proxyUrl.length === 0 || storePath.length === 0) {
            return;
        }

        if (postType === 0 || pageId === undefined || parseInt(pageId) === 0) {
            disableSubmit();
            return;
        }

        let data = {
            'proxy_url': proxyUrl,
            'store_path': storePath
        };

        data.action = 'jane_' + 'verify_store_path';

        $.post( jane_object.ajax_url, data, function( response ){
            if (response.data.valid) {
                dirtyFields = false;
                enableSubmit();
                hideErrorBox();
                setErrorMessage('');
            } else {
                dirtyFields = true;
                setErrorMessage(getErrorMessage(storePath));
                showErrorBox();
                disableSubmit();
            }
        }, "json");
    }

    function disableSubmit() {
        $('#submit').attr('disabled', 'disabled');
    }

    function enableSubmit() {
        $('#submit').removeAttr('disabled');
    }

    function showErrorBox() {
        $('#error_messages').show();
    }

    function hideErrorBox() {
        $('#error_messages').hide();
    }

    function setErrorMessage(errorMessage) {
        $('#error_messages').html(errorMessage);
    }
});