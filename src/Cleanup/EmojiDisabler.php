<?php

declare(strict_types=1);

namespace WPSecureDefaults\Cleanup;

defined('ABSPATH') || exit;

/**
 * Removes all WordPress emoji-related assets and functionality.
 *
 * Removes:
 * - Emoji detection JavaScript (frontend and admin)
 * - Emoji CSS styles (frontend and admin)
 * - Staticize emoji filters for feeds and email
 * - TinyMCE emoji plugin
 * - DNS prefetch hint for the emoji SVG CDN
 */
final class EmojiDisabler
{
    public function register(): void
    {
        // Defer remove_action calls to init so core hooks are registered first.
        // Note: print_emoji_detection_script is added at priority 7 on wp_head — must match.
        add_action('init', [$this, 'removeEmojiHooks'], 99);

        add_filter('tiny_mce_plugins', [$this, 'removeTinyMceEmoji']);
        add_filter('wp_resource_hints', [$this, 'removeDnsPrefetch'], 10, 2);
    }

    public function removeEmojiHooks(): void
    {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    }

    /** @param array<int|string, mixed> $plugins */
    public function removeTinyMceEmoji(array $plugins): array
    {
        return array_diff($plugins, ['wpemoji']);
    }

    /**
     * @param array<int|string, mixed> $urls
     * @return array<int|string, mixed>
     */
    public function removeDnsPrefetch(array $urls, string $relationType): array
    {
        if ($relationType !== 'dns-prefetch') {
            return $urls;
        }

        // Use the WP core filter to get the actual SVG URL in case it changes in future versions
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- emoji_svg_url is a WP core hook
        $emojiSvgUrl = (string) apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/');

        return array_values(array_filter($urls, function (mixed $url) use ($emojiSvgUrl): bool {
            $urlString = is_array($url) ? (string) ($url['href'] ?? '') : (string) $url;

            return !str_contains($urlString, $emojiSvgUrl);
        }));
    }
}
