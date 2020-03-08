<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {

	public function __construct() {
		$this->display_settings();
	}

	public function display_settings() {
		?>
			<div id="pprh-settings">
				<form method="post">
					<?php
						$this->save_user_options();
						$this->preconnects_html();
						$this->settings_html();
					?>
				</form>
			</div>
		<?php
	}

	public function save_user_options() {
		global $wpdb;
		$post_meta_table = $wpdb->prefix . 'postmeta';

		if ( isset( $_POST['reset_global_preconnects'] ) && check_admin_referer( 'pprh_save_admin_options', 'pprh_admin_options_nonce' ) ) {
			update_option( 'pprh_reset_global_preconnects', 'true' );
			$this->reset_hints( 'global' );
		}

		if ( isset( $_POST['reset_post_preconnects'] ) ) {
			$wpdb->update(
				$post_meta_table,
				array( 'meta_value' => 'true' ),
				array( 'meta_key' => 'pprh_reset_post_preconnects' ),
				array( '%s' )
			);
			update_option( 'pprh_reset_home_preconnects', 'true' );
			$this->reset_auto_post_preconnects();
		}

		if ( isset( $_POST['reset_home_preconnects'] ) ) {
			update_option( 'pprh_reset_home_preconnects', 'true' );
			$this->reset_hints( '0' );
		}

		if ( isset( $_POST['save_options'] ) && check_admin_referer( 'pprh_save_admin_options', 'pprh_admin_options_nonce' ) ) {
			update_option( 'pprh_autoload_preconnects', wp_unslash( $_POST['autoload_preconnects'] ) );
			update_option( 'pprh_disable_wp_hints', wp_unslash( $_POST['disable_wp_hints'] ) );
			update_option( 'pprh_allow_unauth', wp_unslash( $_POST['allow_unauth'] ) );
			update_option( 'pprh_html_head', wp_unslash( $_POST['html_head'] ) );
		}

		if ( isset( $_POST['pprhPostModalType'] ) ) {
			$json = json_encode( wp_unslash( $_POST['pprhPostModalType'] ) );
			update_option( 'pprh_post_modal_types', $json );
		}
	}

	public function preconnects_html() {

		// wp_nonce_field( 'pprh_save_admin_options', 'pprh_admin_options_nonce' );
		?>
		<h2><?php esc_html_e( 'Auto Preconnect Options', 'pprh' ); ?></h2>

		<table class="pprh-settings-table">
			<tbody>

				<?php
				$this->reset_globals_html();

				if ( get_option( 'show_on_front' ) === 'posts' ) {
					$this->reset_home_preconnects();
				}

				$this->reset_post_page_preconnects();
				?>
			</tbody>
		</table>

		<?php
	}


	public function reset_globals_html() {
		?>
		<tr>
			<th><?php esc_html_e( 'Reset Global Preconnect Links?', 'pprh' ); ?></th>

			<td>
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'This will reset all of the automatically generated global preconnect hints, which are used on all posts and pages.', 'pprh' ); ?></span>
				</span>
			</td>

			<td><input type="submit" name="reset_global_preconnects" id="pprhResetGlobalPreconnects" class="button-secondary" value="Reset"/></td>
		</tr>

		<?php
	}

	public function reset_home_preconnects() {
		?>
		<tr>
			<th><?php esc_html_e( 'Reset Home Preconnect Links?', 'pprh' ); ?></th>

			<td>
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'This will reset automatically created preconnect hints on the home page. (This option only applies when the home page is set to display recent posts.)', 'pprh' ); ?></span>
				</span>
			</td>

			<td><input type="submit" name="reset_home_preconnects" id="pprhHomeReset" class="button-secondary" value="Reset"/></td>
		</tr>

		<?php
	}


	public function reset_post_page_preconnects() {
		?>
		<tr>
			<th><?php esc_html_e( 'Reset All Post/Page Preconnect Links?', 'pprh' ); ?></th>

			<td>
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'This will reset all of the automatically generated preconnect hints, which are unique to each posts or page.', 'pprh' ); ?></span>
				</span>
			</td>

			<td><input type="submit" name="reset_post_preconnects" id="pprhResetPostPreconnects" class="button-secondary" value="Reset"/></td>
		</tr>

		<?php
	}

	public function settings_html() {

		wp_nonce_field( 'pprh_save_admin_options', 'pprh_admin_options_nonce' );
		// $saved_posts = json_decode( get_option( 'pprh_post_modal_types' ) );

		?>
		<h2 style="margin-top: 30px;"><?php esc_html_e( 'Settings', 'pprh' ); ?></h2>

		<table class="pprh-settings-table">
			<tbody>

			<?php
			$this->auto_set_globals();
			$this->disable_auto_wp_hints();
			$this->allow_unauth();
			$this->set_hint_destination();
			$this->check_post_modal_types();
			?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="3">
						<input type="submit" name="save_options" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'pprh' ); ?>" />
					</td>
				</tr>

			</tfoot>

		</table>

		<?php
	}

	public function auto_set_globals() {
		?>
		<tr>
			<th><?php esc_html_e( 'Automatically Set Preconnect Hints?', 'pprh' ); ?></th>

			<td>
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'JavaScript, CSS, and images loaded from external domains will preconnect automatically.', 'pprh' ); ?></span>
				</span>
			</td>

			<td>
				<label>
					<select name="autoload_preconnects">
						<option value="true" <?php $this->get_option_status( 'autoload_preconnects', 'true' ); ?>>
							<?php esc_html_e( 'Yes', 'pprh' ); ?>
						</option>
						<option value="false" <?php $this->get_option_status( 'autoload_preconnects', 'false' ); ?>>
							<?php esc_html_e( 'No', 'pprh' ); ?>
						</option>
					</select>
				</label>
			</td>
		</tr>

		<?php
	}

	public function disable_auto_wp_hints() {
		?>
		<tr>
			<th><?php esc_html_e( 'Disable Automatically Generated WordPress Resource Hints?', 'pprh' ); ?></th>

			<td>
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'This option will remove three resource hints automatically generated by WordPress, as of 4.8.2.', 'pprh' ); ?></span>
				</span>
			</td>

			<td>
				<label>
					<select name="disable_wp_hints">
						<option value="true" <?php $this->get_option_status( 'disable_wp_hints', 'true' ); ?>>
							<?php esc_html_e( 'Yes', 'pprh' ); ?>
						</option>
						<option value="false" <?php $this->get_option_status( 'disable_wp_hints', 'false' ); ?>>
							<?php esc_html_e( 'No', 'pprh' ); ?>
						</option>
					</select>
				</label>
			</td>
		</tr>

		<?php
	}

	public function allow_unauth() {
		?>
		<tr>
			<th><?php esc_html_e( 'Allow unauthenticated users to auto-set post/page preconnect hints?', 'pprh' ); ?></th>

			<td>
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'Automatically set preconnect hints used on posts/pages are initially set once by the first user to access that page.', 'pprh' ); ?></span>
				</span>
			</td>

			<td>
				<label>
					<select name="allow_unauth">
						<option value="true" <?php $this->get_option_status( 'allow_unauth', 'true' ); ?>>
							<?php esc_html_e( 'Yes', 'pprh' ); ?>
						</option>
						<option value="false" <?php $this->get_option_status( 'allow_unauth', 'false' ); ?>>
							<?php esc_html_e( 'No', 'pprh' ); ?>
						</option>
					</select>
				</label>
			</td>
		</tr>

	<?php
	}

	public function set_hint_destination() {
		?>
		<tr>
			<th><?php esc_html_e( 'Send resource hints in HTML head or HTTP header?', 'pprh' ); ?></th>

			<td>
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'Send hints', 'pprh' ); ?></span>
				</span>
			</td>

			<td>
				<select id="pprhHintLocation" name="html_head">
					<option value="true" <?php $this->get_option_status( 'html_head', 'true' ); ?>>
						<?php esc_html_e( 'HTML <head>', 'pprh' ); ?>
					</option>
					<option value="false" <?php $this->get_option_status( 'html_head', 'false' ); ?>>
						<?php esc_html_e( 'HTTP Header', 'pprh' ); ?>
					</option>
				</select>
			</td>
		</tr>

		<?php
	}

