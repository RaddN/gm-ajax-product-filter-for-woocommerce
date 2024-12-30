<?php

if (!defined('ABSPATH')) {
    exit;
}

function dapfforwc_product_filter_shortcode($atts) {
    global $dapfforwc_styleoptions,$dapfforwc_product_count,$post,$dapfforwc_options, $dapfforwc_advance_settings;
    $use_anchor = $dapfforwc_advance_settings["use_anchor"] ?? "";
    $use_filters_word = $dapfforwc_options["use_filters_word_in_permalinks"] ?? "";
    $dapfforwc_slug = "";
    // Check if the post object is available
    if (isset($post)) {
        // Use the dapfforwc_get_full_slug function to get the complete slug
        $dapfforwc_slug = dapfforwc_get_full_slug($post->ID);
    }

    $second_operator = $dapfforwc_options["product_show_settings"][$dapfforwc_slug ]? strtoupper($dapfforwc_options["product_show_settings"][$dapfforwc_slug ]["operator_second"]) ?? "IN":"IN";
    $default_filter =$dapfforwc_options["default_filters"][$dapfforwc_slug ] ?? [] ;
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
        'post_status' => 'publish',
        'tax_query' => array('relation' => 'AND'),
    );
    
    $all_cata = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => true,
    ]);
    $all_tags = get_terms([
        'taxonomy' => 'product_tag',
        'hide_empty' => true,
    ]);
    
    // Fetch all attributes dynamically
    $all_attributes = wc_get_attribute_taxonomies();
    $attribute_taxonomies = array_map(function ($attr) {
        return 'pa_' . $attr->attribute_name;
    }, $all_attributes);
    
    $matched_cata = array();
    $matched_tag = array();
    $matched_attributes = array();
    
    // Iterate through $default_filter
    foreach ($default_filter as $filter) {
        foreach ($all_cata as $term) {
            if (strcasecmp($term->slug, $filter) === 0) {
                $matched_cata[] = $term->slug;
                break;
            }
        }
        foreach ($all_tags as $term) {
            if (strcasecmp($term->slug, $filter) === 0) {
                $matched_tag[] = $term->slug;
                break;
            }
        }
        foreach ($attribute_taxonomies as $taxonomy) {
            $attribute_terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => true]);
            foreach ($attribute_terms as $term) {
                if (strcasecmp($term->slug, $filter) === 0) {
                    $matched_attributes[$taxonomy][] = $term->slug;
                    break;
                }
            }
        }
    }
    
    // Add category filter
    if (!empty($atts['category'])) {
        $categories = array_map('sanitize_text_field', explode(',', $atts['category']));
        foreach ($categories as $category) {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $category,
                'operator' => $second_operator,
            );
        }
    } 
    // Add attribute filter
    else if (!empty($atts['attribute']) && !empty($atts['terms'])) {
        $terms = array_map('sanitize_text_field', explode(',', $atts['terms']));
        foreach ($terms as $term) {
            $args['tax_query'][] = array(
                'taxonomy' => 'pa_' . sanitize_title($atts['attribute']),
                'field' => 'slug',
                'terms' => $term,
                'operator' => $second_operator,
            );
        }
    } 
    // Add tag filter
    else if (!empty($atts['tag'])) {
        $tags = array_map('sanitize_text_field', explode(',', $atts['tag']));
        foreach ($tags as $tag) {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_tag',
                'field' => 'slug',
                'terms' => $tag,
                'operator' => $second_operator,
            );
        }
    } 
    // Handle matched categories from $default_filter
    else if (!empty($matched_cata)) {
        $args['tax_query'][] = array(
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => array_map('sanitize_text_field', $matched_cata),
            'operator' => $second_operator,
        );
        
    } 
    // Handle matched tags from $default_filter
    else if (!empty($matched_tag)) {
        $args['tax_query'][] = array(
            'taxonomy' => 'product_tag',
            'field' => 'slug',
            'terms' => array_map('sanitize_text_field', $matched_tag),
            'operator' => $second_operator,
        );
    } 
    // Handle matched attributes from $default_filter
    else if (!empty($matched_attributes)) {
        foreach ($matched_attributes as $taxonomy => $terms) {
            $args['tax_query'][] = array(
                'taxonomy' => $taxonomy,
                'field' => 'slug',
                'terms' => $terms,
                'operator' => $second_operator,
            );
        }
    }
    
    // Query the products based on the filters
    $products = new WP_Query($args);

    $updated_filters = dapfforwc_get_updated_filters($products);
    
    ob_start(); // Start output buffering
    ?>
    <form id="product-filter" method="POST">
    <?php wp_nonce_field('gm-product-filter-action', 'gm-product-filter-nonce'); 
    $dapfforwc_options = get_option('dapfforwc_options');
    $sub_option = $dapfforwc_styleoptions["price"]["sub_option"]??""; // Fetch the sub_option value
    $minimizable_price = $dapfforwc_styleoptions["price"]["minimize"]["type"]??"";
    $sub_option_rating = $dapfforwc_styleoptions["rating"]["sub_option"]??""; // Fetch the sub_option value
    $minimizable_rating = $dapfforwc_styleoptions["rating"]["minimize"]["type"]??"";
    ?>
    <!-- display rating -->
     <!-- Filter by Rating -->
      
