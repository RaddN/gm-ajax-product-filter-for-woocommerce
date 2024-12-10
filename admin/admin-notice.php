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
    // Only run in the admin area
    if (is_admin()) {
        // Check if the notice should be displayed
        $dismissed_time = get_option('woocommerce_slug_check_dismissed_time');
        if ($dismissed_time && (time() - $dismissed_time) < 3 * DAY_IN_SECONDS) {
            return; // Don't show the notice if dismissed within the past 3 days
        }

        // Get all product category slugs
        $category_terms = get_terms(array(
            'taxonomy'   => 'product_cat',
            'fields'     => 'slugs',
            'hide_empty' => false, 
            'orderby'    => 'name',
        ));

        // Get all product tag slugs
        $tag_terms = get_terms(array(
            'taxonomy'   => 'product_tag',
            'fields'     => 'slugs',
            'hide_empty' => false, 
            'orderby'    => 'name',
        ));

        // Get all attribute slugs
        $attribute_terms = [];
        $attribute_taxonomies = wc_get_attribute_taxonomies();
        foreach ($attribute_taxonomies as $attribute) {
            $terms = get_terms(array(
                'taxonomy'   => 'pa_' . $attribute->attribute_name,
                'fields'     => 'slugs',
                'hide_empty' => false,
            ));
            $attribute_terms = array_merge($attribute_terms, $terms);
        }

        // Merge all slugs into one array
        $all_slugs = array_merge($category_terms, $tag_terms, $attribute_terms);

        // Find duplicate slugs
        $slug_counts = array_count_values($all_slugs);
        $duplicate_slugs = array_filter($slug_counts, function($count) {
            return $count > 1;
        });

        // Show admin notice if duplicates exist
        if (!empty($duplicate_slugs)) {
            add_action('admin_notices', function() use ($duplicate_slugs) {
                ?>
                <div class="notice notice-error is-dismissible" id="woocommerce-slug-check-notice">
                    <p><?php esc_html_e('The following slugs are duplicated across categories, tags, or attributes:', 'gm-ajax-product-filter-for-woocommerce'); ?></p>
                    <ul>
                        <?php foreach (array_keys($duplicate_slugs) as $slug) : ?>
                            <li><?php echo esc_html($slug); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <p><?php esc_html_e('Please ensure each slug is unique to avoid filtering issues.', 'gm-ajax-product-filter-for-woocommerce'); ?></p>
                    <button type="button" id="woocommerce-slug-check-remind-later" class="button"><?php esc_html_e('Remind Me Later', 'gm-ajax-product-filter-for-woocommerce'); ?></button>
                </div>
                <script type="text/javascript">
                    jQuery(document).ready(function($) {
                        $('#woocommerce-slug-check-remind-later').on('click', function() {
                            $.post(ajaxurl, {
                                action: 'dismiss_slug_check_notice',
                            }, function() {
                                $('#woocommerce-slug-check-notice').fadeOut();
                            });
                        });
                    });
                </script>
                <?php
            });
        }
    }
}
add_action('admin_init', 'check_woocommerce_duplicate_slugs');

// Handle AJAX dismiss action
function dismiss_slug_check_notice() {
    update_option('woocommerce_slug_check_dismissed_time', time());
    wp_send_json_success();
}
add_action('wp_ajax_dismiss_slug_check_notice', 'dismiss_slug_check_notice');


