<?php

class GKTPP_Enter_Data {

     public static function add_url_hint() {

          if ( ! is_admin() )
               exit();
          ?>

		<table id="gktpp-enter-data" class="gktpp-input-table fixed widefat striped" cellspacing="0">
			<?php wp_nonce_field( 'gkt_preP-settings-page' ); ?>

			<thead>
                    <tr>
					<th colspan="5"><h1 style="text-align: center;"><?php esc_html_e( 'Add New Resource Hint', 'gktpp' ); ?></h1></th>
				</tr>
			</thead>

			<tbody>

				<tr>
					<td colspan="1">URL:</td>
					<td colspan="4">
						<label for="url">
							<input placeholder="<?php esc_attr_e( 'Enter valid URL...', 'gktpp' ); ?>" id="gktpp-url-input" class="widefat" name="url" />
						</label>
					</td>
				</tr>

				<?php self::show_pp_radio_options(); ?>

				<?php self::show_posts_and_pages_dropdown(); ?>

			</tbody>

			<tfoot>
                    <tr>
					<th colspan="5" style="text-align: center;">
						<input type="submit" name="gktpp-settings-submit" class="button button-primary" value="<?php esc_attr_e( 'Insert Resource Hint', 'gktpp' ); ?>" />
					</th>
				</tr>
			</tfoot>

		</table>
		<?php
	}

	private static function show_pp_radio_options() {
		$hint_type = '';
		?>
		<tr>
			<td colspan="1">
				<label for="hint_type">
                         <span class='gktpp-help-tip-hint'></span>
					<?php esc_html_e( 'DNS-Prefetch', 'gktpp' ); ?>
					<input class="gktpp-radio" name="hint_type" type="radio" value="DNS-Prefetch" <?php if ( 'DNS-Prefetch' === $hint_type ) { echo 'checked="checked"';} ?> />

                         <p class='gktpp-help-tip-box'>
                              <span><?php esc_html_e( "Insert domain names from external URL's to perform DNS resolution early.", 'gktpp' ); ?></span>
                         </p>

				</label>
			</td>

			<td colspan="1">
				<label for="hint_type">
                         <span class='gktpp-help-tip-hint'></span>
                         <?php esc_html_e( 'Prefetch', 'gktpp' ); ?>
                         <input class="gktpp-radio" name="hint_type" type="radio" value="Prefetch" <?php if ( 'Prefetch' === $hint_type ) { echo 'checked="checked"'; } ?> />

                         <p class="gktpp-help-tip-box">
                              <span><?php esc_html_e( 'Insert the full URL of a resource that is likely to be needed on a page later.', 'gktpp' ); ?></span>
                         </p>

				</label>
			</td>

			<td colspan="1">
				<label for="hint_type">
                         <span class='gktpp-help-tip-hint'></span>
                         <?php esc_html_e( 'Prerender' ); ?>
                         <input class="gktpp-radio" name="hint_type" type="radio" value="Prerender" <?php if ( 'Prerender' === $hint_type ) { echo 'checked="checked"'; } ?> />

                         <p class="gktpp-help-tip-box">
                              <span><?php esc_html_e( 'Insert the full URL of a page/post your visitors are likely to navigate towards.', 'gktpp' ); ?></span>
                         </p>

				</label>
			</td>

			<td colspan="1">
				<label for="hint_type">
                         <span class="gktpp-help-tip-hint"></span>
                         <?php esc_html_e( 'Preconnect' ); ?>
                         <input class="gktpp-radio" name="hint_type" type="radio" value="Preconnect" <?php if ( 'Preconnect' === $hint_type ) { echo 'checked="checked"'; } ?> />

                         <p class="gktpp-help-tip-box">
                              <span><?php esc_html_e( "Insert domain names from external URL's to perform DNS resolution, initial connection, and SSL negotiation ahead of time.", 'gktpp' ); ?></span>
                         </p>

				</label>
			</td>

			<td colspan="1">
				<label for="hint_type">
                         <span class="gktpp-help-tip-hint"></span>
                         <?php esc_html_e( 'Preload' ); ?>
                         <input class="gktpp-radio" name="hint_type" type="radio" value="Preload" <?php if ( 'Preload' === $hint_type ) { echo 'checked="checked"'; } ?> />

                         <p class="gktpp-help-tip-box">
                              <span><?php esc_html_e( 'Insert the full URL of a resource that is needed on a current page.', 'gktpp' ); ?></span>
                         </p>

				</label>
			</td>

		</tr>

		<?php
	}

