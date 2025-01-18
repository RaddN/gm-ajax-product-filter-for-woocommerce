<?php

if (!defined('ABSPATH')) {
    exit;
}

class Dapfforwc_Dynamic_Ajax_Filter_Widget_Wp_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'dapfforwc_dynamic_ajax_filter_widget_wp_widget',
            __( 'Dynamic Ajax Filter', 'dynamic-ajax-product-filters-for-woocommerce' ),
            [ 'description' => __( 'A widget for dynamic AJAX filtering.', 'dynamic-ajax-product-filters-for-woocommerce' ) ]
        );
    }

    public function widget( $args, $instance ) {
        echo $args['before_widget'];

        // Output widget content
        $filter_type = $instance['filter_type'] ?? 'all';
        $output = '';

        switch ( $filter_type ) {
            case 'all':
                $product_selector = esc_attr( $instance['product_selector'] ?? '' );
                $pagination_selector = esc_attr( $instance['pagination_selector'] ?? '' );
                $output .= do_shortcode( "[plugincy_filters product_selector=\"$product_selector\" pagination_selector=\"$pagination_selector\"]" );
                break;
            case 'single':
                $filter_name = esc_attr( $instance['filter_name'] ?? '' );
                $output .= do_shortcode( "[plugincy_filters_single name=\"$filter_name\"]" );
                break;
            case 'selected':
                $output .= do_shortcode( '[plugincy_filters_selected]' );
                break;
        }

        echo $output;

        echo $args['after_widget'];
    }

    public function form( $instance ) {
        $filter_type = $instance['filter_type'] ?? 'all';
        $product_selector = $instance['product_selector'] ?? '';
        $pagination_selector = $instance['pagination_selector'] ?? '';
        $filter_name = $instance['filter_name'] ?? '';
        $custom_style = $instance['custom_style'] ?? '';
        $advanced_option = $instance['advanced_option'] ?? '';

        ?>
        <style>
            .dapfforwc-tabs {
                display: flex;
                border-bottom: 1px solid #ddd;
                margin-bottom: 10px;
            }
            .dapfforwc-tabs a {
                background: #f9f9f9;
                border: 1px solid #ddd;
                padding: 5px 10px;
                cursor: pointer;
                margin-right: 5px;
                border-bottom: none;
            }
            .dapfforwc-tabs a.active {
                background: #fff;
                border-bottom: 1px solid #fff;
            }
            .dapfforwc-tab-content {
                display: none;
                padding: 10px 0;
            }
            .dapfforwc-tab-content.active {
                display: block;
            }
        </style>
        
        <div id="<?php echo $this->id; ?>">
            <div class="dapfforwc-tabs">
               <a href="#general-tab" class="active button"><?php esc_html_e( 'General', 'dynamic-ajax-product-filters-for-woocommerce' ); ?></a>
                <a href="#style-tab" class="button"><?php esc_html_e( 'Style', 'dynamic-ajax-product-filters-for-woocommerce' ); ?></a>
                <a href="#advanced-tab" class="button"><?php esc_html_e( 'Advanced', 'dynamic-ajax-product-filters-for-woocommerce' ); ?></a>
            </div>

            <!-- General Tab -->
            <div class="dapfforwc-tab-content active">
                <p>
                    <label for="<?php echo $this->get_field_id( 'filter_type' ); ?>"><?php esc_html_e( 'Select Filter Type:', 'dynamic-ajax-product-filters-for-woocommerce' ); ?></label>
                    <select class="widefat" id="<?php echo $this->get_field_id( 'filter_type' ); ?>" name="<?php echo $this->get_field_name( 'filter_type' ); ?>">
                        <option value="all" <?php selected( $filter_type, 'all' ); ?>><?php esc_html_e( 'All Filters', 'dynamic-ajax-product-filters-for-woocommerce' ); ?></option>
                        <option value="single" <?php selected( $filter_type, 'single' ); ?>><?php esc_html_e( 'Single Filter', 'dynamic-ajax-product-filters-for-woocommerce' ); ?></option>
                        <option value="selected" <?php selected( $filter_type, 'selected' ); ?>><?php esc_html_e( 'Selected Filters', 'dynamic-ajax-product-filters-for-woocommerce' ); ?></option>
                    </select>
                </p>
                <p>
                    <label for="<?php echo $this->get_field_id( 'product_selector' ); ?>"><?php esc_html_e( 'Product Selector (for All Filters):', 'dynamic-ajax-product-filters-for-woocommerce' ); ?></label>
                    <input class="widefat" id="<?php echo $this->get_field_id( 'product_selector' ); ?>" name="<?php echo $this->get_field_name( 'product_selector' ); ?>" type="text" value="<?php echo esc_attr( $product_selector ); ?>">
                </p>
                <p>
                    <label for="<?php echo $this->get_field_id( 'pagination_selector' ); ?>"><?php esc_html_e( 'Pagination Selector (for All Filters):', 'dynamic-ajax-product-filters-for-woocommerce' ); ?></label>
                    <input class="widefat" id="<?php echo $this->get_field_id( 'pagination_selector' ); ?>" name="<?php echo $this->get_field_name( 'pagination_selector' ); ?>" type="text" value="<?php echo esc_attr( $pagination_selector ); ?>">
                </p>
            </div>

            <!-- Style Tab -->
            <div class="dapfforwc-tab-content">
                <p>
                    <label for="<?php echo $this->get_field_id( 'custom_style' ); ?>"><?php esc_html_e( 'Custom CSS:', 'dynamic-ajax-product-filters-for-woocommerce' ); ?></label>
                    <textarea class="widefat" id="<?php echo $this->get_field_id( 'custom_style' ); ?>" name="<?php echo $this->get_field_name( 'custom_style' ); ?>"><?php echo esc_attr( $custom_style ); ?></textarea>
                </p>
            </div>

            <!-- Advanced Tab -->
            <div class="dapfforwc-tab-content">
                <p>
                    <label for="<?php echo $this->get_field_id( 'advanced_option' ); ?>"><?php esc_html_e( 'Advanced Option:', 'dynamic-ajax-product-filters-for-woocommerce' ); ?></label>
                    <input class="widefat" id="<?php echo $this->get_field_id( 'advanced_option' ); ?>" name="<?php echo $this->get_field_name( 'advanced_option' ); ?>" type="text" value="<?php echo esc_attr( $advanced_option ); ?>">
                </p>
            </div>
        </div>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = [];
        $instance['filter_type'] = sanitize_text_field( $new_instance['filter_type'] );
        $instance['product_selector'] = sanitize_text_field( $new_instance['product_selector'] );
        $instance['pagination_selector'] = sanitize_text_field( $new_instance['pagination_selector'] );
        $instance['filter_name'] = sanitize_text_field( $new_instance['filter_name'] );
        $instance['custom_style'] = sanitize_textarea_field( $new_instance['custom_style'] );
        $instance['advanced_option'] = sanitize_text_field( $new_instance['advanced_option'] );
        return $instance;
    }
}

