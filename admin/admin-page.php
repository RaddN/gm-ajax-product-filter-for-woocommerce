<?php
if (!defined('ABSPATH')) {
    exit;
}

function dapfforwc_admin_menu() {
    add_menu_page(
        'WooCommerce Product Filters',
        'Product Filters',
        'manage_options',
        'dapfforwc-admin',
        'dapfforwc_admin_page_content',
        'dashicons-filter',
        58
    );
}
add_action('admin_menu', 'dapfforwc_admin_menu');

function get_loading_effects() {
    $loading_effects = [
        [
            'name' => 'Basic',
            'value' => 'basic',
            'html' =>'<div class="basic" style="display:none;" id="loader"></div>',
            'css' => '.basic {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 9px solid;
    border-color: #dbdcef;
    border-right-color: #474bff;
    animation: basic-d3wgkg 1s infinite linear;
 }
 
 @keyframes basic-d3wgkg {
    to {
       transform: rotate(1turn);
    }
 }'
        ],
        [
            'name' => 'Comet',
            'value' => 'comet',
            'html' =>'<div class="comet" style="display:none;" id="loader"></div>',
            'css' => ' .comet {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: conic-gradient(#0000 10%,#474bff);
    -webkit-mask: radial-gradient(farthest-side,#0000 calc(100% - 9px),#000 0);
    animation: spinner-zp9dbg 1s infinite linear;
 }
 
 @keyframes spinner-zp9dbg {
    to {
       transform: rotate(1turn);
    }
 }'
        ],
        [
            'name' => 'Counter Arcs',
            'value' => 'counter_arcs',
            'html' =>'<div class="counter_arcs" style="display:none;" id="loader"></div>',
            'css' => '.counter_arcs {
    width: 56px;
    height: 56px;
    display: grid;
    animation: spinner-plncf9 4s infinite;
 }
 
 .counter_arcs::before,
 .counter_arcs::after {
    content: "";
    grid-area: 1/1;
    border: 9px solid;
    border-radius: 50%;
    border-color: #474bff #474bff #0000 #0000;
    mix-blend-mode: darken;
    animation: counter_arcs-plncf9 1s infinite linear;
 }
 
 .counter_arcs::after {
    border-color: #0000 #0000 #dbdcef #dbdcef;
    animation-direction: reverse;
 }
 
 @keyframes counter_arcs-plncf9 {
    100% {
       transform: rotate(1turn);
    }
 }'
        ],
        [
            'name' => 'Dot Ring',
            'value' => 'dot_ring',
            'html' =>'<div class="dot_ring" style="display:none;" id="loader"></div>',
            'css' => '.dot_ring {
   width: 11.2px;
   height: 11.2px;
   animation: dot_ring-z355kx 1s infinite linear;
   border-radius: 11.2px;
   box-shadow: 28px 0px 0 0 #474bff, 17.4px 21.8px 0 0 #474bff, -6.2px 27.2px 0 0 #474bff, -25.2px 12px 0 0 #474bff, -25.2px -12px 0 0 #474bff, -6.2px -27.2px 0 0 #474bff, 17.4px -21.8px 0 0 #474bff;
}

@keyframes dot_ring-z355kx {
   to {
      transform: rotate(360deg);
   }
}'
        ],
        [
            'name' => 'Half Ring',
            'value' => 'half_ring',
            'html' =>'<div class="half_ring" style="display:none;" id="loader"></div>',
            'css' => '.half_ring {
   width: 11.2px;
   height: 11.2px;
   border-radius: 11.2px;
   box-shadow: 28px 0px 0 0 rgba(71,75,255,0.2), 22.7px 16.5px 0 0 rgba(71,75,255,0.4), 8.68px 26.6px 0 0 rgba(71,75,255,0.6), -8.68px 26.6px 0 0 rgba(71,75,255,0.8), -22.7px 16.5px 0 0 #474bff;
   animation: half_ring-b87k6z 1s infinite linear;
}

