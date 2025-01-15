<?php
if (!defined('ABSPATH')) {
    exit;
}

function dapfforwc_get_min_max_price() {
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1, // Get all products
    );

    $loop = new WP_Query($args);
    $min_price = null;
    $max_price = null;

    while ($loop->have_posts()) {
        $loop->the_post();
        $product = wc_get_product(get_the_ID());
        $price = $product->get_price();

        if (is_null($min_price) || $price < $min_price) {
            $min_price = $price;
        }

        if (is_null($max_price) || $price > $max_price) {
            $max_price = $price;
        }
    }

    wp_reset_postdata(); // Reset post data

    return array('min' => $min_price, 'max' => $max_price);
}