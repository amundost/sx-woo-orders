<?php
echo "<form method='POST'><button type='submit' name='check_updates'>Check for updates</button></form>";

if (isset($_POST['check_updates'])) {
    sx_woo_orders_check_for_update();
}
if (isset($_POST['update_plugin'])) {
    $plugin_slug = 'sx-woo-orders'; // The slug of your plugin
    $version = $_POST['version'];
    $zip_url = "https://github.com/amundost/sx-woo-orders/archive/refs/tags/$version.zip";

    $result = updatePlugin($plugin_slug, $zip_url);

    if ($result) {
        echo 'Plugin updated successfully!';
    } else {
        echo 'Failed to update the plugin.';
    }
}

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
    $data = json_decode($response_body);
    $available_version = 'Not able to load version tag';
    if (isset($data->tag_name))
        $available_version = $data->tag_name;
    echo "<p>Available version: $available_version</p>";

    if ($current_version != $available_version) {
        echo "<form method='POST'>
                <input type='hidden' name='version' value=$available_version>
                <button type='submit' name='update_plugin'>Update plugin</button>
            </form>";
    } else {
        echo "No update available";
    }
}

function updatePlugin($plugin_slug, $zip_url)
{
    if (!current_user_can('update_plugins')) {
        return false;
    }

    include_once (ABSPATH . 'wp-admin/includes/class-wp-upgrader.php'); // Load the upgrade API

    // Create a temporary directory to store the plugin zip
    $tmp_dir = get_temp_dir();
    $tmp_file = $tmp_dir . '/' . $plugin_slug . '.zip';

    // Fetch the new plugin zip file
    $response = wp_remote_get($zip_url, array('timeout' => 300));

    if (is_wp_error($response)) {
        return false;
    }

    // Save the zip file to the temporary directory
    if (!file_put_contents($tmp_file, wp_remote_retrieve_body($response))) {
        return false;
    }

    // Initialize the Plugin Upgrader
    $upgrader = new Plugin_Upgrader();
    $result = $upgrader->install($tmp_file);

    // Clean up: delete the temporary zip file
    unlink($tmp_file);

    if (is_wp_error($result)) {
        return false;
    }

    // Deactivate the plugin before updating
    $plugin_file = WP_PLUGIN_DIR . '/' . $plugin_slug . '/' . $plugin_slug . '.php';
    $was_active = is_plugin_active($plugin_file);
    if ($was_active) {
        deactivate_plugins($plugin_file);
    }

    // Update the plugin
    $result = $upgrader->upgrade($plugin_file);

    // Reactivate the plugin if it was active before the update
    if ($was_active) {
        activate_plugin($plugin_file);
    }

    return true;
}
?>