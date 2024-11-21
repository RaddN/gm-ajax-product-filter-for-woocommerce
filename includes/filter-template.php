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
    
    if (!empty($atts['attribute'])|| !empty($atts['terms']) || !empty($atts['category']) || !empty($atts['tag'])) {
        echo "<script> rfilterindex = 1;</script>";
    }
    ?>
    <form id="product-filter" method="POST">
    <?php wp_nonce_field('gm-product-filter-action', 'gm-product-filter-nonce'); ?>
<?php
    $options = get_option('wcapf_options');
    
    // Display categories
        echo '<div class="filter-group category" style="display: ' . (!empty($options['show_categories']) ? 'block' : 'none') . ';"><label>Category:</label>';
        $categories = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => true));
        $selected_categories = explode(',', $atts['category']);
        if ($categories) {
            foreach ($categories as $category) {
                echo '<label> <input type="checkbox" class="filter-checkbox" name="category[]" value="' . esc_attr($category->slug) . '"' . (in_array($category->slug, $selected_categories) ? ' checked' : '') . '> ' . esc_html($category->name) . ' </label><br>';
                // echo '<label><input type="checkbox" class="filter-checkbox" name="category[]" value="' . $category->slug . '"' . (in_array($category->slug, (array)$atts['category']) ? ' checked' : '') . '> ' . $category->name . '</label><br>';
            }
        }
        echo '</div>';

        echo '<div class="filter-group attributes" style="display: ' . (!empty($options['show_attributes']) ? 'block' : 'none') . ';"><label style="display:none;">Attributes:</label>';
        $attributes = wc_get_attribute_taxonomies();
        
        if ($attributes) {
            foreach ($attributes as $attribute) {
                $terms = get_terms(array('taxonomy' => 'pa_' . $attribute->attribute_name, 'hide_empty' => true));
                $selected_terms = explode(',', $atts['terms']);
                if ($terms) {
                    usort($terms, function($a, $b) {
                        return customSort($a->name, $b->name);
                    });
                    echo '<div id="' . esc_attr($attribute->attribute_name) . '"> <div class="title">' . esc_html($attribute->attribute_label) . '</div><div class="items">';
                    foreach ($terms as $term) {
                        echo '<label><input type="checkbox" class="filter-checkbox" name="attribute[' . esc_attr($attribute->attribute_name) . '][]" value="' . esc_attr($term->slug) . '"' . (in_array($term->slug, $selected_terms) ? ' checked' : '') . '> ' . esc_html($term->name) . '</label>';
                    }
                    echo '</div></div>';
                }
            }
        }
        echo '</div>';
        echo '<div class="filter-group tags" style="display: ' . (!empty($options['show_tags']) ? 'block' : 'none') . ';"><label>Tags:</label>';
        $tags = get_terms(array('taxonomy' => 'product_tag', 'hide_empty' => true));
        $selected_tags = explode(',', $atts['tag']);
        if ($tags) {
            foreach ($tags as $tag) {
                echo '<label><input type="checkbox" class="filter-checkbox" name="tags[]" value="' . esc_attr($tag->slug) . '"' . (in_array($tag->slug, $selected_tags) ? ' checked' : '') . '> ' . esc_html($tag->name) . '</label><br>';
            }
        }
        echo '</div>';
    echo '</form>';
?>
<!-- Loader HTML -->
<div id="loader" style="display:none;"></div>
<div id="roverlay" style="display: none;"></div>

<div id="filtered-products">
    <!-- AJAX results will be displayed here -->
</div>
<?php

    // End output buffering and return content
    return ob_get_clean();
}
add_shortcode('wcapf_product_filter', 'wcapf_product_filter_shortcode');

// General sorting function
function customSort($a, $b) {
    // Try to convert to timestamp for date comparison
    $dateA = strtotime($a);
    $dateB = strtotime($b);

    if ($dateA && $dateB) {
        return $dateA <=> $dateB; // Both are dates
    }

    // Check if both are numeric
    if (is_numeric($a) && is_numeric($b)) {
        return $a <=> $b; // Both are numbers
    }

    // Fallback to string comparison
    return strcmp($a, $b);
}


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
        return '<p style="background:red;background: red;text-align: center;color: #fff;">Please provide an attribute slug. Expample: [wcapf_product_filter_single name="conference-by-month"]</p>';
    }

    // Generate the output
    $output = '<form class="rfilterbuttons" id="'.$atts['name'].'"><ul>';
    $output .= '</ul></form>';

    return $output;


}
add_shortcode('wcapf_product_filter_single', 'wcapf_product_filter_shortcode_single');