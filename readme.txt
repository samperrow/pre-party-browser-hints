=== Pre * Party Resource Hints ===
Contributors: Sam Perrow
Donate link: https://www.paypal.com/us/cgi-bin/webscr?cmd=_flow&SESSION=PMdwpV-0mzP8aloKEF8VGrQ6uiNwwXP7vzkFyjm_p9X7NqGMgkF1lYzxN7G&dispatch=5885d80a13c0db1f8e263663d3faee8dcce3e160f5b9538489e17951d2c62172
Tags: W3C, DNS prefetch, prerender, preconnect, prefetch, preload, web perf, performance, speed, resource hints
Requires at least: 4.0
Tested up to: 4.8
Stable tag: 4.4.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Take advantage of the latest W3C browser implementations of DNS-prefetch, prerender, preconnect, prefetch, and preload to improve page load time.

== Description ==

This plugin allows users to easily embed resource hints from domain names and URL's from external sources on selected pages to improve page load time. DNS prefetch, prerender, preconnect, prefetch, and preload are all supported. By default, preconnect hints will automatically be enabled for all pages and posts.

== Installation ==

1. Upload the entire `pre-party-browser-hints` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

FAQ

How can I tell which URL's to enter?
Go to https://www.webpagetest.org, enter your website's URL, and click on the "waterfall" chart that appears.
a) For all resources that are loaded from external websites or domain names, I recommend inserting a preconnect link for that domain name (Preconnect does more than DNS Prefetch).
b) If you have a very popular link on your site that you are confident a user would navigate towards, I recommend inserting a link for that URL with the "prerender" option set.
c) Prefetch and preload work similarly, which allows single resources to be loaded before they are requested by the user. Use this for loading images, videos, JavaScript files, etc, on different pages.

DNS Prefetch:

For all HTTP requests loaded from external sources on a page web, add the domain name of each on the "Insert URLs" tab, select the option for "DNS Prefetch", and select the page/post you would like to use the hint on.

Prefetch:

Insert an absolute URL for a CSS, JavaScript, image, etc, that is hosted on an external domain, select the option "Prefetch", and select the page/post you would like to use the hint on.

Prerender:

Insert a valid domain name that is a visitor to your website is likely to visit, select the option "Prerender", and select the page/post you would like to use the hint on. The domain you entered will now be loaded by the browser after all requests have been loaded on the page.

Preconnect:

If you would like to have preconnect links automatically set, they will automatically do so when you visit the plugin screen. If you would like these removed, just select the option to have these disabled at the bottom of the main plugin screen.
Insert a valid domain name which you are requesting resources from on a particular page, select the "Preconnect" radio button, and the page/post you would like to use this resource hint.


Preload:

Insert an absolute URL for a CSS, JavaScript, image, etc, that is hosted on an external domain, select the option "Preload", and select the page/post you would like to use the hint on.

== Screenshots ==

1. screenshot-1.jpg




1. [Support Forum](https://wordpress.org/support/plugin/pre-party-browser-hints)
2. Send me an email at sam.perrow399@gmail.com





== Changelog ==

1. Most recent update: July 6, 2017.
2. Version 1.2

July 6: 1) added ability to choose to add links to the Header or <head>;
        2) cleaned up UI;
        3) fixed bugs on find-external-domains.js/ improved functionality;
        4) removed option to select which pages/posts the links went to- this added bugs and increased unnecessary bugs;
        5) condensed the info tab links into one;
        6) modified schema for gktpp_table; and removed the other table;
        7) improved security;


June 11: 1) tested compat with wp 4.8

Mar 27: 1) changed db table schema which caused problems for sites with lots of posts.

Dec 9: 1) Ensured compatibility with WP v 4.7

Nov 11: 1) added plugin icon and banner image. 2) added tip info

Nov 6: 1) updated screenshot image on wp plugin page.

Nov 6: 1) a) added screenshot images on plugin page. b) removed 'console.log' in the front end JS file.

Nov 4: 1) Fixed some images that were not loading properly.

Nov 3: 1) initial commit.
