=== Pre* Party Resource Hints ===
Contributors: Sam Perrow
Donate link: https://www.paypal.me/samperrow
Tags: W3C, DNS prefetch, prerender, preconnect, prefetch, preload, web perf, performance, speed, resource hints
Requires at least: 4.4
Tested up to: 4.9.4
Stable tag: 4.4.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Take advantage of browser resource hints and plug-and-play features to improve page load time.

== Description ==

This plugin allows users to easily embed resource hints from domain names and URL's from external sources to improve page load time. DNS prefetch, prerender, preconnect, prefetch, and preload are all supported. By default, preconnect hints will automatically be enabled for all pages and posts. You have the choice to include these hints in the HTTP header or the website's <head>.

== Installation ==

1. Upload the entire `pre-party-browser-hints` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

FAQ

How can I determine which URL's to enter?
Go to https://www.webpagetest.org, enter your website's URL, and click on the "waterfall" chart that appears.
a) For all resources that are loaded from external websites or domain names, I recommend inserting a preconnect link for that domain name (Preconnect is more powerful than DNS Prefetch, as it takes care of the DNS lookup, initial connection, and SSL negotiation).
b) If you have a very popular link on your site that you are confident a user would navigate towards, I recommend inserting a link for that URL with the "prerender" option set.
c) Prefetch and preload work similarly, which allows single resources to be loaded before they are requested by the user. Use this for loading images, videos, JavaScript files, etc.

DNS Prefetch:
For all HTTP requests loaded from external sources on a page web, add the domain name of each in the "Add New Resource Hint" form, select the option for "DNS Prefetch".

Prefetch:
Insert an absolute URL for a CSS, JavaScript, image, etc, that is hosted on an external domain, and select the option "Prefetch".

Prerender:
Insert a valid URL that a visitor to your website is likely to visit, and select the option "Prerender". The URL you entered will now be loaded by the browser after all requests have been loaded on the page.

Preconnect:
For all HTTP requests loaded from external sources on a page web, add the domain name of each in the "Add New Resource Hint" form, select the option for "Preconnect". Preconnect is more powerful than DNS Prefetch, because it resolves three connections instead of one.

If you would like to have preconnect links automatically set, by default the . If you would like these removed, just select the option to have these disabled at the bottom of the main plugin screen.

How are the preconnect hints automatically set?
By default, after installing this plugin and loading a page from your website, a JavaScript file will be loaded which searches for the domain names from resources loaded from external domains. These domains are sent via Ajax to your website's MySQL database, which are then used as resource hints for subsequent page loads.

Preload:
Insert an absolute URL for a CSS, JavaScript, image, etc, and select the option "Preload".



== Screenshots ==

1. screenshot-1.png

2. screenshot-2.png

3. screenshot-3.png

4. screenshot-4.png




1. [Support Forum](https://wordpress.org/support/plugin/pre-party-browser-hints)

2. Send me an email at sam.perrow399@gmail.com





== Changelog ==

1. Most recent update: Feb 19, 2018.
2. Version 1.5.1

Feb 19, 2018:
1) Added ability for multisite install's to create a plugin table for each site upon creation.
2) Added ability for multisite install's to delete the plugin table(s) for each site upon deletion.

Feb 4, 2018:
1) Optimize performance by forcing the PHP files that are needed only on the FE to be loaded only on the FE and same for BE PHP files. Doing this allowed code execution to be reduced from ~6 milliseconds to ~1 millisecond!
2) Cleaned up some code to reduce amount of code.
3) Tested compatibility with WP 4.9.2

Dec 22, 2017:
1) fixed small issue with setting the 'crossorigin' attribute.

Nov 17, 2017:
1) fixed bug preventing resource hints from appearing in the WP admin HTTP header

Nov 14, 2017:
1) Tested compatibility with WP 4.9
2) Fixed some issues with the resource hint output for the HTTP Header option
3) Fixed bug that threw an error when inserting hints
4) fixed bug that prevented plugin from being deleted properly
5) Changed how the "as" attribute was being set for preload hints.


Oct 7, 2017:
1) Improved code, changed some variable names
2) renamed class names to be consistent with WP coding standards.
3) added option to remove auto generated WP resource hints.
4) segmented each <form> to have its own method.
5) updated HTTP Header string to make it compatible with the most recent Chrome version and incorporate the 'as' attribute.

Sept 20:
1) fixed bugs due to WP 4.8.2 changes.

Sept 13:
1) improved code on class-GKTPP_Send_Entered_Hints.php
2) added two more screenshots of the before and after effects of implementing this plugin.

July 13:
1) added a form on plugin admin screen to contact author about feature requests or bug reporting;
    2) fixed minor issue with the preconnect JS script not firing;
    3) fixed minor UI issues/CSS;

July 6:
1) added ability to choose to add links to the Header or <head>;
   2) cleaned up UI;
   3) fixed bugs on find-external-domains.js/ improved functionality;
   4) removed option to select which pages/posts the links went to- this was unwanted feature and removing it improved performance
   5) condensed the info tab links into one;
   6) modified schema for gktpp_table; and removed the other table;
   7) improved security;


June 11:
1) tested compat with wp 4.8

Mar 27:
1) changed db table schema which caused problems for sites with lots of posts.

Dec 9:
1) Ensured compatibility with WP v 4.7

Nov 11:
1) added plugin icon and banner image. 2) added tip info

Nov 6:
1) updated screenshot image on wp plugin page.
2) a) added screenshot images on plugin page. b) removed 'console.log' in the front end JS file.

Nov 4:
1) Fixed some images that were not loading properly.

Nov 3:
1) initial commit.
