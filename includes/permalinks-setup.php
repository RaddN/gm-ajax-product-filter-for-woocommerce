<?php

function wcapf_add_rewrite_rules() {
    add_rewrite_rule(
        '^(.+)/(.+)/(.+)/?$',
        'index.php?pagename=your-page-slug&filters=$matches[1],$matches[2],$matches[3]',
        'top'
    );
}
add_action('init', 'wcapf_add_rewrite_rules');


function myplugin_register_template() {
    global $wp;

    // Get the pages from the options
    $options = get_option('wcapf_options');
    $pages = isset($options['pages']) ? $options['pages'] : [];

    // Loop through each page and check if the current request starts with any of them
    foreach ($pages as $page) {
        // Check if the current request starts with the page slug
        if (strpos($wp->request, $page . '/') === 0) {
            // Get the part of the URI after the page slug
            $slug = str_replace($page . '/', '', $wp->request);

            // Store the slug in a transient
            set_transient($page . '_slug', $slug, 30); // Store for 30 seconds

            // Redirect to the main page
            wp_redirect(home_url("/$page"), 301);
            exit;
        }
    }
}

function myplugin_add_console_log() {
    // Get the pages from the options
    $options = get_option('wcapf_options');
    $pages = isset($options['pages']) ? $options['pages'] : [];

    // Check each page's transient
    foreach ($pages as $page) {
        if ($slug = get_transient($page . '_slug')) {
            // Output the script to log the value to the console and update the URL
            echo "<script>
                    // Update the URL with the slug value
                    var newUrl = window.location.href + '" . esc_js($slug) . "';
                    window.history.replaceState(null, null, newUrl);
                  </script>";

            // Delete the transient after logging
            delete_transient($page . '_slug');
        }
    }
}

// Hook the functions to appropriate actions
add_action('template_redirect', 'myplugin_register_template');
add_action('wp_footer', 'myplugin_add_console_log');