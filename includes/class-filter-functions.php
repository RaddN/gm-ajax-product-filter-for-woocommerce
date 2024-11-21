<?php
class WCAPF_Filter_Functions {

    public function process_filter() {

        if (!isset($_POST['gm-product-filter-nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['gm-product-filter-nonce'])), 'gm-product-filter-action')) {
            wp_send_json_error(array('message' => 'Security check failed'), 403);
            wp_die();
        }
            // Determine the current page number
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => 12,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'ASC',
            'paged' => $paged,
            'tax_query' => array(
                'relation' => 'AND'
            )
        );
        $argsOptions = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'tax_query' => array(
                'relation' => 'AND'
            )
        );
        $args = $this->apply_filters_to_args($args);
        $argsOptions = $this->apply_filters_to_args($argsOptions);

        $query = new WP_Query($args);
        $OptionsQuery = new WP_Query($argsOptions);
    
        // Cache the updated filters
        $cache_key = 'updated_filters_' . md5(serialize($argsOptions));
        $updated_filters = get_transient($cache_key);
    
        if ($updated_filters === false) {
            $updated_filters = $this->get_updated_filters($OptionsQuery);
            set_transient($cache_key, $updated_filters, HOUR_IN_SECONDS);
        }
        // Capture the product listing
        ob_start();

        if ($query->have_posts()) {
            while ($query->have_posts()) : $query->the_post();
            $this->display_product($query->post);
            endwhile;
         // Add pagination links
        $this->pagination($query,$paged);
        } else {
            echo '<p>No products found</p>';
        }

        $product_html = ob_get_clean();

        // Send both the filtered products and updated filters back to the AJAX request
        wp_send_json_success(array(
            'products' => $product_html,
            'filters' => $updated_filters,
            'pagination' => $this->pagination($query,$paged)
        ));

        wp_die();
    }
    private function apply_filters_to_args($args) {
        if (!isset($_POST['gm-product-filter-nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['gm-product-filter-nonce'])), 'gm-product-filter-action')) {
            wp_send_json_error(array('message' => 'Security check failed'), 403);
            wp_die();
        }
        if (!empty($_POST['category'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => array_map('sanitize_text_field', wp_unslash($_POST['category']))
            );
        }
    
        if (!empty($_POST['attribute']) && is_array($_POST['attribute'])) {
            $attributes = map_deep(wp_unslash($_POST['attribute']), 'sanitize_text_field');
            foreach ($attributes as $attribute_name => $attribute_values) {
                if (!empty($attribute_values) && is_array($attribute_values)) {
                    $sanitized_values = array_map('sanitize_text_field', $attribute_values);
                    $args['tax_query'][] = array(
                        'taxonomy' => 'pa_' . sanitize_key($attribute_name),
                        'field' => 'slug',
                        'terms' => $sanitized_values,
                    );
                }
            }
        }
    
        if (!empty($_POST['tags'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_tag',
                'field' => 'slug',
                'terms' => array_map('sanitize_text_field', wp_unslash($_POST['tags']))
            );
        }
    
        return $args;
    }
    private function display_product($post) {
        global $options;
        $product = wc_get_product($post->ID);
        if(isset($options['use_custom_template'])){
        // Get product details
        $product_link = get_permalink();
        $product_title = get_the_title(); 
        $product_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full')[0];
        $product_excerpt = get_the_excerpt();
        $product_price = $product->get_price_html(); 
        $product_category = wp_strip_all_tags(get_the_term_list(get_the_ID(), 'product_cat', '', ', ')); 
        $product_sku = $product->get_sku(); 
        $product_stock = $product->is_in_stock() ? __('In Stock', 'gm-ajax-product-filter-for-woocommerce') : __('Out of Stock', 'gm-ajax-product-filter-for-woocommerce'); 
        $add_to_cart_url = esc_url(add_query_arg('add-to-cart', get_the_ID(), $product_link)); 

            // Retrieve the custom template from the database
            $custom_template = $options['custom_template_code'];
            
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
            
            echo wp_kses($custom_template, $allowed_tags);;
            } else {
                wc_get_template_part('content', 'product');
            }
    }

    // Function to get updated filter options based on the filtered products
    private function get_updated_filters($query) {
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
    // Function to generate pagination
    private function pagination($query,$paged) {
        $big = 999999999; // an unlikely integer
        $paginationLinks = paginate_links(array(
            'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format' => '?paged=%#%',
            'current' => max(1, $paged),
            'total' => $query->max_num_pages,
            'prev_text' => __('« Prev','gm-ajax-product-filter-for-woocommerce'),
            'next_text' => __('Next »', 'gm-ajax-product-filter-for-woocommerce'),
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
