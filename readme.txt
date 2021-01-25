=== whatwedo ACF Cleanup ===
Contributors:      whatwedo, marcwieland95
Tags:              admin, advanced custom fields, acf, cleanup, metadata
Requires at least: 5.5
Tested up to:      5.6
Stable tag:        1.0.0
License:           MIT
License URI:       https://opensource.org/licenses/MIT

Remove old metadata created by Advanced Custom Fields.

== Description ==

We analyze the post of the selected post types against the ACF groups in use. Afterwards we remove all data from deleted groups.
There's a dry run available (discovery) to see how many fields would get removed. On the actual cleanup you get prompted because it can't be undone (so make a backup first).

== Installation ==

= Manual Installation =

1. Upload the entire `/wwd-acf-cleanup` directory to the `/wp-content/plugins/` directory.
2. Activate Whatwedo ACF Cleanup through the 'Plugins' menu in WordPress.
3. Do a database backup manually or a third party tool
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

This plugin was created for our own usage to clean a big database from old ACF data.
It's not under active development but can be used (on your own risk).

== Changelog ==

= 1.0.0 (2020-1-25) =
* First release