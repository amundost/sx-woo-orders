<?php
/*
Plugin Name: SX Woo Orders
Plugin URI: https://github.com/amundost/sx-woo-orders
Description: A WordPress plugin for managing WooCommerce orders.
Version: 1.0.12
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
        'manage_options',          // Capability required
        'slackhax-admin',       // Menu slug
        'sx_admin_page_content',   // Function to display content
        'dashicons-admin-generic', // Icon URL (or dashicon class)
        2                          // Position on the menu
    );
    // Add first submenu item
    add_submenu_page(
        'slackhax-admin',          // Parent slug
        'All Orders',         // Page title
        'All Orders',         // Submenu title
        'manage_options',     // Capability
        'all-orders',         // Menu slug
        'sx_woo_orders_page_content' // Callback function for submenu page
    );
    add_submenu_page(
        'slackhax-admin',          // Parent slug
        'Order Details',         // Page title
        'Order Details',         // Submenu title
        'manage_options',     // Capability
        'orders-details',         // Menu slug
        'sx_woo_order_details' // Callback function for submenu page
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
function my_plugin_register_page_templates($templates)
{
    $templates['templates/page-template-print-orders.php'] = 'Print Orders';
    return $templates;
}
add_filter('theme_page_templates', 'my_plugin_register_page_templates');

function my_plugin_load_page_template($template)
{
    if (is_page_template('templates/page-template-print-orders.php')) {
        $template = plugin_dir_path(__FILE__) . 'templates/page-template-print-orders.php';
    }
    return $template;
}
add_filter('template_include', 'my_plugin_load_page_template');