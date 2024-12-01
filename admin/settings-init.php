<?php

function wcapf_settings_init() {
    $options = get_option('wcapf_options') ?: [
        'show_categories' => 0,
        'show_attributes' => 1,
        'show_tags' => 0,
        'use_url_filter' => '',
        'update_filter_options' => 0,
        'show_loader' => 1,
        'pages' => [], 
        'default_filters' => [],
        'use_custom_template' => 0,
        'custom_template_code' => '',
    ];
    update_option('wcapf_options', $options);

    register_setting('wcapf_options_group', 'wcapf_options');
    
    add_settings_section('wcapf_section', __('Filter Settings', 'gm-ajax-product-filter-for-woocommerce'), null, 'wcapf-admin');

    $fields = [
        'show_categories' => __('Show Categories', 'gm-ajax-product-filter-for-woocommerce'),
        'show_attributes' => __('Show Attributes', 'gm-ajax-product-filter-for-woocommerce'),
        'show_tags' => __('Show Tags', 'gm-ajax-product-filter-for-woocommerce'),
        'use_url_filter' => __('Use URL-Based Filter', 'gm-ajax-product-filter-for-woocommerce'),
        'update_filter_options' => __('Update filter options', 'gm-ajax-product-filter-for-woocommerce'),
        'show_loader' => __('Show Loader', 'gm-ajax-product-filter-for-woocommerce'),
        'use_custom_template' => __('Use Custom Product Template', 'gm-ajax-product-filter-for-woocommerce')
    ];

    foreach ($fields as $key => $label) {
        add_settings_field($key, $label, "wcapf_{$key}_render", 'wcapf-admin', 'wcapf_section');
    }

    // Pages List Field
        // Add Page Management Section
    //     add_settings_section('wcapf_page_section_before', null, function() {
    //         global $options;
    //         echo '<div class="page_manage" style="' . ($options['use_url_filter'] === "permalinks" ? 'display:block;' : 'display:none;') . '">';
    //     }, 'wcapf-admin');
    //     add_settings_section('wcapf_page_section', __('Pages Manage', 'gm-ajax-product-filter-for-woocommerce'), function() {
    //         echo '<p>' . esc_html__('Add the pages below where you have added the shortcode.', 'gm-ajax-product-filter-for-woocommerce') . '</p>';
    //     }, 'wcapf-admin');
    // add_settings_field('pages', __('Pages List', 'gm-ajax-product-filter-for-woocommerce'), 'wcapf_pages_render', 'wcapf-admin', 'wcapf_page_section');
    // add_settings_section('wcapf_page_section_after', null, function() {
    //     echo '</div>';
    // }, 'wcapf-admin');
    
    // Default Filter List Field
    add_settings_section('wcapf_default_filters_section', __('Default Filters for Pages', 'gm-ajax-product-filter-for-woocommerce'), function() {
        echo '<p>' . esc_html__('Define default filters for each listed page below.', 'gm-ajax-product-filter-for-woocommerce') . '</p>';
    }, 'wcapf-admin');
    add_settings_field('default_filters', __('Default Filter List', 'gm-ajax-product-filter-for-woocommerce'), 'wcapf_default_filters_render', 'wcapf-admin', 'wcapf_default_filters_section');

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
}
add_action('admin_init', 'wcapf_settings_init');