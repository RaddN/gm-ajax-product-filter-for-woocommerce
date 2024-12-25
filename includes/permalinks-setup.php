<?php

if (!defined('ABSPATH')) {
    exit;
}

function myplugin_register_template() {
    global $wp;
    global $options;
    $pages = $options['pages'];
    $request = $wp->request;
    error_log("Current request: " . $request);
    // Loop through each page and check if the current request starts with any of them
    foreach ($pages as $page) {
        if (str_starts_with($request, $page . '/')) {
            // Get the part of the URI after the page slug
            $slug = substr($request, strlen($page) + 1);
            
            if($options["use_filters_word_in_permalinks"]!=="on"){
                set_transient('gmfilter_slug', $slug, 30);
            // Redirect to the main page
            wp_redirect(home_url("/$page?filters=$slug"), 301);
            exit;
            }else{
                $segments = explode('/', $slug);

            if ($segments[0] === 'filters') {
                $found_page = true;

                // Store the slug and redirect
                set_transient('gmfilter_slug', $slug, 30);
                wp_redirect(home_url("/$page?filters=$slug"), 301);
                exit;
            }
            }
        }else if(str_starts_with($request, 'filters')){
            $slug = substr($request, strlen("filters") + 1);
            if($options["use_filters_word_in_permalinks"]!=="on"){
                set_transient('gmfilter_slug', $slug, 30);
            // Redirect to the main page
            wp_redirect(home_url("/?filters=$slug"), 301);
            exit;
            }else{
                // Store the slug and redirect
                set_transient('gmfilter_slug', $slug, 30);
                wp_redirect(home_url("/?filters=$slug"), 301);
                exit;
            }
        }
    }
}
function remove_session() {
    // Remove the slug from the session
    delete_transient('gmfilter_slug');
}

// Hook the functions to appropriate actions
add_action('template_redirect', 'myplugin_register_template');
add_action('wp_footer', 'remove_session');