	private static function show_posts_and_pages_dropdown() {
		$all_pages_and_posts = '';
		?>
		<tr>
			<td colspan="5">
				<label for="allPagesAndPosts">
					<?php esc_html_e( 'Insert into all pages and posts?', 'gktpp' ); ?>
					<input id="allPagesPostsCheck" name="allPagesAndPosts" type="checkbox" />
				</label>
			</td>
		</tr>

		<tr>
			<td colspan="2">
				<dl class="dropdown" style="margin-left: 50px;">
					<dt class="gktppDropdown"><?php esc_html_e( 'Select Page', 'gktpp' ); ?></dt>
					<dd>
						<div class="multiSelect">
							<ul>
								<li><input type="checkbox" class="gktppCheckAll" name="allPages"><?php esc_html_e( 'All Pages', 'gktpp' ); ?></li>
								<?php
								$pages = get_pages();

								foreach ( $pages as $page ) {
									$option = '<li>';
									$option .= '<input name="gktpp_pages[]" class="gktppVisibleCBs" type="checkbox" value="' . esc_attr( $page->ID ) . '">';
									$option .= '<input name="gktpp_post_titles[]" class="gktppHiddenCB" type="checkbox" value="' . esc_attr( $page->post_title ) . '">';
									$option .= esc_html( $page->post_title );
									$option .= '</li>';
									echo $option;
								}
								?>
						 	</ul>
						</div>
					</dd>
				</dl>
			</td>

			<td colspan="3">
				<dl class="dropdown" style="float: right; margin-right: 50px;">
					<dt class="gktppDropdown"><?php esc_html_e( 'Select Post', 'gktpp' ); ?></dt>
					<dd>
						<div class="multiSelect">
							<ul>
								<li><input type="checkbox" class="gktppCheckAll" name="allPosts"><?php esc_html_e( 'All Posts', 'gktpp' ); ?></li>
								<?php
									$posts = get_posts();

								foreach ( $posts as $post ) {
									$option = '<li>';
									$option .= '<input name="gktpp_pages[]" class="gktppVisibleCBs" type="checkbox" value="' . $post->ID . '">';
									$option .= '<input name="gktpp_post_titles[]" class="gktppHiddenCB" type="checkbox" value="' . $post->post_title . '">';
									$option .= esc_html( $post->post_title );
									$option .= '</li>';
									echo $option;
								}
								?>
						 	</ul>
						</div>
					</dd>
				</dl>
			</td>
		</tr>

		<?php
	}

     public static function get_preconnect_status() {
          if ( ! is_admin() )
               exit;

          global $pagenow;
          if ( ('admin.php' === $pagenow) && ( $_GET['page'] === 'gktpp-plugin-settings' ) ) {

               add_option( 'gktpp_preconnect_status', 'Yes', '', 'yes' );
               if ( isset( $_POST['gktpp-set-preconnect'] ) ) {
                    update_option( 'gktpp_preconnect_status', $_POST['gktpp-preconnect-option'], 'no' );
               }
          ?>
               <form method="post" action='<?php admin_url( "admin.php?page=gktpp-plugin-settings&_wpnonce=" );?>'>
                    <?php $preconnect_status = get_option( 'gktpp_preconnect_status' ); ?>

                    <table id="gktpp-prec-option" class="gktpp-input-table fixed widefat striped" style="margin-top: 100px;">
                         <thead></thead>
                         <tbody>
                              <tr>
                                   <td style=""><span class="gktpp-help-tip-hint"></span>Automatically Configure Preconnect Hints?
                                        <p id="gktpp-prec-tip" class='gktpp-help-tip-box' style="margin-top: -105px;"><span><?php esc_html_e( 'JavaScript, CSS, and images loaded from external domains will automatically be preconnected. To update a page, just save that page in the admin panel.', 'gktpp' ); ?></span>
                                        </p>
                                        <select name="gktpp-preconnect-option">
                                             <option value="<?php echo esc_attr( 'Yes', 'gktpp' ); ?>" <?php if ( 'Yes' === $preconnect_status ) echo 'selected="selected"'; ?>>Yes</option>
                                             <option value="<?php echo esc_attr( 'No', 'gktpp' ); ?>" <?php if ( 'No' === $preconnect_status ) echo 'selected="selected"'; ?>>No</option>
                                        </select>
                                        <input type="submit" name="gktpp-set-preconnect" class="button-secondary" value="<?php esc_attr_e( 'Save', 'gktpp' ); ?>" />
                                   </td>
                              </tr>
                         </tbody>
                         <tfoot></tfoot>
                    </table>
               </form>
               <?php
          }
     }
}
