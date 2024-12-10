<?php
// Render the "Product Selector" field
function wcapf_product_selector_callback() {
    $options = get_option('wcapf_advance_options');
    $product_selector = isset($options['product_selector']) ? esc_attr($options['product_selector']) : '.products';
    ?>
    <input type="text" name="wcapf_advance_options[product_selector]" value="<?php echo esc_attr($product_selector); ?>" placeholder=".products">
    <p class="description">
        <?php esc_html_e('Enter the CSS selector for the product container. Default is .products.', 'gm-ajax-product-filter-for-woocommerce'); ?>
    </p>
    <?php
}

// Render the "Pagination Selector" field
function wcapf_pagination_selector_callback() {
    $options = get_option('wcapf_advance_options');
    $pagination_selector = isset($options['pagination_selector']) ? esc_attr($options['pagination_selector']) : '.woocommerce-pagination ul.page-numbers';
    ?>
    <input type="text" name="wcapf_advance_options[pagination_selector]" value="<?php echo esc_attr($pagination_selector); ?>" placeholder=".woocommerce-pagination ul.page-numbers">
    <p class="description">
        <?php esc_html_e('Enter the CSS selector for the pagination container. Default is .woocommerce-pagination ul.page-numbers.', 'gm-ajax-product-filter-for-woocommerce'); ?>
    </p>
    <?php
}

function wcapf_use_anchor_render() { wcapf_render_advance_checkbox('use_anchor'); }


function wcapf_render_advance_checkbox($key) {
    $options = get_option('wcapf_advance_options');
    ?>
    <label class="switch <?php echo esc_attr($key); ?>">
    <input type='checkbox' name='wcapf_advance_options[<?php echo esc_attr($key); ?>]' <?php checked(isset($options[$key]) && $options[$key] === "on"); ?>>
    <span class="slider round"></span>
    </label>
    <?php
}

// Callback function for Import settings
function wcapf_import_settings_callback() {
    ?>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="wcapf_import_file" id="wcapf_import_file" accept=".json" required />
        <button type="submit" name="wcapf_import_button" id="wcapf_import_button" class="button button-primary">Import Settings</button>
    </form>
    <?php
}


// Callback function for Export settings
function wcapf_export_settings_callback() {
    ?>
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <input type="hidden" name="action" value="export_wcapf_settings">
        <button type="submit" name="wcapf_export_button" id="wcapf_export_button" class="button button-primary">Export Settings</button>
    </form>
    <?php
}


// Handle the export settings action
function wcapf_export_settings_action() {
    // Collect the relevant options
    $options = [
        'wcapf_options' => get_option('wcapf_options'),
        'wcapf_style_options' => get_option('wcapf_style_options'),
        'wcapf_advance_options' => get_option('wcapf_advance_options'),
    ];

    // Convert the options to JSON format
    $json_data = json_encode($options, JSON_PRETTY_PRINT);

    // Set headers for the JSON file download
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="wcapf-settings.json"');
    
    // Output the JSON data and terminate the script
    echo $json_data;
    exit;
}

// Hook the export function to admin_post action
add_action('admin_post_export_wcapf_settings', 'wcapf_export_settings_action');

