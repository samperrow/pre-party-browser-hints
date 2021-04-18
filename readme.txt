=== Pre* Party Resource Hints ===
Contributors: samperrow
Donate link: https://www.paypal.me/samperrow
Tags: W3C, DNS prefetch, prerender, preconnect, prefetch, preload, web perf, performance, speed, resource hints
Requires at least: 4.4
Tested up to: 5.7.1
Stable tag: 1.7.6.1
Requires PHP: 5.6.30
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Take advantage of browser resource hints and plug-and-play features to improve page load time.

== Installation Video ==

[youtube https://www.youtube.com/watch?v=Aha9E3AXvJQ]

== Description ==

This plugin allows users to automatically and easily embed resource hints to improve page load time. 

DNS prefetch, prerender, preconnect, prefetch, and preload are all supported. 

After installation, preconnect hints will automatically be created the next time your website is visited.

You have the choice to include these resource hints in the HTTP header or the website's <head>.

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

1. screenshot-1.png

2. screenshot-2.png

3. screenshot-3.png

4. screenshot-4.png

1. [Support Forum](https://wordpress.org/support/plugin/pre-party-browser-hints)

2. Send me an email at sam.perrow399@gmail.com
