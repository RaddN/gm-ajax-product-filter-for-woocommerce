jQuery(document).ready(function($) {
    let advancesettings;
    if (typeof wcapf_data !== 'undefined' && wcapf_data.advance_settings) {
        advancesettings = wcapf_data.advance_settings;
    }
    if (typeof wcapf_data !== 'undefined' && wcapf_data.product_count) {
        product_count = wcapf_data.product_count;
    }
    var rfilterbuttonsId = $('.rfilterbuttons').attr('id');
    var path = window.location.pathname;
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
                syncCheckboxSelections();
            } else {
                console.error('Error:', response.message);
            }
        }).fail(handleAjaxError);
    }
    function attachPaginationEvents() {
        $(document).on('click', `${pagination_selector} a.page-numbers`, function(e) {
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
        const currentPageSlug = path === "/" ? path : path.replace(/^\/|\/$/g, '');
        
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




