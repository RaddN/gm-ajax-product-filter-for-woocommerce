<?php

if (!defined('ABSPATH')) {
    exit;
}

function dapfforwc_product_filter_shortcode($atts) {
    global $dapfforwc_styleoptions,$post,$dapfforwc_options, $dapfforwc_advance_settings;
    $use_anchor = $dapfforwc_advance_settings["use_anchor"] ?? "";
    $use_filters_word = $dapfforwc_options["use_filters_word_in_permalinks"] ?? "";
    $dapfforwc_slug = "";
    // Check if the post object is available
    if (isset($post)) {
        // Use the dapfforwc_get_full_slug function to get the complete slug
        $dapfforwc_slug = dapfforwc_get_full_slug($post->ID);
    }

    $second_operator = strpos($dapfforwc_slug, 'autosave') === false ? $dapfforwc_options["product_show_settings"][$dapfforwc_slug ]? strtoupper($dapfforwc_options["product_show_settings"][$dapfforwc_slug ]["operator_second"]) ?? "IN":"IN":"IN";
    $default_filter =$dapfforwc_options["default_filters"][$dapfforwc_slug ] ?? [] ;
    $dapfforwc_slug = get_transient('dapfforwc_slug');
    $filters_array = explode('/', str_replace('filters/', '', $dapfforwc_slug));
    $default_filter = array_merge($default_filter , $filters_array);
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
    if (!empty($atts['attribute']) && !empty($atts['terms'])) {
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
     if (!empty($atts['tag'])) {
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
    if (!empty($matched_cata)) {
        $args['tax_query'][] = array(
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => array_map('sanitize_text_field', $matched_cata),
            'operator' => $second_operator,
        );
        
    } 
    // Handle matched tags from $default_filter
     if (!empty($matched_tag)) {
        $args['tax_query'][] = array(
            'taxonomy' => 'product_tag',
            'field' => 'slug',
            'terms' => array_map('sanitize_text_field', $matched_tag),
            'operator' => $second_operator,
        );
    } 
    // Handle matched attributes from $default_filter
    if (!empty($matched_attributes)) {
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
    // echo "<pre>"; print_r($dapfforwc_styleoptions ); echo "</pre>";
    
    ob_start(); // Start output buffering
    ?>
    <form id="product-filter" method="POST">
    <?php
    wp_nonce_field('gm-product-filter-action', 'gm-product-filter-nonce'); 
    echo dapfforwc_filter_form($updated_filters,$default_filter,$use_anchor,$use_filters_word,$atts,$min_price=0,$max_price=10000);

    echo '</form>';
    ?>
    
<!-- Loader HTML -->
<?php echo $dapfforwc_options["loader_html"] ?>
<style><?php echo $dapfforwc_options["loader_css"] ?></style>
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
function dapfforwc_render_filter_option($sub_option, $title, $value, $checked, $dapfforwc_styleoptions , $name, $attribute,$singlevalueSelect, $count,$min_price=0,$max_price=10000) {
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
        case 'color_circle':
        case 'color_value':
            $color = $dapfforwc_styleoptions[$attribute]['colors'][$value] ?? '#000'; // Default color
            $border = ($sub_option === 'color_no_border') ? 'none' : '1px solid #000';
            $value_show = ($sub_option === 'color_value') ? 'block' : 'none';
            $output .= '<label style="position: relative;"><input type="'.($singlevalueSelect==="yes"?'radio':'checkbox').'" class="filter-color" name="' . $name . '[]" value="' . $value . '"' . $checked . '>
                <span class="color-box" style="background-color: ' . $color . '; border: ' . $border . '; width: 30px; height: 30px;"></span><span style="display:'.$value_show.';">'.$value.'<span></label>';
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
                $output .= '<div class="range-input"><label for="min-price">Min Price:</label>
        <input type="number" id="min-price" name="min_price" min="0" step="1" placeholder="Min" value="'.$min_price.'" style="position: relative; height: max-content; top: unset; pointer-events: all;">
        
        <label for="max-price">Max Price:</label>
        <input type="number" id="max-price" name="max_price" min="0" step="1" placeholder="Max" value="'.$max_price.'" style="position: relative; height: max-content; top: unset; pointer-events: all;"></div>';
                break;
        case 'slider':
            $output .= '<div class="price-input">
        <div class="field">
          <span>Min</span>
          <input type="number" id="min-price" name="min_price" class="input-min" min="0" value="'.$min_price.'">
        </div>
        <div class="separator">-</div>
        <div class="field">
          <span>Max</span>
          <input type="number" id="max-price" name="max_price" min="0" class="input-max" value="'.$max_price.'">
        </div>
      </div>
      <div class="slider">
        <div class="progress"></div>
      </div>
      <div class="range-input">
        <input type="range" id="price-range-min" class="range-min" min="0" max="10000" value="'.$min_price.'" >
        <input type="range" id="price-range-max" class="range-max" min="0" max="10000" value="'.$max_price.'">
      </div>';
            break;
        case 'price':
            $output .= '<div class="price-input" style="visibility: hidden; margin: 0;">
        <div class="field">
            <input type="number" id="min-price" name="min_price" class="input-min" min="0" value="'.$min_price.'">
        </div>
        <div class="separator">-</div>
        <div class="field">
            <input type="number" id="max-price" name="max_price" min="0" class="input-max" value="'.$max_price.'">
        </div>
        </div>
        <div class="slider">
        <div class="progress progress-percentage"></div>
        </div>
        <div class="range-input">
        <input type="range" id="price-range-min" class="range-min" min="0" max="10000" value="'.$min_price.'">
        <input type="range" id="price-range-max" class="range-max" min="0" max="10000" value="'.$max_price.'">
        </div>';
            break;
        case 'rating-text':
            $output .= '<label><input type="checkbox" name="rating[]" value="5" '.(in_array("5", $checked) ? ' checked' : '').'> 5 Stars 
    </label>
        <label><input type="checkbox" name="rating[]" value="4" '.(in_array("4", $checked) ? ' checked' : '').'> 4 Stars & Up</label>
        <label><input type="checkbox" name="rating[]" value="3" '.(in_array("3", $checked) ? ' checked' : '').'> 3 Stars & Up</label>
        <label><input type="checkbox" name="rating[]" value="2" '.(in_array("2", $checked) ? ' checked' : '').'> 2 Stars & Up</label>
        <label><input type="checkbox" name="rating[]" value="1" '.(in_array("1", $checked) ? ' checked' : '').'> 1 Star & Up</label>';
            break;
        case 'rating':
            for ( $i = 5; $i >= 1; $i-- ) {
                $output .= '<label>';
                $output .= '<input type="checkbox" name="rating[]" value="' . esc_attr( $i ) . '" '.(in_array($i, $checked) ? ' checked' : '').'>';
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
    $categoryHierarchyOutput = "";
    foreach ($categories as $category) {
        $value = esc_attr($category->slug);
        $title = esc_html($category->name);
        $count = $show_count === 'yes' ? $category->count : 0;
        $checked = in_array($category->slug, $selected_categories) ? ($sub_option === 'select' || str_contains($sub_option, 'select2') ? ' selected' : ' checked') : '';
        $anchorlink = $use_filters_word === 'on' ? "filters/$value" : $value;
        
        // Fetch child categories
        $child_categories = dapfforwc_get_child_categories($child_category, $category->term_id);

        // Render current category
        $categoryHierarchyOutput.= $use_anchor === 'on'
            ? '<a href="' . esc_attr($anchorlink) . '" style="display:flex;align-items: center;">'
                . dapfforwc_render_filter_option($sub_option, $title, $value, $checked, $dapfforwc_styleoptions, 'category', 'category', $singlevaluecataSelect, $count)
                . (!empty($child_categories) && $hierarchical === 'enable_hide_child' ? '<span class="show-sub-cata">+</span>' : '')
              . '</a>'
            : '<a style="display:flex;align-items: center;">'
                . dapfforwc_render_filter_option($sub_option, $title, $value, $checked, $dapfforwc_styleoptions, 'category', 'category', $singlevaluecataSelect, $count) . (!empty($child_categories) && $hierarchical === 'enable_hide_child' ? '<span class="show-sub-cata" style="cursor:pointer;">+</span>' : '')
              . '</a>';

        // Render child categories
        if (!empty($child_categories)) {
            $categoryHierarchyOutput.= '<div class="child-categories" style="display:' . ($hierarchical === 'enable_hide_child' ? 'none;' : 'block;') . '">';
            $categoryHierarchyOutput .= dapfforwc_render_category_hierarchy($child_categories, $selected_categories, $sub_option, $dapfforwc_styleoptions, $singlevaluecataSelect, $show_count, $use_anchor, $use_filters_word, $hierarchical, $child_category);
            $categoryHierarchyOutput.= '</div>';
        }
    }
    return $categoryHierarchyOutput;
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

function dapfforwc_product_filter_shortcode_selected() {

    // Generate the output
    $output = '<form class="rfilterselected"><ul>';
    $output .= '</ul></form>';

    return $output;


}
add_shortcode('plugincy_filters_selected', 'dapfforwc_product_filter_shortcode_selected');


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
        foreach ($categories as $category) {
            $category->count = count(array_filter($query->posts, function($product) use ($category) {
                return has_term($category->term_id, 'product_cat', $product->ID);
            }));
        }

        // Get attributes for the filtered products
        $attributes_taxonomies = wc_get_attribute_taxonomies();
        if ($attributes_taxonomies) {
            foreach ($attributes_taxonomies as $attribute) {
                $attribute_terms = wp_get_object_terms($product_ids, 'pa_' . $attribute->attribute_name, array('fields' => 'all'));
                if (!empty($attribute_terms)) {
                    foreach ($attribute_terms as $term) {
                        $term->count = count(array_filter($query->posts, function($product) use ($term, $attribute) {
                            return has_term($term->term_id, 'pa_' . $attribute->attribute_name, $product->ID);
                        }));
                    }
                    $attributes[$attribute->attribute_name] = $attribute_terms;
                }
            }
        }

        // Get tags for the filtered products
        $tags = wp_get_object_terms($product_ids, 'product_tag', array('fields' => 'all'));
        foreach ($tags as $tag) {
            $tag->count = count(array_filter($query->posts, function($product) use ($tag) {
                return has_term($tag->term_id, 'product_tag', $product->ID);
            }));
        }
    }
    
    $data = array(
        'categories' => $categories,
        'attributes' => $attributes,
        'tags' => $tags
    );

    return $data;
}