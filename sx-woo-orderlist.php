<?php
// Check for WooCommerce installation and activation

// Shortcode to display WooCommerce orders
function sx_woo_orders_list()
{
    if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        global $wpdb;

        $query = $wpdb->prepare("
            SELECT o.ID, o.status, o.total_amount, a.first_name, a.last_name, s.date_created, a.address_type 
            FROM {$wpdb->prefix}wc_orders as o
            JOIN {$wpdb->prefix}wc_order_addresses as a ON o.ID = a.order_id 
            JOIN {$wpdb->prefix}wc_order_stats as s ON o.ID = s.order_id 
            WHERE a.address_type like %s
        ", 'billing');

        $results = $wpdb->get_results($query);
        if (empty($results)) {
            return 'No orders found.';
        }

        $output = '<table style="width: 100%; border-collapse: collapse; text-align: center;" border="1">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Order Date</th>
                            <th>Order Status</th>
                            <th>Customer</th>
                            <th>Details</th>
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
                            <td>Print Details</td>
                        </tr>';
        }

        $output .= '</tbody></table>';

        return $output;
    }
}
function count_wc_orders()
{
    global $wpdb;

    // Define the table name (prefix the table with the WordPress table prefix)
    $table_name = $wpdb->prefix . 'wc_orders';

    // Write the SQL query to count the number of items in the wc_orders table
    $query = "SELECT COUNT(*) FROM $table_name";

    // Execute the query and get the result
    $count = $wpdb->get_var($query);

    // Return the count
    return $count;
}
echo '<h1>OrderList</h1>';
echo sx_woo_orders_list();