<?php
/*
Plugin Name: SX Woo Orders
Plugin URI: https://github.com/amundost/sx-woo-orders
Description: A WordPress plugin for managing WooCommerce orders.
Version: 1.0.5
Author: Amund Østvoll
Author URI: https://www.slackhax.com
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

add_filter('site_transient_update_plugins', 'sx_woo_orders_check_for_update');
add_action('admin_menu', 'slackhax_admin');

// Check for WooCommerce installation and activation
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    // Shortcode to display WooCommerce orders
    function sx_woo_orders_list()
    {
        global $wpdb;

        $query = $wpdb->prepare("
            SELECT o.ID, o.status, o.total_amount, a.first_name, a.last_name, s.date_created 
            FROM {$wpdb->prefix}wc_orders as o
            JOIN {$wpdb->prefix}wc_order_addresses as a ON o.ID = a.order_id 
            JOIN {$wpdb->prefix}wc_order_stats as s ON o.ID = s.order_id 
            WHERE a.address_type = %s 
            ORDER BY o.post_date DESC
        ", 'billing');

        $results = $wpdb->get_results($query);
        if (empty($results)) {
            return 'No orders found.';
        }

        $output = '<table style="width: 100%; border-collapse: collapse;" border="1">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Order Date</th>
                            <th>Order Status</th>
                            <th>Customer</th>
                        </tr>
                    </thead>
                    <tbody>';

        foreach ($results as $order) {
            $order_id = $order->ID;
            $order_date = date('F j, Y', strtotime($order->date_created));
            $order_status = $order->status;
            $name = $order->first_name . " " . $order->last_name;

            $output .= '<tr>
                            <td>' . esc_html($order_id) . '</td>
                            <td>' . esc_html($order_date) . '</td>
                            <td>' . esc_html($order_status) . '</td>
                            <td>' . esc_html($name) . '</td>
                        </tr>';
        }

        $output .= '</tbody></table>';

        return $output;
    }
}

// Update check function
function sx_woo_orders_check_for_update($transient)
{
    // If no update info is available, return the transient as is.
    if (empty($transient->checked)) {
        return $transient;
    }

    $plugin_slug = 'sx-woo-orders';
    $github_api_url = 'https://api.github.com/repos/amundost/sx-woo-orders/releases/latest';

    // Make an API request to GitHub to fetch the latest release information.
    $response = wp_remote_get($github_api_url);

    // If an error occurred during the request, return the transient as is.
    if (is_wp_error($response)) {
        return $transient;
    }

    $response_body = wp_remote_retrieve_body($response);
    $data = json_decode($response_body);

    // If the response is empty or malformed, return the transient as is.
    if (empty($data)) {
        return $transient;
    }

    // Get the current version from the plugin header
    $plugin_file = plugin_dir_path(__FILE__) . "{$plugin_slug}.php";
    $plugin_data = get_plugin_data($plugin_file);
    $current_version = $plugin_data['Version'];

    // Check if a new version is available
    if (version_compare($current_version, $data->tag_name, '<')) {
        $plugin_obj = new stdClass();
        $plugin_obj->slug = $plugin_slug;
        $plugin_obj->new_version = $data->tag_name;
        $plugin_obj->url = $data->html_url;
        $plugin_obj->package = $data->zipball_url;

        $transient->response[$plugin_slug . '/' . $plugin_slug . '.php'] = $plugin_obj;
    }

    return $transient;
}


function slackhax_admin()
{
    add_menu_page(
        'SlackHax Admin',       // Page title
        'Slackhax',             // Menu title
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
}

function sx_admin_page_content()
{
    echo "<div class='wrap'>";
    echo "<h1>SlackHax</h1>";
    echo "<p>SlackHax extra tools</p>";
    echo "</div>";
}

function sx_woo_orders_page_content()
{
    echo "<div class='wrap'>";
    echo "<h1>View WooCommerce orders</h1>";
    echo sx_woo_orders_list();
    echo "</div>";
}