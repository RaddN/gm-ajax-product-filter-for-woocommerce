<form method="post" action="options.php">
    <?php
    settings_fields('wcapf_style_options_group');
    do_settings_sections('wcapf-style');

    // Fetch WooCommerce attributes
    $attributes = wc_get_attribute_taxonomies();
    $form_styles = get_option('wcapf_style_options', []);

    // Define extra options
    $extra_options = [
        (object) ['attribute_name' => 'category', 'attribute_label' => __('Category Options', 'gm-ajax-product-filter-for-woocommerce')],
        (object) ['attribute_name' => 'tag', 'attribute_label' => __('Tag Options', 'gm-ajax-product-filter-for-woocommerce')],
    ];

    // Combine attributes and extra options
    $all_options = array_merge($attributes, $extra_options);

    // Get the first attribute for default display
    $first_attribute = !empty($all_options) ? $all_options[0]->attribute_name : '';
    ?>    

    <?php if (!empty($all_options)) : ?>
        <div class="attribute-selection">
            <label for="attribute-dropdown">
                <strong><?php esc_html_e('Select Attribute:', 'gm-ajax-product-filter-for-woocommerce'); ?></strong>
            </label>
            <select id="attribute-dropdown" style="margin-bottom: 20px;">
                <?php foreach ($all_options as $option) : ?>
                    <option value="<?php echo esc_attr($option->attribute_name); ?>">
                        <?php echo esc_html($option->attribute_label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Style Options Container -->
        <div id="style-options-container">
            <?php foreach ($all_options as $option) :
                $attribute_name = $option->attribute_name;
                $selected_style = $form_styles[$attribute_name]['type'] ?? 'dropdown';
                $sub_option = $form_styles[$attribute_name]['sub_option'] ?? '';

                // Define sub-options
                $sub_options = [
                    'checkbox' => [
                        'checkbox' => __('Checkbox', 'gm-ajax-product-filter-for-woocommerce'),
                        'radio_check' => __('Radio Check', 'gm-ajax-product-filter-for-woocommerce'),
                        'radio' => __('Radio', 'gm-ajax-product-filter-for-woocommerce'),
                        'square_check' => __('Square Check', 'gm-ajax-product-filter-for-woocommerce'),
                        'square' => __('Square', 'gm-ajax-product-filter-for-woocommerce'),
                        'checkbox_hide' => __('Checkbox Hide', 'gm-ajax-product-filter-for-woocommerce'),
                    ],
                    'color' => [
                        'color' => __('Color', 'gm-ajax-product-filter-for-woocommerce'),
                        'color_no_border' => __('Color Without Border', 'gm-ajax-product-filter-for-woocommerce'),
                    ],
                    'image' => [
                        'image' => __('Image', 'gm-ajax-product-filter-for-woocommerce'),
                        'image_no_border' => __('Image Without Border', 'gm-ajax-product-filter-for-woocommerce'),
                    ],
                    'dropdown' => [
                        'select' => __('Select', 'gm-ajax-product-filter-for-woocommerce'),
                        'select2' => __('Select 2', 'gm-ajax-product-filter-for-woocommerce'),
                        'select2_classic' => __('Select 2 Classic', 'gm-ajax-product-filter-for-woocommerce'),
                    ],
                ];
                ?>
                <div class="style-options" id="options-<?php echo esc_attr($attribute_name); ?>" style="display: <?php echo $attribute_name === $first_attribute && $attribute_name !== "category" ? 'block' : 'none'; ?>;">
                    <h3><?php echo esc_html($option->attribute_label); ?></h3>

                    <!-- Primary Options -->
                    <div class="primary_options">
                        <?php foreach ($sub_options as $key => $label) : ?>
                            <label class="<?php echo $selected_style === $key ? 'active' : ''; ?>">
                                <span class="active" style="display:none;"><i class="fa fa-check"></i></span>
                                <input type="radio" name="wcapf_style_options[<?php echo esc_attr($attribute_name); ?>][type]" value="<?php echo esc_html($key); ?>" <?php checked($selected_style, $key); ?> data-type="<?php echo esc_html($key); ?>">
                                <img src="<?php echo esc_url(plugins_url('../assets/images/' . $key . '.png', __FILE__)); ?>" alt="<?php echo esc_attr($key); ?>">
                                <div class="title"><?php echo esc_html($key); ?></div>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <!-- Sub-Options -->
                    <div class="sub-options" style="margin-left: 20px;">
                        <p><strong><?php esc_html_e('Additional Options:', 'gm-ajax-product-filter-for-woocommerce'); ?></strong></p>
                        <div class="dynamic-sub-options">
                            <?php foreach ($sub_options[$selected_style] as $key => $label) : ?>
                                
                                <label class="<?php echo $sub_option === $key ? 'active' : ''; ?>">
                                    <span class="active" style="display:none;"><i class="fa fa-check"></i></span>
                                    <input type="radio" class="optionselect" name="wcapf_style_options[<?php echo esc_attr($attribute_name); ?>][sub_option]" value="<?php echo esc_attr($key); ?>" <?php checked($sub_option, $key); ?>>
                                    <img src="<?php echo esc_url(plugins_url('../assets/images/' . $key . '.png', __FILE__)); ?>" alt="<?php echo esc_attr($label); ?>">
                                    <div class="title"><?php echo esc_html($label); ?></div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <!-- Advanced Options for Color/Image -->
                     <div class="flex">
                    <?php 
                    $terms = [];
                    if($attribute_name==="category"){
                        $terms = get_terms( array(
                            'taxonomy'   => 'product_cat',
                            'hide_empty' => false,
                        ) );
                    }
                    elseif($attribute_name==="tag"){
                        $terms =  get_terms( array(
                            'taxonomy'   => 'product_tag',
                            'hide_empty' => false,
                        ) );
                    }
                    else $terms = get_terms(['taxonomy' => 'pa_' . $attribute_name, 'hide_empty' => false]);?>
                    <div class="advanced-options <?php echo $attribute_name ?>" style="display: <?php echo $selected_style === 'color' || $selected_style === 'image' ? 'block' : 'none'; ?>;">
    <h4><?php esc_html_e('Advanced Options for Terms', 'gm-ajax-product-filter-for-woocommerce'); ?></h4>
    <?php if (!empty($terms)) : ?>
        
        <!-- Color Options -->
        <div class="color" style="display: <?php echo $selected_style === 'color' ? 'block' : 'none'; ?>;">
            <h5><?php esc_html_e('Set Colors for Terms', 'gm-ajax-product-filter-for-woocommerce'); ?></h5>
            <?php foreach ($terms as $term) :
                $color_value = $form_styles[$attribute_name]['colors'][$term->slug] ?? color_name_to_hex(esc_attr($term->slug)) ; // Fetch stored color or default
                ?>
                <div class="term-option">
                    <label for="color-<?php echo esc_attr($term->slug); ?>">
                        <strong><?php echo esc_html($term->name); ?></strong>
                    </label>
                    <input type="color" id="color-<?php echo esc_attr($term->slug); ?>" name="wcapf_style_options[<?php echo esc_attr($attribute_name); ?>][colors][<?php echo esc_attr($term->slug); ?>]" value="<?php echo esc_attr($color_value); ?>">
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Image Options -->
        <div class="image" style="display: <?php echo $selected_style === 'image' ? 'block' : 'none'; ?>;">
            <h5><?php esc_html_e('Set Images for Terms', 'gm-ajax-product-filter-for-woocommerce'); ?></h5>
            <?php foreach ($terms as $term) :
                $image_value = $form_styles[$attribute_name]['images'][$term->slug] ?? ''; // Fetch stored image URL
                ?>
                <div class="term-option">
                    <label for="image-<?php echo esc_attr($term->slug); ?>">
                        <strong><?php echo esc_html($term->name); ?></strong>
                    </label>
                    <input type="text" id="image-<?php echo esc_attr($term->slug); ?>" name="wcapf_style_options[<?php echo esc_attr($attribute_name); ?>][images][<?php echo esc_attr($term->slug); ?>]" value="<?php echo esc_attr($image_value); ?>" placeholder="<?php esc_attr_e('Image URL', 'gm-ajax-product-filter-for-woocommerce'); ?>">
                    <button type="button" class="upload-image-button"><?php esc_html_e('Upload', 'gm-ajax-product-filter-for-woocommerce'); ?></button>
                </div>
            <?php endforeach; ?>
        </div>

    <?php else : ?>
        <p><?php esc_html_e('No terms found. Please create terms for this attribute first.', 'gm-ajax-product-filter-for-woocommerce'); ?></p>
    <?php endif; ?>
</div>

            <!-- Advanced Options for Color/Image Ends -->

            <!-- Optional Settings -->
<div class="optional_settings">
    <h4><?php esc_html_e('Optional Settings:', 'gm-ajax-product-filter-for-woocommerce'); ?></h4>

    <!-- Enable Minimization Option -->
    <div class="setting-item">
        <p><strong><?php esc_html_e('Enable Minimization Option:', 'gm-ajax-product-filter-for-woocommerce'); ?></strong></p>
        <label>
            <input type="radio" name="wcapf_style_options[<?php echo esc_attr($attribute_name); ?>][minimize][type]" value="disabled" 
                <?php checked($form_styles[esc_attr($attribute_name)]['minimize']['type'] ?? '', 'disabled'); ?>>
            <?php esc_html_e('Disabled', 'gm-ajax-product-filter-for-woocommerce'); ?>
        </label>
        <label>
            <input type="radio" name="wcapf_style_options[<?php echo esc_attr($attribute_name); ?>][minimize][type]" value="arrow" 
                <?php checked($form_styles[esc_attr($attribute_name)]['minimize']['type'] ?? '', 'arrow'); ?>>
            <?php esc_html_e('Enabled with Arrow', 'gm-ajax-product-filter-for-woocommerce'); ?>
        </label>
        <label>
            <input type="radio" name="wcapf_style_options[<?php echo esc_attr($attribute_name); ?>][minimize][type]" value="no_arrow" 
                <?php checked($form_styles[esc_attr($attribute_name)]['minimize']['type'] ?? '', 'no_arrow'); ?>>
            <?php esc_html_e('Enabled without Arrow', 'gm-ajax-product-filter-for-woocommerce'); ?>
        </label>
    </div>

    <!-- Single Selection Option -->
    <div class="setting-item single-selection">
        <p><strong><?php esc_html_e('Single Selection:', 'gm-ajax-product-filter-for-woocommerce'); ?></strong></p>
        <label>
            <input type="checkbox" name="wcapf_style_options[<?php echo esc_attr($attribute_name); ?>][single_selection]" value="yes" 
                <?php checked($form_styles[esc_attr($attribute_name)]['single_selection'] ?? '', 'yes'); ?>>
            <?php esc_html_e('Only one value can be selected at a time', 'gm-ajax-product-filter-for-woocommerce'); ?>
        </label>
    </div>

    <!-- Show/Hide Number of Products -->
    <div class="setting-item">
        <p><strong><?php esc_html_e('Show/Hide Number of Products:', 'gm-ajax-product-filter-for-woocommerce'); ?></strong></p>
        <label>
            <input type="checkbox" name="wcapf_style_options[<?php echo esc_attr($attribute_name); ?>][show_product_count]" value="yes" 
                <?php checked($form_styles[esc_attr($attribute_name)]['show_product_count'] ?? '', 'yes'); ?>>
            <?php esc_html_e('Show number of products', 'gm-ajax-product-filter-for-woocommerce'); ?>
        </label>
    </div>
</div>
<!-- optional ends -->
    </div>


                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <p><?php esc_html_e('No attributes found. Please create attributes in WooCommerce first.', 'gm-ajax-product-filter-for-woocommerce'); ?></p>
    <?php endif; ?>
    <?php submit_button(); ?>
</form>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropdown = document.getElementById('attribute-dropdown');
    const firstAttribute = dropdown.value;

    // Show first attribute's options on load
    document.querySelector(`#options-${firstAttribute}`).style.display = 'block';

    // Handle dropdown change
    dropdown.addEventListener('change', function () {
        const selectedAttribute = this.value;

        // Hide all options
        document.querySelectorAll('.style-options').forEach(function (el) {
            el.style.display = 'none';
        });

        // Show the selected attribute's options
        if (selectedAttribute) {
            const selectedOptions = document.getElementById(`options-${selectedAttribute}`);
            if (selectedOptions) {
                selectedOptions.style.display = 'block';
            }
        }
    });
    // Handle primary option change
    document.querySelectorAll('.style-options .primary_options input[type="radio"][name^="wcapf_style_options"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            const selectedType = this.value;
            const attributeName = this.name.match(/\[(.*?)\]/)[1];
            const subOptionsContainer = document.querySelector(`#options-${attributeName} .dynamic-sub-options`);

            // Remove 'active' class from all labels
            document.querySelectorAll('.primary_options label').forEach(label => {
                label.classList.remove('active');
                const checkIcon = label.querySelector('.active');
                if (checkIcon) {
                    checkIcon.style.display = 'none'; // Hide check icon
                }
            });
            // Add 'active' class to the selected label
            const selectedLabel = radio.closest('label');
            selectedLabel.classList.add('active');

            const subOptions = <?php echo json_encode($sub_options); ?>;

            const currentOptions = subOptions[selectedType] || {};
            subOptionsContainer.innerHTML = '';

            const fragment = document.createDocumentFragment();
            for (const key in currentOptions) {
                const label = document.createElement('label');
                label.innerHTML = `
                    <span class="active" style="display:none;"><i class="fa fa-check"></i></span>
                    <input type="radio" class="optionselect" name="wcapf_style_options[${attributeName}][sub_option]" value="${key}">
                    <img src="/wp-content/plugins/gm-ajax-product-filter-for-woocommerce/assets/images/${key}.png" alt="${currentOptions[key]}">
                    <div class="title">${currentOptions[key]}</div>
                `;
                fragment.appendChild(label);
            }
            subOptionsContainer.appendChild(fragment);

            // Re-attach event listeners to the new radio buttons
            attachSubOptionListeners();

           if(selectedType==="color" || selectedType==="image") {
            document.querySelector(`.advanced-options.${attributeName}`).style.display = 'block';
            document.querySelector(`.advanced-options.${attributeName} .color`).style.display = 'none';
            document.querySelector(`.advanced-options.${attributeName} .image`).style.display = 'none';
            document.querySelector(`.advanced-options.${attributeName} .${selectedType}`).style.display = 'block';

           }else {
            document.querySelectorAll('.advanced-options').forEach(advanceoptions =>{
                advanceoptions.style.display = 'none';
            })
           }
        });
    });

    // Function to attach listeners to sub-option radio buttons
    function attachSubOptionListeners() {
    const radioButtons = document.querySelectorAll('.optionselect');
    
    
    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            // Remove 'active' class from all labels
            document.querySelectorAll('.dynamic-sub-options label').forEach(label => {
                label.classList.remove('active');
                const checkIcon = label.querySelector('.active');
                if (checkIcon) {
                    checkIcon.style.display = 'none'; // Hide check icon
                }
            });

            // Add 'active' class to the selected label
            const selectedLabel = this.closest('label');
            selectedLabel.classList.add('active');
            const checkIcon = selectedLabel.querySelector('.active');
            if (checkIcon) {
                checkIcon.style.display = 'inline'; // Show check icon
            }

            // Managing single selection checkbox
            const singleSelectionCheckbox = this.closest('.style-options').querySelector('.setting-item.single-selection input');
            console.log(this.value);
            if (this.value === 'select') {
                singleSelectionCheckbox.checked = true;
            } else {
                singleSelectionCheckbox.checked = false; // Uncheck if other options are selected
            }
        });
    });
}

// Call the function to attach listeners
attachSubOptionListeners();

});
</script>