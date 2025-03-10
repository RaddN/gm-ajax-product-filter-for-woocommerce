<?php

if (!defined('ABSPATH')) {
    exit;
}

function dapfforwc_product_filter_shortcode($atts)
{
    global $dapfforwc_styleoptions, $post, $dapfforwc_options, $dapfforwc_advance_settings, $dapfforwc_min_max_price, $wp;
    $update_filter_options =  isset($dapfforwc_options["update_filter_options"]) ? $dapfforwc_options["update_filter_options"] : "";
    $use_anchor = isset($dapfforwc_advance_settings["use_anchor"]) ? $dapfforwc_advance_settings["use_anchor"] : "";
    $use_filters_word = isset($dapfforwc_options["use_filters_word_in_permalinks"]) ? $dapfforwc_options["use_filters_word_in_permalinks"] : "";
    $remove_outofStock_product = isset($dapfforwc_advance_settings["remove_outofStock"]) ? $dapfforwc_advance_settings["remove_outofStock"] : "";
    $dapfforwc_slug = isset($post) ? dapfforwc_get_full_slug($post->ID) : "";
    $second_operator = strtoupper($dapfforwc_options["product_show_settings"][$dapfforwc_slug]["operator_second"] ?? "IN");
    $request = $wp->request;
    // Validate and sanitize host
    $host = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : '';

    // Validate and sanitize request URI
    $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';

    // Build the sanitized URL
    if (!empty($host) && !empty($request_uri)) {
        $url_page = esc_url("http://{$host}{$request_uri}");
    } else {
        $url_page = home_url(); // Fallback to homepage if values are missing
    }

    // Parse the URL
    $parsed_url = wp_parse_url($url_page);
    // Parse the query string into an associative array
    if (isset($parsed_url['query'])) {
        parse_str($parsed_url['query'], $query_params);
    }
    // Get the value of 'filters'
    $filters = $query_params['filters'] ?? null;
    $default_filter = array_merge(
        $dapfforwc_options["default_filters"][$dapfforwc_slug] ?? [],
        explode('/', $filters ?? ''),
        explode('/', $request)
    );

    // Define default attributes and merge with user-defined attributes
    $atts = shortcode_atts(array(
        'attribute' => '',
        'terms' => '',
        'category' => '',
        'tag' => '',
        'product_selector' => '',
        'pagination_selector' => '',
        'mobile_responsive' => 'style_1',
        'use_custom_template_design' => 'no'
    ), $atts);

    if (!empty($atts['use_custom_template_design']) && $atts['use_custom_template_design'] === "yes") {
        // Ensure it's an array
        $dapfforwc_options['use_custom_template_in_page'] = isset($dapfforwc_options['use_custom_template_in_page']) ? $dapfforwc_options['use_custom_template_in_page'] : [];

        // Merge and ensure uniqueness
        if (is_string($dapfforwc_slug)) {
            $dapfforwc_options['use_custom_template_in_page'] = array_unique(array_merge($dapfforwc_options['use_custom_template_in_page'], [$dapfforwc_slug]));
        }

        // Update options
        update_option('dapfforwc_options', $dapfforwc_options);
    } else {
        // Remove the slug from the settings if it exists
        if (isset($dapfforwc_options['use_custom_template_in_page']) && is_array($dapfforwc_options['use_custom_template_in_page'])) {
            $dapfforwc_options['use_custom_template_in_page'] = array_values(array_diff($dapfforwc_options['use_custom_template_in_page'], [$dapfforwc_slug]));

            // Update options after removal
            update_option('dapfforwc_options', $dapfforwc_options);
        }
    }

    $formOutPut = "<div class='defaultfilters'>";
    // Get Categories, Tags, attributes using the existing function
    $all_data = dapfforwc_get_woocommerce_attributes_with_terms();
    $all_cata = $all_data['categories'];
    $all_tags = $all_data['tags'];

    // Create Lookup Arrays
    $cata_lookup = array_column($all_cata, 'slug', 'slug');
    $tag_lookup = array_column($all_tags, 'slug', 'slug');

    // Match Filters
    $matched_cata = array_intersect_key($cata_lookup, array_flip($default_filter));
    $matched_tag  = array_intersect_key($tag_lookup, array_flip($default_filter));


    // Match Attributes

    $matched_attributes = [];

    if (!empty($all_data['attributes']) && (is_array($all_data['attributes']) || is_object($all_data['attributes']))) {
        foreach ($all_data['attributes'] as $taxonomy => $lookup) {
            // Ensure 'terms' key exists and is an array
            if (isset($lookup['terms']) && is_array($lookup['terms'])) {
                // Extract the term slugs using array_column
                $term_names = array_column($lookup['terms'], 'slug');

                // Use array_intersect to find matched terms with the default filter
                $matched_attributes[$taxonomy] = array_intersect($term_names, $default_filter);
            }
        }
    }
    // Generate Hidden Inputs for Categories
    if (is_array($matched_cata) || is_object($matched_cata)) {
        foreach ($matched_cata as $value) {
            $formOutPut .= '<input type="checkbox" name="category[]" value="' . htmlspecialchars($value) . '" checked style="display:none;">';
        }
    }
    // Generate Hidden Inputs for Tags
    if (is_array($matched_tag) || is_object($matched_tag)) {
        foreach ($matched_tag as $value) {
            $formOutPut .= '<input type="checkbox" name="tag[]" value="' . htmlspecialchars($value) . '" checked style="display:none;">';
        }
    }
    // Generate Hidden Inputs for Attributes
    if (is_array($matched_attributes) || is_object($matched_attributes)) {
        foreach ($matched_attributes as $taxonomy => $terms) {
            $taxonomy = str_replace('pa_', '', $taxonomy);
            if (is_array($terms) || is_object($terms)) {
                foreach ($terms as $term) {
                    $formOutPut .= '<input type="checkbox" name="attribute[' . $taxonomy . '][]" value="' . htmlspecialchars($term) . '" checked style="display:none;">';
                }
            }
        }
    }
    // End Output
    $formOutPut .= "</div>";

    // Prepare the query arguments based on the provided attributes
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'tax_query' => array('relation' => 'AND'),
        'fields'    => 'ids',
    );

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
            if (!empty($terms)) {
                $args['tax_query'][] = array(
                    'taxonomy' => 'pa_' . $taxonomy,
                    'field' => 'slug',
                    'terms' => $terms,
                    'operator' => $second_operator,
                );
            }
        }
    }
    if ($remove_outofStock_product === "on") {
        $args['meta_query'][] =
            array(
                'key' => '_stock_status',
                'value' => 'instock',
            );
    }

    // Query the products based on the filters
    $products = new WP_Query($args);
    $product_ids = $products->posts;
    $updated_filters = dapfforwc_get_updated_filters($product_ids);
    $min_max_prices = dapfforwc_get_min_max_price($products);
    ob_start(); // Start output buffering
