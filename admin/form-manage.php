<?php

function wcapf_render_checkbox($key) {
    global $options;
    ?>
    <label class="switch <?php echo esc_attr($key); ?>">
    <input type='checkbox' name='wcapf_options[<?php echo esc_attr($key); ?>]' <?php checked(isset($options[$key]) && $options[$key] === "on"); ?>>
    <span class="slider round"></span>
    </label>
    <?php
    if($key ==="use_filters_word_in_permalinks") {
        echo "<p>if you want to use permalinks filter in your front page turn it on.</p>";
    }
}

function wcapf_show_categories_render() { wcapf_render_checkbox('show_categories'); }
function wcapf_show_attributes_render() { wcapf_render_checkbox('show_attributes'); }
function wcapf_show_tags_render() { wcapf_render_checkbox('show_tags'); }
function wcapf_show_price_range_render() { wcapf_render_checkbox('show_price_range'); }
function wcapf_show_rating_render() { wcapf_render_checkbox('show_rating'); }
function wcapf_use_filters_word_in_permalinks_render() { wcapf_render_checkbox('use_filters_word_in_permalinks'); }
function wcapf_update_filter_options_render() {wcapf_render_checkbox('update_filter_options');}
function wcapf_show_loader_render() { wcapf_render_checkbox('show_loader'); }
function wcapf_use_custom_template_render() {wcapf_render_checkbox('use_custom_template');}
function wcapf_pages_filter_auto_render() { wcapf_render_checkbox('pages_filter_auto'); }


function wcapf_custom_template_code_render() {
    global $options;
    echo '    
    <div class="custom_template_code" style="' . (isset($options['use_custom_template']) ? 'display:block;' : 'display:none;') . '">';
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
                echo "<span class='placeholder' onclick=\"insertPlaceholder('$placeholder')\">$placeholder</span>";
            }
            ?>
    </div>
    <textarea style="display:none;" id="custom_template_input" name="wcapf_options[custom_template_code]" rows="10" cols="50" class="large-text"><?php if(isset($options['custom_template_code'])){echo esc_textarea($options['custom_template_code']); } ?></textarea>
    <div id="code-editor"></div>
    <p class="description"><?php esc_html_e('Enter your custom template code here.', 'gm-ajax-product-filter-for-woocommerce'); ?></p>
</div>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const checkbox = document.querySelector('input[name="wcapf_options[use_custom_template]"]');
    
    // Function to show or hide the closest <tr> with the .custom_template_code class
    function toggleTemplateRow() {
      const closestTr = document.querySelector(".custom_template_code").closest('tr');
      
      // Check if the next sibling has the class 'custom_template_code'
      if (closestTr) {
        if (checkbox.checked) {
          closestTr.style.display = ''; // Show the row
        } else {
          closestTr.style.display = 'none'; // Hide the row
        }
      }
    }
    // Event listener for checkbox change
    checkbox.addEventListener('change', toggleTemplateRow);

    // Initialize the state on page load
    toggleTemplateRow();
  });
</script>


    <?php
}

function wcapf_use_url_filter_render() {
    global $options;
    ?>
    <fieldset>
    <legend><?php esc_html_e('Select URL Filter Type', 'gm-ajax-product-filter-for-woocommerce'); ?></legend>
        <?php
        $types = [
            'query_string' => __('With Query String (e.g., ?filters)', 'gm-ajax-product-filter-for-woocommerce'),
            'permalinks' => __('With Permalinks (e.g., canada/toronto/feb-2024)', 'gm-ajax-product-filter-for-woocommerce'),
            'ajax' => __('With Ajax', 'gm-ajax-product-filter-for-woocommerce'),
        ];
        foreach ($types as $value => $label) {
            echo "<label><input type='radio' name='wcapf_options[use_url_filter]' value='" . esc_attr($value) . "' " . checked($options['use_url_filter'], $value, false) . "> " . esc_html($label) . "</label><br>";
        }
        ?>
    </fieldset>
    <?php
}
function wcapf_pages_render() {
    global $options;
    $pages = isset($options['pages']) ? array_filter($options['pages']) : []; // Filter out empty values
    ?>
    <div class="page-listing">
    <legend>Manage Pages</legend>
    <div class="page-inputs">
        <input type="text" name="wcapf_options[pages][]" value="" placeholder="Add new page" />
        <button type="button" class="add-page">Add Page</button>
    </div>
    <div class="page-list">
        <?php foreach ($pages as $page) : ?>
            <div class="page-item">
                <input type="text" name="wcapf_options[pages][]" value="<?php echo esc_attr($page); ?>" />
                <button type="button" class="remove-page">Remove</button>
            </div>
        <?php endforeach; ?>
    </div>
        </div>
    <script>
        document.querySelector('.page-inputs .add-page').addEventListener('click', function() {
            var newPage = document.createElement('div');
            newPage.classList.add('page-item');
            newPage.innerHTML = '<input type="text" name="wcapf_options[pages][]" value="" placeholder="Add new page" /> <button type="button" class="remove-page">Remove</button>';
            document.querySelector('.page-list').appendChild(newPage);
        });

        document.querySelector('.page-list').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-page')) {
                e.target.closest('.page-item').remove();
            }
        });

        document.addEventListener("DOMContentLoaded", function () {
    // Get the checkbox element
    const pagesFilterAutoCheckbox = document.querySelector('input[name="wcapf_options[pages_filter_auto]"]');
    // Get all table rows
    const rows = document.querySelectorAll(".page_manage .form-table > tbody > tr");

    // Function to toggle row visibility
    function toggleRows() {
        rows.forEach((row, index) => {
            if (index === 0 || !pagesFilterAutoCheckbox.checked) {
                row.style.display = "table-row";
            } else {
                row.style.display = "none";
            }
        });
    }

    // Attach change event to the checkbox
    pagesFilterAutoCheckbox.addEventListener("change", toggleRows);

    // Initial toggle based on the checkbox state
    toggleRows();
});
    </script>
    <?php
}
// Render function for default filters
function wcapf_default_filters_render() {
    global $options;
    $default_filters = isset($options['default_filters']) ? $options['default_filters'] : [];
    $pages = isset($options['pages']) ? $options['pages'] : [];
    echo '<table class="form-table">';
    foreach ($pages as $page_name) {
        $filters = isset($default_filters[$page_name]) ? $default_filters[$page_name] : [];
        $filters_string = implode(',', $filters); // Convert array to comma-separated string for editing.

        echo '<tr>';
        echo '<th>' . esc_html($page_name) . '</th>';
        echo '<td>';
        echo '<input type="text" name="wcapf_options[default_filters][' . esc_attr($page_name) . ']" value="' . esc_attr($filters_string) . '" placeholder="' . esc_html__('Enter default filters, comma-separated', 'gm-ajax-product-filter-for-woocommerce') . '" />';
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
}
function wcapf_options_sanitize($input) {
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
    return $input;
}
add_filter('pre_update_option_wcapf_options', 'wcapf_options_sanitize');


