<?php
/**
 * Plugin Name: Dynamic AJAX Product Filters for WooCommerce
 * Plugin URI:  https://plugincy.com/
 * Description: A WooCommerce plugin to filter products by attributes, categories, and tags using AJAX for seamless user experience.
 * Version:     1.0.1
 * Author:      Plugincy
 * Author URI:  https://plugincy.com
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: dynamic-ajax-product-filters-for-woocommerce
 */

if (!defined('ABSPATH')) {
    exit;
}

// Global Variables
global $dapfforwc_options, $dapfforwc_advance_settings, $dapfforwc_styleoptions, $dapfforwc_product_count, $dapfforwc_use_url_filter, $dapfforwc_auto_detect_pages_filters, $dapfforwc_slug, $dapfforwc_sub_options ;

$dapfforwc_options = get_option('dapfforwc_options');
$dapfforwc_advance_settings = get_option('dapfforwc_advance_options');
$dapfforwc_styleoptions = get_option('dapfforwc_style_options');
$dapfforwc_product_count = get_option('dapfforwc_product_count');
$dapfforwc_use_url_filter = isset($dapfforwc_options['use_url_filter']) ? $dapfforwc_options['use_url_filter'] : false;
$dapfforwc_auto_detect_pages_filters = $dapfforwc_options['pages_filter_auto'] ?? '';
$dapfforwc_slug = "";

// Get the ID of the front page
$dapfforwc_front_page_id = get_option('page_on_front');
// Get the front page object
$dapfforwc_front_page = get_post($dapfforwc_front_page_id);
// Get the slug of the front page
$dapfforwc_front_page_slug = $dapfforwc_front_page->post_name;

// Define sub-options
$dapfforwc_sub_options = [
    'checkbox' => [
        'checkbox' => __('Checkbox', 'dynamic-ajax-product-filters-for-woocommerce'),
        'radio_check' => __('Radio Check', 'dynamic-ajax-product-filters-for-woocommerce'),
        'radio' => __('Radio', 'dynamic-ajax-product-filters-for-woocommerce'),
        'square_check' => __('Square Check', 'dynamic-ajax-product-filters-for-woocommerce'),
        'square' => __('Square', 'dynamic-ajax-product-filters-for-woocommerce'),
        'checkbox_hide' => __('Checkbox Hide', 'dynamic-ajax-product-filters-for-woocommerce'),
    ],
    'color' => [
        'color' => __('Color', 'dynamic-ajax-product-filters-for-woocommerce'),
        'color_no_border' => __('Color Without Border', 'dynamic-ajax-product-filters-for-woocommerce'),
    ],
    'image' => [
        'image' => __('Image', 'dynamic-ajax-product-filters-for-woocommerce'),
        'image_no_border' => __('Image Without Border', 'dynamic-ajax-product-filters-for-woocommerce'),
    ],
    'dropdown' => [
        'select' => __('Select', 'dynamic-ajax-product-filters-for-woocommerce'),
        'select2' => __('Select 2', 'dynamic-ajax-product-filters-for-woocommerce'),
        'select2_classic' => __('Select 2 Classic', 'dynamic-ajax-product-filters-for-woocommerce'),
    ],
    'price' => [
        'price' => __('Price', 'dynamic-ajax-product-filters-for-woocommerce'),
        'slider' => __('Slider', 'dynamic-ajax-product-filters-for-woocommerce'),
        'input-price-range' => __('input price range', 'dynamic-ajax-product-filters-for-woocommerce'),
    ],
    'rating' => [
        'rating' => __('Rating Star', 'dynamic-ajax-product-filters-for-woocommerce'),
        'rating-text' => __('Rating Text', 'dynamic-ajax-product-filters-for-woocommerce'),
        'dynamic-rating' => __('Dynamic Rating', 'dynamic-ajax-product-filters-for-woocommerce'),
    ],
];



// Check if WooCommerce is active
add_action('plugins_loaded', 'dapfforwc_check_woocommerce');

