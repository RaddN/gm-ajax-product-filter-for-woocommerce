const { useState, useEffect } = wp.element;
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
        document.querySelectorAll('.dashicons').forEach((element) => {
            element.classList.remove('selected');
        });

        document.querySelectorAll(`.dashicons-${device}`).forEach((element) => {
            element.classList.add('selected');
        });

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
    let storestyle =(defaultheight='') =>{ return { type: 'object', default: { background: { desktop: '', tablet: '', mobile: '' }, desktop: {}, tablet: {}, mobile: {},height:defaultheight, }, };};
    let storestring = (defaultvalue = '') => {
        return { type: 'string', default: defaultvalue };
    };

    blocks.registerBlockType( 'plugin/dynamic-ajax-filter', {
        title: 'Dynamic Ajax Filter',
        description: 'A dynamic filter that uses AJAX to filter products.',
        icon: 'filter',
        category: 'widgets',
        attributes: {
            filterOptions: { type: 'array', default: [
                { id: 'category', title: 'Category', visible: true },
                { id: 'tag', title: 'Tag' , visible: true },
                { id: 'price-range', title: 'Price Range', visible: true },
                { id: 'rating', title: 'Rating', visible: true  },
                { id: 'search_text', title: 'Search Text', visible: true }
            ]},
            filterType: storestring(defaultvalue = 'all'),
            usecustomdesign:storestring(defaultvalue = 'no'),
            perPage:storestring(defaultvalue = '12'),
            mobileStyle: storestring(defaultvalue = 'style_1'),
            productSelector:storestring(),
            paginationSelector:storestring(),
            filterName:storestring(),
            backgroundColor:storestring(),
            customCSS:storestring(),
            className: storestring(),
            formStyle: storestyle(defaultheight={desktop: { value: 0, unit: 'px' },tablet: { value: 0, unit: 'px' },mobile: { value: 0, unit: 'px' }}),
            containerStyle: storestyle(),
            widgetTitleStyle: storestyle(),
            widgetItemsStyle: storestyle(),
            buttonStyle: storestyle(),
            ratingStyle: storestyle(),
            resetButtonStyle: storestyle(),
            inputStyle: storestyle(),
            sliderStyle: storestyle(),
            filterWordMobile: {
                type: 'object',
                default:{
                    display: "block",
                },
            },
            selectedDevice: storestring(defaultvalue = 'desktop'),
            singleFilterInactiveStyle: {
                type: 'object',
                default: {},
            },
            singleFilterActiveStyle: {
                type: 'object',
                default: {},
            },
            singleFilterHoverStyle: {
                type: 'object',
                default: {},
            },
            singleFilterContainerStyle: {
                type: 'object',
                default: {},
            }
            
        },
        edit: function( props ) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            const [filterOptions, setFilterOptions] = useState(attributes.filterOptions || ['category','tag' ,'price-range','rating','search_text']);


            const handleBackgroundChange = (value, section) => {
                const updatedBackground = { ...attributes[section].background, [attributes.selectedDevice]: value };
                setAttributes({ [section]: { ...attributes[section], background: updatedBackground } });
            };
            const handleCustomBoxControl = (value, section, cssproperty) => {
                const updatedcssproperty = { ...attributes[section][attributes.selectedDevice], [cssproperty]: value };
                setAttributes({ [section]: { ...attributes[section], [attributes.selectedDevice]: updatedcssproperty } });
            };

            useEffect(() => {
                const fetchAttributes = async () => {
                    try {
                        const response = await fetch('/wp-json/dynamic-ajax-product-filters-for-woocommerce/v1/attributes/');
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        const attributes = await response.json();
                        const options = attributes.map(attr => ({
                            id: attr.slug,
                            title: attr.name,
                            visible: true
                        }));
                        const updatedOptions = Array.from(new Map([...filterOptions, ...options].map(item => [item.id, item])).values());
                        setFilterOptions(updatedOptions);
                        setAttributes({ filterOptions: updatedOptions });
                    } catch (error) {
                        console.error('Error fetching attributes:', error);
                    }
                };
            
                fetchAttributes();
            }, []);

            const handleDragStart = (e, index) => {
                e.dataTransfer.setData('text/plain', index);
            };

            const handleDrop = (e, dropIndex) => {
                const draggedIndex = e.dataTransfer.getData('text/plain');
                const updatedOptions = [...filterOptions];
                const [draggedItem] = updatedOptions.splice(draggedIndex, 1);
                updatedOptions.splice(dropIndex, 0, draggedItem);
                setFilterOptions(updatedOptions);
                setAttributes({ filterOptions: updatedOptions });
            };

            const toggleVisibility = (index) => {
                const updatedOptions = [...filterOptions];
                updatedOptions[index].visible = !updatedOptions[index].visible;
                setFilterOptions(updatedOptions);
                setAttributes({ filterOptions: updatedOptions });
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
                            {
                                name: 'advanced',
                                title: 'Advanced',
                                className: 'tab-advanced',
                            },
                        ],
                    },
                    function( tab ) {
                        if ( tab.name === 'general' ) {
                            return [el( PanelBody, { title: 'Filter Settings' },
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
                                attributes.filterType === 'all' && el( SelectControl, {
                                    label: 'Use Custom Design',
                                    value: attributes.usecustomdesign,
                                    options: [
                                        { label: 'Yes', value: 'yes' },
                                        { label: 'No', value: 'no' },
                                    ],
                                    onChange: function( value ) {
                                        setAttributes( { usecustomdesign: value } );
                                    }
                                } ),
                                attributes.filterType === 'all' && el( TextControl, {
                                    label: 'Per Page',
                                    value: attributes.perPage,
                                    onChange: function( value ) {
                                        setAttributes( { perPage: value } );
                                    }
                                } ),
                                attributes.filterType === 'single' && el( TextControl, {
                                    label: 'Attribute Id',
                                    value: attributes.filterName,
                                    onChange: function( value ) {
                                        setAttributes( { filterName: value } );
                                    }
                                } )
                            ),
                            el(
                                PanelBody,
                                { title: "Form Manage", initialOpen: false },
                                el('div', { className: 'draggable-options' },
                                    filterOptions.map((option, index) =>
                                        el('div', {
                                            key: option.id,
                                            className: `draggable-option ${option.visible ?'':'invisible'}`,
                                            draggable: true,
                                            onDragStart: (e) => handleDragStart(e, index),
                                            onDragOver: (e) => e.preventDefault(),
                                            onDrop: (e) => handleDrop(e, index),
                                        }, 
                                            wp.element.createElement('span', {}, option.title),
                                            wp.element.createElement('span', {
                                                className: `dashicons ${option.visible ? 'dashicons-visibility' : 'dashicons-hidden'}`,
                                                style: { marginLeft: '5px', cursor: 'pointer' },
                                                onClick: () => toggleVisibility(index),
                                            })
                                        )
                                    )
                                )
                            ),
                            el( PanelBody, {title: "Mobile Responsive Style", initialOpen: false},
                                el( SelectControl, {
                                    label: 'Filter Type',
                                    value: attributes.mobileStyle,
                                    options: [
                                        { label: 'Style 1', value: 'style_1' },
                                        { label: 'Style 2', value: 'style_2' },
                                        { label: 'Style 3', value: 'style_3' },
                                        { label: 'Style 4', value: 'style_4' },
                                    ],
                                    onChange: function( value ) {
                                        setAttributes( { mobileStyle: value } );
                                    }
                                } )
                            )
                        ];
                        } else if ( tab.name === 'style' ) {
                            if(attributes.filterType==='all') {
                            return [
                                el( PanelBody, { title: 'Filter Word (Mobile)', initialOpen: true },
                                    el( 'div', { style: { display: 'block' } },
                                        el( 'p', {}, 'Show Filter Word on Mobile' )
                                    ),
                                    el( 'button', {
                                        type: 'button',
                                        onClick: function(){
                                            setAttributes( { filterWordMobile: { ...attributes.filterWordMobile, display: attributes.filterWordMobile.display==="block"?"none":"block" } } );
                                        },
                                        style: { marginBottom: '10px' }
                                    }, attributes.filterWordMobile.display==="block" ? 'showing' : 'hidden' ),
                                ),                             
                                el( PanelBody, { title: 'Form Style', initialOpen: false },
                                el( 'p', {}, 'Background', el(DeviceSelector, {
                                    onChange: function(device) {
                                        setAttributes({ selectedDevice: device });
                                    }
                                }) 
                            ),
                                el( ColorPalette, {
                                    value: attributes.formStyle.background?attributes.formStyle.background[attributes.selectedDevice]:"",
                                    onChange: function(value) { handleBackgroundChange(value, 'formStyle') },
                                } ),
                                el('p', {}, 'Padding', el(DeviceSelector, {
                                    onChange: function(device) {
                                        setAttributes({ selectedDevice: device });
                                    }
                                }) ),
                                el( CustomBoxControl, {
                                    values: attributes.formStyle[attributes.selectedDevice]?.padding || { top: 0, right: 0, bottom: 0, left: 0 },
                                    unit: 'px',
                                    onChange: function (value) {
                                        handleCustomBoxControl(value, 'formStyle', 'padding');
                                    }
                                }),
                                el('p', {}, 'Margin', el(DeviceSelector, {
                                    onChange: function(device) {
                                        setAttributes({ selectedDevice: device });
                                    }
                                }) ),
                                el( CustomBoxControl, {
                                    values: attributes.formStyle[attributes.selectedDevice]?.margin || { top: 0, right: 0, bottom: 0, left: 0 },
                                    unit: 'px',
                                    onChange: function (value) {
                                        handleCustomBoxControl(value, 'formStyle', 'margin');
                                    }
                                }),
                                el( 'p', {}, 'Shadow' ),
                                el( TextControl, {
                                    value: attributes.formStyle["box-shadow"],
                                    onChange: function( value ) {
                                        setAttributes( { formStyle: { ...attributes.formStyle, "box-shadow": value } } );
                                    }
                                } ),
                                el( 'p', {
                                    style: { marginBottom: '5px', fontSize: '11px', color: '#666' }
                                }, 'eg. rgba(100, 100, 111, 0.2) 0px 7px 29px 0px' ),
                                el(
                                    'p',
                                    { className: 'components-base-control__label' },
                                    'Height',
                                    el(DeviceSelector, {
                                        onChange: (device) => {
                                            setAttributes({ selectedDevice: device });
                                        },
                                    }),
                                    el(SelectControl, {
                                        value: attributes.formStyle?.height?.[attributes.selectedDevice]?.unit || 'px',
                                        options: [
                                            { label: 'px', value: 'px' },
                                            { label: '%', value: '%' },
                                            { label: 'em', value: 'em' },
                                        ],
                                        onChange: (unit) => {
                                            setAttributes({
                                                formStyle: {
                                                    ...attributes.formStyle,
                                                    height: {
                                                        ...attributes.formStyle?.height,
                                                        [attributes.selectedDevice]: {
                                                            ...attributes.formStyle?.height?.[attributes.selectedDevice],
                                                            unit,
                                                        },
                                                    },
                                                },
                                            });
                                        },
                                    }),
                                ), 
                                el(RangeControl, {
                                    value: attributes.formStyle?.height?.[attributes.selectedDevice]?.value || 0,
                                    onChange: (value) => {
                                        setAttributes({
                                            formStyle: {
                                                ...attributes.formStyle,
                                                height: {
                                                    ...attributes.formStyle?.height,
                                                    [attributes.selectedDevice]: {
                                                        ...attributes.formStyle?.height?.[attributes.selectedDevice],
                                                        value,
                                                    },
                                                },
                                            },
                                        });
                                    },
                                }),                               
                                el('p', {}, 'Border Radius', el(DeviceSelector, {
                                    onChange: function(device) {
                                        setAttributes({ selectedDevice: device });
                                    }
                                }) ),
                                el( CustomBoxControl, {
                                    values: attributes.formStyle[attributes.selectedDevice]? attributes.formStyle[attributes.selectedDevice]["border-radius"] : { top: 0, right: 0, bottom: 0, left: 0 },
                                    unit: 'px',
                                    onChange: function (value) {
                                        handleCustomBoxControl(value, 'formStyle', "border-radius");
                                    }
                                })
                            ),
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
                                    el('p', {}, 'Padding', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( CustomBoxControl, {
                                        values: attributes.containerStyle[attributes.selectedDevice]?.padding || { top: 0, right: 0, bottom: 0, left: 0 },
                                        unit: 'px',
                                        onChange: function (value) {
                                            handleCustomBoxControl(value, 'containerStyle', 'padding');
                                        }
                                    }),
                                    el('p', {}, 'Margin', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( CustomBoxControl, {
                                        values: attributes.containerStyle[attributes.selectedDevice]?.margin || { top: 0, right: 0, bottom: 0, left: 0 },
                                        unit: 'px',
                                        onChange: function (value) {
                                            handleCustomBoxControl(value, 'containerStyle', 'margin');
                                        }
                                    }),
                                    el('p', {}, 'Border Radius', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( CustomBoxControl, {
                                        values: attributes.containerStyle[attributes.selectedDevice]? attributes.containerStyle[attributes.selectedDevice]["border-radius"]: { top: 0, right: 0, bottom: 0, left: 0 },
                                        unit: 'px',
                                        onChange: function (value) {
                                            handleCustomBoxControl(value, 'containerStyle', "border-radius");
                                        }
                                    }),
                                    el( 'p', {}, 'Shadow' ),
                                    el( TextControl, {
                                        value: attributes.containerStyle["box-shadow"],
                                        onChange: function( value ) {
                                            setAttributes( { containerStyle: { ...attributes.containerStyle, "box-shadow": value } } );
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
                                    el('p', {}, 'Border Radius', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( CustomBoxControl, {
                                        values: attributes.widgetTitleStyle[attributes.selectedDevice]? attributes.widgetTitleStyle[attributes.selectedDevice]["border-radius"] : { top: 0, right: 0, bottom: 0, left: 0 },
                                        unit: 'px',
                                        onChange: function (value) {
                                            handleCustomBoxControl(value, 'widgetTitleStyle', "border-radius");
                                        }
                                    }),
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
                                    el('p', {}, 'Padding', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( CustomBoxControl, {
                                        values: attributes.widgetTitleStyle[attributes.selectedDevice]?.padding || { top: 0, right: 0, bottom: 0, left: 0 },
                                        unit: 'px',
                                        onChange: function (value) {
                                            handleCustomBoxControl(value, 'widgetTitleStyle', 'padding');
                                        }
                                    }),
                                    el('p', {}, 'Margin', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( CustomBoxControl, {
                                        values: attributes.widgetTitleStyle[attributes.selectedDevice]?.margin || { top: 0, right: 0, bottom: 0, left: 0 },
                                        unit: 'px',
                                        onChange: function (value) {
                                            handleCustomBoxControl(value, 'widgetTitleStyle', 'margin');
                                        }
                                    }),
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
                                    el('p', {}, 'Padding', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( CustomBoxControl, {
                                        values: attributes.widgetItemsStyle[attributes.selectedDevice]?.padding || { top: 0, right: 0, bottom: 0, left: 0 },
                                        unit: 'px',
                                        onChange: function (value) {
                                            handleCustomBoxControl(value, 'widgetItemsStyle', 'padding');
                                        }
                                    }),
                                    el('p', {}, 'Margin', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( CustomBoxControl, {
                                        values: attributes.widgetItemsStyle[attributes.selectedDevice]?.margin || { top: 0, right: 0, bottom: 0, left: 0 },
                                        unit: 'px',
                                        onChange: function (value) {
                                            handleCustomBoxControl(value, 'widgetItemsStyle', 'margin');
                                        }
                                    }),
                                    el('p', {}, 'Border Radius', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( CustomBoxControl, {
                                        values: attributes.widgetItemsStyle[attributes.selectedDevice]? attributes.widgetItemsStyle[attributes.selectedDevice]["border-radius"] : { top: 0, right: 0, bottom: 0, left: 0 },
                                        unit: 'px',
                                        onChange: function (value) {
                                            handleCustomBoxControl(value, 'widgetItemsStyle', "border-radius");
                                        }
                                    }),
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
                                    el('p', {}, 'Padding', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( CustomBoxControl, {
                                        values: attributes.buttonStyle[attributes.selectedDevice]?.padding || { top: 0, right: 0, bottom: 0, left: 0 },
                                        unit: 'px',
                                        onChange: function (value) {
                                            handleCustomBoxControl(value, 'buttonStyle', 'padding');
                                        }
                                    }),
                                    el('p', {}, 'Margin', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( CustomBoxControl, {
                                        values: attributes.buttonStyle[attributes.selectedDevice]?.margin || { top: 0, right: 0, bottom: 0, left: 0 },
                                        unit: 'px',
                                        onChange: function (value) {
                                            handleCustomBoxControl(value, 'buttonStyle', 'margin');
                                        }
                                    }),
                                    el('p', {}, 'Border Radius', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( CustomBoxControl, {
                                        values: attributes.buttonStyle[attributes.selectedDevice]?attributes.buttonStyle[attributes.selectedDevice]["border-radius"] : { top: 0, right: 0, bottom: 0, left: 0 },
                                        unit: 'px',
                                        onChange: function (value) {
                                            handleCustomBoxControl(value, 'buttonStyle', "border-radius");
                                        }
                                    })
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
                                    el('p', {}, 'Padding', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( CustomBoxControl, {
                                        values: attributes.resetButtonStyle[attributes.selectedDevice]?.padding || { top: 0, right: 0, bottom: 0, left: 0 },
                                        unit: 'px',
                                        onChange: function (value) {
                                            handleCustomBoxControl(value, 'resetButtonStyle', 'padding');
                                        }
                                    }),
                                    el('p', {}, 'Margin', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( CustomBoxControl, {
                                        values: attributes.resetButtonStyle[attributes.selectedDevice]?.margin || { top: 0, right: 0, bottom: 0, left: 0 },
                                        unit: 'px',
                                        onChange: function (value) {
                                            handleCustomBoxControl(value, 'resetButtonStyle', 'margin');
                                        }
                                    }),
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
                                    el('p', {}, 'Padding', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( CustomBoxControl, {
                                        values: attributes.inputStyle[attributes.selectedDevice]?.padding || { top: 0, right: 0, bottom: 0, left: 0 },
                                        unit: 'px',
                                        onChange: function (value) {
                                            handleCustomBoxControl(value, 'inputStyle', 'padding');
                                        }
                                    }),
                                    el('p', {}, 'Margin', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( CustomBoxControl, {
                                        values: attributes.inputStyle[attributes.selectedDevice]?.margin || { top: 0, right: 0, bottom: 0, left: 0 },
                                        unit: 'px',
                                        onChange: function (value) {
                                            handleCustomBoxControl(value, 'inputStyle', 'margin');
                                        }
                                    }),
                                    el('p', {}, 'Border Radius', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( CustomBoxControl, {
                                        values: attributes.inputStyle[attributes.selectedDevice]?attributes.inputStyle[attributes.selectedDevice]["border-radius"] : { top: 0, right: 0, bottom: 0, left: 0 },
                                        unit: 'px',
                                        onChange: function (value) {
                                            handleCustomBoxControl(value, 'inputStyle', "border-radius");
                                        }
                                    }),
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
                                    el('p', {}, 'Border Radius', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( CustomBoxControl, {
                                        values: attributes.sliderStyle[attributes.selectedDevice]?attributes.sliderStyle[attributes.selectedDevice]["border-radius"] : { top: 0, right: 0, bottom: 0, left: 0 },
                                        unit: 'px',
                                        onChange: function (value) {
                                            handleCustomBoxControl(value, 'sliderStyle', "border-radius");
                                        }
                                    }),
                                    el( 'p', {}, 'Progress Background' ),
                                    el( ColorPalette, {
                                        value: attributes.sliderStyle.progressBackground,
                                        onChange: function( value ) {
                                            setAttributes( { sliderStyle: { ...attributes.sliderStyle, progressBackground: value } } );
                                        }
                                    } ),
                                    el('p', {}, 'Progress Border Radius', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( CustomBoxControl, {
                                        values: attributes.sliderStyle[attributes.selectedDevice]?attributes.sliderStyle[attributes.selectedDevice]["progress-border-radius"] : { top: 0, right: 0, bottom: 0, left: 0 },
                                        unit: 'px',
                                        onChange: function (value) {
                                            handleCustomBoxControl(value, 'sliderStyle', "progress-border-radius");
                                        }
                                    }),
                                    el('p', {}, 'progress Margin', el(DeviceSelector, {
                                        onChange: function(device) {
                                            setAttributes({ selectedDevice: device });
                                        }
                                    }) ),
                                    el( CustomBoxControl, {
                                        values: attributes.sliderStyle[attributes.selectedDevice]?.progressmargin || { top: 0, right: 0, bottom: 0, left: 0 },
                                        unit: 'px',
                                        onChange: function (value) {
                                            handleCustomBoxControl(value, 'sliderStyle', 'progressmargin');
                                        }
                                    }),
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
                        else if(attributes.filterType==='single' || attributes.filterType==='selected') {
                            return [
                                el( PanelBody, { title: 'Inactive Style', initialOpen: true },
                                    el( 'p', {}, 'Inactive Background Color' ),
                                    el( ColorPalette, {
                                        value: attributes.singleFilterInactiveStyle.background?attributes.singleFilterInactiveStyle.background[attributes.selectedDevice]:"",
                                        onChange: function(value) { handleBackgroundChange(value, 'singleFilterInactiveStyle') },
                                    } ),
                                    el( 'p', {}, 'Inactive Text Color' ),
                                    el( ColorPalette, {
                                        value: attributes.singleFilterInactiveStyle?.color || '',
                                        onChange: function(value) {
                                            setAttributes({ singleFilterInactiveStyle: { ...attributes.singleFilterInactiveStyle, color: value } });
                                        }
                                    } )
                                ),
                                el( PanelBody, { title: 'Active Style', initialOpen: false },
                                    el( 'p', {}, 'Active Background Color' ),
                                    el( ColorPalette, {
                                        value: attributes.singleFilterActiveStyle.background?attributes.singleFilterActiveStyle.background[attributes.selectedDevice]:"",
                                        onChange: function(value) { handleBackgroundChange(value, 'singleFilterActiveStyle') },
                                    } ),
                                    el( 'p', {}, 'Active Text Color' ),
                                    el( ColorPalette, {
                                        value: attributes.singleFilterActiveStyle?.color || '',
                                        onChange: function(value) {
                                            setAttributes({ singleFilterActiveStyle: { ...attributes.singleFilterActiveStyle, color: value } });
                                        }
                                    } )
                                ),
                                el( PanelBody, { title: 'Hover Style', initialOpen: false },
                                    el( 'p', {}, 'Background' ),
                                    el( ColorPalette, {
                                        value: attributes.singleFilterHoverStyle.background?attributes.singleFilterHoverStyle.background[attributes.selectedDevice]:"",
                                        onChange: function(value) { handleBackgroundChange(value, 'singleFilterHoverStyle')
                                         },
                                    } ),
                                    el( 'p', {}, 'Color' ),
                                    el( ColorPalette, {
                                        value: attributes.singleFilterHoverStyle?.color || '',
                                        onChange: function(value) {
                                            setAttributes({ singleFilterHoverStyle: { ...attributes.singleFilterHoverStyle, color: value } });
                                        }
                                    } )
                                ),
                                el( PanelBody, { title: 'Typography', initialOpen: false },
                                    el( FontSizePicker, {
                                        value: attributes.singleFilterInactiveStyle["font-size"]|| '',
                                        onChange: function(value) {
                                            setAttributes({ singleFilterInactiveStyle: { ...attributes.singleFilterInactiveStyle, "font-size": value } });
                                        }
                                    } )
                                ),
                                el( PanelBody, { title: 'Spacing', initialOpen: false },
                                    el( 'p', {}, 'Padding' ),
                                    el( CustomBoxControl, {
                                        values: attributes.singleFilterInactiveStyle.padding || { top: 0, right: 0, bottom: 0, left: 0 },
                                        unit: 'px',
                                        onChange: function(value) {
                                            setAttributes({ singleFilterInactiveStyle: { ...attributes.singleFilterInactiveStyle, padding: value } });
                                        }
                                    } ),
                                    el( 'p', {}, 'Margin' ),
                                    el( CustomBoxControl, {
                                        values: attributes.singleFilterInactiveStyle.margin || { top: 0, right: 0, bottom: 0, left: 0 },
                                        unit: 'px',
                                        onChange: function(value) {
                                            setAttributes({ singleFilterInactiveStyle: { ...attributes.singleFilterInactiveStyle, margin: value } });
                                        }
                                    } ),
                                    el( 'p', {}, 'Gap' ),
                                    el( RangeControl, {
                                        value: attributes.singleFilterContainerStyle.gap || 0,
                                        onChange: function(value) {
                                            setAttributes({ singleFilterContainerStyle: { ...attributes.singleFilterContainerStyle, gap: value } });
                                        }
                                    } )
                                ),
                                el( PanelBody, { title: 'Container Style', initialOpen: false },
                                    el( 'p', {}, 'Overflow' ),
                                    el( SelectControl, {
                                        value: attributes.singleFilterContainerStyle.overflow || 'visible',
                                        options: [
                                            { label: 'Visible', value: 'visible' },
                                            { label: 'Hidden', value: 'hidden' },
                                            { label: 'Scroll', value: 'scroll' },
                                            { label: 'Auto', value: 'auto' },
                                        ],
                                        onChange: function(value) {
                                            setAttributes({ singleFilterContainerStyle: { ...attributes.singleFilterContainerStyle, overflow: value } });
                                        }
                                    } ),
                                    el( 'p', {}, 'Flex Wrap' ),
                                    el( SelectControl, {
                                        value: attributes.singleFilterContainerStyle["flex-wrap"] || 'nowrap',
                                        options: [
                                            { label: 'No Wrap', value: 'nowrap' },
                                            { label: 'Wrap', value: 'wrap' },
                                            { label: 'Wrap Reverse', value: 'wrap-reverse' },
                                        ],
                                        onChange: function(value) {
                                            setAttributes({ singleFilterContainerStyle: { ...attributes.singleFilterContainerStyle, "flex-wrap": value } });
                                        }
                                    } )
                                )
                            ];
                        }
                        return null;
                        }
                        else if ( tab.name === 'advanced' ) {
                            return el( PanelBody, { title: 'Advanced' },
                                el( TextControl, {
                                    label: 'Extra Class Name',
                                    value: attributes.className,
                                    onChange: function( value ) {
                                        setAttributes( { className: value } );
                                    }
                                } ),
                                el('label', { htmlFor: 'custom-css-textarea' }, 'Custom CSS'),
                                el('textarea', {
                                    id: 'custom-css-textarea',
                                    value: attributes.customCSS,
                                    onChange: function(event) {
                                        setAttributes({ customCSS: event.target.value });
                                    },
                                    style: { width: '100%', height: '100px' }
                                })
                            );
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