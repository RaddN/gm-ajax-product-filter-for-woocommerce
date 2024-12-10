jQuery(document).ready(function($) {
    let styleoptions = [];
    let advancesettings;
    if (typeof wcapf_data !== 'undefined' && wcapf_data.styleoptions) {
        styleoptions = wcapf_data.styleoptions;
    }
    if (typeof wcapf_data !== 'undefined' && wcapf_data.advance_settings) {
        advancesettings = wcapf_data.advance_settings;
    }
    if (typeof wcapf_data !== 'undefined' && wcapf_data.product_count) {
        product_count = wcapf_data.product_count;
    }
    var rfilterbuttonsId = $('.rfilterbuttons').attr('id');
    // Initialize filters
    $('#product-filter, .rfilterbuttons').on('change', handleFilterChange);
    var rfilterindex = 0;
    // Check if URL contains filters and load products accordingly
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('filters')) {
        applyFiltersFromUrl(urlParams.get('filters'));
    } else {
        // If no URL filters, check current checked items and fetch products
        if (anyFilterSelected()) {
            fetchFilteredProducts();
        }
    }

    function handleFilterChange(e) {
        e.preventDefault();
        if (!anyFilterSelected()) return location.reload();
        $('#roverlay').show();
        $('#loader').show();
        updateUrlFilters(); // Update the URL based on current filters
        fetchFilteredProducts();
    }

    function anyFilterSelected() {
        return $('#product-filter input[type="checkbox"]:checked').length > 0 ||
               $('.rfilterbuttons input[type="checkbox"]:checked').length > 0;
    }
    let product_selector = advancesettings ? advancesettings["product_selector"] ?? 'ul.products':'ul.products';
    let pagination_selector = advancesettings ? advancesettings["pagination_selector"] ?? 'ul.page-numbers' : 'ul.page-numbers';
   
    function fetchFilteredProducts(page = 1) {
        $.post(wcapf_ajax.ajax_url, gatherFormData() + `&paged=${page}&action=wcapf_filter_products`, function(response) {
            $('#roverlay').hide();
            $('#loader').hide();
            if (response.success) {
                $(product_selector).html(response.data.products);
                $(pagination_selector).html(response.data.pagination);
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
        const currentPageSlug = "upcoming-conferences";
        
        const formData = $('#product-filter').serialize();
        const minPrice = $('#min-price').val();
        const maxPrice = $('#max-price').val();
        
        // Append price filters if values exist
        let priceParams = '';
        if (minPrice) priceParams += `&min_price=${encodeURIComponent(minPrice)}`;
        if (maxPrice) priceParams += `&max_price=${encodeURIComponent(maxPrice)}`;
        
        return formData + priceParams + `&current-page=${encodeURIComponent(currentPageSlug)}`;
    }

    function handleAjaxError(xhr, status, error) {
        $('#roverlay').hide();
        $('#loader').hide();
        console.error('AJAX Error:', status, error);
    }
    function renderFilterOption(
        subOption,
        title,
        value,
        checked,
        name,
        attribute = '',
        singleValueSelect = 'no',
        count = 0,
        styleOptions
    ) {
        let output = '';
        const inputType = singleValueSelect === 'yes' ? 'radio' : 'checkbox';
    
        switch (subOption) {
            case 'checkbox':
            case 'radio_check':
            case 'radio':
                output += `<label>
                    <input type="${inputType}" class="filter-${subOption}" name="${name}" value="${value}" ${checked}>
                    ${title}${count ? ` (${count})` : ''}
                </label>`;
                break;
    
            case 'square_check':
            case 'square':
                output += `<label class="square-option">
                    <input type="${inputType}" class="filter-${subOption}" name="${name}" value="${value}" ${checked}>
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
        let subOptioncata = styleoptions["category"]?styleoptions["category"]["sub_option"]:"";
        let subOptiontag = styleoptions["tag"]?styleoptions["tag"]["sub_option"]:"";
        let show_count = styleoptions["category"]?styleoptions["category"]["show_product_count"]:"";
        let show_counttag = styleoptions["tag"]?styleoptions["tag"]["show_product_count"]:"";
        let singlevaluecataSelect = styleoptions["category"]? styleoptions["category"]["single_selection"]:"";
        let singlevaluetagSelect = styleoptions["tag"]?styleoptions["tag"]["single_selection"]:"";
        let styleSettings = styleoptions;
        updateFilterGroup('.filter-group.category .items', filters.categories, 'category[]',subOptioncata, singlevaluecataSelect,attribute="category",show_count, styleSettings);
        // updateFilterGroup('.filter-group.category .items', filters.categories, 'category[]');
        updateFilterGroup('.filter-group.tags .items', filters.tags, 'tag[]',subOptiontag, singlevaluetagSelect,attribute="tag",show_counttag, styleSettings);
        // updateFilterGroup('.filter-group.tags .items', filters.tags, 'tags[]');
        updateAttributes(filters.attributes);
        syncCheckboxSelections();
    }

    function updateFilterGroup(selector, items, name, subOption, singleValueSelect = 'no', attribute = '', show_count, styleOptions) {       
        // Create filter options based on fetched counts
        const filterOptions = items.map(item => {
            const checked = isChecked(name, item.slug) ? 'checked' : '';
            let TotalNumProduct = 0; 
            // Fetch product counts if necessary
            if (show_count === "yes" && attribute==="category") {
                TotalNumProduct = product_count["categories"][item.slug];
            }else if(show_count === "yes" && attribute==="tag"){
                TotalNumProduct = product_count["tags"][item.slug];
            }
            if (subOption === "select" || subOption === "select2" || subOption === "select2_classic") {
                filterOptions.push('<option class="filter-checkbox" value=""> Any </option>');
            }
            return renderFilterOption(
                subOption,
                item.name,  // title
                item.slug,  // value
                checked,
                name,
                attribute,
                singleValueSelect,
                TotalNumProduct,
                styleOptions
            );
        });
        
        // Update the HTML with the rendered filter options
        $(selector).html(filterOptions.join(''));
    }

    function updateAttributes(attributes) {
        sortValues(attributes);
        const filterHtml = Object.keys(attributes).map(name => {
            const subOptionattr = styleoptions[name]?styleoptions[name]["sub_option"]:"";
            const termsHtml = generateTermsHtml(attributes[name], name, subOptionattr);
            const title = formatAttributeTitle(name);
    
            return generateAttributeHtml(name, title, termsHtml, subOptionattr);
        }).join('');
    
        $('.filter-group.attributes').html(filterHtml);
    }
    
    function generateTermsHtml(terms, attributeName, subOptionattr) {
        return terms.map(term => {
            const checked = isChecked(`attribute[${attributeName}][]`, term.slug) ? 'checked' : '';
            const singleValueSelect =styleoptions[attributeName]? styleoptions[attributeName]["single_selection"]:"";
            let show_count = styleoptions[attributeName]?styleoptions[attributeName]["show_product_count"]:"";
            const styleSettings = styleoptions;
            let TotalNumProduct = 0; 
            if (show_count === "yes") {
                TotalNumProduct = product_count["attributes"]['pa_'+attributeName][term.slug];
            }
            
            return renderFilterOption(
                subOptionattr,
                term.name,  // title
                term.slug,  // value
                checked,
               `attribute[${attributeName}][]`,
                attributeName,
                singleValueSelect,
                TotalNumProduct, // Assuming count is 0 as you have not shown the count logic
                styleSettings
            );
        }).join('');
    }
    
    function formatAttributeTitle(name) {
        let title = name.replace(/-/g, ' ');
        return title.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    }
    
    function generateAttributeHtml(name, title, termsHtml, subOptionattr) {
        if (subOptionattr === "select" || subOptionattr === "select2" || subOptionattr === "select2_classic") {
            return `<div id="${name}">
                        <div class="title">${title}</div>
                        <select name="attribute[${name}][]" id="${subOptionattr}" class="items ${subOptionattr} filter-select"><option class="filter-checkbox" value=""> Any </option>${termsHtml}</select>
                    </div>`;
        }
        return `<div id="${name}">
                    <div class="title">${title}</div>
                    <div class="items ${subOptionattr}">
                        ${termsHtml}
                    </div>
                </div>`;
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
    function isSelected(selectName, optionValue) {
        return $(`select[name="${selectName}"] option[value="${optionValue}"]`).is(':selected');
    }

    function syncCheckboxSelections() {
        const $list = $('.rfilterbuttons ul').empty();
        $('#product-filter #' + rfilterbuttonsId + ' input').each(function() {
            const value = $(this).val();
            const checked = $(this).is(':checked');
            const type = this.type;
            $list.append(createCheckboxListItem(value, checked, type));
        });
        $('#product-filter #' + rfilterbuttonsId + ' option').each(function(index) {
            // Skip the first option (index 0)
            if (index === 0) {
                return; // Skip this iteration
            }
            const value = $(this).val();
            const checked = $(this).is(':checked');
            const type = this.type;
        
            $list.append(createCheckboxListItem(value, checked, type));
        });
        attachCheckboxClickEvents();
        attachMainFilterChangeEvents();
    }

    function createCheckboxListItem(value, checked, type) {
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
        $(`#product-filter #${rfilterbuttonsId} input[value="${$(this).val()}"]`).prop('checked', $(this).is(':checked'));
        $(`#product-filter #${rfilterbuttonsId} select option[value="${$(this).val()}"]`).prop('selected', $(this).is(':checked'));
    }

    function attachCheckboxClickEvents() {
        $('.rfilterbuttons ul').off('click', 'li').on('click', 'li', function() {
            const checkbox = $(this).find('input');
            checkbox.prop('checked', !checkbox.is(':checked')).trigger('change');
            $(this).toggleClass('checked', checkbox.is(':checked'));
        });
    }

    function attachMainFilterChangeEvents() {
        $('#' + rfilterbuttonsId + ' input').on('change', function() {
            const relatedCheckbox = $(`.rfilterbuttons ul li input[value="${$(this).val()}"]`);
            relatedCheckbox.prop('checked', $(this).is(':checked')).closest('li').toggleClass('checked', $(this).is(':checked'));
        });
    }
    function applyFiltersFromUrl(filtersString) {
        if (!filtersString) {
            return; // Early return if the string is empty
        }
    
        const filterValues = filtersString.split(',');
    
        filterValues.forEach(value => {
                // Check the input checkbox
                if ($(`input[value="${value}"]`).length) {
                    $(`input[value="${value}"]`).attr('checked', true);
                } else if ($(`select option[value="${value}"]`).length) {
                    // If no input found, check dropdown option
                    $(`select option[value="${value}"]`).prop('selected', true);
                } else {
                    console.log(`Filter "${value}" not found in inputs or dropdown.`);
                }
        });
    
        fetchFilteredProducts(); // Fetch products after applying filters
    }

    // Update URL filters based on current selections
    function updateUrlFilters() {
        const selectedFilters = new Set(); // Use Set to prevent duplicates

        // Gather selected filters from the product-filter
        $('#product-filter input:checked').each(function() {
            selectedFilters.add($(this).val());
        });
        // Gather selected options from the select dropdown
        $('#product-filter select').each(function() {
            // Add each selected option to the Set
            $(this).find('option:selected').each(function() {
                selectedFilters.add($(this).val());
            });
        });

        // Gather selected filters from rfilterbuttons
        $('.rfilterbuttons input:checked').each(function() {
            const value = $(this).val();
            selectedFilters.add(value); // Add the value to the Set
        });

        // Create the filters query string from the Set
        let filtersQueryString = Array.from(selectedFilters); // Convert Set back to array
        if (typeof wcapf_data !== 'undefined' && wcapf_data.options) {
            const options = wcapf_data.options;
            if (options.default_filters) {
                var path = window.location.pathname;
                var currentPage = path.replace(/^\/|\/$/g, '');
                var defaultFilters = options.default_filters[currentPage];
                // Remove values from filtersArray that are present in defaultFilters
                filtersQueryString = filtersQueryString.filter(function (value) {
                    return !defaultFilters.includes(value);
                });
            }
        }
        filtersQueryString.join(',');
        const newUrl = `?filters=${filtersQueryString}`;
        
        // Update the browser's URL without reloading the page
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




