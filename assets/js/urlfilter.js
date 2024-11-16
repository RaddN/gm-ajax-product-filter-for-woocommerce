jQuery(document).ready(function($) {
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
            $('#loader').show();
            fetchFilteredProducts();
        }
    }

    function handleFilterChange(e) {
        e.preventDefault();
        if (!anyFilterSelected()) return location.reload();
        $('#loader').show();
        updateUrlFilters(); // Update the URL based on current filters
        fetchFilteredProducts();
    }

    function anyFilterSelected() {
        return $('#product-filter input[type="checkbox"]:checked').length > 0 ||
               $('.rfilterbuttons input[type="checkbox"]:checked').length > 0;
    }

    function fetchFilteredProducts() {
        $.post(wcapf_ajax.ajax_url, gatherFormData() + '&action=wcapf_filter_products', function(response) {
            $('#loader').hide();
            if (response.success) {
                $('ul.products').html(response.data.products);
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

    function gatherFormData() {
        return $('#product-filter').serialize();
    }

    function handleAjaxError(xhr, status, error) {
        $('#loader').hide();
        console.error('AJAX Error:', status, error);
    }

    function updateFilterOptions(filters) {
        updateFilterGroup('.filter-group.category', filters.categories, 'category[]');
        updateFilterGroup('.filter-group.tags', filters.tags, 'tags[]');
        updateAttributes(filters.attributes);
        syncCheckboxSelections();
    }

    function updateFilterGroup(selector, items, name) {
        $(selector).html(items.map(item => {
            const checked = isChecked(name, item.slug) ? 'checked' : '';
            return `<label class="${checked}"><input type="checkbox" name="${name}" value="${item.slug}" ${checked}> ${item.name}</label><br>`;
        }).join(''));
    }

    function updateAttributes(attributes) {
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
                text: value
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
    function applyFiltersFromUrl(filters) {
        const filterParams = filters.split(',');
        filterParams.forEach(value => {
            // Check the checkbox for each filter value found
            $(`input[type="checkbox"][value="${value}"]`).prop('checked', true);
        });

        // After setting checkboxes, fetch products based on selected filters
        $('#loader').show();
        fetchFilteredProducts(); // Fetch products after setting checkboxes
    }

    // Update URL filters based on current selections
    function updateUrlFilters() {
        const selectedFilters = new Set(); // Use Set to prevent duplicates

        // Gather selected filters from the product-filter
        $('#product-filter input[type="checkbox"]:checked').each(function() {
            const value = $(this).val();
            selectedFilters.add(value); // Add the value to the Set
        });

        // Gather selected filters from rfilterbuttons
        $('.rfilterbuttons input[type="checkbox"]:checked').each(function() {
            const value = $(this).val();
            selectedFilters.add(value); // Add the value to the Set
        });

        // Create the filters query string from the Set
        const filtersQueryString = Array.from(selectedFilters).join(','); // Convert Set back to array
        const newUrl = `?filters=${filtersQueryString}`;
        
        // Update the browser's URL without reloading the page
        history.replaceState(null, '', newUrl);
    }
});
