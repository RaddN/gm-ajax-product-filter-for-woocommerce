<?php
function wcapf_product_filter_shortcode($atts) {
    global $styleoptions,$product_count,$post,$options;
    // Check if the post object is available
    if (isset($post)) {
        // Get the post slug
        $slug = $post->post_name;
    }
    $default_filter =$options["default_filters"][$slug] ?? [] ;
    $downarrow = '<svg class="rotatable" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 448 512" role="graphics-symbol" aria-hidden="false" aria-label=""><path d="M224 416c-8.188 0-16.38-3.125-22.62-9.375l-192-192c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0L224 338.8l169.4-169.4c12.5-12.5 32.75-12.5 45.25 0s12.5 32.75 0 45.25l-192 192C240.4 412.9 232.2 416 224 416z"></path></svg>';
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
    $sub_option = $styleoptions["category"]["sub_option"]; // Fetch the sub_option value
    $minimizable = $styleoptions["category"]["minimize"]["type"];
    $show_count = $styleoptions["category"]["show_product_count"];
    $singlevaluecataSelect = $styleoptions["category"]["single_selection"];
    // Display categories
    echo '<div class="filter-group category" style="display: ' . (!empty($options['show_categories']) ? 'block' : 'none') . ';">';
    echo '<div class="title collapsable_'.$minimizable.'">Category '.($minimizable === "arrow" ? '<div class="collaps">' . $downarrow . '</div>' : '').'</div>';
    $categories = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => true));
    $selected_categories = !empty($default_filter) ?  $default_filter : explode(',', $atts['category']);
    if ($sub_option==="select"||$sub_option==="select2"||$sub_option==="select2_classic") {
        echo '<select name="category[]" id="'.$sub_option.'" class="items '.$sub_option.' filter-select" '.($singlevaluecataSelect!=="yes" ? 'multiple="multiple"' : '').'>';
        echo '<option class="filter-checkbox" value=""> Any </option>';
    }else{
        echo '<div class="items '.$sub_option.'">';
    }
    if ($categories) {
        foreach ($categories as $category) {
            $checked = '';
            if ($sub_option === "select" || $sub_option === "select2" || $sub_option === "select2_classic") {
                // Set 'selected' if the category is in the selected categories for dropdowns
                if (in_array($category->slug, $selected_categories)) {
                    $checked = ' selected';
                }
            } else {
                // Set 'checked' if the category is in the selected categories for checkboxes
                if (in_array($category->slug, $selected_categories)) {
                    $checked = ' checked';
                }
            }
            $value = esc_attr($category->slug);
            $title = esc_html($category->name);
            $count = $show_count==="yes"? $product_count["categories"][$value] : 0;
            
            echo  render_filter_option($sub_option, $title, $value, $checked, $styleoptions, "category", "category",$singlevaluecataSelect,$count);
        }
    }
    if ($sub_option==="select"||$sub_option==="select2"||$sub_option==="select2_classic") {
    echo '</select>';
    }else{ echo '</div>';}
    echo '</div>';
    // category ends
    
// display attributes
        echo '<div class="filter-group attributes" style="display: ' . (!empty($options['show_attributes']) ? 'block' : 'none') . ';"><label style="display:none;">Attributes:</label>';
        $attributes = wc_get_attribute_taxonomies();
        
        if ($attributes) {
            foreach ($attributes as $attribute) {
                $terms = get_terms(array('taxonomy' => 'pa_' . $attribute->attribute_name, 'hide_empty' => true));
                $selected_terms = !empty($default_filter) ?  $default_filter : explode(',', $atts['terms']);
                $sub_optionattr = $styleoptions[$attribute->attribute_name]["sub_option"];
                $minimizable = $styleoptions[$attribute->attribute_name]["minimize"]["type"];
                $show_count = $styleoptions[$attribute->attribute_name]["show_product_count"];
                $singlevalueattrSelect = $styleoptions[$attribute->attribute_name]["single_selection"];
                if ($terms) {
                    usort($terms, function($a, $b) {
                        return customSort($a->name, $b->name);
                    });
                    echo '<div id="' . esc_attr($attribute->attribute_name) . '">
                    <div class="title collapsable_'.$minimizable.'">' . esc_html($attribute->attribute_label) . 
                    ($minimizable === "arrow" ? '<div class="collaps">' . $downarrow . '</div>' : '') . 
                    '</div>';
                    if ($sub_optionattr==="select"||$sub_optionattr==="select2"||$sub_optionattr==="select2_classic") {
                        echo '<select name="attribute['.esc_attr($attribute->attribute_name).'][]" id="'.$sub_optionattr.'" class="items '.$sub_optionattr.' filter-select" '.($singlevalueattrSelect!=="yes" ? 'multiple="multiple"' : '').'>';
                        echo '<option class="filter-checkbox" value=""> Any </option>';
                    }else{
                        echo '<div class="items '.$sub_optionattr.'">';
                    }
                    foreach ($terms as $term) {
                        $checked = in_array($term->slug, $selected_terms) ? ' checked' : '';
                        $count = $show_count === "yes" ? $product_count["attributes"]['pa_' . esc_attr($attribute->attribute_name)][esc_attr($term->slug)]: 0;
                        echo render_filter_option($sub_optionattr, esc_html($term->name) , esc_attr($term->slug), $checked, $styleoptions , "attribute[$attribute->attribute_name]",$attribute->attribute_name,$singlevalueattrSelect,$count);
                    }
                    if ($sub_optionattr==="select"||$sub_optionattr==="select2"||$sub_optionattr==="select2_classic") {
                        echo '</select>';
                        }else{ echo '</div>';}
                    echo '</div>';
                }
                
            }
        }
        echo '</div>';
