<?php

declare(strict_types=1);

namespace WPSecureDefaults\Security;

defined('ABSPATH') || exit;

/**
 * Applies admin-area hardening measures.
 *
 * - Disables the theme/plugin file editor (DISALLOW_FILE_EDIT).
 * - Removes noisy dashboard widgets (Recent Comments, WP Events & News).
 */
final class AdminHardener
{
    public function register(): void
    {
        // Define immediately — WordPress checks this constant before admin loads the editor
        $this->disableFileEdit();

        add_action('wp_dashboard_setup', [$this, 'removeDashboardWidgets'], 20);
    }

    private function disableFileEdit(): void
    {
        // Guard against sites that already define this in wp-config.php
        if (!defined('DISALLOW_FILE_EDIT')) {
            define('DISALLOW_FILE_EDIT', true);
        }
    }

    public function removeDashboardWidgets(): void
    {
        // "Recent Comments" widget
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');

        // "WordPress Events and News" widget (dashboard_primary since WP 4.8+)
        remove_meta_box('dashboard_primary', 'dashboard', 'side');
    }
}
