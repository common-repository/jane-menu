<?php
/**
 * Template for the Edit page of the Store Config
 */

use IHeartJane\WebMenu\StoreConfigs;
use IHeartJane\WebMenu\Helpers;

$item = isset( $id ) ? StoreConfigs\get_config( $id ) : null;

$store_path  = sanitize_text_field( isset( $_POST['store_path']  ) ? $_POST['store_path']  : ( $item ? $item->store_path  : '' ) );
$page_id     = intval(              isset( $_POST['page_id']     ) ? $_POST['page_id']     : ( $item ? $item->page_id     : 0  ) );
$proxy_url   = sanitize_url(        isset( $_POST['proxy_url']   ) ? $_POST['proxy_url']   : ( $item ? $item->proxy_url   : '' ) );
$sitemap_url = sanitize_url(        isset( $_POST['sitemap_url'] ) ? $_POST['sitemap_url'] : ( $item ? $item->sitemap_url : '' ) );

$header = isset( $_POST['header']       ) ? stripcslashes( $_POST['header']       ) : ( $item ? Helpers\escape_for_html( stripcslashes( $item->header       ) ) : '' );
$footer = isset( $_POST['footer']       ) ? stripcslashes( $_POST['footer']       ) : ( $item ? Helpers\escape_for_html( stripcslashes( $item->footer       ) ) : '' );
$head   = isset( $_POST['head_content'] ) ? stripcslashes( $_POST['head_content'] ) : ( $item ? Helpers\escape_for_html( stripcslashes( $item->head_content ) ) : '' );

$store_path = ! empty( $store_path ) && ! isset( $_POST['store_path'] ) ? site_url() . "/" . $store_path . "/" : $store_path;

$post_type    = $page_id   ? get_post_type( $page_id ) : 'page';
$post_type    = $post_type ? $post_type : 'page';

$post_types   = Helpers\get_post_types_dropdown_html( $page_id );
$page_options = Helpers\get_page_dropdown_html( $post_type, $page_id );
// $page_options = Helpers\get_page_dropdown_html( 'post', $page_id );
// $page_options = Helpers\get_page_dropdown_html( 'hierarchical-post', $page_id );

?>
<div class="wrap">
	<form action="" method="post">
		<div id="universal-message-container">
			
            <h2><?php echo ( $item ? "Edit" : "Add" ) . ' Store Configuration'; ?></h2>
            
			<?php settings_errors(); ?>
            <div id="error_messages" class="error-box" style="display: none"></div>
            <div class="options">
				<div class="field_wrapper">
                    
					<div class="store_group">
						<p>
                            <?php echo $post_types; ?>
						</p>
						<p id="page_options">
                            <?php echo $page_options; ?>
						</p>
					</div>
                    
					<div class="store_group">
						<p>
							<label>Store Path</label>
							<br />
							<input type="text" class="regular-text" id="store_path" name="store_path" value="<?php echo esc_attr( $store_path ); ?>" readonly/>
						</p>
					</div>
                    
					<div class="store_group">
						<p>
							<label>Proxy URL<span> * </span></label>
							<br />
							<input type="url" class="regular-text" id="proxy_url" name="proxy_url" value="<?php echo esc_url( $proxy_url ); ?>" required="required" />
						</p>
					</div>
                    
					<div class="store_group">
						<p>
							<label>Sitemap URL<span> * </span></label>
							<br />
							<input type="url" class="regular-text" id="sitemap_url" name="sitemap_url" value="<?php echo esc_url( $sitemap_url ); ?>" required="required" />
						</p>
					</div>
                    
				</div>
            </div>
            
            <input type="hidden" name="field_id" value="<?php echo esc_attr( $item ? $item->id : '' ); ?>">
        </div>
        
        <?php submit_button( __( 'Save Changes', 'iheartjane' ), 'primary', 'save_changes', true, ['id' => 'submit'] ); ?>
        
        <?php if( ! empty( $header . $footer . $head ) ){ ?>
            <div id="legacy_fields" style="margin-top: 200px;">
                <h2>Legacy Fields</h2>
                <p>These are deprecated and will be removed in the future version of this plugin.</p>
                <div class="options">
                    <div class="field_wrapper" style="font-size:14px!important;">
                        <p>
                            <label>Header</label>
                            <br />
                            <textarea id="header" name="header" rows="3" cols="70" readonly><?php echo $header;?></textarea>
                        </p>
                        <p>
                            <label>Footer</label>
                            <br />
                            <textarea id="footer" name="footer" rows="3" cols="70" readonly><?php echo $footer;?></textarea>
                        </p>
                        <p>
                            <label>Content to be inserted in the <?php echo htmlspecialchars('<head>'); ?> tag</label>
                            <br />
                            <textarea id="head_content" name="head_content" rows="3" cols="70" readonly><?php echo $head;?></textarea>
                        </p>
                    </div>
                </div>
            </div>
        <?php } ?>
    </form>
</div>