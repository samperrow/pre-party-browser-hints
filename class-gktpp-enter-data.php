<?php

class GKTPP_Enter_Data extends GKTPP_Table {

     protected static function add_url_hint() {

          if ( ! is_admin() )
               exit();
          ?>

		<table id="gktpp-enter-data" class="gktpp-table fixed widefat striped" cellspacing="0">
			<?php wp_nonce_field( 'gkt_preP-settings-page' ); ?>

			<thead>
                    <tr>
					<th colspan="5"><h2 style="text-align: center;"><?php esc_html_e( 'Add New Resource Hint', 'gktpp' ); ?></h2></th>
				</tr>
			</thead>

			<tbody>

				<tr>
					<td style="text-align: right;" colspan="1"><?php esc_html_e( 'URL:', 'gktpp' ); ?></td>
					<td style="width: 100%;" colspan="4">
						<label for="url">
							<input placeholder="<?php esc_attr_e( 'Enter valid domain or URL...', 'gktpp' ); ?>" class="widefat" name="url" />
						</label>
					</td>
				</tr>

				<?php self::show_pp_radio_options(); ?>

			</tbody>

			<tfoot>
                    <tr>
					<th colspan="5" style="text-align: center; padding: 20px 0;">
						<input type="submit" name="gktpp-settings-submit" class="button button-primary" value="<?php esc_attr_e( 'Insert Resource Hint', 'gktpp' ); ?>" />
					</th>
				</tr>
			</tfoot>

		</table>
		<?php
	}

	protected static function show_pp_radio_options() {
		$hint_type = '';
		?>
		<tr>
			<td colspan="1">
				<label for="hint_type">
                         <button class="gktpp-help-tip-hint before">
                              <p class='gktpp-help-tip-box'><?php esc_html_e( 'Insert domain names from external URL\'s to perform DNS resolution early.', 'gktpp' ); ?></p>
                         </button>
                         <span class="gktpp-hint"><?php esc_html_e( 'DNS-Prefetch' ); ?></span>
					<input class="gktpp-radio" name="hint_type" type="radio" value="DNS-Prefetch" <?php if ( 'DNS-Prefetch' === $hint_type ) { echo 'checked="checked"';} ?> />

				</label>
			</td>

			<td colspan="1">
				<label for="hint_type">
                         <button class="gktpp-help-tip-hint before">
                              <p class='gktpp-help-tip-box'><?php esc_html_e( 'Insert the full URL of a resource that is likely to be needed on a page later.', 'gktpp' ); ?></p>
                         </button>
                         <span class="gktpp-hint"><?php esc_html_e( 'Prefetch' ); ?></span>
                         <input class="gktpp-radio" name="hint_type" type="radio" value="Prefetch" <?php if ( 'Prefetch' === $hint_type ) { echo 'checked="checked"'; } ?> />
				</label>
			</td>

			<td colspan="1">
				<label for="hint_type">
                         <button class="gktpp-help-tip-hint before">
                              <p class='gktpp-help-tip-box'><?php esc_html_e( 'Insert the full URL of a page/post your visitors are likely to navigate towards.', 'gktpp' ); ?></p>
                         </button>
                         <span class="gktpp-hint"><?php esc_html_e( 'Prerender' ); ?></span>
                         <input class="gktpp-radio" name="hint_type" type="radio" value="Prerender" <?php if ( 'Prerender' === $hint_type ) { echo 'checked="checked"'; } ?> />
				</label>
			</td>

			<td colspan="1">
				<label for="hint_type">
                         <button class="gktpp-help-tip-hint before">
                              <p class='gktpp-help-tip-box'><?php esc_html_e( 'Insert domain names from external URL\'s to perform DNS resolution, initial connection, and SSL negotiation ahead of time.', 'gktpp' ); ?></p>
                         </button>
                         <span class="gktpp-hint"><?php esc_html_e( 'Preconnect' ); ?></span>
                         <input class="gktpp-radio" name="hint_type" type="radio" value="Preconnect" <?php if ( 'Preconnect' === $hint_type ) { echo 'checked="checked"'; } ?> />
				</label>
			</td>

			<td colspan="1">
				<label for="hint_type">
                         <button class="gktpp-help-tip-hint before">
                              <p class='gktpp-help-tip-box'><?php esc_html_e( 'Insert the full URL of a resource that is needed on a current page.', 'gktpp' ); ?></p>
                         </button>
                         <span class="gktpp-hint"><?php esc_html_e( 'Preload' ); ?></span>
                         <input class="gktpp-radio" name="hint_type" type="radio" value="Preload" <?php if ( 'Preload' === $hint_type ) { echo 'checked="checked"'; } ?> />
				</label>
			</td>

		</tr>

		<?php
	}

