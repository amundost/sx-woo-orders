<?php
// Hent alle mulige ordrestatusser
$order_statuses = wc_get_order_statuses();

// Håndter formens innsending
$selected_status = isset($_POST['order_status']) ? sanitize_text_field($_POST['order_status']) : 'processing';

// Hent ordrer basert på valgt status
$args = array(
    'limit' => -1 // Hent alle ordrer
);

if ($selected_status) {
    $args['status'] = $selected_status;
}

$orders = wc_get_orders($args);

// Start output buffering
$output = '<form method="POST" action="">';
$output .= '<label for="order_status">Velg ordrestatus:</label>';
$output .= '<select name="order_status" id="order_status">';
$output .= '<option value="">Alle</option>';

foreach ($order_statuses as $status_slug => $status_name) {
    $output .= '<option value="' . esc_attr($status_slug) . '" ' . selected($selected_status, $status_slug, false) . '>' . esc_html($status_name) . '</option>';
}

$output .= '</select>';
$output .= '<button type="submit">Søk</button>';
$output .= '</form>';

$output .= '<table style="width: 100%; border-collapse: collapse; text-align: center;" border="1">';
$output .= '<thead>
                <tr>
                    <th>Order ID</th>
                    <th>Order Date</th>
                    <th>Order Status</th>
                    <th>Customer Name</th>
                    <th>Actions</th>
                </tr>
            </thead>';
$output .= '<tbody>';

// Loop gjennom hver ordre
foreach ($orders as $order) {
    $order_id = $order->get_id();
    $order_date = $order->get_date_created()->date('Y-m-d H:i:s');
    $order_status = wc_get_order_status_name($order->get_status());
    $name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

    // Få URL til "Print Orders"-siden og legg til ordre-ID som en query parameter
    $print_orders_url = sx_woo_orders_get_print_orders_page_url() . '?order_id=' . $order_id;

    $output .= '<tr>
                    <td>' . esc_html($order_id) . '</td>
                    <td>' . esc_html($order_date) . '</td>
                    <td>' . esc_html($order_status) . '</td>
                    <td>' . esc_html($name) . '</td>
                    <td><a href="' . esc_url($print_orders_url) . '">Print Details</a></td>
                </tr>';
}

$output .= '</tbody>';
$output .= '</table>';

// Output tabellen
echo $output;
?>