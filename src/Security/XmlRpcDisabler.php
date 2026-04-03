<?php

declare(strict_types=1);

namespace WPSecureDefaults\Security;

defined('ABSPATH') || exit;

/**
 * Disables XML-RPC and pingbacks completely.
 *
 * Defense in depth: even if a plugin re-enables XML-RPC, the pingback
 * methods are still removed and the X-Pingback header is stripped.
 */
final class XmlRpcDisabler
{
    public function register(): void
    {
        // Disable XML-RPC protocol entirely
        add_filter('xmlrpc_enabled', '__return_false');

        // Remove pingback-specific methods as a secondary layer
        add_filter('xmlrpc_methods', [$this, 'removePingbackMethods']);

        // Strip the X-Pingback header from all responses
        add_filter('wp_headers', [$this, 'removePingbackHeader']);

        // Close pings (belt-and-suspenders alongside CommentsDisabler)
        add_filter('pings_open', '__return_false', 10, 2);
    }

    /** @param array<string, callable> $methods */
    public function removePingbackMethods(array $methods): array
    {
        unset($methods['pingback.ping'], $methods['pingback.extensions.getPingbacks']);

        return $methods;
    }

    /** @param array<string, string> $headers */
    public function removePingbackHeader(array $headers): array
    {
        unset($headers['X-Pingback']);

        return $headers;
    }
}
