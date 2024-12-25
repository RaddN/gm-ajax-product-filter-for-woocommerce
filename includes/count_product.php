<?php

if (!defined('ABSPATH')) {
    exit;
}

function update_product_counts() {
    $product_counts = array();

    // Get product categories and their counts
    $categories = get_terms(array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
    ));

    if (!is_wp_error($categories)) {
        foreach ($categories as $category) {
            $product_counts['categories'][$category->slug] = $category->count;
        }
    }

    // Get product tags and their counts
    $tags = get_terms(array(
        'taxonomy'   => 'product_tag',
        'hide_empty' => false,
    ));

    if (!is_wp_error($tags)) {
        foreach ($tags as $tag) {
            $product_counts['tags'][$tag->slug] = $tag->count;
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
                    $product_counts['attributes'][$taxonomy][$term->slug] = $term->count;
                }
            }
        }
    }

    // Save product counts in the option
    update_option('wcapf_product_count', $product_counts);
}

// Hook to update product counts whenever products are updated
add_action('save_post_product', 'update_product_counts');
add_action('edited_product_cat', 'update_product_counts');
add_action('edited_product_tag', 'update_product_counts');
add_action('edited_term', 'update_product_counts');
add_action('init', 'update_product_counts');