<?php echo '<div id="rating" class="filter-group rating" style="display: ' . (!empty($dapfforwc_options['show_rating']) ? 'block' : 'none') . ';">'; ?>
 <?php echo '<div class="title collapsable_' . esc_attr($minimizable_rating) . '">Rating ' . ($minimizable_rating === "arrow" ? '<div class="collaps"><svg class="rotatable" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 448 512" role="graphics-symbol" aria-hidden="false" aria-label=""><path d="M224 416c-8.188 0-16.38-3.125-22.62-9.375l-192-192c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0L224 338.8l169.4-169.4c12.5-12.5 32.75-12.5 45.25 0s12.5 32.75 0 45.25l-192 192C240.4 412.9 232.2 416 224 416z"></path></svg></div>' : '') .'</div>';?>
    <div class="items rating <?php echo esc_attr($sub_option_rating);?>">
        <?php if($sub_option_rating) {echo  dapfforwc_render_filter_option($sub_option_rating, "", "", "", $dapfforwc_styleoptions, "", "","",""); }else{ echo "Choose style from product filters->form style -> rating";}?>
    </div>
</div>
    <!-- display price range -->

   <?php echo '<div id="price-range" class="filter-group price-range" style="display: ' . (!empty($dapfforwc_options['show_price_range']) ? 'block' : 'none') . ';">'; ?>
 <?php echo '<div class="title collapsable_' . esc_attr($minimizable_price) . '">Price Range ' . ($minimizable_price === "arrow" ? '<div class="collaps"><svg class="rotatable" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 448 512" role="graphics-symbol" aria-hidden="false" aria-label=""><path d="M224 416c-8.188 0-16.38-3.125-22.62-9.375l-192-192c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0L224 338.8l169.4-169.4c12.5-12.5 32.75-12.5 45.25 0s12.5 32.75 0 45.25l-192 192C240.4 412.9 232.2 416 224 416z"></path></svg></div>' : '') .'</div>';?>
    <div class="items">
        <?php if($sub_option) { echo  dapfforwc_render_filter_option($sub_option, "", "", "", $dapfforwc_styleoptions, "", "","","");}else{ echo "Choose style from product filters->form style -> price";} ?>
    </div>
</div>
<?php
// Fetch global options and style configurations
$dapfforwc_options = get_option('dapfforwc_options');
$sub_option = $dapfforwc_styleoptions['category']['sub_option'] ?? '';
$minimizable = $dapfforwc_styleoptions['category']['minimize']['type'] ?? '';
$show_count = $dapfforwc_styleoptions['category']['show_product_count'] ?? '';
$singlevaluecataSelect = $dapfforwc_styleoptions['category']['single_selection'] ?? '';
$hierarchical = $dapfforwc_styleoptions['category']['hierarchical']['type'] ?? '';
$selected_categories = !empty($default_filter) ? $default_filter : explode(',', $atts['category']);

// Fetch categories

// Render categories based on hierarchical mode
if ($hierarchical !== 'enable_separate' && !empty($updated_filters["categories"])) {
    echo '<div id="category" class="filter-group category" style="display: ' . (!empty($dapfforwc_options['show_categories']) ? 'block' : 'none') . ';">';
    echo '<div class="title collapsable_' . esc_attr($minimizable) . '">Category ' . ($minimizable === 'arrow' ? '<div class="collaps"><svg class="rotatable" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 448 512" role="graphics-symbol" aria-hidden="false" aria-label=""><path d="M224 416c-8.188 0-16.38-3.125-22.62-9.375l-192-192c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0L224 338.8l169.4-169.4c12.5-12.5 32.75-12.5 45.25 0s12.5 32.75 0 45.25l-192 192C240.4 412.9 232.2 416 224 416z"></path></svg></div>' : '') . '</div>';
    echo '<div class="items ' . esc_attr($sub_option) . '">';
}

