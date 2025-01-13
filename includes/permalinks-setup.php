<?php

if (!defined('ABSPATH')) {
    exit;
}

function dapfforwc_register_template() {
    global $wp;
    global $dapfforwc_options;
    $pages = $dapfforwc_options['pages'];
    $request = $wp->request;

    
    // Loop through each page and check if the current request starts with any of them
    if (strpos($request, 'filters') !== false) { 
        // Get the part before "filters"
        $dapfforwc_root_slug = substr($request, 0, strpos($request, 'filters') - 1); // -1 to remove trailing slash
        $dapfforwc_root_slug = sanitize_text_field($dapfforwc_root_slug); // Sanitize input
    
        // Get the part after "filters"
        $dapfforwc_slug = substr($request, strpos($request, 'filters') + strlen("filters") + 1);
        $dapfforwc_slug = sanitize_text_field($dapfforwc_slug); // Sanitize input
    
        // Store both values for debugging or processing
        set_transient('dapfforwc_root_slug', $dapfforwc_root_slug, 30);
        set_transient('dapfforwc_slug', $dapfforwc_slug, 30);
    
        // Redirect to a URL with the extracted parts
        $redirect_url = home_url("/$dapfforwc_root_slug?filters=$dapfforwc_slug");
        wp_redirect($redirect_url, 301);
        exit;
    }
    else {
    foreach ($pages as $page) {
        if (str_starts_with($request, $page . '/')) {
            // Get the part of the URI after the page slug
            $dapfforwc_slug = substr($request, strlen($page) + 1);
            
            if($dapfforwc_options["use_filters_word_in_permalinks"]!=="on"){
                set_transient('dapfforwc_slug', $dapfforwc_slug , 30);
            // Redirect to the main page
            wp_redirect(home_url("/$page?filters=$dapfforwc_slug "), 301);
            exit;
            }else{
                $segments = explode('/', $dapfforwc_slug );

            if ($segments[0] === 'filters') {
                $found_page = true;

                // Store the slug and redirect
                set_transient('dapfforwc_slug', $dapfforwc_slug , 30);
                wp_redirect(home_url("/$page?filters=$dapfforwc_slug "), 301);
                exit;
            }
            }
        }
    }
}
}
function dapfforwc_remove_session() {
    // Remove the slug from the session
    delete_transient('dapfforwc_slug');
}

// Hook the functions to appropriate actions
add_action('template_redirect', 'dapfforwc_register_template');
add_action('wp_footer', 'dapfforwc_remove_session');
