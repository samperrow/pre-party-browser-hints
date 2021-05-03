<?php

namespace PPRH;

if ( ! defined ('ABSPATH' ) ) {
	exit;
}

class Upgrade {

	public function __construct() {
		$this->init();
	}

	public function init() {
		?>
		<div id="pprh-upgrade" class="pprh-content">
			<h2>Upgrade to Pre* Party Resource Hints Pro!</h2>
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

			** youtube video here of difference **


<!--			<div style="text-align: center; margin-top: 20px;">-->
<!--				<input style="padding: 10px 30px; font-size: 22px " id="pprhOpenCheckoutModal" type="button" class="button button-primary" value="Upgrade to Pro Now!"/>-->
<!--			</div>-->


		</div>
		<?php
	}

}