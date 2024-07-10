<?php
/*
Plugin Name: SX Woo Orders
Plugin URI: https://github.com/amundost/sx-woo-orders
Description: A WordPress plugin for managing WooCommerce orders.
Version: 1.0.16
Author: Amund Østvoll
Author URI: https://www.slackhax.com
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

add_action('admin_menu', 'slackhax_admin');

function slackhax_admin()
{
    add_menu_page(
        'SlackHax Admin',       // Page title
        'SlackHax',             // Menu title
        'manage_options',       // Capability required
        'slackhax-admin',       // Menu slug
        'sx_admin_page_content',// Function to display content
        'dashicons-admin-generic', // Icon URL (or dashicon class)
        2                       // Position on the menu
    );
    // Add first submenu item
    add_submenu_page(
        'slackhax-admin',          // Parent slug
        'All Orders',              // Page title
        'All Orders',              // Submenu title
        'manage_options',          // Capability
        'all-orders',              // Menu slug
        'sx_woo_orders_page_content', // Callback function for submenu page
        100
    );
    add_submenu_page(
        'slackhax-admin',          // Parent slug
        'Order Details',           // Page title
        'Order Details',           // Submenu title
        'manage_options',          // Capability
        'orders-details',          // Menu slug
        'sx_woo_order_details',    // Callback function for submenu page
        102
    );
    add_submenu_page(
        'slackhax-admin',          // Parent slug
        'Debug Log',               // Page title
        'Debug Log',               // Submenu title
        'manage_options',          // Capability
        'sx-woo-orders-debug-log', // Menu slug
        'sx_woo_orders_display_debug_log', // Callback function for submenu page
        103                       // Position on the menu
    );
}

function sx_admin_page_content()
{
    echo "<div class='wrap'>";
    echo "<h1>SlackHax</h1>";
    echo "<p>SlackHax extra tools</p>";
    require_once ('sx-woo-order-admin.php');
    echo "</div>";
}

function sx_woo_orders_page_content()
{
    echo "<div class='wrap'>";
    echo "<h1>View WooCommerce orders</h1>";
    require_once ('sx-woo-orderlist.php');
    echo "</div>";
}

function sx_woo_order_details()
{
    echo "<div class='wrap'>";
    require_once ('sx-woo-order-details.php');
    echo "</div>";
}

// Display the debug log
function sx_woo_orders_display_debug_log()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $log_file = WP_CONTENT_DIR . '/debug.log';

    echo '<div class="wrap">';
    echo '<h1>Debug Log</h1>';
    echo '<pre style="background: #fff; padding: 10px; border: 1px solid #ccc; max-height: 600px; overflow: auto;">';

    if (file_exists($log_file)) {
        echo esc_html(file_get_contents($log_file));
    } else {
        echo 'No debug log found.';
    }

    echo '</pre>';
    echo '</div>';
}

// Register the template
function sx_woo_orders_register_page_templates($templates)
{
    $templates['templates/page-template-print-order.php'] = 'Print Orders';
    return $templates;
}
add_filter('theme_page_templates', 'sx_woo_orders_register_page_templates');

function sx_woo_orders_load_page_template($template)
{
    if (is_page_template('templates/page-template-print-order.php')) {
        $custom_template = plugin_dir_path(__FILE__) . 'templates/page-template-print-order.php';
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('sx_woo_orders_load_page_template: Loading template from ' . $custom_template);
        }
        if (file_exists($custom_template)) {
            $template = $custom_template;
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('sx_woo_orders_load_page_template: Template file not found at ' . $custom_template);
            }
        }
    }
    return $template;
}
add_filter('template_include', 'sx_woo_orders_load_page_template');

// Function to create the page
function sx_woo_orders_create_page()
{
    // Check if the page already exists
    $page_title = 'Print Orders';
    $page_check = get_page_by_title($page_title);
    $page_template = 'templates/page-template-print-order.php';

    // Log for debugging
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('sx_woo_orders_create_page: Checking if page exists');
    }

    // If the page doesn't exist, create it
    if (!isset($page_check->ID)) {
        $page = array(
            'post_title' => $page_title,
            'post_content' => '',
            'post_status' => 'publish',
            'post_type' => 'page',
            'page_template' => $page_template
        );
        $page_id = wp_insert_post($page);

        // Log for debugging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('sx_woo_orders_create_page: Page created with ID ' . $page_id);
        }

        // Update the page to use the custom template
        update_post_meta($page_id, '_wp_page_template', $page_template);
    } else {
        // Log for debugging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('sx_woo_orders_create_page: Page already exists with ID ' . $page_check->ID);
        }
    }
}

// Hook into plugin activation
register_activation_hook(__FILE__, 'sx_woo_orders_create_page');

// Function to get the URL of the "Print Orders" page
function sx_woo_orders_get_print_orders_page_url()
{
    $page = get_page_by_title('Print Orders');
    if ($page) {
        return get_permalink($page->ID);
    }
    return '';
}