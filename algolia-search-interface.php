<?php
/*
Plugin Name: Algolia Search Interface
Description: A small interface to search with Algolia APIs in WordPress.
Version: 1.0
Author: Shashwat Mittal
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Add a new admin page for the search interface
add_action('admin_menu', 'algolia_search_interface_menu');
function algolia_search_interface_menu() {
    add_menu_page(
        'Algolia Search Interface',
        'Algolia Search',
        'manage_options',
        'algolia-search-interface',
        'algolia_search_interface_page',
        'dashicons-search',
        100
    );

}

// Admin page content
function algolia_search_interface_page() {
    ?>
    <div class="wrap">
        <h1>Algolia Search Interface</h1>
        <div id="algolia-search-app"></div>
    </div>
    <?php
    // Enqueue the JavaScript for the app
    algolia_enqueue_scripts();
}

// Enqueue JavaScript and CSS
function algolia_enqueue_scripts() {
    wp_enqueue_script(
        'algolia-search-interface-js',
        plugins_url('/js/algolia.js', __FILE__),
        ['jquery'],
        filemtime(plugin_dir_path(__FILE__) . 'js/algolia.js'),
        true
    );

    wp_enqueue_style(
        'algolia-search-interface-css',
        plugins_url('/css/algolia.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'css/algolia.css')
    );

    // Localize script with settings
    $algolia_application_id = get_option('algolia_application_id', '');
    $algolia_search_api_key = get_option('algolia_search_api_key', '');

    wp_localize_script('algolia-search-interface-js', 'algoliaSettings', array(
        'appId' => $algolia_application_id,
        'searchApiKey' => $algolia_search_api_key,
    ));
}
