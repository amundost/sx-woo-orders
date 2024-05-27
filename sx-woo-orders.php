<?php
/*
Plugin Name: SX Woo Orders
Plugin URI: https://github.com/amundost/sx-woo-orders
Description: A WordPress plugin for managing WooCommerce orders.
Version: 1.0.3
Author: Amund Østvoll
Author URI: https://www.slackhax.com
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Check for WooCommerce installation and activation
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    // Shortcode to display WooCommerce orders
    function sx_woo_orders_list()
    {
        global $wpdb;

        $query = $wpdb->prepare("
            SELECT o.ID, o.status, o.total_amount, a.first_name, a.last_name, s.date_created 
            FROM {$wpdb->prefix}orders as o
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
    $plugin_slug = 'sx-woo-orders';
    $github_api_url = 'https://api.github.com/repos/amundost/sx-woo-orders/releases/latest';

    $response = wp_remote_get($github_api_url, array('timeout' => 15, 'headers' => array('Accept' => 'application/vnd.github.v3+json')));

    if (is_wp_error($response)) {
        return $transient;
    }

    $release_info = json_decode(wp_remote_retrieve_body($response));

    if (empty($release_info) || !isset($release_info->tag_name)) {
        return $transient;
    }

    $new_version = $release_info->tag_name;
    $current_version = '1.0.3';  // Make sure this version matches the version in your plugin header

    if (version_compare($current_version, $new_version, '<')) {
        $transient->response['sx-woo-orders/sx-woo-orders.php'] = (object) array(
            'slug' => $plugin_slug,
            'new_version' => $new_version,
            'url' => $release_info->html_url,
            'package' => $release_info->zipball_url,
        );
    }

    return $transient;
}
add_action('admin_menu', 'slackhax_admin');

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
    ?>
<div>
    <h1>SlackHax</h1>
    <p>SlackHax extra tools</p>
</div>
<?php
}

function sx_woo_orders_page_content()
{
    echo "<div class='wrap'>";
    echo "<h1>View WooCommerce orders</h1>";
    echo sx_woo_orders_list();
    echo "</div>";
}
add_filter('site_transient_update_plugins', 'sx_woo_orders_check_for_update');