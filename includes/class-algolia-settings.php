<?php
/**
 * Algolia Settings Class
 *
 * @package Algolia_Search_Interface
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Class Algolia_Settings
 */
class Algolia_Settings {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'algolia_search_settings',
            'algolia_search_settings',
            array($this, 'sanitize_settings')
        );
    }

    /**
     * Sanitize settings
     *
     * @param array $input Settings input.
     * @return array Sanitized settings.
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        if (isset($input['algolia_app_id'])) {
            $sanitized['algolia_app_id'] = sanitize_text_field($input['algolia_app_id']);
        }
        
        if (isset($input['algolia_search_api_key'])) {
            $sanitized['algolia_search_api_key'] = sanitize_text_field($input['algolia_search_api_key']);
        }
        
        if (isset($input['algolia_admin_api_key'])) {
            $sanitized['algolia_admin_api_key'] = sanitize_text_field($input['algolia_admin_api_key']);
        }
        
        return $sanitized;
    }

    /**
     * Get settings
     *
     * @return array Settings array.
     */
    public static function get_settings() {
        return get_option('algolia_search_settings', array(
            'algolia_app_id' => '',
            'algolia_search_api_key' => '',
            'algolia_admin_api_key' => '',
        ));
    }
} 