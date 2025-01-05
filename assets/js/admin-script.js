if(document.getElementById("custom_template_input")){
const editor = CodeMirror(document.getElementById("code-editor"), {
            value: document.getElementById("custom_template_input").value,
            mode: "text/html",
            lineNumbers: true,
            lineWrapping: true
        });

        editor.on("change", function() {
            document.getElementById("custom_template_input").value = editor.getValue();
        });


        function insertPlaceholder(placeholder) {
            const cursor = editor.getCursor();
            editor.replaceRange(placeholder, cursor);
            editor.focus();
        }
    }

// form manage script show & hide custom template
    document.addEventListener("DOMContentLoaded", function() {
        const checkbox = document.querySelector('input[name="dapfforwc_options[use_custom_template]"]');
        
        // Function to show or hide the closest <tr> with the .custom_template_code class
        function toggleTemplateRow() {
          const closestTr = document.querySelector(".custom_template_code").closest('tr');
          
          // Check if the next sibling has the class 'custom_template_code'
          if (closestTr) {
            if (checkbox.checked) {
              closestTr.style.display = ''; // Show the row
            } else {
              closestTr.style.display = 'none'; // Hide the row
            }
          }
        }
        // Event listener for checkbox change
        checkbox.addEventListener('change', toggleTemplateRow);
    
        // Initialize the state on page load
        toggleTemplateRow();
      });

    
 //   page manage
     document.querySelector('.page-inputs .add-page').addEventListener('click', function() {
            var newPage = document.createElement('div');
            newPage.classList.add('page-item');
            newPage.innerHTML = '<input type="text" name="dapfforwc_options[pages][]" value="" placeholder="Add new page" /> <button type="button" class="remove-page">Remove</button>';
            document.querySelector('.page-list').appendChild(newPage);
        });

        document.querySelector('.page-list').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-page')) {
                e.target.closest('.page-item').remove();
            }
        });

        document.addEventListener("DOMContentLoaded", function () {
    // Get the checkbox element
    const pagesFilterAutoCheckbox = document.querySelector('input[name="dapfforwc_options[pages_filter_auto]"]');
    // Get all table rows
    const rows = document.querySelectorAll(".page_manage .form-table > tbody > tr");

    // Function to toggle row visibility
    function toggleRows() {
        rows.forEach((row, index) => {
            if (index === 0 || !pagesFilterAutoCheckbox.checked) {
                row.style.display = "table-row";
            } else {
                row.style.display = "none";
            }
        });
    }

    // Attach change event to the checkbox
    pagesFilterAutoCheckbox.addEventListener("change", toggleRows);

    // Initial toggle based on the checkbox state
    toggleRows();
});
    

// loading effect popup
document.addEventListener('DOMContentLoaded', function() {
    const customizeLoaderLink = document.getElementById('customize_loader');
    const popup = document.getElementById('custom-loading-popup');
    const closePopup = document.querySelector('.close-popup');
    const saveEffectButton = document.getElementById('save-effect');
    let selectedEffect = null;

    customizeLoaderLink.addEventListener('click', function(e) {
        e.preventDefault();
        popup.style.display = 'flex';
    });

    closePopup.addEventListener('click', function() {
        popup.style.display = 'none';
    });

    document.querySelectorAll('.loading-option').forEach(option => {
        option.addEventListener('click', function() {
            const html = this.getAttribute('data-html');
            const css = this.getAttribute('data-css');
            document.getElementById('loader_html').value = html;
            document.getElementById('loader_css').value = css;
            // Remove selected class from all options
            document.querySelectorAll('.loading-option').forEach(opt => opt.classList.remove('selected'));
            // Add selected class to the clicked option
            this.classList.add('selected');
            // Store the selected effect value
            selectedEffect = this.getAttribute('data-value');
        });
    });
});