<?php
if (isset($_POST['check_updates'])) {
    $update_available = sx_woo_orders_check_for_update();
    if ($update_available) {
        echo "<form method='POST'><button type='submit' name='update_plugin'>Update plugin</button></form>";
    } else {
        echo "No update available";
    }
}
if (isset($_POST['update plugin'])) {
    $order_count = sx_woo_orders_check_for_update();
}

//add_filter('site_transient_update_plugins', 'sx_woo_orders_check_for_update');
function sx_woo_orders_check_for_update()
{
    $plugin_slug = 'sx-woo-orders';
    $github_api_url = 'https://api.github.com/repos/amundost/sx-woo-orders/releases/latest';

    // Get the current version from the plugin header
    $plugin_file = plugin_dir_path(__FILE__) . "{$plugin_slug}.php";
    $plugin_data = get_plugin_data($plugin_file);
    $current_version = $plugin_data['Version'];
    echo "<p>Installed version $current_version </p>";

    // Make an API request to GitHub to fetch the latest release information.
    $response = wp_remote_get($github_api_url);
    $response_body = wp_remote_retrieve_body($response);
    //echo $response_body;
    $data = json_decode($response_body);
    $available_version = 'Not able to load version tag';
    if (isset($data->tag_name))
        $available_version = $data->tag_name;
    echo "<p>Available version: $available_version</p>";

    if ($current_version != $available_version) {
        return true;
    }
}

echo "<form method='POST'><button type='submit' name='check_updates'>Check for updates</button></form>";