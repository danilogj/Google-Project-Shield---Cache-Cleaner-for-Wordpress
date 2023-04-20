<?php
/*
Plugin Name: Clear Cache for Google Project Shield
Plugin URI: https://www.netcraw.com/servicos/google-project-shield-plugin-wordpress/
Description: Plugin to clear the cache of Google Project Shield via API.
Version: 0.1
Author: Danilo Jorge (Netcraw Creative IT)
Author URI: https://www.netcraw.com/danilojorge
*/

// Register a custom menu page and two submenu pages using the WordPress API
add_action('admin_menu', 'my_admin_menu');
function my_admin_menu() {
    add_menu_page('Google Project Shield', 'Google Project Shield', 'manage_options', 'google_project_shield', 'clear_cache_page');
    add_submenu_page('google_project_shield', 'Config', 'Config', 'manage_options', 'clear_cache_options', 'clear_cache_options');
    add_submenu_page('google_project_shield', 'Clear Cache', 'Clear Cache', 'manage_options', 'clear_cache_page', 'clear_cache_page');
}

// Display a form to enter the API key, host name and site ID and save these options
function clear_cache_options() {
    if (isset($_POST['api_key']) && isset($_POST['hostname']) && isset($_POST['site_id'])) {
        $api_key = sanitize_text_field($_POST['api_key']);
        $hostname = sanitize_text_field($_POST['hostname']);
        $site_id = sanitize_text_field($_POST['site_id']);
        update_option('clear_cache_api_key', $api_key);
        update_option('clear_cache_hostname', $hostname);
        update_option('clear_cache_site_id', $site_id);
        echo '<div class="updated"><p>Options saved successfully!</p></div>';
    } else {
        $api_key = get_option('clear_cache_api_key', '');
        $hostname = get_option('clear_cache_hostname', '');
        $site_id = get_option('clear_cache_site_id', '');
    }
    ?>
    <div class="wrap">
        <h2>Config</h2>
        <p>Developed by <a href="https://www.netcraw.com/">Netcraw Creative IT</a>.</p>
        <form method="post">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">API Key:</th>
                    <td><input type="text" name="api_key" value="<?php echo esc_attr($api_key); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Host Name:</th>
                    <td><input type="text" name="hostname" value="<?php echo esc_attr($hostname); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Site ID:</th>
                    <td><input type="text" name="site_id" value="<?php echo esc_attr($site_id); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Display a form to enter the path to clear and send a POST request to the Google Project Shield API
function clear_cache_page() {
    if (isset($_POST['path'])) {
        $path = sanitize_text_field($_POST['path']);
        $response_code = clear_cache($path);
        if ($response_code == 201) {
            echo '<div class="updated"><p>Cache cleared successfully!</p></div>';
        } else {
            echo '<div class="error"><p>Error clearing cache. Please wait 60 seconds before trying again.</p></div>';
        }
    }
    ?>
    <div class="wrap">
        <h2>Clear Cache</h2>
        <p>Developed by <a href="https://www.netcraw.com/">Netcraw Creative IT</a>.</p>
        <form method="post">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Path:</th>
                    <td><input type="text" name="path" value="<?php echo isset($_POST['path']) ? esc_attr($_POST['path']) : '/'; ?>" /></td>
                </tr>
            </table>
            <?php submit_button('Clear Cache'); ?>
        </form>
    </div>
    <?php
}

// Send a POST request to the Google Project Shield API to clear the cache for the specified path
function clear_cache($path) {
    $api_key = get_option('clear_cache_api_key', '');
    $hostname = get_option('clear_cache_hostname', '');
    $site_id = get_option('clear_cache_site_id', '');

    $url = 'https://api.projectshield.withgoogle.com/invalidate-cache/site/' . $site_id;
    $body = array(
        'api_key' => $api_key,
        'hostname' => $hostname,
        'path' => $path
    );

    $response = wp_remote_post($url, array(
        'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
        'body' => json_encode($body),
        'method' => 'POST'
    ));

    return wp_remote_retrieve_response_code($response);
}

