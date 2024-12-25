<?php
if (!defined('ABSPATH')) {
    exit;
}

function wcapf_admin_menu() {
    add_menu_page(
        'WooCommerce Product Filters',
        'Product Filters',
        'manage_options',
        'wcapf-admin',
        'wcapf_admin_page_content',
        'dashicons-filter',
        58
    );
}
add_action('admin_menu', 'wcapf_admin_menu');

function wcapf_admin_page_content() {
    ?>
    <div class="wrap wcapf_admin">
        <h1>Manage WooCommerce Product Filters</h1>
        <?php settings_errors(); // Displays success or error notices ?>
        <h2 class="nav-tab-wrapper">
            <a href="?page=wcapf-admin&tab=form_manage" class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] == 'form_manage' ? 'nav-tab-active' : ''; ?>">Form Manage</a>
            <a href="?page=wcapf-admin&tab=form_style" class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] == 'form_style' ? 'nav-tab-active' : ''; ?>">Form Style</a>
            <a href="?page=wcapf-admin&tab=advance_settings" class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] == 'advance_settings' ? 'nav-tab-active' : ''; ?>">Advance Settings</a>
        </h2>

        <div class="tab-content">
            <?php
            $active_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'form_manage';

            if ($active_tab == 'form_manage') {
                ?>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('wcapf_options_group');
                    do_settings_sections('wcapf-admin');
                    submit_button();
                    ?>
                    <p>Use shortcode to show filter: <b>[wcapf_product_filter attribute="your-attribute" terms="your-terms1, your-terms2" category="your-cata1, your-cata2" tag="your-tag1, your-tag2"]</b></p>
                    <p>For button style filter use this shortcode: <b>[wcapf_product_filter_single name="conference-by-month"]</b></p>
                </form>
                <?php
            } 
            elseif ($active_tab == 'form_style') {
                include(plugin_dir_path(__FILE__) . 'form_style_tab.php');
            }
            elseif ($active_tab == 'advance_settings') {
                ?>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('wcapf_advance_settings');
                    do_settings_sections('wcapf-advance-settings');
                    submit_button();
                    ?>
                </form>
                <h2>Import &amp; Export Settings</h2>
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">Import Settings</th>
                            <td>    
                            <form method="post" enctype="multipart/form-data" action="<?php echo admin_url('admin-post.php'); ?>">
                                <input type="hidden" name="action" value="import_wcapf_settings">
                                <input type="file" name="wcapf_import_file" accept=".json" required>
                                <button type="submit" name="wcapf_import_button" id="wcapf_import_button" class="button button-primary">Import Settings</button>
                            </form>
                            </td>
                        </tr>
                        <tr><th scope="row">Export Settings</th>
                        <td>
                            <form method="post" action="admin-post.php">
                                <input type="hidden" name="action" value="export_wcapf_settings">
                                <button type="submit" name="wcapf_export_button" id="wcapf_export_button" class="button button-primary">Export Settings</button>
                            </form>
                        </td></tr></tbody></table>
                <?php
            }
            ?>
        </div>
    </div>
    <?php
}
// init settings first
include(plugin_dir_path(__FILE__) . 'settings-init.php');
// include form_manage content
include(plugin_dir_path(__FILE__) . 'form-manage.php');
// color converter include
include(plugin_dir_path(__FILE__) . 'color_name_to_hex.php');
// before save image & color
function save_wcapf_style_options($input) {
    foreach ($input as $attribute => $data) {
        // Handle color data
        if (isset($data['colors'])) {
            foreach ($data['colors'] as $term_slug => $value) {
                $input[$attribute]['colors'][$term_slug] = sanitize_hex_color($value); // Sanitize color
            }
        }

        // Handle image data
        if (isset($data['images'])) {
            foreach ($data['images'] as $term_slug => $value) {
                $input[$attribute]['images'][$term_slug] = esc_url_raw($value); // Sanitize URL
            }
        }
    }
    return $input;
}
add_filter('pre_update_option_wcapf_style_options', 'save_wcapf_style_options');


// include advance settings

include(plugin_dir_path(__FILE__) . 'advance_settings.php');









?>