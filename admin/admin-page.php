<?php
function wcapf_admin_menu() {
    add_menu_page(
        'WooCommerce Product Filters',
        'Product Filters',
        'manage_options',
        'wcapf-admin',
        'wcapf_admin_page_content',
        'dashicons-filter',
        58
    );
}
add_action('admin_menu', 'wcapf_admin_menu');

function wcapf_admin_page_content() {
    ?>
    <div class="wrap wcapf_admin">
        <h1>Manage WooCommerce Product Filters</h1>
        <form method="post" action="options.php">
            
            <?php
            settings_fields('wcapf_options_group');
            do_settings_sections('wcapf-admin');
            submit_button();
            ?>
            <p>Use short code to show filter <b>[wcapf_product_filter attribute="your-attribute" terms="your-terms1, your-terms2" category="yourcata1, your-cata2" tag="your-tag1, your-tag2"]</b> for button style filter use this shortcode <b>[wcapf_product_filter_single name="conference-by-month"]</b></p>
        </form>
    </div>
    <?php
}

function wcapf_settings_init() {
    $options = get_option('wcapf_options') ?: [
        'show_categories' => 0,
        'show_attributes' => 1,
        'show_tags' => 0,
        'use_url_filter' => '',
        'update_filter_options' => 0,
        'show_loader' => 1,
        'pages' => [], 
        'use_custom_template' => 0,
        'custom_template_code' => '',
    ];
    update_option('wcapf_options', $options);

    register_setting('wcapf_options_group', 'wcapf_options');
    
    add_settings_section('wcapf_section', __('Filter Settings', 'gm-ajax-product-filter-for-woocommerce'), null, 'wcapf-admin');

    $fields = [
        'show_categories' => __('Show Categories', 'gm-ajax-product-filter-for-woocommerce'),
        'show_attributes' => __('Show Attributes', 'gm-ajax-product-filter-for-woocommerce'),
        'show_tags' => __('Show Tags', 'gm-ajax-product-filter-for-woocommerce'),
        'use_url_filter' => __('Use URL-Based Filter', 'gm-ajax-product-filter-for-woocommerce'),
        'update_filter_options' => __('Update filter options', 'gm-ajax-product-filter-for-woocommerce'),
        'show_loader' => __('Show Loader', 'gm-ajax-product-filter-for-woocommerce'),
        'use_custom_template' => __('Use Custom Product Template', 'gm-ajax-product-filter-for-woocommerce')
    ];

    foreach ($fields as $key => $label) {
        add_settings_field($key, $label, "wcapf_{$key}_render", 'wcapf-admin', 'wcapf_section');
    }

    // Pages List Field
        // Add Page Management Section
        add_settings_section('wcapf_page_section_before', __('', 'gm-ajax-product-filter-for-woocommerce'), function() {
            global $options;
            echo '<div class="page_manage" style="' . ($options['use_url_filter'] === "permalinks" ? 'display:block;' : 'display:none;') . '">';
        }, 'wcapf-admin');
        add_settings_section('wcapf_page_section', __('Pages Manage', 'gm-ajax-product-filter-for-woocommerce'), function() {
            echo '<p>' . esc_html__( 'Add the pages below where you have added the shortcode.', 'gm-ajax-product-filter-for-woocommerce' ) . '</p>';
        }, 'wcapf-admin');
    add_settings_field('pages', __('Pages List', 'gm-ajax-product-filter-for-woocommerce'), 'wcapf_pages_render', 'wcapf-admin', 'wcapf_page_section');
    add_settings_section('wcapf_page_section_after', __('', 'gm-ajax-product-filter-for-woocommerce'), function() {
        echo '</div>';
    }, 'wcapf-admin');

    // custom code template
    add_settings_field('custom_template_code', __('product custom template code', 'gm-ajax-product-filter-for-woocommerce'), 'wcapf_custom_template_code_render', 'wcapf-admin', 'wcapf_section');
}
add_action('admin_init', 'wcapf_settings_init');

function wcapf_render_checkbox($key) {
    $options = get_option('wcapf_options');
    ?>
    <label class="switch <?php echo $key; ?>">
    <input type='checkbox' name='wcapf_options[<?php echo esc_attr($key); ?>]' <?php checked(isset($options[$key]) && $options[$key] === "on"); ?>>
    <span class="slider round"></span>
    </label>
    <?php
}

function wcapf_show_categories_render() { wcapf_render_checkbox('show_categories'); }
function wcapf_show_attributes_render() { wcapf_render_checkbox('show_attributes'); }
function wcapf_show_tags_render() { wcapf_render_checkbox('show_tags'); }
function wcapf_update_filter_options_render() {
    wcapf_render_checkbox('update_filter_options');
}
function wcapf_show_loader_render() { wcapf_render_checkbox('show_loader'); }
// Render functions for new fields
function wcapf_use_custom_template_render() {
    wcapf_render_checkbox('use_custom_template');
}

function wcapf_custom_template_code_render() {
    $options = get_option('wcapf_options');
    
    echo '
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <div class="custom_template_code" style="' . (isset($options['use_custom_template']) ? 'display:block;' : 'display:none;') . '">';
    ?>

        <!-- Placeholder List -->
        <div id="placeholder-list" style="margin-bottom: 10px;">
        <span class="placeholder" onclick="insertPlaceholder('{{product_link}}')">{{product_link}}</span>
        <span class="placeholder" onclick="insertPlaceholder('{{product_title}}')">{{product_title}}</span>
        <span class="placeholder" onclick="insertPlaceholder('{{product_image}}')">{{product_image}}</span>
        <span class="placeholder" onclick="insertPlaceholder('{{product_price}}')">{{product_price}}</span>
        <span class="placeholder" onclick="insertPlaceholder('{{product_excerpt}}')">{{product_excerpt}}</span>
        <span class="placeholder" onclick="insertPlaceholder('{{product_category}}')">{{product_category}}</span>
        <span class="placeholder" onclick="insertPlaceholder('{{product_sku}}')">{{product_sku}}</span>
        <span class="placeholder" onclick="insertPlaceholder('{{product_stock}}')">{{product_stock}}</span>
        <span class="placeholder" onclick="insertPlaceholder('{{add_to_cart_url}}')">{{add_to_cart_url}}</span>
    </div>
    <textarea style="display:none;" id="custom_template_input" name="wcapf_options[custom_template_code]" rows="10" cols="50" class="large-text"><?php if(isset($options['custom_template_code'])){echo esc_textarea($options['custom_template_code']); } ?></textarea>
    <div id="code-editor"></div>
    <p class="description"><?php esc_html_e('Enter your custom template code here.', 'gm-ajax-product-filter-for-woocommerce'); ?></p>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
<script>
const editor = CodeMirror(document.getElementById("code-editor"), {
            value: document.getElementById("custom_template_input").value,
            mode: "text/html",
            lineNumbers: true,
            lineWrapping: true
        });

        editor.on("change", function() {
            document.getElementById("custom_template_input").value = editor.getValue();
        });


        function insertPlaceholder(placeholder) {
            const cursor = editor.getCursor();
            editor.replaceRange(placeholder, cursor);
            editor.focus();
        }
</script>
    <?php
}

function wcapf_use_url_filter_render() {
    $options = get_option('wcapf_options');
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
    $options = get_option('wcapf_options');
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
    </script>
    <?php
}



?>