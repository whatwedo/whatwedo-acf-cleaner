=== whatwedo ACF Cleaner ===
Contributors:      whatwedo, marcwieland95, trilliput
Tags:              admin, advanced custom fields, acf, cleanup, cleaner, metadata
Requires at least: 5.5
Tested up to:      6.0.1
Requires PHP:      7.0
Stable tag:        1.1.0
License:           MIT
License URI:       https://opensource.org/licenses/MIT

Cleanup old metadata created by Advanced Custom Fields.

== Description ==

We analyze the post of the selected post types against the ACF groups in use. Afterwards we remove all data from deleted groups.
There's a dry run available (discovery) to see how many fields would get removed. On the actual cleanup you get prompted because it can't be undone (so make a backup first).

== Screenshots ==
1. The option page for the plugin. You can find it in "Tools / ACF Cleaner"
2. On every post type with unused data the following metabox is shown

== Installation ==

The plugin can be found it the WordPress Plugin Directory. Search for "ACF Cleaner".

= Manual Installation =

1. Upload the entire `/wwd-acf-cleaner` directory to the `/wp-content/plugins/` directory.
2. Activate "whatwedo ACF Cleaner" through the 'Plugins' menu in WordPress.
3. Do a database backup manually or use a third party tool
4. Go to "Tools / ACF Cleaner"
4. Select the post types you want to clean and run it

== Frequently Asked Questions ==

= What about conditional fields =
Conditional fields are not taken in account at this point. This is probably also the issue why there's nothing like this baked into the ACF plugin itself. Down the road it gets pretty complex.

= Output the names of the deleted fields =
We are aware that it would be handy to know which exact fields will get deleted, so the user can analyzing the actual data. This could be integrated in a future release.
We already know the name of the fields and we're also returning them from the server. Since they're a lot of unordered data, we don't do something proper with those in the frontend .

== Contribute ==

Developed with ♥ by [whatwedo](https://whatwedo.ch) in Bern.
Thanks to [TrilipuT](https://github.com/TrilipuT) for contributing.

This plugin was initially created for our own usage to clean a big database from old ACF data.

Check out the [GitHub repository](https://github.com/whatwedo/whatwedo-acf-cleaner) and submit pull requests or open issues

== Changelog ==

= 1.0.0 (2020-02-22) =
* First release
= 1.1.0 (2022-06-22) =
* Add single post metabox with detailed info for unused fields (thanks to [TrilipuT](https://github.com/TrilipuT))
* More precise unused fields detection added (thanks to [TrilipuT](https://github.com/TrilipuT))
* Add check if Advanced Custom Fields is installed (thanks to [sebastian_a](https://profiles.wordpress.org/sebastian_a/)) for reporting it
