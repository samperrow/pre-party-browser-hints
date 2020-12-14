<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Hint_Info {

	public $image_path = '';

	public function __construct() {
		$this->image_path = PPRH_REL_DIR;
		$this->resource_hint_nav();
	}

	public function resource_hint_nav() {
		?>
		<div id="pprh-info" class="pprh-content">
			<h2><?php esc_html_e( 'Resource Hint Information', 'pprh' ); ?></h2>

			<p><a href="<?php esc_attr_e( '#dns-prefetch' ); ?>">DNS Prefetch</a></p>
			<p><a href="<?php esc_attr_e( '#prefetch' ); ?>">Prefetch</a></p>
			<p><a href="<?php esc_attr_e( '#preconnect' ); ?>">Preconnect</a></p>
			<p><a href="<?php esc_attr_e( '#preload' ); ?>">Preload</a></p>
            <p><a href="<?php esc_attr_e( '#prerender' ); ?>">Prerender</a></p>

			<?php
			$this->show_dnsprefetch_info();
			$this->show_prefetch_info();
			$this->show_preconnect_info();
			$this->show_preload_info();
            $this->show_prerender_info();
			echo '</div>';
	}

	public function get_link( $img_name ) {
		echo esc_attr( $this->image_path );
		echo esc_attr( $img_name );
	}

	public function show_dnsprefetch_info() {
		?>
		<span id="dns-prefetch" style="display: block; height: 30px;"></span>
		<h2><?php esc_html_e( 'DNS Prefetch', 'pprh' ); ?></h2>
		<p><?php echo sprintf( 'DNS Prefetching allows browsers to proactively perform domain name resolution on resources hosted on %s which are requested by a website.', '<b>external domain names</b>' ); ?></p>

		<p><?php echo sprintf( 'In other words, when a website tells the browser to fetch a resource (CSS, JavaScript, images, etc) that is hosted on a different domain, time must be spent converting that domain name into its corresponding IP address. As the chart below shows, this step is normally done when the browser requests the resouce. Implementing DNS prefetching takes care of this step before the referenced resources are needed. This improves page load time by reducing latency, which is particularly noticeable on %s', '<b>mobile networks</b>' ); ?></p>

		<p><?php echo sprintf( 'For example, if your website is %s, and you have an image stored on your CDN at %s, the browser would have to perform a domain name resolution for %s. If you provide a DNS prefetch hint in the head of your web page, the browser knows to perform a DNS lookup ahead of schedule, instead of procrastinating until the last second.', '<i>http://example.com</i>', '<i>http://cdn.example.com</i>', '<i>http://cdn.example.com</i>' ); ?></p>

		<p><?php echo sprintf( 'The waterfall chart below represents the resources loaded by %s, which requires a Google font, an embedded YouTube video, and Google Analytics, without any type of DNS prefetching enabled:', '<a href="https://output.jsbin.com/keninux/1">a typical website</a>' ); ?></p>

		<img alt="Waterfall diagram before DNS Prefetching" class="pprh-admin-pic" width="1055" height="721" src="<?php $this->get_link( 'images/jsbin-no-dnsprefetch.jpg' ); ?>"/>

		<br>

		<p><?php esc_html_e( 'As you can see, the typical way DNS lookups occur is when each resource is requested by the browser. By inserting the below DNS prefetch hints at the top of the page, the browser will know to perform the DNS resolution before it is asked:' ); ?></p>

		<div class="pprh-code-block">
			<script src="<?php $this->get_link( 'js/markup/dns-prefetch.js' ); ?>"></script>
		</div>

		<p><?php echo sprintf( 'The waterfall chart below clearly shows when the DNS resolution is made %s', '<a href="https://output.jsbin.com/keninux/2"> after taking advantage of DNS prefetching:</a>' ); ?></p>

		<br>

		<img alt="Waterfall diagram after DNS Prefetching" class="pprh-admin-pic" width="1056" height="496" src="<?php $this->get_link( 'images/jsbin-with-dnsprefetch.jpg' ); ?>"/>

		<p><?php esc_html_e( 'The DNS lookups occur before each resource is requested, preventing that step from slowing down resource delivery. This can improve the loading of each resource by hundreds of milliseconds or more, which can be particularly helpful for mobile users.' ); ?></p>

		<p><?php esc_html_e( 'More information:' ); ?></p>
		<ul>
			<li><a href="https://developers.google.com/speed/pagespeed/service/PreResolveDns">Google: PreResolve DNS</a></li>
			<li><a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Controlling_DNS_prefetching">Firefox: Controlling DNS prefetching</a></li>
		</ul>
		<hr>
		<?php
	}

	public function show_prefetch_info() {
		?>
		<span id="prefetch" style="display: block; height: 30px;"></span>
		<h2><?php esc_html_e( 'Prefetch' ); ?></h2>

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
		<span id="prerender" style="display: block; height: 30px;"></span>
		<h2><?php esc_html_e( 'Prerender' ); ?></h2>

		<p><?php echo sprintf( 'Prerendering allows %s to be loaded by the browser before any request is made by the user.', '<b>entire web pages</b>' ); ?></p>

		<p><?php echo sprintf( 'The prerender resource hint should used to notify the browser that a user will %s visit a web page soon to allow the browser to load the entire page before the user has chosen to navigate there. When the visitor does click to navigate to the page, it will be %s.', '<b>probably</b>', '<b>displayed immediately</b>' ); ?></p>

		<p><?php echo sprintf( 'Prerendering a URL loads all of the related CSS, JavaScript, images, etc, that are required by the prefetched page. These files will also %s. In some circumstances the request to prerender a page will be canceled by the user\'s browser for various reasons, such as limited bandwidth, or pop-up messages from the prerendered page.', '<b>be executed</b>' ); ?></p>

		<p><?php esc_html_e( 'Since prerendering can be resource intentize, this should only be used if you are confident a particular page will be visited. Frequently this would be the most popular link visitors navigate towards, such as FAQ, information, or service pages.' ); ?></p>

		<p><?php echo sprintf( '%s has the URL "https://www.youtube.com" prerender hint enabled as below:', '<a href="https://output.jsbin.com/fenamaq">This demo website</a>' ); ?><p>

		<div class="pprh-code-block">
			<script src="<?php $this->get_link( 'js/markup/prerender.js' ); ?>"></script>
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
		<span id="preconnect" style="display: block; height: 30px;"></span>
		<h2><?php esc_html_e( 'Preconnect' ); ?></h2>

		<p><?php esc_html_e( 'Preconnecting allows the browser to establish a connection to an external domain before the request has been made.' ); ?></p>

		<p><?php esc_html_e( 'Typically the three steps involved in establishing a connection (DNS lookup, initial connection, and SSL negotiation) must be carried out when the resource is requested by the browser. Preconnecting allows these steps to be made proactively. This is similar to DNS prefetching, however preconnecting allows the initial connection and SSL negotiation to be resolved as well, instead of just the DNS lookup as is the case with DNS prefetching.' ); ?></p>

		<p><a href="https://output.jsbin.com/dudeger"><?php _e( 'This example page </a> loads a Google Font, embedded YouTube iframe video, and a Google Analytics tracker in the typical manner, without taking advantage of the preconnect resource hint. A summary of the normal requests this demo website makes is shown in the chart below, courtesy of <a href="https://www.webpagetest.org">webpagetest.org</a>', 'pprh' ); ?></p>

		<h3 style="text-align: center;"><?php esc_html_e( 'Typical HTTPS Socket Negotiation Periods:' ); ?></h3>
		<img alt="Waterfall diagram before preconnect hints enabled." class="pprh-admin-pic" width="1054" height="719" src="<?php $this->get_link( 'images/jsbin-no-preconnect.jpg' ); ?>"/>
		<br>

		<p><?php esc_html_e( 'Let\'s add the corresponding preconnect hints in the top of the web page and see what happens:' ); ?></p>

		<div class="pprh-code-block">
			<script src="<?php $this->get_link( 'js/markup/preconnect.js' ); ?>"></script>
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
		<span id="preload" style="display: block; height: 30px;"></span>
		<h2><?php esc_html_e( 'Preload' ); ?></h2>

		<p><?php echo sprintf( 'Preload %s proactively.', '<b>fetches one resource</b>' ); ?></p>

		<p><?php echo sprintf( 'This works in a similar manner to prefetching, however the preload hint is %s. Whereas prefetching resources is optional (to the browser), low priority, and used to load resources a visitor is likely to require in the future.', '<b>mandatory, high priority, used for current navigation, and non-render blocking</b>' ); ?></p>

		<p><?php esc_html_e( 'Let\'s see what the waterfall chart looks like after the following resource hints are placed into the top of a web page:' ); ?></p>

		<div class="pprh-code-block">
			<script src="<?php $this->get_link( 'js/markup/preload.js' ); ?>"></script>
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

//if ( is_admin() ) {
//	new Hint_Info();
//}