    protected static function show_info() { ?>
        <div class="gktpp-table info postbox">

            <h2 class="gktpp-collapse-btn" class="hndle ui-sortable-handle" style="text-align: center;">
                <span><?php esc_html_e( 'Settings', 'gktpp' ); ?></span>
                <button type="button" class="handlediv" aria-expanded="false">
                        <span class="gktpp-toggle-indicator" aria-hidden="true"></span>
                </button>
            </h2>

            <div class="gktpp-collapse-box">
                <?php self::user_options(); ?>
            </div>

        </div>
        <?php
    }

    private static function user_options() {
        global $wpdb;

        if ( isset( $_POST['gktpp-reset-preconnect'] ) ) {
            update_option( 'gktpp_reset_preconnect', 'notset', 'yes' );
            update_option( 'gktpp_preconnect_status', 'Yes', 'yes' );
        }

        if ( isset( $_POST['gktpp-save-user-options'] ) ) {
            update_option( 'gktpp_preconnect_status', $_POST['gktpp-preconnect-status'], 'yes' );
            update_option( 'gktpp_disable_wp_hints', $_POST['gktpp-disable-wp-hints-option'], 'no' );
            update_option( 'gktpp_send_in_header', $_POST['gktpp-send-in-header'], 'yes' );
        } 
        
        $preconnect_status = get_option( 'gktpp_preconnect_status' );
        $header_option = get_option( 'gktpp_send_in_header' );
        $disable_hints = get_option( 'gktpp_disable_wp_hints' ); 

        ?>

       <form class="gktpp-form" method="post" action='<?php admin_url( "admin.php?page=gktpp-plugin-settings&_wpnonce=" );?>'>

            <div class="gktpp-div">
                <h2 class="gktpp-hint"><?php esc_html_e( 'Automatically Set Preconnect Hints?', 'gktpp' ); ?></h2>
                <button class="gktpp-help-tip-hint after">
                    <p class='gktpp-help-tip-box'><?php esc_html_e( 'JavaScript, CSS, and images loaded from external domains will preconnect automatically.', 'gktpp' ); ?></p>
                </button>
            
            <br />
            <br />

                <select name="gktpp-preconnect-status">
                    <option value="<?php echo esc_attr( 'Yes', 'gktpp' ); ?>" <?php if ( 'Yes' === $preconnect_status ) echo 'selected="selected"'; ?>><?php esc_html_e( 'Yes', 'gktpp' ); ?></option>
                    <option value="<?php echo esc_attr( 'No', 'gktpp' ); ?>" <?php if ( 'No' === $preconnect_status ) echo 'selected="selected"'; ?>><?php esc_html_e( 'No', 'gktpp' ); ?></option>
                </select>
                <input type="submit" name="gktpp-reset-preconnect" class="button-secondary" value="<?php esc_attr_e( 'Reset Links', 'gktpp' ); ?>" />
            </div>


            <div class="gktpp-div">
                <h2 class="gktpp-hint"><?php esc_html_e( 'Send Resource Hints in the Header or <head>?', 'gktpp' ); ?></h2>
                <button class="gktpp-help-tip-hint after">
                    <p class='gktpp-help-tip-box'><?php esc_html_e( 'Embedding hints in the header allows the browser more time to process the hints, while loading in the <head> allows them to be more visible. Header is recommended.', 'gktpp' ); ?></p>
                </button>

                <br />
                <br />

                <select id="gktppHintLocation" name="gktpp-send-in-header">
                    <option value="<?php echo esc_attr( 'HTTP Header', 'gktpp' ); ?>"<?php if ( 'HTTP Header' === $header_option ) echo 'selected="selected"'; ?>><?php esc_html_e( 'HTTP Header', 'gktpp' ); ?></option>
                    <option value="<?php echo esc_attr( 'Send in head', 'gktpp' ); ?>"<?php if ( 'Send in head' === $header_option ) echo 'selected="selected"'; ?>><?php esc_html_e( 'Send in <head>', 'gktpp' ); ?></option>
                </select>

                <?php 
                    $active_cache_plugin = self::get_cache_info();

                    if (strlen($active_cache_plugin) > 0) {
                        echo "<span id='gktppCachePlugins'>$active_cache_plugin</span>";
                        echo "<p id='gktppBox'></p>";
                    }

                ?>
            </div>


            <div class="gktpp-div">
                <h2 class="gktpp-hint"><?php esc_html_e( 'Disable Auto-Generated WordPress Resource Hints?', 'gktpp' ); ?></h2>
                <button class="gktpp-help-tip-hint after">
                    <p class='gktpp-help-tip-box'><?php esc_html_e( 'This option will remove three resource hints automatically generated by WordPress, as of 4.8.2.', 'gktpp' ); ?></p>
                </button>

                <br />
                <br />

                <select name="gktpp-disable-wp-hints-option">
                    <option value="<?php echo esc_attr( 'Yes', 'gktpp' ); ?>"<?php if ( 'Yes' === $disable_hints ) echo 'selected="selected"'; ?>><?php esc_html_e( 'Yes', 'gktpp' ); ?></option>
                    <option value="<?php echo esc_attr( 'No', 'gktpp' ); ?>"<?php if ( 'No' === $disable_hints ) echo 'selected="selected"'; ?>><?php esc_html_e( 'No', 'gktpp' ); ?></option>
                </select>
            </div>

                <input style="margin: 0 25px;" type="submit" name="gktpp-save-user-options" class="button button-primary" value="<?php esc_attr_e( 'Save Options', 'gktpp' ); ?>" />
        
        </form>


    <?php }

