<?php
// Automatic add & remove pages slug list based on shortcode used
function find_wcapf_shortcode_pages() {
    $shortcode = 'wcapf_product_filter';

    // Use WP_Query instead of direct SQL
    $query = new WP_Query([
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => -1, // To get all pages
        's'              => $shortcode // Search for the shortcode in content
    ]);
    
    // Return the pages with the shortcode
    return $query->posts;
}

function update_wcapf_options_with_page_slugs() {
    // Fetch pages containing the shortcode
    $pages_with_wcapf_shortcode = find_wcapf_shortcode_pages();
    
    // Get the current options
    $options = get_option('wcapf_options');
    $options['pages'] = [];

    // Extract slugs from the pages with the shortcode
    $slugs = array_map(function($page) {
        // Get the current page slug
        $current_slug = $page->post_name;

        // Get the parent page slug if it exists
        $parent_id = wp_get_post_parent_id($page->ID);
        if ($parent_id) {
            $parent_post = get_post($parent_id);
            $parent_slug = $parent_post->post_name;
            return $parent_slug . '/' . $current_slug; // Combine parent and current slug
        }

        return $current_slug; // Return only the current slug if no parent
    }, $pages_with_wcapf_shortcode);

    // Ensure unique values and update the pages array
    $options['pages'] = array_unique(array_merge($options['pages'], $slugs));

    // Update the options in the database
    update_option('wcapf_options', $options);
}

// Hook the function to an action, for example, when the admin initializes
add_action('admin_init', 'update_wcapf_options_with_page_slugs');





// Step 1: Find pages containing a specific shortcode
function find_shortcode_pages($shortcode) {
    $query = new WP_Query([
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => -1, // Get all pages
        's'              => $shortcode, // Search for the shortcode in content
    ]);

    return $query->posts;
}

// Step 2: Parse shortcode attributes
function get_shortcode_attributes_from_page($content, $shortcode) {
    preg_match_all('/\[' . $shortcode . '[^\]]*\]/', $content, $matches);

    $attributes_list = [];
    foreach ($matches[0] as $shortcode_instance) {
        $attributes_list[] = shortcode_parse_atts($shortcode_instance);
    }

    return $attributes_list;
}

// Step 3: Update options with slug and shortcode attributes
function update_wcapf_options_with_filters() {
    global $advance_settings;
    $shortcode = $advance_settings["product_shortcode"] ?? 'products'; // Shortcode to search for
    $pages_with_shortcode = find_shortcode_pages($shortcode);

    // Get the current options
    $options = get_option('wcapf_options');
    $options['default_filters'] = []; // Initialize if not set
    $options['product_show_settings'] = [];

    foreach ($pages_with_shortcode as $page) {
        $attributes_list = get_shortcode_attributes_from_page($page->post_content, $shortcode);

        // Get the current page slug
        $current_slug = $page->post_name;

        // Get the parent page slug if it exists
        $parent_id = wp_get_post_parent_id($page->ID);
        if ($parent_id) {
            $parent_post = get_post($parent_id);
            $parent_slug = $parent_post->post_name;
            $full_slug = $parent_slug . '/' . $current_slug; // Combine parent and current slug
        } else {
            $full_slug = $current_slug; // Just use the current slug if no parent
        }

        foreach ($attributes_list as $attributes) {
            // Ensure that the 'category', 'attribute', and 'terms' keys exist
            $arrayCata = isset($attributes['category']) ? explode(", ", $attributes['category']) : [];
            $tagValue = isset($attributes['tags']) ? $attributes['tags'] : [];
            $termsValue = isset($attributes['terms']) ? $attributes['terms'] : [];
            $filters = !empty($arrayCata) ? $arrayCata : (!empty($tagValue) ? $tagValue : $termsValue);

            // Use the combined full slug as the key in default_filters
            $options['default_filters'][$full_slug] = $filters;


             // Set display settings
             $options['product_show_settings'][$full_slug] = [
                'per_page'        => $attributes['limit'] ?? $attributes['per_page'] ?? '',
                'orderby'         => $attributes['orderby'] ?? '',
                'order'           => $attributes['order'] ?? '',
                'operator_second' => $attributes['terms_operator'] ?? $attributes['tag_operator'] ?? $attributes['cat_operator'] ?? ''
            ];
        }
    }

    // Save the updated options
    update_option('wcapf_options', $options);
}

// Step 4: Hook to an appropriate action (e.g., admin_init or save_post)
add_action('admin_init', 'update_wcapf_options_with_filters');
