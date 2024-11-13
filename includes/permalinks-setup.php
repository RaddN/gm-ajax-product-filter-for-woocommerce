<?php
session_start();
function myplugin_register_template() {
    global $wp;
    global $options;

    $pages = isset($options['pages']) ? $options['pages'] : [];

    // Loop through each page and check if the current request starts with any of them
    foreach ($pages as $page) {
        // Check if the current request starts with the page slug
        if (strpos($wp->request, $page . '/') === 0) {
            // Get the part of the URI after the page slug
            $slug = str_replace($page . '/', '', $wp->request);

            // Store the slug in the session
            $_SESSION["filters" . '_slug'] = $slug;
            // Redirect to the main page
            wp_redirect(home_url("/$page"), 301);
            exit;
        }
    }
}

function myplugin_add_console_log() {
    // Output the script to check session and update the URL
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {";
    // Check each page's session variable
        if (isset($_SESSION["filters" . '_slug'])) {
            $slug = sanitize_text_field($_SESSION["filters" . '_slug']);
            echo "
                // Update the URL with the slug value
                var newUrl = window.location.href + '" . esc_js($slug) . "';
                window.history.replaceState(null, null, newUrl);
                console.log('" . esc_js($slug) . "');
                // Remove the slug from the session
                ";
            unset($_SESSION[$page . '_slug']);
            }
    echo "});</script>";
}

// Hook the functions to appropriate actions
add_action('template_redirect', 'myplugin_register_template');
add_action('wp_footer', 'myplugin_add_console_log');
