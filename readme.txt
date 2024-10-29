=== Advanced Tagline ===
Contributors: kmorey
Author URI: http://kmorey.net
Plugin URI: http://kmorey.net/computers/advanced-tagline-wordpress-plugin/
Donate Link: http://pledgie.org/campaigns/3038
Tags: tagline, random, sequential, quote, quotes, widget
Requires at least: 2.5
Tested up to: 2.7.1
Stable tag: 1.5.3

Advanced Tagline gives the option to have multiple taglines for your blog and display
them at random or sequentially with each page view

== Description ==

Advanced Tagline gives the option to have multiple taglines for your blog and display them at random or sequentially with each page view.

Typical uses:

* Random Quotes
* Song Lyrics
* "Tip of the day" style help

**Features**

* Sequential or random display
* Easily edit active taglines
* Easily add/remove taglines
* Batch import (csv), overwrite or append
* Export to csv
* Each tag can have an optional link
* Store plugin data using get_option and update_option
* Uses 'get_header' action and 'bloginfo' filter to help avoid theme editing
* Can be used as a widget

**Version 1.5.3**
* Fixed conflicts with other plugins / admin features

**Version 1.5.2**
* Fixed a bug regarding the proper selection of the Options > Display Mode setting in admin. The value saves correctly, but it would always display with Standard selected.

**Version 1.5.1**
* Adjusted CSS to avoid conflicts with other parts of WordPress admin (namely widget management system. Thanks to @zaneselvans)
* Switched to use jQuery that is bundled with Wordpress to avoid jQuery.noConflict()

**Version 1.5**

* Switched from mootools to jQuery. This should help with some of the conflicts reported with other plugins and WP 2.7.
* Uses ajax for all changes (except import)
* Export to CSV

**Version 1.4.4**

* Changed `advtag_get_tagline([$echo = TRUE])` to `advtag_get_tagline([$show_link = TRUE [, $echo = TRUE]])`. In some cases, even though your tagline has a link, you might not want to show it, such as in the `<title></title>` tag. In cases like this, use `advtag_get_tagline(FALSE)` rather than `bloginfo('description')` or `bloginfo('advtag')`.

**Version 1.4.3**

* Fixed Internet Explorer bugs
* Fixed typos

**Version 1.4.2**

* Added option to replace blog description/tagline or stand alone (previous versions only replaced)
* Advanced Tagline can now be used as a widget

**Version 1.4.1**

* Fixed a bug causing Advanced Tagline to appear in the plugins list twice
* Fixed a bug causing sequential mode to skip every other tagline if Advanced Tagline <1.4 was previously installed and the call to `advtag_next_tagline()` wasn't removed

**Version 1.4**

* Tagline links may now have an optional target
* Implemented 'get_header' action and 'bloginfo' filter to greatly simplify installation

**Version 1.3**

* Fixed a bug that skipped the first tagline in sequential mode
* Fixed display if no link is specified to skip the anchor tag
* Fixed batch import to accept lines with one column (no link)
* Remove screenshots from package
* Visual style changes to blend better with WordPress admin styles

== Installation ==

1. Upload the 'advanced-tagline' folder to the '/wp-content/plugins/' directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure your taglines through Settings -> Advanced Tagline
1. The plugin now replaces bloginfo('description') so most themes won't need to be edited
1. If you wish to keep your blog description where it is, select 'Standalone' in the mode configuration and insert the following code into your theme: `<?php bloginfo('advtag'); ?>`

**NOTE:** After installing 1.4 or later, you can safely remove the following php code from your themes if you added it as a result of installing a previous version:

* `<?php if (function_exists("advtag_next_tagline")) { advtag_next_tagline(); } ?>`
* `<?php if (function_exists("advtag_get_tagline")) { advtag_get_tagline(); } ?>`