if ($hierarchical === 'enable' || $hierarchical === 'enable_hide_child') {
    $parent_categories = [];
    $child_category = [];
    if (isset($updated_filters["categories"])) {
        foreach ($updated_filters["categories"] as $category) {
            // Check if the category is an instance of WP_Term and if its parent is 0
            if ($category instanceof WP_Term && $category->parent == 0) {
                $parent_categories[] = $category;
            }else{
                $child_category[] = $category;
            }
        }
    }
    $top_level_categories = $parent_categories;
    if ($top_level_categories) {
        dapfforwc_render_category_hierarchy($top_level_categories, $selected_categories, $sub_option, $dapfforwc_styleoptions, $singlevaluecataSelect, $show_count, $use_anchor, $use_filters_word, $hierarchical, $child_category);
    }
} elseif ($hierarchical === 'enable_separate') {
    // Render parent categories in a unified section
    $parent_categories = [];
    $child_category = [];
    if (isset($updated_filters["categories"])) {
        foreach ($updated_filters["categories"] as $category) {
            // Check if the category is an instance of WP_Term and if its parent is 0
            if ($category instanceof WP_Term && $category->parent == 0) {
                $parent_categories[] = $category;
            }else{
                $child_category[] = $category;
            }
        }
    }
    $parent_categories = $parent_categories;

    echo '<div id="category" class="filter-group category" style="display: ' . (!empty($dapfforwc_options['show_categories']) ? 'block' : 'none') . ';">';
    echo '<div class="title collapsable_' . esc_attr($minimizable) . '">Categories ' . ($minimizable === 'arrow' ? '<div class="collaps"><svg class="rotatable" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 448 512" role="graphics-symbol" aria-hidden="false" aria-label=""><path d="M224 416c-8.188 0-16.38-3.125-22.62-9.375l-192-192c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0L224 338.8l169.4-169.4c12.5-12.5 32.75-12.5 45.25 0s12.5 32.75 0 45.25l-192 192C240.4 412.9 232.2 416 224 416z"></path></svg></div>' : '') . '</div>';
    echo '<div class="items ' . esc_attr($sub_option) . '">';

    foreach ($parent_categories as $parent_category) {
        $value = esc_attr($parent_category->slug);
        $title = esc_html($parent_category->name);
        $count = $show_count === 'yes' ? $parent_category->count : 0;
        $checked = in_array($parent_category->slug, $selected_categories) ? ($sub_option === 'select' || str_contains($sub_option, 'select2') ? ' selected' : ' checked') : '';
        $anchorlink = $use_filters_word === 'on' ? "filters/$value" : $value;

        echo $use_anchor === 'on'
            ? '<a href="' . esc_attr($anchorlink) . '">' . dapfforwc_render_filter_option($sub_option, $title, $value, $checked, $dapfforwc_styleoptions, 'category', 'category', $singlevaluecataSelect, $count) . '</a>'
            : dapfforwc_render_filter_option($sub_option, $title, $value, $checked, $dapfforwc_styleoptions, 'category', 'category', $singlevaluecataSelect, $count);
    }

    echo '</div></div>';

    // Render child categories grouped by parent
    foreach ($parent_categories as $parent_category) {
        $child_categories = dapfforwc_get_child_categories($child_category, $parent_category->term_id);

        if (!empty($child_categories)) {
            echo '<div id="category-with-child" class="filter-group category with-child" style="display: ' . (!empty($dapfforwc_options['show_categories']) ? 'block' : 'none') . ';">';
            echo '<div class="title collapsable_' . esc_attr($minimizable) . '">' . esc_html($parent_category->name) . ' ' . ($minimizable === 'arrow' ? '<div class="collaps"><svg class="rotatable" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 448 512" role="graphics-symbol" aria-hidden="false" aria-label=""><path d="M224 416c-8.188 0-16.38-3.125-22.62-9.375l-192-192c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0L224 338.8l169.4-169.4c12.5-12.5 32.75-12.5 45.25 0s12.5 32.75 0 45.25l-192 192C240.4 412.9 232.2 416 224 416z"></path></svg></div>' : '') . '</div>';
            echo '<div class="items ' . esc_attr($sub_option) . '">';

            dapfforwc_render_category_hierarchy($child_categories, $selected_categories, $sub_option, $dapfforwc_styleoptions, $singlevaluecataSelect, $show_count, $use_anchor, $use_filters_word, $hierarchical, $child_categories);

            echo '</div></div>';
        }
    }
} else {
    // Render categories non-hierarchically
    foreach ($updated_filters["categories"] as $category) {
        $value = esc_attr($category->slug);
        $title = esc_html($category->name);
        $count = $show_count === 'yes' ? $dapfforwc_product_count['categories'][$value] : 0;
        $checked = in_array($category->slug, $selected_categories) ? ($sub_option === 'select' || str_contains($sub_option, 'select2') ? ' selected' : ' checked') : '';
        $anchorlink = $use_filters_word === 'on' ? "filters/$value" : $value;

        echo $use_anchor === 'on'
            ? '<a href="' . esc_attr($anchorlink) . '">' . dapfforwc_render_filter_option($sub_option, $title, $value, $checked, $dapfforwc_styleoptions, 'category', 'category', $singlevaluecataSelect, $count) . '</a>'
            : dapfforwc_render_filter_option($sub_option, $title, $value, $checked, $dapfforwc_styleoptions, 'category', 'category', $singlevaluecataSelect, $count);
    }
}

