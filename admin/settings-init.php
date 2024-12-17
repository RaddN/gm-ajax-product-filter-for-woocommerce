<?php

function wcapf_settings_init() {
    $options = get_option('wcapf_options') ?: [
        'show_categories' => 0,
        'show_attributes' => 1,
        'show_tags' => 0,
        'show_price_range' => 0,
        'use_url_filter' => '',
        'update_filter_options' => 0,
        'show_loader' => 1,
        'pages_filter_auto' => 1,
        'pages' => [], 
        'default_filters' => [],
        'use_custom_template' => 0,
        'custom_template_code' => '',
        'product_selector' => '.products',
        'pagination_selector' => '.woocommerce-pagination ul.page-numbers',
        'use_filters_word_in_permalinks' => 0,
        'filters_word_in_permalinks' => 'filters',
    ];
    update_option('wcapf_options', $options);

    register_setting('wcapf_options_group', 'wcapf_options');
    
    add_settings_section('wcapf_section', __('Filter Settings', 'gm-ajax-product-filter-for-woocommerce'), null, 'wcapf-admin');

    $fields = [
        'show_categories' => __('Show Categories', 'gm-ajax-product-filter-for-woocommerce'),
        'show_attributes' => __('Show Attributes', 'gm-ajax-product-filter-for-woocommerce'),
        'show_tags' => __('Show Tags', 'gm-ajax-product-filter-for-woocommerce'),
        'show_price_range' => __('Show Price Range', 'gm-ajax-product-filter-for-woocommerce'),
        'show_rating' => __('Show Rating', 'gm-ajax-product-filter-for-woocommerce'),
        'use_url_filter' => __('Use URL-Based Filter', 'gm-ajax-product-filter-for-woocommerce'),
        'use_filters_word_in_permalinks' => __('use filters word in permalinks', 'gm-ajax-product-filter-for-woocommerce'),
        'update_filter_options' => __('Update filter options', 'gm-ajax-product-filter-for-woocommerce'),
        'show_loader' => __('Show Loader', 'gm-ajax-product-filter-for-woocommerce'),
        'use_custom_template' => __('Use Custom Product Template', 'gm-ajax-product-filter-for-woocommerce'),
    ];

    foreach ($fields as $key => $label) {
        add_settings_field($key, $label, "wcapf_{$key}_render", 'wcapf-admin', 'wcapf_section');
    }
    
    // Pages List Field
        // Add Page Management Section
        add_settings_section('wcapf_page_section_before', null, function() {
            global $options;
            echo '<div class="page_manage">';
        }, 'wcapf-admin');
        add_settings_section('wcapf_page_section', __('Pages Manage', 'gm-ajax-product-filter-for-woocommerce'), function() {
            echo '<p>' . esc_html__('Add the pages below where you have added the shortcode.', 'gm-ajax-product-filter-for-woocommerce') . '</p>';
        }, 'wcapf-admin');
        add_settings_field("pages_filter_auto", 'Auto Detect Pages & Default Filters', "wcapf_pages_filter_auto_render", 'wcapf-admin', 'wcapf_page_section');
    add_settings_field('pages', __('Pages List', 'gm-ajax-product-filter-for-woocommerce'), 'wcapf_pages_render', 'wcapf-admin', 'wcapf_page_section');
    add_settings_section('wcapf_page_section_after', null, function() {
        echo '</div>';
    }, 'wcapf-admin');
    
    // Default Filter List Field
    add_settings_field('default_filters', __('Default Filter List', 'gm-ajax-product-filter-for-woocommerce'), 'wcapf_default_filters_render', 'wcapf-admin', 'wcapf_page_section');

    // custom code template
    add_settings_field('custom_template_code', __('product custom template code', 'gm-ajax-product-filter-for-woocommerce'), 'wcapf_custom_template_code_render', 'wcapf-admin', 'wcapf_section');



    // form style register
    register_setting('wcapf_style_options_group', 'wcapf_style_options');

        // Add Form Style section
    add_settings_section(
        'wcapf_style_section',
        __('Form Style Options', 'gm-ajax-product-filter-for-woocommerce'),
        function () {
            echo '<p>' . esc_html__('Select the filter box style for each attribute below. Additional options will appear based on your selection.', 'gm-ajax-product-filter-for-woocommerce') . '</p>';
        },
        'wcapf-style'
    );

//   advance settings register
$Advance_options = get_option('wcapf_advance_options') ?: [
    'product_selector' => '.products',
    'pagination_selector' => '.woocommerce-pagination ul.page-numbers',
    'product_shortcode' => 'products',
    'use_anchor' => 0,
];
    update_option('wcapf_advance_options', $Advance_options);
    register_setting('wcapf_advance_settings', 'wcapf_advance_options');
    // Add the "Advance Settings" section
    add_settings_section(
        'wcapf_advance_settings_section',
        __('Advance Settings', 'gm-ajax-product-filter-for-woocommerce'),
        null,
        'wcapf-advance-settings'
    );

    // Add the "Product Selector" field
    add_settings_field(
        'product_selector',
        __('Product Selector', 'gm-ajax-product-filter-for-woocommerce'),
        'wcapf_product_selector_callback',
        'wcapf-advance-settings',
        'wcapf_advance_settings_section'
    );
    // Add the "Pagination Selector" field
    add_settings_field(
        'pagination_selector',
        __('Pagination Selector', 'gm-ajax-product-filter-for-woocommerce'),
        'wcapf_pagination_selector_callback',
        'wcapf-advance-settings',
        'wcapf_advance_settings_section'
    );
    // Add the "Product shotcode Selector" field
    add_settings_field(
        'product_shortcode',
        __('Product Shortcode Selector', 'gm-ajax-product-filter-for-woocommerce'),
        'wcapf_product_shortcode_callback',
        'wcapf-advance-settings',
        'wcapf_advance_settings_section'
    );

    add_settings_field('use_anchor', __('Make filter link indexable for best SEO', 'gm-ajax-product-filter-for-woocommerce'), "wcapf_use_anchor_render", 'wcapf-advance-settings', 'wcapf_advance_settings_section');
}
add_action('admin_init', 'wcapf_settings_init');


