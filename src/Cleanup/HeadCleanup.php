<?php

declare(strict_types=1);

namespace WPSecureDefaults\Cleanup;

defined('ABSPATH') || exit;

/**
 * Removes unnecessary tags and links from the HTML <head>.
 *
 * Removed items:
 * - RSD (Really Simple Discovery) link
 * - Windows Live Writer manifest link
 * - WordPress version generator meta tag (frontend and RSS feeds)
 * - Shortlink
 * - REST API link
 * - oEmbed discovery links
 *
 * All remove_action() calls are deferred to init priority 99 to guarantee
 * that WordPress core has already added these hooks before we remove them.
 */
final class HeadCleanup
{
    public function register(): void
    {
        add_action('init', [$this, 'removeHeadLinks'], 99);

        // the_generator also fires in RSS feeds, not just wp_head
        add_filter('the_generator', [$this, 'returnEmptyString']);
    }

    public function removeHeadLinks(): void
    {
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wlw_manifest_link');
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'wp_shortlink_wp_head', 10);
        remove_action('wp_head', 'rest_output_link_wp_head', 10);
        remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
    }

    public function returnEmptyString(): string
    {
        return '';
    }
}