<<<<<<< Updated upstream
    public function set_hint_destination() {
        ?>
        <tr>
            <th>
                <?php esc_html_e( 'Send resource hints in HTML head or HTTP header?', 'pprh' ); ?>
            </th>
=======
	public function get_option_status( $option, $val ) {
		echo esc_html( ( get_option( 'pprh_' . $option ) === $val ? 'selected=selected' : '' ) );
	}

	private function reset_hints( $post_type ) {
		global $wpdb;

		$wpdb->delete(
			PPRH_DB_TABLE,
			array(
				'hint_type' => 'preconnect',
				'post_id'   => $post_type
			)
		);
	}

	private function reset_auto_post_preconnects() {
		global $wpdb;
		$table = PPRH_DB_TABLE;

		$wpdb->query(
			$wpdb->prepare( "DELETE FROM $table WHERE hint_type = %s AND post_id != %s AND post_id != %s", 'preconnect', '0', 'global' )
		);
	}

	private function check_post_modal_types() {
		global $wp_post_types;
		$saved_posts = json_decode( get_option( 'pprh_post_modal_types' ) );
		?>
		<tr>
			<th><?php esc_html_e( 'Allow resource hint post modal to be shown on these post types:', 'pprh' ); ?></th>
>>>>>>> Stashed changes

			<td>
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'Check the boxes of the post types you would like the modal window to appear', 'pprh' ); ?></span>
				</span>
<<<<<<< Updated upstream
            </td>

            <td>
                <select id="pprhHintLocation" name="pprh_html_head">
                    <option value="true" <?php $this->get_option_status( 'pprh_html_head', 'true' ); ?>>
                        <?php esc_html_e( 'HTML <head>', 'pprh' ); ?>
                    </option>
                    <option value="false" <?php $this->get_option_status( 'pprh_html_head', 'false' ); ?>>
                        <?php esc_html_e( 'HTTP Header', 'pprh' ); ?>
                    </option>
                </select>

            </td>
        </tr>

        <?php
    }

	public function get_option_status( $option_name, $val ) {
		echo esc_html( ( get_option( $option_name ) === $val ? 'selected=selected' : '' ) );
=======
			</td>

			<td>
				<?php
					$str = '';
					foreach ( $wp_post_types as $post_type ) {
						if ( ! empty( $saved_posts ) ) {
							foreach ( $saved_posts as $post ) {
								if ( $post === $post_type->name ) {
									$str = 'checked="checked"';
									break;
								}
							}
						}
						echo '<input type="checkbox"' . $str . ' name="pprhPostModalType[]" value="' . esc_html( $post_type->name ) . '"><span> ' . esc_html( $post_type->label ) . '</span><br/>';
						$str = '';
					}
                ?>
			</td>
		</tr>
	
		<?php
>>>>>>> Stashed changes
	}

}

if ( is_admin() ) {
	new Settings();
}
