<?php
/*
Plugin Name: SX Woo Orders
Plugin URI: https://github.com/amundost/sx-woo-orders
Description: A WordPress plugin for managing WooCommerce orders.
Version: 1.0
Author: Your Name
Author URI: https://www.slackhax.com
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Check for WooCommerce installation and activation
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    // Shortcode to display WooCommerce orders
    function sx_woo_orders_list_shortcode($atts)
    {
        global $wpdb;

        // Fetch orders from WooCommerce orders table
        $results = $wpdb->get_results("
            SELECT o.ID, o.status, o.total_amount, a.first_name, a.last_name, s.date_created
            FROM {$wpdb->prefix}orders as o, {$wpdb->prefix}wc_order_addresses as o,{$wpdb->prefix}wc_order_stats as s
            WHERE o.ID = a.order_id
            AND o.ID = s.order_id
            AND a.address_type = 'billing'
            ORDER BY post_date DESC
        ");

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
    add_shortcode('sx_woo_orders_list', 'sx_woo_orders_list_shortcode');
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
    $current_version = '1.0';  // Make sure this version matches the version in your plugin header

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
add_filter('site_transient_update_plugins', 'sx_woo_orders_check_for_update');