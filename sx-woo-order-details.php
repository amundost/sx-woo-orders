<?php
/* Template Name: Order Print Page */

get_header();

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if ($order_id) {
    $order = wc_get_order($order_id);
    echo $order;
    if ($order) {
        // Display order details
        ?>
        <div class="order-details">
            <h1>Order #<?php echo $order->get_id(); ?></h1>
            <p><strong>Date:</strong> <?php echo $order->get_date_created()->date('Y-m-d H:i:s'); ?></p>
            <p><strong>Total:</strong> <?php echo $order->get_total(); ?></p>
            <h2>Items</h2>
            <ul>
                <?php
                foreach ($order->get_items() as $item) {
                    $product = $item->get_product();
                    echo '<li>' . $product->get_name() . ' x ' . $item->get_quantity() . '</li>';
                }
                ?>
            </ul>
        </div>
        <button onclick="window.print();">Print Order</button>
        <?php
    } else {
        echo '<p>Order not found.</p>';
    }
} else {
    echo '<p>No order ID provided.</p>';
}

get_footer();
?>