// display tags
        $tags = get_terms(array('taxonomy' => 'product_tag', 'hide_empty' => true));
        $selected_tags = !empty($default_filter) ?  $default_filter : explode(',', $atts['tag']);
        $sub_option = $styleoptions["tag"]["sub_option"]; // Fetch the sub_option value
        $minimizable = $styleoptions["tag"]["minimize"]["type"];
        $show_count = $styleoptions["tag"]["show_product_count"];
        $singlevalueSelect = $styleoptions["tag"]["single_selection"];
        echo '<div class="filter-group tags" style="display: ' . (!empty($options['show_tags']) ? 'block' : 'none') . ';"><div class="title collapsable_'.$minimizable.'">Tags '.($minimizable === "arrow" ? '<div class="collaps">' . $downarrow . '</div>' : '').'</div>';
        if ($sub_option==="select"||$sub_option==="select2"||$sub_option==="select2_classic") {
            echo '<select name="tag[]" id="'.$sub_option.'" class="items '.$sub_option.' filter-select" '.($singlevalueSelect!=="yes" ? 'multiple="multiple"' : '').'>';
            echo '<option class="filter-checkbox" value=""> Any </option>';
        }else{
            echo '<div class="items '.$sub_option.'">';
        }
        if ($tags) {
            foreach ($tags as $tag) {
            $checked = in_array($tag->slug, $selected_tags) ? ' checked' : '';
            $value = esc_attr($tag->slug);
            $title = esc_html($tag->name);
            $count = $show_count==="yes"? $product_count["tags"][$value]: 0;
            
            echo  render_filter_option($sub_option, $title, $value, $checked, $styleoptions, "tag", $attribute="tag",$singlevalueSelect,$count);
            }
        }
        if ($sub_option==="select"||$sub_option==="select2"||$sub_option==="select2_classic") {
            echo '</select>';
            }else{ echo '</div>';}
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
function render_filter_option($sub_option, $title, $value, $checked, $styleoptions = [], $name, $attribute,$singlevalueSelect, $count) {
    $output = '';
    switch ($sub_option) {
        case 'checkbox':
            $output .= '<label><input type="'.($singlevalueSelect==="yes"?'radio':'checkbox').'" class="filter-checkbox" name="' . $name . '[]" value="' . $value . '"' . $checked . '> ' . $title . ($count!=0?' ('.$count.')':''). '</label>';
            break;

        case 'radio_check':
            $output .= '<label><input type="'.($singlevalueSelect==="yes"?'radio':'checkbox').'" class="filter-radio-check" name="' . $name . '[]" value="' . $value . '"' . $checked . '> ' . $title . ($count!=0?' ('.$count.')':''). '</label>';
            break;

        case 'radio':
            $output .= '<label><input type="'.($singlevalueSelect==="yes"?'radio':'checkbox').'" class="filter-radio" name="' . $name . '[]" value="' . $value . '"' . $checked . '> ' . $title . ($count!=0?' ('.$count.')':''). '</label>';
            break;

        case 'square_check':
            $output .= '<label class="square-option"><input type="'.($singlevalueSelect==="yes"?'radio':'checkbox').'" class="filter-square-check" name="' . $name . '[]" value="' . $value . '"' . $checked . '> <span>' . $title . ($count!=0?' ('.$count.')':''). '</span></label>';
            break;

        case 'square':
            $output .= '<label class="square-option"><input type="'.($singlevalueSelect==="yes"?'radio':'checkbox').'" class="filter-square" name="' . $name . '[]" value="' . $value . '"' . $checked . '> <span>' . $title . ($count!=0?' ('.$count.')':''). '</span></label>';
            break;

        case 'checkbox_hide':
            $output .= '<label><input type="'.($singlevalueSelect==="yes"?'radio':'checkbox').'" class="filter-checkbox" name="' . $name . '[]" value="' . $value . '"' . $checked . ' style="display:none;"> ' . $title . ($count!=0?' ('.$count.')':''). '</label>';
            break;

        case 'color':
        case 'color_no_border':
            $color = $styleoptions[$attribute]['colors'][$value] ?? '#000'; // Default color
            $border = ($sub_option === 'color_no_border') ? 'none' : '1px solid #000';
            $output .= '<label style="position: relative;"><input type="'.($singlevalueSelect==="yes"?'radio':'checkbox').'" class="filter-color" name="' . $name . '[]" value="' . $value . '"' . $checked . '>
                <span class="color-box" style="background-color: ' . $color . '; border: ' . $border . '; width: 30px; height: 30px;"></span></label>';
            break;

        case 'image':
        case 'image_no_border':
            $image = $styleoptions[$attribute]['images'][$value] ?? 'default-image.jpg'; // Default image
            $border_class = ($sub_option === 'image_no_border') ? 'no-border' : '';
            $output .= '<label class="image-option ' . $border_class . '">
                <input type="'.($singlevalueSelect==="yes"?'radio':'checkbox').'" class="filter-image" name="' . $name . '[]" value="' . $value . '"' . $checked . '>
                <img src="' . esc_url($image) . '" alt="' . esc_attr($title) . '" /></label>';
            break;

        case 'select2':
        case 'select2_classic':
        case 'select':
            $output .= '<option class="filter-option" value="' . $value . '"' . $checked . '> ' . $title . ($count!=0?' ('.$count.')':''). '</option>';
            break;

        default:
            $output .= '<label><input type="checkbox" class="filter-checkbox" name="' . $name . '[]" value="' . $value . '"' . $checked . '> ' . $title . ($count!=0?' ('.$count.')':''). '</label>';
            break;
    }

    return $output;
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


