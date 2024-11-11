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
                wc_get_template_part('content', 'product');
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
