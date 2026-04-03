<?php

declare(strict_types=1);

namespace WPSecureDefaults\Utils;

defined('ABSPATH') || exit;

/**
 * Central configuration for WP Secure Defaults.
 *
 * Each feature maps to a WordPress filter that allows developers to override
 * the default behaviour from a theme's functions.php or another plugin:
 *
 *   add_filter('wp_secure_defaults_disable_comments', '__return_false');
 *
 * Unknown features default to false (fail-closed).
 */
final class Config
{
    private const FILTER_PREFIX = 'wp_secure_defaults_';

    /**
     * Default enabled/disabled state for each feature.
     *
     * @var array<string, bool>
     */
    private static array $defaults = [
        'disable_comments'         => true,
        'disable_xmlrpc'           => true,
        'restrict_rest_api'        => true,
        'restrict_rest_users'      => true,  // sub-feature: remove /wp/v2/users endpoint
        'prevent_user_enumeration' => true,
        'admin_hardening'          => true,
        'clean_head'               => true,
        'disable_emojis'           => true,
        'disable_embeds'           => true,
    ];

    public static function isEnabled(string $feature): bool
    {
        $default = self::$defaults[$feature] ?? false;

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound -- prefix is always 'wp_secure_defaults_'
        return (bool) apply_filters(self::FILTER_PREFIX . $feature, $default);
    }
}
