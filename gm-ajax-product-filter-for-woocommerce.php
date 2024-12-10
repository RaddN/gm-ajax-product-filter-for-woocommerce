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
 */

if (!defined('ABSPATH')) {
    exit;
}

// Global Variables
global $options, $advance_settings, $styleoptions, $product_count, $use_url_filter, $auto_detect_pages_filters, $slug;

$options = get_option('wcapf_options');
$advance_settings = get_option('wcapf_advance_options');
$styleoptions = get_option('wcapf_style_options');
$product_count = get_option('wcapf_product_count');
$use_url_filter = isset($options['use_url_filter']) ? $options['use_url_filter'] : false;
$auto_detect_pages_filters = $options['pages_filter_auto'] ?? '';
$slug = "";

// Check if WooCommerce is active
add_action('plugins_loaded', 'gm_filter_check_woocommerce');

function gm_filter_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'missing_woocommerce_notice');
    } else {
        include_once plugin_dir_path(__FILE__) . 'admin/admin-notice.php';
        include_once plugin_dir_path(__FILE__) . 'includes/count_product.php';
        include_once plugin_dir_path(__FILE__) . 'includes/filter-template.php';

        add_action('wp_enqueue_scripts', 'wcapf_enqueue_scripts');
        add_action('admin_enqueue_scripts', 'wcapf_admin_scripts');
        include_once plugin_dir_path(__FILE__) . 'includes/class-filter-functions.php';

        add_action('wp_ajax_wcapf_filter_products', 'wcapf_filter_products');
        add_action('wp_ajax_nopriv_wcapf_filter_products', 'wcapf_filter_products');

        register_setting('wcapf_options_group', 'wcapf_filters', 'sanitize_text_field');
        add_action('admin_init', 'wcapf_register_settings');

        add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wcapf_add_settings_link');
        include(plugin_dir_path(__FILE__) . 'admin/admin-page.php');
    }
}

function missing_woocommerce_notice() {
    echo '<div class="notice notice-error"><p><strong>Filter Plugin</strong> requires WooCommerce to be installed and activated.</p></div>';
}

// Enqueue scripts and styles
function wcapf_enqueue_scripts() {
    global $use_url_filter, $options, $slug, $styleoptions, $product_count, $advance_settings;

    $script_handle = 'filter-ajax';
    $script_path = 'assets/js/filter.js';

    if ($use_url_filter === 'query_string') {
        $script_handle = 'urlfilter-ajax';
        $script_path = 'assets/js/urlfilter.js';
    } elseif ($use_url_filter === 'permalinks') {
        $script_handle = 'permalinksfilter-ajax';
        $script_path = 'assets/js/permalinksfilter.js';
        $slug = sanitize_text_field(get_transient('gmfilter_slug')) ?: '';
    }

    wp_enqueue_script($script_handle, plugin_dir_url(__FILE__) . $script_path, ['jquery'], '1.0.6', true);
    wp_localize_script($script_handle, 'wcapf_data', compact('options', 'slug', 'styleoptions', 'product_count', 'advance_settings'));
    wp_localize_script($script_handle, 'wcapf_ajax', ['ajax_url' => admin_url('admin-ajax.php')]);

    wp_enqueue_style('filter-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', [], '1.0.6');
    wp_enqueue_style('select2-css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', [], '1.0.6');
    wp_enqueue_script('select2-js', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', ['jquery'], '1.0.6', true);

    wp_add_inline_script('select2-js', '
        jQuery(document).ready(function($) {
            $(".select2").select2({ placeholder: "Select Options" });
            $("select.select2_classic").select2({ placeholder: "Select Options", allowClear: true });

            $(".title.collapsable_arrow").on("click", function() {
                $(this).find("svg").toggleClass("rotated");
                $(this).siblings(".items").slideToggle(300);
            });

            $(".title.collapsable_no_arrow").on("click", function() {
                $(this).siblings(".items").slideToggle(300);
            });
        });
    ');
}

function wcapf_admin_scripts() {
    wp_enqueue_style('wcapf-admin-style', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css', [], '1.0.6');
    wp_enqueue_style('wcapf-admin-codemirror-style', plugin_dir_url(__FILE__) . 'assets/css/codemirror.min.css', [], '5.65.2');
    wp_enqueue_script('wcapf-admin-codemirror-script', plugin_dir_url(__FILE__) . 'assets/js/codemirror.min.js', [], '5.65.2', true);
    wp_enqueue_script('wcapf-admin-xml-script', plugin_dir_url(__FILE__) . 'assets/js/xml.min.js', [], '5.65.2', true);
    wp_enqueue_script('wcapf-admin-script', plugin_dir_url(__FILE__) . 'assets/js/admin-script.js', [], '1.0.6', true);
    wp_enqueue_media();
    wp_enqueue_script('wcapf-media-uploader', plugin_dir_url(__FILE__) . 'assets/js/media-uploader.js', ['jquery'], '1.0.0', true);
}

function wcapf_filter_products() {
    $filter = new WCAPF_Filter_Functions();
    $filter->process_filter();
}

function wcapf_register_settings() {
    register_setting('wcapf_options_group', 'wcapf_filters', 'sanitize_text_field');
}

function wcapf_add_settings_link($links) {
    $settings_link = '<a href="admin.php?page=wcapf-admin">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}

if ($use_url_filter === 'permalinks' && !empty($options['pages'])) {
    include(plugin_dir_path(__FILE__) . 'includes/permalinks-setup.php');
}
if ($use_url_filter === 'permalinks' && $auto_detect_pages_filters === "on") {
    include(plugin_dir_path(__FILE__) . 'includes/auto-detect-pages-filters.php');
}