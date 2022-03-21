<?php
/**
Plugin Name: BestWebSoft's LinkedIn
Plugin URI: https://bestwebsoft.com/products/wordpress/plugins/linkedin/
Description: Add LinkedIn Share and Follow buttons to WordPress posts, pages and widgets.
Author: BestWebSoft
Text Domain: bws-linkedin
Domain Path: /languages
Version: 1.1.2
Author URI: https://bestwebsoft.com
License: GPLv3 or later
 */

/**
@ Copyright 2021  BestWebSoft  ( https://support.bestwebsoft.com )

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
		global $submenu, $lnkdn_plugin_info, $wp_version;

		$settings = add_menu_page( esc_html__( 'LinkedIn Settings', 'bws-linkedin' ), 'LinkedIn', 'manage_options', 'linkedin.php', 'lnkdn_settings_page', 'none' );
		add_submenu_page( 'linkedin.php', esc_html__( 'LinkedIn Settings', 'bws-linkedin' ), esc_html__( 'Settings', 'bws-linkedin' ), 'manage_options', 'linkedin.php', 'lnkdn_settings_page' );

		add_submenu_page( 'linkedin.php', 'BWS Panel', 'BWS Panel', 'manage_options', 'lnkdn-bws-panel', 'bws_add_menu_render' );
		/*pls */
		if ( isset( $submenu['linkedin.php'] ) ) {
			$submenu['linkedin.php'][] = array(
				'<span style="color:#d86463"> ' . esc_html__( 'Upgrade to Pro', 'bws-linkedin' ) . '</span>',
				'manage_options',
				'https://bestwebsoft.com/products/wordpress/plugins/linkedin/?k=c64e9f9106c1e15bd3f4ece9473fb80d&amp;pn=588&amp;v=' . $lnkdn_plugin_info['Version'] . '&wp_v=' . $wp_version,
			);
		}
		/* pls*/
		add_action( 'load-' . $settings, 'lnkdn_add_tabs' );
	}
}
/* end lnkdn_add_admin_menu ##*/

