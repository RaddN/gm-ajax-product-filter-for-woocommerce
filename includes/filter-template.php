
<?php
function wcapf_product_filter_shortcode($atts) {
    // Define default attributes and merge with user-defined attributes
    $atts = shortcode_atts(array(
        'attribute' => '',
        'terms' => '',
        'category' => '',
        'tag' => '',
    ), $atts);

    // Prepare the query arguments based on the provided attributes
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'tax_query' => array('relation' => 'AND'),
    );

    // Add category filter if specified
    if (!empty($atts['category'])) {
        $categories = array_map('sanitize_text_field', explode(',', $atts['category']));
        foreach ($categories as $category) {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $category,
            );
        }
    }

    // Add attribute filter if specified
    if (!empty($atts['attribute']) && !empty($atts['terms'])) {
        $terms = array_map('sanitize_text_field', explode(',', $atts['terms']));
        foreach ($terms as $term) {
            $args['tax_query'][] = array(
                'taxonomy' => 'pa_' . sanitize_title($atts['attribute']),
                'field' => 'slug',
                'terms' => $term,
            );
        }
    }

    // Add tag filter if specified
    if (!empty($atts['tag'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'product_tag',
            'field' => 'slug',
            'terms' => sanitize_text_field($atts['tag']),
        );
    }
    if (!empty($atts['tag'])) {
        $tags = array_map('sanitize_text_field', explode(',', $atts['tag']));
        foreach ($tags as $tag) {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_tag',
                'field' => 'slug',
                'terms' => $tag,
            );
        }
    }

    // Query the products based on the filters
    $products = new WP_Query($args);
    
    ob_start(); // Start output buffering
    ?>
    <form id="product-filter" method="POST">
<?php
    $options = get_option('wcapf_options');
    
    // Display categories
    if (!empty($options['show_categories'])) {
        echo '<div class="filter-group category"><label>Category:</label>';
        $categories = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => true));
        $selected_categories = explode(',', $atts['category']);
        if ($categories) {
            foreach ($categories as $category) {
                echo '<label> <input type="checkbox" class="filter-checkbox" name="category[]" value="' . esc_attr($category->slug) . '"' . (in_array($category->slug, $selected_categories) ? ' checked' : '') . '> ' . esc_html($category->name) . ' </label><br>';
                // echo '<label><input type="checkbox" class="filter-checkbox" name="category[]" value="' . $category->slug . '"' . (in_array($category->slug, (array)$atts['category']) ? ' checked' : '') . '> ' . $category->name . '</label><br>';
            }
        }
        echo '</div>';
    }
    if (!empty($options['show_attributes'])) {
        echo '<div class="filter-group attributes"><label style="display:none;">Attributes:</label>';
        $attributes = wc_get_attribute_taxonomies();
        if ($attributes) {
            foreach ($attributes as $attribute) {
                $terms = get_terms(array('taxonomy' => 'pa_' . $attribute->attribute_name, 'hide_empty' => true));
                $selected_terms = explode(',', $atts['terms']);
                if ($terms) {
                    echo '<div id="'.$attribute->attribute_label.'"> <div class="title">' . $attribute->attribute_label . '</div><div class="items">';
                    foreach ($terms as $term) {
                        echo '<label><input type="checkbox" class="filter-checkbox" name="attribute[' . $attribute->attribute_name . '][]" value="' . $term->slug . '"' . (in_array($term->slug,  $selected_terms) ? ' checked' : '') . '> ' . $term->name . '</label>';
                    }
                    echo '</div></div>';
                }
            }
        }
        echo '</div>';
    }
    if (!empty($options['show_tags'])) {
        echo '<div class="filter-group tags"><label>Tags:</label>';
        $tags = get_terms(array('taxonomy' => 'product_tag', 'hide_empty' => true));
        $selected_tags = explode(',', $atts['tag']);
        if ($tags) {
            foreach ($tags as $tag) {
                echo '<label><input type="checkbox" class="filter-checkbox" name="tags[]" value="' . $tag->slug . '"' . (in_array($tag->slug, $selected_tags) ? ' checked' : '') . '> ' . $tag->name . '</label><br>';
            }
        }
        echo '</div>';
    }
    echo '</form>';
?>
<!-- Loader HTML -->
<div id="loader" style="display:none;">
</div>

<div id="filtered-products">
    <!-- AJAX results will be displayed here -->
</div>
<?php

    // End output buffering and return content
    return ob_get_clean();
}
add_shortcode('wcapf_product_filter', 'wcapf_product_filter_shortcode');



function wcapf_product_filter_shortcode_single($atts) {
    $atts = shortcode_atts(
        array(
            'name' => '', // Default attribute name
        ),
        $atts,
        'get_terms_by_attribute'
    );

    // Check if the name is provided
    if (empty($atts['name'])) {
        return 'Please provide an attribute name.';
    }

    // Generate the output
    $output = '<form class="rfilterbuttons"><ul>';
    $output .= '</ul></form>';

    return $output;


}
add_shortcode('wcapf_product_filter_single', 'wcapf_product_filter_shortcode_single');