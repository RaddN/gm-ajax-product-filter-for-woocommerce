<?php
class WCAPF_Filter_Functions {

    public function process_filter() {
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => 12,
            'post_status' => 'publish',
            'tax_query' => array(
                'relation' => 'AND'
            )
        );

        if (isset($_POST['gm-product-filter-nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['gm-product-filter-nonce'])), 'gm-product-filter-action')) {
        // Filter by categories
        if (!empty($_POST['category'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => array_map('sanitize_text_field', wp_unslash($_POST['category']))
            );
        }

        // Filter by attributes
        if (!empty($_POST['attribute'])) {
            foreach (wp_unslash($_POST['attribute']) as $attribute_name => $attribute_values) {
                if (!empty($attribute_values)) {
                    $args['tax_query'][] = array(
                        'taxonomy' => 'pa_' . sanitize_text_field($attribute_name),
                        'field' => 'slug',
                        'terms' => array_map('sanitize_text_field', $attribute_values)
                    );
                }
            }
        }

        // Filter by tags
        if (!empty($_POST['tags'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_tag',
                'field' => 'slug',
                'terms' => array_map('sanitize_text_field', wp_unslash($_POST['tags']))
            );
        }
    }else {
        // Nonce verification failed
        die('Security check failed');
    }
        $query = new WP_Query($args);

        // Prepare the updated filters
        $updated_filters = $this->get_updated_filters($args);

        // Capture the product listing
        ob_start();

        if ($query->have_posts()) {
            while ($query->have_posts()) : $query->the_post();
            global $options;
            if(isset($options['use_custom_template'])){
        // Get product details
        $product = wc_get_product(get_the_ID());
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
              echo  $custom_template;
            } else {
                wc_get_template_part('content', 'product');
            }
            endwhile;
        } else {
            echo '<p>No products found</p>';
        }

        $product_html = ob_get_clean();

        // Send both the filtered products and updated filters back to the AJAX request
        wp_send_json_success(array(
            'products' => $product_html,
            'filters' => $updated_filters
        ));

        wp_die();
    }

    // Function to get updated filter options based on the filtered products
    private function get_updated_filters($args) {
        // Get the current product IDs based on the filtered query
        $query = new WP_Query($args);
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

        return array(
            'categories' => $categories,
            'attributes' => $attributes,
            'tags' => $tags
        );
    }
}
