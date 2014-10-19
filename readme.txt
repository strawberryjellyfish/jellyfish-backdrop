=== Jellyfish backdrop ===
Contributors: toxicToad
Author URI: http://strawberryjellyfish.com/
Donate link: http://strawberryjellyfish.com/donate/
Plugin URL: http://strawberryjellyfish.com/wordpress-plugin-jellyfish-backdrop/
Tags: background, fullscreen, gallery, slideshow, image
Requires at least: 3.0
Tested up to: 4.0
Stable tag: 0.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enables a fullscreen background image or background slideshow on any WordPress
website including the ability for individual post or page backgrounds.

== Description ==

This plugin for WordPress will allow you to have a full screen image
background that stretches and adapts to the size of our browser window.
Backgrounds can also be limited to specific areas of the screen.

You can either use it as a global background that will be displayed on all
pages and posts of your WordPress website or you can give individual posts
or pages their own specific background – great for giving parts of a website
a whole different look.

Jellyfish backdrop has one final trick, you may assign multiple backgrounds
to any page or post which will display in sequence as a dramatic looping fading
background slideshow. If you like you could just use it as a slideshow on one
specific post or page, then let your theme display as normal everywhere else.

=Demo=
See the plugin homepage for demos and full details:
http://strawberryjellyfish.com/wordpress-plugin-jellyfish-backdrop/

== Installation ==

Extract the zip file and just drop the contents in the wp-content/plugins/
directory of your WordPress installation and then activate the Plugin from
Plugins page.

After the plugin is activated you'll find a new Backdrop Settings page under
the Appearance menu of your WordPress Admin. Here you can configure the
global options.

==Usage==

In the settings page in the Appearance menu of your WordPress admin, you can
configure the default settings and choose whether you want a global background
or not.

To add a unique background image to a specific post or page simply open the
post or page in the post/page editor and add a custom field named
"background_image" the value of this field should be the full URL of your
desired background image.

Page / Post backgrounds will override the default background so it's possible
to define one background to be used site wide and another on a specific page.

To create a slide show just add as many background_image custom fields and
URLs as you wish. A slideshow will automatically run if more than one
background_image custom field exists. You can only define slideshows on a
specific post or page, not for the entire site.

By default images are shown as the main page background (body), however you
can make the images appear as backgrounds to other areas of the page by
supplying any valid element id or classname in the Container field on the
admin page. eg. #main, .header

== Frequently Asked Questions ==

== Changelog ==

= 0.5 =
* Added image preloading to JavaScript
* Added container element option
* Code cleanups
* Tested up to WordPress 4.0

= 0.4 =
* changed JavaScript enqueue so it ONLY shows up when required
* updated to latest  jQuery.backstretch.min.js  - v2.0.4

= 0.3 =
* fixed incorrectly queued JavaScript. Changed form input validation method and
  reworked check box handling to clean up some undefined index warnings

= 0.2 =
* Initial Release.

== Upgrade Notice ==

Existing backgrounds should not be effected by an upgrade but it is always good
practice to backup your database and installation before performing an upgrade.

After an upgrade visit the admin page to check the new options available
to your counters.

== Screenshots ==