    private static function get_cache_info() {

        $cache_plugins = array(
            'cache-control/cache-control.php' =>        'Cache Control',
            'cache-enabler/cache-enabler.php' =>        'Cache Enabler',
            'comet-cache/comet-cache.php' =>            'Comet Cache',
            'hyper-cache/plugin.php' =>                 'Hyper Cache',
            'litespeed-cache/litespeed-cache.php' =>    'LiteSpeed Cache',
            'redis-cache/redis-cache.php' =>            'Redis Cache',
            'w3-total-cache/w3-total-cache.php' =>      'W3 Total Cache',
            'wp-fastest-cache/wpFastestCache.php' =>    'WP Fastest Cache',
            'wp-rocket/wp-rocket.php' =>                'WP Rocket',
            'wp-super-cache/wp-cache.php' =>            'WP Super Cache',
        );

        foreach ($cache_plugins as $cache_plugin => $name) {
            if (is_plugin_active($cache_plugin)) {
                return $name;
            }
        }
    }

     protected static function contact_author() { ?>
          <div class="gktpp-table info postbox">

               <h2 class="gktpp-collapse-btn" class="hndle ui-sortable-handle" style="text-align: center;">
                    <span><?php esc_html_e( 'Request New Feature or Report a Bug:' ); ?></span>
                    <button type="button" class="handlediv" aria-expanded="false">
                         <span class="gktpp-toggle-indicator" aria-hidden="true" class=""></span>
                    </button>
               </h2>

               <div class="gktpp-collapse-box">
                    <form class="gktpp-form contact" method="post" action="">
                         <textarea value="" placeholder="<?php esc_attr_e( 'Help make this plugin better!' ); ?>" type="text" name="gktpp_text"></textarea>
                         <input onkeyup="emailValidate(event)" id="gktpp-email" class="gktpp-email input" placeholder="<?php esc_attr_e( 'Email address:' ); ?>" name="gktpp_email"  />
                         <input type="hidden" name="submitted" value="1">
                         <input id="gktpp-submit" onclick="emailValidate(event)" name="gktpp_send_email" type="submit" class="button button-primary" value="<?php esc_attr_e( 'Submit', 'gktpp' ); ?>" />
                         <p id="gktpp-error-message"><?php esc_html_e( 'Please enter a valid email address.' ); ?></p>
                    </form>
               </div>
          </div>

          <?php

          if ( isset( $_POST['submitted'] ) ) {

               if ( empty ( $_POST['gktpp_text'] ) || empty( $_POST['gktpp_email'] ) ) {
                    echo "<script>alert('Please enter a valid message and email address.');</script>";
               }

               if ( isset( $_POST['gktpp_send_email'] ) && isset( $_POST['gktpp_email'] ) ) {
                    $debug_info = "\nURL: " . home_url() . "\nPHP Version: " . phpversion() . "\nWP Version: " . bloginfo('version');
                    wp_mail( 'sam.perrow399@gmail.com', 'Pre Party User Message', 'From: ' . strip_tags($_POST['gktpp_email']) . $debug_info . ' Message: ' . strip_tags( $_POST['gktpp_text'] ) );
               }
          }
     }
}

