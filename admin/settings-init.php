<?php
if (!defined('ABSPATH')) {
    exit;
}
function dapfforwc_settings_init() {
    $dapfforwc_options = get_option('dapfforwc_options') ?: [
        'show_categories' =>"on",
        'show_attributes' => "on",
        'show_tags' => "on",
        'show_price_range' => "",
        'show_rating' => "",
        'show_search' => "",
        'use_url_filter' => 'query_string',
        'update_filter_options' => 0,
        'show_loader' => "on",
        'pages_filter_auto' => "on",
        'pages' => [],
        'loader_html'=>'<div id="loader" style="display:none;"></div>',
        'loader_css'=>'#loader { width: 56px; height: 56px; border-radius: 50%; background: conic-gradient(#0000 10%,#474bff); -webkit-mask: radial-gradient(farthest-side,#0000 calc(100% - 9px),#000 0); animation: spinner-zp9dbg 1s infinite linear; } @keyframes spinner-zp9dbg { to { transform: rotate(1turn); } }',
        'default_filters' => [],
        'use_custom_template' => 0,
        'custom_template_code' => '',
        'product_selector' => '.products',
        'pagination_selector' => '.woocommerce-pagination ul.page-numbers',
        'use_filters_word_in_permalinks' => 0,
        'filters_word_in_permalinks' => 'filters',
    ];
    update_option('dapfforwc_options', $dapfforwc_options);

    register_setting('dapfforwc_options_group', 'dapfforwc_options', 'dapfforwc_options_sanitize');
    
    add_settings_section('dapfforwc_section', __('Filter Settings', 'dynamic-ajax-product-filters-for-woocommerce'), null, 'dapfforwc-admin');

    $fields = [
        'show_categories' => __('Show Categories', 'dynamic-ajax-product-filters-for-woocommerce'),
        'show_attributes' => __('Show Attributes', 'dynamic-ajax-product-filters-for-woocommerce'),
        'show_tags' => __('Show Tags', 'dynamic-ajax-product-filters-for-woocommerce'),
        'show_price_range' => __('Show Price Range', 'dynamic-ajax-product-filters-for-woocommerce'),
        'show_rating' => __('Show Rating', 'dynamic-ajax-product-filters-for-woocommerce'),
        'show_search' => __('Show Search', 'dynamic-ajax-product-filters-for-woocommerce'),
        'use_url_filter' => __('Use URL-Based Filter', 'dynamic-ajax-product-filters-for-woocommerce'),
        'use_filters_word_in_permalinks' => __('use filters word in permalinks', 'dynamic-ajax-product-filters-for-woocommerce'),
        'update_filter_options' => __('Update filter options', 'dynamic-ajax-product-filters-for-woocommerce'),
        'show_loader' => __('Show Loader', 'dynamic-ajax-product-filters-for-woocommerce'),
        'use_custom_template' => __('Use Custom Product Template', 'dynamic-ajax-product-filters-for-woocommerce'),
    ];

    foreach ($fields as $key => $label) {
        add_settings_field($key, $label, "dapfforwc_{$key}_render", 'dapfforwc-admin', 'dapfforwc_section');
    }
    
    // Pages List Field
        // Add Page Management Section
        add_settings_section('dapfforwc_page_section_before', null, function() {
            global $dapfforwc_options;
            echo '<div class="page_manage">';
        }, 'dapfforwc-admin');
        add_settings_section('dapfforwc_page_section', __('Pages Manage', 'dynamic-ajax-product-filters-for-woocommerce'), function() {
            echo '<p>' . esc_html__('Add the pages below where you have added the shortcode.', 'dynamic-ajax-product-filters-for-woocommerce') . '</p>';
        }, 'dapfforwc-admin');
        add_settings_field("pages_filter_auto", 'Auto Detect Pages & Default Filters', "dapfforwc_pages_filter_auto_render", 'dapfforwc-admin', 'dapfforwc_page_section');
    add_settings_field('pages', __('Pages List', 'dynamic-ajax-product-filters-for-woocommerce'), 'dapfforwc_pages_render', 'dapfforwc-admin', 'dapfforwc_page_section');
    add_settings_section('dapfforwc_page_section_after', null, function() {
        echo '</div>';
    }, 'dapfforwc-admin');
    
    // Default Filter List Field
    add_settings_field('default_filters', __('Default Filter List', 'dynamic-ajax-product-filters-for-woocommerce'), 'dapfforwc_default_filters_render', 'dapfforwc-admin', 'dapfforwc_page_section');

    // custom code template
    add_settings_field('custom_template_code', __('product custom template code', 'dynamic-ajax-product-filters-for-woocommerce'), 'dapfforwc_custom_template_code_render', 'dapfforwc-admin', 'dapfforwc_section');

    $default_style = get_option('dapfforwc_style_options') ?: [
        'price' => ['type'=>'price', 'sub_option'=>'price'],
        'rating' => ['type'=>'rating', 'sub_option'=>'rating'],
    ];
    update_option('dapfforwc_style_options', $default_style);
    // form style register
    register_setting('dapfforwc_style_options_group', 'dapfforwc_style_options', 'dapfforwc_options_sanitize');

        // Add Form Style section
    add_settings_section(
        'dapfforwc_style_section',
        __('Form Style Options', 'dynamic-ajax-product-filters-for-woocommerce'),
        function () {
            echo '<p>' . esc_html__('Select the filter box style for each attribute below. Additional options will appear based on your selection.', 'dynamic-ajax-product-filters-for-woocommerce') . '</p>';
        },
        'dapfforwc-style'
    );

//   advance settings register
$Advance_options = get_option('dapfforwc_advance_options') ?: [
    'product_selector' => '.products',
    'pagination_selector' => '.woocommerce-pagination ul.page-numbers',
    'product_shortcode' => 'products',
    'use_anchor' => 0,
    'remove_outofStock' => 0,
    'sidebar_top' => 0,
];
    update_option('dapfforwc_advance_options', $Advance_options);
    register_setting('dapfforwc_advance_settings', 'dapfforwc_advance_options', 'dapfforwc_options_sanitize');
    // Add the "Advance Settings" section
    add_settings_section(
        'dapfforwc_advance_settings_section',
        __('Advance Settings', 'dynamic-ajax-product-filters-for-woocommerce'),
        null,
        'dapfforwc-advance-settings'
    );

    // Add the "Product Selector" field
    add_settings_field(
        'product_selector',
        __('Product Selector', 'dynamic-ajax-product-filters-for-woocommerce'),
        'dapfforwc_product_selector_callback',
        'dapfforwc-advance-settings',
        'dapfforwc_advance_settings_section'
    );
    // Add the "Pagination Selector" field
    add_settings_field(
        'pagination_selector',
        __('Pagination Selector', 'dynamic-ajax-product-filters-for-woocommerce'),
        'dapfforwc_pagination_selector_callback',
        'dapfforwc-advance-settings',
        'dapfforwc_advance_settings_section'
    );
    // Add the "Product shotcode Selector" field
    add_settings_field(
        'product_shortcode',
        __('Product Shortcode Selector', 'dynamic-ajax-product-filters-for-woocommerce'),
        'dapfforwc_product_shortcode_callback',
        'dapfforwc-advance-settings',
        'dapfforwc_advance_settings_section'
    );

    add_settings_field('use_anchor', __('Make filter link indexable for best SEO', 'dynamic-ajax-product-filters-for-woocommerce'), "dapfforwc_use_anchor_render", 'dapfforwc-advance-settings', 'dapfforwc_advance_settings_section');
    add_settings_field('remove_outofStock', __('Remove out of stock product', 'dynamic-ajax-product-filters-for-woocommerce'), "dapfforwc_remove_outofStock_render", 'dapfforwc-advance-settings', 'dapfforwc_advance_settings_section');
    add_settings_field('sidebar_top', __('Sidebar on top', 'dynamic-ajax-product-filters-for-woocommerce'), "dapfforwc_sidebar_top_render", 'dapfforwc-advance-settings', 'dapfforwc_advance_settings_section');

}
add_action('admin_init', 'dapfforwc_settings_init');


