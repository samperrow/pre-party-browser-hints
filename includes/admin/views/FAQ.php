<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FAQ {

	public $image_path = '';

	public function __construct() {
		$this->image_path = PPRH_REL_DIR;
	}


	public function markup() {
		?>
		<div class="pprh-content faq">
			<?php
//				$this->show_faq();
//				$this->upgrade_to_pro();
				$this->show_hint_info();
			?>
		</div>
		<?php
	}

	public function show_faq() {
		?>
		<div class="postbox">
			<div>
				<h3>Frequently Asked Questions</h3>

				<p>How do I add new resource hints?</p>
				<p>-Resource hints can be added manually on the main plugin page, or hints can be created automatically with the "Auto Preconnect", "Auto Prefetch", and "Auto Prerender" features, respecitvely.</p>

				<p>How can I update existing hints?</p>
				<p>-Hover over a hint you would like to update, and an "Edit" link will appear. Click that, make the desired changes, and click "Update".</p>

				<p>What are the crossorigin, "as", "type", and "media" attributes used for?</p>
				<ol>
					<li>The crossorigin attribute is used for some preload and preconnect hints, and is used to notify the browser that the specified resource originates from an external domain, so that the "SSL handshake" should be done.</li>
					<li>The "as" attribute specifies </li>
					<li>The "type" attribute specifies which MIME type a given resource is. For example, a CSS stylesheet has a MIME type of "text/css", and an HTML document is "text/html".</li>
					<li>The "media" attribute is only used for preload hints, and gives the user the ability to only load certain hints on specific devices or screen widths. For example, setting a "media" attribute to "max-width: 600px;" tells the browse to only load a resource on screens
					less than 600 pixels wide.</li>
				</ol>


				<p>What does the "Auto Preconnect" feature do, and how does it work?</p>
				<p>-This feature will automatically generate the proper preconnect hints.
				This works by using JavaScript code to retrieve all resources downloaded from external domains, and sending that back to the server via an ajax call.
				These hints will be stored until they are reset by the user. To reset theUpon installation, and after the "Reset" button is clicked (in the Auto Preconnect box in the Settings tab)</p>

				<p>What does the "Auto Prefetch" feature do, and how does it work?</p>
				<p>-</p>

				<p>My resource hints are not appearing on the front end, what is the problem?</p>
				<p>-</p>

				<p>What are the benefits of upgrading to the Pro version?</p>
				<ul>
					<li>Ability to add resource hints individually to specific pages/posts</li>
					<li>The "Auto Preconnect" feature will automatically generate the proper hints for each page/post.</li>
					<li>The Auto Prerender feature will generate one prerender hint (which download an entire web page in the background) for each page/post. </li>
				</ul>

				<p>What does the "Auto Prerender" feature do, and how does it work?</p>
				<p>-</p>

				add info about how to do "resets" etc..

			</div>
		</div>
		<?php
	}

	public function upgrade_to_pro() {
		?>
		<div class="postbox">
			<div>
				<h3>Upgrade to Pre* Party Pro!</h3>
				<p>I have been hard at work for over a year creating a dramatically improved version of this plugin. It has taken much longer than I anticipated, however the benefits of the upgraded version are extraordinary, and there is nothing comparable on the market. </p>
				<p>The main benefits are below:</p>

				<ol>
					<li>Ability to create unique resource hints on a post-specific basis.</li>
					<li><b>Automatic creation of prerender hints.</b> Integration with Google Analytics data, which allows for each page or posts' most commonly visited second page to be determined. From this information, a prerender hint will be automatically generated for that second page, which
						is only used on the previous page. For example, if 70% of your visitors visit the "/shop" page after they land on your home page, a prerender hint to "/shop" will be created and used only on your home page. The same will be done with every other page on your site!<br/>
						-Using prerender resource hints in this manner allows for a page to be loaded <i>instantly</i> when the user navigates towards that page.<br/>
						-For further elaboration, please read this article: <a href="https://ipullrank.com/how-i-sped-up-my-site-68-percent-with-one-line-of-code/">https://ipullrank.com/how-i-sped-up-my-site-68-percent-with-one-line-of-code/</a>
					</li>

					<li>There is a modal box on every page/post admin page which allows for simple and easy resource hint creation to specific pages/posts.</li>
					<li>Automatic creation of preconnect hints are unique to every page and post on your site. The "auto-preconnect" hints are created in the same manner, except that if one page has resources loaded from external domains which other pages do not, that page will have unique
						preconnect hints created and used ONLY on that specific page.</li>
				</ol>

				<p>** youtube video here of difference **</p>
			</div>
		</div>
		<?php
	}


	public function show_hint_info() {
		?>
		<div class="postbox"><div>
			<h3>Resource Hint Information</h3>
			<p><a href="<?php esc_attr_e( '#dns-prefetch-info' ); ?>">DNS Prefetch</a></p>
			<p><a href="<?php esc_attr_e( '#prefetch-info' ); ?>">Prefetch</a></p>
			<p><a href="<?php esc_attr_e( '#preconnect-info' ); ?>">Preconnect</a></p>
			<p><a href="<?php esc_attr_e( '#preload-info' ); ?>">Preload</a></p>
			<p><a href="<?php esc_attr_e( '#prerender-info' ); ?>">Prerender</a></p>

			<?php
			$this->show_dnsprefetch_info();
			$this->show_prefetch_info();
			$this->show_preconnect_info();
			$this->show_preload_info();
			$this->show_prerender_info();
			echo '</div></div>';
	}

	public function get_link( $img_name ) {
		echo esc_attr( $this->image_path );
		echo esc_attr( $img_name );
	}

	public function show_dnsprefetch_info() {
		?>
		<span id="dns-prefetch-info" style="display: block; height: 30px;"></span>
		<h3><?php esc_html_e( 'DNS Prefetch', 'pprh' ); ?></h3>
		<p><?php echo sprintf( 'DNS Prefetching allows browsers to proactively perform domain name resolution on resources hosted on %s which are requested by a website.', '<b>external domain names</b>' ); ?></p>
		<p><?php echo sprintf( 'In other words, when a website tells the browser to fetch a resource (CSS, JavaScript, images, etc) that is hosted on a different domain, time must be spent converting that domain name into its corresponding IP address. As the chart below shows, this step is normally done when the browser requests the resouce. Implementing DNS prefetching takes care of this step before the referenced resources are needed. This improves page load time by reducing latency, which is particularly noticeable on %s', '<b>mobile networks</b>' ); ?></p>
		<p><?php echo sprintf( 'For example, if your website is %s, and you have an image stored on your CDN at %s, the browser would have to perform a domain name resolution for %s. If you provide a DNS prefetch hint in the head of your web page, the browser knows to perform a DNS lookup ahead of schedule, instead of procrastinating until the last second.', '<i>http://example.com</i>', '<i>http://cdn.example.com</i>', '<i>http://cdn.example.com</i>' ); ?></p>
		<p><?php echo sprintf( 'The waterfall chart below represents the resources loaded by %s, which requires a Google font, an embedded YouTube video, and Google Analytics, without any type of DNS prefetching enabled:', '<a href="https://output.jsbin.com/keninux/1">a typical website</a>' ); ?></p>
		<img alt="Waterfall diagram before DNS Prefetching" class="pprh-admin-pic" width="1055" height="721" src="<?php $this->get_link( 'images/jsbin-no-dnsprefetch.jpg' ); ?>"/>
		<br>
		<p><?php esc_html_e( 'As you can see, the typical way DNS lookups occur is when each resource is requested by the browser. By inserting the below DNS prefetch hints at the top of the page, the browser will know to perform the DNS resolution before it is asked:' ); ?></p>

		<div class="pprh-code-block">
			<code>
				&lt;link rel="dns-prefetch" href="https://www.youtube.com&gt;<br/>
				&lt;link rel="dns-prefetch" href="https://static.jsbin.com&gt;<br/>
				&lt;link rel="dns-prefetch" href="https://s.ytimg.com&gt;<br/>
				&lt;link rel="dns-prefetch" href="https://i.ytimg.com&gt;<br/>
				&lt;link rel="dns-prefetch" href="https://fonts.gstatic.com&gt;<br/>
				&lt;link rel="dns-prefetch" href="https://static.doubleclick.net&gt;<br/>
				&lt;link rel="dns-prefetch" href="https://www.google.com&gt;<br/>
				&lt;link rel="dns-prefetch" href="https://googleads.g.doubleclick.net&gt;<br/>
				&lt;link rel="dns-prefetch" href="https://stats.g.doubleclick.net&gt;<br/>
				&lt;link rel="dns-prefetch" href="https://www.google-analytics.com&gt;<br/>
				&lt;link rel="dns-prefetch" href="https://weather.com&gt;
			</code>
		</div>

		<p><?php echo sprintf( 'The waterfall chart below clearly shows when the DNS resolution is made %s', '<a href="https://output.jsbin.com/keninux/2"> after taking advantage of DNS prefetching:</a>' ); ?></p>
		<br>
		<img alt="Waterfall diagram after DNS Prefetching" class="pprh-admin-pic" width="1056" height="496" src="<?php $this->get_link( 'images/jsbin-with-dnsprefetch.jpg' ); ?>"/>

		<p><?php esc_html_e( 'The DNS lookups occur before each resource is requested, preventing that step from slowing down resource delivery. This can improve the loading of each resource by hundreds of milliseconds or more, which can be particularly helpful for mobile users.' ); ?></p>
		<p><?php esc_html_e( 'More information:', 'pprh' ); ?></p>
		<ul>
			<li><a href="https://developers.google.com/speed/pagespeed/service/PreResolveDns">Google: PreResolve DNS</a></li>
			<li><a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Controlling_DNS_prefetching">Firefox: Controlling DNS prefetching</a></li>
		</ul>
		<hr>
		<?php
	}

	public function show_prefetch_info() {
		?>
		<span id="prefetch-info" style="display: block; height: 30px;"></span>
		<h3><?php esc_html_e( 'Prefetch' ); ?></h3>
		<p><?php echo sprintf( 'Prefetching allows %s (such as images, web fonts, CSS, JS files) to be loaded by the browser %s by the web page, by taking advantage of browser idle time.', '<b>individual resources</b>', '<b>before they are initiated</b>' ); ?></p>
		<p><?php esc_html_e( 'Link prefetching utilizes browser idle time to download resources that the user might require in the immediate future. When idle, the browser begins silently downloading the specified documents and stores them in the browser cache. When that resource is requested, it can be served immediately, instead of downloading the entire resource from scratch. Prefetching will allow an entire resource to be loaded, instead of simply resolving the DNS as in DNS prefetching.' ); ?></p>
		<p><?php echo sprintf( 'Prefetching resources can be especially useful for %s that will be loaded later in the page, or resources behind a redirect.', '<a href="https://developers.google.com/web/fundamentals/performance/critical-rendering-path/optimizing-critical-rendering-path">critical resources</a>' ); ?></p>
		<p><?php esc_html_e( 'For example, if you have a CSS, JavaScript, or Google font that will be requested by a page a user is likely to click on, prefetching this file allows the browser to download the file ahead of time, improving the future page load time.' ); ?></p>
		<p><?php esc_html_e( 'The waterfall chart below indicates when the browser loads a large image that has been prefetched:' ); ?></p>
		<img alt="Effect of Prefetching" class="pprh-admin-pic" width="1055" height="263" src="<?php $this->get_link( 'images/jsbin-prefetch.jpg' ); ?>"/>
		<p><?php esc_html_e( 'More information:' ); ?></p>

		<ul>
			<li><a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Link_prefetching_FAQ"><?php esc_html_e( 'Firefox: Link prefetching FAQ' ); ?></a></li>
			<li><a href="https://en.wikipedia.org/wiki/Link_prefetching"><?php esc_html_e( 'Wikipedia' ); ?></a></li>
			<li><a href="https://www.w3.org/TR/resource-hints/#dfn-prefetch"><?php esc_html_e( 'W3C Resource Hints: Prefetch' ); ?></a></li>
		</ul>
		<hr>
		<?php
	}

	public function show_prerender_info() {
		?>
		<span id="prerender-info" style="display: block; height: 30px;"></span>
		<h3><?php esc_html_e( 'Prerender' ); ?></h3>
		<p><?php echo sprintf( 'Prerendering allows %s to be loaded by the browser before any request is made by the user.', '<b>entire web pages</b>' ); ?></p>
		<p><?php echo sprintf( 'The prerender resource hint should used to notify the browser that a user will %s visit a web page soon to allow the browser to load the entire page before the user has chosen to navigate there. When the visitor does click to navigate to the page, it will be %s.', '<b>probably</b>', '<b>displayed immediately</b>' ); ?></p>
		<p><?php echo sprintf( 'Prerendering a URL loads all of the related CSS, JavaScript, images, etc, that are required by the prefetched page. These files will also %s. In some circumstances the request to prerender a page will be canceled by the user\'s browser for various reasons, such as limited bandwidth, or pop-up messages from the prerendered page.', '<b>be executed</b>' ); ?></p>
		<p><?php esc_html_e( 'Since prerendering can be resource intentize, this should only be used if you are confident a particular page will be visited. Frequently this would be the most popular link visitors navigate towards, such as FAQ, information, or service pages.' ); ?></p>
		<p><?php echo sprintf( '%s has the URL "https://www.youtube.com" prerender hint enabled as below:', '<a href="https://output.jsbin.com/fenamaq">This demo website</a>' ); ?><p>

		<div class="pprh-code-block">
			<code>
				&lt;link rel="prerender" href="https://www.youtube.com"&gt;
			</code>
		</div>

		<p><?php esc_html_e( 'The waterfall chart of the website is below:' ); ?></p>
		<img alt="Waterfall diagram before prerender" class="pprh-admin-pic" width="1055" height="647" src="<?php $this->get_link( 'images/jsbin-prefetch-example.jpg' ); ?>"/>
		<p><?php esc_html_e( 'As you can see, after the given browser has finished loading the host URL\'s typical contents, the browser will begin loading the prerendered page in the background. So when a user clicks to navigate to YouTube, that website will be rendered instantly!' ); ?></p>
		<p><?php echo sprintf( 'For Chrome users, check out %s to see if a page has been successfully prerendered or not. If the prerender attempt was successful, you will see the following at the top of that page, along with past your past prerender history:', '<a href="chrome://net-internals/#prerender">chrome://net-internals/#prerender</a>' ); ?></p>
		<img alt="Waterfall diagram with prerendered content" class="pprh-admin-pic" width="307" height="141" src="<?php $this->get_link( 'images/chrome-prerender-status.jpg' ); ?>"/>
		<p><?php esc_html_e( 'More information:' ); ?></p>

		<ul>
			<li><a href="https://www.stevesouders.com/blog/2013/11/07/prebrowsing/"><?php esc_html_e( 'Steve Souders "Prebrowsing"' ); ?></a></li>
			<li><a href="https://www.w3.org/TR/resource-hints/#dfn-prerender"><?php esc_html_e( 'W3C: Prerender' ); ?></a></li>
			<li><a href=""></a></li>
		</ul>
		<hr>
		<?php
	}

	public function show_preconnect_info() {
		?>
		<span id="preconnect-info" style="display: block; height: 30px;"></span>
		<h3><?php esc_html_e( 'Preconnect' ); ?></h3>
		<p><?php esc_html_e( 'Preconnecting allows the browser to establish a connection to an external domain before the request has been made.' ); ?></p>
		<p><?php esc_html_e( 'Typically the three steps involved in establishing a connection (DNS lookup, initial connection, and SSL negotiation) must be carried out when the resource is requested by the browser. Preconnecting allows these steps to be made proactively. This is similar to DNS prefetching, however preconnecting allows the initial connection and SSL negotiation to be resolved as well, instead of just the DNS lookup as is the case with DNS prefetching.' ); ?></p>
		<p><a href="https://output.jsbin.com/dudeger"><?php _e( 'This example page </a> loads a Google Font, embedded YouTube iframe video, and a Google Analytics tracker in the typical manner, without taking advantage of the preconnect resource hint. A summary of the normal requests this demo website makes is shown in the chart below, courtesy of <a href="https://www.webpagetest.org">webpagetest.org</a>', 'pprh' ); ?></p>
		<h3 style="text-align: center;"><?php esc_html_e( 'Typical HTTPS Socket Negotiation Periods:' ); ?></h3>
		<img alt="Waterfall diagram before preconnect hints enabled." class="pprh-admin-pic" width="1054" height="719" src="<?php $this->get_link( 'images/jsbin-no-preconnect.jpg' ); ?>"/>
		<br>
		<p><?php esc_html_e( 'Let\'s add the corresponding preconnect hints in the top of the web page and see what happens:' ); ?></p>

		<div class="pprh-code-block">
			<code>
				&lt;link rel="preconnect" href="https://www.youtube.com"&gt;<br/>
				&lt;link rel="preconnect" href="https://static.jsbin.com"&gt;<br/>
				&lt;link rel="preconnect" href="https://s.ytimg.com"&gt;<br/>
				&lt;link rel="preconnect" href="https://i.ytimg.com"&gt;<br/>
				&lt;link rel="preconnect" href="https://fonts.gstatic.com" crossorigin&gt;<br/>
				&lt;link rel="preconnect" href="https://static.doubleclick.net"&gt;<br/>
				&lt;link rel="preconnect" href="https://www.google.com"&gt;<br/>
				&lt;link rel="preconnect" href="https://googleads.g.doubleclick.net"&gt;<br/>
				&lt;link rel="preconnect" href="https://stats.g.doubleclick.net"&gt;<br/>
				&lt;link rel="preconnect" href="https://www.google-analytics.com"&gt;
			</code>
		</div>

		<h3 style="text-align: center;"><?php esc_html_e( 'HTTPS Socket Negotiation With Preconnect Enabled:' ); ?></h3>
		<img alt="Waterfall diagram after preconnect hints enabled." class="pprh-admin-pic" width="1056" height="721" src="<?php $this->get_link( 'images/jsbin-with-preconnect.jpg' ); ?>"/>
		<p><?php esc_html_e( 'As you can see, the three steps required to load resources from external domains occurs much earlier in the page request, reducing the need for these to be loaded earlier. The "crossorigin" attribute must be used when preconnecting to fonts, if this is left out only a DNS prefetch will be performed.' ); ?></p>
		<p><?php esc_html_e( 'Basically, preconnecting is DNS-prefetching, but also takes care of the initial connection and SSL negotiation, whereas DNS prefetching solely handles the DNS lookup.' ); ?></p>
		<p><?php esc_html_e( 'More information:' ); ?></p>
		<ul>
			<li><a href="https://www.w3.org/TR/resource-hints/#dfn-preconnect"><?php esc_html_e( 'W3C: Preconnect' ); ?></a></li>
			<li><a href="https://www.igvita.com/2015/08/17/eliminating-roundtrips-with-preconnect/"><?php esc_html_e( 'Eliminating Roundtrips with Preconnect, by Ilya Grigorik' ); ?></a></li>
			<li><a href=""></a></li>
		</ul>
		<hr>
		<?php
	}

	public function show_preload_info() {
		?>
		<span id="preload-info" style="display: block; height: 30px;"></span>
		<h3><?php esc_html_e( 'Preload' ); ?></h3>
		<p><?php echo sprintf( 'Preload %s proactively.', '<b>fetches one resource</b>' ); ?></p>
		<p><?php echo sprintf( 'This works in a similar manner to prefetching, however the preload hint is %s. Whereas prefetching resources is optional (to the browser), low priority, and used to load resources a visitor is likely to require in the future.', '<b>mandatory, high priority, used for current navigation, and non-render blocking</b>' ); ?></p>
		<p><?php esc_html_e( 'Let\'s see what the waterfall chart looks like after the following resource hints are placed into the top of a web page:' ); ?></p>
		<div class="pprh-code-block">
			<code>
				&lt;link rel="preload" href="https://fonts.googleapis.com/css?family=Merriweather" as="font" crossorigin&gt;<br/>
				&lt;link rel="preload" href="https://www.youtube.com/embed/kkwiQmGWK4c" as="document"&gt;<br/>
				&lt;link rel="preload" href="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js" as="script"&gt;
			</code>
		</div>

		<p><?php echo sprintf( 'The waterfall chart below, courtesy of %s, shows when preloading resources are loaded by the browser:', '<a href="https://www.webpagetest.org">webpagetest.org</a>' ); ?></p>
		<img alt="Waterfall diagram before preload hints enabled." class="pprh-admin-pic" width="1046" height="597" src="<?php $this->get_link( 'images/jsbin-preload.jpg' ); ?>">
		<h1><?php esc_html_e( 'Caution!' ); ?></h1>
		<p><?php echo sprintf( 'Although the resources above may have been preloaded correctly, %s. In order to utilize preloaded resources, %s. For example, if a CSS file were preloaded, you would include a link element with an href set to the CSS file that has been preloaded. Same goes for JS files, and all others.', '<b>they cannot be used on the web page yet</b>', '<b>you must reference them within your web page</b>' ); ?></p>
		<p><?php esc_html_e( 'More information:' ); ?></p>
		<ul>
			<li><a href="https://w3c.github.io/preload/"><?php esc_html_e( 'W3C: Preload' ); ?></a></li>
			<li><a href="https://developers.google.com/web/updates/2016/03/link-rel-preload"><?php esc_html_e( 'Prioritizing Your Resources with link rel="preload"' ); ?></a></li>
		</ul>
		<hr>
		<?php
	}

}
