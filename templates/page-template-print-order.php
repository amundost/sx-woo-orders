<?php
/*
Template Name: Print Orders
*/

// Ensure the user is an admin
if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
}

// Get the order ID from the URL
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id == 0) {
    wp_die('No order specified.');
}

// Fetch the order
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
</head>

<body <?php body_class(); ?>>
    <div class="woocommerce-order">
        <?php
        // Display order details
        echo '<h1>Order Details</h1>';
        echo '<p>Order ID: ' . $order->get_id() . '</p>';
        echo '<p>Order Date: ' . $order->get_date_created()->date('Y-m-d H:i:s') . '</p>';
        echo '<p>Order Total: ' . $order->get_formatted_order_total() . '</p>';

        // Optional: include more details such as billing/shipping info, items, etc.
        ?>
    </div>
    <?php wp_footer(); ?>
</body>

</html>