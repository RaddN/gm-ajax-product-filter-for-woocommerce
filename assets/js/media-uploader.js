jQuery(document).ready(function ($) {
    $('.upload-image-button').click(function (e) {
        e.preventDefault();

        var button = $(this);
        var inputField = button.prev('input'); // Select the input field before the button

        // Open the Media Library
        var fileFrame = wp.media.frames.fileFrame = wp.media({
            title: 'Select or Upload an Image',
            button: {
                text: 'Use this Image',
            },
            multiple: false // Single file selection
        });

        // When an image is selected
        fileFrame.on('select', function () {
            var attachment = fileFrame.state().get('selection').first().toJSON();
            inputField.val(attachment.url); // Set the image URL in the input field
        });

        fileFrame.open();
    });
});
