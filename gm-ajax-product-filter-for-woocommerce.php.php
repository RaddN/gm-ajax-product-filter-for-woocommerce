<?php
/**
 * Plugin Name: GM AJAX Product Filter for woocommerce
 * Description: Filter WooCommerce products by attributes, categories, and tags using AJAX.
 * Version: 1.0
 * Author: Plugincy
 * Author URI: https://plugincy.com
 * License: GPL2
 * Text Domain: gm-ajax-product-filter-for-woocommerce
 */

if (!defined('ABSPATH')) {
    exit;
}
// Retrieve the 'use_url_filter' setting from the options
$options = get_option('wcapf_options');
$use_url_filter = isset($options['use_url_filter']) ? $options['use_url_filter'] : false;

// Enqueue scripts for frontend
function wcapf_enqueue_scripts() {
    // Determine the script to enqueue based on the 'use_url_filter' setting
    global $use_url_filter;
    if ($use_url_filter === 'query_string') {
        $script_handle = 'urlfilter-ajax';
        $script_path = 'assets/js/urlfilter.js';
    } elseif ($use_url_filter === 'permalinks') {
        $script_handle = 'permalinksfilter-ajax';
        $script_path = 'assets/js/permalinksfilter.js';
    } else {
        $script_handle = 'filter-ajax';
        $script_path = 'assets/js/filter.js';
    }

    // Enqueue the determined script
    wp_enqueue_script(
        $script_handle,
        plugin_dir_url(__FILE__) . $script_path,
        array('jquery'),
        null,
        true
    );
     // Retrieve the PHP variable
     $options = get_option('wcapf_options');
  
    // Pass the variable to the JavaScript file
    wp_localize_script($script_handle, 'wcapf_data', array(
        'options' => $options
     ));
    // Localize the script for AJAX functionality
    wp_localize_script(
        $script_handle,
        'wcapf_ajax',
        array('ajax_url' => admin_url('admin-ajax.php'))
    );

    // Enqueue the CSS style
    wp_enqueue_style('filter-style', plugin_dir_url(__FILE__) . 'assets/css/style.css');
}

add_action('wp_enqueue_scripts', 'wcapf_enqueue_scripts');

// Enqueue admin scripts
function wcapf_admin_scripts() {
    wp_enqueue_style('wcapf-admin-style', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css');
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

// Admin page for filter settings

// Include filter processing class
include(plugin_dir_path(__FILE__) . 'admin/admin-page.php');

// Register settings for filter management in admin
function wcapf_register_settings() {
    register_setting('wcapf_options_group', 'wcapf_filters', 'sanitize_text_field');
}
add_action('admin_init', 'wcapf_register_settings');

// include permalinks setup
if ($use_url_filter === 'permalinks'){
    include(plugin_dir_path(__FILE__) . 'includes/permalinks-setup.php');

}