if ( ! function_exists( 'lnkdn_plugins_loaded' ) ) {
	function lnkdn_plugins_loaded() {
		/* Internationalization */
		load_plugin_textdomain( 'bws-linkedin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

/* Initialization */
if ( ! function_exists( 'lnkdn_init' ) ) {
	function lnkdn_init() {
		global $lnkdn_plugin_info, $lnkdn_lang_codes;

		if ( empty( $lnkdn_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$lnkdn_plugin_info = get_plugin_data( __FILE__ );
		}

		/*## add general functions */
		require_once dirname( __FILE__ ) . '/bws_menu/bws_include.php';
		bws_include_init( plugin_basename( __FILE__ ) );

		bws_wp_min_version_check( plugin_basename( __FILE__ ), $lnkdn_plugin_info, '4.5' );/* check compatible with current WP version ##*/

		/* Get options from the database */
		if ( ! is_admin() || ( isset( $_GET['page'] ) && ( 'linkedin.php' === $_GET['page'] || 'social-buttons.php' === $_GET['page'] ) ) ) {
			/* Get/Register and check settings for plugin */
			lnkdn_settings();
			$lnkdn_lang_codes = array(
				'en_US' => 'English',
				'ar_AE' => 'Arabic',
				'zh_CN' => 'Chinese - Simplified',
				'zh_TW' => 'Chinese - Traditional',
				'cs_CZ' => 'Czech',
				'da_DK' => 'Danish',
				'nl_NL' => 'Dutch',
				'fr_FR' => 'French',
				'de_DE' => 'German',
				'in_ID' => 'Indonesian',
				'it_IT' => 'Italian',
				'ja_JP' => 'Japanese',
				'ko_KR' => 'Korean',
				'ms_MY' => 'Malay',
				'no_NO' => 'Norwegian',
				'pl_PL' => 'Polish',
				'pt_BR' => 'Portuguese',
				'ro_RO' => 'Romanian',
				'ru_RU' => 'Russian',
				'es_ES' => 'Spanish',
				'sv_SE' => 'Swedish',
				'tl_PH' => 'Tagalog',
				'th_TH' => 'Thai',
				'tr_TR' => 'Turkish',
			);
		}
	}
}

/* Function for admin_init */
if ( ! function_exists( 'lnkdn_admin_init' ) ) {
	function lnkdn_admin_init() {
		global $bws_plugin_info, $lnkdn_plugin_info, $bws_shortcode_list, $pagenow, $lnkdn_options;

		/*## Function for bws menu */
		if ( empty( $bws_plugin_info ) ) {
			$bws_plugin_info = array(
				'id'      => '588',
				'version' => $lnkdn_plugin_info['Version'],
			);
		}

		/*pls */
		if ( 'plugins.php' === $pagenow ) {
			if ( function_exists( 'bws_plugin_banner_go_pro' ) ) {
				lnkdn_settings();
				bws_plugin_banner_go_pro( $lnkdn_options, $lnkdn_plugin_info, 'lnkdn', 'linkedin', '23b248c24d3fbef44d7ac493141591ab', '588', 'bws-linkedin' );
			}
		}
		/* ##*/ /* pls*/

		/* Add LinkedIn to global $bws_shortcode_list */
		$bws_shortcode_list['lnkdn'] = array(
			'name'        => 'LinkedIn Button',
			'js_function' => 'lnkdn_shortcode_init',
		);
	}
}

/*## Function for activation */
if ( ! function_exists( 'lnkdn_plugin_activate' ) ) {
	function lnkdn_plugin_activate() {
		/* registering uninstall hook */
		if ( is_multisite() ) {
			switch_to_blog( 1 );
			register_uninstall_hook( __FILE__, 'lnkdn_uninstall' );
			restore_current_blog();
		} else {
			register_uninstall_hook( __FILE__, 'lnkdn_uninstall' );
		}
	}
}
/* ##*/

if ( ! function_exists( 'lnkdn_settings' ) ) {
	function lnkdn_settings() {
		global $lnkdn_options, $lnkdn_plugin_info;

		/* install the option defaults */
		if ( ! get_option( 'lnkdn_options' ) ) {
			$options_defaults = lnkdn_get_options_default();
			add_option( 'lnkdn_options', $options_defaults );
		}

		$lnkdn_options = get_option( 'lnkdn_options' );

		if ( ! isset( $lnkdn_options['plugin_option_version'] ) || $lnkdn_options['plugin_option_version'] !== $lnkdn_plugin_info['Version'] ) {

			/*## */
			lnkdn_plugin_activate();
			/* ##*/
			$options_defaults                       = lnkdn_get_options_default();
			$lnkdn_options                          = array_merge( $options_defaults, $lnkdn_options );
			$lnkdn_options['plugin_option_version'] = $options_defaults['plugin_option_version'];

			/* show pro features */
			$lnkdn_options['hide_premium_options'] = array();
			update_option( 'lnkdn_options', $lnkdn_options );
		}
	}
}

if ( ! function_exists( 'lnkdn_get_options_default' ) ) {
	function lnkdn_get_options_default() {
		global $lnkdn_plugin_info;

		$options_default = array(
			'plugin_option_version'    => $lnkdn_plugin_info['Version'],
			'display_settings_notice'  => 1,
			'suggest_feature_banner'   => 1,
			'follow'                   => 0,
			'follow_count_mode'        => 'top',
			'follow_page_name'         => '',
			'homepage'                 => 1,
			'pages'                    => 1,
			'posts'                    => 1,
			'lang'                     => 'en_US',
			'position'                 => array( 'before_post' ),
			'use_multilanguage_locale' => 0,
			'share'                    => 0,
			'share_url'                => '',
		);

		return $options_default;
	}
}

/*## Add settings page in admin area */
if ( ! function_exists( 'lnkdn_settings_page' ) ) {
	function lnkdn_settings_page() {
		if ( ! class_exists( 'Bws_Settings_Tabs' ) ) {
			require_once dirname( __FILE__ ) . '/bws_menu/class-bws-settings.php';
		}
		require_once dirname( __FILE__ ) . '/includes/class-lnkdn-settings.php';
		$page = new Lnkdn_Settings_Tabs( plugin_basename( __FILE__ ) );
		if ( method_exists( $page, 'add_request_feature' ) ) {
			$page->add_request_feature();
		} ?>
		<div id="lnkdn_settings_form" class="wrap">
			<h1><?php esc_html_e( 'LinkedIn Settings', 'bws-linkedin' ); ?></h1>
			<noscript>
				<div class="error below-h2">
					<p><strong><?php esc_html_e( 'WARNING', 'bws-linkedin' ); ?>
							:</strong> <?php esc_html_e( 'The plugin works correctly only if JavaScript is enabled.', 'bws-linkedin' ); ?>
					</p>
				</div>
			</noscript>
			<?php $page->display_content(); ?>
		</div>
		<?php
	}
}

/* Function for forming buttons tags ##*/
if ( ! function_exists( 'lnkdn_return_button' ) ) {
	function lnkdn_return_button( $request ) {
		global $lnkdn_options;

		if ( empty( $lnkdn_options['share_url'] ) ) {
			$share_url = get_permalink();
		} else {
			$share_url = $lnkdn_options['share_url'];
		}

		if ( 'share' === $request ) {
			$share = '<div class="lnkdn-share-button">
						<script type="IN/Share" data-url="' . $share_url . '" data-counter=""></script>
					</div>';
			return $share;
		}

		if ( 'follow' === $request && '' !== $lnkdn_options['follow_page_name'] ) {
			$follow = '<div class="lnkdn-follow-button">
						<script type="IN/FollowCompany" data-id="' . $lnkdn_options['follow_page_name'] . '" data-counter="' . $lnkdn_options['follow_count_mode'] . '"></script>
					</div>';
			return $follow;
		}
	}
}

/* LinkedIn buttons on page */
if ( ! function_exists( 'lnkdn_position' ) ) {
	function lnkdn_position( $content ) {
		global $lnkdn_options;

		if ( is_feed() ) {
			return $content;
		}

		if ( ! empty( $lnkdn_options['position'] ) ) {
			$display_button = false;

			if ( ( ! is_home() && ! is_front_page() ) || 1 === intval( $lnkdn_options['homepage'] ) ) {
				if ( ( is_single() && 1 === intval( $lnkdn_options['posts'] ) ) || ( is_page() && 1 === intval( $lnkdn_options['pages'] ) ) || ( is_home() && 1 === intval( $lnkdn_options['homepage'] ) ) ) {
					$display_button = true;
				}
			}

			$display_button = apply_filters( 'lnkdn_button_in_the_content', $display_button );

			if ( $display_button ) {
				$share  = ( 1 === intval( $lnkdn_options['share'] ) ) ? lnkdn_return_button( 'share' ) : '';
				$follow = ( 1 === intval( $lnkdn_options['follow'] ) ) ? lnkdn_return_button( 'follow' ) : '';
				$button = '<div class="lnkdn_buttons">' . $share . $follow . '</div>';

				if ( in_array( 'before_post', $lnkdn_options['position'] ) ) {
					$content = $button . $content;
				}
				if ( in_array( 'after_post', $lnkdn_options['position'] ) ) {
					$content .= $button;
				}
			}
		}
		return $content;
	}
}

if ( ! function_exists( 'lnkdn_admin_head' ) ) {
	function lnkdn_admin_head() {
		global $lnkdn_plugin_info;
		wp_enqueue_style( 'lnkdn_icon', plugins_url( 'css/icon.css', __FILE__ ), array(), $lnkdn_plugin_info['Version'] );

		if ( ! is_admin() ) {
			wp_enqueue_style( 'lnkdn_stylesheet', plugins_url( 'css/style.css', __FILE__ ), array(), $lnkdn_plugin_info['Version'] );
			lnkdn_js();
		} elseif ( isset( $_GET['page'] ) && ( 'linkedin.php' === $_GET['page'] || 'social-buttons.php' === $_GET['page'] ) ) {
			wp_enqueue_style( 'lnkdn_stylesheet', plugins_url( 'css/style.css', __FILE__ ), array(), $lnkdn_plugin_info['Version'] );
			bws_enqueue_settings_scripts();
			bws_plugins_include_codemirror();
		}
	}
}

/* lnkdn script add */
if ( ! function_exists( 'lnkdn_js' ) ) {
	function lnkdn_js() {
		global $lnkdn_options, $lnkdn_shortcode_add_script, $lnkdn_js_added;

		if ( isset( $lnkdn_js_added ) ) {
			return;
		}

		if ( 1 === intval( $lnkdn_options['share'] ) || 1 === intval( $lnkdn_options['follow'] ) || isset( $lnkdn_shortcode_add_script ) || defined( 'BWS_ENQUEUE_ALL_SCRIPTS' ) ) {
			wp_enqueue_script( 'in.js', '//platform.linkedin.com/in.js', array(), null, true );

			$lnkdn_js_added = true;
		}
	}
}

if ( ! function_exists( 'lnkdn_add_lang_to_script' ) ) {
	function lnkdn_add_lang_to_script( $tag, $handle ) {
		global $lnkdn_options, $lnkdn_lang_codes, $mltlngg_current_language;

		if ( 'in.js' === $handle ) {

			if ( 1 === intval( $lnkdn_options['use_multilanguage_locale'] ) && isset( $mltlngg_current_language ) ) {
				if ( array_key_exists( $mltlngg_current_language, $lnkdn_lang_codes ) ) {
					$lnkdn_locale = $mltlngg_current_language;
				} else {
					$lnkdn_locale_from_multilanguage = str_replace( '_', '-', $mltlngg_current_language );
					if ( array_key_exists( $lnkdn_locale_from_multilanguage, $lnkdn_lang_codes ) ) {
						$lnkdn_locale = $lnkdn_locale_from_multilanguage;
					} else {
						$lnkdn_locale_from_multilanguage = explode( '_', $mltlngg_current_language );
						if ( is_array( $lnkdn_locale_from_multilanguage ) && array_key_exists( $lnkdn_locale_from_multilanguage[0], $lnkdn_lang_codes ) ) {
							$lnkdn_locale = $lnkdn_locale_from_multilanguage[0];
						}
					}
				}
			}

			if ( empty( $lnkdn_locale ) ) {
				$lnkdn_locale = $lnkdn_options['lang'];
			}
			$return_string = 'lang: ' . $lnkdn_locale;
			$tag           = preg_replace( ':(?=</script>):', " $return_string", $tag, 1 );
		}
		return $tag;

	}
}

if ( ! function_exists( 'lnkdn_pagination_callback' ) ) {
	function lnkdn_pagination_callback( $content ) {
		$content .= "if ( typeof( IN ) != 'undefined' ) { IN.parse(); }";
		return $content;
	}
}

/**
 * LinkedIn Buttons shortcode
 * [bws_linkedin display="share,follow"]
 */
if ( ! function_exists( 'lnkdn_shortcode' ) ) {
	function lnkdn_shortcode( $atts ) {
		global $lnkdn_options, $lnkdn_shortcode_add_script;

		$buttons        = '';
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
		lnkdn_js();

		return '<div class="lnkdn_buttons">' . $buttons . '</div>';
	}
}

/* add shortcode content */
if ( ! function_exists( 'lnkdn_shortcode_button_content' ) ) {
	function lnkdn_shortcode_button_content( $content ) {
		global $wp_version;
		?>
		<div id="lnkdn" style="display:none;">
			<fieldset>
				<label>
					<input type="checkbox" name="lnkdn_selected_share" value="share" checked="checked" />
					<?php esc_html_e( 'LinkedIn Share Button', 'bws-linkedin' ); ?>
				</label>
				<br />
				<label>
					<input type="checkbox" name="lnkdn_selected_follow" value="follow" checked="checked" />
					<?php esc_html_e( 'LinkedIn Follow Button', 'bws-linkedin' ); ?>
				</label>
				<input class="bws_default_shortcode" type="hidden" name="default" value='[bws_linkedin display="share,follow"]' />
				<div class="clear"></div>
			</fieldset>
		</div>
		<?php
		$script = "function lnkdn_shortcode_init() {
				( function( $ ) {
					$( '.mce-reset input[name^=\"lnkdn_selected\"]' ).change( function() {
						var result = '';
						$( '.mce-reset input[name^=\"lnkdn_selected\"]' ).each( function() {
							if ( $( this ).is( ':checked' ) ) {
								result += $( this ).val() + ',';
							}
						} );
						if ( '' == result ) {
							$( '.mce-reset #bws_shortcode_display' ).text( '' );
						} else {
							result = result.slice( 0, - 1 );
							$( '.mce-reset #bws_shortcode_display' ).text( '[bws_linkedin display=\"' + result + '\"]' );
						}
					} );
				} ) ( jQuery );
			}";
		wp_register_script( 'lnkdn_bws_shortcode_button', '' );
		wp_enqueue_script( 'lnkdn_bws_shortcode_button' );
		wp_add_inline_script( 'lnkdn_bws_shortcode_button', sprintf( $script ) );
	}
}

/* Adding class in 'body' Twenty Fifteen/Sixteen Theme for LinkedIn Buttons */
if ( ! function_exists( 'lnkdn_add_body_class' ) ) {
	function lnkdn_add_body_class( $classes ) {
		$current_theme = wp_get_theme();
		if ( 'Twenty Fifteen' === $current_theme->get( 'Name' ) || 'Twenty Sixteen' === $current_theme->get( 'Name' ) ) {
			$classes[] = 'lnkdn-button-certain-theme';
		}
		if ( 'Twenty Twelve' === $current_theme->get( 'Name' ) ) {
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
			if ( $file === $this_plugin ) {
				$settings_link = '<a href="admin.php?page=linkedin.php">' . esc_html__( 'Settings', 'bws-linkedin' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
		}
		return $links;
	}
}

if ( ! function_exists( 'lnkdn_register_plugin_links' ) ) {
	function lnkdn_register_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file === $base ) {
			if ( ! is_network_admin() ) {
				$links[] = '<a href="admin.php?page=linkedin.php">' . esc_html__( 'Settings', 'bws-linkedin' ) . '</a>';
			}
			$links[] = '<a href="https://support.bestwebsoft.com/hc/en-us/sections/201989376" target="_blank">' . esc_html__( 'FAQ', 'bws-linkedin' ) . '</a>';
			$links[] = '<a href="https://support.bestwebsoft.com">' . esc_html__( 'Support', 'bws-linkedin' ) . '</a>';
		}
		return $links;
	}
}

if ( ! function_exists( 'lnkdn_admin_notices' ) ) {
	function lnkdn_admin_notices() {
		global $hook_suffix, $lnkdn_plugin_info;

		if ( 'plugins.php' === $hook_suffix && ! is_network_admin() ) {
			bws_plugin_banner_to_settings( $lnkdn_plugin_info, 'lnkdn_options', 'bws-linkedin', 'admin.php?page=linkedin.php' );
		}

		if ( isset( $_GET['page'] ) && 'linkedin.php' === $_GET['page'] ) {
			bws_plugin_suggest_feature_banner( $lnkdn_plugin_info, 'lnkdn_options', 'bws-linkedin' );
		}
	}
}

/* Add help tab */
if ( ! function_exists( 'lnkdn_add_tabs' ) ) {
	function lnkdn_add_tabs() {
		$screen = get_current_screen();
		$args   = array(
			'id'      => 'lnkdn',
			'section' => '201989376',
		);
		bws_help_tab( $screen, $args );
	}
}

if ( ! function_exists( 'lnkdn_uninstall' ) ) {
	function lnkdn_uninstall() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

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

		require_once dirname( __FILE__ ) . '/bws_menu/bws_include.php';
		bws_include_init( plugin_basename( __FILE__ ) );
		bws_delete_plugin( plugin_basename( __FILE__ ) );
	}
}

/* Plugin uninstall function */
register_activation_hook( __FILE__, 'lnkdn_plugin_activate' );
/* Calling a function add administrative menu. */
add_action( 'admin_menu', 'lnkdn_add_admin_menu' );
/* Initialization ##*/
add_action( 'init', 'lnkdn_init' );
add_action( 'admin_init', 'lnkdn_admin_init' );
add_action( 'plugins_loaded', 'lnkdn_plugins_loaded' );
/* Adding stylesheets */
add_action( 'admin_enqueue_scripts', 'lnkdn_admin_head' );
add_action( 'wp_enqueue_scripts', 'lnkdn_admin_head' );
add_filter( 'script_loader_tag', 'lnkdn_add_lang_to_script', 10, 2 );
add_filter( 'pgntn_callback', 'lnkdn_pagination_callback' );
/* Adding plugin buttons */
add_shortcode( 'bws_linkedin', 'lnkdn_shortcode' );
add_filter( 'the_content', 'lnkdn_position' );
/* custom filter for bws button in tinyMCE */
add_filter( 'bws_shortcode_button_content', 'lnkdn_shortcode_button_content' );
/* Adding class in 'body' Twenty Fifteen/Sixteen Theme for LinkedIn Buttons */
add_filter( 'body_class', 'lnkdn_add_body_class' );
/*## Additional links on the plugin page */
add_filter( 'plugin_action_links', 'lnkdn_action_links', 10, 2 );
add_filter( 'plugin_row_meta', 'lnkdn_register_plugin_links', 10, 2 );
/* Adding banner */
add_action( 'admin_notices', 'lnkdn_admin_notices' );
/* end ##*/