?>
    
    <style>
        .progress-percentage:after {
            content: "<?php echo esc_html($min_max_prices['max']); ?> ";
        }

        <?php if ($atts['mobile_responsive'] === 'style_1') { ?>
        /* responsive filter */
        @media (max-width: 781px) {
            .rfilterbuttons {
                display: none;
            }

            #product-filter .filter-group div .title {
                cursor: pointer !important;
            }

            #product-filter:before {
                content: "Filter";
                background: linear-gradient(90deg, #041a57, #d62229);
                color: white;
                padding: 10px 11px;
                width: 60px;
                height: 45px;
                position: absolute;
                left: 0px;
            }

            form#product-filter {
                display: flex;
                flex-direction: row !important;
                overflow: scroll;
                gap: 10px;
                height: 66px;
                margin-left: 64px;
            }

            .filter-group .title {
                font-size: 16px !important;
            }

            .child-categories {
                display: block !important;
            }

            .filter-group {
                min-width: max-content;
                height: min-content;
            }

            #product-filter .items {
                position: absolute;
                left: 0;
                background: white;
                padding: 20px 15px;
                box-shadow: #efefef99 0 -4px 10px 4px;
                z-index: 999;
            }
        }

        <?php } ?><?php if ($atts['mobile_responsive'] === 'style_2') { ?><?php } ?>
    </style>
    <?php if ($atts['mobile_responsive'] === 'style_3') { ?>

        <style>
            @media (min-width: 781px) {

                #mobileonly,
                #filter-button {
                    display: none !important;
                }
            }

            @media (max-width: 781px) {
                .items {
                    display: block !important;
                }

                .mobile-filter {
                    position: fixed;
                    z-index: 999;
                    background: #ffffff;
                    width: 95%;
                    padding: 30px 20px 300px 20px;
                    height: 100%;
                    overflow: scroll;
                    box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
                    border-radius: 30px;
                    margin: 5px !important;
                    display: none;
                }

                .rfilterselected ul {
                    flex-wrap: nowrap;
                    overflow: scroll;
                }
            }
        </style>
    <?php } ?>
    <?php if ($atts['mobile_responsive'] === 'style_4') { ?>

        <style>
            @media (min-width: 781px) {

                #mobileonly,
                #filter-button {
                    display: none !important;
                }
            }

            @media (max-width: 781px) {
                .items {
                    display: block !important;
                }

                .mobile-filter {
                    position: fixed;
                    z-index: 999;
                    background: #ffffff;
                    width: 80%;
                    height: 100%;
                    overflow: scroll;
                    box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
                    bottom: 0;
                    right: 0;
                    transition: transform 0.3s ease-in-out;
                    transform: translateX(150%);
                }

                .mobile-filter.open {
                    transform: translateX(0%);
                }

                .rfilterselected ul {
                    flex-wrap: nowrap;
                    overflow: scroll;
                }
            }
        </style>
    <?php }

    if ($atts['mobile_responsive'] === 'style_3' ||  $atts['mobile_responsive'] === 'style_4') { ?>
        <button id="filter-button" style="position: fixed; z-index:999;     bottom: 20px;
    right: 20px; background-color: #041a57; color: white; border: none; border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
            <i class="fa fa-filter" aria-hidden="true"></i>
        </button>
        <div class="mobile-filter">
            <div class="sm-top-btn" id="mobileonly" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ccc; padding: 20px;margin-bottom: 10px;">
                <button id="filter-cancel-button" style="background: none;padding:0;color: #000;"> Cancel </button>
                <p style="margin: 0;" id="rcountproduct">Show(5)</p>
            </div>
        <?php
        echo '<div class="rfilterselected" id="mobileonly"><div><ul></ul></div></div>';
    }
    if ($atts['mobile_responsive'] === 'style_3') { ?>
            <script>
                jQuery(document).ready(function($) {
                    function isMobile() {
                        return $(window).width() < 768; // Adjust the width as needed
                    }

                    if (isMobile()) {
                        $('#filter-cancel-button').on('click', function(event) {
                            event.preventDefault();
                            $('.mobile-filter').slideUp();
                        });

                        $('#filter-button').on('click', function(event) {
                            event.preventDefault();
                            $('.mobile-filter').slideDown();
                        });

                        $(document).on('click', function(event) {
                            if (!$(event.target).closest('.mobile-filter, #filter-button').length) {
                                $('.mobile-filter').slideUp();
                            }
                        });
                    }
                });
            </script>
        <?php }

    if ($atts['mobile_responsive'] === 'style_4') { ?>
            <script>
                jQuery(document).ready(function($) {
                    function isMobile() {
                        return $(window).width() < 768; // Adjust the width as needed
                    }

                    if (isMobile()) {
                        $('#filter-button').on('click', function(event) {
                            event.preventDefault();
                            $('.mobile-filter').toggleClass('open');
                        });

                        $('#filter-cancel-button').on('click', function(event) {
                            event.preventDefault();
                            $('.mobile-filter').removeClass('open');
                        });

                        $(document).on('click', function(event) {
                            if (!$(event.target).closest('.mobile-filter, #filter-button').length) {
                                $('.mobile-filter').removeClass('open');
                            }
                        });
                    }
                });
            </script>
        <?php } ?>
        <form id="product-filter" method="POST"
            <?php if (!empty($atts['product_selector'])) {
                echo 'data-product_selector="' . esc_attr($atts["product_selector"]) . '"';
            } ?>
            <?php if (!empty($atts['pagination_selector'])) {
                echo 'data-pagination_selector="' . esc_attr($atts["pagination_selector"]) . '"';
            } ?>>
            <?php
            wp_nonce_field('gm-product-filter-action', 'gm-product-filter-nonce');
            echo $formOutPut;
            echo dapfforwc_filter_form($updated_filters, $default_filter, $use_anchor, $use_filters_word, $atts, $min_price = $dapfforwc_styleoptions["price"]["min_price"] ?? $min_max_prices['min'], $max_price = $dapfforwc_styleoptions["price"]["max_price"] ?? $min_max_prices['max'] + 1);
            echo '</form>';
            if ($atts['mobile_responsive'] === 'style_3' || $atts['mobile_responsive'] === 'style_4') { ?>
        </div>
    <?php }
    ?>

    <!-- Loader HTML -->
    <?php echo $dapfforwc_options["loader_html"] ?>
    <style>
        <?php echo $dapfforwc_options["loader_css"] ?>
    </style>
    <?php
    if (isset($dapfforwc_options["loader_html"])) {
        echo $dapfforwc_options["loader_html"];
    }

    if (isset($dapfforwc_options["loader_css"])) {
        echo '<style>' . $dapfforwc_options["loader_css"] . '</style>';
    }
    ?>
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
function dapfforwc_customSort($a, $b)
{
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
function dapfforwc_render_filter_option($sub_option, $title, $value, $checked, $dapfforwc_styleoptions, $name, $attribute, $singlevalueSelect, $count, $min_price = 0, $max_price = 10000)
{
    $output = '';
    $min_max_prices = dapfforwc_get_min_max_price();
    switch ($sub_option) {
        case 'checkbox':
            $output .= '<label><input type="' . ($singlevalueSelect === "yes" ? 'radio' : 'checkbox') . '" class="filter-checkbox" name="' . $name . '[]" value="' . $value . '"' . $checked . '> ' . $title . ($count != 0 ? ' (' . $count . ')' : '') . '</label>';
            break;

        case 'radio_check':
            $output .= '<label><input type="' . ($singlevalueSelect === "yes" ? 'radio' : 'checkbox') . '" class="filter-radio-check" name="' . $name . '[]" value="' . $value . '"' . $checked . '> ' . $title . ($count != 0 ? ' (' . $count . ')' : '') . '</label>';
            break;

        case 'radio':
            $output .= '<label><input type="' . ($singlevalueSelect === "yes" ? 'radio' : 'checkbox') . '" class="filter-radio" name="' . $name . '[]" value="' . $value . '"' . $checked . '> ' . $title . ($count != 0 ? ' (' . $count . ')' : '') . '</label>';
            break;

        case 'square_check':
            $output .= '<label class="square-option"><input type="' . ($singlevalueSelect === "yes" ? 'radio' : 'checkbox') . '" class="filter-square-check" name="' . $name . '[]" value="' . $value . '"' . $checked . '> <span>' . $title . ($count != 0 ? ' (' . $count . ')' : '') . '</span></label>';
            break;

        case 'square':
            $output .= '<label class="square-option"><input type="' . ($singlevalueSelect === "yes" ? 'radio' : 'checkbox') . '" class="filter-square" name="' . $name . '[]" value="' . $value . '"' . $checked . '> <span>' . $title . ($count != 0 ? ' (' . $count . ')' : '') . '</span></label>';
            break;

        case 'checkbox_hide':
            $output .= '<label><input type="' . ($singlevalueSelect === "yes" ? 'radio' : 'checkbox') . '" class="filter-checkbox" name="' . $name . '[]" value="' . $value . '"' . $checked . ' style="display:none;"> <span>' . $title . ($count != 0 ? ' (' . $count . ')' : '') . '</span></label>';
            break;

        case 'color':
        case 'color_no_border':
        case 'color_circle':
        case 'color_value':
            $color = $dapfforwc_styleoptions[$attribute]['colors'][$value] ?? '#000'; // Default color
            $border = ($sub_option === 'color_no_border') ? 'none' : '1px solid #000';
            $value_show = ($sub_option === 'color_value') ? 'block' : 'none';
            $output .= '<label style="position: relative;"><input type="' . ($singlevalueSelect === "yes" ? 'radio' : 'checkbox') . '" class="filter-color" name="' . $name . '[]" value="' . $value . '"' . $checked . '>
                <span class="color-box" style="background-color: ' . $color . '; border: ' . $border . '; width: 30px; height: 30px;"></span><span style="display:' . $value_show . ';">' . $value . '<span></label>';
            break;

        case 'image':
        case 'image_no_border':
            $image = $dapfforwc_styleoptions[$attribute]['images'][$value] ?? 'default-image.jpg'; // Default image
            $border_class = ($sub_option === 'image_no_border') ? 'no-border' : '';
            $output .= '<label class="image-option ' . $border_class . '">
                <input type="' . ($singlevalueSelect === "yes" ? 'radio' : 'checkbox') . '" class="filter-image" name="' . $name . '[]" value="' . $value . '"' . $checked . '>
                <img src="' . esc_url($image) . '" alt="' . esc_attr($title) . '" /></label>';
            break;

        case 'select2':
        case 'select2_classic':
        case 'select':
            $output .= '<option class="filter-option" value="' . $value . '"' . ($checked ? 'selected' : '') . '> ' . $title . ($count != 0 ? ' (' . $count . ')' : '') . '</option>';
            break;
        case 'input-price-range':
            $default_min_price = $dapfforwc_styleoptions["price"]["min_price"] ?? $min_max_prices['min'];
            $default_max_price = $dapfforwc_styleoptions["price"]["max_price"] ?? $min_max_prices['max'] + 1;
            $output .= '<div class="range-input"><label for="min-price">Min Price:</label>
        <input type="number" id="min-price" name="min_price" min="' . $default_min_price . '" step="1" placeholder="Min" value="' . $min_price . '" style="position: relative; height: max-content; top: unset; pointer-events: all;">
        
        <label for="max-price">Max Price:</label>
        <input type="number" id="max-price" name="max_price" min="' . $default_min_price . '" step="1" placeholder="Max" value="' . $max_price . '" style="position: relative; height: max-content; top: unset; pointer-events: all;"></div>';
            break;
        case 'slider':
            $default_min_price = $dapfforwc_styleoptions["price"]["min_price"] ?? $min_max_prices['min'];
            $default_max_price = $dapfforwc_styleoptions["price"]["max_price"] ?? $min_max_prices['max'] + 1;
            $output .= '<div class="price-input">
        <div class="field">
          <span>Min</span>
          <input type="number" id="min-price" name="min_price" class="input-min" min="' . $default_min_price . '" value="' . $min_price . '">
        </div>
        <div class="separator">-</div>
        <div class="field">
          <span>Max</span>
          <input type="number" id="max-price" name="max_price" min="' . $default_min_price . '" class="input-max" value="' . $max_price . '">
        </div>
      </div>
      <div class="slider">
        <div class="progress"></div>
      </div>
      <div class="range-input">
        <input type="range" id="price-range-min" class="range-min" min="' . $default_min_price . '" max="' . $default_max_price . '" value="' . $min_price . '" >
        <input type="range" id="price-range-max" class="range-max" min="' . $default_min_price . '" max="' . $default_max_price . '" value="' . $max_price . '">
      </div>';
            break;
        case 'price':
            $default_min_price = $dapfforwc_styleoptions["price"]["min_price"] ?? $min_max_prices['min'];
            $default_max_price = $dapfforwc_styleoptions["price"]["max_price"] ?? $min_max_prices['max'] + 1;
            $output .= '<div class="price-input" style="visibility: hidden; margin: 0;">
        <div class="field">
            <input type="number" id="min-price" name="min_price" class="input-min" min="' . $default_min_price . '" value="' . $min_price . '">
        </div>
        <div class="separator">-</div>
        <div class="field">
            <input type="number" id="max-price" name="max_price" min="' . $default_min_price . '" class="input-max" value="' . $max_price . '">
        </div>
        </div>
        <div class="slider">
        <div class="progress progress-percentage"></div>
        </div>
        <div class="range-input">
        <input type="range" id="price-range-min" class="range-min" min="' . $default_min_price . '" max="' . $default_max_price . '" value="' . $min_price . '">
        <input type="range" id="price-range-max" class="range-max" min="' . $default_min_price . '" max="' . $default_max_price . '" value="' . $max_price . '">
        </div>';
            break;
        case 'rating-text':
            $output .= '<label><input type="checkbox" name="rating[]" value="5" ' . (in_array("5", $checked) ? ' checked' : '') . '> 5 Stars 
    </label>
        <label><input type="checkbox" name="rating[]" value="4" ' . (in_array("4", $checked) ? ' checked' : '') . '> 4 Stars & Up</label>
        <label><input type="checkbox" name="rating[]" value="3" ' . (in_array("3", $checked) ? ' checked' : '') . '> 3 Stars & Up</label>
        <label><input type="checkbox" name="rating[]" value="2" ' . (in_array("2", $checked) ? ' checked' : '') . '> 2 Stars & Up</label>
        <label><input type="checkbox" name="rating[]" value="1" ' . (in_array("1", $checked) ? ' checked' : '') . '> 1 Star & Up</label>';
            break;
        case 'rating':
            for ($i = 5; $i >= 1; $i--) {
                $output .= '<label>';
                $output .= '<input type="checkbox" name="rating[]" value="' . esc_attr($i) . '" ' . (in_array($i, $checked) ? ' checked' : '') . '>';
                $output .= '<span class="stars">';
                for ($j = 1; $j <= $i; $j++) {
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
            $output .= '<label><input type="checkbox" class="filter-checkbox" name="' . $name . '[]" value="' . $value . '"' . $checked . '> ' . $title . ($count != 0 ? ' (' . $count . ')' : '') . '</label>';
            break;
    }

    return $output;
}
// Function to get child categories from $updated_filters["categories"]
function dapfforwc_get_child_categories($categories, $parent_id)
{
    $child_categories = array();

    foreach ($categories as $category) {
        // Check if the category is a WP_Term object
        if ($category instanceof WP_Term) {
            if ($category->parent == $parent_id) {
                $child_categories[] = $category;
            }
        }
        // Check if the category is a stdClass object
        elseif (is_object($category) && $category instanceof stdClass) {
            if (isset($category->parent) && $category->parent == $parent_id) {
                $child_categories[] = $category;
            }
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
        if (is_object($category)) {
            $value = esc_attr($category->slug);
            $title = esc_html($category->name);
        } elseif (is_array($category)) {
            $value = esc_attr($category['slug']);
            $title = esc_html($category['name']);
        } else {
            // Handle cases where $category is neither an object nor an array
            $value = '';
            $title = '';
        }
        $count = $show_count === 'yes' ? (is_object($category) ? $category->count : $category["count"]) : 0;
        $checked = in_array($category->slug, $selected_categories) ? ($sub_option === 'select' || str_contains($sub_option, 'select2') ? ' selected' : ' checked') : '';
        $anchorlink = $use_filters_word === 'on' ? "filters/$value" : $value;

        // Fetch child categories
        $child_categories = dapfforwc_get_child_categories($child_category, $category->term_id);

        // Render current category
        $categoryHierarchyOutput .= $use_anchor === 'on'
            ? '<a href="' . esc_attr($anchorlink) . '" style="display:flex;align-items: center;">'
            . dapfforwc_render_filter_option($sub_option, $title, $value, $checked, $dapfforwc_styleoptions, 'category', 'category', $singlevaluecataSelect, $count)
            . (!empty($child_categories) && $hierarchical === 'enable_hide_child' ? '<span class="show-sub-cata">+</span>' : '')
            . '</a>'
            : '<a style="display:flex;align-items: center;text-decoration: none;">'
            . dapfforwc_render_filter_option($sub_option, $title, $value, $checked, $dapfforwc_styleoptions, 'category', 'category', $singlevaluecataSelect, $count) . (!empty($child_categories) && $hierarchical === 'enable_hide_child' ? '<span class="show-sub-cata" style="cursor:pointer;">+</span>' : '')
            . '</a>';

        // Render child categories
        if (!empty($child_categories)) {
            $categoryHierarchyOutput .= '<div class="child-categories" style="display:' . ($hierarchical === 'enable_hide_child' ? 'none;' : 'block;') . '">';
            $categoryHierarchyOutput .= dapfforwc_render_category_hierarchy($child_categories, $selected_categories, $sub_option, $dapfforwc_styleoptions, $singlevaluecataSelect, $show_count, $use_anchor, $use_filters_word, $hierarchical, $child_category);
            $categoryHierarchyOutput .= '</div>';
        }
    }
    return $categoryHierarchyOutput;
}

function dapfforwc_product_filter_shortcode_single($atts)
{
    $atts = shortcode_atts(
        array(
            'name' => '', // Default attribute name
        ),
        $atts,
        'get_terms_by_attribute'
    );

    // Check if the name is provided
    if (empty($atts['name'])) {
        return '<p style="background:red;background: red;text-align: center;color: #fff;">Please provide an attribute slug.</p>';
    }

    // Generate the output
    $output = '<form class="rfilterbuttons" id="' . $atts['name'] . '"><ul>';
    $output .= '<li>
                <input id="selected_category_1" type="checkbox" value="category_1" checked="">
                <label for="selected_category_1">Category 1</label>
            </li>
           <li>
                <input id="selected_category_2" type="checkbox" value="category_2" checked="">
                <label for="selected_category_2">Category 2</label>
            </li>
            <li class="checked">
                <input id="selected_category_3" type="checkbox" value="category_3" checked="">
                <label for="selected_category_3">Category 3</label>
            </li>';
    $output .= '</ul></form>';

    return $output;
}
add_shortcode('plugincy_filters_single', 'dapfforwc_product_filter_shortcode_single');

function dapfforwc_product_filter_shortcode_selected()
{

    // Generate the output
    $output = '<form class="rfilterselected"><div><ul>';
    $output .= '<li class="checked">
                <input id="selected_category_1" type="checkbox" value="category_1" checked="">
                <label for="selected_category_1">Category 1</label>
                <label style="font-size:12px;margin-left:5px;">x</label>
            </li>
           <li class="checked">
                <input id="selected_category_2" type="checkbox" value="category_2" checked="">
                <label for="selected_category_2">Category 2</label>
                <label style="font-size:12px;margin-left:5px;">x</label>
            </li>
            <li class="checked">
                <input id="selected_category_3" type="checkbox" value="category_3" checked="">
                <label for="selected_category_3">Category 3</label>
                <label style="font-size:12px;margin-left:5px;">x</label>
            </li>';
    $output .= '</ul></div></form>';

    return $output;
}
add_shortcode('plugincy_filters_selected', 'dapfforwc_product_filter_shortcode_selected');


// function dapfforwc_get_updated_filters($query) {
//     // Get the current product IDs based on the filtered query
//     $product_ids = wp_list_pluck($query->posts, 'ID');
//     // Initialize arrays to store filter data
//     $categories = array();
//     $attributes = array();
//     $tags = array();

//     if (!empty($product_ids)) {
//         // Get categories for the filtered products
//         $categories = wp_get_object_terms($product_ids, 'product_cat', array('fields' => 'all'));
//         foreach ($categories as $category) {
//             $category->count = count(array_filter($query->posts, function($product) use ($category) {
//                 return has_term($category->term_id, 'product_cat', $product->ID);
//             }));
//         }

//         // Get attributes for the filtered products
//         $attributes_taxonomies = wc_get_attribute_taxonomies();
//         if ($attributes_taxonomies) {
//             foreach ($attributes_taxonomies as $attribute) {
//                 $attribute_terms = wp_get_object_terms($product_ids, 'pa_' . $attribute->attribute_name, array('fields' => 'all'));
//                 if (!empty($attribute_terms)) {
//                     foreach ($attribute_terms as $term) {
//                         $term->count = count(array_filter($query->posts, function($product) use ($term, $attribute) {
//                             return has_term($term->term_id, 'pa_' . $attribute->attribute_name, $product->ID);
//                         }));
//                         // Add the attribute label to the term
//                         $term->attribute_label = $attribute->attribute_label; // Add this line
//                     }
//                     $attributes[$attribute->attribute_name] = $attribute_terms;
//                 }
//             }
//         }

//         // Get tags for the filtered products
//         $tags = wp_get_object_terms($product_ids, 'product_tag', array('fields' => 'all'));
//         foreach ($tags as $tag) {
//             $tag->count = count(array_filter($query->posts, function($product) use ($tag) {
//                 return has_term($tag->term_id, 'product_tag', $product->ID);
//             }));
//         }
//     }

//     $data = array(
//         'categories' => $categories,
//         'attributes' => $attributes,
//         'tags' => $tags
//     );

//     return $data;
// }

function dapfforwc_get_updated_filters($product_ids)
{
    $categories = [];
    $attributes = [];
    $tags = [];

    if (!empty($product_ids)) {
        // Get attributes with terms
        $all_data = dapfforwc_get_woocommerce_attributes_with_terms();

        // Extract categories and tags from all_data
        // Categories
        if (is_array($all_data['categories']) || is_object($all_data['categories'])) {
            foreach ($all_data['categories'] as $term_id => $category) {
                if (!empty(array_intersect($product_ids, $category['products']))) {
                    $categories[$term_id] = (object) [
                        'term_id' => $term_id,
                        'name'    => $category['name'],
                        'slug'    => $category['slug'],
                        'parent'  => $category['parent'],
                        'taxonomy' => 'product_cat',
                        'count'   => count(array_intersect($category['products'], $product_ids)),
                    ];
                }
            }
        }

        // Tags
        if (is_array($all_data['tags']) || is_object($all_data['tags'])) {
            foreach ($all_data['tags'] as $term_id => $tag) {
                if (!empty(array_intersect($product_ids, $tag['products']))) {
                    $tags[$term_id] = (object) [
                        'term_id' => $term_id,
                        'name'    => $tag['name'],
                        'slug'    => $tag['slug'],
                        'taxonomy' => 'product_tag',
                        'count'   => count(array_intersect($tag['products'], $product_ids)),
                    ];
                }
            }
        }

        // Extract attributes
        if (is_array($all_data['attributes']) || is_object($all_data['attributes'])) {
            foreach ($all_data['attributes'] as $attribute) {
                $attribute_name = $attribute['attribute_name'];
                $terms = $attribute['terms'];

                if (is_array($terms) || is_object($terms)) {
                    foreach ($terms as $term) {
                        // Check if the term's products match the provided product IDs
                        if (!empty(array_intersect($product_ids, $term['products']))) {
                            $attributes[$attribute_name][] = [
                                'term_id' => $term['term_id'],
                                'attribute_label' => $term['name'],
                                'name'    => $term['name'],
                                'slug'    => $term['slug'],
                                'count'   => count(array_intersect($term['products'], $product_ids)),
                            ];
                        }
                    }
                }
            }
        }
    }

    return [
        'categories' => array_values($categories), // Return as array
        'attributes' => $attributes,
        'tags' => array_values($tags), // Return as array
    ];
}


function dapfforwc_get_woocommerce_attributes_with_terms()
{
    global $wpdb;

    $cache_file = __DIR__ . '/woocommerce_attributes_cache.json'; // Path to cache file
    $cache_time = 43200; // 12 hours in seconds

    // Check if the cache file exists and is still valid
    if (file_exists($cache_file) && (filemtime($cache_file) > (time() - $cache_time))) {
        // Read the cached data
        return json_decode(file_get_contents($cache_file), true);
    }

    $data = ['attributes' => [], 'categories' => [], 'tags' => []];

    // Fetch attributes, categories, tags, and associated product IDs in a single query

    $query = "
    SELECT t.term_id, t.name, t.slug, tr.object_id, tt.taxonomy, a.attribute_name, a.attribute_label, tt.parent
    FROM {$wpdb->prefix}terms AS t
    INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON t.term_id = tt.term_id
    LEFT JOIN {$wpdb->prefix}term_relationships AS tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
    LEFT JOIN {$wpdb->prefix}woocommerce_attribute_taxonomies AS a ON tt.taxonomy = CONCAT('pa_', a.attribute_name)
    WHERE tt.taxonomy IN ('product_cat', 'product_tag') OR a.attribute_name IS NOT NULL
    ORDER BY tt.taxonomy, t.name;
";

    // Prepare the query with placeholders
    $prepared_query = $wpdb->prepare($query, 'product_cat', 'product_tag');

    $results = $wpdb->get_results($prepared_query, ARRAY_A);

    if (is_array($results) || is_object($results)) {
        foreach ($results as $row) {
            $term_id = $row['term_id'];
            $taxonomy = $row['taxonomy'];

            if ($taxonomy === 'product_cat') {
                if (!isset($data['categories'][$term_id])) {
                    $data['categories'][$term_id] = [
                        'name' => $row['name'],
                        'slug' => $row['slug'],
                        'parent' => $row['parent'],
                        'products' => []
                    ];
                }
                if ($row['object_id']) {
                    $data['categories'][$term_id]['products'][] = $row['object_id'];
                }
            } elseif ($taxonomy === 'product_tag') {
                if (!isset($data['tags'][$term_id])) {
                    $data['tags'][$term_id] = [
                        'name' => $row['name'],
                        'slug' => $row['slug'],
                        'products' => []
                    ];
                }
                if ($row['object_id']) {
                    $data['tags'][$term_id]['products'][] = $row['object_id'];
                }
            } elseif (!empty($row['attribute_name'])) {
                $attr_name = $row['attribute_name'];

                if (!isset($data['attributes'][$attr_name])) {
                    $data['attributes'][$attr_name] = [
                        'attribute_label' => $row['attribute_label'],
                        'attribute_name' => $attr_name,
                        'terms' => []
                    ];
                }

                // Check if the term already exists
                $term_key = array_search($term_id, array_column($data['attributes'][$attr_name]['terms'], 'term_id'));

                if ($term_key === false) {
                    $data['attributes'][$attr_name]['terms'][] = [
                        'term_id'    => $term_id,
                        'name'       => $row['name'],
                        'slug'       => $row['slug'],
                        'products'   => $row['object_id'] ? [$row['object_id']] : [],
                    ];
                } else {
                    if ($row['object_id']) {
                        $data['attributes'][$attr_name]['terms'][$term_key]['products'][] = $row['object_id'];
                    }
                }
            }
        }
    }

    // Write the data to the cache file
    file_put_contents($cache_file, json_encode($data));

    return $data;
}


// Clear cache when a term is updated
add_action('edited_term', function ($term_id) {
    $cache_file = __DIR__ . '/woocommerce_attributes_cache.json';
    if (file_exists($cache_file)) {
        wp_delete_file($cache_file);
    }
});

// Clear cache when a product is updated
add_action('save_post_product', function ($post_id) {
    $cache_file = __DIR__ . '/woocommerce_attributes_cache.json';
    if (file_exists($cache_file)) {
        wp_delete_file($cache_file);
    }
});
