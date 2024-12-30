<?php

if (!defined('ABSPATH')) {
    exit;
}

function dapfforwc_update_product_counts() {
    $dapfforwc_product_counts = array();

    // Get product categories and their counts
    $categories = get_terms(array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
    ));

    if (!is_wp_error($categories)) {
        foreach ($categories as $category) {
            $dapfforwc_product_counts['categories'][$category->slug] = $category->count;
        }
    }

    // Get product tags and their counts
    $tags = get_terms(array(
        'taxonomy'   => 'product_tag',
        'hide_empty' => false,
    ));

    if (!is_wp_error($tags)) {
        foreach ($tags as $tag) {
            $dapfforwc_product_counts['tags'][$tag->slug] = $tag->count;
        }
    }

    // Get product attributes and their counts
    $attribute_taxonomies = wc_get_attribute_taxonomies();

    if (!empty($attribute_taxonomies)) {
        foreach ($attribute_taxonomies as $attribute) {
            $taxonomy = 'pa_' . $attribute->attribute_name;

            $terms = get_terms(array(
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
            ));

            if (!is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $dapfforwc_product_counts['attributes'][$taxonomy][$term->slug] = $term->count;
                }
            }
        }
    }

    // Save product counts in the option
    update_option('dapfforwc_product_count', $dapfforwc_product_counts);
}

// Hook to update product counts whenever products are updated
add_action('save_post_product', 'dapfforwc_update_product_counts');
add_action('edited_product_cat', 'dapfforwc_update_product_counts');
add_action('edited_product_tag', 'dapfforwc_update_product_counts');
add_action('edited_term', 'dapfforwc_update_product_counts');
add_action('init', 'dapfforwc_update_product_counts');
