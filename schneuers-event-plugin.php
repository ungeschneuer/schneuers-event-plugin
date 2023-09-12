<?php
/**
 * Plugin Name: Schneuers Event-Plugin
 * Description: Event-Plugin von Marcel für Elementor
 * Version: 1.0
 * Author: Marcel Schneuer
 * Author URI: https://schneuer.online
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: schneuers-event-plugin
 */

// Include the custom post type registration file
require_once(__DIR__ . '/cpt/schneuers_event_cpt.php');

/**
 * Enqueue styles for the plugin
 */
function custom_events_enqueue_styles()
{
    wp_enqueue_style('custom-events-plugin-style', plugin_dir_url(__FILE__) . 'assets/css/schneuers-event-admin-ui.css');
}
add_action('admin_enqueue_scripts', 'custom_events_enqueue_styles');

/**
 * Register the Elementor widget
 */
function register_event_widget()
{
    if (class_exists('Elementor\Widget_Base')) {
        require_once(__DIR__ . '/widgets/elementor-event-list.php');
        \Elementor\Plugin::instance()->widgets_manager->register(new \Event_List_Widget());
    }
}
add_action('init', 'register_event_widget');

/**
 * Register widget-specific styles
 */
function register_widget_styles()
{
    wp_register_style('event-list-style', plugin_dir_url(__FILE__) . 'assets/css/event-list.css');
}
add_action('wp_enqueue_scripts', 'register_widget_styles');

/**
 * Activate the plugin.
 */
function pluginprefix_activate()
{
    // Trigger functions for activation tasks (e.g., custom post type registration)
    custom_events_register_post_type();
    // Register the Elementor widget (you can uncomment this line if needed)
    // register_event_widget();
    // Clear the permalinks after the post type and widget have been registered.
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'pluginprefix_activate');

/**
 * Deactivation hook.
 */
function pluginprefix_deactivate()
{
    // Trigger functions for deactivation tasks (e.g., unregister post type)
    unregister_post_type('event');
    // Clear the permalinks to remove our post type's rules from the database.
    flush_rewrite_rules();
    // Unregister the Elementor widget
    unregister_event_widget();
}
register_deactivation_hook(__FILE__, 'pluginprefix_deactivate');

/**
 * Unregister the Elementor widget
 */
function unregister_event_widget()
{
    if (class_exists('Elementor\Widget_Base')) {
        \Elementor\Plugin::instance()->widgets_manager->unregister_widget_type('event_list_widget');
    }
}
