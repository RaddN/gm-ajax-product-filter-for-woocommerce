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