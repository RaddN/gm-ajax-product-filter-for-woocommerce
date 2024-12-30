jQuery(document).ready(function($) {
    let advancesettings;
    let dapfforwc_options ;
    let front_page_slug;
    if (typeof dapfforwc_data !== 'undefined' && dapfforwc_data.front_page_slug) {
        front_page_slug = dapfforwc_data.front_page_slug;
    }
    if (typeof dapfforwc_data !== 'undefined' && dapfforwc_data.dapfforwc_options) {
        dapfforwc_options = dapfforwc_data.dapfforwc_options;
    }
    if (typeof dapfforwc_data !== 'undefined' && dapfforwc_data.dapfforwc_advance_settings) {
        advancesettings = dapfforwc_data.dapfforwc_advance_settings;
    }
    var rfilterbuttonsId = $('.rfilterbuttons').attr('id');
    // Initialize filters and handle changes
    $('#product-filter, .rfilterbuttons').on('change', handleFilterChange);
    // fetchFilteredProducts();
    var rfiltercurrentUrl = window.location.href;
    var path = window.location.pathname;
    rfiltercurrentUrl = rfiltercurrentUrl.split('?')[0];
    const urlParams = new URLSearchParams(window.location.search);
    const gmfilter = urlParams.get('filters');
    
    if (typeof dapfforwc_data !== 'undefined' && dapfforwc_data.slug) {
        
        const slugArray = dapfforwc_data.slug.split('/').filter(value => value !== '');
        console.log(slugArray);
        if (slugArray.length > 0) {
            const filtersString = slugArray.join(',');
            applyFiltersFromUrl(filtersString);
            updateUrlFilters(); 
        } 
    }else if(gmfilter){
        const slugtoArray = gmfilter.split('/').filter(value => value !== '');
        console.log(slugtoArray);
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
        return $('#product-filter input:checked').length > 0 ||
               $('.rfilterbuttons input:checked').length > 0;
    }
    let product_selector = advancesettings ? advancesettings["product_selector"] ?? 'ul.products':'ul.products';
    let pagination_selector = advancesettings ? advancesettings["pagination_selector"] ?? 'ul.page-numbers' : 'ul.page-numbers';
    function fetchFilteredProducts(page = 1) {
        $.post(dapfforwc_ajax.ajax_url, gatherFormData() +  `&paged=${page}&action=dapfforwc_filter_products`, function(response) {
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
        $(document).on('click', ` ${pagination_selector} a.page-numbers`, function(e) {
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
    function updateUrlFilters() {
        const selectedFilters = new Set();
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
        let filtersArray = Array.from(selectedFilters).filter(Boolean);
        if (typeof dapfforwc_data !== 'undefined' && dapfforwc_data.dapfforwc_options) {
            const dapfforwc_options = dapfforwc_data.dapfforwc_options;
            if (dapfforwc_options.default_filters) {
                var currentPage = path==="/"? front_page_slug : path.replace(/^\/|\/$/g, '');
                console.log(currentPage);
                var defaultFilters = dapfforwc_options.default_filters[currentPage];
                // Remove values from filtersArray that are present in defaultFilters
                filtersArray = filtersArray.filter(function (value) {
                    return !defaultFilters.includes(value);
                });
            }
        }
        const filterUse = dapfforwc_options?dapfforwc_options.use_filters_word_in_permalinks==="on"?"filters/":"":"";
        const newUrl = rfiltercurrentUrl 
        ? (filtersArray.length !== 0 
            ? `${rfiltercurrentUrl}${filterUse}${filtersArray.join('/')}` 
            : `${rfiltercurrentUrl}${filtersArray.join('/')}`) 
        : `${filtersArray.join('/')}`;
        history.replaceState(null, '', newUrl);
    }
    // for responsive
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




// cateogry hide & show manage for herichical

jQuery(document).ready(function($) {
    $('.show-sub-cata').on('click', function(event) {
        event.preventDefault();
        const $childCategories = $(this).closest('a').next('.child-categories');
        $childCategories.slideToggle(() => {
            $(this).text($childCategories.is(':visible') ? '-' : '+');
        });
    });
});



//  for price range

const rangeInput = document.querySelectorAll(".range-input input"),
  priceInput = document.querySelectorAll(".price-input input"),
  range = document.querySelector(".slider .progress");
let priceGap = 1000;

priceInput.forEach((input) => {
  input.addEventListener("input", (e) => {
    let minPrice = parseInt(priceInput[0].value),
      maxPrice = parseInt(priceInput[1].value);

    if (maxPrice - minPrice >= priceGap && maxPrice <= rangeInput[1].max) {
      if (e.target.className === "input-min") {
        rangeInput[0].value = minPrice;
        range.style.left = (minPrice / rangeInput[0].max) * 100 + "%";
      } else {
        rangeInput[1].value = maxPrice;
        range.style.right = 100 - (maxPrice / rangeInput[1].max) * 100 + "%";
      }
    }
  });
});

rangeInput.forEach((input) => {
  input.addEventListener("input", (e) => {
    let minVal = parseInt(rangeInput[0].value),
      maxVal = parseInt(rangeInput[1].value);

    if (maxVal - minVal < priceGap) {
      if (e.target.className === "range-min") {
        rangeInput[0].value = maxVal - priceGap;
      } else {
        rangeInput[1].value = minVal + priceGap;
      }
    } else {
      priceInput[0].value = minVal;
      priceInput[1].value = maxVal;
      range.style.left = (minVal / rangeInput[0].max) * 100 + "%";
      range.style.right = 100 - (maxVal / rangeInput[1].max) * 100 + "%";
    }
  });
});
