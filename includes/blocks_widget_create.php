<?php

if (!defined('ABSPATH')) {
    exit;
}

class Dapfforwc_Dynamic_Ajax_Filter_Widget_Wp_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'dapfforwc_dynamic_ajax_filter_widget_wp_widget',
            __( 'Dynamic Ajax Filter', 'dapfforwc' ),
            [ 'description' => __( 'A widget for dynamic AJAX filtering.', 'dapfforwc' ) ]
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
               <a href="#general-tab" class="active button"><?php _e( 'General', 'text_domain' ); ?></a>
                <a href="#style-tab" class="button"><?php _e( 'Style', 'text_domain' ); ?></a>
                <a href="#advanced-tab" class="button"><?php _e( 'Advanced', 'text_domain' ); ?></a>
            </div>

            <!-- General Tab -->
            <div class="dapfforwc-tab-content active">
                <p>
                    <label for="<?php echo $this->get_field_id( 'filter_type' ); ?>"><?php _e( 'Select Filter Type:', 'dapfforwc' ); ?></label>
                    <select class="widefat" id="<?php echo $this->get_field_id( 'filter_type' ); ?>" name="<?php echo $this->get_field_name( 'filter_type' ); ?>">
                        <option value="all" <?php selected( $filter_type, 'all' ); ?>><?php _e( 'All Filters', 'dapfforwc' ); ?></option>
                        <option value="single" <?php selected( $filter_type, 'single' ); ?>><?php _e( 'Single Filter', 'dapfforwc' ); ?></option>
                        <option value="selected" <?php selected( $filter_type, 'selected' ); ?>><?php _e( 'Selected Filters', 'dapfforwc' ); ?></option>
                    </select>
                </p>
                <p>
                    <label for="<?php echo $this->get_field_id( 'product_selector' ); ?>"><?php _e( 'Product Selector (for All Filters):', 'dapfforwc' ); ?></label>
                    <input class="widefat" id="<?php echo $this->get_field_id( 'product_selector' ); ?>" name="<?php echo $this->get_field_name( 'product_selector' ); ?>" type="text" value="<?php echo esc_attr( $product_selector ); ?>">
                </p>
                <p>
                    <label for="<?php echo $this->get_field_id( 'pagination_selector' ); ?>"><?php _e( 'Pagination Selector (for All Filters):', 'dapfforwc' ); ?></label>
                    <input class="widefat" id="<?php echo $this->get_field_id( 'pagination_selector' ); ?>" name="<?php echo $this->get_field_name( 'pagination_selector' ); ?>" type="text" value="<?php echo esc_attr( $pagination_selector ); ?>">
                </p>
            </div>

            <!-- Style Tab -->
            <div class="dapfforwc-tab-content">
                <p>
                    <label for="<?php echo $this->get_field_id( 'custom_style' ); ?>"><?php _e( 'Custom CSS:', 'dapfforwc' ); ?></label>
                    <textarea class="widefat" id="<?php echo $this->get_field_id( 'custom_style' ); ?>" name="<?php echo $this->get_field_name( 'custom_style' ); ?>"><?php echo esc_attr( $custom_style ); ?></textarea>
                </p>
            </div>

            <!-- Advanced Tab -->
            <div class="dapfforwc-tab-content">
                <p>
                    <label for="<?php echo $this->get_field_id( 'advanced_option' ); ?>"><?php _e( 'Advanced Option:', 'dapfforwc' ); ?></label>
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
            return __( 'Dynamic Ajax Filter', 'dapfforwc' );
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
                    'label' => __( 'Filter Options', 'dapfforwc' ),
                ]
            );
        
            $this->add_control(
                'filter_type',
                [
                    'label'   => __( 'Select Filter Type', 'dapfforwc' ),
                    'type'    => \Elementor\Controls_Manager::SELECT,
                    'options' => [
                        'all'      => __( 'All Filters', 'dapfforwc' ),
                        'single'   => __( 'Single Filter', 'dapfforwc' ),
                        'selected' => __( 'Selected Filters', 'dapfforwc' ),
                    ],
                    'default' => 'all',
                ]
            );
        
            $this->add_control(
                'product_selector',
                [
                    'label' => __( 'Product Selector', 'dapfforwc' ),
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
                    'label' => __( 'Pagination Selector', 'dapfforwc' ),
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
                    'label' => __( 'attribute id', 'dapfforwc' ),
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
                    'label' => __( 'Filters Word (Mobile)', 'dapfforwc' ),
                    'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
                    'condition' => [
                        'filter_type' => 'all',
                    ],
                ]
            );
            
            $this->add_control(
                'filters_word_visibility',
                [
                    'label'        => __( 'Show Filters Word on Mobile', 'dapfforwc' ),
                    'type'         => \Elementor\Controls_Manager::SWITCHER,
                    'label_on'     => __( 'Hide', 'dapfforwc' ),
                    'label_off'    => __( 'Show', 'dapfforwc' ),
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
                    'label' => __( 'Form Styles', 'dapfforwc' ),
                    'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
                    'condition' => [
                        'filter_type' => 'all',
                    ],
                ]
            );
    
            $this->add_responsive_control(
                'form_background',
                [
                    'label'     => __( 'Background', 'dapfforwc' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        'form#product-filter' => 'background-color: {{VALUE}};',
                    ],
                ]
            );
            $this->add_responsive_control(
                'form_border_radius',
                [
                    'label'      => __( 'Border Radius', 'dapfforwc' ),
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
                    'label'      => __( 'Padding', 'dapfforwc' ),
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
                    'label'      => __( 'Margin', 'dapfforwc' ),
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
                    'label'     => __( 'Box Shadow', 'dapfforwc' ),
                    'selector'  => 'form#product-filter',
                ]
            );
        
            $this->add_responsive_control(
                'form_height',
                [
                    'label'      => __( 'Form Height', 'dapfforwc' ),
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
                    'label' => __( 'Container Styles', 'dapfforwc' ),
                    'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
                    'condition' => [
                        'filter_type' => 'all',
                    ],
                ]
            );
        
            $this->add_control(
                'container_background',
                [
                    'label'     => __( 'Background', 'dapfforwc' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '.filter-group.attributes>div, .filter-group.category, .filter-group.tag, .filter-group.price-range, div#rating' => 'background-color: {{VALUE}};',
                    ],
                ]
            );
            $this->add_responsive_control(
                'container_border_radius',
                [
                    'label'      => __( 'Border Radius', 'dapfforwc' ),
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
                    'label'      => __( 'Padding', 'dapfforwc' ),
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
                    'label'      => __( 'Margin', 'dapfforwc' ),
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
                    'label'     => __( 'Box Shadow', 'dapfforwc' ),
                    'selector'  => '.filter-group.attributes>div, .filter-group.category, .filter-group.tag, .filter-group.price-range, div#rating',
                ]
            );
        
            $this->add_control(
                'container_overflow',
                [
                    'label'     => __( 'Overflow', 'dapfforwc' ),
                    'type'      => \Elementor\Controls_Manager::SELECT,
                    'options'   => [
                        'visible' => __( 'Visible', 'dapfforwc' ),
                        'hidden'  => __( 'Hidden', 'dapfforwc' ),
                        'scroll'  => __( 'Scroll', 'dapfforwc' ),
                        'auto'    => __( 'Auto', 'dapfforwc' ),
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
                    'label' => __( 'Widget Title Styles', 'dapfforwc' ),
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
                    'label'    => __( 'Background', 'dapfforwc' ),
                    'types'    => [ 'classic', 'gradient' ],
                    'selector' => '{{WRAPPER}} .filter-group.attributes .title,{{WRAPPER}} .filter-group.category .title,{{WRAPPER}} .filter-group.tag .title,{{WRAPPER}} .filter-group.price-range .title,{{WRAPPER}} div#rating .title',
                ]
            );
        
            $this->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                [
                    'name' => 'widget_title_typography',
                    'label' => __( 'Typography', 'dapfforwc' ),
                    'selector' => '{{WRAPPER}} .filter-group .title',
                ]
            );
        
            $this->add_control(
                'widget_title_color',
                [
                    'label' => __( 'Text Color', 'dapfforwc' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .filter-group .title' => 'color: {{VALUE}} !important;',
                    ],
                ]
            );

            $this->add_responsive_control(
                'widget_title_radius',
                [
                    'label'      => __( 'Border Radius', 'dapfforwc' ),
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
                    'label' => __( 'Text Align', 'dapfforwc' ),
                    'type' => \Elementor\Controls_Manager::CHOOSE,
                    'options' => [
                        'left' => [
                            'title' => __( 'Left', 'dapfforwc' ),
                            'icon' => 'eicon-text-align-left',
                        ],
                        'center' => [
                            'title' => __( 'Center', 'dapfforwc' ),
                            'icon' => 'eicon-text-align-center',
                        ],
                        'space-between' => [
                            'title' => __( 'space-between', 'dapfforwc' ),
                            'icon' => 'eicon-justify-space-between-h',
                        ],
                        'right' => [
                            'title' => __( 'Right', 'dapfforwc' ),
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
                    'label' => __( 'Padding', 'dapfforwc' ),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'selectors' => [
                        '{{WRAPPER}} .filter-group .title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
        
            $this->add_responsive_control(
                'widget_title_margin',
                [
                    'label' => __( 'Margin', 'dapfforwc' ),
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
                    'label' => __( 'Widget Items Styles', 'dapfforwc' ),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                    'condition' => [
                        'filter_type' => 'all',
                    ],
                ]
            );
        
            $this->add_control(
                'widget_items_background_color',
                [
                    'label' => __( 'Background Color', 'dapfforwc' ),
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
                    'label' => __( 'Typography', 'dapfforwc' ),
                    'selector' => '{{WRAPPER}} .items label',
                ]
            );
        
            $this->add_control(
                'widget_items_color',
                [
                    'label' => __( 'Text Color', 'dapfforwc' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .items label, .price-input span,.price-input .separator' => 'color: {{VALUE}};',
                    ],
                ]
            );
        
            $this->add_responsive_control(
                'widget_items_padding',
                [
                    'label' => __( 'Padding', 'dapfforwc' ),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'selectors' => [
                        '{{WRAPPER}} .items' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
        
            $this->add_responsive_control(
                'widget_items_margin',
                [
                    'label' => __( 'Margin', 'dapfforwc' ),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'selectors' => [
                        '{{WRAPPER}} .items' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_responsive_control(
                'widget_items_radius',
                [
                    'label'      => __( 'Border Radius', 'dapfforwc' ),
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
                    'label' => __( 'Gap', 'dapfforwc' ),
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
                    'label' => __( 'Button Style', 'dapfforwc' ),
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
                    'label'    => __( 'Background', 'dapfforwc' ),
                    'types'    => [ 'classic', 'gradient' ],
                    'selector' => 'form#product-filter button',
                ]
            );
            
            $this->add_control(
                'button_text_color',
                [
                    'label'     => __( 'Text Color', 'dapfforwc' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        'form#product-filter button' => 'color: {{VALUE}};',
                    ],
                ]
            );
            
            $this->add_control(
                'button_hover_background',
                [
                    'label'     => __( 'Hover Background', 'dapfforwc' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        'form#product-filter button:hover' => 'background-color: {{VALUE}};',
                    ],
                ]
            );
            
            $this->add_control(
                'button_hover_text_color',
                [
                    'label'     => __( 'Hover Text Color', 'dapfforwc' ),
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
                    'label'    => __( 'Border', 'dapfforwc' ),
                    'selector' => 'form#product-filter button',
                ]
            );
            
            $this->add_responsive_control(
                'button_padding',
                [
                    'label'      => __( 'Padding', 'dapfforwc' ),
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
                    'label'      => __( 'Margin', 'dapfforwc' ),
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
                    'label'      => __( 'Border Radius', 'dapfforwc' ),
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
                    'label' => __( 'Rating Style', 'dapfforwc' ),
                    'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
                    'condition' => [
                        'filter_type' => 'all',
                    ],
                ]
            );
            
            $this->add_responsive_control(
                'rating_size',
                [
                    'label'      => __( 'Rating Size', 'dapfforwc' ),
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
                    'label'     => __( 'Inactive Color', 'dapfforwc' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .dynamic-rating label, .items.rating i' => 'color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_control(
                'rating_active_color',
                [
                    'label'     => __( 'Active Color', 'dapfforwc' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .dynamic-rating  input:checked + label:hover,{{WRAPPER}} .dynamic-rating  input:checked ~ label:hover,{{WRAPPER}} .dynamic-rating  label:hover ~ input:checked ~ label,{{WRAPPER}} .dynamic-rating  input:checked ~ label:hover ~ label, .items.rating input:checked  + .stars i' => 'color: {{VALUE}};',
                    ],
                ]
            );
            
            $this->add_control(
                'rating_hover_color',
                [
                    'label'     => __( 'Hover Color', 'dapfforwc' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .dynamic-rating input:checked ~ label,{{WRAPPER}} .dynamic-rating:not(:checked) label:hover,{{WRAPPER}} .dynamic-rating:not(:checked) label:hover ~ label, .items.rating input:hover  + .stars i' => 'color: {{VALUE}};',
                    ],
                ]
            );
            
            $this->add_responsive_control(
                'rating_gap',
                [
                    'label'      => __( 'Gap', 'dapfforwc' ),
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
                    'label' => __( 'Reset Button Style', 'dapfforwc' ),
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
                    'label'    => __( 'Background', 'dapfforwc' ),
                    'types'    => [ 'classic', 'gradient' ],
                    'selector' => 'form#product-filter span#reset-rating',
                ]
            );
            // Inside the Reset Button Style section
            $this->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                [
                    'name'     => 'reset_button_typography',
                    'label'    => __( 'Typography', 'dapfforwc' ),
                    'selector' => 'form#product-filter span#reset-rating',
                ]
            );

            
            // Text color
            $this->add_control(
                'reset_button_text_color',
                [
                    'label'     => __( 'Text Color', 'dapfforwc' ),
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
                    'label'     => __( 'Hover Background', 'dapfforwc' ),
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
                    'label'     => __( 'Hover Text Color', 'dapfforwc' ),
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
                    'label'    => __( 'Border', 'dapfforwc' ),
                    'selector' => 'form#product-filter span#reset-rating',
                ]
            );
            
            // Padding
            $this->add_responsive_control(
                'reset_button_padding',
                [
                    'label'      => __( 'Padding', 'dapfforwc' ),
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
                    'label'      => __( 'Margin', 'dapfforwc' ),
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
                    'label' => __( 'Input Style', 'dapfforwc' ),
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
                    'label'     => __( 'Background Color', 'dapfforwc' ),
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
                    'label'     => __( 'Text Color', 'dapfforwc' ),
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
                    'label'      => __( 'Padding', 'dapfforwc' ),
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
                    'label'      => __( 'Margin', 'dapfforwc' ),
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
                    'label'      => __( 'Border Radius', 'dapfforwc' ),
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
                    'label'    => __( 'Border', 'dapfforwc' ),
                    'selector' => 'form#product-filter input[type="search"], form#product-filter input[type="number"]',
                ]
            );
            
            $this->end_controls_section();
            // price slider
            $this->start_controls_section(
                'slider_style',
                [
                    'label' => __( 'Slider', 'dapfforwc' ),
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
                    'label'     => __( 'Slider Background', 'dapfforwc' ),
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
                    'label'      => __( 'Slider Border Radius', 'dapfforwc' ),
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
                    'label'     => __( 'Progress Background', 'dapfforwc' ),
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
                    'label'      => __( 'Progress Border Radius', 'dapfforwc' ),
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
                    'label' => __( 'Margin', 'dapfforwc' ),
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
                    'label'      => __( 'Thumb Size', 'dapfforwc' ),
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
                    'label'     => __( 'Thumb Background', 'dapfforwc' ),
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
                    'label'     => __( 'Tooltip Background', 'dapfforwc' ),
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
            'label' => __( 'Single Filter Styles', 'dapfforwc' ),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => [
                'filter_type' => 'single',
            ],
        ]
    );

    $this->add_control(
        'inactive_item_background',
        [
            'label' => __( 'Inactive Background Color', 'dapfforwc' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rfilterbuttons li' => 'background-color: {{VALUE}};',
            ],
        ]
    );
    $this->add_control(
        'inactive_item_color',
        [
            'label' => __( 'Inactive Text Color', 'dapfforwc' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rfilterbuttons ul li label' => 'color: {{VALUE}} !important;',
            ],
        ]
    );
    $this->add_control(
        'active_item_background',
        [
            'label' => __( 'Active Background Color', 'dapfforwc' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rfilterbuttons ul li.checked' => 'background-color: {{VALUE}};',
            ],
        ]
    );
    $this->add_control(
        'active_item_color',
        [
            'label' => __( 'Active Text Color', 'dapfforwc' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rfilterbuttons ul li.checked label' => 'color: {{VALUE}} !important;',
            ],
        ]
    );


    $this->add_control(
        'inactive_item_hover_color',
        [
            'label' => __( 'Inactive Hover Color', 'dapfforwc' ),
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
            'label' => __( 'Typography', 'dapfforwc' ),
            'selector' => '{{WRAPPER}} .rfilterbuttons ul li',
        ]
    );

    $this->add_control(
        'inactive_item_color',
        [
            'label' => __( 'Text Color', 'dapfforwc' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rfilterbuttons ul li label' => 'color: {{VALUE}} !important;',
            ],
        ]
    );

    $this->add_responsive_control(
        'inactive_item_padding',
        [
            'label' => __( 'Padding', 'dapfforwc' ),
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'selectors' => [
                '{{WRAPPER}} .rfilterbuttons ul li' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]
    );

    $this->add_responsive_control(
        'inactive_item_margin',
        [
            'label' => __( 'Margin', 'dapfforwc' ),
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'selectors' => [
                '{{WRAPPER}} .rfilterbuttons ul li' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]
    );
    $this->add_responsive_control(
        'inactive_item_gap',
        [
            'label' => __( 'Gap', 'dapfforwc' ),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'selectors' => [
                '{{WRAPPER}} .rfilterbuttons ul' => 'gap: {{SIZE}}{{UNIT}};',
            ],
        ]
    );

    $this->add_control(
        'inactive_item_overflow',
        [
            'label' => __( 'Overflow', 'dapfforwc' ),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'visible' => __( 'Visible', 'dapfforwc' ),
                'hidden'  => __( 'Hidden', 'dapfforwc' ),
                'scroll'  => __( 'Scroll', 'dapfforwc' ),
                'auto'    => __( 'Auto', 'dapfforwc' ),
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
            'label' => __( 'Flex Wrap', 'dapfforwc' ),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'nowrap'  => __( 'No Wrap', 'dapfforwc' ),
                'wrap'    => __( 'Wrap', 'dapfforwc' ),
                'wrap-reverse' => __( 'Wrap Reverse', 'dapfforwc' ),
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
            'label' => __( 'Selected Filter Styles', 'dapfforwc' ),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => [
                'filter_type' => 'selected',
            ],
        ]
    );

    $this->add_control(
        'selected_filter_background',
        [
            'label' => __( 'Background Color', 'dapfforwc' ),
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
            'label' => __( 'Typography', 'dapfforwc' ),
            'selector' => '{{WRAPPER}} .rfilterselected ul li.checked',
        ]
    );

    $this->add_control(
        'selected_filter_color',
        [
            'label' => __( 'Text Color', 'dapfforwc' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rfilterselected ul li.checked label' => 'color: {{VALUE}} !important;',
            ],
        ]
    );

    $this->add_responsive_control(
        'selected_filter_padding',
        [
            'label' => __( 'Padding', 'dapfforwc' ),
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'selectors' => [
                '{{WRAPPER}} .rfilterselected ul li.checked' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]
    );

    $this->add_responsive_control(
        'selected_filter_margin',
        [
            'label' => __( 'Margin', 'dapfforwc' ),
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'selectors' => [
                '{{WRAPPER}} .rfilterselected ul' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]
    );
    $this->add_responsive_control(
        'selected_filter_radius',
        [
            'label'      => __( 'Border Radius', 'dapfforwc' ),
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
            'label' => __( 'Gap', 'dapfforwc' ),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'selectors' => [
                '{{WRAPPER}} .rfilterselected ul' => 'gap: {{SIZE}}{{UNIT}};',
            ],
        ]
    );

    $this->add_control(
        'selected_filter_overflow',
        [
            'label' => __( 'Overflow', 'dapfforwc' ),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'visible' => __( 'Visible', 'dapfforwc' ),
                'hidden'  => __( 'Hidden', 'dapfforwc' ),
                'scroll'  => __( 'Scroll', 'dapfforwc' ),
                'auto'    => __( 'Auto', 'dapfforwc' ),
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
            'label'      => __( 'Height', 'dapfforwc' ),
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
            'label' => __( 'Flex Wrap', 'dapfforwc' ),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'nowrap'  => __( 'No Wrap', 'dapfforwc' ),
                'wrap'    => __( 'Wrap', 'dapfforwc' ),
                'wrap-reverse' => __( 'Wrap Reverse', 'dapfforwc' ),
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
            'label'   => __( 'Position', 'dapfforwc' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'default'  => __( 'Default', 'dapfforwc' ),
                'absolute' => __( 'Absolute', 'dapfforwc' ),
                'fixed'    => __( 'Fixed', 'dapfforwc' ),
                'sticky'   => __( 'Sticky', 'dapfforwc' ),
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
            'label'      => __( 'Vertical Orientation', 'dapfforwc' ),
            'type'       => \Elementor\Controls_Manager::SELECT,
            'options'    => [
                'top'    => __( 'Top', 'dapfforwc' ),
                'bottom' => __( 'Bottom', 'dapfforwc' ),
            ],
            'condition'  => [
                'selected_filter_position!' => 'default',
            ],
        ]
    );
    
    $this->add_responsive_control(
        'selected_filter_offset',
        [
            'label'      => __( 'Vertical Offset', 'dapfforwc' ),
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
            'label'      => __( 'Horizontal Orientation', 'dapfforwc' ),
            'type'       => \Elementor\Controls_Manager::SELECT,
            'options'    => [
                'left'  => __( 'Left', 'dapfforwc' ),
                'right' => __( 'Right', 'dapfforwc' ),
            ],
            'condition'  => [
                'selected_filter_position!' => 'default',
            ],
        ]
    );
    
    $this->add_responsive_control(
        'selected_filter_horizontal_offset',
        [
            'label'      => __( 'Horizontal Offset', 'dapfforwc' ),
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
            'label'     => __( 'Z-Index', 'dapfforwc' ),
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
