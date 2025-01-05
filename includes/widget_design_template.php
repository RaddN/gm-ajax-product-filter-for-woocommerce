
<?php 

if (!defined('ABSPATH')) {
    exit;
}

function dapfforwc_filter_form($updated_filters,$default_filter,$use_anchor,$use_filters_word,$atts,$min_price,$max_price){
    global $dapfforwc_styleoptions,$post,$dapfforwc_options, $dapfforwc_advance_settings;
    $dapfforwc_product_count = [];

// Extract category counts
$dapfforwc_product_count['categories'] = [];
foreach ($updated_filters['categories'] as $category) {
    $dapfforwc_product_count['categories'][$category->slug] = $category->count;
}

// Extract tag counts
$dapfforwc_product_count['tags'] = [];
foreach ($updated_filters['tags'] as $tag) {
    $dapfforwc_product_count['tags'][$tag->slug] = $tag->count;
}

// Extract attribute counts
$dapfforwc_product_count['attributes'] = [];
foreach ($updated_filters['attributes'] as $key => $terms) {
    $dapfforwc_product_count['attributes'][$key] = [];
    foreach ($terms as $term) {
        $dapfforwc_product_count['attributes'][$key][$term->slug] = $term->count;
    }
}

    $formOutPut = ""
    
    ?>
    <?php 
    $sub_option = $dapfforwc_styleoptions["price"]["sub_option"]??""; // Fetch the sub_option value
    $minimizable_price = $dapfforwc_styleoptions["price"]["minimize"]["type"]??"";
    $sub_option_rating = $dapfforwc_styleoptions["rating"]["sub_option"]??""; // Fetch the sub_option value
    $minimizable_rating = $dapfforwc_styleoptions["rating"]["minimize"]["type"]??"";
    ?>
      
<?php $formOutPut .= '<div id="rating" class="filter-group rating" style="display: ' . (!empty($dapfforwc_options['show_rating']) ? 'block' : 'none') . ';">'; ?>
 <?php $formOutPut .= '<div class="title collapsable_' . esc_attr($minimizable_rating) . '"><div> Rating <span id="reset-rating" style="font-size:14px;color:red; cursor:pointer;">reset</span></div>' . ($minimizable_rating === "arrow" ? '<div class="collaps"><svg class="rotatable" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 448 512" role="graphics-symbol" aria-hidden="false" aria-label=""><path d="M224 416c-8.188 0-16.38-3.125-22.62-9.375l-192-192c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0L224 338.8l169.4-169.4c12.5-12.5 32.75-12.5 45.25 0s12.5 32.75 0 45.25l-192 192C240.4 412.9 232.2 416 224 416z"></path></svg></div>' : '') .'</div>';
   $formOutPut .= '<div class="items rating '.esc_attr($sub_option_rating).'"> ';?>
        <?php if($sub_option_rating) {$formOutPut .=  dapfforwc_render_filter_option($sub_option_rating, "", "", $checked = $default_filter, $dapfforwc_styleoptions, "", "","",""); }else{ $formOutPut .= "Choose style from product filters->form style -> rating";}
        $formOutPut .='</div></div>';?>

   <?php $formOutPut .= '<div id="price-range" class="filter-group price-range" style="display: ' . (!empty($dapfforwc_options['show_price_range']) ? 'block' : 'none') . ';">'; ?>
 <?php $formOutPut .= '<div class="title collapsable_' . esc_attr($minimizable_price) . '">Price Range ' . ($minimizable_price === "arrow" ? '<div class="collaps"><svg class="rotatable" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 448 512" role="graphics-symbol" aria-hidden="false" aria-label=""><path d="M224 416c-8.188 0-16.38-3.125-22.62-9.375l-192-192c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0L224 338.8l169.4-169.4c12.5-12.5 32.75-12.5 45.25 0s12.5 32.75 0 45.25l-192 192C240.4 412.9 232.2 416 224 416z"></path></svg></div>' : '') .'</div>';
    $formOutPut .= '<div class="items">';?>
        <?php if($sub_option) { $formOutPut .=  dapfforwc_render_filter_option($sub_option, "", "", "", $dapfforwc_styleoptions, "", "","","",$min_price,$max_price);}else{ $formOutPut .= "Choose style from product filters->form style -> price";} 
    $formOutPut .='</div></div>';
// Fetch global options and style configurations

$sub_option = $dapfforwc_styleoptions['category']['sub_option'] ?? '';
$minimizable = $dapfforwc_styleoptions['category']['minimize']['type'] ?? '';
$show_count = $dapfforwc_styleoptions['category']['show_product_count'] ?? '';
$singlevaluecataSelect = $dapfforwc_styleoptions['category']['single_selection'] ?? '';
$hierarchical = $dapfforwc_styleoptions['category']['hierarchical']['type'] ?? '';
$selected_categories = !empty($default_filter) ? $default_filter : explode(',', $atts['category']);

// Fetch categories

// Render categories based on hierarchical mode
if ($hierarchical !== 'enable_separate' && !empty($updated_filters["categories"])) {
    $formOutPut .= '<div id="category" class="filter-group category" style="display: ' . (!empty($dapfforwc_options['show_categories']) ? 'block' : 'none') . ';">';
    $formOutPut .= '<div class="title collapsable_' . esc_attr($minimizable) . '">Category ' . ($minimizable === 'arrow' ? '<div class="collaps"><svg class="rotatable" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 448 512" role="graphics-symbol" aria-hidden="false" aria-label=""><path d="M224 416c-8.188 0-16.38-3.125-22.62-9.375l-192-192c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0L224 338.8l169.4-169.4c12.5-12.5 32.75-12.5 45.25 0s12.5 32.75 0 45.25l-192 192C240.4 412.9 232.2 416 224 416z"></path></svg></div>' : '') . '</div>';
    $formOutPut .= '<div class="items ' . esc_attr($sub_option) . '">';
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
        $formOutPut .= dapfforwc_render_category_hierarchy($top_level_categories, $selected_categories, $sub_option, $dapfforwc_styleoptions, $singlevaluecataSelect, $show_count, $use_anchor, $use_filters_word, $hierarchical, $child_category);
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

    $formOutPut .= '<div id="category" class="filter-group category" style="display: ' . (!empty($dapfforwc_options['show_categories']) ? 'block' : 'none') . ';">';
    $formOutPut .= '<div class="title collapsable_' . esc_attr($minimizable) . '">Categories ' . ($minimizable === 'arrow' ? '<div class="collaps"><svg class="rotatable" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 448 512" role="graphics-symbol" aria-hidden="false" aria-label=""><path d="M224 416c-8.188 0-16.38-3.125-22.62-9.375l-192-192c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0L224 338.8l169.4-169.4c12.5-12.5 32.75-12.5 45.25 0s12.5 32.75 0 45.25l-192 192C240.4 412.9 232.2 416 224 416z"></path></svg></div>' : '') . '</div>';
    $formOutPut .= '<div class="items ' . esc_attr($sub_option) . '">';

    foreach ($parent_categories as $parent_category) {
        $value = esc_attr($parent_category->slug);
        $title = esc_html($parent_category->name);
        $count = $show_count === 'yes' ? $parent_category->count : 0;
        $checked = in_array($parent_category->slug, $selected_categories) ? ($sub_option === 'select' || str_contains($sub_option, 'select2') ? ' selected' : ' checked') : '';
        $anchorlink = $use_filters_word === 'on' ? "filters/$value" : $value;

        $formOutPut .= $use_anchor === 'on'
            ? '<a href="' . esc_attr($anchorlink) . '">' . dapfforwc_render_filter_option($sub_option, $title, $value, $checked, $dapfforwc_styleoptions, 'category', 'category', $singlevaluecataSelect, $count) . '</a>'
            : dapfforwc_render_filter_option($sub_option, $title, $value, $checked, $dapfforwc_styleoptions, 'category', 'category', $singlevaluecataSelect, $count);
    }

    $formOutPut .= '</div></div>';

    // Render child categories grouped by parent
    foreach ($parent_categories as $parent_category) {
        $child_categories = dapfforwc_get_child_categories($child_category, $parent_category->term_id);

        if (!empty($child_categories)) {
            $formOutPut .= '<div id="category-with-child" class="filter-group category with-child" style="display: ' . (!empty($dapfforwc_options['show_categories']) ? 'block' : 'none') . ';">';
            $formOutPut .= '<div class="title collapsable_' . esc_attr($minimizable) . '">' . esc_html($parent_category->name) . ' ' . ($minimizable === 'arrow' ? '<div class="collaps"><svg class="rotatable" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 448 512" role="graphics-symbol" aria-hidden="false" aria-label=""><path d="M224 416c-8.188 0-16.38-3.125-22.62-9.375l-192-192c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0L224 338.8l169.4-169.4c12.5-12.5 32.75-12.5 45.25 0s12.5 32.75 0 45.25l-192 192C240.4 412.9 232.2 416 224 416z"></path></svg></div>' : '') . '</div>';
            $formOutPut .= '<div class="items ' . esc_attr($sub_option) . '">';

            $formOutPut .= dapfforwc_render_category_hierarchy($child_categories, $selected_categories, $sub_option, $dapfforwc_styleoptions, $singlevaluecataSelect, $show_count, $use_anchor, $use_filters_word, $hierarchical, $child_categories);

            $formOutPut .= '</div></div>';
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

        $formOutPut .= $use_anchor === 'on'
            ? '<a href="' . esc_attr($anchorlink) . '">' . dapfforwc_render_filter_option($sub_option, $title, $value, $checked, $dapfforwc_styleoptions, 'category', 'category', $singlevaluecataSelect, $count) . '</a>'
            : dapfforwc_render_filter_option($sub_option, $title, $value, $checked, $dapfforwc_styleoptions, 'category', 'category', $singlevaluecataSelect, $count);
    }
}

if ($hierarchical !== 'enable_separate' && !empty($updated_filters["categories"])) {
    $formOutPut .= '</div></div>';
}
?>
<?php
// category ends
    
    
// display attributes
        $formOutPut .= '<div class="filter-group attributes" style="display: ' . (!empty($dapfforwc_options['show_attributes']) ? 'block' : 'none') . ';"><label style="display:none;">Attributes:</label>';
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
                    $formOutPut .= '<div id="' . esc_attr($attribute_name) . '">
                            <div class="title collapsable_' . esc_attr($minimizable) . '">' . esc_html($attribute_name) . 
                            ($minimizable === "arrow" ? '<div class="collaps"><svg class="rotatable" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 448 512" role="graphics-symbol" aria-hidden="false" aria-label=""><path d="M224 416c-8.188 0-16.38-3.125-22.62-9.375l-192-192c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0L224 338.8l169.4-169.4c12.5-12.5 32.75-12.5 45.25 0s12.5 32.75 0 45.25l-192 192C240.4 412.9 232.2 416 224 416z"></path></svg></div>' : '') . 
                            '</div>';
                            
                    if ($sub_optionattr === "select" || $sub_optionattr === "select2" || $sub_optionattr === "select2_classic") {
                        $formOutPut .= '<select name="attribute[' . esc_attr($attribute_name) . '][]" id="' . esc_attr($sub_optionattr) . '" class="items ' . esc_attr($sub_optionattr) . ' filter-select" ' . ($singlevalueattrSelect !== "yes" ? 'multiple="multiple"' : '') . '>';
                        $formOutPut .= '<option class="filter-checkbox" value=""> Any </option>';
                    } else {
                        $formOutPut .= '<div class="items ' . esc_attr($sub_optionattr) . '">';
                    }

                    foreach ($terms as $term) {
                        $checked = in_array($term->slug, $selected_terms) ? ' checked' : '';
                        $count = $show_count === "yes" ? $term->count : 0; // Use term count directly
                        $anchorlink = $use_filters_word === "on" ? "filters/" . esc_attr($term->slug) : esc_attr($term->slug);
                        $formOutPut .= $use_anchor === "on" ? '<a href="' . esc_attr($anchorlink) . '">' . dapfforwc_render_filter_option($sub_optionattr, esc_html($term->name), esc_attr($term->slug), $checked, $dapfforwc_styleoptions, "attribute[$attribute_name]", $attribute_name, $singlevalueattrSelect, $count) . '</a>' : dapfforwc_render_filter_option($sub_optionattr, esc_html($term->name), esc_attr($term->slug), $checked, $dapfforwc_styleoptions, "attribute[$attribute_name]", $attribute_name, $singlevalueattrSelect, $count);
                    }

                    if ($sub_optionattr === "select" || $sub_optionattr === "select2" || $sub_optionattr === "select2_classic") {
                        $formOutPut .= '</select>';
                    } else {
                        $formOutPut .= '</div>';
                    }
                    $formOutPut .= '</div>';
                }
            }
        }
        $formOutPut .= '</div>';
