<?php
/**
 * Displays the content on the plugin settings page
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'Lnkdn_Settings_Tabs' ) ) {
	/**
	 * Class Lnkdn_Settings_Tabs for display Settings tabs
	 */
	class Lnkdn_Settings_Tabs extends Bws_Settings_Tabs {
		/**
		 * Constructor.
		 *
		 * @access public
		 *
		 * @see Bws_Settings_Tabs::__construct() for more information on default arguments.
		 *
		 * @param string $plugin_basename Plugin basename.
		 */
		public function __construct( $plugin_basename ) {
			global $lnkdn_options, $lnkdn_plugin_info;

			$tabs = array(
				'settings'    => array( 'label' => esc_html__( 'Settings', 'bws-linkedin' ) ),
				'display'     => array(
					'label'  => esc_html__( 'Display', 'bws-linkedin' ),
					'is_pro' => 1,
				),
				'misc'        => array( 'label' => esc_html__( 'Misc', 'bws-linkedin' ) ),
				'custom_code' => array( 'label' => esc_html__( 'Custom Code', 'bws-linkedin' ) ),
				'license'     => array( 'label' => esc_html__( 'License Key', 'bws-linkedin' ) ),
			);

			parent::__construct(
				array(
					'plugin_basename'    => $plugin_basename,
					'plugins_info'       => $lnkdn_plugin_info,
					'prefix'             => 'lnkdn',
					'default_options'    => lnkdn_get_options_default(),
					'options'            => $lnkdn_options,
					'is_network_options' => is_network_admin(),
					'tabs'               => $tabs,
					'doc_link'           => 'https://bestwebsoft.com/documentation/bestwebsofts-linkedin/bestwebsofts-linkedin-user-guide/',
					'wp_slug'            => 'bws-linkedin',
					'link_key'           => 'c64e9f9106c1e15bd3f4ece9473fb80d',
					'link_pn'            => '588',
				)
			);

			add_action( get_parent_class( $this ) . '_additional_misc_options', array( $this, 'additional_misc_options' ) );
			add_action( get_parent_class( $this ) . '_display_metabox', array( $this, 'display_metabox' ) );
			add_action( get_parent_class( $this ) . '_display_second_postbox', array( $this, 'display_second_postbox' ) );
		}

		/**
		 * Save plugin options to the database
		 *
		 * @access public
		 * @return array    The action results
		 */
		public function save_options() {
			global $wpdb, $lnkdn_lang_codes;
			$message = '';
			$notice  = '';
			$error   = '';

			if ( ! isset( $_POST['lnkdn_settings_nonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lnkdn_settings_nonce_field'] ) ), 'lnkdn_settings_nonce_action' ) ) {
				esc_html_e( 'Sorry, your nonce did not verify', 'bws-linkedin' );
				exit;
			} else {
				$this->options['follow']                   = isset( $_REQUEST['lnkdn_follow'] ) ? 1 : 0;
				$this->options['follow_page_name']         = isset( $_REQUEST['lnkdn_follow_page_name'] ) ? preg_replace( '/[^0-9]*/', '', sanitize_text_field( wp_unslash( $_REQUEST['lnkdn_follow_page_name'] ) ) ) : '';
				$this->options['homepage']                 = isset( $_REQUEST['lnkdn_homepage'] ) ? 1 : 0;
				$this->options['pages']                    = isset( $_REQUEST['lnkdn_pages'] ) ? 1 : 0;
				$this->options['posts']                    = isset( $_REQUEST['lnkdn_posts'] ) ? 1 : 0;
				$this->options['share']                    = isset( $_REQUEST['lnkdn_share'] ) ? 1 : 0;
				$this->options['use_multilanguage_locale'] = isset( $_REQUEST['lnkdn_use_multilanguage_locale'] ) ? 1 : 0;
				$this->options['position']                 = array();
				if ( ! empty( $_REQUEST['lnkdn_position'] ) && is_array( $_REQUEST['lnkdn_position'] ) ) {
					foreach ( $_REQUEST['lnkdn_position'] as $position ) {
						if ( in_array( sanitize_text_field( wp_unslash( $position ) ), array( 'before_post', 'after_post' ) ) ) {
							$this->options['position'][] = sanitize_text_field( wp_unslash( $position ) );
						}
					}
				}

				$this->options['lang']      = ( isset( $_REQUEST['lnkdn_lang'] ) && array_key_exists( sanitize_text_field( wp_unslash( $_REQUEST['lnkdn_lang'] ) ), $lnkdn_lang_codes ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['lnkdn_lang'] ) ) : $this->options['lang'];
				$this->options['share_url'] = isset( $_REQUEST['lnkdn_share_url'] ) ? trim( esc_url( wp_unslash( $_REQUEST['lnkdn_share_url'] ) ) ) : '';
				if ( filter_var( $this->options['share_url'], FILTER_VALIDATE_URL ) === false ) {
					$this->options['share_url'] = '';
				}

				$this->options['follow_count_mode'] = isset( $_REQUEST['lnkdn_follow_count_mode'] ) && in_array( sanitize_text_field( wp_unslash( $_REQUEST['lnkdn_follow_count_mode'] ) ), array( 'top', 'right' ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['lnkdn_follow_count_mode'] ) ) : '';

				if ( 1 === intval( $this->options['follow'] ) && empty( $this->options['follow_page_name'] ) ) {
					$error = esc_html__( 'Enter the Company/Showcase Page ID for "Follow" button. Settings are not saved.', 'bws-linkedin' );
				}

				$this->options = apply_filters( 'lnkdn_before_save_options', $this->options );
				if ( empty( $error ) ) {
					$message = esc_html__( 'Settings saved', 'bws-linkedin' );
					update_option( 'lnkdn_options', $this->options );
				}
			}

			return compact( 'message', 'notice', 'error' );
		}

		/**
		 * Display Settings Tab
		 */
		public function tab_settings() {
			global $lnkdn_lang_codes, $wp_version;

			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$all_plugins = get_plugins(); ?>
			<h3 class="bws_tab_label"><?php esc_html_e( 'LinkedIn Settings', 'bws-linkedin' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<div class="bws_tab_sub_label"><?php esc_html_e( 'General', 'bws-linkedin' ); ?></div>
			<table class="form-table lnkdn_settings_form">
				<tr>
					<th><?php esc_html_e( 'Buttons', 'bws-linkedin' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" value="1" class="bws_option_affect" data-affect-show=".lnkdn_share_hide" name="lnkdn_share" <?php checked( $this->options['share'], 1 ); ?> /> <?php esc_html_e( 'Share', 'bws-linkedin' ); ?>
							</label>
							<br />
							<label>
								<input type="checkbox" value="1" class="bws_option_affect" data-affect-show=".lnkdn_follow_hide" name="lnkdn_follow"<?php checked( 1, $this->options['follow'] ); ?> /> <?php esc_html_e( 'Follow', 'bws-linkedin' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Display on', 'bws-linkedin' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" value="1" name="lnkdn_homepage" class="lnkdn-no-ajax"<?php checked( 1, $this->options['homepage'] ); ?> />
								<?php esc_html_e( 'Home page', 'bws-linkedin' ); ?>
							</label>
							<br />
							<label>
								<input type="checkbox" value="1" name="lnkdn_posts" class="lnkdn-no-ajax"<?php checked( 1, $this->options['posts'] ); ?> />
								<?php esc_html_e( 'Posts', 'bws-linkedin' ); ?>
							</label>
							<br />
							<label>
								<input type="checkbox" value="1" name="lnkdn_pages" class="lnkdn-no-ajax"<?php checked( 1, $this->options['pages'] ); ?> />
								<?php esc_html_e( 'Pages', 'bws-linkedin' ); ?>
							</label>
							<?php do_action( 'lnkdn_display_on_setting_action', $this->options ); ?>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Buttons Position', 'bws-linkedin' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="lnkdn_position[]" value="before_post" class="lnkdn-no-ajax" 
								<?php
								if ( in_array( 'before_post', $this->options['position'] ) ) {
									echo 'checked="checked"';
								}
								?>
								/>
								<?php esc_html_e( 'Before content', 'bws-linkedin' ); ?>
							</label>
							<br />
							<label>
								<input type="checkbox" name="lnkdn_position[]" value="after_post" class="lnkdn-no-ajax" 
								<?php
								if ( in_array( 'after_post', $this->options['position'] ) ) {
									echo 'checked="checked"';
								}
								?>
								/>
								<?php esc_html_e( 'After content', 'bws-linkedin' ); ?>
							</label>
						</fieldset>
						<div class="bws_info"><?php esc_html_e( 'Unselect all to use a shortcode only.', 'bws-linkedin' ); ?></div>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Language', 'bws-linkedin' ); ?></th>
					<td>
						<select name="lnkdn_lang">
							<?php
							foreach ( $lnkdn_lang_codes as $key => $value ) {
								echo '<option value="' . esc_attr( $key ) . '"';
								if ( $key === $this->options['lang'] ) {
									echo 'selected="selected"';
								}
								echo '>' . esc_html( $value ) . '</option>';
							}
							?>
						</select>
						<div class="bws_info"><?php esc_html_e( 'Select the default language for LinkedIn button(-s).', 'bws-linkedin' ); ?></div>
					</td>
				</tr>
				<tr>
					<th>Multilanguage</th>
					<td>
						<?php
						if ( array_key_exists( 'multilanguage/multilanguage.php', $all_plugins ) || array_key_exists( 'multilanguage-pro/multilanguage-pro.php', $all_plugins ) ) {
							if ( is_plugin_active( 'multilanguage/multilanguage.php' ) || is_plugin_active( 'multilanguage-pro/multilanguage-pro.php' ) ) {
								?>
								<label>
									<input type="checkbox" name="lnkdn_use_multilanguage_locale" class="lnkdn-no-ajax" value="1" <?php checked( 1, $this->options['use_multilanguage_locale'] ); ?> />
									<span class="bws_info"><?php esc_html_e( 'Enable to switch language automatically on multilingual website using Multilanguage plugin.', 'bws-linkedin' ); ?></span>
								</label>
							<?php } else { ?>
								<input disabled="disabled" type="checkbox" name="lnkdn_use_multilanguage_locale" value="1" />
								<span class="bws_info"><?php esc_html_e( 'Enable to switch language automatically on multilingual website using Multilanguage plugin.', 'bws-linkedin' ); ?> <a href="<?php bloginfo( 'url' ); ?>/wp-admin/plugins.php"><?php printf( esc_html__( 'Activate %s', 'bws-linkedin' ), 'Multilanguage' ); ?></a></span>
								<?php
							}
						} else {
							?>
							<input disabled="disabled" type="checkbox" name="lnkdn_use_multilanguage_locale" value="1" />
							<span class="bws_info"><?php esc_html_e( 'Enable to switch language automatically on multilingual website using Multilanguage plugin.', 'bws-linkedin' ); ?> <a href="https://bestwebsoft.com/products/wordpress/plugins/multilanguage/?k=293cebedcff853dd94d5b373161d4694&pn=588&v=<?php echo esc_attr( $this->plugins_info['Version'] ); ?>&wp_v=<?php echo esc_attr( $wp_version ); ?>"><?php esc_html_e( 'Learn More', 'bws-linkedin' ); ?></a></span>
						<?php } ?>
					</td>
				</tr>
			</table>
			<div class="lnkdn_share_hide">
				<div class="bws_tab_sub_label lnkdn_share_enabled"><?php esc_html_e( 'Share Button', 'bws-linkedin' ); ?></div>
				<table class="form-table lnkdn_settings_form lnkdn_share_enabled">
					<tr>
						<th><?php esc_html_e( 'URL', 'bws-linkedin' ); ?></th>
						<td>
							<input type="text" name="lnkdn_share_url" value="<?php echo esc_url( $this->options['share_url'] ); ?>">
							<div class="bws_info"><?php esc_html_e( 'URL to be shared. Leave blank to use a current page URL.', 'bws-linkedin' ); ?></div>
						</td>
					</tr>
				</table>
			</div>
			<div class="lnkdn_follow_hide">
				<div class="bws_tab_sub_label lnkdn_follow_enabled"><?php esc_html_e( 'Follow Button', 'bws-linkedin' ); ?></div>
				<table class="form-table lnkdn_settings_form lnkdn_follow_enabled">
					<tr>
						<th><?php esc_html_e( 'Count Mode', 'bws-linkedin' ); ?></th>
						<td>
							<fieldset>
								<label>
									<input type="radio" name="lnkdn_follow_count_mode" value="top" <?php checked( 'top', $this->options['follow_count_mode'] ); ?> /> <?php esc_html_e( 'Vertical', 'bws-linkedin' ); ?>
								</label>
								<br />
								<label>
									<input type="radio" name="lnkdn_follow_count_mode" value="right" <?php checked( 'right', $this->options['follow_count_mode'] ); ?> /> <?php esc_html_e( 'Horizontal', 'bws-linkedin' ); ?>
								</label>
								<br />
								<label>
									<input type="radio" name="lnkdn_follow_count_mode" value="" <?php checked( '', $this->options['follow_count_mode'] ); ?> /> <?php esc_html_e( 'No count', 'bws-linkedin' ); ?>
								</label>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Company or Showcase Page ID', 'bws-linkedin' ); ?></th>
						<td>
							<input type="text" name="lnkdn_follow_page_name" value="<?php
							if ( preg_match( '/^[0-9]{4,8}$/', preg_replace( '/[^0-9]*/', '', $this->options['follow_page_name'] ) ) ) {
								echo esc_html( preg_replace( '/[^0-9]*/', '', $this->options['follow_page_name'] ) );
							}
							?>" />
							<div class="bws_info"><?php esc_html_e( "Can't find your page ID?", 'bws-linkedin' ); ?>&nbsp;<a href='https://support.bestwebsoft.com/hc/en-us/articles/115002405226'><?php esc_html_e( 'Read the instruction', 'bws-linkedin' ); ?></a></div>
						</td>
					</tr>
				</table>
			</div>
			<?php
			wp_nonce_field( 'lnkdn_settings_nonce_action', 'lnkdn_settings_nonce_field' );
		}

		/**
		 * Display custom options on the 'misc' tab
		 *
		 * @access public
		 */
		public function additional_misc_options() {
			do_action( 'lnkdn_settings_page_misc_action', $this->options );
		}

		/**
		 * Display custom metabox
		 *
		 * @access public
		 */
		public function display_metabox() {
			?>
			<div class="postbox">
				<h3 class="hndle">
					<?php esc_html_e( 'LinkedIn Buttons Shortcode', 'bws-linkedin' ); ?>
				</h3>
				<div class="inside">
					<?php
					esc_html_e( 'Add LinkedIn button(-s) to your posts, pages, custom post types or widgets by using the following shortcode:', 'bws-linkedin' );
					bws_shortcode_output( '[bws_linkedin display=&#34;share,follow&#34;]' );
					?>
				</div>
			</div>
			<?php
		}

		/**
		 * Display custom metabox
		 *
		 * @access public
		 */
		public function display_second_postbox() {
			if ( ! $this->hide_pro_tabs ) {
				?>
				<div class="postbox bws_pro_version_bloc">
					<div class="bws_table_bg"></div>
					<h3 class="hndle">
						<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php esc_html_e( 'Close', 'bws-linkedin' ); ?>"></button>
						<?php esc_html_e( 'LinkedIn Buttons Preview', 'bws-linkedin' ); ?>
					</h3>
					<div class="inside">
						<img src='<?php echo esc_url( plugins_url( 'images/preview_screenshot.png', dirname( __FILE__ ) ) ); ?>' />
					</div>
					<?php $this->bws_pro_block_links(); ?>
				</div>
				<?php
			}
		}

		/**
		 * Display Settings Tab
		 */
		public function tab_display() {
			?>
			<h3 class="bws_tab_label"><?php esc_html_e( 'Display Settings', 'bws-linkedin' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<div class="bws_pro_version_bloc">
				<div class="bws_pro_version_table_bloc">
					<div class="bws_table_bg"></div>
					<table class="form-table bws_pro_version">
						<tr>
							<td colspan="2">
								<?php esc_html_e( 'Please choose the necessary post types (or single pages) where LinkedIn buttons will be displayed:', 'bws-linkedin' ); ?>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<label>
									<input disabled="disabled" checked="checked" type="checkbox" name="jstree_url" value="1" />
									<?php esc_html_e( 'Show URL for pages', 'bws-linkedin' ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<img src="<?php echo esc_url( plugins_url( 'images/pro_screen_1.png', dirname( __FILE__ ) ) ); ?>" alt="<?php esc_html_e( "Example of the site's pages tree", 'bws-linkedin' ); ?>" title="<?php esc_html_e( "Example of the site's pages tree", 'bws-linkedin' ); ?>" />
							</td>
						</tr>
					</table>
				</div>
				<?php $this->bws_pro_block_links(); ?>
			</div>
			<?php
		}
	}
}