if ($hierarchical !== 'enable_separate' && !empty($updated_filters["categories"])) {
    echo '</div></div>';
}
?>
<?php
// category ends
    
    
// display attributes
        echo '<div class="filter-group attributes" style="display: ' . (!empty($dapfforwc_options['show_attributes']) ? 'block' : 'none') . ';"><label style="display:none;">Attributes:</label>';
        $attributes = $updated_filters["attributes"];

        if ($attributes) {
            foreach ($attributes as $attribute_name => $attribute_terms) {
                $terms = $attribute_terms; // Directly use the terms from the array
                $selected_terms = !empty($default_filter) ? $default_filter : explode(',', $atts['terms']);
                $sub_optionattr = $dapfforwc_styleoptions[$attribute_name]["sub_option"] ?? "";
                $minimizable = $dapfforwc_styleoptions[$attribute_name]["minimize"]["type"] ?? "";
                $show_count = $dapfforwc_styleoptions[$attribute_name]["show_product_count"] ?? "";
                $singlevalueattrSelect = $dapfforwc_styleoptions[$attribute_name]["single_selection"] ?? "";
                
                if ($terms) {
                    usort($terms, function($a, $b) {
                        return dapfforwc_customSort($a->name, $b->name);
                    });
                    echo '<div id="' . esc_attr($attribute_name) . '">
                            <div class="title collapsable_' . esc_attr($minimizable) . '">' . esc_html($attribute_name) . 
                            ($minimizable === "arrow" ? '<div class="collaps"><svg class="rotatable" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 448 512" role="graphics-symbol" aria-hidden="false" aria-label=""><path d="M224 416c-8.188 0-16.38-3.125-22.62-9.375l-192-192c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0L224 338.8l169.4-169.4c12.5-12.5 32.75-12.5 45.25 0s12.5 32.75 0 45.25l-192 192C240.4 412.9 232.2 416 224 416z"></path></svg></div>' : '') . 
                            '</div>';
                            
                    if ($sub_optionattr === "select" || $sub_optionattr === "select2" || $sub_optionattr === "select2_classic") {
                        echo '<select name="attribute[' . esc_attr($attribute_name) . '][]" id="' . esc_attr($sub_optionattr) . '" class="items ' . esc_attr($sub_optionattr) . ' filter-select" ' . ($singlevalueattrSelect !== "yes" ? 'multiple="multiple"' : '') . '>';
                        echo '<option class="filter-checkbox" value=""> Any </option>';
                    } else {
                        echo '<div class="items ' . esc_attr($sub_optionattr) . '">';
                    }

                    foreach ($terms as $term) {
                        $checked = in_array($term->slug, $selected_terms) ? ' checked' : '';
                        $count = $show_count === "yes" ? $term->count : 0; // Use term count directly
                        $anchorlink = $use_filters_word === "on" ? "filters/" . esc_attr($term->slug) : esc_attr($term->slug);
                        echo $use_anchor === "on" ? '<a href="' . esc_attr($anchorlink) . '">' . dapfforwc_render_filter_option($sub_optionattr, esc_html($term->name), esc_attr($term->slug), $checked, $dapfforwc_styleoptions, "attribute[$attribute_name]", $attribute_name, $singlevalueattrSelect, $count) . '</a>' : dapfforwc_render_filter_option($sub_optionattr, esc_html($term->name), esc_attr($term->slug), $checked, $dapfforwc_styleoptions, "attribute[$attribute_name]", $attribute_name, $singlevalueattrSelect, $count);
                    }

                    if ($sub_optionattr === "select" || $sub_optionattr === "select2" || $sub_optionattr === "select2_classic") {
                        echo '</select>';
                    } else {
                        echo '</div>';
                    }
                    echo '</div>';
                }
            }
        }
        echo '</div>';
