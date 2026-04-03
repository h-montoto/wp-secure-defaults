<?php

declare(strict_types=1);

namespace WPSecureDefaults\Security;

defined('ABSPATH') || exit;

use WPSecureDefaults\Utils\Config;

/**
 * Restricts REST API access to authenticated users only.
 *
 * Safety guarantees:
 * - Logged-in users (including the Gutenberg block editor) are never blocked.
 * - Explicit authentication from other plugins (Application Passwords, OAuth, etc.)
 *   is preserved by checking for an existing non-null, non-error $result.
 * - Runs at priority 99 so other auth plugins have had a chance to authenticate first.
 */
final class RestApiHardener
{
    public function register(): void
    {
        // Restrict unauthenticated REST API access (priority 99 = late, after other auth plugins)
        add_filter('rest_authentication_errors', [$this, 'restrictRestApi'], 99);

        // Optionally remove the /wp/v2/users endpoint
        add_filter('rest_endpoints', [$this, 'removeUserEndpoints'], 10);
    }

    /**
     * Block unauthenticated REST requests.
     *
     * The filter value follows WordPress convention:
     * - null  → no authentication decision yet
     * - true  → explicitly authenticated by another plugin
     * - WP_Error → already rejected by another plugin
     *
     * @param \WP_Error|true|null $result
     * @return \WP_Error|true|null
     */
    public function restrictRestApi(\WP_Error|bool|null $result): \WP_Error|bool|null
    {
        // Respect a previous plugin's rejection
        if (is_wp_error($result)) {
            return $result;
        }

        // Respect a previous plugin's explicit authentication
        if (true === $result) {
            return $result;
        }

        // Allow logged-in users (covers cookie auth used by Gutenberg and wp-admin)
        if (is_user_logged_in()) {
            return $result;
        }

        return new \WP_Error(
            'rest_not_authorized',
            __('REST API access is restricted to authenticated users.', 'wp-secure-defaults-main'),
            ['status' => 401]
        );
    }

    /**
     * Remove the /wp/v2/users endpoint if restrict_rest_users is enabled.
     *
     * @param array<string, mixed> $endpoints
     * @return array<string, mixed>
     */
    public function removeUserEndpoints(array $endpoints): array
    {
        if (!Config::isEnabled('restrict_rest_users')) {
            return $endpoints;
        }

        foreach (array_keys($endpoints) as $route) {
            if (str_starts_with($route, '/wp/v2/users')) {
                unset($endpoints[$route]);
            }
        }

        return $endpoints;
    }
}