// Register the widget
function dapfforwc_register_wp_widget() {
    register_widget( 'Dapfforwc_Dynamic_Ajax_Filter_Widget_Wp_Widget' );
}
add_action( 'widgets_init', 'dapfforwc_register_wp_widget' );



// creating blocks for gutenberg

function register_dynamic_ajax_filter_block() {
    wp_register_script(
        'dynamic-ajax-filter-block',
        plugins_url( 'block.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components' ),
        filemtime( plugin_dir_path( __FILE__ ) . 'block.js' )
    );

    register_block_type( 'plugin/dynamic-ajax-filter', array(
        'editor_script' => 'dynamic-ajax-filter-block',
        'render_callback' => 'render_dynamic_ajax_filter_block',
        'attributes' => array(
            'filterType' => array(
                'type' => 'string',
                'default' => 'all',
            ),
            'productSelector' => array(
                'type' => 'string',
                'default' => '',
            ),
            'paginationSelector' => array(
                'type' => 'string',
                'default' => '',
            ),
            'filterName' => array(
                'type' => 'string',
                'default' => '',
            ),
            'backgroundColor' => array(
                'type' => 'string',
                'default' => '',
            ),
            'color' => array(
                'type' => 'string',
                'default' => '',
            ),
            'typography' => array(
                'type' => 'object',
                'default' => array(),
            ),
        ),
    ) );
}
add_action( 'init', 'register_dynamic_ajax_filter_block' );

function generate_css($styles, $device = 'desktop', $hover = false, $active = false, $sliderProgress = false, $sliderthumb = false, $slidertooltip = false) {
    $css = '';

    foreach ($styles as $key => $value) {
        switch ($key) {
            case 'font-size':
            case 'width':
            case 'gap':
                $css .= "$key: {$value}px;";
                break;

            case 'height':
                $css .= $key . ": " . $value[$device]['value'] . ($value[$device]['unit'] ?? 'px') . ";";
                break;

            case 'padding':
            case 'margin':
            case 'border-radius':
                $css .= "$key: {$value['top']}px {$value['right']}px {$value['bottom']}px {$value['left']}px;";
                break;

            case 'progress-border-radius':
                if ($sliderProgress) {
                    $css .= "border-radius: {$value['top']}px {$value['right']}px {$value['bottom']}px {$value['left']}px;";
                }
                break;
            case 'progressBackground':
                if ($sliderProgress) {
                    $css .= "background: {$value};";
                }
                break;

            case 'progressmargin':
                if ($sliderProgress) {
                    $css .= "margin: {$value['top']}px {$value['right']}px {$value['bottom']}px {$value['left']}px;";
                }
                break;
            
            case 'thumbBackground':
                if ($sliderthumb) {
                    $css .= "background: {$value};";
                }
                break;

            case 'thumbSize':
                if ($sliderthumb) {
                    $css .= "width: {$value}px; height: {$value}px;";
                }
                break;
            case 'tooltipBackground':
                if ($slidertooltip) {
                    $css .= "background: {$value};";
                }
                break;
            case 'background':
                if (is_array($value) && isset($value[$device])) {
                    $css .= "background: {$value[$device]};";
                }
                break;
            case $device:
                $css .= generate_css($value, $key);
                break;

            case 'desktop':
            case 'tablet':
            case 'mobile':
            case 'smartphone':
                break;

            case 'text-align':
                $css .= "$key: $value; justify-content: $value;";
                break;

            case 'hoverBackground':
                if ($hover) {
                    $css .= "background: $value !important;";
                }
                break;

            case 'hoverColor':
                if ($hover) {
                    $css .= "color: $value !important;";
                }
                break;

            case 'activeColor':
                if ($active) {
                    $css .= "color: $value !important;";
                }
                break;

            default:
                $css .= "$key: $value !important;";
                break;
        }
    }

    return $css;
}