@keyframes half_ring-b87k6z {
   to {
      transform: rotate(360deg);
   }
}'
        ],
        [
            'name' => 'Chase',
            'value' => 'chase',
            'html' =>'<div class="chase" style="display:none;" id="loader"></div>',
            'css' => '.chase {
   position: relative;
   width: 22.4px;
   height: 22.4px;
}

.chase::before,
.chase::after {
   content: "";
   width: 100%;
   height: 100%;
   display: block;
   animation: chase-b4c8mmmd 0.5s backwards, chase-49opz7md 1.25s 0.5s infinite ease;
   border: 5.6px solid #474bff;
   border-radius: 50%;
   box-shadow: 0 -33.6px 0 -5.6px #474bff;
   position: absolute;
}

.chase::after {
   animation-delay: 0s, 1.25s;
}

@keyframes chase-b4c8mmmd {
   from {
      box-shadow: 0 0 0 -5.6px #474bff;
   }
}

@keyframes chase-49opz7md {
   to {
      transform: rotate(360deg);
   }
}'
        ]
    ];

    return json_encode($loading_effects);
}

function dapfforwc_admin_page_content() { global $dapfforwc_options;
    ?>
    <div class="wrap wcapf_admin">
        <h1>Manage WooCommerce Product Filters</h1>
        <?php settings_errors(); // Displays success or error notices
        $nonce = wp_create_nonce('dapfforwc_tab_nonce');
        ?>
        <h2 class="nav-tab-wrapper">
            <a href="?page=dapfforwc-admin&tab=form_manage&_wpnonce=<?php echo esc_attr($nonce); ?>" class="nav-tab <?php echo isset($_GET['tab']) && sanitize_text_field(wp_unslash($_GET['tab'])) == 'form_manage' ? 'nav-tab-active' : ''; ?>">Form Manage</a>
            <a href="?page=dapfforwc-admin&tab=form_style&_wpnonce=<?php echo esc_attr($nonce); ?>" class="nav-tab <?php echo isset($_GET['tab']) && sanitize_text_field(wp_unslash($_GET['tab'])) == 'form_style' ? 'nav-tab-active' : ''; ?>">Form Style</a>
            <a href="?page=dapfforwc-admin&tab=advance_settings&_wpnonce=<?php echo esc_attr($nonce); ?>" class="nav-tab <?php echo isset($_GET['tab']) && sanitize_text_field(wp_unslash($_GET['tab'])) == 'advance_settings' ? 'nav-tab-active' : ''; ?>">Advance Settings</a>
        </h2>

        <div class="tab-content">
            <?php
            $active_tab = 'form_manage'; // Default tab
            if (isset($_GET['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'dapfforwc_tab_nonce')) {
                $active_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'form_manage';
            }            

            if ($active_tab == 'form_manage') {
                ?>
                <form method="post" action="options.php">
                <div id="custom-loading-popup" style="display:none;">
                    <div class="popup-content">
                        <span class="close-popup">&times;</span>
                        <h2>Customize Loading Effect</h2>
                        <label for="loader_html">HTML</label>
                        <p style="text-align:left;"><b>Note:</b> style="display:none;" id="loader" is required </p>
                        <textarea id="loader_html" placeholder="html values will be here" name="dapfforwc_options[loader_html]"><?php echo esc_html($dapfforwc_options["loader_html"]); ?></textarea>
                        <label for="loader_css">CSS</label>
                        <textarea id="loader_css" placeholder="CSS values will be here" name="dapfforwc_options[loader_css]"><?php echo esc_html($dapfforwc_options["loader_css"]); ?></textarea>
                        <p>Select a loading effect (or get html & css code from anywhere paste & save): </p>
                        <div class="loading-options">
                            <?php
                            $loading_effects_json = get_loading_effects();
                            $loading_effects = json_decode($loading_effects_json, true);

                            foreach ($loading_effects as $effect) {
                                ?>
                                <div class="loading-option" data-value="<?php echo esc_attr($effect['value']); ?>" 
                     data-html="<?php echo esc_html($effect['html']); ?>" 
                     data-css="<?php echo esc_html($effect['css']); ?>">
                                    <style><?php echo esc_attr($effect['css']); ?></style>
                                    <div class="<?php echo esc_attr($effect['value']); ?>"></div>
                                    <span class="effect-name"><?php echo esc_html($effect['name']); ?></span>
                                    <div class="checkmark" style="display:none;">✓</div>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <button id="save-effect" class="button button-primary">Save</button>
                    </div>
                    
                </div>
                    <?php
                    settings_fields('dapfforwc_options_group');
                    do_settings_sections('dapfforwc-admin');
                    submit_button();
                    ?>
                    <p>Use shortcode to show filter: <b>[plugincy_filters attribute="your-attribute" terms="your-terms1, your-terms2" category="your-cata1, your-cata2" tag="your-tag1, your-tag2"]</b></p>
                    <p>For button style filter use this shortcode: <b>[plugincy_filters_single name="conference-by-month"]</b></p>
                    <p>For show currently selected filter above product: <b>[plugincy_filters_selected]</b></p>
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
                    settings_fields('dapfforwc_advance_settings');
                    do_settings_sections('dapfforwc-advance-settings');
                    submit_button();
                    ?>
                </form>
                <h2>Import &amp; Export Settings</h2>
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">Import Settings</th>
                            <td>    
                            <form method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                <?php wp_nonce_field( 'dapfforwc_import_settings_nonce' ); ?>
                                <input type="hidden" name="action" value="dapfforwc_import_settings">
                                <input type="file" name="dapfforwc_import_file" accept=".json" required>
                                <button type="submit" name="wcapf_import_button" id="wcapf_import_button" class="button button-primary">Import Settings</button>
                            </form>
                            </td>
                        </tr>
                        <tr><th scope="row">Export Settings</th>
                        <td>
                            <form method="post" action="admin-post.php">
                                <input type="hidden" name="action" value="dapfforwc_export_settings">
                                <button type="submit" name="wcapf_export_button" id="wcapf_export_button" class="button button-primary">Export Settings</button>
                            </form>
                        </td></tr></tbody></table>
                        <form method="post">
                            <?php wp_nonce_field('reset_settings_nonce_action', 'reset_settings_nonce'); ?>
                            <table class="form-table" role="presentation">
                                <tbody>
                                    <tr>
                                        <th scope="row">Reset Settings</th>
                                        <td>    
                                            <input type="hidden" name="reset_settings" value="1">
                                            <button type="submit" class="button button-danger">Reset Settings</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </form>
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
function dapfforwc_save_style_options($input) {
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
add_filter('pre_update_option_dapfforwc_style_options', 'dapfforwc_save_style_options');


// include advance settings

include(plugin_dir_path(__FILE__) . 'advance_settings.php');


// handle reset settings
add_action('admin_init', 'handle_reset_settings');

function handle_reset_settings() {
    if (isset($_POST['reset_settings']) && $_POST['reset_settings'] == '1') {
        // Verify the nonce
        if (!isset($_POST['reset_settings_nonce']) || !wp_verify_nonce($_POST['reset_settings_nonce'], 'reset_settings_nonce_action')) {
            return; // Nonce is invalid, stop the process
        }

        // Reset the options
        update_option('dapfforwc_options', []);
        update_option('dapfforwc_style_options', []);
        update_option('dapfforwc_advance_options', []);
        update_option('dapfforwc_product_count', []);

        // Add an admin notice to inform the user
        add_action('admin_notices', 'reset_settings_notice');
    }
}

function reset_settings_notice() {
    dapfforwc_settings_init();
    include(plugin_dir_path(__FILE__) . 'includes/auto-detect-pages-filters.php');
    dapfforwc_update_filter_options_with_page_slugs();
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php esc_html_e('Settings have been reset successfully.', 'dynamic-ajax-product-filters-for-woocommerce'); ?></p>
    </div>
    <?php
}











?>