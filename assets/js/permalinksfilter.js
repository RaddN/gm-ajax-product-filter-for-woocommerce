jQuery(document).ready(function($) {
    // Initialize filters and handle changes
    $('#product-filter, .rfilterbuttons').on('change', handleFilterChange);
    fetchFilteredProducts();
    var index = 0;
    const currentUrl = window.location.href.replace(/\/$/, '');
    // Create a function to extract values after the last segment
function extractValuesAfterLastSegment(url) {
    // Create an anchor element to easily parse the URL
    const a = document.createElement('a');
    a.href = url;
    // Get the pathname from the URL
    const pathname = a.pathname;
    // Split the pathname by '/' and filter out any empty strings
    const valuesArray = pathname.split('/').filter(value => value !== '');
    // Return all values after the last segment
    return valuesArray.slice(1); // Skip the first segment
}
const extractedValues = extractValuesAfterLastSegment(currentUrl);
    if (extractedValues.length > 0) {
        const filtersString = extractedValues.join(',');
        applyFiltersFromUrl(filtersString);
    } else if (anyFilterSelected()) {
        $('#loader').show();
        fetchFilteredProducts();
    }

    function handleFilterChange(e) {
        e.preventDefault();
        updateUrlFilters(); // Update URL based on selected filters
        $('#loader').show();
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
                    if (!options.update_filter_options && index<1) {
                updateFilterOptions(response.data.filters);
                 index++;
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
            return `<div id="${name}"><div class="title">${name}</div><div class="items">${termsHtml}</div></div>`;
        }).join(''));
    }

    function isChecked(name, value) {
        return $(`input[name="${name}"][value="${value}"]`).is(':checked');
    }

    function syncCheckboxSelections() {
        const $list = $('.rfilterbuttons ul').empty();
        $('#conference-by-month input[type="checkbox"]').each(function() {
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
                name: 'attribute[conference-by-month][]',
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
        $(`#conference-by-month input[type="checkbox"][value="${$(this).val()}"]`).prop('checked', $(this).is(':checked'));
    }

    function attachCheckboxClickEvents() {
        $('.rfilterbuttons ul').off('click', 'li').on('click', 'li', function() {
            const checkbox = $(this).find('input[type="checkbox"]');
            checkbox.prop('checked', !checkbox.is(':checked')).trigger('change');
            $(this).toggleClass('checked', checkbox.is(':checked'));
        });
    }

    function attachMainFilterChangeEvents() {
        $('#conference-by-month input[type="checkbox"]').on('change', function() {
            const relatedCheckbox = $(`.rfilterbuttons ul li input[value="${$(this).val()}"]`);
            relatedCheckbox.prop('checked', $(this).is(':checked')).closest('li').toggleClass('checked', $(this).is(':checked'));
        });
    }

    function applyFiltersFromUrl(filtersString) {
        
        filtersString.split(',').forEach(value => {
            $(`input[type="checkbox"][value="${value}"]`).prop('checked', true);
        });
        $('#loader').show();
        fetchFilteredProducts();
    }


    function updateUrlFilters() {
        const selectedFilters = new Set();
        $('#product-filter input[type="checkbox"]:checked').each(function() {
            selectedFilters.add($(this).val());
        });
        const filtersArray = Array.from(selectedFilters);
        const { domain, currentPath } = extractDomainAndPath(window.location);
        const newUrl = `${domain}/${currentPath}/${filtersArray.join('/')}`;

        history.replaceState(null, '', newUrl);
    }
    function extractDomainAndPath(url) {
        const a = document.createElement('a');
        a.href = url;
        const domain = a.protocol + '//' + a.host;
        const pathname = a.pathname.split('/').filter(value => value !== '');
        return {
            domain: domain,
            currentPath: pathname[0] || null // Return the first segment or null if not found
        };
    }
});
