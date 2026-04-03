=== Secure Defaults ===
Contributors: hmontoto
Tags: security, hardening, comments, xmlrpc, rest-api
Requires at least: 6.0
Tested up to: 6.9
Stable tag: 0.0.1
Requires PHP: 8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Baseline security hardening for WordPress. Disables comments, XML-RPC, restricts REST API, and removes unnecessary head output.

== Description ==

Secure Defaults applies opinionated security hardening as a baseline for any WordPress installation. It works entirely through WordPress hooks — no database writes, no persistent state. Deactivating the plugin fully restores the original behaviour.

**Security features:**

* Disable comments globally (all post types, REST endpoint, admin UI)
* Disable XML-RPC and pingbacks
* Restrict REST API to authenticated users only (Gutenberg-safe)
* Remove `/wp/v2/users` REST endpoint
* Block `?author=N` user enumeration
* Disable file editor in wp-admin (`DISALLOW_FILE_EDIT`)
* Remove noisy dashboard widgets

**Cleanup features:**

* Remove RSD, WLW, generator meta, shortlink and REST link from `<head>`
* Remove WordPress version from RSS feeds
* Disable emoji scripts, styles and DNS prefetch
* Disable oEmbed scripts and discovery

Every feature can be toggled independently via WordPress filters:

`add_filter('secure_defaults_disable_comments', '__return_false');`

See the full filter list in the FAQ section.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin from **Plugins > Installed Plugins**.

All features are enabled by default. No configuration required.

== Frequently Asked Questions ==

= How do I disable a specific feature? =

Add the relevant filter to your theme's `functions.php` or a site-specific plugin:

`add_filter('secure_defaults_disable_comments', '__return_false');`

Available filters:

* `secure_defaults_disable_comments`
* `secure_defaults_disable_xmlrpc`
* `secure_defaults_restrict_rest_api`
* `secure_defaults_restrict_rest_users`
* `secure_defaults_prevent_user_enumeration`
* `secure_defaults_admin_hardening`
* `secure_defaults_clean_head`
* `secure_defaults_disable_emojis`
* `secure_defaults_disable_embeds`

= Will this break the Gutenberg block editor? =

No. The REST API restriction only applies to unauthenticated requests. Logged-in users, including Gutenberg, are never blocked.

= Will this break other plugins that use XML-RPC? =

Yes, intentionally. If you need XML-RPC for a specific plugin, disable the feature:
`add_filter('secure_defaults_disable_xmlrpc', '__return_false');`

= Does deactivating the plugin undo all changes? =

Yes. All modifications are runtime hooks. Deactivating the plugin immediately restores WordPress defaults. The only exception is `DISALLOW_FILE_EDIT` — if it was already defined in `wp-config.php`, the plugin does not touch it.

== Screenshots ==

== Changelog ==

= 0.0.1 =
* Initial release.

== Upgrade Notice ==

= 0.0.1 =
Initial release.
