<?php

declare(strict_types=1);

namespace WPSecureDefaults\Security;

defined('ABSPATH') || exit;

use WPSecureDefaults\Utils\Config;

/**
 * Prevents user enumeration via ?author=N query strings and the REST API.
 *
 * Two-layer defence for the query-string attack:
 * 1. redirect_canonical — cancels the canonical redirect (which would expose the username slug).
 * 2. template_redirect — catches any remaining requests and sends a 301 to home.
 *
 * For the REST /wp/v2/users endpoint this module only acts as a fallback when
 * RestApiHardener is disabled, to avoid processing the filter twice.
 */
final class UserEnumerationProtection
{
    public function register(): void
    {
        if (!is_admin()) {
            add_filter('redirect_canonical', [$this, 'blockAuthorEnumeration'], 10, 2);
            add_action('template_redirect', [$this, 'blockAuthorEnumerationFallback'], 1);
        }

        // Only remove /wp/v2/users here when RestApiHardener is not doing it already
        add_filter('rest_endpoints', [$this, 'removeUserEndpoints'], 10);
    }

    /**
     * Cancel the canonical redirect for numeric author queries.
     *
     * @param string|false $redirect The redirect URL WordPress wants to use.
     * @param string       $request  The original request URL.
     * @return string|false
     */
    public function blockAuthorEnumeration(string|false $redirect, string $request): string|false
    {
        if (is_user_logged_in()) {
            return $redirect;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- intentional: detecting enumeration on public URLs, no nonce expected
        if (isset($_GET['author']) && is_numeric($_GET['author'])) {
            return false;
        }

        return $redirect;
    }

    /**
     * Fallback: redirect numeric author requests directly to home.
     * Catches cases where redirect_canonical is not triggered (e.g. plain permalinks).
     */
    public function blockAuthorEnumerationFallback(): void
    {
        if (is_user_logged_in()) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- intentional: detecting enumeration on public URLs, no nonce expected
        if (isset($_GET['author']) && is_numeric($_GET['author'])) {
            wp_safe_redirect(home_url('/'), 301);
            exit;
        }
    }

    /**
     * Remove /wp/v2/users endpoint — only when RestApiHardener is not active,
     * to avoid processing the same filter twice.
     *
     * @param array<string, mixed> $endpoints
     * @return array<string, mixed>
     */
    public function removeUserEndpoints(array $endpoints): array
    {
        if (!Config::isEnabled('restrict_rest_users')) {
            return $endpoints;
        }

        // RestApiHardener already removes these when it is active
        if (Config::isEnabled('restrict_rest_api')) {
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
