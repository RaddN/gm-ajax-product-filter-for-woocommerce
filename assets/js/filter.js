jQuery(document).ready(function($) {
    let advancesettings,dapfforwc_options;
    if (typeof dapfforwc_data !== 'undefined' && dapfforwc_data.dapfforwc_advance_settings) {
        advancesettings = dapfforwc_data.dapfforwc_advance_settings;
    }
    if (typeof dapfforwc_data !== 'undefined' && dapfforwc_data.dapfforwc_options) {
        dapfforwc_options = dapfforwc_data.dapfforwc_options;
    }
    // Initialize filters
    var rfilterbuttonsId = $('.rfilterbuttons').attr('id');
    var path = window.location.pathname;
    var currentPage = path.replace(/^\/|\/$/g, '');
    var orderby;
    let selectedValesbyuser = store_selected_values();
    // Initialize filters and handle changes
    $('#product-filter, .rfilterbuttons').on('change', handleFilterChange);
    $('#product-filter, .rfilterbuttons').on('submit', handleFilterChange);
    $('.woocommerce-ordering select').on('change', function(event) {
        // Prevent the default form submission and page reload
        event.preventDefault();

        // Get the selected value
        orderby = $(this).val();
        fetchFilteredProducts();

    });
     // Prevent form submission on pressing Enter
     $('.woocommerce-ordering').on('submit', function(event) {
        event.preventDefault();
    });
    
    
    fetchFilteredProducts();
    var rfilterindex = 0;
    
    // Handle filter changes and AJAX loading
    function handleFilterChange(e) {
        e.preventDefault();
        if (!anyFilterSelected()) return location.reload();
        selectedValesbyuser = store_selected_values();
        syncCheckboxSelections();
        $('#roverlay').show();
        $('#loader').show();
        fetchFilteredProducts();
    }
    function store_selected_values() {
        let selectedValues = [];
    
        // Get selected values from checkboxes and radio buttons
        selectedValues = selectedValues.concat(
            $('#product-filter input:checked').map(function() {
                return $(this).val();
            }).get()
        );
    
        // Get selected values from select elements
        $('#product-filter select').each(function() {
            const values = $(this).val();
            if (values) { // Check if a value is selected
                selectedValues = selectedValues.concat(values);
            }
        });
    
        return selectedValues;
    }
    function selectfromurl(){
        let urlvalues = currentPage.split('/');
    urlvalues.forEach(value => {
        // Check the input checkbox
        if ($(`input[value="${value}"]`).length) {
            $(`input[value="${value}"]`).attr('checked', true);
        } else if ($(`select option[value="${value}"]`).length) {
            // If no input found, check dropdown option
            $(`select option[value="${value}"]`).prop('selected', true);
        }
    });
    }
    selectfromurl();
    function anyFilterSelected() {
        const inputchecked = $('#product-filter input:checked').length > 0;
        const selectSelected = $('#product-filter select').filter(function() { return this.value; }).length > 0;
        const textInputSelected = $('#product-filter input[type="text"]').filter(function() { return this.value.trim() !== ""; }).length > 0;
        const numberInputSelected = $('#product-filter input[type="number"]').filter(function() { return this.value.trim() !== ""; }).length > 0;
        const range = $('#product-filter input[type="range"]').filter(function() { return this.value.trim() !== ""; }).length > 0;
    
        return inputchecked || selectSelected || textInputSelected || numberInputSelected || range;
    }
    let product_selector = advancesettings ? advancesettings["product_selector"] ?? 'ul.products':'ul.products';
    let pagination_selector = advancesettings ? advancesettings["pagination_selector"] ?? 'ul.page-numbers' : 'ul.page-numbers';
    let productSelector_shortcode = $('#product-filter').data('product_selector');
    let paginationSelector_shortcode = $('#product-filter').data('pagination_selector');
   
    function fetchFilteredProducts(page = 1) {
        selectfromurl();
        $.post(dapfforwc_ajax.ajax_url, gatherFormData() + `&selectedvalues=${selectedValesbyuser}&orderby=${orderby}&paged=${page}&action=dapfforwc_filter_products`, function(response) {
            $('#roverlay').hide();
            $('#loader').hide();
            if (response.success) {
                $(productSelector_shortcode ?? product_selector).html(response.data.products);
                $('.woocommerce-result-count').text(`${response.data.total_product_fetch} results found`);
                $('#rcountproduct').text(`show(${response.data.total_product_fetch})`);
                if(dapfforwc_options["update_filter_options"]==="on"){
                    $('#product-filter div').remove();
                    $("form#product-filter").append(response.data.filter_options);
                    }
                $(paginationSelector_shortcode ?? pagination_selector).html(response.data.pagination);
                syncCheckboxSelections();
            } else {
                console.error('Error:', response.message);
            }
        }).fail(handleAjaxError);
    }
    function attachPaginationEvents() {
        $(document).on('click', `${paginationSelector_shortcode ?? pagination_selector} a.page-numbers`, function(e) {
            e.preventDefault(); // Prevent the default anchor click behavior
            const url = $(this).attr('href'); // Get the URL from the link
            const page = new URL(url).searchParams.get('paged'); // Extract the page number
            $('#roverlay').show();
            $('#loader').show();
            fetchFilteredProducts(page); // Fetch products for the selected page
        });
    }
    
    // Call this function after updating the product listings
    if($('#product-filter').length){
        attachPaginationEvents();
        }
    function changePseudoElementContent(beforeContent, afterContent) {
        // Create a new style element
        var style = $('<style></style>');
        style.text(`
            .progress-percentage:before { 
                content: "${beforeContent}"; 
            }
            .progress-percentage:after { 
                content: "${afterContent}"; 
            }
        `);
        
        // Append the style to the head
        $('head').append(style);
    }

    function gatherFormData() {
        const currentPageSlug = path === "/" ? path : path.replace(/^\/|\/$/g, '');
        
        const formData = $('#product-filter').serialize();
        
        // price range
        const rangeInput = document.querySelectorAll(".range-input input"),
        priceInput = document.querySelectorAll(".price-input input"),
        range = document.querySelector(".slider .progress");
        let minPrice = rangeInput[0]?parseInt(rangeInput[0].value):0,
        maxPrice = rangeInput[1]?parseInt(rangeInput[1].value):0;
        const minPriceDefault = rangeInput[0]?parseInt(rangeInput[0].value):0;
        changePseudoElementContent(`$${minPrice}`, `$${maxPrice}`);
        rangeInput.forEach((input) => {
            input.addEventListener("input", (e) => {
                minPrice = parseInt(rangeInput[0].value) || 0; // Default to 0 if NaN
                maxPrice = parseInt(rangeInput[1].value) || 0; // Default to 0 if NaN
                changePseudoElementContent(`$${minPrice}`, `$${maxPrice}`);
                priceInput[0].value = minPrice;
                priceInput[1].value = maxPrice;
                range.style.left = ((minPrice - minPriceDefault)/ (rangeInput[0].max - minPriceDefault)) * 100 + "%";
                range.style.right = 100 - (maxPrice / rangeInput[1].max) * 100 + "%";
            });
          });
          priceInput.forEach((input) => {
            input.addEventListener("input", (e) => {
              let minPrice = parseInt(priceInput[0].value),
                maxPrice = parseInt(priceInput[1].value);
                if (e.target.className === "input-min") {
                  rangeInput[0].value = minPrice;
                  range.style.left = (minPrice / rangeInput[0].max) * 100 + "%";
                } else {
                  rangeInput[1].value = maxPrice;
                  range.style.right = 100 - (maxPrice / rangeInput[1].max) * 100 + "%";
                }
              
            });
          });
    //   price range ends
        
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
        // reset button
        $(document).on('click', '#reset-rating', function(event) {
            event.preventDefault();
            $('input[name="rating[]"]').prop('checked', false);
            fetchFilteredProducts();
        });
    function attachMainFilterChangeEvents() {
        $('#' + rfilterbuttonsId + ' input').on('change', function() {
            const relatedCheckbox = $(`.rfilterbuttons ul li input[value="${$(this).val()}"]`);
            relatedCheckbox.prop('checked', $(this).is(':checked')).closest('li').toggleClass('checked', $(this).is(':checked'));
        });
    }
    // create list of current selected filter
    function selectedFilterShowProductTop(){
        // Clear existing content
    $('.rfilterselected ul').empty();
    for (let value of selectedValesbyuser) {
        $('.rfilterselected ul').append(`
            <li class="checked">
                <input id="selected_${value}" type="checkbox" value="${value}" checked>
                <label for="selected_${value}">${value.replace(/-/g, ' ')}</label>
                <label style="font-size:12px;margin-left:5px;">x</label>
            </li>`);
    }}
    selectedFilterShowProductTop();
    syncCheckboxSelections();

    $('.rfilterselected').on('change', 'li', function(e) {
        const value = $(this).find('input[type="checkbox"]').val(); 
        $(`#product-filter input[value="${value}"]`).prop('checked', false);
        handleFilterChange(e);
    });
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
        noproductfound();
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

             // Show message if no products found
             noproductfound();

             function noproductfound() {
                 if ($("form#product-filter").children().length === 2) {
                     $(productSelector_shortcode ?? product_selector).html('<p>No products found</p>');
                     $(paginationSelector_shortcode ?? pagination_selector).html('');
                 }
             }
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

