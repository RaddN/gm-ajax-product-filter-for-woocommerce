<?php

if (!defined('ABSPATH')) {
    exit;
}
function dapfforwc_update_filter_options_with_page_slugs() {
    $shortcode = 'plugincy_filters';
    // Fetch pages containing the shortcode
    $dapfforwc_pages_with_shortcode = dapfforwc_find_shortcode_pages($shortcode);
    
    // Get the current options
    $dapfforwc_options = get_option('dapfforwc_options')?:[];
    $dapfforwc_options['pages'] = [];

    // Extract slugs from the pages with the shortcode
    $dapfforwc_slugs = array_map(function($page) {
        // Use the dapfforwc_get_full_slug function to get the complete slug
        return dapfforwc_get_full_slug($page->ID);
    }, $dapfforwc_pages_with_shortcode);

    // Ensure unique values and update the pages array
    $dapfforwc_options['pages'] = array_unique(array_merge($dapfforwc_options['pages'], $dapfforwc_slugs));

    // Update the options in the database
    update_option('dapfforwc_options', $dapfforwc_options);
}

// Hook the function to an action, for example, when the admin initializes
add_action('admin_init', 'dapfforwc_update_filter_options_with_page_slugs');

// Step 1: Find pages containing a specific shortcode
function dapfforwc_find_shortcode_pages($shortcode) {
    $query = new WP_Query([
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => -1, // Get all pages
        's'              => $shortcode, // Search for the shortcode in content
    ]);

    return $query->posts;
}

// Step 2: Parse shortcode attributes
function dapfforwc_get_shortcode_attributes_from_page($content, $shortcode) {
    // Use regex to match the shortcode and capture its attributes
    preg_match_all('/\[' . preg_quote($shortcode, '/') . '([^]]*)\]/', $content, $matches);

    $attributes_list = [];
    foreach ($matches[1] as $shortcode_instance) {
        // Clean up the attribute string and parse it
        $shortcode_instance = trim($shortcode_instance);
        $attributes_list[] = shortcode_parse_atts($shortcode_instance);
    }

    return $attributes_list;
}


function dapfforwc_update_options_with_filters() {
    global $dapfforwc_advance_settings;

    // Get the product shortcodes, explode by commas, and trim whitespace
    $shortcodes = array_map('trim', explode(',', $dapfforwc_advance_settings["product_shortcode"] ?? 'products'));
    
    // Initialize default options
    $dapfforwc_options = get_option('dapfforwc_options') ?: [];
    $dapfforwc_options['default_filters'] = []; // Initialize if not set
    $dapfforwc_options['product_show_settings'] = [];

    // Find pages for each shortcode
    foreach ($shortcodes as $shortcode) {
        $pages_with_shortcode = dapfforwc_find_shortcode_pages($shortcode);

        foreach ($pages_with_shortcode as $page) {
            $attributes_list = dapfforwc_get_shortcode_attributes_from_page($page->post_content, $shortcode);

            // Get the full slug using the new function
            $full_slug = dapfforwc_get_full_slug($page->ID);

            foreach ($attributes_list as $attributes) {
                // Ensure that the 'category', 'attribute', and 'terms' keys exist
                $arrayCata = isset($attributes['category']) ? explode(",", $attributes['category']) : [];
                $tagValue = isset($attributes['tags']) ? $attributes['tags'] : [];
                $termsValue = isset($attributes['terms']) ? $attributes['terms'] : [];
                $filters = !empty($arrayCata) ? $arrayCata : (!empty($tagValue) ? $tagValue : $termsValue);

                // Use the combined full slug as the key in default_filters
                $dapfforwc_options['default_filters'][$full_slug] = $filters;

                // Set display settings
                $dapfforwc_options['product_show_settings'][$full_slug] = [
                    'per_page'        => $attributes['limit'] ?? $attributes['per_page'] ?? '',
                    'orderby'         => $attributes['orderby'] ?? '',
                    'order'           => $attributes['order'] ?? '',
                    'operator_second' => $attributes['terms_operator'] ?? $attributes['tag_operator'] ?? $attributes['cat_operator'] ?? 'IN'
                ];
            }
        }
    }

    // Save the updated options
    update_option('dapfforwc_options', $dapfforwc_options);
}

// Step 4: Hook to an appropriate action (e.g., admin_init or save_post)
add_action('admin_init', 'dapfforwc_update_options_with_filters');