// display tags
        $tags = $updated_filters["tags"];
        if(!empty($tags)){
        $selected_tags = !empty($default_filter) ?  $default_filter : explode(',', $atts['tag']);
        $sub_option = $dapfforwc_styleoptions["tag"]["sub_option"]??""; // Fetch the sub_option value
        $minimizable = $dapfforwc_styleoptions["tag"]["minimize"]["type"]??"";
        $show_count = $dapfforwc_styleoptions["tag"]["show_product_count"]??"";
        $singlevalueSelect = $dapfforwc_styleoptions["tag"]["single_selection"]??"";
        echo '<div id="tags" class="filter-group tags" style="display: ' . (!empty($dapfforwc_options['show_tags']) ? 'block' : 'none') . ';"><div class="title collapsable_'.esc_attr($minimizable).'">Tags '.($minimizable === "arrow" ? '<div class="collaps"><svg class="rotatable" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 448 512" role="graphics-symbol" aria-hidden="false" aria-label=""><path d="M224 416c-8.188 0-16.38-3.125-22.62-9.375l-192-192c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0L224 338.8l169.4-169.4c12.5-12.5 32.75-12.5 45.25 0s12.5 32.75 0 45.25l-192 192C240.4 412.9 232.2 416 224 416z"></path></svg></div>' : '').'</div>';
        if ($sub_option==="select"||$sub_option==="select2"||$sub_option==="select2_classic") {
            echo '<select name="tag[]" id="'.esc_attr($sub_option).'" class="items '.esc_attr($sub_option).' filter-select" '.($singlevalueSelect!=="yes" ? 'multiple="multiple"' : '').'>';
            echo '<option class="filter-checkbox" value=""> Any </option>';
        }else{
            echo '<div class="items '.esc_attr($sub_option).'">';
        }
        if ($tags) {
            foreach ($tags as $tag) {
            $checked = in_array($tag->slug, $selected_tags) ? ' checked' : '';
            $value = esc_attr($tag->slug);
            $title = esc_html($tag->name);
            $count = $show_count==="yes"? $dapfforwc_product_count["tags"][$value]: 0;
            $anchorlink = $use_filters_word ==="on"?"filters/$value":$value;
            echo $use_anchor==="on" ? '<a href="'.esc_attr($anchorlink) .'">'. dapfforwc_render_filter_option($sub_option, $title, $value, $checked, $dapfforwc_styleoptions, "tag", $attribute="tag",$singlevalueSelect,$count).'</a>' :  dapfforwc_render_filter_option($sub_option, $title, $value, $checked, $dapfforwc_styleoptions, "tag", $attribute="tag",$singlevalueSelect,$count);
            }
        }
        if ($sub_option==="select"||$sub_option==="select2"||$sub_option==="select2_classic") {
            echo '</select>';
            }else{ echo '</div>';}
        echo '</div>';
        }
        // tags ends
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
add_shortcode('plugincy_filters', 'dapfforwc_product_filter_shortcode');

