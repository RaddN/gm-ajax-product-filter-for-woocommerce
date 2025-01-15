const { useState } = wp.element;
const { useSelect, useDispatch } = wp.data;

const CustomBoxControl = ( { label, values, unit="px", onChange } ) => {
    // Define default values for padding/margin (or other styles)
    const defaultValues = { top: 0, right: 0, bottom: 0, left: 0 };

    // Merge defaults with the provided values
    let currentValues = { ...defaultValues, ...values };

    const handleChange = ( side, value ) => {
        const updatedValues = { ...currentValues, [side]: value };
        onChange( updatedValues );

    };

    const handleReset = () => {
        const formattedValue = `${defaultValues.top}${unit} ${defaultValues.right}${unit} ${defaultValues.bottom}${unit} ${defaultValues.left}${unit}`;
        onChange( formattedValue );
    };

    return wp.element.createElement(
        'div',
        { className: 'custom-box-control' },
        wp.element.createElement( 'label', {}, label ),
        wp.element.createElement(
            'div',
            { className: 'custom-box-control-grid' },
            [ 'top', 'right', 'bottom', 'left' ].map( ( side ) =>
                wp.element.createElement( 'div', { key: side, className: `control-${side}` },
                    wp.element.createElement(
                        'label',
                        { htmlFor: `control-${side}` },
                        side.charAt(0).toUpperCase() + side.slice(1)
                    ),
                    wp.element.createElement( 'input', {
                        id: `control-${side}`,
                        type: 'number',
                        value: currentValues[side],
                        onChange: ( e ) => handleChange( side, parseInt( e.target.value, 10 ) || 0 ),
                        style: { width: '100%'},
                    } ),
                    wp.element.createElement( 'span', {}, unit )
                )
            )
        ),
        wp.element.createElement(
            'button',
            {
                type: 'button',
                onClick: handleReset,
                style: { marginTop: '10px' }
            },
            'Reset'
        )
    );
};

const DeviceSelector = ({ onChange }) => {
    const [selectedDevice, setSelectedDevice] = useState('desktop');

    const handleDeviceChange = (device) => {
        setSelectedDevice(device);
        onChange(device);
        if (device === 'smartphone') {
            dispatch('core/edit-post').__experimentalSetPreviewDeviceType("mobile");
        }
        else {
            dispatch('core/edit-post').__experimentalSetPreviewDeviceType(device);
        }
    };

    return wp.element.createElement(
        'div',
        { className: 'device-selector' },
        ['desktop', 'tablet', 'smartphone'].map((device) =>
            wp.element.createElement('span', {
                key: device,
                className: `dashicons dashicons-${device} ${selectedDevice === device ? 'selected' : ''}`,
                onClick: () => handleDeviceChange(device),
                style: { cursor: 'pointer', marginRight: '5px' }
            })
        )
    );
};

