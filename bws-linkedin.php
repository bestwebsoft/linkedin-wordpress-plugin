<?php
/*##
Plugin Name: LinkedIn by BestWebSoft
Plugin URI: https://bestwebsoft.com/products/wordpress/plugins/linkedin/
Description: Add LinkedIn Share and Follow buttons to WordPress posts, pages and widgets. 5 plugins included – profile, insider, etc.
Author: BestWebSoft
Text Domain: bws-linkedin
Domain Path: /languages
Version: 1.0.5
Author URI: https://bestwebsoft.com
License: GPLv3 or later
*/

/*	@ Copyright 2017  BestWebSoft  ( https://support.bestwebsoft.com )

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* Add BWS menu */
if ( ! function_exists( 'lnkdn_add_admin_menu' ) ) {
	function lnkdn_add_admin_menu() {
		bws_general_menu();
		$settings = add_submenu_page( 'bws_panel', __( 'LinkedIn Settings', 'bws-linkedin' ), 'LinkedIn', 'manage_options', 'linkedin.php', 'lnkdn_settings_page' );
		add_action( 'load-' . $settings, 'lnkdn_add_tabs' );
	}
}
/* end lnkdn_add_admin_menu ##*/

if ( ! function_exists( 'lnkdn_plugins_loaded' ) ) {
	function lnkdn_plugins_loaded() {
		/* Internationalization, first(!) */
		load_plugin_textdomain( 'bws-linkedin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

/* Initialization */
if ( ! function_exists( 'lnkdn_init' ) ) {
	function lnkdn_init() {
		global $lnkdn_plugin_info, $lnkdn_lang_codes;

		if ( empty( $lnkdn_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			$lnkdn_plugin_info = get_plugin_data( __FILE__ );
		}

		/*## add general functions */
		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );
		
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $lnkdn_plugin_info, '3.8' );/* check compatible with current WP version ##*/

		/* Get options from the database */
		if ( ! is_admin() || ( isset( $_GET['page'] ) && ( "linkedin.php" == $_GET['page'] || "social-buttons.php" == $_GET['page'] ) ) ) {
			/* Get/Register and check settings for plugin */
			lnkdn_settings();
			$lnkdn_lang_codes = array(
				"en_US" => 'English', "ar_AE" => 'Arabic', "zh_CN" => 'Chinese - Simplified', "zh_TW" => 'Chinese - Traditional', "cs_CZ" => 'Czech', "da_DK" => 'Danish', "nl_NL" => 'Dutch', "fr_FR" => 'French', "de_DE" => 'German', "in_ID" => 'Indonesian', "it_IT" => 'Italian', "ja_JP" => 'Japanese', "ko_KR" => 'Korean', "ms_MY" => 'Malay', "no_NO" => 'Norwegian', "pl_PL" => 'Polish', "pt_BR" => 'Portuguese', "ro_RO" => 'Romanian', "ru_RU" => 'Russian', "es_ES" => 'Spanish', "sv_SE" => 'Swedish', "tl_PH" => 'Tagalog', "th_TH" => 'Thai', "tr_TR" => 'Turkish'
			);
		}
	}
}

/* Function for admin_init */
if ( ! function_exists( 'lnkdn_admin_init' ) ) {
	function lnkdn_admin_init() {
		/* Add variable for bws_menu */
		global $bws_plugin_info, $lnkdn_plugin_info, $bws_shortcode_list;
		
		/*## Function for bws menu */
		if ( ! isset( $bws_plugin_info ) || empty( $bws_plugin_info ) )	{
			$bws_plugin_info = array( 'id' => '588', 'version' => $lnkdn_plugin_info["Version"] );
		}

		/* Add LinkedIn to global $bws_shortcode_list ##*/
		$bws_shortcode_list['lnkdn'] = array( 'name' => 'LinkedIn Button', 'js_function' => 'lnkdn_shortcode_init'  );
	}
}

if ( ! function_exists ( 'lnkdn_settings' ) ) {
	function lnkdn_settings() {
		global $lnkdn_options, $lnkdn_plugin_info, $lnkdn_options_defaults;

		/* Default options */
		$lnkdn_options_defaults = array(
			'plugin_option_version' 	=> $lnkdn_plugin_info["Version"],
			'display_settings_notice'	=> 1,
			'suggest_feature_banner'    => 1,
			'follow' 					=> 0,
			'follow_count_mode' 		=> 'top',
			'follow_page_name' 			=> '',
			'homepage'					=> 1,
			'lang' 						=> 'en_US',
			'pages'						=> 1,			
			'position' 					=> 'before_post',
			'posts'						=> 1,
			'share' 					=> 1,
			'share_count_mode' 			=> 'top',
			'use_multilanguage_locale'	=> 0			
		);

		if ( ! get_option( 'lnkdn_options' ) )
			add_option( 'lnkdn_options', $lnkdn_options_defaults );

		$lnkdn_options = get_option( 'lnkdn_options' );

		if ( ! isset( $lnkdn_options['plugin_option_version'] ) || $lnkdn_options['plugin_option_version'] != $lnkdn_plugin_info["Version"] ) {
			$lnkdn_options = array_merge( $lnkdn_options_defaults, $lnkdn_options );
			$lnkdn_options['plugin_option_version'] = $lnkdn_plugin_info["Version"];
			update_option( 'lnkdn_options', $lnkdn_options );
		}
	}
}

/* Add settings page in admin area */
if ( ! function_exists( 'lnkdn_settings_page' ) ) {
	function lnkdn_settings_page() {
		global $lnkdn_options, $wp_version, $lnkdn_plugin_info, $lnkdn_options_defaults, $lnkdn_lang_codes;
		$message = $error = "";
		$plugin_basename  = plugin_basename( __FILE__ );

		if ( ! function_exists( 'get_plugins' ) || ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$all_plugins = get_plugins();

		/* Save data for settings page */
		if ( isset( $_REQUEST['lnkdn_form_submit'] ) && check_admin_referer( $plugin_basename, 'lnkdn_nonce_name' ) ) {
			$lnkdn_options['follow']					= isset( $_REQUEST['lnkdn_follow'] ) ? 1 : 0 ;
			$lnkdn_options['follow_page_name']			= preg_replace( "/[^0-9]*/" , "", $_REQUEST['lnkdn_follow_page_name'] );
			$lnkdn_options['homepage']					= isset( $_REQUEST['lnkdn_homepage'] ) ? 1 : 0 ;
			$lnkdn_options['pages']						= isset( $_REQUEST['lnkdn_pages'] ) ? 1 : 0 ;
			$lnkdn_options['posts']						= isset( $_REQUEST['lnkdn_posts'] ) ? 1 : 0 ;
			$lnkdn_options['share']						= isset( $_REQUEST['lnkdn_share'] ) ? 1 : 0 ;
			$lnkdn_options['use_multilanguage_locale'] 	= isset( $_REQUEST['lnkdn_use_multilanguage_locale'] ) ? 1 : 0;
			if ( in_array( $lnkdn_options['position'], array( 'before_post', 'after_post', 'after_and_before', 'only_shortcode' ) ) ) {
				$lnkdn_options['position'] = $_REQUEST['lnkdn_position'];
			}
			if ( array_key_exists( $lnkdn_options['lang'], $lnkdn_lang_codes ) ) {
				$lnkdn_options['lang'] = $_REQUEST['lnkdn_lang'];
			}
			if ( in_array( $lnkdn_options['follow_count_mode'], array( 'top', 'right', '' ) ) ) {
				$lnkdn_options['follow_count_mode'] = $_REQUEST['lnkdn_follow_count_mode'];
			}
			if ( in_array( $lnkdn_options['share_count_mode'], array( 'top', 'right', '' ) ) ) {
				$lnkdn_options['share_count_mode'] = $_REQUEST['lnkdn_share_count_mode'];
			}

			if ( 1 == $lnkdn_options['follow'] && empty( $lnkdn_options['follow_page_name'] ) ) {
				$error = __( 'Enter the Company/Showcase Page ID for "Follow" button. Settings are not saved.', 'bws-linkedin' );
			}
			if ( empty( $error ) ) {
				$lnkdn_options = apply_filters( 'lnkdn_before_save_options', $lnkdn_options );
				update_option( 'lnkdn_options', $lnkdn_options );
				$message = __( 'Settings saved', 'bws-linkedin' );
			}
		}

		/*## restore settings */
		if ( isset( $_REQUEST['bws_restore_confirm'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
			$lnkdn_options = $lnkdn_options_defaults;
			update_option( 'lnkdn_options', $lnkdn_options );
			$message = __( 'All plugin settings were restored', 'bws-linkedin' );
		} 

		/*pls GO PRO */
		if ( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) {
			$go_pro_result = bws_go_pro_tab_check( $plugin_basename, 'pntrst_options' );
			if ( ! empty( $go_pro_result['error'] ) )
				$error = $go_pro_result['error'];
			elseif ( ! empty( $go_pro_result['message'] ) )
				$message = $go_pro_result['message'];
		}/* end GO PRO pls*/##*/ ?>
		<!-- general -->
		<div class="wrap">
			<h1><?php _e( 'LinkedIn Settings', 'bws-linkedin' ); ?></h1>
			<ul class="subsubsub lnkdn_how_to_use">
				<li><a href="https://docs.google.com/document/d/1fc4WbNSuL-eV1gSXWR_BkMEsjy7jyFS5CRG4k7SWEeU/edit" target="_blank"><?php _e( 'How to Use Step-by-step Instruction', 'bws-linkedin' ); ?></a></li>
			</ul>
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab <?php if ( ! isset( $_GET['action'] ) ) echo 'nav-tab-active'; ?>" href="admin.php?page=linkedin.php"><?php _e( 'Settings', 'bws-linkedin' ); ?></a>
				<!-- pls -->
				<a class="nav-tab<?php if ( isset( $_GET['action'] ) && 'extra' == $_GET['action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=linkedin.php&amp;action=extra"><?php _e( 'Extra settings', 'bws-linkedin' ); ?></a>
				<!-- end pls -->
				<a class="nav-tab <?php if ( isset( $_GET['action'] ) && 'custom_code' == $_GET['action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=linkedin.php&amp;action=custom_code"><?php _e( 'Custom code', 'bws-linkedin' ); ?></a>
				<!-- pls -->
				<a class="nav-tab bws_go_pro_tab<?php if ( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=linkedin.php&amp;action=go_pro"><?php _e( 'Go PRO', 'bws-linkedin' ); ?></a>
				<!-- end pls -->
			</h2>
			<!-- end general -->
			<noscript><div class="error below-h2"><p><strong><?php _e( 'Please, enable JavaScript in Your browser.', 'bws-linkedin' ); ?></strong></p></div></noscript>
			<div class="updated fade below-h2" <?php if ( '' == $message || "" != $error ) echo 'style="display:none"'; ?>><p><strong><?php echo $message; ?></strong></p></div>
			<?php bws_show_settings_notice(); ?>
			<div class="error below-h2" <?php if ( "" == $error ) echo 'style="display:none"'; ?>><p><strong><?php echo $error; ?></strong></p></div>
			<?php /*## check action */ 
			if ( ! isset( $_GET['action'] ) ) {
				if ( isset( $_REQUEST['bws_restore_default'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
					bws_form_restore_default_confirm( $plugin_basename );
				} else { /* check action ##*/ ?>
					<div class="lnkdn_settings_block">
						<br />
						<div><?php printf( 
								__( "If you'd like to add LinkedIn Buttons to your page or post, please use %s button", 'bws-linkedin' ), 
								'<span class="bws_code"><span class="bwsicons bwsicons-shortcode"></span></span>' 
							); ?>
							<div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help">
								<div class="bws_hidden_help_text" style="min-width:200px;">
									<?php printf(
										__( "You can add LinkedIn Buttons to your page or post by clicking on %s button in the content edit block using the Visual mode. If the button isn't displayed, please use the shortcode %s to show LinkedIn Buttons, or use parameter 'display' to show one of them or both, e.g. %s", 'bws-linkedin' ), 
										'<span class="bws_code"><span class="bwsicons bwsicons-shortcode"></span></span>',
										'<code>[bws_linkedin]</code>',
										'<br><code>[bws_linkedin display="share,follow"]</code>'
									); ?>
								</div>
							</div>
						</div>
						<div class="lnkdn_form">
							<form method="post" action="" class="bws_form">
								<table class="form-table">
									<tbody>
										<tr valign="top">
											<th><?php _e( 'Display LinkedIn Buttons', 'bws-linkedin' ); ?></th>
											<td>
												<fieldset>
													<label> 
														<input type="checkbox" name="lnkdn_share" <?php if ( 1 == $lnkdn_options['share'] ) echo 'checked="checked"'; ?> value="1" />
														<?php _e( 'Share', 'bws-linkedin' ); ?>
													</label>
													<br />
													<label> 
														<input type="checkbox" name="lnkdn_follow" <?php if ( 1 == $lnkdn_options['follow'] ) echo 'checked="checked"'; ?> value="1" />
														<?php _e( 'Follow', 'bws-linkedin' ); ?>
													</label>
													<br />
												</fieldset>
											</td>
										</tr>
										<tr>
											<th scope="row"><?php _e( 'Language', 'bws-linkedin' ); ?></th>
											<td>
												<fieldset>
													<select name="lnkdn_lang">
														<?php foreach ( $lnkdn_lang_codes as $key => $value ) {
															echo '<option value="' . $key . '"';
															if ( $key == $lnkdn_options['lang'] ) {
																echo 'selected="selected"';
															}
															echo '>' . esc_html( $value ) . '</option>';
														} ?>
													</select>
													<span class="bws_info">(<?php _e( 'Select the language to display information on the button', 'bws-linkedin' ); ?>)</span>
													<br />
													<label>
														<?php if ( array_key_exists( 'multilanguage/multilanguage.php', $all_plugins ) || array_key_exists( 'multilanguage-pro/multilanguage-pro.php', $all_plugins ) ) {
															if ( is_plugin_active( 'multilanguage/multilanguage.php' ) || is_plugin_active( 'multilanguage-pro/multilanguage-pro.php' ) ) { ?>
																<input type="checkbox" name="lnkdn_use_multilanguage_locale" value="1" <?php if ( 1 == $lnkdn_options["use_multilanguage_locale"] ) echo 'checked="checked"'; ?> /> 
																<?php _e( 'Use the current site language', 'bws-linkedin' ); ?> <span class="bws_info">(<?php _e( 'Using', 'bws-linkedin' ); ?> Multilanguage by BestWebSoft)</span>
															<?php } else { ?>
																<input disabled="disabled" type="checkbox" name="lnkdn_use_multilanguage_locale" value="1" />
																<?php _e( 'Use the current site language', 'bws-linkedin' ); ?> 
																<span class="bws_info">(<?php _e( 'Using', 'bws-linkedin' ); ?> Multilanguage by BestWebSoft) 
																	<a href="<?php echo bloginfo( "url" ); ?>/wp-admin/plugins.php"><?php _e( 'Activate', 'bws-linkedin' ); ?> Multilanguage</a>
																</span>
															<?php }
														} else { ?>
															<input disabled="disabled" type="checkbox" name="lnkdn_use_multilanguage_locale" value="1" />
															<?php _e( 'Use the current site language', 'bws-linkedin' ); ?> 
															<span class="bws_info">(<?php _e( 'Using', 'bws-linkedin' ); ?> Multilanguage by BestWebSoft) 
																<a href="https://bestwebsoft.com/products/wordpress/plugins/multilanguage/?k=293cebedcff853dd94d5b373161d4694&pn=588&v=<?php echo $lnkdn_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>"><?php _e( 'Download', 'bws-linkedin' ); ?> Multilanguage</a>
															</span>
														<?php } ?>
													</label>
												</fieldset>
											</td>
										</tr>
										<tr>
											<th scope="row"><?php _e( 'Buttons Position', 'bws-linkedin' ); ?></th>
											<td>
												<select name="lnkdn_position">
													<option value="before_post" <?php if ( 'before_post' == $lnkdn_options['position'] ) echo 'selected="selected"'; ?>><?php _e( 'Before', 'bws-linkedin' ); ?></option>
													<option value="after_post" <?php if ( 'after_post' == $lnkdn_options['position'] ) echo 'selected="selected"'; ?>><?php _e( 'After', 'bws-linkedin' ); ?></option>
													<option value="after_and_before" <?php if ( 'after_and_before' == $lnkdn_options['position'] ) echo 'selected="selected"'; ?>><?php _e( 'Before And After', 'bws-linkedin' ); ?></option>
													<option value="only_shortcode" <?php if ( 'only_shortcode' == $lnkdn_options['position'] ) echo 'selected="selected"'; ?>><?php _e( 'Only Shortcode', 'bws-linkedin' ); ?></option>
												</select>
												<span class="bws_info">(<?php _e( 'Please select location for the buttons on the page', 'bws-linkedin' ); ?>)</span>
											</td>
										</tr>
										<tr>
											<th scope="row"><?php _e( 'Show buttons', 'bws-linkedin' ); ?></th>
											<td>
												<p>
													<label>
														<input type="checkbox" name="lnkdn_posts" <?php if ( 1 == $lnkdn_options['posts'] ) echo 'checked="checked"'; ?> value="1" />
														<?php _e( 'Show in posts', 'bws-linkedin' ); ?>
													</label>
												</p>
												<p>
													<label>
														<input type="checkbox" name="lnkdn_pages" <?php if ( 1 == $lnkdn_options['pages'] ) echo 'checked="checked"'; ?> value="1" />
														<?php _e( 'Show in pages', 'bws-linkedin' ); ?>
													</label>
												</p>
												<p>
													<label>
														<input type="checkbox" name="lnkdn_homepage" <?php if ( 1 == $lnkdn_options['homepage'] ) echo 'checked="checked"'; ?> value="1" />
														<?php _e( 'Show on the homepage', 'bws-linkedin' ); ?>
													</label>
												</p>
												<p>
													<span class="bws_info">(<?php _e( 'Please select the page on which you want to see the buttons', 'bws-linkedin' ); ?>)</span>
												</p>
											</td>
										</tr>
										<?php do_action( 'lnkdn_settings_page_action', $lnkdn_options ); ?>
									</tbody>
								</table>
								<table class="form-table">
									<tbody>
										<!-- Share button settings -->
										<tr class="lnkdn-share-options lnkdn-first" <?php if ( 0 == $lnkdn_options['share'] ) { echo 'style="display:none"'; } ?>>
											<th colspan="2"><?php _e( 'Settings for Share Button', 'bws-linkedin' ); ?></th>
										</tr>
										<tr class="lnkdn-share-options" <?php if ( 0 == $lnkdn_options['share'] ) { echo 'style="display:none"'; } ?>>
											<th scope="row"><?php _e( 'Count Mode', 'bws-linkedin' ); ?></th>
											<td>
												<select name="lnkdn_share_count_mode">
													<option value="top" <?php if ( 'top' == $lnkdn_options['share_count_mode'] ) echo 'selected="selected"'; ?>><?php _e( 'Vertical', 'bws-linkedin' ); ?></option>
													<option value="right" <?php if ( 'right' == $lnkdn_options['share_count_mode'] ) echo 'selected="selected"'; ?>><?php _e( 'Horizontal', 'bws-linkedin' ); ?></option>
													<option value="" <?php if ( '' == $lnkdn_options['share_count_mode'] ) echo 'selected="selected"'; ?>><?php _e( 'No-count', 'bws-linkedin' ); ?></option>
												</select>
												<p>
													<span class="bws_info">(<?php _e( 'Display the number of users who have shared the page', 'bws-linkedin' ); ?>)</span>
												</p>
											</td>
										</tr>
										<!-- Follow button settings -->
										<tr class="lnkdn-follow-options lnkdn-first" <?php if ( 0 == $lnkdn_options['follow'] ) { echo 'style="display:none"'; } ?>>
											<th colspan="2"><?php _e( 'Settings for Follow Button', 'bws-linkedin' ); ?></th>
										</tr>
										<tr class="lnkdn-follow-options" <?php if ( 0 == $lnkdn_options['follow'] ) { echo 'style="display:none"'; } ?>>
											<th><?php _e( 'Count Mode', 'bws-linkedin' ); ?></th>
											<td>
												<select name="lnkdn_follow_count_mode">
													<option value="top" <?php if ( 'top' == $lnkdn_options['follow_count_mode'] ) echo 'selected="selected"'; ?>><?php _e( 'Vertical', 'bws-linkedin' ); ?></option>
													<option value="right" <?php if ( 'right' == $lnkdn_options['follow_count_mode'] ) echo 'selected="selected"'; ?>><?php _e( 'Horizontal', 'bws-linkedin' ); ?></option>
													<option value="" <?php if ( '' == $lnkdn_options['follow_count_mode'] ) echo 'selected="selected"'; ?>><?php _e( 'No-count', 'bws-linkedin' ); ?></option>
												</select>
												<p>
													<span class="bws_info">(<?php _e( 'Display the number of users who are following this page or person', 'bws-linkedin' ); ?>)</span>
												</p>
											</td>
										</tr>
										<tr class="lnkdn-follow-options" <?php if ( 0 == $lnkdn_options['follow'] ) { echo 'style="display:none"'; } ?>>
											<th><?php _e( 'Company/Showcase Page ID', 'bws-linkedin' ); ?></th>
											<td>
												<input type="text" name="lnkdn_follow_page_name" value="<?php if ( preg_match( "/^[0-9]{4,8}$/", preg_replace( "/[^0-9]*/" , "", $lnkdn_options['follow_page_name'] ) ) ) { echo preg_replace( "/[^0-9]*/" , "", $lnkdn_options['follow_page_name'] ); } ?>" placeholder="<?php _e( 'Enter the Company/Showcase Page ID', 'bws-linkedin' ); ?>" />
												<div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help">
													<div class="bws_hidden_help_text" style="min-width:200px;">
														<?php printf(
															__( "You can find the Company/Showcase Page ID like this: go to %s. If you have not yet signed in - click on the button 'Sign in with LinkedIn'. Enter your Company/Showcase Page Name in the appropriate field and further click the button 'Get Code'. When field appears, find %s. Number in quotes is your %s to be copied", 'bws-linkedin' ), 
															'<a href="https://developer.linkedin.com/plugins/follow-company" target="_blank">Follow Company Plugin Page</a>',
															'<code>data-id</code>',
															'<strong>ID</strong>'
														); ?>
													</div>
												</div>
											</td>
										</tr>
									</tbody>
								</table>
								<p class="submit">
									<input id="bws-submit-button" type="submit" value="<?php _e( 'Save Changes', 'bws-linkedin' ); ?>" class="button-primary" />
									<input type="hidden" name="lnkdn_form_submit" value="1" />
									<?php wp_nonce_field( $plugin_basename, 'lnkdn_nonce_name' ); ?>
								</p>
							</form>
						</div>
					</div>
					<!-- general -->
					<?php bws_form_restore_default_settings( $plugin_basename );
				}
			} elseif ( 'custom_code' == $_GET['action'] ) {
				bws_custom_code_tab();
			} /*pls extra banner */ elseif ( 'extra' == $_GET['action'] ) { ?>
				<div class="bws_pro_version_bloc">
					<div class="bws_pro_version_table_bloc">	
						<div class="bws_table_bg"></div>											
						<table class="form-table bws_pro_version">
							<tr>
								<td colspan="2">
									<?php _e( 'Please choose the necessary post types (or single pages) where LinkedIn buttons will be displayed:', 'bws-linkedin' ); ?>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<label>
										<input disabled="disabled" checked="checked" type="checkbox" name="jstree_url" value="1" />
										<?php _e( "Show URL for pages", 'bws-linkedin' );?>
									</label>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<img src="<?php echo plugins_url( 'images/pro_screen_1.png', __FILE__ ); ?>" alt="<?php _e( "Example of the site's pages tree", 'bws-linkedin' ); ?>" title="<?php _e( "Example of site pages' tree", 'bws-linkedin' ); ?>" />
								</td>
							</tr>
							<tr>
								<th scope="row" colspan="2">
									* <?php _e( 'If you upgrade to Pro version all your settings will be saved.', 'bws-linkedin' ); ?>
								</th>
							</tr>				
						</table>	
					</div>
					<div class="bws_pro_version_tooltip">
						<a class="bws_button" href="https://bestwebsoft.com/products/wordpress/plugins/linkedin/?k=c64e9f9106c1e15bd3f4ece9473fb80d&amp;pn=588&amp;v=<?php echo $lnkdn_plugin_info["Version"]; ?>&amp;wp_v=<?php echo $wp_version; ?>" target="_blank" title="LinkedIn Pro"><?php _e( 'Learn More', 'bws-linkedin' ); ?></a>
						<div class="clear"></div>					
					</div>
				</div>
			<?php } elseif ( 'go_pro' == $_GET['action'] ) { 
				bws_go_pro_tab_show( false, $lnkdn_plugin_info, $plugin_basename, 'linkedin.php', 'linkedin-pro.php', 'bws-linkedin-pro/bws-linkedin-pro.php', 'linkedin', 'c64e9f9106c1e15bd3f4ece9473fb80d', '588', isset( $go_pro_result['pro_plugin_is_activated'] ) ); 
			}			
			bws_plugin_reviews_block( $lnkdn_plugin_info['Name'], 'bws-linkedin' ); /* show reviews block pls*/ ?>
		</div>
		<!-- end general -->
	<?php }
}

if ( ! function_exists( 'lnkdn_admin_head' ) ) {
	function lnkdn_admin_head() {
		global $hook_suffix;
		if ( ! is_admin() ) {
			wp_enqueue_style( 'lnkdn_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );
		} elseif ( ( isset( $_GET['page'] ) && ( 'linkedin.php' == $_GET['page'] || 'social-buttons.php' == $_GET['page'] ) ) || 'widgets.php' == $hook_suffix ) {
			wp_enqueue_style( 'lnkdn_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );
			/* Localize script */
			wp_enqueue_script( 'lnkdn_script', plugins_url( 'js/script.js' , __FILE__ ), array( 'jquery' ) );

			if ( isset( $_GET['action'] ) && 'custom_code' == $_GET['action'] )
				bws_plugins_include_codemirror();
		}
	}
}

/* Function for forming buttons tags */
if ( ! function_exists( 'lnkdn_return_button' ) ) {
	function lnkdn_return_button( $request ) {
		global $lnkdn_options;
		if ( 'share' == $request ) {
			$share = '<div class="lnkdn-share-button"><script type="IN/Share" 
				data-url="' . get_permalink() . '" 
				data-counter="' . $lnkdn_options['share_count_mode'] . '"></script></div>';
			return $share;
		}
		
		if ( 'follow' == $request && '' != $lnkdn_options['follow_page_name'] ) {
			$follow = '<div class="lnkdn-follow-button"><script type="IN/FollowCompany" 
				data-id="' . $lnkdn_options['follow_page_name'] . '" 
				data-counter="' . $lnkdn_options['follow_count_mode'] . '"></script></div>';
			return $follow;
		}
	}
}

/* LinkedIn buttons on page */
if ( ! function_exists( 'lnkdn_position' ) ) {
	function lnkdn_position( $content ) {
		global $lnkdn_options;
		
		if ( ! is_feed() && 'only_shortcode' != $lnkdn_options['position'] ) {
			if ( ( ! is_home() && ! is_front_page() ) || 1 == $lnkdn_options['homepage'] ) {
				if ( ( is_single() && 1 == $lnkdn_options['posts'] ) || ( is_page() && 1 == $lnkdn_options['pages'] ) || ( is_home() && 1 == $lnkdn_options['homepage'] ) ) {
					$share  = ( 1 == $lnkdn_options['share'] ) ? lnkdn_return_button( 'share' ) : '';
					$follow = ( 1 == $lnkdn_options['follow'] ) ? lnkdn_return_button( 'follow' ) : '';
					$button = '<div class="lnkdn_buttons">' . $share . $follow . '</div>';
				}
			}			

			if ( ! empty( $button ) ) {
				
				$button = apply_filters( 'lnkdn_button_in_the_content', $button );

				if ( 'before_post' == $lnkdn_options['position'] ) {
					return $button . $content;
				} elseif ( 'after_post' == $lnkdn_options['position'] ) {
					return  $content . $button;
				} elseif ( 'after_and_before' == $lnkdn_options['position'] ) {
					return $button . $content . $button;
				}
			}
		}
		return $content;
	}
}

if ( ! function_exists( 'lnkdn_js' ) ) {
	function lnkdn_js( $extension = '' ) {
		global $lnkdn_options, $lnkdn_lang_codes, $lnkdn_shortcode_add_script, $lnkdn_js_added;

		if ( isset( $lnkdn_js_added ) )
			return;

		if ( 1 == $lnkdn_options['share'] || 1 == $lnkdn_options['follow'] 
			|| isset( $lnkdn_shortcode_add_script )
			|| defined( 'BWS_ENQUEUE_ALL_SCRIPTS' ) ) {
			if ( 1 == $lnkdn_options['use_multilanguage_locale'] && isset( $_SESSION['language'] ) ) {
				if ( array_key_exists( $_SESSION['language'], $lnkdn_lang_codes ) ) {
					$lnkdn_locale = $_SESSION['language'];
				} else {
					$locale_from_multilanguage = explode( '_', $_SESSION['language'] );
					if ( is_array( $locale_from_multilanguage ) && array_key_exists( $locale_from_multilanguage[0], $lnkdn_lang_codes ) )
						$lnkdn_locale = $locale_from_multilanguage[0];
				}
			}
			if ( empty( $lnkdn_locale ) ) {
				$lnkdn_locale = $lnkdn_options['lang'];
			} ?>
			<script src="//platform.linkedin.com/in.js" type="text/javascript"> <?php echo 'lang: ' . $lnkdn_locale . "\n" . $extension; ?></script>
			<?php $lnkdn_js_added = true;
		}
	}
}

if ( ! function_exists( 'lnkdn_pagination_callback' ) ) {
	function lnkdn_pagination_callback( $content ) {
		$content .= "if ( typeof( IN ) != 'undefined' ) { IN.parse(); }";
		return $content;
	}
}

/* LinkedIn Buttons shortcode */
/* [bws_linkedin display="share,follow"] */
if ( ! function_exists( 'lnkdn_shortcode' ) ) {
	function lnkdn_shortcode( $atts ) {
		global $lnkdn_options, $lnkdn_shortcode_add_script;

		$buttons = '';
		$shortcode_atts = shortcode_atts( array( 'display' => 'share,follow' ), $atts );
		$shortcode_atts = ( str_word_count( $shortcode_atts['display'], 1 ) );

		foreach ( $shortcode_atts as $value ) {
			if ( 'share' === $value ) {
				$buttons .= lnkdn_return_button( 'share' );
			}

			if ( 'follow' === $value ) {
				$buttons .= lnkdn_return_button( 'follow' );
			}
		}
		$lnkdn_shortcode_add_script = true;
		
		return '<div class="lnkdn_buttons">' . $buttons . '</div>';
	}
}

/* add shortcode content  */
if ( ! function_exists( 'lnkdn_shortcode_button_content' ) ) {
	function lnkdn_shortcode_button_content( $content ) {
		global $wp_version; ?>
		<div id="lnkdn" style="display:none;">
			<fieldset>
				<label>
					<input type="checkbox" name="lnkdn_selected_share" value="share" checked="checked" />
					<?php _e( 'LinkedIn Share Button', 'bws-linkedin' ) ?>
				</label>
				<br />
				<label>
					<input type="checkbox" name="lnkdn_selected_follow" value="follow" checked="checked" />
					<?php _e( 'LinkedIn Follow Button', 'bws-linkedin' ) ?>
				</label>
				<input class="bws_default_shortcode" type="hidden" name="default" value='[bws_linkedin display="share,follow"]' />
				<div class="clear"></div>
			</fieldset>
		</div>
		<script type="text/javascript">
			function lnkdn_shortcode_init() {
				(function( $ ) {
					var current_object = '<?php echo ( $wp_version < 3.9 ) ? "#TB_ajaxContent" : ".mce-reset"; ?>';
					$( current_object + ' input[name^="lnkdn_selected"]' ).change(function() {
						var result = '';
						$( current_object + ' input[name^="lnkdn_selected"]' ).each(function() {
							if ( $( this ).is( ':checked' ) ) {
								result += $( this ).val() + ',';
							}
						});
						if ( '' == result ) {
							$( current_object + ' #bws_shortcode_display' ).text( '' );
						} else {
							result = result.slice( 0, - 1 );
							$( current_object + ' #bws_shortcode_display' ).text( '[bws_linkedin display="' + result + '"]' );
						}
					});
				}) ( jQuery );
			}
		</script>
	<?php }
}

/* LinkedIn Main Widget */
if ( ! class_exists( 'Lnkdn_Main_Widget' ) ) {
	class Lnkdn_Main_Widget extends WP_Widget {
		function __construct() {
			parent::__construct( 'lnkdn_main', __( 'LinkedIn Widgets', 'bws-linkedin' ), array( 'description' => __( 'Choose one of 5 LinkedIn widgets', 'bws-linkedin' ) ) );
		}

		function widget( $args, $instance ) {
			$title 				= ( ! empty( $instance['lnkdn_title'] ) ) ? apply_filters( 'widget_title', $instance['lnkdn_title'], $instance, $this->id_base ) : '';
			$select_widget 		= ( ! empty( $instance['lnkdn_select_widget'] ) ) ? $instance['lnkdn_select_widget'] : '';
			$public_profile_url = ( ! empty( $instance['lnkdn_public_profile_url'] ) ) ? $instance['lnkdn_public_profile_url'] : '';
			$company_id			= ( ! empty( $instance['lnkdn_company_id'] ) && 'all_jobs' != $instance['lnkdn_display_jobs_mode'] ) ? $instance['lnkdn_company_id'] : '';
			$show_connections 	= ( ! empty( $instance['lnkdn_show_connections'] ) && 'show' == $instance['lnkdn_show_connections'] ) ? '' : 'false';
			$display_mode 		= ( ! empty( $instance['lnkdn_display_mode'] ) ) ? $instance['lnkdn_display_mode'] : '';
			$display_jobs_mode 	= ( ! empty( $instance['lnkdn_display_jobs_mode'] ) && 'all_jobs' != $instance['lnkdn_display_jobs_mode'] ) ? $instance['lnkdn_display_jobs_mode'] : '';
			$behavior 			= ( ! empty( $instance['lnkdn_behavior'] ) ) ? $instance['lnkdn_behavior'] : '';
			$school_id			= ( ! empty( $instance['lnkdn_school_id'] ) ) ? $instance['lnkdn_school_id'] : '';

			if ( 'icon' == $display_mode ) {
				$display_mode = 'hover';
				if ( 'on_click' == $behavior ) {
					$display_mode = 'click';
				}
			}

			echo $args['before_widget'];
			if ( ! empty( $title ) ) {
				echo $args['before_title'] . $title . $args['after_title'];
			} 

			if ( 'member_profile' == $select_widget ) { ?>
				<script type="IN/MemberProfile" 
					data-id="<?php echo $public_profile_url; ?>" 
					data-format="<?php echo $display_mode; ?>" 
					data-related="<?php echo $show_connections; ?>" 
					data-text="" 
					data-width="100%">
				</script>
			<?php }

			if ( 'company_profile' == $select_widget ) { ?>
				<script type="IN/CompanyProfile" 
					data-id="<?php echo $company_id; ?>" 
					data-format="<?php echo $display_mode; ?>" 
					data-related="<?php echo $show_connections; ?>" 
					data-text="" 
					data-width="100%">
				</script>
			<?php }

			if ( 'company_insider' == $select_widget ) { ?>
				<script type="IN/CompanyInsider" 
					data-id="<?php echo $company_id; ?>"></script>
			<?php }

			if ( 'jymbii' == $select_widget ) { ?>
				<script type="IN/JYMBII" 
					data-companyid="<?php echo $company_id; ?>" 
					data-format="<?php echo $display_mode; ?>" 
					data-width="100%">
				</script>
			<?php }

			if ( 'alumni_tool' == $select_widget ) { 
				lnkdn_js( 'extensions: AlumniFacet@//www.linkedin.com/edu/alumni-facet-extension-js' ); ?>
				<script type="IN/AlumniFacet" data-linkedin-schoolid="<?php echo $school_id; ?>"></script>
			<?php }
			echo $args['after_widget'];
		}

		function form( $instance ) {
			$select_widget 		= isset( $instance['lnkdn_select_widget'] ) ? $instance['lnkdn_select_widget'] : 'member_profile';
			$title 				= isset( $instance['lnkdn_title'] ) ? esc_attr( $instance['lnkdn_title'] ) : '';
			$public_profile_url = isset( $instance['lnkdn_public_profile_url'] ) ? $instance['lnkdn_public_profile_url'] : '';
			$company_id			= isset( $instance['lnkdn_company_id'] ) ? $instance['lnkdn_company_id'] : '';
			$show_connections 	= isset( $instance['lnkdn_show_connections'] ) ? $instance['lnkdn_show_connections'] : 'show';
			$display_mode 		= isset( $instance['lnkdn_display_mode'] ) ? $instance['lnkdn_display_mode'] : 'inline';
			$display_jobs_mode 	= isset( $instance['lnkdn_display_jobs_mode'] ) ? $instance['lnkdn_display_jobs_mode'] : 'your_jobs';
			$behavior 			= isset( $instance['lnkdn_behavior'] ) ? $instance['lnkdn_behavior'] : 'on_hover';
			$school_id			= isset( $instance['lnkdn_school_id'] ) ? $instance['lnkdn_school_id'] : ''; ?>

			<noscript><div class="error below-h2"><p><strong><?php _e( 'Please, enable JavaScript in Your browser.', 'bws-linkedin' ); ?></strong></p></div></noscript>
			<p class="lnkdn_all">
				<label for="<?php echo $this->get_field_id( 'lnkdn_select_widget' ); ?>"><?php _e( 'LinkedIn Widgets', 'bws-linkedin' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'lnkdn_select_widget' ); ?>" name="<?php echo $this->get_field_name( 'lnkdn_select_widget' ); ?>">
					<option value="member_profile" <?php if ( 'member_profile' == $select_widget ) echo 'selected="selected"'; ?>><?php _e( 'Member Profile Widget', 'bws-linkedin' ); ?></option>
					<option value="company_profile" <?php if ( 'company_profile' == $select_widget ) echo 'selected="selected"'; ?>><?php _e( 'Company Profile Widget', 'bws-linkedin' ); ?></option>
					<option value="company_insider" <?php if ( 'company_insider' == $select_widget ) echo 'selected="selected"'; ?>><?php _e( 'Company Insider Widget', 'bws-linkedin' ); ?></option>
					<option value="jymbii" <?php if ( 'jymbii' == $select_widget ) echo 'selected="selected"'; ?>><?php _e( 'Jobs Your May Be Interested In Widget', 'bws-linkedin' ); ?></option>
					<option value="alumni_tool" <?php if ( 'alumni_tool' == $select_widget ) echo 'selected="selected"'; ?>><?php _e( 'Alumni Tool Widget', 'bws-linkedin' ); ?></option>
				</select>
			</p>
			<p class="lnkdn_all">
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'bws-linkedin' ); ?>:</label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'lnkdn_title' ); ?>" name="<?php echo $this->get_field_name( 'lnkdn_title' ); ?>" type="text" value="<?php echo $title; ?>" />
			</p>
			<p class="lnkdn_member_profile <?php if ( 'member_profile' != $select_widget ) echo 'lnkdn-hide-option'; ?>">
				<label for="<?php echo $this->get_field_id( 'lnkdn_public_profile_url' ); ?>"><?php _e( 'Public Profile URL', 'bws-linkedin' ); ?>:</label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'lnkdn_public_profile_url' ); ?>" name="<?php echo $this->get_field_name( 'lnkdn_public_profile_url' ); ?>" type="text" value="<?php echo esc_html( $public_profile_url ); ?>" placeholder="<?php _e( 'Enter the Public Profile URL', 'bws-linkedin' ); ?>" />
			</p>
			<p class="lnkdn_jymbii <?php if ( 'jymbii' != $select_widget ) echo 'lnkdn-hide-option'; ?>">
				<label for="<?php echo $this->get_field_id( 'lnkdn_display_jobs_mode' ); ?>"><?php _e( 'Display Mode', 'bws-linkedin' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'lnkdn_display_jobs_mode' ); ?>" name="<?php echo $this->get_field_name( 'lnkdn_display_jobs_mode' ); ?>">
					<option value="your_jobs" <?php if ( 'your_jobs' == $display_jobs_mode ) echo 'selected="selected"'; ?>><?php _e( 'Your Jobs', 'bws-linkedin' ); ?></option>
					<option value="all_jobs" <?php if ( 'all_jobs' == $display_jobs_mode ) echo 'selected="selected"'; ?>><?php _e( 'All Jobs', 'bws-linkedin' ); ?></option>
				</select>
			</p>
			<p class="lnkdn_company_profile lnkdn_company_insider lnkdn_jymbii lnkdn_all_jobs <?php if ( ( 'company_profile' != $select_widget && 'company_insider' != $select_widget && 'jymbii' != $select_widget ) || 'all_jobs' == $display_jobs_mode ) echo 'lnkdn-hide-option'; ?>">
				<label for="<?php echo $this->get_field_id( 'lnkdn_company_id' ); ?>"><?php _e( 'Company ID', 'bws-linkedin' ); ?>:</label>
				<label class="bws_help_box dashicons dashicons-editor-help">
					<label for="<?php echo $this->get_field_id( 'lnkdn_company_id' ); ?>" class="bws_hidden_help_text lnkdn_company_profile_help" style="<?php if ( 'company_profile' != $select_widget ) { echo 'visibility:hidden'; } ?>">
						<?php printf(
							__( "You can find the Company ID like this: go to %s . If you have not yet signed in - click on the button 'Sign in with LinkedIn'. Enter your Company Name in the appropriate field and further click the button 'Get Code'. When field appears, find %s. Number in quotes is your %s to be copied", 'bws-linkedin' ), 
							'<a href="https://developer.linkedin.com/plugins/company-profile" target="_blank">Company Profile</a>',
							'<code>data-id</code>',
							'<strong>ID</strong>'
						); ?>
					</label>
					<label class="bws_hidden_help_text lnkdn_company_insider_help" style="<?php if ( 'company_insider' != $select_widget ) { echo 'visibility:hidden'; } ?>">
						<?php printf(
							__( "You can find the Company ID like this: go to %s . If you have not yet signed in - click on the button 'Sign in with LinkedIn'. Enter your Company Name in the appropriate field and further click the button 'Get Code'. When field appears, find %s. Number in quotes is your %s to be copied", 'bws-linkedin' ), 
							'<a href="https://developer.linkedin.com/plugins/company-insider" target="_blank">Company Insider</a>',
							'<code>data-id</code>',
							'<strong>ID</strong>'
						); ?>
					</label>
					<label class="bws_hidden_help_text lnkdn_jymbii_help" style="<?php if ( 'jymbii' != $select_widget ) { echo 'visibility:hidden'; } ?>">
						<?php printf(
							__( "You can find the Company ID like this: go to %s . If you have not yet signed in - click on the button 'Sign in with LinkedIn'. Enter your Company Name in the appropriate field and further click the button 'Get Code'. When field appears, find %s. Number in quotes is your %s to be copied", 'bws-linkedin' ),
							'<a href="https://developer.linkedin.com/plugins/jymbii" target="_blank">JYMBII</a>',
							'<code>data-companyid</code>',
							'<strong>ID</strong>'
						); ?>
					</label>
				</label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'lnkdn_company_id' ); ?>" name="<?php echo $this->get_field_name( 'lnkdn_company_id' ); ?>" type="text" value="<?php if ( preg_match( "/^[0-9]{4,8}$/", preg_replace( "/[^0-9]*/" , "", $company_id ) ) ) { echo preg_replace( "/[^0-9]*/" , "", $company_id ); } ?>" placeholder="<?php _e( 'Enter the Company ID', 'bws-linkedin' ); ?>" />
			</p>
			<p class="lnkdn_member_profile lnkdn_company_profile <?php if ( 'member_profile' != $select_widget && 'company_profile' != $select_widget ) echo 'lnkdn-hide-option'; ?>">
				<label for="<?php echo $this->get_field_id( 'lnkdn_display_mode' ); ?>"><?php _e( 'Display Mode', 'bws-linkedin' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'lnkdn_display_mode' ); ?>" name="<?php echo $this->get_field_name( 'lnkdn_display_mode' ); ?>">
					<option value="inline" <?php if ( 'inline' == $display_mode ) echo 'selected="selected"'; ?>><?php _e( 'Inline', 'bws-linkedin' ); ?></option>
					<option value="icon" <?php if ( 'icon' == $display_mode ) echo 'selected="selected"'; ?>><?php _e( 'Icon', 'bws-linkedin' ); ?></option>
				</select>
			</p>
			<p class="lnkdn_member_profile lnkdn_company_profile <?php if ( 'member_profile' != $select_widget && 'company_profile' != $select_widget ) echo 'lnkdn-hide-option'; ?>">
				<label for="<?php echo $this->get_field_id( 'lnkdn_show_connections' ); ?>"><?php _e( 'Show Connections', 'bws-linkedin' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'lnkdn_show_connections' ); ?>" name="<?php echo $this->get_field_name( 'lnkdn_show_connections' ); ?>">
					<option value="show" <?php if ( 'show' == $show_connections ) echo 'selected="selected"'; ?>><?php _e( 'Show', 'bws-linkedin' ); ?></option>
					<option value="hide" <?php if ( 'hide' == $show_connections ) echo 'selected="selected"'; ?>><?php _e( 'Hide', 'bws-linkedin' ); ?></option>
				</select>
			</p>
			<p class="lnkdn_inline <?php if ( ( 'member_profile' != $select_widget && 'company_profile' != $select_widget ) || 'inline' == $display_mode ) echo 'lnkdn-hide-option'; ?>">
				<label for="<?php echo $this->get_field_id( 'lnkdn_behavior' ); ?>"><?php _e( 'Behavior', 'bws-linkedin' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'lnkdn_behavior' ); ?>" name="<?php echo $this->get_field_name( 'lnkdn_behavior' ); ?>">
					<option value="on_hover" <?php if ( 'on_hover' == $behavior ) echo 'selected="selected"'; ?>><?php _e( 'On Hover', 'bws-linkedin' ); ?></option>
					<option value="on_click" <?php if ( 'on_click' == $behavior ) echo 'selected="selected"'; ?>><?php _e( 'On Click', 'bws-linkedin' ); ?></option>
				</select>
			</p>
			<p class="lnkdn_alumni_tool <?php if ( 'alumni_tool' != $select_widget ) echo 'lnkdn-hide-option'; ?>">
				<label for="<?php echo $this->get_field_id( 'lnkdn_school_id' ); ?>"><?php _e( 'School ID', 'bws-linkedin' ); ?>:</label>
				<label for="<?php echo $this->get_field_id( 'lnkdn_school_id' ); ?>" class="bws_help_box dashicons dashicons-editor-help">
					<label for="<?php echo $this->get_field_id( 'lnkdn_school_id' ); ?>" class="bws_hidden_help_text lnkdn_alumni_tool_help">
						<?php printf(
							__( "You can find the School ID like this: go to %s . If you have not yet signed in - click on the button 'Sign in with LinkedIn'. Enter your School Name in the appropriate field and further click the button 'Get Code'. When field appears, find %s. Number in quotes is your %s to be copied", 'bws-linkedin' ), 
							'<a href="https://developer.linkedin.com/plugins/alumni" target="_blank">Alumni Tool</a>',
							'<code>data-linkedin-schoolid</code>',
							'<strong>ID</strong>'
						); ?>
					</label>
				</label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'lnkdn_school_id' ); ?>" name="<?php echo $this->get_field_name( 'lnkdn_school_id' ); ?>" type="text" value="<?php if ( preg_match( "/^[0-9]{4,10}$/", preg_replace( "/[^0-9]*/" , "", $school_id ) ) ) { echo preg_replace( "/[^0-9]*/" , "", $school_id ); } ?>" placeholder="<?php _e( 'Enter the School ID', 'bws-linkedin' ); ?>" />
			</p>
		<?php }

		function update( $new_instance, $old_instance ) {
			$instance 							  = $old_instance;
			$instance['lnkdn_select_widget']      = in_array( $instance['lnkdn_select_widget'], array( 'member_profile', 'company_profile', 'company_insider', 'jymbii', 'alumni_tool' ) ) ? $new_instance['lnkdn_select_widget'] : 'member_profile';
			$instance['lnkdn_title']			  = strip_tags( $new_instance['lnkdn_title'] );
			$instance['lnkdn_public_profile_url'] = esc_url_raw( $new_instance['lnkdn_public_profile_url'] );
			$instance['lnkdn_display_jobs_mode']  = in_array( $instance['lnkdn_display_jobs_mode'], array( 'your_jobs', 'all_jobs' ) ) ? $new_instance['lnkdn_display_jobs_mode'] : 'your_jobs';
			$instance['lnkdn_company_id'] 		  = preg_replace( "/[^0-9]*/" , "", $new_instance['lnkdn_company_id'] );
			$instance['lnkdn_display_mode'] 	  = in_array( $instance['lnkdn_display_mode'], array( 'inline', 'icon' ) ) ? $new_instance['lnkdn_display_mode'] : 'inline';
			$instance['lnkdn_show_connections']   = in_array( $instance['lnkdn_show_connections'], array( 'show', 'hide' ) ) ? $new_instance['lnkdn_show_connections'] : 'show';
			$instance['lnkdn_behavior'] 		  = in_array( $instance['lnkdn_behavior'], array( 'on_hover', 'on_click' ) ) ? $new_instance['lnkdn_behavior'] : 'on_hover';
			$instance['lnkdn_school_id']		  = preg_replace( "/[^0-9]*/" , "", $new_instance['lnkdn_school_id'] );
			return $instance;
		}
	}
}

if ( ! function_exists( 'lnkdn_register_main_widget' ) ) {
	function lnkdn_register_main_widget() {
		register_widget( 'Lnkdn_Main_Widget' );
	}
}

/* Adding class in 'body' Twenty Fifteen/Sixteen Theme for LinkedIn Buttons */
if ( ! function_exists( 'lnkdn_add_body_class' ) ) {
	function lnkdn_add_body_class( $classes ) {
		$get_theme = wp_get_theme();
		if ( $get_theme == 'Twenty Fifteen' || $get_theme == 'Twenty Sixteen' ) {
			$classes[] = 'lnkdn-button-certain-theme';
		}
		if ( $get_theme == 'Twenty Twelve' ) {
			$classes[] = 'lnkdn-button-twenty-twelve-theme';
		}
		return $classes;
	}
}

/*## Functions creates other links on plugins page. */
if ( ! function_exists( 'lnkdn_action_links' ) ) {
	function lnkdn_action_links( $links, $file ) {
		if ( ! is_network_admin() ) {
			/* Static so we don't call plugin_basename on every plugin row. */
			static $this_plugin;
			if ( ! $this_plugin ) {
				$this_plugin = plugin_basename( __FILE__ );
			}
			if ( $file == $this_plugin ) {
				$settings_link = '<a href="admin.php?page=linkedin.php">' . __( 'Settings', 'bws-linkedin' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
		}
		return $links;
	}
}

if ( ! function_exists( 'lnkdn_register_plugin_links' ) ) {
	function lnkdn_register_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			if ( ! is_network_admin() )
				$links[] = '<a href="admin.php?page=linkedin.php">' . __( 'Settings', 'bws-linkedin' ) . '</a>';
			$links[] = '<a href="https://support.bestwebsoft.com/hc/en-us/sections/201989376" target="_blank">' . __( 'FAQ', 'bws-linkedin' ) . '</a>';
			$links[] = '<a href="https://support.bestwebsoft.com">' . __( 'Support', 'bws-linkedin' ) . '</a>';
		}
		return $links;
	}
}

if ( ! function_exists ( 'lnkdn_admin_notices' ) ) {
	function lnkdn_admin_notices() {
		global $hook_suffix, $lnkdn_plugin_info, $lnkdn_options;

		if ( 'plugins.php' == $hook_suffix && ! is_network_admin() ) {			
			/*pls show banner go pro */
			if ( empty( $lnkdn_options ) )
				$lnkdn_options = get_option( 'lnkdn_options' );

			if ( isset( $lnkdn_options['first_install'] ) && strtotime( '-1 week' ) > $lnkdn_options['first_install'] )
				bws_plugin_banner( $lnkdn_plugin_info, 'lnkdn', 'linkedin', '23b248c24d3fbef44d7ac493141591ab', '588', 'bws-linkedin' );

			/* show banner go settings pls*/
			bws_plugin_banner_to_settings( $lnkdn_plugin_info, 'lnkdn_options', 'bws-linkedin', 'admin.php?page=linkedin.php' );
		}

		if ( isset( $_GET['page'] ) && 'linkedin.php' == $_GET['page'] )
			bws_plugin_suggest_feature_banner( $lnkdn_plugin_info, 'lnkdn_options', 'bws-linkedin' );
	}
}

/* Add help tab */
if ( ! function_exists( 'lnkdn_add_tabs' ) ) {
	function lnkdn_add_tabs() {
		$screen = get_current_screen();
		$args = array(
			'id' 	  => 'lnkdn',
			'section' => '201989376'
		);
		bws_help_tab( $screen, $args );
	}
}

if ( ! function_exists( 'lnkdn_uninstall' ) ) {
	function lnkdn_uninstall() {
		if ( ! function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$all_plugins = get_plugins();

		if ( ! array_key_exists( 'bws-linkedin-pro/bws-linkedin-pro.php', $all_plugins ) && 
			! array_key_exists( 'bws-linkedin-plus/bws-linkedin-plus.php', $all_plugins ) &&
			! array_key_exists( 'bws-social-buttons/bws-social-buttons.php', $all_plugins ) &&
			! array_key_exists( 'bws-social-buttons-pro/bws-social-buttons-pro.php', $all_plugins ) ) {

			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
				global $wpdb;
				$old_blog = $wpdb->blogid;
				/* Get all blog ids */
				$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
				foreach ( $blogids as $blog_id ) {
					switch_to_blog( $blog_id );
					delete_option( 'lnkdn_options' );
				}
				switch_to_blog( $old_blog );
			} else {
				delete_option( 'lnkdn_options' );
			}
		}

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );
		bws_delete_plugin( plugin_basename( __FILE__ ) );
	}
}

/* Calling a function add administrative menu. */
add_action( 'admin_menu', 'lnkdn_add_admin_menu' );
/* Initialization ##*/
add_action( 'init', 'lnkdn_init' );
add_action( 'admin_init', 'lnkdn_admin_init' );
add_action( 'plugins_loaded', 'lnkdn_plugins_loaded' );
/* Adding stylesheets */
add_action( 'wp_footer', 'lnkdn_js' );
add_action( 'admin_enqueue_scripts', 'lnkdn_admin_head' );
add_action( 'wp_enqueue_scripts', 'lnkdn_admin_head' );
add_filter( 'pgntn_callback', 'lnkdn_pagination_callback' );
/* Adding plugin buttons */
add_shortcode( 'bws_linkedin', 'lnkdn_shortcode' );
add_filter( 'widget_text', 'do_shortcode' );
add_filter( 'the_content', 'lnkdn_position' );
/* custom filter for bws button in tinyMCE */
add_filter( 'bws_shortcode_button_content', 'lnkdn_shortcode_button_content' );
/* Register widget */
add_action( 'widgets_init', 'lnkdn_register_main_widget' );
/* Adding class in 'body' Twenty Fifteen/Sixteen Theme for LinkedIn Buttons */
add_filter( 'body_class', 'lnkdn_add_body_class' );
/*## Additional links on the plugin page */
add_filter( 'plugin_action_links', 'lnkdn_action_links', 10, 2 );
add_filter( 'plugin_row_meta', 'lnkdn_register_plugin_links', 10, 2 );
/* Adding banner */
add_action( 'admin_notices', 'lnkdn_admin_notices' );
/* Plugin uninstall function */
register_uninstall_hook( __FILE__, 'lnkdn_uninstall' );
/* end ##*/