function render_dynamic_ajax_filter_block($attributes) {
    $filter_type = $attributes['filterType'];
    $output = '';

// Extract styles
$form_style = $attributes['formStyle'] ?? [];
$container_style = $attributes['containerStyle'] ?? [];
$widget_title_style = $attributes['widgetTitleStyle'] ?? [];
$widget_items_style = $attributes['widgetItemsStyle'] ?? [];
$button_style = $attributes['buttonStyle'] ?? [];
$rating_style = $attributes['ratingStyle'] ?? [];
$reset_button_style = $attributes['resetButtonStyle'] ?? [];
$input_style = $attributes['inputStyle'] ?? [];
$slider_style = $attributes['sliderStyle'] ?? [];
$filter_word_mobile = $attributes['filterWordMobile'] ?? '';
$custom_css = $attributes['customCSS'] ?? '';
$class_name = $attributes['className'] ?? '';


// Generate CSS for desktop, tablet, and smartphone
$form_style_css = generate_css($form_style);
$form_sm_style_css = generate_css($form_style, 'smartphone');
$form_md_style_css = generate_css($form_style, 'tablet');

$container_style_css = generate_css($container_style);
$container_sm_style_css = generate_css($container_style, 'smartphone');
$container_md_style_css = generate_css($container_style, 'tablet');

$widget_title_style_css = generate_css($widget_title_style);
$widget_sm_title_style_css = generate_css($widget_title_style, 'smartphone');
$widget_md_title_style_css = generate_css($widget_title_style, 'tablet');

$widget_items_style_css = generate_css($widget_items_style);
$widget_items_sm_style_css = generate_css($widget_items_style, 'smartphone');
$widget_items_md_style_css = generate_css($widget_items_style, 'tablet');

$button_style_css = generate_css($button_style);
$button_sm_style_css = generate_css($button_style, 'smartphone');
$button_md_style_css = generate_css($button_style, 'tablet');
$button_hover_css = generate_css($button_style, '', true);

$rating_style_css = generate_css($rating_style);
$rating_sm_style_css = generate_css($rating_style, 'smartphone');
$rating_md_style_css = generate_css($rating_style, 'tablet');
$rating_hover_css = generate_css($rating_style, '', true);
$rating_active_css = generate_css($rating_style, '', false, true);

$reset_button_style_css = generate_css($reset_button_style);
$reset_button_sm_style_css = generate_css($reset_button_style, 'smartphone');
$reset_button_md_style_css = generate_css($reset_button_style, 'tablet');
$reset_button_hover_css = generate_css($reset_button_style, '', true);

$input_style_css = generate_css($input_style);
$input_sm_style_css = generate_css($input_style, 'smartphone');
$input_md_style_css = generate_css($input_style, 'tablet');

$slider_style_css = generate_css($slider_style);
$slider_sm_style_css = generate_css($slider_style, 'smartphone');
$slider_md_style_css = generate_css($slider_style, 'tablet');
$slider_progress_style_css = generate_css($slider_style, '', false, false, true);
$slider_thumb_style_css = generate_css($slider_style, '', false, false, false, true);
$slider_tooltip_style_css = generate_css($slider_style, '', false, false, false, false, true);

$filter_word_mobile_css = generate_css($filter_word_mobile);





    switch ( $filter_type ) {
        case 'all':
            $product_selector = esc_attr( $attributes['productSelector'] );
            $pagination_selector = esc_attr( $attributes['paginationSelector'] );
            $output .= '<style>';
            if($form_style_css){$output .= 'form#product-filter {' . $form_style_css . '}';}
            if($container_style_css){$output .= '.filter-group.attributes>div, .filter-group.category, .filter-group.tag, .filter-group.price-range, div#rating {' . $container_style_css . '}';}
            if($widget_title_style_css){$output .= '.filter-group.attributes .title, .filter-group.category .title, .filter-group.tag .title, .filter-group.price-range .title, div#rating .title {' . $widget_title_style_css . '}';}
            if($widget_items_style_css){$output .= '.filter-group.attributes .items, .filter-group.category .items, .filter-group.tag .items, .filter-group.price-range .items, div#rating .items {' . $widget_items_style_css . '    display: flex; flex-direction: column;} label { color: unset !important; }';}
            if($button_style_css){$output .= 'form#product-filter button {' . $button_style_css . '}';}
            if($rating_style_css){$output .= 'form#product-filter i {' . $rating_style_css . '} .dynamic-rating label{' . $rating_style_css . '}';}
            if($reset_button_style_css){$output .= 'form#product-filter span#reset-rating {' . $reset_button_style_css . '}';}
            if($input_style_css){$output .= 'form#product-filter input[type="search"], form#product-filter input[type="number"] {' . $input_style_css . '}';}
            if($slider_style_css){$output .= 'form#product-filter .slider {' . $slider_style_css . '}';}
            if($slider_progress_style_css){$output .= 'form#product-filter .slider .progress {' . $slider_progress_style_css . '}';}
            if($slider_thumb_style_css){$output .= 'form#product-filter input[type="range"]::-webkit-slider-thumb {' . $slider_thumb_style_css . '} input[type="range"]::-moz-range-thumb {' . $slider_thumb_style_css . '}';}
            if($slider_tooltip_style_css){$output .= 'form#product-filter .progress-percentage:before,form#product-filter  .progress-percentage:after {' . $slider_tooltip_style_css . '}';}
            $output .= '
            
 @media screen and (max-width: 576px) {';
            if($form_sm_style_css){$output .= 'form#product-filter {' . $form_sm_style_css . '}';}
            if($container_sm_style_css){$output .= '.filter-group.attributes>div, .filter-group.category, .filter-group.tag, .filter-group.price-range, div#rating {' . $container_sm_style_css . '}';}
            if($widget_sm_title_style_css){$output .= '.filter-group.attributes .title, .filter-group.category .title, .filter-group.tag .title, .filter-group.price-range .title, div#rating .title {' . $widget_sm_title_style_css . '}';}
            if($widget_items_sm_style_css){$output .= '.filter-group.attributes .items, .filter-group.category .items, .filter-group.tag .items, .filter-group.price-range .items, div#rating .items {' . $widget_items_sm_style_css . '}';}
            if($button_sm_style_css){$output .= 'form#product-filter button {' . $button_sm_style_css . '}';}
            if($rating_sm_style_css){$output .= 'form#product-filter i {' . $rating_sm_style_css . '}';}
            if($reset_button_sm_style_css){$output .= 'form#product-filter span#reset-rating {' . $reset_button_sm_style_css . '}';}
            if($input_sm_style_css){$output .= 'form#product-filter input[type="search"], form#product-filter input[type="number"] {' . $input_sm_style_css . '}';}
            if($filter_word_mobile_css){$output .= 'form#product-filter:before {' . $filter_word_mobile_css . '}';}
            $output .= '}';
            $output .= '
            
@media screen and (min-width: 576px) and (max-width: 768px) {';
            if($form_md_style_css){$output .= 'form#product-filter {' . $form_md_style_css . '}';}
            if($container_md_style_css){$output .= '.filter-group.attributes>div, .filter-group.category, .filter-group.tag, .filter-group.price-range, div#rating {' . $container_md_style_css . '}';}
            if($widget_md_title_style_css){$output .= '.filter-group.attributes .title, .filter-group.category .title, .filter-group.tag .title, .filter-group.price-range .title, div#rating .title {' . $widget_md_title_style_css . '}';}
            if($widget_items_md_style_css){$output .= '.filter-group.attributes .items, .filter-group.category .items, .filter-group.tag .items, .filter-group.price-range .items, div#rating .items {' . $widget_items_md_style_css . '}';}
            if($button_md_style_css){$output .= 'form#product-filter button {' . $button_md_style_css . '}';}
            if($rating_md_style_css){$output .= 'form#product-filter i {' . $rating_md_style_css . '}';}
            if($reset_button_md_style_css){$output .= 'form#product-filter span#reset-rating {' . $reset_button_md_style_css . '}';}
            if($input_md_style_css){$output .= 'form#product-filter input[type="search"], form#product-filter input[type="number"] {' . $input_md_style_css . '}';}
            $output .= '}
            
';
            $output .= $custom_css;
            $output .= 'form#product-filter button:hover {' . $button_hover_css . '}';
            $output .= 'form#product-filter span#reset-rating:hover {' . $reset_button_hover_css . '}';
            $output .= 'form#product-filter i:hover,.dynamic-rating input:checked ~ label, .dynamic-rating:not(:checked) label:hover, .dynamic-rating:not(:checked) label:hover ~ label {' . $rating_hover_css . '}';
            $output .= 'form#product-filter i.active,   .dynamic-rating  input:checked + label:hover,
  .dynamic-rating  input:checked ~ label:hover,
  .dynamic-rating  label:hover ~ input:checked ~ label,
  .dynamic-rating  input:checked ~ label:hover ~ label {' . $rating_active_css . '}';
            $output .= '</style>';
            $output .= '<div class="'.$class_name.'">'.do_shortcode( "[plugincy_filters product_selector=\"$product_selector\" pagination_selector=\"$pagination_selector\"]" ).'</div>';
            break;
        case 'single':
            $filter_name = esc_attr( $attributes['filterName'] );
            $output .= '<div class="'.$class_name.'">'.do_shortcode( "[plugincy_filters_single name=\"$filter_name\"]" ).'</div>';
            break;
        case 'selected':
            $output .= '<div class="'.$class_name.'">'.do_shortcode( '[plugincy_filters_selected]' ).'</div>';
            break;
    }

    return $output;
}



// creating blocks for elementor


/**
 * Check if Elementor is installed and active.
 *
 * @return bool True if Elementor is active, false otherwise.
 */
function dapfforwc_is_elementor_active() {
    return defined( 'ELEMENTOR_VERSION' );
}

/**
 * Register the custom Elementor widget if Elementor is active.
 */
function dapfforwc_register_dynamic_ajax_filter_widget_elementor() {
    if ( ! dapfforwc_is_elementor_active() ) {
        error_log( "Elementor is not installed or active." );
        return;
    }

    // Define the custom widget class
    class Dapfforwc_Dynamic_Ajax_Filter_Widget extends \Elementor\Widget_Base {

        public function get_name() {
            return 'dynamic_ajax_filter';
        }

        public function get_title() {
            return __( 'Dynamic Ajax Filter', 'dynamic-ajax-product-filters-for-woocommerce' );
        }

        public function get_icon() {
            return 'eicon-taxonomy-filter';
        }

        public function get_categories() {
            return [ 'general' ];
        }

        protected function _register_controls() {
            // Content Tab: Filter Options
            $this->start_controls_section(
                'filter_options',
                [
                    'label' => __( 'Filter Options', 'dynamic-ajax-product-filters-for-woocommerce' ),
                ]
            );
        
            $this->add_control(
                'filter_type',
                [
                    'label'   => __( 'Select Filter Type', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'    => \Elementor\Controls_Manager::SELECT,
                    'options' => [
                        'all'      => __( 'All Filters', 'dynamic-ajax-product-filters-for-woocommerce' ),
                        'single'   => __( 'Single Filter', 'dynamic-ajax-product-filters-for-woocommerce' ),
                        'selected' => __( 'Selected Filters', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    ],
                    'default' => 'all',
                ]
            );
        
            $this->add_control(
                'product_selector',
                [
                    'label' => __( 'Product Selector', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => '',
                    'condition' => [
                        'filter_type' => 'all',
                    ],
                ]
            );
        
            $this->add_control(
                'pagination_selector',
                [
                    'label' => __( 'Pagination Selector', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => '',
                    'condition' => [
                        'filter_type' => 'all',
                    ],
                ]
            );
        
            $this->add_control(
                'filter_name',
                [
                    'label' => __( 'attribute id', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => '',
                    'condition' => [
                        'filter_type' => 'single',
                    ],
                ]
            );
        
            $this->end_controls_section();

            $this->start_controls_section(
                'filters_mobile_style_section',
                [
                    'label' => __( 'Filters Word (Mobile)', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
                    'condition' => [
                        'filter_type' => 'all',
                    ],
                ]
            );
            
            $this->add_control(
                'filters_word_visibility',
                [
                    'label'        => __( 'Show Filters Word on Mobile', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'         => \Elementor\Controls_Manager::SWITCHER,
                    'label_on'     => __( 'Hide', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'label_off'    => __( 'Show', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'return_value' => 'none',
                    'default'      => 'block',
                    'selectors'    => [
                        '#product-filter:before' => 'display: {{VALUE}};',
                    ],
                ]
            );


            
            $this->end_controls_section();

             // Form styles
             $this->start_controls_section(
                'form_style_section',
                [
                    'label' => __( 'Form Styles', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
                    'condition' => [
                        'filter_type' => 'all',
                    ],
                ]
            );
    
            $this->add_responsive_control(
                'form_background',
                [
                    'label'     => __( 'Background', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        'form#product-filter' => 'background-color: {{VALUE}};',
                    ],
                ]
            );
            $this->add_responsive_control(
                'form_border_radius',
                [
                    'label'      => __( 'Border Radius', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%' ],
                    'selectors'  => [
                        'form#product-filter' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
        
            $this->add_responsive_control(
                'form_padding',
                [
                    'label'      => __( 'Padding', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors'  => [
                        'form#product-filter' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
        
            $this->add_responsive_control(
                'form_margin',
                [
                    'label'      => __( 'Margin', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors'  => [
                        'form#product-filter' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
        
            $this->add_group_control(
                \Elementor\Group_Control_Box_Shadow::get_type(),
                [
                    'name'      => 'form_box_shadow',
                    'label'     => __( 'Box Shadow', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'selector'  => 'form#product-filter',
                ]
            );
        
            $this->add_responsive_control(
                'form_height',
                [
                    'label'      => __( 'Form Height', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'       => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => [ 'px', '%', 'em', 'vh' ],
                    'range'      => [
                        'px' => [
                            'min' => 0,
                            'max' => 1000,
                        ],
                        '%'  => [
                            'min' => 0,
                            'max' => 100,
                        ],
                        'em' => [
                            'min' => 0,
                            'max' => 50,
                        ],
                        'vh' => [
                            'min' => 0,
                            'max' => 100,
                        ],
                    ],
                    'selectors'  => [
                        'form#product-filter' => 'height: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );
            
        
            $this->end_controls_section();

            // widget container style
            $this->start_controls_section(
                'container_style_section',
                [
                    'label' => __( 'Container Styles', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
                    'condition' => [
                        'filter_type' => 'all',
                    ],
                ]
            );
        
            $this->add_control(
                'container_background',
                [
                    'label'     => __( 'Background', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '.filter-group.attributes>div, .filter-group.category, .filter-group.tag, .filter-group.price-range, div#rating' => 'background-color: {{VALUE}};',
                    ],
                ]
            );
            $this->add_responsive_control(
                'container_border_radius',
                [
                    'label'      => __( 'Border Radius', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%' ],
                    'selectors'  => [
                        '.filter-group.attributes>div, .filter-group.category, .filter-group.tag, .filter-group.price-range, div#rating' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
        
            $this->add_responsive_control(
                'container_padding',
                [
                    'label'      => __( 'Padding', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors'  => [
                        '.filter-group.attributes>div, .filter-group.category, .filter-group.tag, .filter-group.price-range, div#rating' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
        
            $this->add_responsive_control(
                'container_margin',
                [
                    'label'      => __( 'Margin', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors'  => [
                        '.filter-group.attributes>div, .filter-group.category, .filter-group.tag, .filter-group.price-range, div#rating' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
        
            $this->add_group_control(
                \Elementor\Group_Control_Box_Shadow::get_type(),
                [
                    'name'      => 'container_box_shadow',
                    'label'     => __( 'Box Shadow', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'selector'  => '.filter-group.attributes>div, .filter-group.category, .filter-group.tag, .filter-group.price-range, div#rating',
                ]
            );
        
            $this->add_control(
                'container_overflow',
                [
                    'label'     => __( 'Overflow', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'      => \Elementor\Controls_Manager::SELECT,
                    'options'   => [
                        'visible' => __( 'Visible', 'dynamic-ajax-product-filters-for-woocommerce' ),
                        'hidden'  => __( 'Hidden', 'dynamic-ajax-product-filters-for-woocommerce' ),
                        'scroll'  => __( 'Scroll', 'dynamic-ajax-product-filters-for-woocommerce' ),
                        'auto'    => __( 'Auto', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    ],
                    'default'   => 'visible',
                    'selectors' => [
                        '.filter-group.attributes>div, .filter-group.category, .filter-group.tag, .filter-group.price-range, div#rating' => 'overflow: {{VALUE}};',
                    ],
                ]
            );
        
            $this->end_controls_section();
        
        
            // Style Tab: Widget Title (All Filters)
            $this->start_controls_section(
                'title_styles',
                [
                    'label' => __( 'Widget Title Styles', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                    'condition' => [
                        'filter_type' => 'all',
            ],
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Background::get_type(),
                [
                    'name'     => 'widget_title_background',
                    'label'    => __( 'Background', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'types'    => [ 'classic', 'gradient' ],
                    'selector' => '{{WRAPPER}} .filter-group.attributes .title,{{WRAPPER}} .filter-group.category .title,{{WRAPPER}} .filter-group.tag .title,{{WRAPPER}} .filter-group.price-range .title,{{WRAPPER}} div#rating .title',
                ]
            );
        
            $this->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                [
                    'name' => 'widget_title_typography',
                    'label' => __( 'Typography', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'selector' => '{{WRAPPER}} .filter-group .title',
                ]
            );
        
            $this->add_control(
                'widget_title_color',
                [
                    'label' => __( 'Text Color', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .filter-group .title' => 'color: {{VALUE}} !important;',
                    ],
                ]
            );

            $this->add_responsive_control(
                'widget_title_radius',
                [
                    'label'      => __( 'Border Radius', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%' ],
                    'selectors'  => [
                        '{{WRAPPER}} .filter-group .title' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
        
            $this->add_responsive_control(
                'widget_title_alignment',
                [
                    'label' => __( 'Text Align', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type' => \Elementor\Controls_Manager::CHOOSE,
                    'options' => [
                        'left' => [
                            'title' => __( 'Left', 'dynamic-ajax-product-filters-for-woocommerce' ),
                            'icon' => 'eicon-text-align-left',
                        ],
                        'center' => [
                            'title' => __( 'Center', 'dynamic-ajax-product-filters-for-woocommerce' ),
                            'icon' => 'eicon-text-align-center',
                        ],
                        'space-between' => [
                            'title' => __( 'space-between', 'dynamic-ajax-product-filters-for-woocommerce' ),
                            'icon' => 'eicon-justify-space-between-h',
                        ],
                        'right' => [
                            'title' => __( 'Right', 'dynamic-ajax-product-filters-for-woocommerce' ),
                            'icon' => 'eicon-text-align-right',
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .filter-group .title' => 'text-align: {{VALUE}} !important; justify-content: {{VALUE}} !important;',
                    ],
                ]
            );
        
            $this->add_responsive_control(
                'widget_title_padding',
                [
                    'label' => __( 'Padding', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'selectors' => [
                        '{{WRAPPER}} .filter-group .title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
        
            $this->add_responsive_control(
                'widget_title_margin',
                [
                    'label' => __( 'Margin', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'selectors' => [
                        '{{WRAPPER}} .filter-group .title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
        
            $this->end_controls_section();
        
            // Style Tab: Widget Items (All Filters)
            $this->start_controls_section(
                'items_styles',
                [
                    'label' => __( 'Widget Items Styles', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                    'condition' => [
                        'filter_type' => 'all',
                    ],
                ]
            );
        
            $this->add_control(
                'widget_items_background_color',
                [
                    'label' => __( 'Background Color', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .items' => 'background-color: {{VALUE}};',
                    ],
                ]
            );
        
            $this->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                [
                    'name' => 'widget_items_typography',
                    'label' => __( 'Typography', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'selector' => '{{WRAPPER}} .items label',
                ]
            );
        
            $this->add_control(
                'widget_items_color',
                [
                    'label' => __( 'Text Color', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .items label, .price-input span,.price-input .separator' => 'color: {{VALUE}};',
                    ],
                ]
            );
        
            $this->add_responsive_control(
                'widget_items_padding',
                [
                    'label' => __( 'Padding', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'selectors' => [
                        '{{WRAPPER}} .items' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
        
            $this->add_responsive_control(
                'widget_items_margin',
                [
                    'label' => __( 'Margin', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'selectors' => [
                        '{{WRAPPER}} .items' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_responsive_control(
                'widget_items_radius',
                [
                    'label'      => __( 'Border Radius', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%' ],
                    'selectors'  => [
                        '{{WRAPPER}} .items' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
        
            $this->add_responsive_control(
                'widget_items_gap',
                [
                    'label' => __( 'Gap', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'selectors' => [
                        '{{WRAPPER}} .items' => 'display: flex ; flex-direction: column; gap: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );

        
            $this->end_controls_section();

            // button style

            $this->start_controls_section(
                'section_button_style',
                [
                    'label' => __( 'Button Style', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
                    'condition' => [
                        'filter_type' => 'all',
                    ],
                ]
            );
            
            $this->add_group_control(
                \Elementor\Group_Control_Background::get_type(),
                [
                    'name'     => 'button_background',
                    'label'    => __( 'Background', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'types'    => [ 'classic', 'gradient' ],
                    'selector' => 'form#product-filter button',
                ]
            );
            
            $this->add_control(
                'button_text_color',
                [
                    'label'     => __( 'Text Color', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        'form#product-filter button' => 'color: {{VALUE}};',
                    ],
                ]
            );
            
            $this->add_control(
                'button_hover_background',
                [
                    'label'     => __( 'Hover Background', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        'form#product-filter button:hover' => 'background-color: {{VALUE}};',
                    ],
                ]
            );
            
            $this->add_control(
                'button_hover_text_color',
                [
                    'label'     => __( 'Hover Text Color', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        'form#product-filter button:hover' => 'color: {{VALUE}};',
                    ],
                ]
            );
            
            $this->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                    'name'     => 'button_border',
                    'label'    => __( 'Border', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'selector' => 'form#product-filter button',
                ]
            );
            
            $this->add_responsive_control(
                'button_padding',
                [
                    'label'      => __( 'Padding', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors'  => [
                        'form#product-filter button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
            
            $this->add_responsive_control(
                'button_margin',
                [
                    'label'      => __( 'Margin', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors'  => [
                        'form#product-filter button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
            $this->add_responsive_control(
                'button_radius',
                [
                    'label'      => __( 'Border Radius', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%' ],
                    'selectors'  => [
                        'form#product-filter button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
            
            $this->end_controls_section();

            // rating style
            $this->start_controls_section(
                'section_rating_style',
                [
                    'label' => __( 'Rating Style', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
                    'condition' => [
                        'filter_type' => 'all',
                    ],
                ]
            );
            
            $this->add_responsive_control(
                'rating_size',
                [
                    'label'      => __( 'Rating Size', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'       => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => [ 'px', 'em', '%' ],
                    'range'      => [
                        'px' => [
                            'min' => 10,
                            'max' => 100,
                        ],
                    ],
                    'selectors'  => [
                        '{{WRAPPER}} .dynamic-rating  label:before, .items.rating i' => 'font-size: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );
            $this->add_control(
                'rating_inactive_color',
                [
                    'label'     => __( 'Inactive Color', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .dynamic-rating label, .items.rating i' => 'color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_control(
                'rating_active_color',
                [
                    'label'     => __( 'Active Color', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .dynamic-rating  input:checked + label:hover,{{WRAPPER}} .dynamic-rating  input:checked ~ label:hover,{{WRAPPER}} .dynamic-rating  label:hover ~ input:checked ~ label,{{WRAPPER}} .dynamic-rating  input:checked ~ label:hover ~ label, .items.rating input:checked  + .stars i' => 'color: {{VALUE}};',
                    ],
                ]
            );
            
            $this->add_control(
                'rating_hover_color',
                [
                    'label'     => __( 'Hover Color', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .dynamic-rating input:checked ~ label,{{WRAPPER}} .dynamic-rating:not(:checked) label:hover,{{WRAPPER}} .dynamic-rating:not(:checked) label:hover ~ label, .items.rating input:hover  + .stars i' => 'color: {{VALUE}};',
                    ],
                ]
            );
            
            $this->add_responsive_control(
                'rating_gap',
                [
                    'label'      => __( 'Gap', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'       => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => [ 'px', 'em', '%' ],
                    'range'      => [
                        'px' => [
                            'min' => 0,
                            'max' => 50,
                        ],
                    ],
                    'selectors'  => [
                        '{{WRAPPER}} .dynamic-rating  label:before, .items.rating i ' => 'margin: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );
            
            $this->end_controls_section();

            // reset button style
            $this->start_controls_section(
                'section_reset_button_style',
                [
                    'label' => __( 'Reset Button Style', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
                    'condition' => [
                        'filter_type' => 'all',
                    ],
                ]
            );
            
            // Background color
            $this->add_group_control(
                \Elementor\Group_Control_Background::get_type(),
                [
                    'name'     => 'reset_button_background',
                    'label'    => __( 'Background', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'types'    => [ 'classic', 'gradient' ],
                    'selector' => 'form#product-filter span#reset-rating',
                ]
            );
            // Inside the Reset Button Style section
            $this->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                [
                    'name'     => 'reset_button_typography',
                    'label'    => __( 'Typography', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'selector' => 'form#product-filter span#reset-rating',
                ]
            );

            
            // Text color
            $this->add_control(
                'reset_button_text_color',
                [
                    'label'     => __( 'Text Color', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        'form#product-filter span#reset-rating' => 'color: {{VALUE}} !important;',
                    ],
                ]
            );
            
            
            // Hover background color
            $this->add_control(
                'reset_button_hover_background',
                [
                    'label'     => __( 'Hover Background', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        'form#product-filter span#reset-rating:hover' => 'background-color: {{VALUE}};',
                    ],
                ]
            );
            
            // Hover text color
            $this->add_control(
                'reset_button_hover_text_color',
                [
                    'label'     => __( 'Hover Text Color', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        'form#product-filter span#reset-rating:hover' => 'color: {{VALUE}}!important;',
                    ],
                ]
            );
            
            // Border
            $this->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                    'name'     => 'reset_button_border',
                    'label'    => __( 'Border', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'selector' => 'form#product-filter span#reset-rating',
                ]
            );
            
            // Padding
            $this->add_responsive_control(
                'reset_button_padding',
                [
                    'label'      => __( 'Padding', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors'  => [
                        'form#product-filter span#reset-rating' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
            
            // Margin
            $this->add_responsive_control(
                'reset_button_margin',
                [
                    'label'      => __( 'Margin', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors'  => [
                        'form#product-filter span#reset-rating' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
            
            $this->end_controls_section();

            // input style
            $this->start_controls_section(
                'section_input_style',
                [
                    'label' => __( 'Input Style', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
                    'condition' => [
                        'filter_type' => 'all',
                    ],
                ]
            );
            
            // Background color
            $this->add_control(
                'input_background_color',
                [
                    'label'     => __( 'Background Color', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        'form#product-filter input[type="search"], form#product-filter input[type="number"]' => 'background-color: {{VALUE}};',
                    ],
                ]
            );
            
            // Text color
            $this->add_control(
                'input_text_color',
                [
                    'label'     => __( 'Text Color', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        'form#product-filter input[type="search"], form#product-filter input[type="number"]' => 'color: {{VALUE}};',
                    ],
                ]
            );
            
            // Padding
            $this->add_responsive_control(
                'input_padding',
                [
                    'label'      => __( 'Padding', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors'  => [
                        'form#product-filter input[type="search"], form#product-filter input[type="number"]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
            
            // Margin
            $this->add_responsive_control(
                'input_margin',
                [
                    'label'      => __( 'Margin', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors'  => [
                        'form#product-filter input[type="search"], form#product-filter input[type="number"]' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
            
            // Border radius
            $this->add_responsive_control(
                'input_border_radius',
                [
                    'label'      => __( 'Border Radius', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%' ],
                    'selectors'  => [
                        'form#product-filter input[type="search"], form#product-filter input[type="number"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
            
            // Border
            $this->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                    'name'     => 'input_border',
                    'label'    => __( 'Border', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'selector' => 'form#product-filter input[type="search"], form#product-filter input[type="number"]',
                ]
            );
            
            $this->end_controls_section();
            // price slider
            $this->start_controls_section(
                'slider_style',
                [
                    'label' => __( 'Slider', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
                    'condition' => [
                        'filter_type' => 'all',
                    ],
                ]
            );
            
            // Slider background
            $this->add_control(
                'slider_background',
                [
                    'label'     => __( 'Slider Background', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'default'   => '#ddd',
                    'selectors' => [
                        '.slider' => 'background: {{VALUE}};',
                    ],
                ]
            );
            
            // Slider border radius
            $this->add_control(
                'slider_border_radius',
                [
                    'label'      => __( 'Slider Border Radius', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'       => \Elementor\Controls_Manager::SLIDER,
                    'range'      => [
                        'px' => [
                            'min' => 0,
                            'max' => 50,
                        ],
                    ],
                    'default'    => [
                        'size' => 5,
                    ],
                    'selectors'  => [
                        '.slider' => 'border-radius: {{SIZE}}px;',
                    ],
                ]
            );
            
            // Progress bar background
            $this->add_control(
                'progress_background',
                [
                    'label'     => __( 'Progress Background', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'default'   => '#432fb8',
                    'selectors' => [
                        '.slider .progress' => 'background: {{VALUE}};',
                    ],
                ]
            );
            
            // Progress bar border radius
            $this->add_control(
                'progress_border_radius',
                [
                    'label'      => __( 'Progress Border Radius', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'       => \Elementor\Controls_Manager::SLIDER,
                    'range'      => [
                        'px' => [
                            'min' => 0,
                            'max' => 50,
                        ],
                    ],
                    'default'    => [
                        'size' => 5,
                    ],
                    'selectors'  => [
                        '.slider .progress' => 'border-radius: {{SIZE}}px;',
                    ],
                ]
            );
            
            // Thumb margin
            $this->add_responsive_control(
                'thumb_margin',
                [
                    'label' => __( 'Margin', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'selectors' => [
                        'input[type="range"]::-webkit-slider-thumb' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        'input[type="range"]::-moz-range-thumb'    => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
            
            // Thumb width
            $this->add_control(
                'thumb_width',
                [
                    'label'      => __( 'Thumb Size', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'       => \Elementor\Controls_Manager::SLIDER,
                    'range'      => [
                        'px' => [
                            'min' => 5,
                            'max' => 50,
                        ],
                    ],
                    'default'    => [
                        'size' => 17,
                    ],
                    'selectors'  => [
                        'input[type="range"]::-webkit-slider-thumb'=> 'width: {{SIZE}}px; height: {{SIZE}}px;', 
                        'input[type="range"]::-moz-range-thumb' => 'width: {{SIZE}}px; height: {{SIZE}}px;',
                    ],
                ]
            );
            
            // Thumb background
            $this->add_control(
                'thumb_background',
                [
                    'label'     => __( 'Thumb Background', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'default'   => '#432fb8',
                    'selectors' => [
                        'input[type="range"]::-webkit-slider-thumb'=> 'background:{{VALUE}};', 
                        'input[type="range"]::-moz-range-thumb' => 'background: {{VALUE}};',
                    ],
                ]
            );
            
            // Tooltip background
            $this->add_control(
                'tooltip_background',
                [
                    'label'     => __( 'Tooltip Background', 'dynamic-ajax-product-filters-for-woocommerce' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'default'   => 'red',
                    'selectors' => [
                        '.progress-percentage:before, .progress-percentage:after' => 'background: {{VALUE}};',
                    ],
                ]
            );
            
            $this->end_controls_section();
            
            
            

             // Style Tab: Active & Inactive Items (Single Filter)
    $this->start_controls_section(
        'single_filter_styles',
        [
            'label' => __( 'Single Filter Styles', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => [
                'filter_type' => 'single',
            ],
        ]
    );

    $this->add_control(
        'inactive_item_background',
        [
            'label' => __( 'Inactive Background Color', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rfilterbuttons li' => 'background-color: {{VALUE}};',
            ],
        ]
    );
    $this->add_control(
        'inactive_item_color',
        [
            'label' => __( 'Inactive Text Color', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rfilterbuttons ul li label' => 'color: {{VALUE}} !important;',
            ],
        ]
    );
    $this->add_control(
        'active_item_background',
        [
            'label' => __( 'Active Background Color', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rfilterbuttons ul li.checked' => 'background-color: {{VALUE}};',
            ],
        ]
    );
    $this->add_control(
        'active_item_color',
        [
            'label' => __( 'Active Text Color', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rfilterbuttons ul li.checked label' => 'color: {{VALUE}} !important;',
            ],
        ]
    );


    $this->add_control(
        'inactive_item_hover_color',
        [
            'label' => __( 'Inactive Hover Color', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rfilterbuttons li:hover' => 'color: {{VALUE}}',
                '{{WRAPPER}} .rfilterbuttons li:hover label' => 'color: {{VALUE}} !important;',
            ],
        ]
    );
    $this->add_group_control(
        \Elementor\Group_Control_Typography::get_type(),
        [
            'name' => 'inactive_item_typography',
            'label' => __( 'Typography', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'selector' => '{{WRAPPER}} .rfilterbuttons ul li',
        ]
    );

    $this->add_control(
        'inactive_item_color',
        [
            'label' => __( 'Text Color', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rfilterbuttons ul li label' => 'color: {{VALUE}} !important;',
            ],
        ]
    );

    $this->add_responsive_control(
        'inactive_item_padding',
        [
            'label' => __( 'Padding', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'selectors' => [
                '{{WRAPPER}} .rfilterbuttons ul li' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]
    );

    $this->add_responsive_control(
        'inactive_item_margin',
        [
            'label' => __( 'Margin', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'selectors' => [
                '{{WRAPPER}} .rfilterbuttons ul li' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]
    );
    $this->add_responsive_control(
        'inactive_item_gap',
        [
            'label' => __( 'Gap', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'selectors' => [
                '{{WRAPPER}} .rfilterbuttons ul' => 'gap: {{SIZE}}{{UNIT}};',
            ],
        ]
    );

    $this->add_control(
        'inactive_item_overflow',
        [
            'label' => __( 'Overflow', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'visible' => __( 'Visible', 'dynamic-ajax-product-filters-for-woocommerce' ),
                'hidden'  => __( 'Hidden', 'dynamic-ajax-product-filters-for-woocommerce' ),
                'scroll'  => __( 'Scroll', 'dynamic-ajax-product-filters-for-woocommerce' ),
                'auto'    => __( 'Auto', 'dynamic-ajax-product-filters-for-woocommerce' ),
            ],
            'default' => 'visible',
            'selectors' => [
                '{{WRAPPER}} .rfilterbuttons ul' => 'overflow-x: {{VALUE}};overflow-y: hidden;',
            ],
        ]
    );
    $this->add_control(
        'inactive_item_flex_wrap',
        [
            'label' => __( 'Flex Wrap', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'nowrap'  => __( 'No Wrap', 'dynamic-ajax-product-filters-for-woocommerce' ),
                'wrap'    => __( 'Wrap', 'dynamic-ajax-product-filters-for-woocommerce' ),
                'wrap-reverse' => __( 'Wrap Reverse', 'dynamic-ajax-product-filters-for-woocommerce' ),
            ],
            'default' => 'wrap',
            'selectors' => [
                '{{WRAPPER}} .rfilterbuttons ul' => 'flex-wrap: {{VALUE}};',
            ],
        ]
    );

    $this->end_controls_section();

    // Style Tab: Selected Filters (Selected Filter)
    $this->start_controls_section(
        'selected_filter_styles',
        [
            'label' => __( 'Selected Filter Styles', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => [
                'filter_type' => 'selected',
            ],
        ]
    );

    $this->add_control(
        'selected_filter_background',
        [
            'label' => __( 'Background Color', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rfilterselected ul li.checked' => 'background-color: {{VALUE}};',
            ],
        ]
    );
    $this->add_group_control(
        \Elementor\Group_Control_Typography::get_type(),
        [
            'name' => 'selected_filter_typography',
            'label' => __( 'Typography', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'selector' => '{{WRAPPER}} .rfilterselected ul li.checked',
        ]
    );

    $this->add_control(
        'selected_filter_color',
        [
            'label' => __( 'Text Color', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rfilterselected ul li.checked label' => 'color: {{VALUE}} !important;',
            ],
        ]
    );

    $this->add_responsive_control(
        'selected_filter_padding',
        [
            'label' => __( 'Padding', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'selectors' => [
                '{{WRAPPER}} .rfilterselected ul li.checked' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]
    );

    $this->add_responsive_control(
        'selected_filter_margin',
        [
            'label' => __( 'Margin', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'selectors' => [
                '{{WRAPPER}} .rfilterselected ul' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]
    );
    $this->add_responsive_control(
        'selected_filter_radius',
        [
            'label'      => __( 'Border Radius', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%' ],
            'selectors'  => [
                '.rfilterselected li' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]
    );
    $this->add_responsive_control(
        'selected_filter_gap',
        [
            'label' => __( 'Gap', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'selectors' => [
                '{{WRAPPER}} .rfilterselected ul' => 'gap: {{SIZE}}{{UNIT}};',
            ],
        ]
    );

    $this->add_control(
        'selected_filter_overflow',
        [
            'label' => __( 'Overflow', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'visible' => __( 'Visible', 'dynamic-ajax-product-filters-for-woocommerce' ),
                'hidden'  => __( 'Hidden', 'dynamic-ajax-product-filters-for-woocommerce' ),
                'scroll'  => __( 'Scroll', 'dynamic-ajax-product-filters-for-woocommerce' ),
                'auto'    => __( 'Auto', 'dynamic-ajax-product-filters-for-woocommerce' ),
            ],
            'default' => 'visible',
            'selectors' => [
                '{{WRAPPER}} .rfilterselected>div' => 'overflow-x: {{VALUE}};overflow-y: hidden;',
            ],
        ]
    );

    $this->add_responsive_control(
        'selected_filter_height',
        [
            'label'      => __( 'Height', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => [ 'px', '%', 'em', 'vh' ],
            'range'      => [
                'px' => [
                    'min' => 0,
                    'max' => 1000,
                ],
                '%'  => [
                    'min' => 0,
                    'max' => 100,
                ],
                'em' => [
                    'min' => 0,
                    'max' => 50,
                ],
                'vh' => [
                    'min' => 0,
                    'max' => 100,
                ],
            ],
            'selectors'  => [
                '{{WRAPPER}} .rfilterselected>div' => 'height: {{SIZE}}{{UNIT}};',
            ],
        ]
    );
    $this->add_control(
        'selected_filter_flex_wrap',
        [
            'label' => __( 'Flex Wrap', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'nowrap'  => __( 'No Wrap', 'dynamic-ajax-product-filters-for-woocommerce' ),
                'wrap'    => __( 'Wrap', 'dynamic-ajax-product-filters-for-woocommerce' ),
                'wrap-reverse' => __( 'Wrap Reverse', 'dynamic-ajax-product-filters-for-woocommerce' ),
            ],
            'default' => 'wrap',
            'selectors' => [
                '{{WRAPPER}} .rfilterselected ul' => 'flex-wrap: {{VALUE}};',
            ],
        ]
    );

    // position manage

    $this->add_control(
        'selected_filter_position',
        [
            'label'   => __( 'Position', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'default'  => __( 'Default', 'dynamic-ajax-product-filters-for-woocommerce' ),
                'absolute' => __( 'Absolute', 'dynamic-ajax-product-filters-for-woocommerce' ),
                'fixed'    => __( 'Fixed', 'dynamic-ajax-product-filters-for-woocommerce' ),
                'sticky'   => __( 'Sticky', 'dynamic-ajax-product-filters-for-woocommerce' ),
            ],
            'default' => 'default',
            'selectors' => [
                '{{WRAPPER}}' => 'position: {{VALUE}};',
            ],
        ]
    );
    
    $this->add_responsive_control(
        'selected_filter_orientation',
        [
            'label'      => __( 'Vertical Orientation', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'type'       => \Elementor\Controls_Manager::SELECT,
            'options'    => [
                'top'    => __( 'Top', 'dynamic-ajax-product-filters-for-woocommerce' ),
                'bottom' => __( 'Bottom', 'dynamic-ajax-product-filters-for-woocommerce' ),
            ],
            'condition'  => [
                'selected_filter_position!' => 'default',
            ],
        ]
    );
    
    $this->add_responsive_control(
        'selected_filter_offset',
        [
            'label'      => __( 'Vertical Offset', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => [ 'px', '%', 'em', 'vh' ],
            'range'      => [
                'px' => [
                    'min' => 0,
                    'max' => 1000,
                ],
            ],
            'default'    => [
                'unit' => 'px',
                'size' => 0,
            ],
            'selectors'  => [
                '{{WRAPPER}}' => '{{selected_filter_orientation.VALUE}}: {{SIZE}}{{UNIT}};',
            ],
            'condition'  => [
                'selected_filter_position!' => 'default',
            ],
        ]
    );
    
    $this->add_responsive_control(
        'selected_filter_horizontal_orientation',
        [
            'label'      => __( 'Horizontal Orientation', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'type'       => \Elementor\Controls_Manager::SELECT,
            'options'    => [
                'left'  => __( 'Left', 'dynamic-ajax-product-filters-for-woocommerce' ),
                'right' => __( 'Right', 'dynamic-ajax-product-filters-for-woocommerce' ),
            ],
            'condition'  => [
                'selected_filter_position!' => 'default',
            ],
        ]
    );
    
    $this->add_responsive_control(
        'selected_filter_horizontal_offset',
        [
            'label'      => __( 'Horizontal Offset', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => [ 'px', '%', 'em', 'vw' ],
            'range'      => [
                'px' => [
                    'min' => 0,
                    'max' => 1000,
                ],
            ],
            'default'    => [
                'unit' => 'px',
                'size' => 0,
            ],
            'selectors'  => [
                '{{WRAPPER}}' => '{{selected_filter_horizontal_orientation.VALUE}}: {{SIZE}}{{UNIT}};',
            ],
            'condition'  => [
                'selected_filter_position!' => 'default',
            ],
        ]
    );
    
    $this->add_control(
        'selected_filter_z_index',
        [
            'label'     => __( 'Z-Index', 'dynamic-ajax-product-filters-for-woocommerce' ),
            'type'      => \Elementor\Controls_Manager::NUMBER,
            'default'   => '',
            'selectors' => [
                '{{WRAPPER}}' => 'z-index: {{VALUE}};',
            ]
        ]
    );
    

    $this->end_controls_section();

        }
        

        protected function render() {
            $settings = $this->get_settings_for_display();
            $output = '';

            switch ( $settings['filter_type'] ) {
                case 'all':
                    $product_selector = esc_attr( $settings['product_selector'] );
                    $pagination_selector = esc_attr( $settings['pagination_selector'] );
                    $output .= do_shortcode( "[plugincy_filters product_selector=\"$product_selector\" pagination_selector=\"$pagination_selector\"]" );
                    break;

                case 'single':
                    $filter_name = esc_attr( $settings['filter_name'] );
                    $output .= do_shortcode( "[plugincy_filters_single name=\"$filter_name\"]" );
                    break;

                case 'selected':
                    $output .= do_shortcode( "[plugincy_filters_selected]" );
                    break;
            }

            echo $output;
        }
    }

    // Register the custom widget
    \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Dapfforwc_Dynamic_Ajax_Filter_Widget() );
}
add_action( 'elementor/widgets/register', 'dapfforwc_register_dynamic_ajax_filter_widget_elementor' );
