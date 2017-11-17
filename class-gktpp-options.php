<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GKTPP_Options {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'settings_page_init' ) );
		add_filter( 'set-screen-option', array( $this, 'apply_wp_screen_options' ), 1, 3 );
	}

	public function settings_page_init() {
		$settings_page = add_menu_page(
			' Pre* Party Settings',
			'Pre* Party',
			'manage_options',
			'gktpp-plugin-settings',
			array( $this, 'settings_page' ),
			plugins_url( '/pre-party-browser-hints/images/lightning.png' )
		);
		add_action( "load-{$settings_page}", array( $this, 'save_plugin_tabs' ) );
		add_action( "load-{$settings_page}", array( $this, 'screen_option' ) );
	}

	public function save_plugin_tabs() {
		if ( isset( $_POST['gktpp-settings-submit'] ) ) {
			check_admin_referer( 'gkt_preP-settings-page' );

			if ( ( '' !== $_POST['url'] ) && ( isset( $_POST['hint_type'] ) ) ) {
				GKTPP_Insert_To_DB::insert_data_to_db();
				$url_parameters = isset( $_GET['tab'] ) ? 'updated=true&tab=' . $_GET['tab'] : 'updated=true';
				wp_safe_redirect( admin_url( 'admin.php?page=gktpp-plugin-settings&' . $url_parameters ) );
				exit();
			}
	    }
	}

	public function apply_wp_screen_options( $status, $option, $value ) {
		if ( 'gktpp_screen_options' === $option ) {
			return $value;
		}

		return $status;
	}

	public function admin_tabs( $current = 'insert-urls' ) {
		$tabs = array(
			'insert-urls' => 'Insert URLs',
			'info' => 'Information',
			);

		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $tabs as $tab => $name ) {
			$class = ( $tab === $current ) ? ' nav-tab-active' : '';
			echo "<a class='nav-tab$class' href='?page=gktpp-plugin-settings&tab=$tab'>" . esc_html( $name ) . "</a>";
		}
		echo '</h2>';
	}

	public function settings_page() {
		if ( ! is_admin() ) {
			exit;
		}
		global $pagenow;
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Pre* Party Plugin Settings', 'gktpp' ); ?></h2>

			<form method="post" action="<?php admin_url( 'admin.php?page=gktpp-plugin-settings' ); ?>">
				<?php ( isset( $_GET['tab'] ) ) ? $this -> admin_tabs( $_GET['tab'] ) : $this -> admin_tabs( 'insert-urls' ); ?>
			</form>
				<?php
				if ( 'admin.php' === $pagenow && 'gktpp-plugin-settings' === $_GET['page'] ) {
					( isset( $_GET['tab'] ) ) ? $tab = $_GET['tab'] : $tab = 'insert-urls';

					switch ( $tab ) {
						case 'insert-urls' :
							$callPrepareItems = new GKTPP_Table();
							$callPrepareItems->prepare_items();
						break;

						case 'info':
							self::resource_hint_nav();
							self::show_dnsprefetch_info();
							self::show_prefetch_info();
							self::show_prerender_info();
							self::show_preconnect_info();
							self::show_preload_info();
						break;

						default:
							$callPrepareItems = new GKTPP_Table();
							$callPrepareItems->prepare_items();
						break;
					}
				}
				?>
		</div>
	<?php }

	public function screen_option() {
		$option = 'per_page';
		$args = array(
			'label'   => 'URLs',
			'default' => 10,
			'option'  => 'gktpp_screen_options',
		);

		add_screen_option( $option, $args );

		$this->resource_obj = new GKTPP_Table();
	}

	public static function url_updated( $status ) { ?>
		<div class="inline notice notice-success is-dismissible">
			<p><?php esc_html_e( "Resource hints {$status} successfully." ); ?></p>
		</div>
	<?php }

	private function resource_hint_nav() { ?>
		<p><a href="<?php echo esc_html( '/wp-admin/admin.php?page=gktpp-plugin-settings&tab=info#gktpp-dns-prefetch'); ?>">DNS Prefetch</a></p>
		<p><a href="<?php echo esc_html( '/wp-admin/admin.php?page=gktpp-plugin-settings&tab=info#gktpp-prefetch' ); ?>">Prefetch</a></p>
		<p><a href="<?php echo esc_html( '/wp-admin/admin.php?page=gktpp-plugin-settings&tab=info#gktpp-prerender' ); ?>">Prerender</a></p>
		<p><a href="<?php echo esc_html( '/wp-admin/admin.php?page=gktpp-plugin-settings&tab=info#gktpp-preconnect' ); ?>">Preconnect</a></p>
		<p><a href="<?php echo esc_html( '/wp-admin/admin.php?page=gktpp-plugin-settings&tab=info#gktpp-preload' ); ?>">Preload</a></p>

	<?php }

	private function show_dnsprefetch_info() {
		?>
		<span id="gktpp-dns-prefetch" style="display: block; height: 30px;"></span>
		<h2><?php echo esc_html_e( 'DNS Prefetch', 'gktpp' ); ?></h2>
		<p><?php echo __( 'DNS Prefetching allows browsers to proactively perform domain name resolution on resources hosted on <b>external domain names</b> which are requested by a website.' ); ?></p>

		<p><?php echo __( 'In other words, when a website tells the browser to fetch a resource (CSS, JavaScript, images, etc) that is hosted on a different domain, time must be spent converting that domain name into its corresponding IP address. As the chart below shows, this step is normally done when the browser requests the resouce. Implementing DNS prefetching takes care of this step before the referenced resources are needed. This improves page load time by reducing latency, which is particularly noticeable on <b>mobile networks</b>' ); ?></p>

		<p><?php echo __( 'For example, if your website is <i>http://example.com</i>, and you have an image stored on your CDN at <i>http://cdn.example.com</i>, the browser would have to perform a domain name resolution for <i>http://cdn.example.com</i>. If you provide a DNS prefetch hint in the head of your web page, the browser knows to perform a DNS lookup ahead of schedule, instead of procrastinating until the last second.' ); ?></p>

		<p><?php echo __( 'The waterfall chart below represents the resources loaded by <a href="https://output.jsbin.com/keninux/1">a typical website</a>, which requires a Google font, an embedded YouTube video, and Google Analytics, without any type of DNS prefetching enabled:' ); ?></p>

		<img class="gktpp-admin-pic" width="1055" height="721" src="<?php echo plugins_url( '/pre-party-browser-hints/images/jsbin-no-dnsprefetch.jpg' ); ?>"/>

		<br>

		<p><?php echo __( 'As you can see, the typical way DNS lookups occur is when each resource is requested by the browser. By inserting the below DNS prefetch hints at the top of the page, the browser will know to perform the DNS resolution before it is asked:' ); ?></p>

		<div class="gktpp-code-block">
			<script src="https://gist.github.com/sarcastasaur/9b71fa3be44258a9af670f799effaad6.js"></script>
		</div>

		<p><?php echo __( 'The waterfall chart below clearly shows when the DNS resolution is made <a href="https://output.jsbin.com/keninux/2">after taking advantage of DNS prefetching:' ); ?></a></p>

		<br>

		<img class="gktpp-admin-pic" width="1056" height="496" src="<?php echo plugins_url( '/pre-party-browser-hints/images/jsbin-with-dnsprefetch.jpg' ); ?>"/>

		<p><?php echo __( 'The DNS lookups occur before each resource is requested, preventing that step from slowing down resource delivery. This can improve the loading of each resource by hundreds of milliseconds or more, which can be particularly helpful for mobile users.' ); ?></p>

		<p><?php echo __( 'More information:' ); ?></p>
		<ul>
			<li><a href="https://developers.google.com/speed/pagespeed/service/PreResolveDns">Google: PreResolve DNS</a></li>
			<li><a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Controlling_DNS_prefetching">Firefox: Controlling DNS prefetching</a></li>
		</ul>
		<hr>
		<?php
	}

	private function show_prefetch_info() {
		?>
		<span id="gktpp-prefetch" style="display: block; height: 30px;"></span>
		<h2><?php echo __( 'Prefetch' ); ?></h2>

		<p><?php echo __( 'Prefetching allows <b>individual resources</b> (such as images, web fonts, CSS, JS files) to be loaded by the browser <b>before they are initiated</b> by the web page, by taking advantage of browser idle time.' ); ?></p>

		<p><?php echo __( 'Link prefetching utilizes browser idle time to download resources that the user might require in the immediate future. When idle, the browser begins silently downloading the specified documents and stores them in the browser cache. When that resource is requested, it can be served immediately, instead of downloading the entire resource from scratch. Prefetching will allow an entire resource to be loaded, instead of simply resolving the DNS as in DNS prefetching.' ); ?></p>

		<p><?php echo __( 'Prefetching resources can be especially useful for <a href="https://developers.google.com/web/fundamentals/performance/critical-rendering-path/optimizing-critical-rendering-path">critical resources</a> that will be loaded later in the page, or resources behind a redirect.' ); ?></p>

		<p><?php echo __( 'For example, if you have a CSS, JavaScript, or Google font that will be requested by a page a user is likely to click on, prefetching this file allows the browser to download the file ahead of time, improving the future page load time.' ); ?></p>

		<p><?php echo __( 'The waterfall chart below indicates when the browser loads a large image that has been prefetched:' ); ?></p>

		<img class="gktpp-admin-pic" width="1055" height="263" src="<?php echo plugins_url( '/pre-party-browser-hints/images/jsbin-prefetch.jpg' ); ?>"/>

		<p><?php echo __( 'More information:' ); ?></p>

		<ul>
			<li><a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Link_prefetching_FAQ"><?php echo __( 'Firefox: Link prefetching FAQ' ); ?></a></li>
			<li><a href="https://en.wikipedia.org/wiki/Link_prefetching"><?php echo __( 'Wikipedia' ); ?></a></li>
			<li><a href="https://www.w3.org/TR/resource-hints/#dfn-prefetch"><?php echo __( 'W3C Resource Hints: Prefetch' ); ?></a></li>
		</ul>
		<hr>
		<?php
	}

	private function show_prerender_info() {
		?>
		<span id="gktpp-prerender" style="display: block; height: 30px;"></span>
		<h2><?php echo __( 'Prerender' ); ?></h2>

		<p><?php echo __( 'Prerendering allows <b>entire web pages</b> to be loaded by the browser before any request is made by the user.' ); ?></p>

		<p><?php echo __( 'The prerender resource hint should used to notify the browser that a user will <b>probably</b> visit a web page soon to allow the browser to load the entire page before the user has chosen to navigate there. When the visitor does click to navigate to the page, it will be <b>displayed immediately</b>.' ); ?></p>

		<p><?php echo __( 'Prerendering a URL loads all of the related CSS, JavaScript, images, etc, that are required by the prefetched page. These files will also <b>be executed</b>. In some circumstances the request to prerender a page will be canceled by the user\'s browser for various reasons, such as limited bandwidth, or pop-up messages from the prerendered page.' ); ?></p>

		<p><?php echo __( 'Since prerendering can be resource intentize, this should only be used if you are confident a particular page will be visited. Frequently this would be the most popular link visitors navigate towards, such as FAQ, information, or service pages.' ); ?></p>

		<p><?php echo __( '<a href="https://output.jsbin.com/fenamaq">This demo website</a> has the URL "https://www.youtube.com" prerender hint enabled as below:' ); ?><p>

		<div class="gktpp-code-block">
			<script src="https://gist.github.com/sarcastasaur/0b4161665b29262099f5fce295660708.js"></script>
		</div>

		<p><?php echo __( 'The waterfall chart of the website is below:' ); ?></p>

		<img class="gktpp-admin-pic" width="1055" height="647" src="<?php echo plugins_url( '/pre-party-browser-hints/images/jsbin-prefetch-example.jpg' ); ?>"/>

		<p><?php echo __( 'As you can see, after the given browser has finished loading the host URL\'s typical contents, the browser will begin loading the prerendered page in the background. So when a user clicks to navigate to YouTube, that website will be rendered instantly!' ); ?></p>

		<p><?php echo __( 'For Chrome users, check out <a href="chrome://net-internals/#prerender">chrome://net-internals/#prerender</a> to see if a page has been successfully prerendered or not. If the prerender attempt was successful, you will see the following at the top of that page, along with past your past prerender history:' ); ?></p>

		<img class="gktpp-admin-pic" width="307" height="141" src="<?php echo plugins_url( '/pre-party-browser-hints/images/chrome-prerender-status.jpg' ); ?>"/>

		<p><?php echo __( 'More information:' ); ?></p>

		<ul>
			<li><a href="https://www.stevesouders.com/blog/2013/11/07/prebrowsing/"><?php echo __( 'Steve Souders "Prebrowsing"' ); ?></a></li>
			<li><a href="https://www.w3.org/TR/resource-hints/#dfn-prerender"><?php echo __( 'W3C: Prerender' ); ?></a></li>
			<li><a href=""></a></li>
		</ul>
		<hr>
		<?php
	}

	private function show_preconnect_info() {
		?>
		<span id="gktpp-preconnect" style="display: block; height: 30px;"></span>
		<h2><?php echo __( 'Preconnect' ); ?></h2>

		<p><?php echo __( 'Preconnecting allows the browser to establish a connection to an external domain before the request has been made.' ); ?></p>

		<p><?php echo __( 'Typically the three steps involved in establishing a connection (DNS lookup, initial connection, and SSL negotiation) must be carried out when the resource is requested by the browser. Preconnecting allows these steps to be made proactively. This is similar to DNS prefetching, however preconnecting allows the initial connection and SSL negotiation to be resolved as well, instead of just the DNS lookup as is the case with DNS prefetching.' ); ?></p>


		<p><a href="https://output.jsbin.com/dudeger"><?php echo __( 'This example page</a> loads a Google Font, embedded YouTube iframe video, and a Google Analytics tracker in the typical manner, without taking advantage of the preconnect resource hint. A summary of the normal requests this demo website makes is shown in the chart below, courtesy of <a href="https://www.webpagetest.org">webpagetest.org</a>' ); ?></p>

		<h3 style="text-align: center;"><?php echo __( 'Typical HTTPS Socket Negotiation Periods:' ); ?></h3>
		<img class="gktpp-admin-pic" width="1054" height="719" src="<?php echo plugins_url( '/pre-party-browser-hints/images/jsbin-no-preconnect.jpg' ); ?>"/>
		<br>

		<p><?php echo __( 'Let\'s add the corresponding preconnect hints in the top of the web page and see what happens:' ); ?></p>

		<div class="gktpp-code-block">
			<script src="https://gist.github.com/sarcastasaur/3329b4d00bd708015ad8b84a6d8fba9d.js"></script>
		</div>

		<h3 style="text-align: center;"><?php echo __( 'HTTPS Socket Negotiation With Preconnect Enabled:' ); ?></h3>
		<img class="gktpp-admin-pic" width="1056" height="721" src="<?php echo plugins_url( '/pre-party-browser-hints/images/jsbin-with-preconnect.jpg' ); ?>"/>

		<p><?php echo __( 'As you can see, the three steps required to load resources from external domains occurs much earlier in the page request, reducing the need for these to be loaded earlier. The "crossorigin" attribute must be used when preconnecting to fonts, if this is left out only a DNS prefetch will be performed.' ); ?></p>

		<p><?php echo __( 'Basically, preconnecting is DNS-prefetching, but also takes care of the initial connection and SSL negotiation, whereas DNS prefetching solely handles the DNS lookup.' ); ?></p>


		<p><?php echo __( 'More information:' ); ?></p>
		<ul>
			<li><a href="https://www.w3.org/TR/resource-hints/#dfn-preconnect"><?php echo __( 'W3C: Preconnect' ); ?></a></li>
			<li><a href="https://www.igvita.com/2015/08/17/eliminating-roundtrips-with-preconnect/"><?php echo __( 'Eliminating Roundtrips with Preconnect, by Ilya Grigorik' ); ?></a></li>
			<li><a href=""></a></li>
		</ul>
		<hr>
		<?php
	}

	private function show_preload_info() {
		?>
		<span id="gktpp-preload" style="display: block; height: 30px;"></span>
		<h2><?php echo __( 'Preload' ); ?></h2>

		<p><?php echo __( 'Preloading <b>fetches one resource</b> proactively.' ); ?></p>

		<p><?php echo __( 'This works in a similar manner as prefetching, however the preload hint is <b>mandatory, high priority, used for current navigation, and non-render blocking</b>. Whereas prefetching resources is optional (to the browser), low priority, and used to load resources a visitor is likely to require in the future.' ); ?></p>

		<p><?php echo __( 'Let\'s see what the waterfall chart looks like after the following resource hints are placed into the top of a web page:' ); ?></p>

		<div class="gktpp-code-block">
			<script src="https://gist.github.com/sarcastasaur/5683b903e195225ebb9e6379c80eba8c.js"></script>
		</div>

		<p><?php echo __( 'The waterfall chart below, courtesy of <a href="https://www.webpagetest.org">webpagetest.org</a>, shows when preloading resources are loaded by the browser:' ); ?></p>

		<img class="gktpp-admin-pic" width="1046" height="597" src="<?php echo plugins_url( '/pre-party-browser-hints/images/jsbin-preload.jpg' ); ?>">

		<p><?php echo __( 'More information:' ); ?></p>
		<ul>
			<li><a href="https://w3c.github.io/preload/"><?php echo __( 'W3C: Preload' ); ?></a></li>
		</ul>
		<hr>
		<?php
	}
}

if ( is_admin() )
	$settings_page = new GKTPP_Options();
