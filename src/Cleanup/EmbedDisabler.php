<?php

declare(strict_types=1);

namespace WPSecureDefaults\Cleanup;

defined('ABSPATH') || exit;

/**
 * Disables WordPress oEmbed and the wp-embed script.
 *
 * Removes:
 * - oEmbed discovery links in <head>
 * - wp-embed.min.js (loaded in footer)
 * - oEmbed REST route (/wp-json/oembed/...)
 * - Embed rewrite rules (?embed=true)
 * - oEmbed data parsing and pre-result filters
 * - oEmbed external URL discovery
 */
final class EmbedDisabler
{
    public function register(): void
    {
        add_action('init', [$this, 'removeEmbedHooks'], 99);

        // wp-embed script is enqueued late; deregister it in the footer before it prints
        add_action('wp_footer', [$this, 'deregisterEmbedScript'], 1);

        // Remove embed-related rewrite rules
        add_filter('rewrite_rules_array', [$this, 'removeEmbedRewriteRules']);

        // Disable external oEmbed discovery
        add_filter('embed_oembed_discover', '__return_false');
    }

    public function removeEmbedHooks(): void
    {
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
        remove_action('wp_head', 'wp_oembed_add_host_js');
        remove_action('rest_api_init', 'wp_oembed_register_route');
        remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
        remove_filter('pre_oembed_result', 'wp_filter_pre_oembed_result', 10);
    }

    public function deregisterEmbedScript(): void
    {
        wp_deregister_script('wp-embed');
    }

    /**
     * Strip rewrite rules that expose content via the embed endpoint.
     *
     * @param array<string, string> $rules
     * @return array<string, string>
     */
    public function removeEmbedRewriteRules(array $rules): array
    {
        foreach (array_keys($rules) as $rule) {
            if (str_contains($rules[$rule], 'embed=true')) {
                unset($rules[$rule]);
            }
        }

        return $rules;
    }
}
