<?php
if (!defined('ABSPATH')) {
    exit;
}

function dapfforwc_render_checkbox($key) {
    global $dapfforwc_options;
    ?>
    <label class="switch <?php echo esc_attr($key); ?>">
    <input type='checkbox' name='dapfforwc_options[<?php echo esc_attr($key); ?>]' <?php checked(isset($dapfforwc_options[$key]) && $dapfforwc_options[$key] === "on"); ?>>
    <span class="slider round"></span>
    </label>
    <?php
    if($key ==="use_filters_word_in_permalinks") {
        echo "<p>if you want to use permalinks filter in your front page turn it on.</p>";
    }
}

function dapfforwc_show_categories_render() { dapfforwc_render_checkbox('show_categories'); }
function dapfforwc_show_attributes_render() { dapfforwc_render_checkbox('show_attributes'); }
function dapfforwc_show_tags_render() { dapfforwc_render_checkbox('show_tags'); }
function dapfforwc_show_price_range_render() { dapfforwc_render_checkbox('show_price_range'); }
function dapfforwc_show_rating_render() { dapfforwc_render_checkbox('show_rating'); }
function dapfforwc_use_filters_word_in_permalinks_render() { dapfforwc_render_checkbox('use_filters_word_in_permalinks'); }
function dapfforwc_update_filter_options_render() {dapfforwc_render_checkbox('update_filter_options');}
function dapfforwc_show_loader_render() { dapfforwc_render_checkbox('show_loader'); }
function dapfforwc_use_custom_template_render() {dapfforwc_render_checkbox('use_custom_template');}
function dapfforwc_pages_filter_auto_render() { dapfforwc_render_checkbox('pages_filter_auto'); }


function dapfforwc_custom_template_code_render() {
    global $dapfforwc_options;
    echo '    
    <div class="custom_template_code" style="' . (isset($dapfforwc_options['use_custom_template']) ? 'display:block;' : 'display:none;') . '">';
    ?>
        <!-- Placeholder List -->
        <div id="placeholder-list" style="margin-bottom: 10px;">
        <?php
            $placeholders = [
                '{{product_link}}' => 'Product Link',
                '{{product_title}}' => 'Product Title',
                '{{product_image}}' => 'Product Image',
                '{{product_price}}' => 'Product Price',
                '{{product_excerpt}}' => 'Product Excerpt',
                '{{product_category}}' => 'Product Category',
                '{{product_sku}}' => 'Product SKU',
                '{{product_stock}}' => 'Product Stock',
                '{{add_to_cart_url}}' => 'Add to Cart URL',
                '{{product_id}}' => 'Product ID'
            ];
            foreach ($placeholders as $placeholder => $label) {
                echo "<span class='placeholder' onclick=\"insertPlaceholder('".esc_html($placeholder)."')\">".esc_html($placeholder)."</span>";
            }
            ?>
    </div>
    <textarea style="display:none;" id="custom_template_input" name="dapfforwc_options[custom_template_code]" rows="10" cols="50" class="large-text"><?php if(isset($dapfforwc_options['custom_template_code'])){echo esc_textarea($dapfforwc_options['custom_template_code']); } ?></textarea>
    <div id="code-editor"></div>
    <p class="description"><?php esc_html_e('Enter your custom template code here.', 'dynamic-ajax-product-filters-for-woocommerce'); ?></p>
</div>


    <?php
}

