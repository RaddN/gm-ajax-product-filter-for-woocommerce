jQuery(document).ready(function($) {
    let advancesettings;
    if (typeof dapfforwc_data !== 'undefined' && dapfforwc_data.dapfforwc_advance_settings) {
        advancesettings = dapfforwc_data.dapfforwc_advance_settings;
    }
    // Initialize filters
    var rfilterbuttonsId = $('.rfilterbuttons').attr('id');
    var path = window.location.pathname;
    $('#product-filter, .rfilterbuttons').on('change', handleFilterChange);
    fetchFilteredProducts();
    var rfilterindex = 0;
    // Handle filter changes and AJAX loading
    function handleFilterChange(e) {
        e.preventDefault();
        if (!anyFilterSelected()) return location.reload();
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
        $.post(dapfforwc_ajax.ajax_url, gatherFormData() + `&paged=${page}&action=dapfforwc_filter_products`, function(response) {
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
