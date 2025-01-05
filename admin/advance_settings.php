<?php
if (!defined('ABSPATH')) {
    exit;
}
// Render the "Product Selector" field
function dapfforwc_product_selector_callback() {
    $dapfforwc_options = get_option('dapfforwc_advance_options');
    $product_selector = isset($dapfforwc_options['product_selector']) ? esc_attr($dapfforwc_options['product_selector']) : '.products';
    ?>
    <input type="text" name="dapfforwc_advance_options[product_selector]" value="<?php echo esc_attr($product_selector); ?>" placeholder=".products">
    <p class="description">
        <?php esc_html_e('Enter the CSS selector for the product container. Default is .products.', 'dynamic-ajax-product-filters-for-woocommerce'); ?>
    </p>
    <?php
}

// Render the "Pagination Selector" field
function dapfforwc_pagination_selector_callback() {
    $dapfforwc_options = get_option('dapfforwc_advance_options');
    $pagination_selector = isset($dapfforwc_options['pagination_selector']) ? esc_attr($dapfforwc_options['pagination_selector']) : '.woocommerce-pagination ul.page-numbers';
    ?>
    <input type="text" name="dapfforwc_advance_options[pagination_selector]" value="<?php echo esc_attr($pagination_selector); ?>" placeholder=".woocommerce-pagination ul.page-numbers">
    <p class="description">
        <?php esc_html_e('Enter the CSS selector for the pagination container. Default is .woocommerce-pagination ul.page-numbers.', 'dynamic-ajax-product-filters-for-woocommerce'); ?>
    </p>
    <?php
}
// Render the "Product Shortcode Selector" field
function dapfforwc_product_shortcode_callback() {
    $dapfforwc_options = get_option('dapfforwc_advance_options');
    $product_shortcode = isset($dapfforwc_options['product_shortcode']) ? esc_attr($dapfforwc_options['product_shortcode']) : 'products';
    ?>
    <input type="text" name="dapfforwc_advance_options[product_shortcode]" value="<?php echo esc_attr($product_shortcode); ?>" placeholder="products">
    <p class="description">
        <?php esc_html_e('Enter the selector for the products shortcode. Default is products', 'dynamic-ajax-product-filters-for-woocommerce'); ?>
    </p>
    <?php
}

function dapfforwc_use_anchor_render() { dapfforwc_render_advance_checkbox('use_anchor'); }
function dapfforwc_remove_outofStock_render() { dapfforwc_render_advance_checkbox('remove_outofStock'); }


function dapfforwc_render_advance_checkbox($key) {
    $dapfforwc_options = get_option('dapfforwc_advance_options');
    ?>
    <label class="switch <?php echo esc_attr($key); ?>">
    <input type='checkbox' name='dapfforwc_advance_options[<?php echo esc_attr($key); ?>]' <?php checked(isset($dapfforwc_options[$key]) && $dapfforwc_options[$key] === "on"); ?>>
    <span class="slider round"></span>
    </label>
    <?php
}



// Handle the export settings action
function dapfforwc_export_settings_action() {
    // Collect the relevant options
    $dapfforwc_options = [
        'dapfforwc_options' => get_option('dapfforwc_options'),
        'dapfforwc_style_options' => get_option('dapfforwc_style_options'),
        'dapfforwc_advance_options' => get_option('dapfforwc_advance_options'),
    ];

    // Convert the options to JSON format
    $json_data = wp_json_encode($dapfforwc_options, JSON_PRETTY_PRINT);

    // Set headers for the JSON file download
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="dapfforwc-settings.json"');
    
    // Output the JSON data and terminate the script
    echo $json_data;
    exit;
}

// Hook the export function to admin_post action
add_action('admin_post_dapfforwc_export_settings', 'dapfforwc_export_settings_action');


// Handle the import settings action
function dapfforwc_import_settings_action() {
    if ( !isset( $_POST['_wpnonce'] ) || !wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'dapfforwc_import_settings_nonce' ) ) {
        wp_die('Nonce verification failed.');
    }

    $import_file = $_FILES['dapfforwc_import_file']??[];
    // Check if the file was uploaded
    if (isset($_FILES['dapfforwc_import_file']) ) {
        $file = $import_file['tmp_name']??[];

        // Read the file contents
        $json_data = file_get_contents($file);
        
        // Decode the JSON data
        $dapfforwc_options = json_decode($json_data, true);

        // Validate the JSON structure before updating the options
        if (isset($dapfforwc_options['dapfforwc_options'], $dapfforwc_options['dapfforwc_style_options'], $dapfforwc_options['dapfforwc_advance_options'])) {
            // Update the WordPress options with the imported data
            update_option('dapfforwc_options', $dapfforwc_options['dapfforwc_options']);
            update_option('dapfforwc_style_options', $dapfforwc_options['dapfforwc_style_options']);
            update_option('dapfforwc_advance_options', $dapfforwc_options['dapfforwc_advance_options']);

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
add_action('admin_post_dapfforwc_import_settings', 'dapfforwc_import_settings_action');


// Display success or error messages
function dapfforwc_display_import_message() {
   
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
            echo '<div class="' . esc_attr($class) . '"><p>' . esc_html($message) . '</p></div>';
        }
    }
}
add_action('admin_notices', 'dapfforwc_display_import_message');

