=== Pre* Party Resource Hints ===
Contributors: samperrow
Donate link: https://www.paypal.me/samperrow
Tags: W3C, DNS prefetch, prerender, preconnect, prefetch, preload, web perf, performance, speed, resource hints
Requires at least: 4.4
Tested up to: 5.2.3
Stable tag: 1.6.41
Requires PHP: 5.3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Take advantage of browser resource hints and plug-and-play features to improve page load time.

== Installation Video ==

[youtube https://www.youtube.com/watch?v=Aha9E3AXvJQ]

== Description ==

This plugin allows users to automatically and easily embed resource hints to improve page load time. 

DNS prefetch, prerender, preconnect, prefetch, and preload are all supported. 

After installation, preconnect hints will automatically be created the next time your website is visited.

Resource hints will be added in your website's <head>. (The option to insert them in the HTTP header has been removed, in order for this plugin to be compatible with future changes I have planned.)

== Installation ==

1. Upload the entire `pre-party-browser-hints` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

How can I determine which URL's to enter?
Go to https://www.webpagetest.org, enter your website's URL, and click on the "waterfall" chart that appears.
a) For all resources that are loaded from external websites or domain names, I recommend inserting a preconnect link for that domain name (Preconnect is more powerful than DNS Prefetch, as it takes care of the DNS lookup, initial connection, and SSL negotiation).
b) If you have a very popular link on your site that you are confident a user would navigate towards, I recommend inserting a link for that URL with the "prerender" option set.
c) Prefetch and preload work similarly, which allows single resources to be loaded before they are requested by the user. Use this for loading images, videos, JavaScript files, etc.

How does the plugin automatically add preconnect hints?
After installing the plugin (or clicking the 'Reset Links' button), a JavaScript file will be loaded on your website (after a page is loaded) which captures the resources loaded from external domains and sends them via Ajax to your database six seconds after the page has been loaded. This script fires 6 seconds after the website has been loaded, to allow for all resources to be completely loaded.

Many websites have cache plugins that can interfere with this functionality. I have configured the JavaScript file to only function when it is in its original folder (not been merged/combined). This is to prevent it from triggering after every page load. To get this funtionality working properly, ensure that this file (/wp-content/plugins/pre-party-browser-hints/js/find-external-domains.js) is not effected by any cache plugins.

DNS Prefetch:
For all HTTP requests loaded from external sources on a page web, add the domain name of each in the "Add New Resource Hint" form, select the option for "DNS Prefetch".

Prefetch:
Insert an absolute URL for a CSS, JavaScript, image, etc, that is hosted on an external domain, and select the option "Prefetch".

Prerender:
Insert a valid URL that a visitor to your website is likely to visit, and select the option "Prerender". The URL you entered will now be loaded by the browser after all requests have been loaded on the page.

Preconnect:
For all HTTP requests loaded from external sources on a page web, add the domain name of each in the "Add New Resource Hint" form, select the option for "Preconnect". Preconnect is more powerful than DNS Prefetch, because it resolves three connections instead of one.

If you would like to have preconnect links automatically set, simply install this plugin and allow it do it the magic for you. If you would like this option disabled, just select the option to have these disabled near the bottom of the main plugin screen.

Preload:
Insert an absolute URL for a CSS, JavaScript, image, etc, and select the option "Preload".

To activate the preloaded resource, you must call that file in HTML as you would any file. For example, if you preload 'jquery.js', you must insert a script tag with a src attribute set to 'jquery.js'. Otherwise the preloaded link will be saved in the browser, but not activated in the DOM.


== Screenshots ==

1. Insert new resource hints

2. Plugin settings

3. Detailed information about all five resource hints

4. Before plugin installation

5. After preconnect hints created for externally hosted resources

6. HTTPS socket negotiation with preconnect hints enabled

7. Default HTTPS socket negotation time period


== Changelog ==

1. Most recent update: October 5, 2019.
2. Version 1.6.41

October 5, 2019:
1) Entered URL's will no longer be converted to lower case. This is because some sites have case-sensitive URI's.

September 28, 2019:
1) Fixed bug with preconnect-ajax hints.
2) fixed issue with upgrading db table from v 1.5.8

September 15, 2019:
1) Dramatically reconfigured plugin. Version 1.6.0 is basically a complete rewrite of all code used. It is more organized, concise, efficient, easy to understand, simple, and user friendly than previously.
2) The option to add hints in the HTTP header has been removed. This is necessary because that feature will not be compatible with future versions of the plugin.
3) Moved all plugin settings to the "Settings" tab for easier navigation.
4) Added ability for users to set the crossorigin, as, and type attributes for hints.
5) Transformed "Contact" box into a pop up modal.

