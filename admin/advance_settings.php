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
// Render the "Product Shortcode Selector" field
function wcapf_product_shortcode_callback() {
    $options = get_option('wcapf_advance_options');
    $product_shortcode = isset($options['product_shortcode']) ? esc_attr($options['product_shortcode']) : 'products';
    ?>
    <input type="text" name="wcapf_advance_options[product_shortcode]" value="<?php echo esc_attr($product_shortcode); ?>" placeholder="products">
    <p class="description">
        <?php esc_html_e('Enter the selector for the products shortcode. Default is products', 'gm-ajax-product-filter-for-woocommerce'); ?>
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


// Handle the import settings action
function wcapf_import_settings_action() {
    // Check if the file was uploaded
    if (isset($_FILES['wcapf_import_file']) && $_FILES['wcapf_import_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['wcapf_import_file']['tmp_name'];

        // Read the file contents
        $json_data = file_get_contents($file);
        
        // Decode the JSON data
        $options = json_decode($json_data, true);

        // Validate the JSON structure before updating the options
        if (isset($options['wcapf_options'], $options['wcapf_style_options'], $options['wcapf_advance_options'])) {
            // Update the WordPress options with the imported data
            update_option('wcapf_options', $options['wcapf_options']);
            update_option('wcapf_style_options', $options['wcapf_style_options']);
            update_option('wcapf_advance_options', $options['wcapf_advance_options']);

            // Redirect back with a success message
            wp_redirect(add_query_arg('import', 'success', wp_get_referer()));
            exit;
        } else {
            // Redirect back with an error message
            wp_redirect(add_query_arg('import', 'error', wp_get_referer()));
            exit;
        }
    } else {
        // Handle file upload error
        wp_redirect(add_query_arg('import', 'upload_error', wp_get_referer()));
        exit;
    }
}

// Hook the import function to admin_post action
add_action('admin_post_import_wcapf_settings', 'wcapf_import_settings_action');


// Display success or error messages
function wcapf_display_import_message() {
    if (isset($_GET['import'])) {
        $message = '';
        $class = '';

        switch ($_GET['import']) {
            case 'success':
                $message = 'Settings imported successfully.';
                $class = 'updated';
                break;
            case 'error':
                $message = 'Invalid JSON structure. Please check your file.';
                $class = 'error';
                break;
            case 'upload_error':
                $message = 'File upload error. Please try again.';
                $class = 'error';
                break;
        }
        if ($message) {
            echo '<div class="' . $class . '"><p>' . esc_html($message) . '</p></div>';
        }
    }
}
add_action('admin_notices', 'wcapf_display_import_message');

