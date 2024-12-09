<?php
// Hook to admin_notices action
add_action('admin_notices', 'custom_admin_notice');

function custom_admin_notice() {
    global $options;

    // Check the conditions
    if (isset($options['use_url_filter']) && $options['use_url_filter'] === 'permalinks' && count($options['pages'])<2) {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p><?php  esc_html_e('Please note: You have enabled permalinks filtering please add the pages slug where you have used filter.', 'gm-ajax-product-filter-for-woocommerce'); ?></p>
        </div>
        <?php
    }
}


// check duplicate slugs

function check_woocommerce_duplicate_slugs() {
    // Only run in admin area
    if (is_admin()) {
        // Get all product category slugs
        $category_terms = get_terms(array(
            'taxonomy'   => 'product_cat',
            'fields'      => 'slugs',
            'hide_empty'  => false, // Include empty categories
            'orderby'     => 'name', // Optional, sorts the terms
        ));

        // Get all product tag slugs
        $tag_terms = get_terms(array(
            'taxonomy'   => 'product_tag',
            'fields'      => 'slugs',
            'hide_empty'  => false, // Include empty tags
            'orderby'     => 'name', // Optional, sorts the terms
        ));

        // Get all attribute slugs (We need to loop through each attribute)
        $attribute_terms = [];
        $attribute_taxonomies = wc_get_attribute_taxonomies(); // Get all product attributes
        foreach ($attribute_taxonomies as $attribute) {
            $terms = get_terms(array(
                'taxonomy'   => 'pa_' . $attribute->attribute_name,
                'fields'      => 'slugs',
                'hide_empty'  => false,
            ));
            $attribute_terms = array_merge($attribute_terms, $terms);
        }

        // Merge all slugs into one array
        $all_slugs = array_merge($category_terms, $tag_terms, $attribute_terms);

        // Find duplicate slugs
        $slug_counts = array_count_values($all_slugs); // Count occurrences of each slug
        $duplicate_slugs = array_filter($slug_counts, function($count) {
            return $count > 1; // Only keep slugs that appear more than once
        });

        // Show admin notice if duplicates exist
        if (!empty($duplicate_slugs)) {
            add_action('admin_notices', function() use ($duplicate_slugs) {
                ?>
                <div class="notice notice-error">
                    <p><?php esc_html_e('The following slugs are duplicated across categories, tags, or attributes:', 'gm-ajax-product-filter-for-woocommerce'); ?></p>
                    <ul>
                        <?php foreach (array_keys($duplicate_slugs) as $slug) : ?>
                            <li><?php echo esc_html($slug); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <p><?php esc_html_e('Please ensure each slug is unique to avoid filtering issues.', 'gm-ajax-product-filter-for-woocommerce'); ?></p>
                </div>
                <?php
            });
        }
    }
}
add_action('admin_init', 'check_woocommerce_duplicate_slugs');

