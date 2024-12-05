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
        global $wpdb;

        // Get all product category slugs
        $category_slugs = $wpdb->get_col("
            SELECT slug
            FROM {$wpdb->terms} 
            WHERE term_id IN (
                SELECT term_id
                FROM {$wpdb->term_taxonomy}
                WHERE taxonomy = 'product_cat'
            )
        ");

        // Get all product tag slugs
        $tag_slugs = $wpdb->get_col("
            SELECT slug
            FROM {$wpdb->terms} 
            WHERE term_id IN (
                SELECT term_id
                FROM {$wpdb->term_taxonomy}
                WHERE taxonomy = 'product_tag'
            )
        ");

        // Get all attribute terms slugs
        $attribute_slugs = $wpdb->get_col("
            SELECT slug
            FROM {$wpdb->terms} 
            WHERE term_id IN (
                SELECT term_id
                FROM {$wpdb->term_taxonomy}
                WHERE taxonomy LIKE 'pa_%'
            )
        ");

        // Merge all slugs into one array
        $all_slugs = array_merge($category_slugs, $tag_slugs, $attribute_slugs);

        // Find duplicate slugs
        $duplicate_slugs = array_unique(array_diff_assoc($all_slugs, array_unique($all_slugs)));

        // Show admin notice if duplicates exist
        if (!empty($duplicate_slugs)) {
            add_action('admin_notices', function() use ($duplicate_slugs) {
                ?>
                <div class="notice notice-error">
                    <p><?php esc_html_e('The following slugs are duplicated across categories, tags, or attributes:', 'text-domain'); ?></p>
                    <ul>
                        <?php foreach ($duplicate_slugs as $slug) : ?>
                            <li><?php echo esc_html($slug); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <p><?php esc_html_e('Please ensure each slug is unique to avoid filtering issues.', 'text-domain'); ?></p>
                </div>
                <?php
            });
        }
    }
}
add_action('admin_init', 'check_woocommerce_duplicate_slugs');