function dapfforwc_use_url_filter_render() {
    global $dapfforwc_options;
    ?>
    <fieldset>
    <legend><?php esc_html_e('Select URL Filter Type', 'dynamic-ajax-product-filters-for-woocommerce'); ?></legend>
        <?php
        $types = [
            'query_string' => __('With Query String (e.g., ?filters)', 'dynamic-ajax-product-filters-for-woocommerce'),
            'permalinks' => __('With Permalinks (e.g., canada/toronto/feb-2024)', 'dynamic-ajax-product-filters-for-woocommerce'),
            'ajax' => __('With Ajax', 'dynamic-ajax-product-filters-for-woocommerce'),
        ];
        foreach ($types as $value => $label) {
            echo "<label><input type='radio' name='dapfforwc_options[use_url_filter]' value='" . esc_attr($value) . "' " . checked($dapfforwc_options['use_url_filter'], $value, false) . "> " . esc_html($label) . "</label><br>";
        }
        ?>
    </fieldset>
    <?php
}
function dapfforwc_pages_render() {
    global $dapfforwc_options;
    $pages = isset($dapfforwc_options['pages']) ? array_filter($dapfforwc_options['pages']) : []; // Filter out empty values
    ?>
    <div class="page-listing">
    <legend>Manage Pages</legend>
    <div class="page-inputs">
        <input type="text" name="dapfforwc_options[pages][]" value="" placeholder="Add new page" />
        <button type="button" class="add-page">Add Page</button>
    </div>
    <div class="page-list">
        <?php foreach ($pages as $page) : ?>
            <div class="page-item">
                <input type="text" name="dapfforwc_options[pages][]" value="<?php echo esc_attr($page); ?>" />
                <button type="button" class="remove-page">Remove</button>
            </div>
        <?php endforeach; ?>
    </div>
        </div>
    <?php
}
// Render function for default filters
function dapfforwc_default_filters_render() {
    global $dapfforwc_options;
    $default_filters = isset($dapfforwc_options['default_filters']) ? $dapfforwc_options['default_filters'] : [];
    $pages = isset($dapfforwc_options['pages']) ? $dapfforwc_options['pages'] : [];
    echo '<table class="form-table">';
    foreach ($pages as $page_name) {
        $filters = isset($default_filters[$page_name]) ? $default_filters[$page_name] : [];
        $filters_string = implode(',', $filters); // Convert array to comma-separated string for editing.

        echo '<tr>';
        echo '<th>' . esc_html($page_name) . '</th>';
        echo '<td>';
        echo '<input type="text" name="dapfforwc_options[default_filters][' . esc_attr($page_name) . ']" value="' . esc_attr($filters_string) . '" placeholder="' . esc_html__('Enter default filters, comma-separated', 'dynamic-ajax-product-filters-for-woocommerce') . '" />';
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
}
function dapfforwc_options_sanitize($input) {
    if (isset($input['default_filters'])) {
        foreach ($input['default_filters'] as $page_name => $filters_value) {
            // Ensure $filters_value is a string before calling explode
            if (is_string($filters_value)) {
                $input['default_filters'][$page_name] = array_filter(array_map('trim', explode(',', $filters_value)));
            } elseif (is_array($filters_value)) {
                // If already an array, clean up empty values and trim items
                $input['default_filters'][$page_name] = array_filter(array_map('trim', $filters_value));
            } else {
                // Invalid type, fallback to an empty array
                $input['default_filters'][$page_name] = [];
            }
        }
    }
    else{
        $sanitized = [];
    foreach ( $input as $key => $value ) {
        if ( is_array( $value ) ) {
            $sanitized[ $key ] = dapfforwc_sanitize_nested_array($value);
        } elseif ( is_string( $value ) ) {
            $sanitized[ $key ] = sanitize_text_field( $value );
        }
    }

    return $sanitized;
    }
    return $input;
}
add_filter('pre_update_option_dapfforwc_options', 'dapfforwc_options_sanitize');


// Helper function to sanitize nested arrays.
function dapfforwc_sanitize_nested_array($array) {
    $sanitized_array = array();

    foreach ($array as $key => $value) {
        if (is_array($value)) {
            // Recursively handle nested arrays.
            $sanitized_array[$key] = dapfforwc_sanitize_nested_array($value);
        } else {
            // Apply appropriate sanitization based on key or type.
            if (is_string($value)) {
                // Assume strings need text sanitization.
                $sanitized_array[$key] = sanitize_text_field($value);
            } elseif (is_numeric($value)) {
                // Handle numeric values.
                $sanitized_array[$key] = $value;
            } else {
                // Default sanitization for unexpected types.
                $sanitized_array[$key] = sanitize_text_field((string)$value);
            }
        }
    }

    return $sanitized_array;
}


