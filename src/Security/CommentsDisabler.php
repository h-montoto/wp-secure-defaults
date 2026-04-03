<?php

declare(strict_types=1);

namespace WPSecureDefaults\Security;

defined('ABSPATH') || exit;

/**
 * Disables the WordPress comment system site-wide.
 *
 * Covers:
 * - Closing comments and pings on all content
 * - Hiding existing comments from queries
 * - Removing comment support from all post types
 * - Removing comment-related admin UI (menu, dashboard widget, admin bar)
 * - Disabling the /wp/v2/comments REST endpoint
 * - Redirecting direct access to comment admin pages
 */
final class CommentsDisabler
{
    public function register(): void
    {
        // Close comments and pings on all content (priority 20 runs after most theme/plugin filters)
        add_filter('comments_open', '__return_false', 20, 2);
        add_filter('pings_open', '__return_false', 20, 2);

        // Return an empty comment list so no existing comments are ever rendered
        add_filter('comments_array', [$this, 'returnEmptyArray'], 10, 2);

        // Remove comment support from all post types after they are registered (priority 100)
        add_action('init', [$this, 'removeCommentSupport'], 100);

        // Remove admin UI elements (priority 999 ensures everything is registered first)
        add_action('admin_menu', [$this, 'removeCommentMenus'], 999);
        add_action('wp_dashboard_setup', [$this, 'removeCommentDashboardWidget'], 20);
        add_action('admin_bar_menu', [$this, 'removeCommentAdminBarItem'], 999);

        // Disable /wp/v2/comments REST endpoint
        add_filter('rest_endpoints', [$this, 'disableCommentEndpoints'], 10);

        // Redirect direct access to comment admin pages
        add_action('admin_init', [$this, 'redirectCommentPages'], 10);
    }

    /** @param mixed $comments */
    public function returnEmptyArray($comments): array
    {
        return [];
    }

    public function removeCommentSupport(): void
    {
        foreach (get_post_types() as $postType) {
            if (post_type_supports($postType, 'comments')) {
                remove_post_type_support($postType, 'comments');
            }

            if (post_type_supports($postType, 'trackbacks')) {
                remove_post_type_support($postType, 'trackbacks');
            }
        }
    }

    public function removeCommentMenus(): void
    {
        remove_menu_page('edit-comments.php');
        remove_submenu_page('options-general.php', 'options-discussion.php');
    }

    public function removeCommentDashboardWidget(): void
    {
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
    }

    public function removeCommentAdminBarItem(\WP_Admin_Bar $wpAdminBar): void
    {
        $wpAdminBar->remove_node('comments');
    }

    /** @param array<string, mixed> $endpoints */
    public function disableCommentEndpoints(array $endpoints): array
    {
        foreach (array_keys($endpoints) as $route) {
            if (str_starts_with($route, '/wp/v2/comments')) {
                unset($endpoints[$route]);
            }
        }

        return $endpoints;
    }

    public function redirectCommentPages(): void
    {
        global $pagenow;

        if (in_array($pagenow, ['edit-comments.php', 'options-discussion.php'], true)) {
            wp_safe_redirect(admin_url());
            exit;
        }
    }
}
