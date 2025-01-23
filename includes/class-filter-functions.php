<?php
if (!defined('ABSPATH')) {
    exit;
}
class dapfforwc_Filter_Functions {

    public function process_filter() {
        global $dapfforwc_options,$dapfforwc_styleoptions,$dapfforwc_advance_settings;
        $update_filter_options = $dapfforwc_options["update_filter_options"]??"";
        $remove_outofStock_product = $dapfforwc_advance_settings["remove_outofStock"] ?? ""; 

        if (!isset($_POST['gm-product-filter-nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['gm-product-filter-nonce'])), 'gm-product-filter-action')) {
            wp_send_json_error(array('message' => 'Security check failed'), 403);
            wp_die();
        }
            // Determine the current page number
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $orderbyFormUser = isset($_POST['orderby']) && $_POST['orderby'] !== "undefined" ? sanitize_text_field(wp_unslash($_POST['orderby'])) : "";
    $currentpage_slug = isset($_POST['current-page']) ? sanitize_text_field(wp_unslash($_POST['current-page'])) : "";
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => isset($dapfforwc_options["product_show_settings"][$currentpage_slug]["per_page"]) ? 
            intval($dapfforwc_options["product_show_settings"][$currentpage_slug]["per_page"]) : 12,
            'post_status' => 'publish',
            'orderby' => $orderbyFormUser!=="" ? $orderbyFormUser : $dapfforwc_options["product_show_settings"][$currentpage_slug]["orderby"] ?? 'date',
            'order' => isset($dapfforwc_options["product_show_settings"][$currentpage_slug]["order"])?strtoupper($dapfforwc_options["product_show_settings"][$currentpage_slug]["order"]) : 'ASC',
            'paged' => $paged,
            'tax_query' => array(
                'relation' => 'AND'
            )
        );
        $args_options = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'tax_query' => array(
                'relation' => 'AND'
            )
        );
        if ($remove_outofStock_product==="on") {
            $args['meta_query'][] =
                array(
                    'key' => '_stock_status',
                    'value' => 'instock',
                );
            $args_options['meta_query'][] =
            array(
                'key' => '_stock_status',
                'value' => 'instock',
            );
        }
        $second_operator = isset($dapfforwc_options["product_show_settings"]["upcoming-conferences"]["operator_second"])?strtoupper($dapfforwc_options["product_show_settings"]["upcoming-conferences"]["operator_second"]) : "IN";

        $args = $this->apply_filters_to_args($args,$second_operator);

        $args_options = $this->apply_filters_to_args($args_options ,$second_operator);

        $filter_options = new WP_Query($args_options);
        $count_total_showing_product = $filter_options->post_count;
        $query = new WP_Query($args);

        

        $updated_filters = dapfforwc_get_updated_filters($filter_options);
        $default_filter = [];
        $min_max_prices = dapfforwc_get_min_max_price();

        // Check if 'selectedvalues' is set and not empty
        if (!empty($_POST['selectedvalues'])) {
            // Convert the string to an array
            $default_filter = array_map('sanitize_text_field', explode(',', wp_unslash($_POST['selectedvalues'])));
        }
        
        $filterform = dapfforwc_filter_form($updated_filters,$default_filter,"","","",$min_price=floatval($_POST['min_price']) ?? $dapfforwc_styleoptions["price"]["min_price"] ?? $min_max_prices['min'],$max_price=floatval($_POST['max_price']) ?? $dapfforwc_styleoptions["price"]["max_price"] ?? $min_max_prices['max']);
        // Capture the product listing
        ob_start();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) : $query->the_post();
            $this->display_product($query->post);
            endwhile;
         // Add pagination links
        $this->pagination($query,$paged);
        } else {
            echo '<div style="display: flex ; flex-direction: column; align-items: center; gap: 10px;">
            <p style="margin-top: 20px; font-size: 24px; color: #212121;">No products found</p>
            <p>We\'re sorry. We cannot find any matches for your search term.</p>
            <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100" viewBox="0 0 50 50">
            <path d="M 21 3 C 11.621094 3 4 10.621094 4 20 C 4 29.378906 11.621094 37 21 37 C 24.710938 37 28.140625 35.804688 30.9375 33.78125 L 44.09375 46.90625 L 46.90625 44.09375 L 33.90625 31.0625 C 36.460938 28.085938 38 24.222656 38 20 C 38 10.621094 30.378906 3 21 3 Z M 21 5 C 29.296875 5 36 11.703125 36 20 C 36 28.296875 29.296875 35 21 35 C 12.703125 35 6 28.296875 6 20 C 6 11.703125 12.703125 5 21 5 Z"></path>
            </svg>
            </div>';
        }

        $product_html = ob_get_clean();

        // Send both the filtered products and updated filters back to the AJAX request
        wp_send_json_success(array(
            'products' => $product_html,
            'total_product_fetch' => $count_total_showing_product,
            'pagination' => $this->pagination($query,$paged),
            'filter_options' => $filterform
        ));

        wp_die();
    }
    private function apply_filters_to_args($args,$second_operator) {
        if (!isset($_POST['gm-product-filter-nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['gm-product-filter-nonce'])), 'gm-product-filter-action')) {
            wp_send_json_error(array('message' => 'Security check failed'), 403);
            wp_die();
        }
    
        // Minimum Price Filter
        if (isset($_POST['min_price']) && $_POST['min_price'] !== '') {
            $args['meta_query'][] = array(
                'key' => '_price',
                'value' => floatval($_POST['min_price']),
                'compare' => '>=',
                'type' => 'NUMERIC',
            );
        }
    
        // Maximum Price Filter
        if (isset($_POST['max_price']) && $_POST['max_price'] !== '') {
            $args['meta_query'][] = array(
                'key' => '_price',
                'value' => floatval($_POST['max_price']),
                'compare' => '<=',
                'type' => 'NUMERIC',
            );
        }
    
        // Rating Filter
        if (isset($_POST['rating']) && !empty($_POST['rating'])) {
            $ratings = array_map('intval', $_POST['rating']);
            $args['meta_query'][] = array(
                'key'     => '_wc_average_rating',
                'value'   => min($ratings),  // Minimum rating selected
                'compare' => '>=',
                'type'    => 'DECIMAL(2,1)',
            );
        }
    
        // Category Filter
        if (!empty($_POST['category'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => array_map('sanitize_text_field', wp_unslash($_POST['category'])),
                'operator' => $second_operator,
            );
        }
    
        // Attribute Filters
        if (!empty($_POST['attribute']) && is_array($_POST['attribute'])) {
            $attributes = map_deep(wp_unslash($_POST['attribute']), 'sanitize_text_field');
            foreach ($attributes as $attribute_name => $attribute_values) {
                if (!empty($attribute_values) && is_array($attribute_values)) {
                    $sanitized_values = array_map('sanitize_text_field', $attribute_values);
                    $args['tax_query'][] = array(
                        'taxonomy' => 'pa_' . sanitize_key($attribute_name),
                        'field' => 'slug',
                        'terms' => $sanitized_values,
                        'operator' => $second_operator,
                    );
                }
            }
        }
    
        // Tag Filter
        if (!empty($_POST['tag'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_tag',
                'field' => 'slug',
                'terms' => array_map('sanitize_text_field', wp_unslash($_POST['tag'])),
                'operator' => $second_operator,
            );
        }
        if (!empty($_POST['s'])) {
            $search_term = sanitize_text_field(wp_unslash($_POST['s']));
            $args['s'] = $search_term; // Add the search term to the main query args
        }
    
        return $args;
    }
    private function display_product($post) {
        global $dapfforwc_options;
        $product = wc_get_product($post->ID);
        if(isset($dapfforwc_options['use_custom_template']) && $dapfforwc_options['use_custom_template']==="on"){
        // Get product details
        $product_link = get_permalink();
        $product_title = get_the_title(); 
        $product_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full')[0];
        $product_excerpt = get_the_excerpt();
        $product_price = $product->get_price_html(); 
        $product_category = wp_strip_all_tags(get_the_term_list(get_the_ID(), 'product_cat', '', ', ')); 
        $product_sku = $product->get_sku(); 
        $product_stock = $product->is_in_stock() ? __('In Stock', 'dynamic-ajax-product-filters-for-woocommerce') : __('Out of Stock', 'dynamic-ajax-product-filters-for-woocommerce'); 
        $add_to_cart_url = esc_url(add_query_arg('add-to-cart', get_the_ID(), $product_link)); 

            // Retrieve the custom template from the database
            $custom_template = $dapfforwc_options['custom_template_code'];
            
            // Replace placeholders with actual values
            $custom_template = str_replace('{{product_link}}', esc_url($product_link), $custom_template);
            $custom_template = str_replace('{{product_title}}', esc_html($product_title), $custom_template);
            $custom_template = str_replace('{{product_image}}', esc_url($product_image), $custom_template);
            $custom_template = str_replace('{{product_excerpt}}', apply_filters('the_excerpt', $product_excerpt), $custom_template);
            $custom_template = str_replace('{{product_price}}', wp_kses_post($product_price), $custom_template);
            $custom_template = str_replace('{{product_category}}', $product_category, $custom_template);
            $custom_template = str_replace('{{product_sku}}', esc_html($product_sku), $custom_template);
            $custom_template = str_replace('{{product_stock}}', esc_html($product_stock), $custom_template);
            $custom_template = str_replace('{{add_to_cart_url}}', $add_to_cart_url, $custom_template);
            $custom_template = str_replace('{{product_id}}', esc_html($post->ID), $custom_template);
            $allowed_tags = array(
                'a' => array(
                    'href' => array(),
                    'title' => array(),
                    'class' => array(),
                    'target' => array(), // Allow target attribute for links
                ),
                'strong' => array(),
                'em' => array(),
                'li' => array(
                    'class' => array(),
                ),
                'div' => array(
                    'class' => array(),
                    'id' => array(), // Allow id for divs
                ),
                'img' => array(
                    'src' => array(),
                    'alt' => array(),
                    'class' => array(),
                    'width' => array(), // Allow width attribute
                    'height' => array(), // Allow height attribute
                ),
                'h1' => array('class' => array()), // Allow h1
                'h2' => array('class' => array()),
                'h3' => array('class' => array()), // Allow h3
                'h4' => array('class' => array()), // Allow h4
                'h5' => array('class' => array()), // Allow h5
                'h6' => array('class' => array()), // Allow h6
                'span' => array('class' => array()),
                'p' => array('class' => array()),
                'br' => array(), // Allow line breaks
                'blockquote' => array(
                    'cite' => array(), // Allow cite attribute for blockquotes
                    'class' => array(),
                ),
                'table' => array(
                    'class' => array(),
                    'style' => array(), // Allow inline styles
                ),
                'tr' => array(
                    'class' => array(),
                ),
                'td' => array(
                    'class' => array(),
                    'colspan' => array(), // Allow colspan attribute
                    'rowspan' => array(), // Allow rowspan attribute
                ),
                'th' => array(
                    'class' => array(),
                    'colspan' => array(),
                    'rowspan' => array(),
                ),
                'ul' => array('class' => array()), // Allow unordered lists
                'ol' => array('class' => array()), // Allow ordered lists
                'script' => array(), // Be cautious with scripts
            );
            
            echo wp_kses(do_shortcode($custom_template), $allowed_tags);
            } else {
                wc_get_template_part('content', 'product');
            }
    }
    // Function to generate pagination
    private function pagination($query,$paged) {
        $big = 999999999; // an unlikely integer
        $paginationLinks = paginate_links(array(
            'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format' => '?paged=%#%',
            'current' => max(1, $paged),
            'total' => $query->max_num_pages,
            'prev_text' => __('« Prev','dynamic-ajax-product-filters-for-woocommerce'),
            'next_text' => __('Next »', 'dynamic-ajax-product-filters-for-woocommerce'),
            'type' => 'array', // This returns an array of pagination links
        ));
    
        if ($paginationLinks) {
            // Start building the pagination HTML
            $paginationHtml = '';
            foreach ($paginationLinks as $link) {
                // Wrap each link in an <a> tag
                $paginationHtml .= '<li>' . $link . '</li>';
            }
            return $paginationHtml; // Return the constructed HTML
        }
        return '';
    }
}