// General sorting function
function dapfforwc_customSort($a, $b) {
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
function dapfforwc_render_filter_option($sub_option, $title, $value, $checked, $dapfforwc_styleoptions , $name, $attribute,$singlevalueSelect, $count) {
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
            $color = $dapfforwc_styleoptions[$attribute]['colors'][$value] ?? '#000'; // Default color
            $border = ($sub_option === 'color_no_border') ? 'none' : '1px solid #000';
            $output .= '<label style="position: relative;"><input type="'.($singlevalueSelect==="yes"?'radio':'checkbox').'" class="filter-color" name="' . $name . '[]" value="' . $value . '"' . $checked . '>
                <span class="color-box" style="background-color: ' . $color . '; border: ' . $border . '; width: 30px; height: 30px;"></span></label>';
            break;

        case 'image':
        case 'image_no_border':
            $image = $dapfforwc_styleoptions[$attribute]['images'][$value] ?? 'default-image.jpg'; // Default image
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
        case 'input-price-range':
                $output .= '<label for="min-price">Min Price:</label>
        <input type="number" id="min-price" name="min_price" min="0" step="1" placeholder="Min">
        
        <label for="max-price">Max Price:</label>
        <input type="number" id="max-price" name="max_price" min="0" step="1" placeholder="Max">';
                break;
        case 'slider':
            $output .= '<div class="price-input">
        <div class="field">
          <span>Min</span>
          <input type="number" id="min-price" name="min_price" class="input-min" min="0" value="0">
        </div>
        <div class="separator">-</div>
        <div class="field">
          <span>Max</span>
          <input type="number" id="max-price" name="max_price" min="0" class="input-max" value="10000">
        </div>
      </div>
      <div class="slider">
        <div class="progress"></div>
      </div>
      <div class="range-input">
        <input type="range" id="price-range-min" class="range-min" min="0" max="10000" value="0" >
        <input type="range" id="price-range-max" class="range-max" min="0" max="10000" value="10000">
      </div>';
            break;
        case 'price':
            $output .= '<div class="price-input">
        <div class="field">
            <input type="number" id="min-price" name="min_price" class="input-min" min="0" value="0">
        </div>
        <div class="separator">-</div>
        <div class="field">
            <input type="number" id="max-price" name="max_price" min="0" class="input-max" value="10000">
        </div>
        </div>
        <div class="slider">
        <div class="progress"></div>
        </div>
        <div class="range-input">
        <input type="range" id="price-range-min" class="range-min" min="0" max="10000" value="0">
        <input type="range" id="price-range-max" class="range-max" min="0" max="10000" value="10000">
        </div>';
            break;
        case 'rating-text':
            $output .= '<label><input type="checkbox" name="rating[]" value="5"> 5 Stars 
    </label>
        <label><input type="checkbox" name="rating[]" value="4"> 4 Stars & Up</label>
        <label><input type="checkbox" name="rating[]" value="3"> 3 Stars & Up</label>
        <label><input type="checkbox" name="rating[]" value="2"> 2 Stars & Up</label>
        <label><input type="checkbox" name="rating[]" value="1"> 1 Star & Up</label>';
            break;
        case 'rating':
            for ( $i = 5; $i >= 1; $i-- ) {
                $output .= '<label>';
                $output .= '<input type="checkbox" name="rating[]" value="' . esc_attr( $i ) . '">';
                $output .= '<span class="stars">';
                for ( $j = 1; $j <= $i; $j++ ) {
                    $output .= '<i class="fa fa-star" aria-hidden="true"></i>';
                }
                $output .= '</span>';
                $output .= '</label>';
            }
            break;
        case 'dynamic-rating':
            $output .= '<input type="radio" id="star5" name="rating[]" value="5" />
  <label class="star" for="star5" title="Awesome" aria-hidden="true"></label>
  <input type="radio" id="star4" name="rating[]" value="4" />
  <label class="star" for="star4" title="Great" aria-hidden="true"></label>
  <input type="radio" id="star3" name="rating[]" value="3" />
  <label class="star" for="star3" title="Very good" aria-hidden="true"></label>
  <input type="radio" id="star2" name="rating[]" value="2" />
  <label class="star" for="star2" title="Good" aria-hidden="true"></label>
  <input type="radio" id="star1" name="rating[]" value="1" />
  <label class="star" for="star1" title="Bad" aria-hidden="true"></label>';
            break;
        default:
            $output .= '<label><input type="checkbox" class="filter-checkbox" name="' . $name . '[]" value="' . $value . '"' . $checked . '> ' . $title . ($count!=0?' ('.$count.')':''). '</label>';
            break;
    }

    return $output;
}
// Function to get child categories from $updated_filters["categories"]
function dapfforwc_get_child_categories($categories, $parent_id) {
    $child_categories = array();

    foreach ($categories as $category) {
        if ($category instanceof WP_Term && $category->parent == $parent_id) {
            $child_categories[] = $category;
        }
    }

    return $child_categories;
}
// Recursive function to render categories
function dapfforwc_render_category_hierarchy(
    $categories, 
    $selected_categories, 
    $sub_option, 
    $dapfforwc_styleoptions, 
    $singlevaluecataSelect, 
    $show_count, 
    $use_anchor, 
    $use_filters_word, 
    $hierarchical,
    $child_category
) {
    foreach ($categories as $category) {
        $value = esc_attr($category->slug);
        $title = esc_html($category->name);
        $count = $show_count === 'yes' ? $category->count : 0;
        $checked = in_array($category->slug, $selected_categories) ? ($sub_option === 'select' || str_contains($sub_option, 'select2') ? ' selected' : ' checked') : '';
        $anchorlink = $use_filters_word === 'on' ? "filters/$value" : $value;
        
        // Fetch child categories
        $child_categories = dapfforwc_get_child_categories($child_category, $category->term_id);

        // Render current category
        echo $use_anchor === 'on'
            ? '<a href="' . esc_attr($anchorlink) . '" style="display:flex;align-items: center;">'
                . dapfforwc_render_filter_option($sub_option, $title, $value, $checked, $dapfforwc_styleoptions, 'category', 'category', $singlevaluecataSelect, $count)
                . (!empty($child_categories) && $hierarchical === 'enable_hide_child' ? '<span class="show-sub-cata">+</span>' : '')
              . '</a>'
            : '<a style="display:flex;align-items: center;">'
                . dapfforwc_render_filter_option($sub_option, $title, $value, $checked, $dapfforwc_styleoptions, 'category', 'category', $singlevaluecataSelect, $count) . (!empty($child_categories) && $hierarchical === 'enable_hide_child' ? '<span class="show-sub-cata" style="cursor:pointer;">+</span>' : '')
              . '</a>';

        // Render child categories
        if (!empty($child_categories)) {
            echo '<div class="child-categories" style="display:' . ($hierarchical === 'enable_hide_child' ? 'none;' : 'block;') . '">';
            dapfforwc_render_category_hierarchy($child_categories, $selected_categories, $sub_option, $dapfforwc_styleoptions, $singlevaluecataSelect, $show_count, $use_anchor, $use_filters_word, $hierarchical, $child_category);
            echo '</div>';
        }
    }
}

