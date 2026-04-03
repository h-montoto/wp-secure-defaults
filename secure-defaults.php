<?php
/**
 * Plugin Name: Secure Defaults
 * Plugin URI:  https://github.com/h-montoto/secure-defaults
 * Description: Baseline security hardening for WordPress. Disables comments, XML-RPC, restricts REST API, prevents user enumeration, and removes unnecessary head output.
 * Version:     0.0.1
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author:      Hugo Montoto
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: secure-defaults
 */

defined('ABSPATH') || exit;

// Plugin constants
define('SECURE_DEFAULTS_VERSION', '0.0.1');
define('SECURE_DEFAULTS_FILE', __FILE__);
define('SECURE_DEFAULTS_DIR', plugin_dir_path(__FILE__));

// PHP version gate — fail gracefully without breaking the site
if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    add_action('admin_notices', function (): void {
        printf(
            '<div class="notice notice-error"><p>%s</p></div>',
            esc_html__(
                'Secure Defaults requires PHP 8.0 or higher. The plugin has not been loaded.',
                'secure-defaults'
            )
        );
    });
    return;
}

// Load the custom PSR-4 autoloader (the only manual require in the plugin)
require_once SECURE_DEFAULTS_DIR . 'src/Core/Autoloader.php';
\SecureDefaults\Core\Autoloader::register();

// Flush rewrite rules on activation/deactivation so embed rules are handled correctly
register_activation_hook(__FILE__, function (): void {
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function (): void {
    flush_rewrite_rules();
});

// Boot the plugin after all plugins are loaded so third-party filters can override defaults
add_action('plugins_loaded', function (): void {
    $plugin = new \SecureDefaults\Core\Plugin();
    $plugin->run();
}, 10);
