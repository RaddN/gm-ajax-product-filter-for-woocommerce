<?php
/**
 * Plugin Name: GM AJAX Product Filter for WooCommerce
 * Plugin URI:  https://plugincy.com/
 * Description: A WooCommerce plugin to filter products by attributes, categories, and tags using AJAX for seamless user experience.
 * Version:     1.0.4
 * Author:      Plugincy
 * Author URI:  https://plugincy.com
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: gm-ajax-product-filter-for-woocommerce
 * GM AJAX Product Filter for WooCommerce is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by the Free Software Foundation,
 * either version 2 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check if WooCommerce is active
add_action('plugins_loaded', 'gm_filter_check_woocommerce');

function gm_filter_check_woocommerce() {
    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'missing_woocommerce_notice');
    }else{
        include_once plugin_dir_path(__FILE__) . 'admin/admin-notice.php';

        // count product & store
        include_once plugin_dir_path(__FILE__) . 'includes/count_product.php';
    }
}

function missing_woocommerce_notice() {
    echo '<div class="notice notice-error"><p><strong>Filter Plugin</strong> requires WooCommerce to be installed and activated.</p></div>';
}



// Retrieve the 'use_url_filter' setting from the options
$options = get_option('wcapf_options');
$advance_settings = get_option('wcapf_advance_options');
$styleoptions = get_option('wcapf_style_options');
$product_count = get_option('wcapf_product_count');
$use_url_filter = isset($options['use_url_filter']) ? $options['use_url_filter'] : false;
$auto_detect_pages_filters =  $options['pages_filter_auto'] ?? '';
$slug = "";

// Enqueue scripts for frontend
function wcapf_enqueue_scripts() {
    // Determine the script to enqueue based on the 'use_url_filter' setting
    global $use_url_filter, $options, $slug, $styleoptions, $product_count, $advance_settings;

    switch ($use_url_filter) {
        case 'query_string':
            $script_handle = 'urlfilter-ajax';
            $script_path = 'assets/js/urlfilter.js';
            break;
        case 'permalinks':
            $script_handle = 'permalinksfilter-ajax';
            $script_path = 'assets/js/permalinksfilter.js';
            if (get_transient('gmfilter_slug')) {
                $slug = sanitize_text_field(get_transient('gmfilter_slug'));
            }
            break;
        default:
            $script_handle = 'filter-ajax';
            $script_path = 'assets/js/filter.js';
            break;
    }

    // Enqueue the determined script
    wp_enqueue_script(
        $script_handle,
        plugin_dir_url(__FILE__) . $script_path,
        array('jquery'),
        '1.0.6',
        true
    );
  
    // Pass the variable to the JavaScript file
    wp_localize_script($script_handle, 'wcapf_data', array(
        'options' => $options,
        'slug' => $slug,
        'styleoptions' => $styleoptions,
        'product_count' => $product_count,
        'advance_settings' => $advance_settings
     ));
     
    // Localize the script for AJAX functionality
    wp_localize_script(
        $script_handle,
        'wcapf_ajax',
        array('ajax_url' => admin_url('admin-ajax.php'))
    );
    // Enqueue the CSS style
    wp_enqueue_style('filter-style', plugin_dir_url(__FILE__) . 'assets/css/style.css',array(),'1.0.6');


    // Enqueue Select2 CSS and JS
    wp_enqueue_style('select2-css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css',array(),'1.0.6');
    wp_enqueue_script('select2-js', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), '1.0.6', true);

    // Enqueue your custom script to initialize Select2
    wp_add_inline_script('select2-js', '
        jQuery(document).ready(function($) {
    $(".select2").select2(
{
        placeholder: "Select Options"
}
    );
    $("select.select2_classic").select2({
        placeholder: "Select Options",
        allowClear: true
    });
    $(".title.collapsable_arrow").on("click", function() {
    const svg = $(this).find("svg");
svg.toggleClass("rotated");
$(this).siblings(".items").slideToggle(300); // Smooth toggle effect
});
$(".title.collapsable_no_arrow").on("click", function() {
$(this).siblings(".items").slideToggle(300); // Smooth toggle effect
});
});
    ');
}

add_action('wp_enqueue_scripts', 'wcapf_enqueue_scripts');

// Enqueue admin scripts
function wcapf_admin_scripts() {
    wp_enqueue_style('wcapf-admin-style', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css',array(),'1.0.6');
    wp_enqueue_style('wcapf-admin-codemirror-style', plugin_dir_url(__FILE__) . 'assets/css/codemirror.min.css',array(),'5.65.2');
    wp_enqueue_script('wcapf-admin-codemirror-script', plugin_dir_url(__FILE__) . 'assets/js/codemirror.min.js',array(), '5.65.2', true);
    wp_enqueue_script('wcapf-admin-xml-script', plugin_dir_url(__FILE__) . 'assets/js/xml.min.js',array(), '5.65.2', true);
    wp_enqueue_script('wcapf-admin-script', plugin_dir_url(__FILE__) . 'assets/js/admin-script.js',array(), '1.0.6', true);
    wp_enqueue_media();
    wp_enqueue_script('wcapf-media-uploader', plugin_dir_url(__FILE__) . 'assets/js/media-uploader.js', ['jquery'], '1.0.0', true);
}
add_action('admin_enqueue_scripts', 'wcapf_admin_scripts');

// Create shortcode for filter
include(plugin_dir_path(__FILE__) . 'includes/filter-template.php');

// Include filter processing class
include(plugin_dir_path(__FILE__) . 'includes/class-filter-functions.php');

// Register AJAX action for product filtering
add_action('wp_ajax_wcapf_filter_products', 'wcapf_filter_products');
add_action('wp_ajax_nopriv_wcapf_filter_products', 'wcapf_filter_products');
function wcapf_filter_products() {
    $filter = new WCAPF_Filter_Functions();
    $filter->process_filter();
}

// Register settings for filter management in admin
function wcapf_register_settings() {
    register_setting('wcapf_options_group', 'wcapf_filters', 'sanitize_text_field');
}
add_action('admin_init', 'wcapf_register_settings');

// include permalinks setup
if ($use_url_filter === 'permalinks' && !empty($options['pages'])){
    include(plugin_dir_path(__FILE__) . 'includes/permalinks-setup.php');
}


// Hook into the 'plugin_action_links' filter
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'my_plugin_settings_link');

/**
 * Add settings link to the plugin page
 *
 * @param array $links The array of plugin action links.
 * @return array Modified array with our custom link added.
 */
function my_plugin_settings_link($links) {
    // Add the settings link
    $settings_link = '<a href="admin.php?page=wcapf-admin">Settings</a>';
    array_unshift($links, $settings_link); // Add at the beginning of the links array
    return $links;
}

if ($use_url_filter === 'permalinks' && $auto_detect_pages_filters === "on"){
include(plugin_dir_path(__FILE__) . 'includes/auto-detect-pages-filters.php');
}

// Admin page for filter settings

include(plugin_dir_path(__FILE__) . 'admin/admin-page.php');