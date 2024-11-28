jQuery(document).ready(function($) {
    let styleoptions = [];
    if (typeof wcapf_data !== 'undefined' && wcapf_data.styleoptions) {
        styleoptions = wcapf_data.styleoptions;
    }
    var rfilterbuttonsId = $('.rfilterbuttons').attr('id');
    // Initialize filters and handle changes
    $('#product-filter, .rfilterbuttons').on('change', handleFilterChange);
    // fetchFilteredProducts();
    var rfilterindex = 0;
    var rfiltercurrentUrl = window.location.href;
    var path = window.location.pathname;
    rfiltercurrentUrl = rfiltercurrentUrl.split('?')[0];
    const urlParams = new URLSearchParams(window.location.search);
    const gmfilter = urlParams.get('gmfilter');
    
    if (typeof wcapf_data !== 'undefined' && wcapf_data.slug) {
        
        const slugArray = wcapf_data.slug.split('/').filter(value => value !== '');
        if (slugArray.length > 0) {
            const filtersString = slugArray.join(',');
            applyFiltersFromUrl(filtersString);
            updateUrlFilters(); 
        } 
    }else if(gmfilter){
        const slugtoArray = gmfilter.split('/').filter(value => value !== '');
        if (slugtoArray.length > 0) {
            const filtersString = slugtoArray.join(',');
            applyFiltersFromUrl(filtersString);
            updateUrlFilters(); 
        } 
    }
    else if (anyFilterSelected()) {
        fetchFilteredProducts();
    }
    function handleFilterChange(e) {
        e.preventDefault();
        updateUrlFilters(); // Update URL based on selected filters
        $('#roverlay').show();
        $('#loader').show();
        fetchFilteredProducts();
    }

    function anyFilterSelected() {
        return $('#product-filter input[type="checkbox"]:checked').length > 0 ||
               $('.rfilterbuttons input[type="checkbox"]:checked').length > 0;
    }

    function fetchFilteredProducts(page = 1) {
        $.post(wcapf_ajax.ajax_url, gatherFormData() +  `&paged=${page}&action=wcapf_filter_products`, function(response) {
            $('#roverlay').hide();
            $('#loader').hide();
            if (response.success) {
                $('ul.products').html(response.data.products);
                $('ul.page-numbers').html(response.data.pagination);
                if (typeof wcapf_data !== 'undefined' && wcapf_data.options) {
                    const options = wcapf_data.options;
                    if (!options.update_filter_options && rfilterindex<1) {
                updateFilterOptions(response.data.filters);
                 rfilterindex++;
                    }
                    if (options.update_filter_options) {
                        updateFilterOptions(response.data.filters);
                    }
                }
            } else {
                console.error('Error:', response.message);
            }
        }).fail(handleAjaxError);
    }
    function attachPaginationEvents() {
        $(document).on('click', '.woocommerce-pagination a.page-numbers', function(e) {
            e.preventDefault(); // Prevent the default anchor click behavior
            const url = $(this).attr('href'); // Get the URL from the link
            const page = new URL(url).searchParams.get('paged'); // Extract the page number
            $('#roverlay').show();
            $('#loader').show();
            fetchFilteredProducts(page); // Fetch products for the selected page
        });
    }
    
    // Call this function after updating the product listings
    attachPaginationEvents();
    function gatherFormData() {
        return $('#product-filter').serialize();
    }

    function handleAjaxError(xhr, status, error) {
        $('#roverlay').hide();
        $('#loader').hide();
        console.error('AJAX Error:', status, error);
    }
    async function countProducts(categorySlug = '', attribute = '', termSlug = '', tagSlug = '') {
        const response = await fetch(`/wp-json/custom/v1/count-products?category_slug=${categorySlug}&attribute=${attribute}&term_slug=${termSlug}&tag_slug=${tagSlug}`);
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
    
        const productCount = await response.json();
        return productCount;
    }

    function renderFilterOption(
        subOption,
        title,
        value,
        checked,
        name,
        attribute = '',
        singleValueSelect = 'no',
        count = 0
    ) {
        let output = '';
        const inputType = singleValueSelect === 'yes' ? 'radio' : 'checkbox';
    
        switch (subOption) {
            case 'checkbox':
            case 'radio_check':
            case 'radio':
                output += `<label>
                    <input type="${inputType}" class="filter-${subOption}" name="${name}" value="${value}"${checked}>
                    ${title}${count ? ` (${count})` : ''}
                </label>`;
                break;
    
            case 'square_check':
            case 'square':
                output += `<label class="square-option">
                    <input type="${inputType}" class="filter-${subOption}" name="${name}" value="${value}"${checked}>
                    <span>${title}${count ? ` (${count})` : ''}</span>
                </label>`;
                break;
    
            case 'checkbox_hide':
                output += `<label>
                    <input type="${inputType}" class="filter-checkbox" name="${name}" value="${value}"${checked} style="display:none;">
                    ${title}${count ? ` (${count})` : ''}
                </label>`;
                break;
    
            case 'color':
            case 'color_no_border':
                const color = styleOptions?.[attribute]?.colors?.[value] || '#000';
                const border = subOption === 'color_no_border' ? 'none' : '1px solid #000';
                output += `<label style="position: relative;">
                    <input type="${inputType}" class="filter-color" name="${name}" value="${value}"${checked}>
                    <span class="color-box" style="background-color: ${color}; border: ${border}; width: 30px; height: 30px;"></span>
                </label>`;
                break;
    
            case 'image':
            case 'image_no_border':
                const image = styleOptions?.[attribute]?.images?.[value] || 'default-image.jpg';
                const borderClass = subOption === 'image_no_border' ? 'no-border' : '';
                output += `<label class="image-option ${borderClass}">
                    <input type="${inputType}" class="filter-image" name="${name}" value="${value}"${checked}>
                    <img src="${image}" alt="${title}">
                </label>`;
                break;
    
            case 'select2':
            case 'select2_classic':
            case 'select':
                output += `<option class="filter-option" value="${value}"${checked}>${title}${count ? ` (${count})` : ''}</option>`;
                break;
    
            default:
                output += `<label>
                    <input type="checkbox" class="filter-checkbox" name="${name}" value="${value}"${checked}>
                    ${title}${count ? ` (${count})` : ''}
                </label>`;
                break;
        }
    
        return output;
    }
    function updateFilterOptions(filters) {
        // let subOptioncata = styleoptions["category"]["sub_option"];
        // let show_count = styleoptions["category"]["show_product_count"];
        // let singlevaluecataSelect = styleoptions["category"]["single_selection"];
        // updateFilterGroup('.filter-group.category .items', filters.categories, 'category[]',subOptioncata, singlevaluecataSelect,attribute="category",show_count);
        updateFilterGroup('.filter-group.category .items', filters.categories, 'category[]');
        updateFilterGroup('.filter-group.tags .items', filters.tags, 'tags[]');
        updateAttributes(filters.attributes);
        syncCheckboxSelections();
    }

    // async function fetchProductCounts(slugs) {
    //     // Modify this function to fetch counts for multiple slugs if your API supports it
    //     const counts = {};
    //     const promises = slugs.map(async slug => {
    //         try {
    //             const count = await countProducts(slug);
    //             counts[slug] = count; // Store the count by slug
    //         } catch (error) {
    //             console.error(`Error fetching count for ${slug}:`, error);
    //             counts[slug] = 0; // Fallback on error
    //         }
    //     });
    //     await Promise.all(promises);
    //     return counts; // Return an object with counts for all slugs
    // }
    // async function updateFilterGroup(selector, items, name, subOption, singleValueSelect = 'no', attribute = '', show_count) {
    //     let counts = {};
        
    //     // Fetch product counts if necessary
    //     if (show_count === "yes") {
    //         const slugs = items.map(item => item.slug); // Extract all slugs
    //         counts = await fetchProductCounts(slugs); // Fetch counts in a single call
    //     }
    
    //     // Build the select element if needed
    //     let addselect = '';
    //     if (["select", "select2", "select2_classic"].includes(subOption)) {
    //         addselect = `<select name="category[]" id="${subOption}" class="${subOption} filter-select" ${singleValueSelect !== "yes" ? 'multiple="multiple"' : ''}>`;
    //         addselect += '<option class="filter-checkbox" value="any"> Any </option>';
    //     }
    
    //     // Create filter options based on fetched counts
    //     const filterOptions = items.map(item => {
    //         const checked = isChecked(name, item.slug) ? ' checked="checked"' : '';
    //         const TotalNumProduct = show_count === "yes" ? counts[item.slug] || 0 : 0; // Use the fetched count
            
    //         return renderFilterOption(
    //             subOption,
    //             item.name,  // title
    //             item.slug,  // value
    //             checked,
    //             name,
    //             attribute,
    //             singleValueSelect,
    //             TotalNumProduct
    //         );
    //     });
    
    //     // Update the HTML with the rendered filter options
    //     $(selector).html(addselect + filterOptions.join(''));
    // }

    function updateFilterGroup(selector, items, name) {
        $(selector).html(items.map(item => {
            const checked = isChecked(name, item.slug) ? 'checked' : '';
            return `<label class="${checked}"><input type="checkbox" name="${name}" value="${item.slug}" ${checked}> ${item.name}</label>`;
        }).join(''));
    }

    function updateAttributes(attributes) {
        sortValues(attributes);
        $('.filter-group.attributes').html(Object.keys(attributes).map(name => {
            const termsHtml = attributes[name].map(term => {
                const checked = isChecked(`attribute[${name}][]`, term.slug) ? 'checked' : '';
                return `<label class="${checked}"><input type="checkbox" name="attribute[${name}][]" value="${term.slug}" ${checked}> ${term.name}</label>`;
            }).join('');
           let title = name.replace(/-/g, ' ');
           title = title.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
            return `<div id="${name}"><div class="title">${title}</div><div class="items">${termsHtml}</div></div>`;
        }).join(''));
    }

        // Function to sort values
        function sortValues(data) {
            const sortedData = {};
    
            $.each(data, function(key, values) {
                // Sort each array based on the name
                sortedData[key] = values.sort(function(a, b) {
                    return customSort(a.name, b.name);
                });
            });
    
            return sortedData;
        }
    
        // Custom sorting function
        function customSort(a, b) {
            // Check if both are dates
            const dateA = Date.parse(a);
            const dateB = Date.parse(b);
            if (!isNaN(dateA) && !isNaN(dateB)) {
                return dateA - dateB; // Sort as dates
            }
    
            // Check if both are numbers
            if (!isNaN(a) && !isNaN(b)) {
                return a - b; // Sort as numbers
            }
    
            // Otherwise, sort as strings
            return a.localeCompare(b);
        }

    function isChecked(name, value) {
        return $(`input[name="${name}"][value="${value}"]`).is(':checked');
    }

    function syncCheckboxSelections() {
        const $list = $('.rfilterbuttons ul').empty();
        $('#' + rfilterbuttonsId + ' input[type="checkbox"]').each(function() {
            const value = $(this).val();
            const checked = $(this).is(':checked');
            $list.append(createCheckboxListItem(value, checked));
        });
        attachCheckboxClickEvents();
        attachMainFilterChangeEvents();
    }
    function createCheckboxListItem(value, checked) {
        const formattedLabel = value.split('-').map(word => 
            word.charAt(0).toUpperCase() + word.slice(1)
        ).join(' ');
        return $('<li></li>').addClass(checked ? 'checked' : '').append(
            $('<input>', {
                name: 'attribute[' + rfilterbuttonsId + '][]',
                id: 'text_' + value,
                type: 'checkbox',
                value: value,
                checked: checked
            }).on('change', syncToMainFilter),
            $('<label></label>', {
                for: 'text_' + value,
                text: formattedLabel
            })
        );
    }

    function syncToMainFilter() {
        $(`#${rfilterbuttonsId} input[type="checkbox"][value="${$(this).val()}"]`).prop('checked', $(this).is(':checked'));
    }

    function attachCheckboxClickEvents() {
        $('.rfilterbuttons ul').off('click', 'li').on('click', 'li', function() {
            const checkbox = $(this).find('input[type="checkbox"]');
            checkbox.prop('checked', !checkbox.is(':checked')).trigger('change');
            $(this).toggleClass('checked', checkbox.is(':checked'));
        });
    }

    function attachMainFilterChangeEvents() {
        $('#' + rfilterbuttonsId + ' input[type="checkbox"]').on('change', function() {
            const relatedCheckbox = $(`.rfilterbuttons ul li input[value="${$(this).val()}"]`);
            relatedCheckbox.prop('checked', $(this).is(':checked')).closest('li').toggleClass('checked', $(this).is(':checked'));
        });
    }

    function applyFiltersFromUrl(filtersString) {
        
        filtersString.split(',').forEach(value => {
            $(`input[type="checkbox"][value="${value}"]`).prop('checked', true);
        });
        fetchFilteredProducts();
    }
    function updateUrlFilters() {
        const selectedFilters = new Set();
        $('#product-filter input[type="checkbox"]:checked').each(function() {
            selectedFilters.add($(this).val());
        });
        let filtersArray = Array.from(selectedFilters);
        if (typeof wcapf_data !== 'undefined' && wcapf_data.options) {
            const options = wcapf_data.options;
            if (options.default_filters) {
                var currentPage = path.replace(/^\/|\/$/g, '');
                var defaultFilters = options.default_filters[currentPage];
                // Remove values from filtersArray that are present in defaultFilters
                filtersArray = filtersArray.filter(function (value) {
                    return !defaultFilters.includes(value);
                });
            }
        }
        const newUrl = rfiltercurrentUrl?`${rfiltercurrentUrl}${filtersArray.join('/')}`:`${filtersArray.join('/')}`;
        history.replaceState(null, '', newUrl);
    }
});


jQuery(document).ready(function($) {
    function isMobile() {
        return $(window).width() <= 768;
    }
    function textChange() {
        if (isMobile()) {
            $('#product-filter .filter-group div .title').each(function() {
                $(this).text($(this).text().split(' ').pop());
            });
            $('#product-filter .items').hide();
        }
    }
    textChange();
     $(document).ajaxComplete(function() {
        textChange();
    });
            // Use event delegation for dynamically added elements
            $('#product-filter').on('click', '.title', function(event) {
                if (isMobile()) {
                event.stopPropagation();
                $('#product-filter .items').hide();
                $(this).next('.items').slideToggle(); // Toggle items with a sliding effect
                }
            });
            $(document).on('click', function() {
                if (isMobile()) {
                    $('.items').hide();
                }
            });
});