function dapfforwc_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'dapfforwc_missing_woocommerce_notice');
    } else {
        include_once plugin_dir_path(__FILE__) . 'admin/admin-notice.php';
        include_once plugin_dir_path(__FILE__) . 'includes/count_product.php';
        include_once plugin_dir_path(__FILE__) . 'includes/filter-template.php';

        add_action('wp_enqueue_scripts', 'dapfforwc_enqueue_scripts');
        add_action('admin_enqueue_scripts', 'dapfforwc_admin_scripts');
        include_once plugin_dir_path(__FILE__) . 'includes/class-filter-functions.php';

        add_action('wp_ajax_dapfforwc_filter_products', 'dapfforwc_filter_products');
        add_action('wp_ajax_nopriv_dapfforwc_filter_products', 'dapfforwc_filter_products');

        register_setting('dapfforwc_options_group', 'dapfforwc_filters', 'sanitize_text_field');

        add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'dapfforwc_add_settings_link');
        include(plugin_dir_path(__FILE__) . 'admin/admin-page.php');
    }
}

function dapfforwc_missing_woocommerce_notice() {
    echo '<div class="notice notice-error"><p><strong>Filter Plugin</strong> requires WooCommerce to be installed and activated.</p></div>';
}

// Enqueue scripts and styles
function dapfforwc_enqueue_scripts() {
    global $dapfforwc_use_url_filter, $dapfforwc_options, $dapfforwc_slug , $dapfforwc_styleoptions, $dapfforwc_product_count, $dapfforwc_advance_settings,$dapfforwc_front_page_slug;

    $script_handle = 'filter-ajax';
    $script_path = 'assets/js/filter.js';

    if ($dapfforwc_use_url_filter === 'query_string') {
        $script_handle = 'urlfilter-ajax';
        $script_path = 'assets/js/urlfilter.js';
    } elseif ($dapfforwc_use_url_filter === 'permalinks') {
        $script_handle = 'permalinksfilter-ajax';
        $script_path = 'assets/js/permalinksfilter.js';
        $dapfforwc_slug = sanitize_text_field(get_transient('dapfforwc_slug')) ?: '';
    }

    wp_enqueue_script($script_handle, plugin_dir_url(__FILE__) . $script_path, ['jquery'], '1.0.6', true);
    wp_localize_script($script_handle, 'dapfforwc_data', compact('dapfforwc_options', 'dapfforwc_slug', 'dapfforwc_styleoptions', 'dapfforwc_product_count', 'dapfforwc_advance_settings','dapfforwc_front_page_slug'));
    wp_localize_script($script_handle, 'dapfforwc_ajax', ['ajax_url' => admin_url('admin-ajax.php')]);

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

function dapfforwc_admin_scripts() {
    global $dapfforwc_sub_options;
    wp_enqueue_style('dapfforwc-admin-style', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css', [], '1.0.6');
    wp_enqueue_style('dapfforwc-admin-codemirror-style', plugin_dir_url(__FILE__) . 'assets/css/codemirror.min.css', [], '5.65.2');
    wp_enqueue_script('dapfforwc-admin-codemirror-script', plugin_dir_url(__FILE__) . 'assets/js/codemirror.min.js', [], '5.65.2', true);
    wp_enqueue_script('dapfforwc-admin-xml-script', plugin_dir_url(__FILE__) . 'assets/js/xml.min.js', [], '5.65.2', true);
    wp_enqueue_script('dapfforwc-admin-script', plugin_dir_url(__FILE__) . 'assets/js/admin-script.js', [], '1.0.6', true);
    wp_enqueue_media();
    wp_enqueue_script('dapfforwc-media-uploader', plugin_dir_url(__FILE__) . 'assets/js/media-uploader.js', ['jquery'], '1.0.0', true);

    $inline_script = 'document.addEventListener("DOMContentLoaded", function () {
    const dropdown = document.getElementById("attribute-dropdown");
    const firstAttribute = dropdown.value;

    document.querySelector(`#options-${firstAttribute}`).style.display = "block";

    function toggleDisplay(selector, display) {
        document.querySelectorAll(selector).forEach(el => {
            el.style.display = display;
        });
    }

    dropdown.addEventListener("change", function () {
    const selectedAttribute = this.value;

    toggleDisplay(".style-options", "none");

    if (selectedAttribute) {
        const selectedOptions = document.getElementById(`options-${selectedAttribute}`);
        if (selectedOptions) {
            selectedOptions.style.display = "block";
        }
    }

    if (selectedAttribute === "price") {
        toggleDisplay(".primary_options label", "none");
        toggleDisplay(".primary_options label.price", "block");
    }
    else if (selectedAttribute === "rating") {
        toggleDisplay(".primary_options label", "none");
        toggleDisplay(".primary_options label.rating", "block");
    } else if(selectedAttribute === "category"){
        toggleDisplay(".hierarchical", "block");
    }
    else {
        toggleDisplay(".hierarchical", "none");
        toggleDisplay(".primary_options label", "block");
        toggleDisplay(".primary_options label.price", "none");
        toggleDisplay(".primary_options label.rating", "none");
    }
});

    document.querySelectorAll(`.style-options .primary_options input[type="radio"][name^="dapfforwc_style_options"]`).forEach(function (radio) {
        radio.addEventListener("change", function () {
            const selectedType = this.value;
            const attributeName = this.name.match(/\[(.*?)\]/)[1];
            const subOptionsContainer = document.querySelector(`#options-${attributeName} .dynamic-sub-options`);
   
            document.querySelectorAll(".primary_options label").forEach(label => {
                label.classList.remove("active");
                const checkIcon = label.querySelector(".active");
                if (checkIcon) {
                    checkIcon.style.display = "none"; 
                }
            });
            const selectedLabel = radio.closest("label");
            selectedLabel.classList.add("active");

            const subOptions = '. wp_json_encode($dapfforwc_sub_options) .'

            const currentOptions = subOptions[selectedType] || {};
            subOptionsContainer.innerHTML = "";

            const fragment = document.createDocumentFragment();
            for (const key in currentOptions) {
                const label = document.createElement("label");
                label.innerHTML = `
                    <span class="active" style="display:none;"><i class="fa fa-check"></i></span>
                    <input type="radio" class="optionselect" name="dapfforwc_style_options[${attributeName}][sub_option]" value="${key}">
                    <img src="/wp-content/plugins/dynamic-ajax-product-filters-for-woocommerce/assets/images/${key}.png" alt="${currentOptions[key]}">
                    <div class="title">${currentOptions[key]}</div>
                `;
                fragment.appendChild(label);
            }
            subOptionsContainer.appendChild(fragment);

            attachSubOptionListeners();

           if(selectedType==="color" || selectedType==="image") {
            document.querySelector(`.advanced-options.${attributeName}`).style.display = "block";
            document.querySelector(`.advanced-options.${attributeName} .color`).style.display = "none";
            document.querySelector(`.advanced-options.${attributeName} .image`).style.display = "none";
            document.querySelector(`.advanced-options.${attributeName} .${selectedType}`).style.display = "block";

           }else {
            document.querySelectorAll(".advanced-options").forEach(advanceoptions =>{
                advanceoptions.style.display = "none";
            })
           }
        });
    });

    function attachSubOptionListeners() {
    const radioButtons = document.querySelectorAll(".optionselect");
    
    
    radioButtons.forEach(radio => {
        radio.addEventListener("change", function() {
            document.querySelectorAll(".dynamic-sub-options label").forEach(label => {
                label.classList.remove("active");
                const checkIcon = label.querySelector(".active");
                if (checkIcon) {
                    checkIcon.style.display = "none";
                }
            });

            const selectedLabel = this.closest("label");
            selectedLabel.classList.add("active");
            const checkIcon = selectedLabel.querySelector(".active");
            if (checkIcon) {
                checkIcon.style.display = "inline"; // Show check icon
            }

            // Managing single selection checkbox
            const singleSelectionCheckbox = this.closest(".style-options").querySelector(".setting-item.single-selection input");
            console.log(this.value);
            if (this.value === "select") {
                singleSelectionCheckbox.checked = true;
            } else {
                singleSelectionCheckbox.checked = false; // Uncheck if other options are selected
            }
        });
    });
}

// Call the function to attach listeners
attachSubOptionListeners();

});';
    wp_add_inline_script('dapfforwc-admin-script', $inline_script);
}

function dapfforwc_filter_products() {
    $filter = new dapfforwc_Filter_Functions();
    $filter->process_filter();
}


function dapfforwc_add_settings_link($links) {
    $settings_link = '<a href="admin.php?page=dapfforwc-admin">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}

if ($dapfforwc_use_url_filter !== '' && !empty($dapfforwc_options['pages'])) {
    include(plugin_dir_path(__FILE__) . 'includes/permalinks-setup.php');
}
if ($dapfforwc_auto_detect_pages_filters === "on") {
    include(plugin_dir_path(__FILE__) . 'includes/auto-detect-pages-filters.php');
}