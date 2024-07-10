<?php
/*
Template Name: Print Orders
*/

if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id == 0) {
    wp_die('No order specified.');
}

$order = wc_get_order($order_id);

if (!$order) {
    wp_die('Order not found.');
}

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <style>
        .woocommerce-order {
            max-width: 800px;
            margin: 0 auto;
            font-family: Arial, sans-serif;
        }

        .woocommerce-order .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .woocommerce-order table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .woocommerce-order table,
        .woocommerce-order th,
        .woocommerce-order td {
            border: 1px solid #ddd;
        }

        .woocommerce-order th,
        .woocommerce-order td {
            padding: 8px;
            text-align: left;
        }
    </style>
</head>

<body <?php body_class(); ?>>
    <div class="woocommerce-order">
        <?php
        echo '<div class="header"><h1>Ordrebekreftelse</h1></div>';
        echo '<p>Ordre nr: #' . $order->get_id() . '</p>';
        echo '<p>Bestillingsdato: ' . $order->get_date_created()->date('Y-m-d H:i:s') . '</p>';
        echo '<p>Totalt: ' . $order->get_formatted_order_total() . '</p>';

        // Billing Address
        echo '<h2>Fakturaadresse</h2>';
        echo '<p>' . $order->get_formatted_billing_address() . '</p>';

        // Shipping Address
        echo '<h2>Leveringsadresse</h2>';
        echo '<p>' . $order->get_formatted_shipping_address() . '</p>';

        // Order Items
        echo '<h2>Ordreinnhold</h2>';
        echo '<table>';
        echo '<thead><tr><th>Produkt</th><th>Antall</th><th>Pris</th></tr></thead>';
        echo '<tbody>';
        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            $product_name = $item->get_name();
            $quantity = $item->get_quantity();
            $total = $item->get_total();
            echo '<tr>';
            echo '<td>' . esc_html($product_name) . '</td>';
            echo '<td>' . esc_html($quantity) . '</td>';
            echo '<td>' . wc_price($total) . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        ?>
    </div>
    <?php wp_footer(); ?>
</body>

</html>