// display tags
        $tags = $updated_filters["tags"];
        if(!empty($tags)){
        $selected_tags = !empty($default_filter) ?  $default_filter : explode(',', $atts['tag']);
        $sub_option = $dapfforwc_styleoptions["tag"]["sub_option"]??""; // Fetch the sub_option value
        $minimizable = $dapfforwc_styleoptions["tag"]["minimize"]["type"]??"";
        $show_count = $dapfforwc_styleoptions["tag"]["show_product_count"]??"";
        $singlevalueSelect = $dapfforwc_styleoptions["tag"]["single_selection"]??"";
        $formOutPut .= '<div id="tags" class="filter-group tags" style="display: ' . (!empty($dapfforwc_options['show_tags']) ? 'block' : 'none') . ';"><div class="title collapsable_'.esc_attr($minimizable).'">Tags '.($minimizable === "arrow" ? '<div class="collaps"><svg class="rotatable" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 448 512" role="graphics-symbol" aria-hidden="false" aria-label=""><path d="M224 416c-8.188 0-16.38-3.125-22.62-9.375l-192-192c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0L224 338.8l169.4-169.4c12.5-12.5 32.75-12.5 45.25 0s12.5 32.75 0 45.25l-192 192C240.4 412.9 232.2 416 224 416z"></path></svg></div>' : '').'</div>';
        if ($sub_option==="select"||$sub_option==="select2"||$sub_option==="select2_classic") {
            $formOutPut .= '<select name="tag[]" id="'.esc_attr($sub_option).'" class="items '.esc_attr($sub_option).' filter-select" '.($singlevalueSelect!=="yes" ? 'multiple="multiple"' : '').'>';
            $formOutPut .= '<option class="filter-checkbox" value=""> Any </option>';
        }else{
            $formOutPut .= '<div class="items '.esc_attr($sub_option).'">';
        }
        if ($tags) {
            foreach ($tags as $tag) {
            $checked = in_array($tag->slug, $selected_tags) ? ' checked' : '';
            $value = esc_attr($tag->slug);
            $title = esc_html($tag->name);
            $count = $show_count==="yes"? $dapfforwc_product_count["tags"][$value]: 0;
            $anchorlink = $use_filters_word ==="on"?"filters/$value":$value;
            $formOutPut .= $use_anchor==="on" ? '<a href="'.esc_attr($anchorlink) .'">'. dapfforwc_render_filter_option($sub_option, $title, $value, $checked, $dapfforwc_styleoptions, "tag", $attribute="tag",$singlevalueSelect,$count).'</a>' :  dapfforwc_render_filter_option($sub_option, $title, $value, $checked, $dapfforwc_styleoptions, "tag", $attribute="tag",$singlevalueSelect,$count);
            }
        }
        if ($sub_option==="select"||$sub_option==="select2"||$sub_option==="select2_classic") {
            $formOutPut .= '</select>';
            }else{ $formOutPut .= '</div>';}
        $formOutPut .= '</div>';
        }
        // tags ends

        return $formOutPut;

} //function ends
?>