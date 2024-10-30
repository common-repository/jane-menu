<?php
/**
 * Template for the List page of the Store Config
 */

use IHeartJane\WebMenu\Constants;
use IHeartJane\WebMenu\SitemapHelper;

$sitemap_enabled = get_option( Constants\OPTION_SITEMAP_ENABLED_NAME );

$url_new = admin_url( 'admin.php?page=' . Constants\ADMIN_PAGE_NAME . '&action=new' );

$sitemap_url_displayed = $sitemap_enabled ? '' : ' style="display: none;"';

?>

<div id="jane_configuration">

    <div id="jane_configuration_content">
        <form method="post">
            <h2><?php settings_errors(); ?></h2>
            <table class="form-table">
                <tbody>
                    <tr class="row-store-id" style="display: none;">
                        <th scope="row">
                            <label for="menu_options"><?php _e( 'Menu Type', 'iheartjane' ); ?></label>
                        </th>
                        <td>
                            <p>
                                <input type="radio" id="std" name="jane_menu_type" value="std" disabled <?php echo 'checked="checked"'; ?>>
                                <label for="standard">Standard</label>&nbsp;&nbsp;&nbsp;
                                <input type="radio" id="cbd" name="jane_menu_type" value="cbd" disabled <?php echo ''; ?>>
                                <label for="custom">Semi Custom</label>
                            </p>
                        </td>
                    </tr>
                    
                    <tr class="row-jane-sitemap-enabled">
                        <th scope="row">
                            <label for="jane_sitemap_enabled">
                                <?php _e( 'Add sitemap index to robots.txt', 'iheartjane' ); ?>
                            </label>
                        </th>
                        <td>
                            <input type="checkbox" name="jane_sitemap_enabled" id="jane_sitemap_enabled" value="1" <?php checked( $sitemap_enabled, 1 ); ?> />
                        </td>
                    </tr>
                    
                    <tr class="row-sitemap-index"<?php echo $sitemap_url_displayed; ?>>
                        <th scope="row">
                            <?php _e( 'Sitemap index location', 'iheartjane' ); ?>
                        </th>
                        <td>
                            <input type="text" readonly id="jane_sitemap_index_location" value="<?php echo esc_attr( SitemapHelper::get_custom_sitemap_url() ); ?>"/>
                        </td>
                    </tr>
                    
                </tbody>
            </table>
            <?php submit_button( __( 'Save Changes', 'iheartjane' ), 'primary', 'save_menu_options' ); ?>
        </form>
    </div>
    
    <div id="jane_configuration_control">Settings</div>
</div>
    
<div class="wrap">
    
    <h2 style="margin-top:2em"><?php _e( 'Store Configurations', 'iheartjane' ); ?> <a href="<?php echo esc_url( $url_new ); ?>" class="add-new-h2"><?php _e( 'Add New', 'iheartjane' ); ?></a></h2>

    <form method="post">
        <input type="hidden" name="page" value="<?php echo esc_attr( Constants\ADMIN_PAGE_NAME ); ?>" >

        <?php
            $list_table = new \IHeartJane\WebMenu\StoreConfigs\Table_List();
            $list_table->prepare_items();
            $list_table->search_box( 'Search', 'search_id' );
            $list_table->display();
        ?>
    </form>
    
</div>