function dapfforwc_product_filter_shortcode_single($atts) {
    $atts = shortcode_atts(
        array(
            'name' => '', // Default attribute name
        ),
        $atts,
        'get_terms_by_attribute'
    );

    // Check if the name is provided
    if (empty($atts['name'])) {
        return '<p style="background:red;background: red;text-align: center;color: #fff;">Please provide an attribute slug. Expample: [plugincy_filters_single name="conference-by-month"]</p>';
    }

    // Generate the output
    $output = '<form class="rfilterbuttons" id="'.$atts['name'].'"><ul>';
    $output .= '</ul></form>';

    return $output;


}
add_shortcode('plugincy_filters_single', 'dapfforwc_product_filter_shortcode_single');


function dapfforwc_get_updated_filters($query) {
    // Get the current product IDs based on the filtered query
    $product_ids = wp_list_pluck($query->posts, 'ID');
    // Initialize arrays to store filter data
    $categories = array();
    $attributes = array();
    $tags = array();

    if (!empty($product_ids)) {
        // Get categories for the filtered products
        $categories = wp_get_object_terms($product_ids, 'product_cat', array('fields' => 'all'));

        // Get attributes for the filtered products
        $attributes_taxonomies = wc_get_attribute_taxonomies();
        if ($attributes_taxonomies) {
            foreach ($attributes_taxonomies as $attribute) {
                $attribute_terms = wp_get_object_terms($product_ids, 'pa_' . $attribute->attribute_name, array('fields' => 'all'));
                if (!empty($attribute_terms)) {
                    $attributes[$attribute->attribute_name] = $attribute_terms;
                }
            }
        }
        // Get tags for the filtered products
        $tags = wp_get_object_terms($product_ids, 'product_tag', array('fields' => 'all'));
    }
    

    $data = array(
        'categories' => $categories,
        'attributes' => $attributes,
        'tags' => $tags
    );

    return $data;
}