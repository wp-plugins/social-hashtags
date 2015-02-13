====== Social Hashtags ======
Contributors: shanaver, mandiberg, thomasrstorey, hachacha, janiceaa
Tags: instagram, youtube, hashtags, videos, photos, images, API, twitter, teleportd
Requires at least: 3.0.1
Tested up to: 4.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Grabs images & videos matching any hashtag from social APIs like instagram & youtube.

== Update Plans ==

We are going to upgrade twitter to oAuth, and clean up the layout.

== Description ==

Grabs images & videos matching any hashtag from social APIs like instagram & youtube.  Stores thumbnails & details locally for each one in a custom post type so you have full control over the content on your site.  This allows you to categorize, make private/public, etc and include them any wayt hat you like on your pages.

Extendable to include twitter, telportd and others as well.

We first developed this for the original Kony2012 site, it was a huge hit.  Sorry that it took so long to get it into the Wordpress plugins.

== Installation ==

1. Upload dntly folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Add an API source & hashtag to pull from

== Frequently Asked Questions ==

= How long does it take to set up? =

It takes about 1 minute to start pulling in videos from youtube.  It takes about 5 minutes to start pulling in instagram content because you will need to create an instagram client first.

== Screenshots ==

1. Settings

== Changelog ==

= 1.0.0 =
* Initial Wordpress.com version

= 1.0.1 =
* Spelling fixes, etc

= 2.0.0 =
* Add basic default archive page template
* Fix cron functionality
* Add logging through WLS plugin
* Lots of cleanup & restructuring

= 2.0.1 =
* Add link to the archive page

= 2.0.2 =
* Use remote thumnail as media attachment image

= 2.3.0 =
* Allow for Custom Post Type name/slug to be custom defined

= 3.0.0
* Added option to keep or remove hashtagged words in retrieved posts.
* Added functionality to store more metadata from posts retrieved from Twitter and Instagram, including:
  * URL to post on Twitter/Instagram
  * URL to user on Twitter/Instagram
  * Timestamp for post from Twitter/Instagram
* Exposed metadata from retrieved posts to be displayed in the archive page.
* Added option to keep or remove Emoji from retrieved posts.
* Added option to whitelist usernames.
* Added option to pick a wordpress user to use as the author for social-hashtag posts.
* Verified for Wordpress 4.1

== Upgrade Notice ==

= 1.0.0 =
Initial release