February 16, 2019:
1) Ensured compatibility with WP 5.1
2) Removed legacy file
3) Made minor syntax changes.

October 19, 2018:
1) Tested compatibility with WP 5.0
2) Tested up to PHP 7.3.0.
3) Updated github URL.

August 9, 2018:
1) Added a warning indicator on the admin page to let users know that they should not load hints in the HTTP header if they have a cache plugin active, and to notify them to refresh their cache.
2) Removed the need for jQuery to be loaded dynamically in the "find-external-domains.js" script, and optimized the code. 100% vanilla JS now!
3) In the "Request New Feature or Report a Bug" feature, I added some info that gets sent to me to better diagnose potential plugin problems (WP version, PHP version, URL).

June 29, 2018:
1) updated a change in my GitHub username onto file paths that display HTML links on the info tab.
2) Created a plugin installation video and put it on the readme


April 27, 2018:
1) fixed bug preventing users from deleting or updating resource hint statuses.
2) fixed bug some users could notice upon installation/reactivation in the admin.php file. 

April 14, 2018:
1) Fixed some UI issues on admin page (URL input field not taking up max space, jQuery sometimes not loading).
2) removed check to see if JS preconnect hint array had items before sending it to db (if it was empty, this caused the script to keep firing over and over).
3) changed some text in description.
4) cleaned up old variable's on class-PPRH-display-items.php file.

April 2, 2018:
1) removed unneccessary call on admin side for creating table.
2) fixed some bugs relating to how URL's are inserted and how the crossorigin attribute is created.
3) fixed bug in creating the header string.
4) fixed bug relating to how user options were saved on admin side.

March 31, 2018:
1) improved automatic discovery of external domains by using the Resource Timing API.
2) cleaned up UI by consolidating form elements and save buttons into one.
3) improved ability for preload hints 'as' attribute to be determined when user inputs data.
4) improved sanitization and overall URL entry process.
5) cleaned up the code which governs how hints are delivered from the db to the browser.
6) 'crossorigin' attribute is now determined on the back end.
7) added more detailed information to the Preload information section.
8) modified db table schema- added 5 columns: 'as_attr', 'type_attr', 'crossorigin', 'header_string', and 'head_string' for those respective attributes which browsers are getting more particular about. The last two columns are helpful for storing the specified links in the db, and delivering them very quickly to the browser.
9) the improvements above have been able to bring total PHP execution time on the front end down to around 0.07-0.1 milliseconds (that's 0.00007 seconds). Essentially I am shifting more of the calculations/computations to the back end when the user inputs data rather than the front end.

Feb 26, 2018:
1) modified call order of admin.php functions
2) fixed SQL bug that occurred while deleting previous ajax hints

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

Sept 20, 2017:
1) fixed bugs due to WP 4.8.2 changes.

Sept 13, 2017:
1) improved code on class-PPRH_Send_Entered_Hints.php
2) added two more screenshots of the before and after effects of implementing this plugin.

July 13, 2017:
1) added a form on plugin admin screen to contact author about feature requests or bug reporting;
2) fixed minor issue with the preconnect JS script not firing;
3) fixed minor UI issues/CSS;

July 6, 2017:
1) added ability to choose to add links to the Header or <head>;
2) cleaned up UI;
3) fixed bugs on find-external-domains.js/ improved functionality;
4) removed option to select which pages/posts the links went to- this was unwanted feature and removing it improved performance
5) condensed the info tab links into one;
6) modified schema for PPRH_table; and removed the other table;
7) improved security;

June 11, 2017:
1) tested compat with wp 4.8

Mar 27, 2017:
1) changed db table schema which caused problems for sites with lots of posts.

Dec 9, 2016:
1) Ensured compatibility with WP v 4.7

Nov 11, 2016:
1) added plugin icon and banner image. 2) added tip info

Nov 6, 2016:
1) updated screenshot image on wp plugin page.
2) a) added screenshot images on plugin page. b) removed 'console.log' in the front end JS file.

Nov 4, 2016:
1) Fixed some images that were not loading properly.

Nov 3, 2016:
1) initial commit.

== Upgrade Notice ==

= 1.5.5 =
Ensured compatibility with WP 5.1, minor syntax updates. See Changelog section.