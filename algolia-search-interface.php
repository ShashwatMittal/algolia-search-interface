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

// Define constants
define('ALGOLIA_SEARCH_INTERFACE_VERSION', '1.0.0');
define('ALGOLIA_SEARCH_INTERFACE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ALGOLIA_SEARCH_INTERFACE_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once ALGOLIA_SEARCH_INTERFACE_PLUGIN_DIR . 'includes/class-algolia-settings.php';

// Initialize settings
$algolia_settings = new Algolia_Settings();

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

    // Add settings submenu
    add_submenu_page(
        'algolia-search-interface',
        'Algolia Settings',
        'Settings',
        'manage_options',
        'algolia-search-settings',
        'algolia_search_settings_page'
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

// Settings page content
function algolia_search_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Save settings if form is submitted
    if (isset($_POST['algolia_settings_nonce']) && wp_verify_nonce($_POST['algolia_settings_nonce'], 'algolia_save_settings')) {
        $settings = array(
            'algolia_app_id' => sanitize_text_field($_POST['algolia_app_id']),
            'algolia_search_api_key' => sanitize_text_field($_POST['algolia_search_api_key']),
            'algolia_admin_api_key' => sanitize_text_field($_POST['algolia_admin_api_key']),
        );
        update_option('algolia_search_settings', $settings);
        echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
    }

    // Get current settings
    $settings = get_option('algolia_search_settings', array(
        'algolia_app_id' => '',
        'algolia_search_api_key' => '',
        'algolia_admin_api_key' => '',
    ));
    ?>
    <div class="wrap">
        <h1>Algolia Search Settings</h1>
        <form method="post" action="">
            <?php wp_nonce_field('algolia_save_settings', 'algolia_settings_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="algolia_app_id">Application ID</label></th>
                    <td>
                        <input type="text" id="algolia_app_id" name="algolia_app_id" 
                               value="<?php echo esc_attr($settings['algolia_app_id']); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="algolia_search_api_key">Search API Key</label></th>
                    <td>
                        <input type="text" id="algolia_search_api_key" name="algolia_search_api_key" 
                               value="<?php echo esc_attr($settings['algolia_search_api_key']); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="algolia_admin_api_key">Admin API Key</label></th>
                    <td>
                        <input type="text" id="algolia_admin_api_key" name="algolia_admin_api_key" 
                               value="<?php echo esc_attr($settings['algolia_admin_api_key']); ?>" class="regular-text">
                    </td>
                </tr>
            </table>
            <?php submit_button('Save Settings'); ?>
        </form>
    </div>
    <?php
}

// Enqueue JavaScript and CSS
function algolia_enqueue_scripts() {
    wp_enqueue_script(
        'algolia-search-interface-js',
        plugins_url('/js/algolia.js', __FILE__),
        ['jquery'],
        ALGOLIA_SEARCH_INTERFACE_VERSION,
        true
    );

    wp_enqueue_style(
        'algolia-search-interface-css',
        plugins_url('/css/algolia.css', __FILE__),
        array(),
        ALGOLIA_SEARCH_INTERFACE_VERSION
    );

    // Localize script with settings
    $settings = get_option('algolia_search_settings', array());
    wp_localize_script('algolia-search-interface-js', 'algoliaSettings', array(
        'appId' => $settings['algolia_app_id'] ?? '',
        'searchApiKey' => $settings['algolia_search_api_key'] ?? '',
    ));
}
