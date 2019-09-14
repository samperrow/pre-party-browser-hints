<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PPRH_Updates {

	public function __construct() {
		$this->add_info();
	}

	private function add_info() {
		?>
		<div style="padding: 0 200px 0 20px; font-size: 15px; line-height: 2em;">
			<h3>Pre* Party Version 2.0.0 Changes and Updates</h3>

			<ul class="children" style="list-style-type: disc;">
				<li>Added ability to add resource hints on a page/post specific basis. There is a modal box in admin post pages which allows you to insert hints. The slight drawback of this is that I was forced to remove support for adding hints in the HTTP header. This was because at the time in which headers are sent by WordPress, the current post ID is not known (as far as I know). </li>
				<li>Cleaned up notification circle CSS and UI</li>
				<li>Improved security by adding more nonces, input sanitization/validation to/from the database table.</li>
				<li>Improved code quality, structure, and simplified the archetecture of this plugin.</li>
				<li>Changed all instances of 'gktpp' to 'pprh'. This more accurately describes the plugin.</li>
				<li>Added ability for users to enter resource hint attributes, such as `crossorigin`, `as`, and `type`.</li>
				<li>Added post-specific auto preconnect hints. This means any page on your site will, by default, have unique preconnect hints added for that post. The first time the plugin is installed, or after the 'Reset Global Preconnect' hints option is reset, the first page that is loaded will save those hints as 'global' hints, which are used on all pages/posts. This is to minimize too many duplicate hints.</li>
				<li>All of this work took MUCH longer than I had intended it would. I have been working on this for about 15-20 hours/week for 4-5 months, and therefore in order to continue quality development, I must make some money off this, so I am charging a license fee. With a license, users will be able to add hints on a post-specific basis.</li>
			</ul>
		</div>

		<?php

	}


}

if ( is_admin() ) {
	new PPRH_Updates();
}
