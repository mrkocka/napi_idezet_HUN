=== Daily Quotes MRK ===
Contributors: mrkocka
Tags: quote, daily quote, elementor, magyar
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Requires Plugins: elementor
Stable tag: 1.1.2
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display one daily quote with its author in Elementor. Manage your own Hungarian or multilingual quotes in the WordPress dashboard.

== Description ==

Daily Quotes MRK displays a daily quote and its attribution using a native Elementor widget. It has no button, shortcode, public AJAX endpoint, external quote service, or bundled quote collection.

* Add quotes and optional author names in the dashboard.
* Edit quotes or archive them without physically deleting their original records.
* Style the quote and author separately using Elementor typography, color, and text-shadow controls.
* Choose horizontal or vertical layout, spacing, and alignment per device.
* All widget instances use the same daily selection in the site's WordPress timezone.

Elementor is required to use the widget. The quote management screen remains available if Elementor is temporarily disabled. Elementor Pro is not required.

The default interface language is English and all user-facing strings are translatable.

= Daily selection and caching =

A daily quote is selected when the widget is first rendered on that calendar day, using the WordPress site timezone. Adding a quote does not replace today's selection. Archiving today's quote immediately excludes it from future server renders and selects another active quote if available. Consecutive days may select the same quote.

Elementor element caching is disabled for this dynamic widget. Full-page, CDN, or browser caches can still serve previously rendered HTML: configure these to expire at the daily change and purge them after edits or archival. This plugin does not purge third-party caches.

If there are no active quotes, the public widget renders nothing. The Elementor editor displays an explanatory message.

= Data and permissions =

Only administrators are granted the plugin's access, creation, editing, and archival capabilities by default. Other roles are not automatically given access.

Quotes use the napi_idezet custom post type, with the quote author stored as post metadata. The WordPress user who submitted a quote is stored separately. Archival changes the post status to napi_idezet_archived; it does not delete the quote text, attribution, or original submitter.

Active quotes are intended for public display even though their internal storage status is private. Do not enter confidential information or quotes you do not have permission to publish.

Deactivation and uninstall retain quotes, attribution, plugin options, and granted capabilities. There is no automatic data deletion or permanent-delete action in this plugin.

This plugin makes no external requests and does not collect analytics, create cookies, or send quote data to third parties. It does not include Elementor; Elementor's own behavior and settings are separate.

= Migration =

The plugin reuses records from the earlier custom Napi idezet shortcode plugin. Deactivate that version before using this standalone Elementor version. Existing Elementor widget instances from version 1.0.0 remain compatible.

== Installation ==

1. Install and activate Elementor.
2. Upload the napi-idezet-elementor directory to wp-content/plugins, or install the release ZIP through Plugins > Add New > Upload Plugin.
3. Activate Daily Quotes MRK.
4. Open Quotes in the dashboard and add a quote.
5. Open an Elementor page and add the Daily Quotes MRK widget.
6. Adjust layout under Content and the quote/author typography and colors under Style.

== Frequently Asked Questions ==

= Is Elementor Pro required? =

No. The free Elementor plugin is sufficient.

= Will my existing quotes survive an update or uninstall? =

Yes. This plugin retains quote records and metadata. Always maintain a database backup.

= Can I restore an archived quote in the interface? =

There is no restore action yet. The record remains in the database.

= Why is yesterday's quote still visible? =

Check the WordPress timezone and purge your full-page or CDN cache. The new selection is made when WordPress renders the widget on the new day.

== Changelog ==

= 1.1.2 =
* Use English for all interface strings, widget controls, notices, and plugin metadata.
* Refresh the translation template. Existing quote content is unchanged.

= 1.1.1 =
* Rename the plugin to Daily Quotes MRK; retain all data and widget identifiers.

= 1.1.0 =
* Prepare the standalone Elementor edition for directory submission.
* Add dependency metadata, translation support, license, and documentation.
* Preserve quote management and the daily Elementor widget.

= 1.0.0 =
* Initial standalone daily Elementor widget with separate quote and author styles.

== Upgrade Notice ==

= 1.1.2 =
The default interface is now English. Existing quotes and widget settings are retained.

= 1.1.1 =
Plugin name changed to Daily Quotes MRK. No data migration is required.

= 1.1.0 =
Directory preparation release. Existing quote records and widget identifiers are retained.
