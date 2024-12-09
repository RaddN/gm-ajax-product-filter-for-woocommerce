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