( function( blocks, element, editor, components ) {
    var el = element.createElement;
    var InspectorControls = editor.InspectorControls;
    var PanelBody = components.PanelBody;
    var TextControl = components.TextControl;
    var SelectControl = components.SelectControl;
    var ColorPalette = components.ColorPalette;
    var FontSizePicker = components.FontSizePicker;
    var TabPanel = components.TabPanel;
    var RangeControl = components.RangeControl;
    var __ = wp.i18n.__;

    blocks.registerBlockType( 'plugin/dynamic-ajax-filter', {
        title: 'Dynamic Ajax Filter',
        description: 'A dynamic filter that uses AJAX to filter products.',
        icon: 'filter',
        category: 'widgets',
        attributes: {
            filterType: {
                type: 'string',
                default: 'all',
            },
            productSelector: {
                type: 'string',
                default: '',
            },
            paginationSelector: {
                type: 'string',
                default: '',
            },
            filterName: {
                type: 'string',
                default: '',
            },
            backgroundColor: {
                type: 'string',
                default: '',
            },
            color: {
                type: 'string',
                default: '',
            },
            typography: {
                type: 'object',
                default: {},
            },
            formStyle: {
                type: 'object',
                default: {
                    background: {
                        desktop: '',
                        tablet: '',
                        mobile: ''
                    }
                },
            },
            containerStyle: {
                type: 'object',
                default: {
                    background: {
                        desktop: '',
                        tablet: '',
                        mobile: ''
                    }
                },
            },
            widgetTitleStyle: {
                type: 'object',
                default:  {
                    background: {
                        desktop: '',
                        tablet: '',
                        mobile: ''
                    }
                },
            },
            widgetItemsStyle: {
                type: 'object',
                default:  {
                    background: {
                        desktop: '',
                        tablet: '',
                        mobile: ''
                    }
                },
            },
            buttonStyle: {
                type: 'object',
                default:  {
                    background: {
                        desktop: '',
                        tablet: '',
                        mobile: ''
                    }
                },
            },
            ratingStyle: {
                type: 'object',
                default:  {
                    background: {
                        desktop: '',
                        tablet: '',
                        mobile: ''
                    }
                },
            },
            resetButtonStyle: {
                type: 'object',
                default:  {
                    background: {
                        desktop: '',
                        tablet: '',
                        mobile: ''
                    }
                },
            },
            inputStyle: {
                type: 'object',
                default:  {
                    background: {
                        desktop: '',
                        tablet: '',
                        mobile: ''
                    }
                },
            },
            sliderStyle: {
                type: 'object',
                default:  {
                    background: {
                        desktop: '',
                        tablet: '',
                        mobile: ''
                    }
                },
            },
            selectedDevice: {
                type: 'string',
                default: 'desktop',
            },
        },
        edit: function( props ) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;

            const handleBackgroundChange = (value, section) => {
                const updatedBackground = { ...attributes[section].background, [attributes.selectedDevice]: value };
                setAttributes({ [section]: { ...attributes[section], background: updatedBackground } });
            };

            return [
                el( InspectorControls, {},
                    el( TabPanel, {
                        className: 'my-tab-panel',
                        activeClass: 'active-tab',
                        tabs: [
                            {
                                name: 'general',
                                title: 'General',
                                className: 'tab-general',
                            },
                            {
                                name: 'style',
                                title: 'Style',
                                className: 'tab-style',
                            },
                        ],
                    },
                    function( tab ) {
                        if ( tab.name === 'general' ) {
                            return el( PanelBody, { title: 'Filter Settings' },
                                el( SelectControl, {
                                    label: 'Filter Type',
                                    value: attributes.filterType,
                                    options: [
                                        { label: 'All', value: 'all' },
                                        { label: 'Single', value: 'single' },
                                        { label: 'Selected', value: 'selected' },
                                    ],
                                    onChange: function( value ) {
                                        setAttributes( { filterType: value } );
                                    }
                                } ),
                                attributes.filterType === 'all' && el( TextControl, {
                                    label: 'Product Selector',
                                    value: attributes.productSelector,
                                    onChange: function( value ) {
                                        setAttributes( { productSelector: value } );
                                    }
                                } ),
                                attributes.filterType === 'all' && el( TextControl, {
                                    label: 'Pagination Selector',
                                    value: attributes.paginationSelector,
                                    onChange: function( value ) {
                                        setAttributes( { paginationSelector: value } );
                                    }
                                } ),
                                attributes.filterType === 'single' && el( TextControl, {
                                    label: 'Filter Name',
                                    value: attributes.filterName,
                                    onChange: function( value ) {
                                        setAttributes( { filterName: value } );
                                    }
                                } )
                            );
                        } else if ( tab.name === 'style' ) {
                            if(attributes.filterType==='all') {
                            return [
                                el( PanelBody, { title: 'Form Style', initialOpen: false },
                                el( 'p', {}, 'Background', el(DeviceSelector, {
                                    onChange: function(device) {
                                        setAttributes({ selectedDevice: device });
                                    }
                                }) ),
                                el( ColorPalette, {
                                    value: attributes.formStyle.background[attributes.selectedDevice],
                                    onChange: function(value) { handleBackgroundChange(value, 'formStyle') },
                                } ),
                                el('p', {}, 'Padding'),
                                el(CustomBoxControl, {
                                    values: attributes.formStyle?.padding || { top: 0, right: 0, bottom: 0, left: 0 },
                                    unit: 'px',
                                    onChange: function (value) {
                                        setAttributes({
                                            formStyle: {
                                                ...attributes.formStyle,
                                                padding: value,
                                            },
                                        });
                                    },
                                }),
                                el( 'p', {}, 'Margin' ),
                                el( CustomBoxControl, {
                                    values: attributes.formStyle.margin,
                                    onChange: function( value ) {
                                        setAttributes( { formStyle: { ...attributes.formStyle, margin: value } } );
                                    }
                                } ),
                                el( 'p', {}, 'Shadow' ),
                                el( TextControl, {
                                    value: attributes.formStyle.shadow,
                                    onChange: function( value ) {
                                        setAttributes( { formStyle: { ...attributes.formStyle, shadow: value } } );
                                    }
                                } ),
                                el( 'p', {}, 'Height' ),
                                el( RangeControl, {
                                    value: attributes.formStyle.height,
                                    onChange: function( value ) {
                                        setAttributes( { formStyle: { ...attributes.formStyle, height: value } } );
                                    }
                                } ),
                                el( 'p', {}, 'Border Radius' ),
                                el( CustomBoxControl, {
                                    values: attributes.formStyle["border-radius"],
                                    onChange: function( value ) {
                                        setAttributes( { formStyle: { ...attributes.formStyle, "border-radius": value } } );
                                    }
                                } )),
                                el( PanelBody, { title: 'Container Style', initialOpen: false },
                                    el( 'p', {}, 'Background', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( ColorPalette, {
                                        value: attributes.containerStyle.background[attributes.selectedDevice]??'#000',
                                        onChange: function(value) { 
                                            handleBackgroundChange(value, 'containerStyle') },
                                    } ),
                                    el( 'p', {}, 'Padding' ),
                                    el( CustomBoxControl, {
                                        values: attributes.containerStyle.padding,
                                        onChange: function( value ) {
                                            setAttributes( { containerStyle: { ...attributes.containerStyle, padding: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Margin' ),
                                    el( CustomBoxControl, {
                                        values: attributes.containerStyle.margin,
                                        onChange: function( value ) {
                                            setAttributes( { containerStyle: { ...attributes.containerStyle, margin: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Border Radius' ),
                                el( CustomBoxControl, {
                                    values: attributes.containerStyle["border-radius"],
                                    onChange: function( value ) {
                                        setAttributes( { containerStyle: { ...attributes.containerStyle, "border-radius": value } } );
                                    }
                                } ),
                                    el( 'p', {}, 'Shadow' ),
                                    el( TextControl, {
                                        value: attributes.containerStyle.shadow,
                                        onChange: function( value ) {
                                            setAttributes( { containerStyle: { ...attributes.containerStyle, shadow: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Overflow' ),
                                    el( SelectControl, {
                                        value: attributes.containerStyle.overflow,
                                        options: [
                                            { label: 'Visible', value: 'visible' },
                                            { label: 'Hidden', value: 'hidden' },
                                            { label: 'Scroll', value: 'scroll' },
                                            { label: 'Auto', value: 'auto' },
                                        ],
                                        onChange: function( value ) {
                                            setAttributes( { containerStyle: { ...attributes.containerStyle, overflow: value } } );
                                        }
                                    } )
                                ),
                                el( PanelBody, { title: 'Widget Title Style', initialOpen: false },
                                    el( 'p', {}, 'Background', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( ColorPalette, {
                                        value: attributes.widgetTitleStyle.background[attributes.selectedDevice]??'#000',
                                        onChange: function(value) { 
                                            handleBackgroundChange(value, 'widgetTitleStyle') },
                                    } ),
                                    el( 'p', {}, 'Typography' ),
                                    el( FontSizePicker, {
                                        value: attributes.widgetTitleStyle["font-size"],
                                        onChange: function( value ) {
                                            setAttributes( { widgetTitleStyle: { ...attributes.widgetTitleStyle, 'font-size': value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Text Color' ),
                                    el( ColorPalette, {
                                        value: attributes.widgetTitleStyle.color,
                                        onChange: function( value ) {
                                            setAttributes( { widgetTitleStyle: { ...attributes.widgetTitleStyle, color: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Border Radius' ),
                                    el( CustomBoxControl, {
                                        values: attributes.widgetTitleStyle["border-radius"],
                                        onChange: function( value ) {
                                            setAttributes( { widgetTitleStyle: { ...attributes.widgetTitleStyle, "border-radius": value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Text Align' ),
                                    el( SelectControl, {
                                        value: attributes.widgetTitleStyle["text-align"],
                                        options: [
                                            { label: 'Left', value: 'left' },
                                            { label: 'Center', value: 'center' },
                                            { label: 'Right', value: 'right' },
                                        ],
                                        onChange: function( value ) {
                                            setAttributes( { widgetTitleStyle: { ...attributes.widgetTitleStyle, "text-align": value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Padding' ),
                                    el( CustomBoxControl, {
                                        values: attributes.widgetTitleStyle.padding,
                                        onChange: function( value ) {
                                            setAttributes( { widgetTitleStyle: { ...attributes.widgetTitleStyle, padding: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Margin' ),
                                    el( CustomBoxControl, {
                                        values: attributes.widgetTitleStyle.margin,
                                        onChange: function( value ) {
                                            setAttributes( { widgetTitleStyle: { ...attributes.widgetTitleStyle, margin: value } } );
                                        }
                                    } ),
                                ),
                                el( PanelBody, { title: 'Widget Items Style', initialOpen: false },
                                    el( 'p', {}, 'Background', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( ColorPalette, {
                                        value: attributes.widgetItemsStyle.background[attributes.selectedDevice],
                                        onChange: function(value) { handleBackgroundChange(value, 'widgetItemsStyle') },
                                    } ),
                                    el( 'p', {}, 'Typography' ),
                                    el( FontSizePicker, {
                                        value: attributes.widgetItemsStyle["font-size"],
                                        onChange: function( value ) {
                                            setAttributes( { widgetItemsStyle: { ...attributes.widgetItemsStyle, 'font-size': value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Text Color' ),
                                    el( ColorPalette, {
                                        value: attributes.widgetItemsStyle.color,
                                        onChange: function( value ) {
                                            setAttributes( { widgetItemsStyle: { ...attributes.widgetItemsStyle, color: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Padding' ),
                                    el( CustomBoxControl, {
                                        values: attributes.widgetItemsStyle.padding,
                                        onChange: function( value ) {
                                            setAttributes( { widgetItemsStyle: { ...attributes.widgetItemsStyle, padding: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Margin' ),
                                    el( CustomBoxControl, {
                                        values: attributes.widgetItemsStyle.margin,
                                        onChange: function( value ) {
                                            setAttributes( { widgetItemsStyle: { ...attributes.widgetItemsStyle, margin: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Border Radius' ),
                                    el( CustomBoxControl, {
                                        values: attributes.widgetItemsStyle["border-radius"],
                                        onChange: function( value ) {
                                            setAttributes( { widgetItemsStyle: { ...attributes.widgetItemsStyle, "border-radius": value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Gap' ),
                                    el( RangeControl, {
                                        value: attributes.widgetItemsStyle.gap,
                                        onChange: function( value ) {
                                            setAttributes( { widgetItemsStyle: { ...attributes.widgetItemsStyle, gap: value } } );
                                        }
                                    } )
                                ),
                                el( PanelBody, { title: 'Button Style', initialOpen: false },
                                    el( 'p', {}, 'Background', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( ColorPalette, {
                                        value: attributes.buttonStyle.background[attributes.selectedDevice],
                                        onChange: function(value) { handleBackgroundChange(value, 'buttonStyle') },
                                    } ),
                                    el( 'p', {}, 'Text Color' ),
                                    el( ColorPalette, {
                                        value: attributes.buttonStyle.color,
                                        onChange: function( value ) {
                                            setAttributes( { buttonStyle: { ...attributes.buttonStyle, color: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Hover Background' ),
                                    el( ColorPalette, {
                                        value: attributes.buttonStyle.hoverBackground,
                                        onChange: function( value ) {
                                            setAttributes( { buttonStyle: { ...attributes.buttonStyle, hoverBackground: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Hover Text Color' ),
                                    el( ColorPalette, {
                                        value: attributes.buttonStyle.hoverColor,
                                        onChange: function( value ) {
                                            setAttributes( { buttonStyle: { ...attributes.buttonStyle, hoverColor: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Border Type' ),
                                    el( SelectControl, {
                                        value: attributes.buttonStyle.borderType,
                                        options: [
                                            { label: 'None', value: 'none' },
                                            { label: 'Solid', value: 'solid' },
                                            { label: 'Dashed', value: 'dashed' },
                                            { label: 'Dotted', value: 'dotted' },
                                        ],
                                        onChange: function( value ) {
                                            setAttributes( { buttonStyle: { ...attributes.buttonStyle, borderType: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Padding' ),
                                    el( CustomBoxControl, {
                                        values: attributes.buttonStyle.padding,
                                        onChange: function( value ) {
                                            setAttributes( { buttonStyle: { ...attributes.buttonStyle, padding: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Margin' ),
                                    el( CustomBoxControl, {
                                        values: attributes.buttonStyle.margin,
                                        onChange: function( value ) {
                                            setAttributes( { buttonStyle: { ...attributes.buttonStyle, margin: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Border Radius' ),
                                    el( CustomBoxControl, {
                                        values: attributes.buttonStyle["border-radius"],
                                        onChange: function( value ) {
                                            setAttributes( { buttonStyle: { ...attributes.buttonStyle, "border-radius": value } } );
                                        }
                                    } )
                                ),
                                el( PanelBody, { title: 'Rating Style', initialOpen: false },
                                    el( 'p', {}, 'Rating Size' ),
                                    el( RangeControl, {
                                        value: attributes.ratingStyle["font-size"],
                                        onChange: function( value ) {
                                            setAttributes( { ratingStyle: { ...attributes.ratingStyle, "font-size": value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Inactive Color' ),
                                    el( ColorPalette, {
                                        value: attributes.ratingStyle["color"],
                                        onChange: function( value ) {
                                            setAttributes( { ratingStyle: { ...attributes.ratingStyle, "color": value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Active Color' ),
                                    el( ColorPalette, {
                                        value: attributes.ratingStyle.activeColor,
                                        onChange: function( value ) {
                                            setAttributes( { ratingStyle: { ...attributes.ratingStyle, activeColor: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Hover Color' ),
                                    el( ColorPalette, {
                                        value: attributes.ratingStyle.hoverColor,
                                        onChange: function( value ) {
                                            setAttributes( { ratingStyle: { ...attributes.ratingStyle, hoverColor: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Gap' ),
                                    el( RangeControl, {
                                        value: attributes.ratingStyle.gap,
                                        onChange: function( value ) {
                                            setAttributes( { ratingStyle: { ...attributes.ratingStyle, gap: value } } );
                                        }
                                    } )
                                ), 
                                el( PanelBody, { title: 'Reset Button Style', initialOpen: false },
                                    el( 'p', {}, 'Background', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( ColorPalette, {
                                        value: attributes.resetButtonStyle.background[attributes.selectedDevice],
                                        onChange: function(value) { handleBackgroundChange(value, 'resetButtonStyle') },
                                    } ),
                                    el( 'p', {}, 'Typography' ),
                                    el( FontSizePicker, {
                                        value: attributes.resetButtonStyle["font-size"],
                                        onChange: function( value ) {
                                            setAttributes( { resetButtonStyle: { ...attributes.resetButtonStyle, 'font-size': value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Text Color' ),
                                    el( ColorPalette, {
                                        value: attributes.resetButtonStyle.color,
                                        onChange: function( value ) {
                                            setAttributes( { resetButtonStyle: { ...attributes.resetButtonStyle, color: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Hover Background' ),
                                    el( ColorPalette, {
                                        value: attributes.resetButtonStyle.hoverBackground,
                                        onChange: function( value ) {
                                            setAttributes( { resetButtonStyle: { ...attributes.resetButtonStyle, hoverBackground: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Hover Text Color' ),
                                    el( ColorPalette, {
                                        value: attributes.resetButtonStyle.hoverColor,
                                        onChange: function( value ) {
                                            setAttributes( { resetButtonStyle: { ...attributes.resetButtonStyle, hoverColor: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Border' ),
                                    el( TextControl, {
                                        value: attributes.resetButtonStyle.border,
                                        onChange: function( value ) {
                                            setAttributes( { resetButtonStyle: { ...attributes.resetButtonStyle, border: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Padding' ),
                                    el( CustomBoxControl, {
                                        values: attributes.resetButtonStyle.padding,
                                        onChange: function( value ) {
                                            setAttributes( { resetButtonStyle: { ...attributes.resetButtonStyle, padding: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Margin' ),
                                    el( CustomBoxControl, {
                                        values: attributes.resetButtonStyle.margin,
                                        onChange: function( value ) {
                                            setAttributes( { resetButtonStyle: { ...attributes.resetButtonStyle, margin: value } } );
                                        }
                                    } )
                                ),
                                el( PanelBody, { title: 'Input Style', initialOpen: false },
                                    el( 'p', {}, 'Background', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( ColorPalette, {
                                        value: attributes.inputStyle.background[attributes.selectedDevice],
                                        onChange: function(value) { handleBackgroundChange(value, 'inputStyle') },
                                    } ),
                                    el( 'p', {}, 'Text Color' ),
                                    el( ColorPalette, {
                                        value: attributes.inputStyle.color,
                                        onChange: function( value ) {
                                            setAttributes( { inputStyle: { ...attributes.inputStyle, color: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Padding' ),
                                    el( CustomBoxControl, {
                                        values: attributes.inputStyle.padding,
                                        onChange: function( value ) {
                                            setAttributes( { inputStyle: { ...attributes.inputStyle, padding: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Margin' ),
                                    el( CustomBoxControl, {
                                        values: attributes.inputStyle.margin,
                                        onChange: function( value ) {
                                            setAttributes( { inputStyle: { ...attributes.inputStyle, margin: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Border Radius' ),
                                    el( CustomBoxControl, {
                                        values: attributes.inputStyle["border-radius"],
                                        onChange: function( value ) {
                                            setAttributes( { inputStyle: { ...attributes.inputStyle, "border-radius": value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Border' ),
                                    el( TextControl, {
                                        value: attributes.inputStyle.border,
                                        onChange: function( value ) {
                                            setAttributes( { inputStyle: { ...attributes.inputStyle, border: value } } );
                                        }
                                    } )
                                ),
                                el( PanelBody, { title: 'Slider Style', initialOpen: false },
                                    el( 'p', {}, 'Background', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( ColorPalette, {
                                        value: attributes.sliderStyle.background[attributes.selectedDevice],
                                        onChange: function(value) { handleBackgroundChange(value, 'sliderStyle') },
                                    } ),
                                    el( 'p', {}, 'Border Radius' ),
                                    el( CustomBoxControl, {
                                        values: attributes.sliderStyle["border-radius"],
                                        onChange: function( value ) {
                                            setAttributes( { sliderStyle: { ...attributes.sliderStyle, "border-radius": value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Progress Background' ),
                                    el( ColorPalette, {
                                        value: attributes.sliderStyle.progressBackground,
                                        onChange: function( value ) {
                                            setAttributes( { sliderStyle: { ...attributes.sliderStyle, progressBackground: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Progress Border Radius' ),
                                    el( CustomBoxControl, {
                                        values: attributes.sliderStyle["progress-border-radius"],
                                        onChange: function( value ) {
                                            setAttributes( { sliderStyle: { ...attributes.sliderStyle, "progress-border-radius": value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Margin' ),
                                    el( CustomBoxControl, {
                                        values: attributes.sliderStyle.margin,
                                        onChange: function( value ) {
                                            setAttributes( { sliderStyle: { ...attributes.sliderStyle, margin: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Thumb Size' ),
                                    el( RangeControl, {
                                        value: attributes.sliderStyle.thumbSize,
                                        onChange: function( value ) {
                                            setAttributes( { sliderStyle: { ...attributes.sliderStyle, thumbSize: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Thumb Background' ),
                                    el( ColorPalette, {
                                        value: attributes.sliderStyle.thumbBackground,
                                        onChange: function( value ) {
                                            setAttributes( { sliderStyle: { ...attributes.sliderStyle, thumbBackground: value } } );
                                        }
                                    } ),
                                    el( 'p', {}, 'Tooltip Background' ),
                                    el( ColorPalette, {
                                        value: attributes.sliderStyle.tooltipBackground,
                                        onChange: function( value ) {
                                            setAttributes( { sliderStyle: { ...attributes.sliderStyle, tooltipBackground: value } } );
                                        }
                                    } )
                                )
                            ];
                        }
                        return null;
                        }
                    } )
                ),
                el( 'form', {
                    id: 'product-filter',
                },
                
                attributes.filterType === 'all' && '[plugincy_filters product_selector="' + attributes.productSelector + '" pagination_selector="' + attributes.paginationSelector + '"]',
                attributes.filterType === 'single' && '[plugincy_filters_single name="' + attributes.filterName + '"]',
                attributes.filterType === 'selected' && '[plugincy_filters_selected]'
                )
            ];
        },
        save: function() {
            return null; // Rendered in PHP
        },
    } );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components );