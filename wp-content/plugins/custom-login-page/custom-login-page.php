<?php
/*
Plugin Name: A5 Custom Login Page
Description: Just customize your login page (or that of your community etc.) by giving the WP login page a different look, with your own logo and special colours and styles.
Version: 	1.9.2
Author: Waldemar Stoffel
Author URI: http://www.waldemarstoffel.com
Plugin URI: http://wasistlos.waldemarstoffel.com/plugins-fur-wordpress/a5-custom-login-page
License: GPL3
Text Domain: custom-login-page
*/

/*  Copyright 2011 - 2014 Waldemar Stoffel  (email: stoffel@atelier-fuenf.de)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

/**
 * ------------------------------------------------------
 *  ACKNOWLEDGEMENTS
 * ------------------------------------------------------
 * 
 * Thx to Jorge Ballesteros - http://motivando.me
 * for the translation into Spanish
 *
 * Thx to Branco Radenovich - http://webhostinggeeks.com/blog
 * for the translation into Slovak
 *
 * ------------------------------------------------------
 */

/**
 *
 * Stop direct call
 *
 */
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) die('Sorry, you don&#39;t have direct access to this page.');

define( 'CLP_PATH', plugin_dir_path(__FILE__) );
if (!class_exists('A5_FormField')) require_once CLP_PATH.'class-lib/A5_FormFieldClass.php';
if (!class_exists('A5_OptionPage')) require_once CLP_PATH.'class-lib/A5_OptionPageClass.php';

class A5_CustomLoginPage {
	
	private static $options;
	
	const language_file = 'custom-login-page';
	
	function __construct(){
		
		register_activation_hook(__FILE__, array($this, 'start_clp')); 
		register_deactivation_hook(__FILE__, array($this, 'unset_clp'));	
		
		add_filter('plugin_row_meta', array($this, 'clp_register_links'), 10, 2);
		add_filter('plugin_action_links', array($this, 'clp_plugin_action_links'), 10, 2);
		
		add_action('login_head', array($this, 'clp_login_css'));
		add_action('admin_init', array($this, 'clp_register_admin_extras'));
		add_action('admin_enqueue_scripts', array($this, 'clp_admin_css'));
		add_action('wp_ajax_clp_save_settings', array($this, 'clp_save_settings'));
		add_action('wp_ajax_clp_import_settings', array($this, 'clp_import_settings'));
		add_action('init', array($this, 'clp_add_rewrite'));
		add_action('template_redirect', array($this, 'clp_css_template'));
		
		if (is_multisite()) :
		
			$plugins = get_site_option('active_sitewide_plugins');
			
			if (isset($plugins[plugin_basename(__FILE__)])) :
		
				add_action('network_admin_menu', array($this, 'clp_site_admin_menu'));
				
				self::$options = get_site_option('clp_options');
				
				if (self::$options['version'] !='1.9.2') :
				
					self::$options['version']='1.9.2';
					
					update_site_option('clp_options', self::$options);
					
				endif;
				
			else:
			
				add_action('admin_menu', array($this, 'clp_admin_menu'));
			
				self::$options = get_option('clp_options');
				
				if (self::$options['version'] !='1.9.2') :
					
					self::$options['version']='1.9.2';
					
					update_option('clp_options', self::$options);
					
				endif;
				
			endif;
			
		else:
			
			add_action('admin_menu', array($this, 'clp_admin_menu'));
			
			self::$options = get_option('clp_options');
			
			if (self::$options['version'] !='1.9.2') :
				
				self::$options['version']='1.9.2';
				
				update_option('clp_options', self::$options);
				
			endif;
		
		endif;
		
		if (!empty(self::$options['url'])) add_filter('login_headerurl', array($this, 'clp_headerurl'));
		if (!empty(self::$options['title'])) add_filter('login_headertitle', array($this, 'clp_headertitle'));
		if (!empty(self::$options['error_custom_message'])) add_filter('login_errors', array($this, 'clp_custom_error'));
		if (!empty(self::$options['logout_custom_message'])) add_filter('login_messages', array($this, 'clp_custom_logout'));
		
		/**
		 *
		 * Importing language file
		 *
		 */
		load_plugin_textdomain(self::language_file, false , basename(dirname(__FILE__)).'/languages');
		
	}	
	
	/**
	 *
	 * Adds links to the plugin page
	 *
	 */
	function clp_register_links($links, $file) {
		
		$base = plugin_basename(__FILE__);
		if ($file == $base) {
			$links[] = '<a href="http://wordpress.org/extend/plugins/custom-login-page/faq/" target="_blank">'.__('FAQ', self::language_file).'</a>';
			$links[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=32XGSBKTQNNHA" target="_blank">'.__('Donate', self::language_file).'</a>';
		}
		
		return $links;
	
	}
	
	function clp_plugin_action_links( $links, $file ) {
		
		$base = plugin_basename(__FILE__);
		
		if ($file == $base) array_unshift($links, '<a href="'.admin_url( 'themes.php?page=clp-settings' ).'">'.__('Settings', self::language_file).'</a>');
	
		return $links;
	
	}
	
	/**
	 *
	 * Changes the link behind the logo
	 *
	 */
	function clp_headerurl() {
		
		return self::$options['url'];
		
	}
	
	/**
	 *
	 * Changes the Title tag of the logo
	 *
	 */
	function clp_headertitle() {
		
		return self::$options['title'];
		
	}
	
	/**
	 *
	 * Changes the Error Message
	 *
	 */
	function clp_custom_error() {
		
		return self::$options['error_custom_message'];
		
	}
	
	/**
	 *
	 * Changes the Logout Message
	 *
	 */
	function clp_custom_logout() {
	
		return self::$options['logout_custom_message'];
		
	}
	
	/**
	 *
	 * Adds the style sheet to the login page
	 *
	 */
	function clp_login_css() {
		
		if (self::$options['inline'] != true) :
		
			echo "<link rel='stylesheet' id='a5-custom-login-css' href='".get_bloginfo('url')."/?clpfile=css&ver=".self::$options['version']."' type='text/css' media='all' />\r\n";
		
		else :
		
			echo "\r\n<style>\r\n".$this->clp_get_the_style()."\r\n</style>\r\n";
		
		endif;
		
	}

	/**
	 *
	 * Setting version on activation
	 *
	 */
	function start_clp() {
		
		$screen = get_current_screen();
		
		if (is_multisite() && $screen->is_network) :
		
			add_site_option('clp_options', array('version' => '1.9.2'));
			
		else:
		
			add_option('clp_options', array('version' => '1.9.2'));
			
		endif;
	
	}
	
	/**
	 *
	 * Cleaning on deactivation
	 *
	 */
	function unset_clp() {
		
		$screen = get_current_screen();
		
		if (is_multisite() && $screen->is_network) :
		
			delete_site_option('clp_options');
			
		else:
		
			delete_option('clp_options');
			
		endif;
		
	}
	
	/**
	 *
	 * Creating Settings Page
	 *
	 */
	function clp_admin_menu() {
		
		add_theme_page('A5 Custom Login Page', '<img alt="" src="'.plugins_url('custom-login-page/img/a5-icon-11.png').'"> A5 Custom Login Page', 'administrator', 'clp-settings', array($this, 'clp_options_page'));	
		
	}
	
	/**
	 *
	 * Creating Multisite Settings Page
	 *
	 */
	function clp_site_admin_menu() {
		
		add_menu_page('A5 Custom Login Page', 'A5 Custom Login Page', 'administrator', 'clp-settings', array($this, 'clp_options_page'), plugins_url('custom-login-page/img/a5-icon-16.png'));	
		
	}	
	
	/**
	 *
	 * register styles and scripts for settings page
	 *
	 */
	function clp_register_admin_extras() {
		 
		 wp_register_style('a5-admin', plugins_url('/css/a5-admin-css.css', __FILE__), false, self::$options['version'], 'all');
		 wp_register_script('clp-admin-script', plugins_url('/js/clp-admin.js', __FILE__), array('jquery'), self::$options['version'], true);
		 wp_register_script('a5-colorpicker', plugins_url('/js/jscolor/jscolor.js', __FILE__), false, '1.4.0', true);
		 wp_register_script('a5-admin-tabs', plugins_url('/js/tabcontent.js', __FILE__), false, '2.2', false);
	
	}
	
	/**
	 *
	 * Adding scripts and stylesheet to settings page
	 *
	 */
	function clp_admin_css($hook) {
		
		if ($hook != 'appearance_page_clp-settings' && $hook != 'toplevel_page_clp-settings') return;
		
		wp_enqueue_style('a5-admin');
		wp_enqueue_script('clp-admin-script');
		wp_enqueue_script('a5-colorpicker');
		wp_enqueue_script('a5-admin-tabs');
		wp_localize_script('clp-admin-script', 'message', $this->clp_localize_admin());	
		
	}
	
	/**
	 *
	 * Adding l10n to the script
	 *
	 */
	function clp_localize_admin() {
		
		return array ('saving' => __('Saving...', self::language_file));
		
	}
	
	/**
	 *
	 * settings page
	 *
	 */
	function clp_options_page() {
	
		?>
	<div class="wrap">
	<table width="100%" cellpadding="2" cellspacing="0"><tr><td><a href="<?php _e('http://wasistlos.waldemarstoffel.com/plugins-fur-wordpress/a5-custom-login-page', self::language_file); ?>"><div id="a5-logo" class="icon32" style="background: url('<?php echo plugins_url('/img/a5-icon-34.png', __FILE__);?>');"></div></a><h2>
	A5 Custom Login Page <?php _e('Settings', self::language_file); ?></h2></td><td valign="middle">&nbsp;</td>
	</tr></table>
	<table>
	<tr>
	<td valign="top" width="200">
    	<?php
    	
			$tabs[] = array('main', __('Body', self::language_file), true);
			$tabs[] = array('logindiv', __('Login Container', self::language_file));
			$tabs[] = array('loginform', __('Login Form', self::language_file));
			$tabs[] = array('button', __('Button', self::language_file));
			$tabs[] = array('message', __('Messages and Input Fields', self::language_file));
			$tabs[] = array('link', __('Links', self::language_file));
			$tabs[] = array('impex', __('Import / Export', self::language_file));
			$tabs[] = array('debug', __('Debug', self::language_file));
			
			a5_navigation('a5-pagetabs', $tabs);
		
		?>
	
	</td>
	<td valign="top" width="100%">
        <?php
		
		// main form
		
		a5_open_page('main');
		
		wp_nonce_field('save_main','mainnonce'); 
		
		a5_open_section();
			
		$fields = array (
		
		a5_text_field('logo', 'logo', @self::$options['logo'], __('Logo URL', self::language_file), array('style' => 'width: 95%;'), false),
		a5_url_field('url', 'url', @self::$options['url'], __('URL to link to', self::language_file), array('style' => 'width: 95%;'), false),
		a5_text_field('title', 'title', @self::$options['title'], __('Title tag of the logo', self::language_file), array('style' => 'width: 95%;'), false)
		);
		
		a5_container_left($fields);
					
		$special = __('You can leave any of the fields empty to keep the default settings of Wordpress.', self::language_file);
		
		$text = array (
		
		__('You can enter the url of the logo, that you want to have in place of the WP logo on the login screen. Just upload any picture (best is a png or gif with transparent background) via the uploader on the Media section and copy the url of that file here.', self::language_file),
		__('In the URL field, you enter the URL to which the logo should link.', self::language_file)
		);
		
		a5_container_right(__('Logo', self::language_file), $text, $special, array ('mainmsg', 2));			
		  
		a5_next_section();
		  
		$fields = array(
		
		a5_number_field('logo_width', 'logo_width', @self::$options['logo_width'], __('Width of the Logo (in px)', self::language_file), array('step' => 1), false),
		a5_number_field('logo_height', 'logo_height', @self::$options['logo_height'], __('Height of the Logo (in px)', self::language_file), array('step' => 1), false),
		a5_number_field('h1_width', 'h1_width', @self::$options['h1_width'], __('Width of the Logo Container (in px)', self::language_file), array('step' => 1), false),
		a5_number_field('h1_height', 'h1_height', @self::$options['h1_height'], __('Height of the Logo Container (in px)', self::language_file), array('step' => 1), false),
		a5_text_field('h1_margin', 'h1_margin', @self::$options['h1_margin'], __('Margin of the Logo Container (CSS)', self::language_file), array(), false),
		a5_text_field('h1_padding', 'h1_padding', @self::$options['h1_padding'], __('Padding of the Logo Container (CSS)', self::language_file), array(), false)
		);
		
		a5_container_left($fields);
		
		$text = array(
		
		__('If your logo is larger than the default WP-logo (274px by 63px), you can enter the width and the height of it here.', self::language_file),
		__('The width and height of the logo-container are by default 326px and 67px. They are used to move the Logo around, since the background-position is always &#39;center top&#39;.', self::language_file),
		sprintf(__('With the margin of the logo, you can position the logo more precisely. Give a CSS value here, i.e. %s to locate it 180 px left.', self::language_file), '&#39;0 0 0 -180px&#39;'),
		sprintf(__('With the padding of the logo, you can position the shadow more precisely. Give a CSS value here, i.e. %s to get rid of it completely.', self::language_file), '&#39;0px&#39;')
		);
		
		a5_container_right(__('Position and Size of the Logo', self::language_file), $text, $special);
		  
		a5_next_section();
		
		$class = "color {hash:true,caps:false,required:false,pickerPosition:'right'}";
		
		$fields = array(
		
		a5_number_field('h1_corner', 'h1_corner', @self::$options['h1_corner'], __('Rounded Corners (in px)', self::language_file), array('step' => 1), false),
		a5_number_field('h1_shadow_x', 'h1_shadow_x', @self::$options['h1_shadow_x'], __('Shadow (x-direction in px)', self::language_file), array('step' => 1), false),
		a5_number_field('h1_shadow_y', 'h1_shadow_y', @self::$options['h1_shadow_y'], __('Shadow (y-direction in px)', self::language_file), array('step' => 1), false),
		a5_number_field('h1_shadow_softness', 'h1_shadow_softness', @self::$options['h1_shadow_softness'], __('Shadow (softness in px)', self::language_file), array('step' => 1), false),
		a5_text_field('h1_shadow_color', 'h1_shadow_color', @self::$options['h1_shadow_color'], __('Shadow Colour', self::language_file), array('class' => $class), false)
		);
		
		a5_container_left($fields);
		
		$text = array(
		
		__('Here you can style the logo a bit. Give it a shadow or round corners if you like', self::language_file)
		);
		
		a5_container_right(__('Styling of the Logo', self::language_file), $text, $special);
		  
		a5_next_section();
			
		$options = array(array('no-repeat', 'no-repeat'), array('repeat-x', 'repeat-x'), array('repeat-y', 'repeat-y'));
		
		$fields = array(
		
		a5_text_field('body_background', 'body_background', @self::$options['body_background'], __('Background Picture', self::language_file), array('style' => 'width: 95%;'), false),
		a5_select('body_img_repeat', 'body_img_repeat', $options, @self::$options['body_img_repeat'], __('Background Repeat', self::language_file), __('default', self::language_file), array('style' => 'width: 135px;'), false),
		a5_text_field('body_img_pos', 'body_img_pos', @self::$options['body_img_pos'], __('Position of the Background Picture', self::language_file), array('style' => 'width: 95%;'), false),
		a5_text_field('body_bg_color1', 'body_bg_color1', @self::$options['body_bg_color1'], __('Background Colour', self::language_file), array('class' => $class), false),
		a5_text_field('body_bg_color2', 'body_bg_color2', @self::$options['body_bg_color2'], __('Second Background Colour (for Gradient)', self::language_file), array('class' => $class), false),
		a5_text_field('body_bg_size', 'body_bg_size', @self::$options['body_bg_size'], __('Background Size', self::language_file), array('style' => 'width: 95%;'), false)
		);
		
		a5_container_left($fields);
		
		$text = array(
		
		__('You can enter the url of the background picture, that you want to have on the login page. Just upload any picture via the uploader on the Media section and copy the url of that file here. Leave it empty, if you don&#39;t want a picture. Background images are tiled by default. You can select the direction of repeating the image or to not repeat it. The position of the image can be something like &#39;100px 50%&#39; or &#39center top&#39;. If you want the background picture to cover the whole screen, put &#39;cover&#39; as size. Otherwise, fill in any css (like &#39;auto 150px&#39;).', self::language_file),
		__('In the last section, you choose the background colour and the colour of the text in the html body element. If you give two background colours, you can create a gradient. Colour no. 1 will always be up.', self::language_file)
		);
		
		a5_container_right(__('Body', self::language_file), $text, $special);
		  
		a5_close_section();
		
		a5_submit_container('main_save', __('Save Changes'), __('Save style', self::language_file));
		  
		// login container
		
		a5_next_page('logindiv');
		
		wp_nonce_field('save_logindiv','logindivnonce');
		
		a5_open_section();
		
		$border_style = array(array('none', 'none'), array('dotted', 'dotted'), array('dashed', 'dashed'), array('solid', 'solid'), array('double', 'double'), array('groove', 'groove'), array('ridge', 'rigde'), array('inset', 'inset'), array('outset', 'outset'));
		
		$fields = array(
		
		a5_text_field('logindiv_background', 'logindiv_background', @self::$options['logindiv_background'], __('Background Picture', self::language_file), array('style' => 'width: 95%;'), false),
		a5_select('logindiv_img_repeat', 'logindiv_img_repeat', $options, @self::$options['logindiv_img_repeat'], __('Background Repeat', self::language_file), __('default', self::language_file), array('style' => 'width: 135px;'), false),
		a5_text_field('logindiv_img_pos', 'logindiv_img_pos', @self::$options['logindiv_img_pos'], __('Position of the Background Picture', self::language_file), array('style' => 'width: 95%;'), false),
		a5_text_field('logindiv_bg_color1', 'logindiv_bg_color1', @self::$options['logindiv_bg_color1'], __('Background Colour', self::language_file), array('class' => $class), false),
		a5_text_field('logindiv_bg_color2', 'logindiv_bg_color2', @self::$options['logindiv_bg_color2'], __('Second Background Colour (for Gradient)', self::language_file), array('class' => $class), false),
		a5_text_field('logindiv_text_color', 'logindiv_text_color', @self::$options['logindiv_text_color'], __('Text Colour', self::language_file), array('class' => $class), false),
		a5_number_field('logindiv_transparency', 'logindiv_transparency', @self::$options['logindiv_transparency'], __('Transparency (in percent)', self::language_file), array('step' => 1, 'min' => 0, 'max' => 100), false),
		a5_select('logindiv_border_style', 'logindiv_border_style', $border_style, @self::$options['logindiv_border_style'], __('Border Style', self::language_file), __('choose a border style', self::language_file), array('style' => 'width: 220px;'), false),
		a5_number_field('logindiv_border_width', 'logindiv_border_width', @self::$options['logindiv_border_width'], __('Border Width (in px)', self::language_file), array('step' => 1), false),
		a5_text_field('logindiv_border_color', 'logindiv_border_color', @self::$options['logindiv_border_color'], __('Border Colour', self::language_file), array('class' => $class), false),
		a5_number_field('logindiv_border_round', 'logindiv_border_round', @self::$options['logindiv_border_round'], __('Rounded Corners (in px)', self::language_file), array('step' => 1), false),
		a5_number_field('logindiv_shadow_x', 'logindiv_shadow_x', @self::$options['logindiv_shadow_x'], __('Shadow (x-direction in px)', self::language_file), array('step' => 1), false),
		a5_number_field('logindiv_shadow_y', 'logindiv_shadow_y', @self::$options['logindiv_shadow_y'], __('Shadow (y-direction in px)', self::language_file), array('step' => 1), false),
		a5_number_field('logindiv_shadow_softness', 'logindiv_shadow_softness', @self::$options['logindiv_shadow_softness'], __('Shadow (softness in px)', self::language_file), array('step' => 1), false),
		a5_text_field('logindiv_shadow_color', 'logindiv_shadow_color', @self::$options['logindiv_shadow_color'], __('Shadow Colour', self::language_file), array('class' => $class), false)
		);

		a5_container_left($fields);
		
		$text = array(
		
		__('You can enter the url of the background picture, that you want to have on the login container. Just upload any picture via the uploader on the Media section and copy the url of that file here. Leave it empty, if you don&#39;t want a picture. Background images are tiled by default. You can select the direction of repeating the image or to not repeat it. The position of the image can be something like &#39;100px 50%&#39; or &#39;center top&#39;.', self::language_file),
		__('In the next section, you choose the background colour and the colour of the text in the login container. If you give two background colours, you can create a gradient. Colour no. 1 will always be up.', self::language_file),
		__('Choose a border, if wanting one. Define style, width and whether or not, you want to have rounded corners (is not supported by all browsers).', self::language_file),
		__('At last, give the container a shadow (is not supported by all browsers).', self::language_file)
		);
		
		a5_container_right(__('Login Container', self::language_file), $text, $special, array('logindivmsg', 2));
		
		a5_next_section();
		
		$fields = array(
		
		a5_number_field('logindiv_left', 'logindiv_left', @self::$options['logindiv_left'], __('Position (x-direction in px)', self::language_file), array('step' => 1), false),
		a5_number_field('logindiv_top', 'logindiv_top', @self::$options['logindiv_top'], __('Position (y-direction in px)', self::language_file), array('step' => 1), false),
		a5_number_field('logindiv_width', 'logindiv_width', @self::$options['logindiv_width'], __('Width (in px)', self::language_file), array('step' => 1), false),
		a5_number_field('logindiv_height', 'logindiv_height', @self::$options['logindiv_height'], __('Height (in px)', self::language_file), array('step' => 1), false),
		a5_text_field('logindiv_padding', 'logindiv_padding', @self::$options['logindiv_padding'], __('Padding', self::language_file), false, false),
		a5_text_field('logindiv_margin', 'logindiv_margin', @self::$options['logindiv_margin'], __('Margin', self::language_file), false, false)
		);
		
		a5_container_left($fields);
		
		$text = array(
		
		__('Position and Size of the Login Container', self::language_file),
		__('Here you can give the whole login container a position. If you enter &#39;0&#39; in both of the fields, it will be in the top left corner of the screen.', self::language_file),
		__('The Padding and Margin are given as css value. I.e. &#39;144px 0 0&#39; (which is the default padding of the login container).', self::language_file)
		);
		
		a5_container_right(__('Position and Size of the Login Container', self::language_file), $text, $special);

		a5_close_section();
		
		a5_submit_container('logindiv_save', __('Save Changes'), __('Save style', self::language_file));
		
		//loginform
		
		a5_next_page('loginform');
		
		a5_open_section();
		
		wp_nonce_field('save_loginform','loginformnonce');
		
		$fields = array(
		
		a5_text_field('loginform_background', 'loginform_background', @self::$options['loginform_background'], __('Background Picture', self::language_file), array('style' => 'width: 95%;'), false),
		a5_select('loginform_img_repeat', 'loginform_img_repeat', $options, @self::$options['loginform_img_repeat'], __('Background Repeat', self::language_file), __('default', self::language_file), array('style' => 'width: 135px;'), false),
		a5_text_field('loginform_img_pos', 'loginform_img_pos', @self::$options['loginform_img_pos'], __('Position of the Background Picture', self::language_file), array('style' => 'width: 95%;'), false),
		a5_text_field('loginform_bg_color1', 'loginform_bg_color1', @self::$options['loginform_bg_color1'], __('Background Colour', self::language_file), array('class' => $class), false),
		a5_text_field('loginform_bg_color2', 'loginform_bg_color2', @self::$options['loginform_bg_color2'], __('Second Background Colour (for Gradient)', self::language_file), array('class' => $class), false),
		a5_text_field('loginform_text_color', 'loginform_text_color', @self::$options['loginform_text_color'], __('Text Colour', self::language_file), array('class' => $class), false),
		a5_number_field('loginform_transparency', 'loginform_transparency', @self::$options['loginform_transparency'], __('Transparency (in percent)', self::language_file), array('step' => 1, 'min' => 0, 'max' => 100), false),
		a5_select('loginform_border_style', 'loginform_border_style', $border_style, @self::$options['loginform_border_style'], __('Border Style', self::language_file), __('choose a border style', self::language_file), array('style' => 'width: 220px;'), false),
		a5_number_field('loginform_border_width', 'loginform_border_width', @self::$options['loginform_border_width'], __('Border Width (in px)', self::language_file), array('step' => 1), false),
		a5_text_field('loginform_border_color', 'loginform_border_color', @self::$options['loginform_border_color'], __('Border Colour', self::language_file), array('class' => $class), false),
		a5_number_field('loginform_border_round', 'loginform_border_round', @self::$options['loginform_border_round'], __('Rounded Corners (in px)', self::language_file), array('step' => 1), false),
		a5_text_field('loginform_margin', 'loginform_margin', @self::$options['loginform_margin'], __('Margin', self::language_file), false, false),
		a5_text_field('loginform_padding', 'loginform_padding', @self::$options['loginform_padding'], __('Padding', self::language_file), false, false),
		a5_number_field('loginform_shadow_x', 'loginform_shadow_x', @self::$options['loginform_shadow_x'], __('Shadow (x-direction in px)', self::language_file), array('step' => 1), false),
		a5_number_field('loginform_shadow_y', 'loginform_shadow_y', @self::$options['loginform_shadow_y'], __('Shadow (y-direction in px)', self::language_file), array('step' => 1), false),
		a5_number_field('loginform_shadow_softness', 'loginform_shadow_softness', @self::$options['loginform_shadow_softness'], __('Shadow (softness in px)', self::language_file), array('step' => 1), false),
		a5_text_field('loginform_shadow_color', 'loginform_shadow_color', @self::$options['loginform_shadow_color'], __('Shadow Colour', self::language_file), array('class' => $class), false)
		);
		
		a5_container_left($fields);
		
		$text = array(
		
		__('You can enter the url of the background picture, that you want to have in the login form. Just upload any picture via the uploader on the Media section and copy the url of that file here. Leave it empty, if you don&#39;t want a picture. Background images are tiled by default. You can select the direction of repeating the image or to not repeat it. The position of the image can be something like &#39;100px 50%&#39; or &#39center top&#39;.', self::language_file),
		__('In the next section, you choose the background colour and the colour of the text in the login form. If you give two background colours, you can create a gradient. Colour no. 1 will always be up.', self::language_file),
		__('Choose a border, if wanting one. Define style, width and whether or not, you want to have rounded corners (is not supported by all browsers).', self::language_file),
		__('Margin and Padding are given as css values. The form has a left margin of 8px by default and a padding of 26px 24px 46px. By changing the top and the bottom padding, you can stretch the form in its length.', self::language_file),
		__('Margin and Padding are given as css values. The form has a left margin of 8px by default and a padding of 26px 24px 46px. By changing the top and the bottom padding, you can stretch the form in its length.', self::language_file),
		__('At last, give the form a shadow (is not supported by all browsers).', self::language_file)
		);
		
		a5_container_right(__('Login Form', self::language_file), $text, $special, array('loginmsg', 2));
		
		a5_close_section();
		
		a5_submit_container('loginform_save', __('Save Changes'), __('Save style', self::language_file));

		// button
		
		a5_next_page('button');
		
		a5_open_section();
		
		wp_nonce_field('save_button','buttonnonce');
		
		$fields = array(
		
		a5_text_field('button_bg_color1', 'button_bg_color1', @self::$options['button_bg_color1'], __('Background Colour', self::language_file), array('class' => $class), false),
		a5_text_field('button_bg_color2', 'button_bg_color2', @self::$options['button_bg_color2'], __('Second Background Colour (for Gradient)', self::language_file), array('class' => $class), false),
		a5_text_field('button_text_color', 'button_text_color', @self::$options['button_text_color'], __('Text Colour', self::language_file), array('class' => $class), false),
		a5_text_field('button_border_color', 'button_border_color', @self::$options['button_border_color'], __('Border Colour', self::language_file), array('class' => $class), false),
		a5_text_field('btn_hover_bg_color1', 'btn_hover_bg_color1', @self::$options['btn_hover_bg_color1'], __('Hover Background Colour', self::language_file), array('class' => $class), false),
		a5_text_field('btn_hover_bg_color2', 'btn_hover_bg_color2', @self::$options['btn_hover_bg_color2'], __('Second Hover Background Colour (for Gradient)', self::language_file), array('class' => $class), false),
		a5_text_field('btn_hover_text_color', 'btn_hover_text_color', @self::$options['btn_hover_text_color'], __('Hover Text Colour', self::language_file), array('class' => $class), false),
		a5_text_field('btn_hover_border_color', 'btn_hover_border_color', @self::$options['btn_hover_border_color'], __('Hover Border Colour', self::language_file), array('class' => $class), false),
		
		);
		
		a5_container_left($fields);
		
		$text = array(
		
		__('Enter the background, text and border colour of the submit button here. The button will look static if you don&#39;t give values for the hover state of it. If you want to have a gradient, enter two background colours. The first one will be up then.', self::language_file)
		);
		
		a5_container_right(__('Submit Button', self::language_file), $text, $special, array('buttonmsg', 2));
		
		a5_close_section();
		
		a5_submit_container('button_save', __('Save Changes'), __('Save style', self::language_file));
		
		// messages
		
		a5_next_page('message');
		
		a5_open_section();
		
		wp_nonce_field('save_message','messagenonce');
		
		$fields = array(
		
		a5_text_field('loggedout_text_color', 'loggedout_text_color', @self::$options['loggedout_text_color'], __('Text Colour', self::language_file), array('class' => $class), false),
		a5_text_field('loggedout_bg_color', 'loggedout_bg_color', @self::$options['loggedout_bg_color'], __('Background Colour', self::language_file), array('class' => $class), false),
		a5_text_field('loggedout_border_color', 'loggedout_border_color', @self::$options['loggedout_border_color'], __('Border Colour', self::language_file), array('class' => $class), false),
		a5_number_field('loggedout_transparency', 'loggedout_transparency', @self::$options['loginform_transparency'], __('Transparency (in percent)', self::language_file), array('step' => 1, 'min' => 0, 'max' => 100), false),
		a5_text_field('logout_custom_message', 'logout_custom_message', @self::$options['logout_custom_message'], __('Logout Message', self::language_file), array('style' => 'width: 95%;'), false)
		);
		
		a5_container_left($fields);
		
		$text = array(
		
		__('This changes the the text container, that appears, when you have successfully logged out.', self::language_file),
		__('Furthermore, you can enter your own logout message here. You can make your blog a bit more personal like that.', self::language_file)
		);
		
		a5_container_right(__('Logged Out Message', self::language_file), $text, $special, array('messagemsg', 2));
		
		a5_next_section();
		
		$fields = array(
		
		a5_text_field('error_text_color', 'error_text_color', @self::$options['error_text_color'], __('Text Colour', self::language_file), array('class' => $class), false),
		a5_text_field('error_bg_color', 'error_bg_color', @self::$options['error_bg_color'], __('Background Colour', self::language_file), array('class' => $class), false),
		a5_text_field('error_border_color', 'error_border_color', @self::$options['error_border_color'], __('Border Colour', self::language_file), array('class' => $class), false),
		a5_number_field('error_transparency', 'error_transparency', @self::$options['loginform_transparency'], __('Transparency (in percent)', self::language_file), array('step' => 1, 'min' => 0, 'max' => 100), false),
		a5_text_field('error_custom_message', 'error_custom_message', @self::$options['error_custom_message'], __('Error Message', self::language_file), array('style' => 'width: 95%;'), false)
		);
		
		a5_container_left($fields);
		
		$text = array(
		
		__('This changes the text container, that appears, when you get an error logging in.', self::language_file),
		__('Furthermore, you can enter your own error message here. By default, Wordpress says that either the username or the password is wrong, which is perhaps a hint to foreigners that you don&#39;t wish to give.', self::language_file)
		);
		
		a5_container_right(__('Error Message', self::language_file), $text, $special);
		
		a5_next_section();
		
		$fields = array(
		
		a5_text_field('input_text_color', 'input_text_color', @self::$options['input_text_color'], __('Text Colour', self::language_file), array('class' => $class), false),
		a5_text_field('input_bg_color', 'input_bg_color', @self::$options['input_bg_color'], __('Background Colour', self::language_file), array('class' => $class), false),
		a5_text_field('input_border_color', 'input_border_color', @self::$options['input_border_color'], __('Border Colour', self::language_file), array('class' => $class), false)
		);
		
		a5_container_left($fields);
		
		$text = array(
		
		__('This changes the colours of the name and password fields of the log in form.', self::language_file)
		);
		
		a5_container_right(__('Input Fields', self::language_file), $text, $special);
		
		a5_close_section();
		
		a5_submit_container('message_save', __('Save Changes'), __('Save style', self::language_file));
		
		// links
		
		a5_next_page('link');
		
		a5_open_section();
		
		wp_nonce_field('save_link','linknonce');
		
		$textdeco = array(array('none', 'none'), array('underline', 'underline'), array('overline', 'overline'), array('line-through', 'line-through'), array('blink', 'blink'));
		
		$fields = array(
		a5_checkbox('hide_nav', 'hide_nav', self::$options['hide_nav'], __('Hide register and lost password links.', self::language_file), array(), false, false),
		a5_checkbox('hide_backlink', 'hide_backlink', self::$options['hide_backlink'], __('Hide back to block link.', self::language_file), array('space' => true), false, false),
		a5_text_field('link_text_color', 'link_text_color', @self::$options['link_text_color'], __('Text Colour', self::language_file), array('class' => $class), false),
		a5_select('link_textdecoration', 'link_textdecoration', $textdeco, @self::$options['link_textdecoration'], __('Text Decoration', self::language_file), __('choose a text decoration', self::language_file), array('style' => 'width: 220px;'), false),
		a5_number_field('link_shadow_x', 'link_shadow_x', @self::$options['link_shadow_x'], __('Shadow (x-direction in px)', self::language_file), array('step' => 1), false),
		a5_number_field('link_shadow_y', 'link_shadow_y', @self::$options['link_shadow_y'], __('Shadow (y-direction in px)', self::language_file), array('step' => 1), false),
		a5_number_field('link_shadow_softness', 'link_shadow_softness', @self::$options['link_shadow_softness'], __('Shadow (softness in px)', self::language_file), array('step' => 1), false),
		a5_text_field('link_shadow_color', 'link_shadow_color', @self::$options['link_shadow_color'], __('Shadow Colour', self::language_file), array('class' => $class), false),
		a5_text_field('hover_text_color', 'hover_text_color', @self::$options['hover_text_color'], __('Hover Text Colour', self::language_file), array('class' => $class), false),
		a5_select('hover_textdecoration', 'hover_textdecoration', $textdeco, @self::$options['hover_textdecoration'], __('Hover Text Decoration', self::language_file), __('choose a text decoration', self::language_file), array('style' => 'width: 220px;'), false),
		a5_number_field('hover_shadow_x', 'hover_shadow_x', @self::$options['hover_shadow_x'], __('Shadow (x-direction in px)', self::language_file), array('step' => 1), false),
		a5_number_field('hover_shadow_y', 'hover_shadow_y', @self::$options['hover_shadow_y'], __('Shadow (y-direction in px)', self::language_file), array('step' => 1), false),
		a5_number_field('hover_shadow_softness', 'hover_shadow_softness', @self::$options['hover_shadow_softness'], __('Shadow (softness in px)', self::language_file), array('step' => 1), false),
		a5_text_field('hover_shadow_color', 'hover_shadow_color', @self::$options['hover_shadow_color'], __('Shadow Colour', self::language_file), array('class' => $class), false),
		a5_text_field('link_size', 'link_size', @self::$options['link_size'], __('Font Size', self::language_file), array(), false)
		);
		
		a5_container_left($fields);

		$text = array(
		
		__('Style the links by giving a text colour, text decoration and shadow for the link and the hover style.', self::language_file),
		sprintf(__('For the font size, give a css value, such as %1$s or %2$s.', self::language_file), '<em>&#39;12px&#39;</em>', '<em>&#39;1em&#39;</em>'),
		__('If you hide the links under the login form, the styles still affect the links in the error message container.', self::language_file)
		);
		
		a5_container_right(__('Links', self::language_file), $text, $special, array('linkmsg', 2));
		
		a5_close_section();
		
		a5_submit_container('link_save', __('Save Changes'), __('Save style', self::language_file));
		
		a5_next_page('impex');
		
		// impex (do better!!!)
		
		a5_open_section();
		
		?>
          <div class="a5-option-container-full">
			<?php wp_nonce_field('save_impex','impexnonce'); ?>
			<h2><?php _e('Export Settings'); ?></h2>
			<p><?php _e('Export the current A5 Custom Login Page settings and download them as a text file. This text file can be imported into this or another A5 Custom Login Page installation:'); ?></p>
			<p><?php echo '<a class="button" href="' . get_bloginfo('url') . '/?clpfile=export" id="settings-download"><strong>'. __('Export &amp; Download') .'</strong> A5 Custom Login Page Settings File</a>'; ?></p>
			<p><?php echo __('The file will be named').' <code>a5-clp-' . str_replace('.','-', $_SERVER['SERVER_NAME']) . '-' . date('y') . date('m') . date('d') . '.txt</code>. '.__('After you downloaded it, you can (but don&#39;t need to) rename the file to something more meaningful.'); ?></p>
			<div id="impexmsg"></div>
		  </div>
		  </div>
		  <div class="a5-option-container">
		  <div class="a5-option-container-full">
			<h2><?php _e('Import Settings'); ?></h2>
			<p class="error"><?php _e('This will overlay any existing setting, you already have.'); ?></p>
			<p><textarea name="clp_import" cols="80" rows="10" id="clp_import"></textarea></p>
		  </div>
		  <?php
		  	
		a5_close_section();
	  
		a5_submit_container('impex_save', __('Save Changes'), __('Save Settings', self::language_file));
		
		a5_next_page('debug');
		
		// debug (do better!!!)
	
		a5_open_section();
		
		?>
		  <div class="a5-option-container-full">
          <?php wp_nonce_field('save_debug','debugnonce'); ?>
			<h2><?php _e('Debugging', self::language_file); ?></h2>
			<p class="error"><?php _e('There seem to be problems with the virtual stylesheet in some environments. By choosing to write the styles inline, those can be avoided.'); ?></p>
            <div id="debugmsg"></div>
            <?php 
			
				a5_checkbox('inline', 'inline', self::$options['inline'], __('Check, to write styles inline instead of to a virtual CSS file.', self::language_file), array(), false);
			
			?>
		  </div>
		  <?php
		  	
		a5_close_section();
		
		a5_submit_container('debug_save', __('Save Changes'), __('Import Settings', self::language_file));
		
		a5_close_page();
		  
		  ?>
	</td>
	</tr>
	</table>
    </div><!-- / class=wrap -->
	<?php
	
	a5_nav_js('a5');
		
	}
	
	/**
	 *
	 * saving the settings
	 *
	 */
	function clp_save_settings() {
		
		$section=$_POST['section'];
		
		switch ($section) :
			
			case 'main':
			
			if (!wp_verify_nonce($_POST['mainnonce'],'save_main')) :
				
				$output = '<p class="error">'.__('Error in Datatransfer.', self::language_file).'</p>';
			
			else :
			
				self::$options['logo'] = $_POST['logo'];
				self::$options['url'] = $_POST['url'];
				self::$options['title'] = $_POST['title'];
				self::$options['logo_width'] = $_POST['logo_width'];
				self::$options['logo_height'] = $_POST['logo_height'];
				self::$options['h1_width'] = $_POST['h1_width'];
				self::$options['h1_height'] = $_POST['h1_height'];
				self::$options['h1_margin'] = $_POST['h1_margin'];
				self::$options['h1_padding'] = $_POST['h1_padding'];
				self::$options['h1_corner'] = $_POST['h1_corner'];
				self::$options['h1_shadow_x'] = $_POST['h1_shadow_x'];
				self::$options['h1_shadow_y'] = $_POST['h1_shadow_y'];
				self::$options['h1_shadow_softness'] = $_POST['h1_shadow_softness'];
				self::$options['h1_shadow_color'] = $_POST['h1_shadow_color'];
				self::$options['body_background'] = $_POST['body_background'];
				self::$options['body_img_repeat'] = $_POST['body_img_repeat'];
				self::$options['body_img_pos'] = $_POST['body_img_pos'];
				self::$options['body_bg_color1'] = $_POST['body_bg_color1'];
				self::$options['body_bg_color2'] = $_POST['body_bg_color2'];
				self::$options['body_bg_size'] = $_POST['body_bg_size'];
								
				if (is_plugin_active_for_network(plugin_basename(__FILE__))) :
				
					update_site_option('clp_options', self::$options);
				
				else : 
				
					update_option('clp_options', self::$options);
					
				endif;
				
				$output='<p class="save">'.__('Settings saved', self::language_file).'</p>';
			
			endif;
			
				echo $output;
				die();
			
			break;
	
			case 'logindiv':
			
			if (!wp_verify_nonce($_POST['logindivnonce'],'save_logindiv')) :
				
				$output = '<p class="error">'.__('Error in Datatransfer.', self::language_file).'</p>';
			
			else :
			
				self::$options['logindiv_background'] = $_POST['logindiv_background'];
				self::$options['logindiv_img_repeat'] = $_POST['logindiv_img_repeat'];
				self::$options['logindiv_img_pos'] = $_POST['logindiv_img_pos'];
				self::$options['logindiv_bg_color1'] = $_POST['logindiv_bg_color1'];
				self::$options['logindiv_bg_color2'] = $_POST['logindiv_bg_color2'];
				self::$options['logindiv_text_color'] = $_POST['logindiv_text_color'];
				self::$options['logindiv_transparency'] = $_POST['logindiv_transparency'];
				self::$options['logindiv_border_style'] = $_POST['logindiv_border_style'];
				self::$options['logindiv_border_width'] = $_POST['logindiv_border_width'];
				self::$options['logindiv_border_color'] = $_POST['logindiv_border_color'];
				self::$options['logindiv_border_round'] = $_POST['logindiv_border_round'];
				self::$options['logindiv_shadow_x'] = $_POST['logindiv_shadow_x'];
				self::$options['logindiv_shadow_y'] = $_POST['logindiv_shadow_y'];
				self::$options['logindiv_shadow_softness'] = $_POST['logindiv_shadow_softness'];
				self::$options['logindiv_shadow_color'] = $_POST['logindiv_shadow_color'];
				self::$options['logindiv_left'] = $_POST['logindiv_left'];
				self::$options['logindiv_top'] = $_POST['logindiv_top'];
				self::$options['logindiv_width'] = $_POST['logindiv_width'];
				self::$options['logindiv_height'] = $_POST['logindiv_height'];
				self::$options['logindiv_padding'] = $_POST['logindiv_padding'];
				self::$options['logindiv_margin'] = $_POST['logindiv_margin'];
				
				if (is_plugin_active_for_network(plugin_basename(__FILE__))) :
				
					update_site_option('clp_options', self::$options);
				
				else : 
				
					update_option('clp_options', self::$options);
					
				endif;
				
				$output='<p class="save">'.__('Settings saved', self::language_file).'</p>';
			
			endif;
				
				echo $output;
				die();
			
			break;
			
			case 'loginform':
			
			if (!wp_verify_nonce($_POST['loginformnonce'],'save_loginform')) :
				
				$output = '<p class="error">'.__('Error in Datatransfer.', self::language_file).'</p>';
			
			else :
			
				self::$options['loginform_background'] = $_POST['loginform_background'];
				self::$options['loginform_img_repeat'] = $_POST['loginform_img_repeat'];
				self::$options['loginform_img_pos'] = $_POST['loginform_img_pos'];
				self::$options['loginform_bg_color1'] = $_POST['loginform_bg_color1'];
				self::$options['loginform_bg_color2'] = $_POST['loginform_bg_color2'];
				self::$options['loginform_text_color'] = $_POST['loginform_text_color'];
				self::$options['loginform_transparency'] = $_POST['loginform_transparency'];				
				self::$options['loginform_border_style'] = $_POST['loginform_border_style'];
				self::$options['loginform_border_width'] = $_POST['loginform_border_width'];
				self::$options['loginform_border_color'] = $_POST['loginform_border_color'];
				self::$options['loginform_border_round'] = $_POST['loginform_border_round'];
				self::$options['loginform_margin'] = $_POST['loginform_margin'];
				self::$options['loginform_padding'] = $_POST['loginform_padding'];				
				self::$options['loginform_shadow_x'] = $_POST['loginform_shadow_x'];
				self::$options['loginform_shadow_y'] = $_POST['loginform_shadow_y'];
				self::$options['loginform_shadow_softness'] = $_POST['loginform_shadow_softness'];
				self::$options['loginform_shadow_color'] = $_POST['loginform_shadow_color'];
				
				if (is_plugin_active_for_network(plugin_basename(__FILE__))) :
				
					update_site_option('clp_options', self::$options);
				
				else : 
				
					update_option('clp_options', self::$options);
					
				endif;
				
				$output='<p class="save">'.__('Settings saved', self::language_file).'</p>';
			
			endif;
			
				echo $output;
				die();
			
			break;
			
			case 'button':
			
			if (!wp_verify_nonce($_POST['buttonnonce'],'save_button')) :
				
				$output = '<p class="error">'.__('Error in Datatransfer.', self::language_file).'</p>';
			
			else :
			
				self::$options['button_bg_color1'] = $_POST['button_bg_color1'];
				self::$options['button_bg_color2'] = $_POST['button_bg_color2'];
				self::$options['button_text_color'] = $_POST['button_text_color'];
				self::$options['button_border_color'] = $_POST['button_border_color'];
				self::$options['btn_hover_bg_color1'] = $_POST['btn_hover_bg_color1'];
				self::$options['btn_hover_bg_color2'] = $_POST['btn_hover_bg_color2'];
				self::$options['btn_hover_text_color'] = $_POST['btn_hover_text_color'];
				self::$options['btn_hover_border_color'] = $_POST['btn_hover_border_color'];
				
				if (is_plugin_active_for_network(plugin_basename(__FILE__))) :
				
					update_site_option('clp_options', self::$options);
				
				else : 
				
					update_option('clp_options', self::$options);
					
				endif;
				
				$output='<p class="save">'.__('Settings saved', self::language_file).'</p>';
			
			endif;
			
				echo $output;
				die();
			
			break;
			
			case 'message':
			
			if (!wp_verify_nonce($_POST['messagenonce'],'save_message')) :
				
				$output = '<p class="error">'.__('Error in Datatransfer.', self::language_file).'</p>';
			
			else :
			
				self::$options['loggedout_text_color'] = $_POST['loggedout_text_color'];
				self::$options['loggedout_bg_color'] = $_POST['loggedout_bg_color'];
				self::$options['loggedout_border_color'] = $_POST['loggedout_border_color'];
				self::$options['loggedout_transparency'] = $_POST['loggedout_transparency'];
				self::$options['logout_custom_message'] = $_POST['logout_custom_message'];
				self::$options['error_text_color'] = $_POST['error_text_color'];
				self::$options['error_bg_color'] = $_POST['error_bg_color'];
				self::$options['error_border_color'] = $_POST['error_border_color'];
				self::$options['error_transparency'] = $_POST['error_transparency'];
				self::$options['error_custom_message'] = $_POST['error_custom_message'];
				self::$options['input_text_color'] = $_POST['input_text_color'];
				self::$options['input_bg_color'] = $_POST['input_bg_color'];
				self::$options['input_border_color'] = $_POST['input_border_color'];
				
				if (is_plugin_active_for_network(plugin_basename(__FILE__))) :
				
					update_site_option('clp_options', self::$options);
				
				else : 
				
					update_option('clp_options', self::$options);
					
				endif;
				
				$output='<p class="save">'.__('Settings saved', self::language_file).'</p>';
			
			endif;
			
				echo $output;
				die();
			
			break;
			
			case 'link':
			
			if (!wp_verify_nonce($_POST['linknonce'],'save_link')) :
				
				$output = '<p class="error">'.__('Error in Datatransfer.', self::language_file).'</p>';
			
			else :
			
				self::$options['hide_nav'] = $_POST['hide_nav'];
				self::$options['hide_backlink'] = $_POST['hide_backlink'];
				self::$options['link_text_color'] = $_POST['link_text_color'];
				self::$options['link_textdecoration'] = $_POST['link_textdecoration'];
				self::$options['link_shadow_x'] = $_POST['link_shadow_x'];
				self::$options['link_shadow_y'] = $_POST['link_shadow_y'];
				self::$options['link_shadow_softness'] = $_POST['link_shadow_softness'];
				self::$options['link_shadow_color'] = $_POST['link_shadow_color'];
				self::$options['hover_text_color'] = $_POST['hover_text_color'];
				self::$options['hover_textdecoration'] = $_POST['hover_textdecoration'];
				self::$options['hover_shadow_x'] = $_POST['hover_shadow_x'];
				self::$options['hover_shadow_y'] = $_POST['hover_shadow_y'];
				self::$options['hover_shadow_softness'] = $_POST['hover_shadow_softness'];
				self::$options['hover_shadow_color'] = $_POST['hover_shadow_color'];
				self::$options['link_size'] = $_POST['link_size'];
				
				if (is_plugin_active_for_network(plugin_basename(__FILE__))) :
				
					update_site_option('clp_options', self::$options);
				
				else : 
				
					update_option('clp_options', self::$options);
					
				endif;
					
				$output='<p class="save">'.__('Settings saved', self::language_file).'</p>';
			
			endif;
			
				echo $output;
				die();
			
			break;
			
			case 'debug':
			
			if (!wp_verify_nonce($_POST['debugnonce'],'save_debug')) :
				
				$output = '<p class="error">'.__('Error in Datatransfer.', self::language_file).'</p>';
			
			else :
			
				self::$options['inline'] = $_POST['inline'];
				
				if (is_plugin_active_for_network(plugin_basename(__FILE__))) :
				
					update_site_option('clp_options', self::$options);
				
				else : 
				
					update_option('clp_options', self::$options);
					
				endif;
					
				$output='<p class="save">'.__('Settings saved', self::language_file).'</p>';
			
			endif;
			
				echo $output;
				die();
			
			break;
			
			default: break;
			
		endswitch;
		
	}
	
	/**
	 *
	 * importing the settings
	 *
	 */
	function clp_import_settings() {
		
		if (!wp_verify_nonce($_POST['impexnonce'],'save_impex')) :
		
			$output = '<p class="error">'.__('Error in Datatransfer.', self::language_file).'</p>';
			
		else:
		
			$clp_import_options = stripslashes($_POST['clp_import']);
			
			self::$options = json_decode($clp_import_options, true);
			
			if (self::$options['log'] != 'original A5 CLP file') :
			
				$msg = '<p class="error">'.__('This doesn&#39;t seem to be a valid settings file.').'</p>';
			
			else:
			
				unset(self::$options['log']);
				
				if (is_plugin_active_for_network(plugin_basename(__FILE__))) :
				
					update_site_option('clp_options', self::$options);
				
				else : 
				
					update_option('clp_options', self::$options);
					
				endif;
				
				$msg = '<p class="save">'.__('Settings successfully imported.').'</p>';
			
			endif;
			
		endif;
		
		echo $msg;
		
		die();
		
	}
	
	/**
	 *
	 * Printing the dss
	 *
	 */
	function clp_get_the_style() {
		
		# collecting variables
		
		$eol = "\r\n";
		$tab = "\t";
		
		# body.login
		
		$body_style = '';
			
		if (!empty(self::$options['body_background'])) $body_style .= $eol.$tab.'background-image: url('.self::$options['body_background'].');';
		if (!empty(self::$options['body_img_repeat'])) $body_style .= $eol.$tab.'background-repeat: '.self::$options['body_img_repeat'].';';
		if (!empty(self::$options['body_img_pos'])) $body_style .= $eol.$tab.'background-position: '.self::$options['body_img_pos'].';';
		if (!empty(self::$options['body_bg_size'])) :
		
			$body_style .= $eol.$tab.'-webkit-background-size: '.self::$options['body_bg_size'].';';
			$body_style .= $eol.$tab.'-moz-background-size: '.self::$options['body_bg_size'].';';
			$body_style .= $eol.$tab.'-o-background-size: '.self::$options['body_bg_size'].';';
			$body_style .= $eol.$tab.'background-size: '.self::$options['body_bg_size'].';';
		
		endif;
		
		if (!empty(self::$options['body_bg_color1'])) $body_style .= $eol.$tab.'background-color: '.self::$options['body_bg_color1'].';';	
		if (!empty(self::$options['body_bg_color2'])) :
			
			$body_style .= $eol.$tab.'background-image: -webkit-gradient(linear, left top, left bottom, from('.self::$options['body_bg_color1'].'), to('.self::$options['body_bg_color2'].'));';
			$body_style .= $eol.$tab.'background-image: -webkit-linear-gradient(top, '.self::$options['body_bg_color1'].', '.self::$options['body_bg_color2'].');';
			$body_style .= $eol.$tab.'background-image: -moz-linear-gradient(top, '.self::$options['body_bg_color1'].', '.self::$options['body_bg_color2'].');';
			$body_style .= $eol.$tab.'background-image: -ms-linear-gradient(top, '.self::$options['body_bg_color1'].', '.self::$options['body_bg_color2'].');';
			$body_style .= $eol.$tab.'background-image: -o-linear-gradient(top, '.self::$options['body_bg_color1'].', '.self::$options['body_bg_color2'].');';
			$body_style .= $eol.$tab.'background-image: -linear-gradient(top, '.self::$options['body_bg_color1'].', '.self::$options['body_bg_color2'].');';
			
		endif;
		
		# .login h1 a
		
		$h1_style = '';
		
		if (!empty(self::$options['logo'])) :
		
			$bg_width = (!empty(self::$options['logo_width'])) ? self::$options['logo_width'] : '274';
			$bg_height = (!empty(self::$options['logo_height'])) ? self::$options['logo_height'] : '63';
			$h1_width = (!empty(self::$options['h1_width'])) ? self::$options['h1_width'] : '326';
			$h1_height = (!empty(self::$options['h1_height'])) ? self::$options['h1_height'] : '67';
		
			$h1_style .= $eol.$tab.'background-image: url('.self::$options['logo'].');';
			$h1_style .= $eol.$tab.'background-position: center top;';
			$h1_style .= $eol.$tab.'background-repeat: no-repeat;';
			$h1_style .= $eol.$tab.'background-size: '.$bg_width.'px '.$bg_height.'px;';
			$h1_style .= $eol.$tab.'width: '.$h1_width.'px;';
			$h1_style .= $eol.$tab.'height: '.$h1_height.'px;';
			
			if (!empty(self::$options['h1_margin'])) $h1_style .= $eol.$tab.'margin: '.self::$options['h1_margin'].';';
			
			if (!empty(self::$options['h1_padding'])) $h1_style .= $eol.$tab.'padding: '.self::$options['h1_padding'].';';
			
			if (!empty(self::$options['h1_corner'])) :
			
				$h1_style .= $eol.$tab.'-webkit-border-radius: '.self::$options['h1_corner'].'px;';
				$h1_style .= $eol.$tab.'-moz-border-radius: '.self::$options['h1_corner'].'px;';
				$h1_style .= $eol.$tab.'border-radius: '.self::$options['h1_corner'].'px;';
				
			endif;
			
			if (!empty(self::$options['h1_shadow_x']) || self::$options['h1_shadow_x']=='0') :
				
				$h1_style .= $eol.$tab.'-webkit-box-shadow: '.self::$options['h1_shadow_x'].'px '.self::$options['h1_shadow_y'].'px '.self::$options['h1_shadow_softness'].'px '.self::$options['h1_shadow_color'].';';
				$h1_style .= $eol.$tab.'-moz-box-shadow: '.self::$options['h1_shadow_x'].'px '.self::$options['h1_shadow_y'].'px '.self::$options['h1_shadow_softness'].'px '.self::$options['h1_shadow_color'].';';
				$h1_style .= $eol.$tab.'box-shadow: '.self::$options['h1_shadow_x'].'px '.self::$options['h1_shadow_y'].'px '.self::$options['h1_shadow_softness'].'px '.self::$options['h1_shadow_color'].';';
				
			endif;
			
		endif;	
		
		# #login
		
		$logindiv_style = '';
		$label_style = '';
		
		if (!empty(self::$options['logindiv_top']) || !empty(self::$options['logindiv_left']) || self::$options['logindiv_top']=='0' || self::$options['logindiv_left']=='0') $logindiv_style .= $eol.$tab.'position: relative;';
		if (!empty(self::$options['logindiv_top']) || self::$options['logindiv_top']=='0') $logindiv_style .= $eol.$tab.'top: '.self::$options['logindiv_top'].'px;';
		if (!empty(self::$options['logindiv_left']) || self::$options['logindiv_left']=='0') $logindiv_style .= $eol.$tab.'left: '.self::$options['logindiv_left'].'px;';
		if (!empty(self::$options['logindiv_bg_color1'])) $logindiv_style .= $eol.$tab.'background-color: '.self::$options['logindiv_bg_color1'].';';
		if (!empty(self::$options['logindiv_bg_color2'])) :
			
			$logindiv_style .= $eol.$tab.'background-image: -webkit-gradient(linear, left top, left bottom, from('.self::$options['logindiv_bg_color1'].'), to('.self::$options['logindiv_bg_color2'].'));';
			$logindiv_style .= $eol.$tab.'background-image: -webkit-linear-gradient(top, '.self::$options['logindiv_bg_color1'].', '.self::$options['logindiv_bg_color2'].');';
			$logindiv_style .= $eol.$tab.'background-image: -moz-linear-gradient(top, '.self::$options['logindiv_bg_color1'].', '.self::$options['logindiv_bg_color2'].');';
			$logindiv_style .= $eol.$tab.'background-image: -ms-linear-gradient(top, '.self::$options['logindiv_bg_color1'].', '.self::$options['logindiv_bg_color2'].');';
			$logindiv_style .= $eol.$tab.'background-image: -o-linear-gradient(top, '.self::$options['logindiv_bg_color1'].', '.self::$options['logindiv_bg_color2'].');';
			$logindiv_style .= $eol.$tab.'background-image: -linear-gradient(top, '.self::$options['logindiv_bg_color1'].', '.self::$options['logindiv_bg_color2'].');';
			
		endif;
		if (!empty(self::$options['logindiv_transparency'])) :
			$logindiv_style .= $eol.$tab.'-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity='.self::$options['logindiv_transparency'].')";';
			$logindiv_style .= $eol.$tab.'filter: alpha(Opacity='.self::$options['logindiv_transparency'].');';
			$logindiv_style .= $eol.$tab.'-moz-opacity: '.(self::$options['logindiv_transparency']/100).';';
			$logindiv_style .= $eol.$tab.'-khtml-opacity: '.(self::$options['logindiv_transparency']/100).';';
			$logindiv_style .= $eol.$tab.'opacity: '.(self::$options['logindiv_transparency']/100).';';
		endif;
		if (!empty(self::$options['logindiv_background'])) $logindiv_style .= $eol.$tab.'background-image: url('.self::$options['logindiv_background'].');';
		if (!empty(self::$options['logindiv_img_repeat'])) $logindiv_style .= $eol.$tab.'background-repeat: '.self::$options['logindiv_img_repeat'].';';
		if (!empty(self::$options['logindiv_img_pos'])) $logindiv_style .= $eol.$tab.'background-position: '.self::$options['logindiv_img_pos'].';';
		if (!empty(self::$options['logindiv_border_style'])) $logindiv_style .= $eol.$tab.'border: '.self::$options['logindiv_border_style'].' '.self::$options['logindiv_border_width'].'px '.self::$options['logindiv_border_color'].';';
		if (!empty(self::$options['logindiv_border_round'])) :
			
			$logindiv_style .= $eol.$tab.'-webkit-border-radius: '.self::$options['logindiv_border_round'].'px;';
			$logindiv_style .= $eol.$tab.'-moz-border-radius: '.self::$options['logindiv_border_round'].'px;';
			$logindiv_style .= $eol.$tab.'border-radius: '.self::$options['logindiv_border_round'].'px;';
			
		endif;
		if (!empty(self::$options['logindiv_shadow_x']) || self::$options['logindiv_shadow_x']=='0') :
			
			$logindiv_style .= $eol.$tab.'-webkit-box-shadow: '.self::$options['logindiv_shadow_x'].'px '.self::$options['logindiv_shadow_y'].'px '.self::$options['logindiv_shadow_softness'].'px '.self::$options['logindiv_shadow_color'].';';
			$logindiv_style .= $eol.$tab.'-moz-box-shadow: '.self::$options['logindiv_shadow_x'].'px '.self::$options['logindiv_shadow_y'].'px '.self::$options['logindiv_shadow_softness'].'px '.self::$options['logindiv_shadow_color'].';';
			$logindiv_style .= $eol.$tab.'box-shadow: '.self::$options['logindiv_shadow_x'].'px '.self::$options['logindiv_shadow_y'].'px '.self::$options['logindiv_shadow_softness'].'px '.self::$options['logindiv_shadow_color'].';';
			
		endif;
		if (!empty(self::$options['logindiv_width'])) $logindiv_style .= $eol.$tab.'width: '.self::$options['logindiv_width'].'px;';
		if (!empty(self::$options['logindiv_height'])) $logindiv_style .= $eol.$tab.'height: '.self::$options['logindiv_height'].'px;';
		if (!empty(self::$options['logindiv_padding'])) $logindiv_style .= $eol.$tab.'padding: '.self::$options['logindiv_padding'].';';
		if (!empty(self::$options['logindiv_margin'])) $logindiv_style .= $eol.$tab.'margin: '.self::$options['logindiv_margin'].';';
		
		if (!empty(self::$options['logindiv_text_color'])) :
			
			$logindiv_style .= $eol.$tab.'color: '.self::$options['logindiv_text_color'].';';
			$label_style .= $eol.$tab.'color: '.self::$options['logindiv_text_color'].';';
			
		endif;
		
		# .login form
		
		$loginform_style = '';
		
		if (isset(self::$options['loginform_transparency']) && self::$options['loginform_transparency'] == '0') :
		
			$loginform_style .= $eol.$tab.'background: transparent;';
		
		else:
			
			if (!empty(self::$options['loginform_bg_color1'])) $loginform_style .= $eol.$tab.'background-color: '.self::$options['loginform_bg_color1'].';';
			if (!empty(self::$options['loginform_bg_color2'])) :
				
				$loginform_style .= $eol.$tab.'background-image: -webkit-gradient(linear, left top, left bottom, from('.self::$options['loginform_bg_color1'].'), to('.self::$options['loginform_bg_color2'].'));';
				$loginform_style .= $eol.$tab.'background-image: -webkit-linear-gradient(top, '.self::$options['loginform_bg_color1'].', '.self::$options['loginform_bg_color2'].');';
				$loginform_style .= $eol.$tab.'background-image: -moz-linear-gradient(top, '.self::$options['loginform_bg_color1'].', '.self::$options['loginform_bg_color2'].');';
				$loginform_style .= $eol.$tab.'background-image: -ms-linear-gradient(top, '.self::$options['loginform_bg_color1'].', '.self::$options['loginform_bg_color2'].');';
				$loginform_style .= $eol.$tab.'background-image: -o-linear-gradient(top, '.self::$options['loginform_bg_color1'].', '.self::$options['loginform_bg_color2'].');';
				$loginform_style .= $eol.$tab.'background-image: -linear-gradient(top, '.self::$options['loginform_bg_color1'].', '.self::$options['loginform_bg_color2'].');';
				
			endif;
			
			if (!empty(self::$options['loginform_transparency'])) :
				$loginform_style .= $eol.$tab.'-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity='.self::$options['loginform_transparency'].')";';
				$loginform_style .= $eol.$tab.'filter: alpha(Opacity='.self::$options['loginform_transparency'].');';
				$loginform_style .= $eol.$tab.'-moz-opacity: '.(self::$options['loginform_transparency']/100).';';
				$loginform_style .= $eol.$tab.'-khtml-opacity: '.(self::$options['loginform_transparency']/100).';';
				$loginform_style .= $eol.$tab.'opacity: '.(self::$options['loginform_transparency']/100).';';
			endif;
			
		endif;
			
		if (!empty(self::$options['loginform_background'])) $loginform_style .= $eol.$tab.'background-image: url('.self::$options['loginform_background'].');';
		if (!empty(self::$options['loginform_img_repeat'])) $loginform_style .= $eol.$tab.'background-repeat: '.self::$options['loginform_img_repeat'].';';
		if (!empty(self::$options['loginform_img_pos'])) $loginform_style .= $eol.$tab.'background-position: '.self::$options['loginform_img_pos'].';';
		if (!empty(self::$options['loginform_border_style']) && !empty(self::$options['loginform_border_width'])) $loginform_style .= $eol.$tab.'border: '.self::$options['loginform_border_style'].' '.self::$options['loginform_border_width'].'px '.self::$options['loginform_border_color'].';';
		if (isset(self::$options['loginform_border_style']) && self::$options['loginform_border_style'] == 'none') $loginform_style .= $eol.$tab.'border: medium none;';
		if (!empty(self::$options['loginform_border_round'])) :
			
			$loginform_style .= $eol.$tab.'-webkit-border-radius: '.self::$options['loginform_border_round'].'px;';
			$loginform_style .= $eol.$tab.'-moz-border-radius: '.self::$options['loginform_border_round'].'px;';
			$loginform_style .= $eol.$tab.'border-radius: '.self::$options['loginform_border_round'].'px;';
			
		endif;
		if (!empty(self::$options['loginform_shadow_x']) || self::$options['loginform_shadow_x'] == '0') :
			
			$loginform_style .= $eol.$tab.'-webkit-box-shadow: '.self::$options['loginform_shadow_x'].'px '.self::$options['loginform_shadow_y'].'px '.self::$options['loginform_shadow_softness'].'px '.self::$options['loginform_shadow_color'].';';
			$loginform_style .= $eol.$tab.'-moz-box-shadow: '.self::$options['loginform_shadow_x'].'px '.self::$options['loginform_shadow_y'].'px '.self::$options['loginform_shadow_softness'].'px '.self::$options['loginform_shadow_color'].';';
			$loginform_style .= $eol.$tab.'box-shadow: '.self::$options['loginform_shadow_x'].'px '.self::$options['loginform_shadow_y'].'px '.self::$options['loginform_shadow_softness'].'px '.self::$options['loginform_shadow_color'].';';
		endif;
		
		if (!empty(self::$options['loginform_margin'])) $loginform_style .= $eol.$tab.'margin: '.self::$options['loginform_margin'].';';
		if (!empty(self::$options['loginform_padding'])) $loginform_style .= $eol.$tab.'padding: '.self::$options['loginform_padding'].';';		
		
		if (!empty(self::$options['loginform_text_color'])) :
			
			$loginform_style .= $eol.$tab.'color: rgb('.$this->rgb_color(self::$options['loginform_text_color']).');'.$eol.$tab.'color: rgba('.$this->rgb_color(self::$options['loginform_text_color'], 1).');';
			$label_style .= $eol.$tab.'color: rgb('.$this->rgb_color(self::$options['loginform_text_color']).');'.$eol.$tab.'color: rgba('.$this->rgb_color(self::$options['loginform_text_color'], 1).');';
			
		endif;
		
		# .login .message
		
		$loggedout_style = '';
		
		if (isset(self::$options['loggedout_transparency']) && self::$options['loggedout_transparency'] == '0') :
		
			$loggedout_style .= $eol.$tab.'background: transparent;'.$eol.$tab.'border: none;'.$eol.$tab.'box-shadow: none;';
			
			if (!empty(self::$options['loggedout_text_color'])) :
			
				$loggedout_style .= $eol.$tab.'color: '.self::$options['loggedout_text_color'].';';
				
			endif;
		
		else :
		
			if (!empty(self::$options['loggedout_bg_color'])) $loggedout_style = $eol.$tab.'background-color: '.self::$options['loggedout_bg_color'].';';
			if (!empty(self::$options['loggedout_transparency'])) :
				$loggedout_style .= $eol.$tab.'-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity='.self::$options['loggedout_transparency'].')";';
				$loggedout_style .= $eol.$tab.'filter: alpha(Opacity='.self::$options['loggedout_transparency'].');';
				$loggedout_style .= $eol.$tab.'-moz-opacity: '.(self::$options['loggedout_transparency']/100).';';
				$loggedout_style .= $eol.$tab.'-khtml-opacity: '.(self::$options['loggedout_transparency']/100).';';
				$loggedout_style .= $eol.$tab.'opacity: '.(self::$options['loggedout_transparency']/100).';';
			endif;
		
			if (!empty(self::$options['loggedout_text_color'])) $loggedout_style .= $eol.$tab.'color: '.self::$options['loggedout_text_color'].';';
			if (!empty(self::$options['loggedout_border_color'])) $loggedout_style .= $eol.$tab.'border-color: '.self::$options['loggedout_border_color'].';';
			
		endif;
		
		# #login_error
		
		$error_style = '';
		
		if (isset(self::$options['error_transparency']) && self::$options['error_transparency'] == '0') :
		
			$error_style .= $eol.$tab.'background: transparent;'.$eol.$tab.'border: none;'.$eol.$tab.'box-shadow: none;';
			
			if (!empty(self::$options['error_text_color'])) :
			
				$error_style .= $eol.$tab.'color: '.self::$options['error_text_color'].';';
				
			endif;
		
		else :
		
			if (!empty(self::$options['error_bg_color'])) $error_style .= $eol.$tab.'background-color: '.self::$options['error_bg_color'].';';
			if (!empty(self::$options['error_transparency'])) :
				$error_style .= $eol.$tab.'-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity='.self::$options['error_transparency'].')";';
				$error_style .= $eol.$tab.'filter: alpha(Opacity='.self::$options['error_transparency'].');';
				$error_style .= $eol.$tab.'-moz-opacity: '.(self::$options['error_transparency']/100).';';
				$error_style .= $eol.$tab.'-khtml-opacity: '.(self::$options['error_transparency']/100).';';
				$error_style .= $eol.$tab.'opacity: '.(self::$options['error_transparency']/100).';';
			endif;
			
			if (!empty(self::$options['error_text_color'])) $error_style .= $eol.$tab.'color: '.self::$options['error_text_color'].';';
			if (!empty(self::$options['error_border_color'])) $error_style .= $eol.$tab.'border-color: '.self::$options['error_border_color'].';';
			
		endif;
		
		# .input
		
		$input_style = '';
		
		if (!empty(self::$options['input_text_color'])) $input_style .= $eol.$tab.'color: '.self::$options['input_text_color'].' !important;';
		if (!empty(self::$options['input_bg_color'])) $input_style .= $eol.$tab.'background-color: '.self::$options['input_bg_color'].' !important;';
		if (!empty(self::$options['input_border_color'])) $input_style .= $eol.$tab.'border-color: '.self::$options['input_border_color'].' !important;';
		
		# #login_error a, .login #nav a, .login #backtoblog a
		
		$link_style = '';
		$hover_style = '';
		
		if (!empty(self::$options['link_text_color'])) $link_style .= $eol.$tab.'color: '.self::$options['link_text_color'].' !important;';
		if (!empty(self::$options['link_textdecoration'])) $link_style .= $eol.$tab.'text-decoration: '.self::$options['link_textdecoration'].' !important;';
		if (!empty(self::$options['link_shadow_x']) || self::$options['link_shadow_x'] == '0') $link_style .= $eol.$tab.'text-shadow: '.self::$options['link_shadow_x'].'px '.self::$options['link_shadow_y'].'px '.self::$options['link_shadow_softness'].'px '.self::$options['link_shadow_color'].' !important;';
		if (!empty($link_style) && empty(self::$options['link_shadow_x'])) $link_style .= $eol.$tab.'text-shadow: none !important;';
		if (!empty(self::$options['link_size'])) $link_style .= $eol.$tab.'font-size: '.self::$options['link_size'].';';
		
		if (!empty(self::$options['hover_text_color'])) $hover_style .= $eol.$tab.'color: '.self::$options['hover_text_color'].' !important;';
		if (!empty(self::$options['hover_textdecoration'])) $hover_style .= $eol.$tab.'text-decoration: '.self::$options['hover_textdecoration'].' !important;';
		if (!empty(self::$options['hover_shadow_x']) || self::$options['hover_shadow_x'] == '0') $hover_style .= $eol.$tab.'text-shadow: '.self::$options['hover_shadow_x'].'px '.self::$options['hover_shadow_y'].'px '.self::$options['hover_shadow_softness'].'px '.self::$options['hover_shadow_color'].' !important;';
		
		# #nav
		
		$nav_style = '';
		
		if (isset(self::$options['hide_nav'])) $nav_style .= $eol.$tab.'display: none;';
		
		# #backtoblog
		
		$backtoblog_style = '';
		
		if (isset(self::$options['hide_backlink'])) $backtoblog_style .= $eol.$tab.'display: none;';
		
		# .button-primary
		
		$button_style = '';
		$btn_hover_style = '';
		
		if (!empty(self::$options['button_bg_color1'])) :
			
			$button_style .= $eol.$tab.'background: transparent !important;';
			$button_style .= $eol.$tab.'background-color: '.self::$options['button_bg_color1'].' !important;';
			
		endif;
		if (!empty(self::$options['button_bg_color2'])) :
			
			$button_style .= $eol.$tab.'background-image: -webkit-gradient(linear, left top, left bottom, from('.self::$options['button_bg_color1'].'), to('.self::$options['button_bg_color2'].')) !important;';
			$button_style .= $eol.$tab.'background-image: -webkit-linear-gradient(top, '.self::$options['button_bg_color1'].', '.self::$options['button_bg_color2'].') !important;';
			$button_style .= $eol.$tab.'background-image: -moz-linear-gradient(top, '.self::$options['button_bg_color1'].', '.self::$options['button_bg_color2'].') !important;';
			$button_style .= $eol.$tab.'background-image: -ms-linear-gradient(top, '.self::$options['button_bg_color1'].', '.self::$options['button_bg_color2'].') !important;';
			$button_style .= $eol.$tab.'background-image: -o-linear-gradient(top, '.self::$options['button_bg_color1'].', '.self::$options['button_bg_color2'].') !important;';
			$button_style .= $eol.$tab.'background-image: -linear-gradient(top, '.self::$options['button_bg_color1'].', '.self::$options['button_bg_color2'].') !important;';
			
		endif;
		if (!empty(self::$options['button_text_color'])) $button_style .= $eol.$tab.'color: '.self::$options['button_text_color'].' !important;';
		if (!empty(self::$options['button_border_color'])) $button_style .= $eol.$tab.'border: solid 1px '.self::$options['button_border_color'].' !important;';
		
		if (!empty(self::$options['btn_hover_bg_color1'])) $btn_hover_style .= $eol.$tab.'background-color: '.self::$options['btn_hover_bg_color1'].' !important;';
		if (!empty(self::$options['btn_hover_bg_color2'])) :
			
			$btn_hover_style .= $eol.$tab.'background-image: -webkit-gradient(linear, left top, left bottom, from('.self::$options['btn_hover_bg_color1'].'), to('.self::$options['btn_hover_bg_color2'].')) !important;';
			$btn_hover_style .= $eol.$tab.'background-image: -webkit-linear-gradient(top, '.self::$options['btn_hover_bg_color1'].', '.self::$options['btn_hover_bg_color2'].') !important;';
			$btn_hover_style .= $eol.$tab.'background-image: -moz-linear-gradient(top, '.self::$options['btn_hover_bg_color1'].', '.self::$options['btn_hover_bg_color2'].') !important;';
			$btn_hover_style .= $eol.$tab.'background-image: -ms-linear-gradient(top, '.self::$options['btn_hover_bg_color1'].', '.self::$options['btn_hover_bg_color2'].') !important;';
			$btn_hover_style .= $eol.$tab.'background-image: -o-linear-gradient(top, '.self::$options['btn_hover_bg_color1'].', '.self::$options['btn_hover_bg_color2'].') !important;';
			$btn_hover_style .= $eol.$tab.'background-image: -linear-gradient(top, '.self::$options['btn_hover_bg_color1'].', '.self::$options['btn_hover_bg_color2'].') !important;';
			
		endif;
		if (!empty(self::$options['btn_hover_text_color'])) $btn_hover_style .= $eol.$tab.'color: '.self::$options['btn_hover_text_color'].' !important;';
		if (!empty(self::$options['btn_hover_border_color'])) $btn_hover_style .= $eol.$tab.'border: solid 1px '.self::$options['btn_hover_border_color'].' !important;';
	
		#building the stylesheet
		
		$link_text_color = '';
		
		if (self::$options['inline'] != true) $clp_css='@charset "UTF-8";'.$eol.'/* CSS Document */'.$eol.$eol;
		
		if(!empty($body_style)) $clp_css.='html body.login {'.$body_style.$eol.'}'.$eol;
		if(!empty($h1_style)) $clp_css.='.login h1 a {'.$h1_style.$eol.'}'.$eol;
		if(!empty($logindiv_style)) $clp_css.='#login {'.$logindiv_style.$eol.'}'.$eol;
		if(!empty($loginform_style)) $clp_css.='.login form {'.$loginform_style.$eol.'}'.$eol;
		if(!empty($label_style)) $clp_css.='#loginform label,'.$eol.'#lostpasswordform label,'.$eol.'#registerform label {'.$label_style.'}'.$eol;
		if(!empty($loggedout_style)) $clp_css.='.login .message {'.$loggedout_style.$eol.'}'.$eol;
		if(!empty($error_style)) $clp_css.='.login #login_error {'.$error_style.$eol.'}'.$eol;
		if(!empty($input_style)) $clp_css.='.input {'.$input_style.$eol.'}'.$eol;
		if(!empty($nav_style)) $clp_css.='#nav {'.$nav_style.$eol.'}'.$eol;
		if(!empty($backtoblog_style)) $clp_css.='#backtoblog {'.$backtoblog_style.$eol.'}'.$eol;
		if(!empty($link_style)) :
		
			if (!empty(self::$options['link_text_color'])) $link_text_color = $eol.$tab.'color: '.self::$options['link_text_color'].' !important;';
			
			$clp_css.='.login #nav {'.$link_text_color.$eol.$tab.'text-shadow: none !important;'.$eol.'}'.$eol;
			$clp_css.='#login_error a,'.$eol.'.login #nav a,'.$eol.'.login #backtoblog a {'.$link_style.$eol.'}'.$eol;
		endif;
		if(!empty($hover_style)) $clp_css.='#login_error a:hover,'.$eol.'.login #nav a:hover,'.$eol.'.login #backtoblog a:hover {'.$hover_style.$eol.'}'.$eol;
		if(!empty($button_style)) $clp_css.='.button-primary {'.$button_style.$eol.'}'.$eol;
		if(!empty($btn_hover_style)) $clp_css.='.button-primary:hover {'.$btn_hover_style.$eol.'}'.$eol;

		return $clp_css;
		
	}
	
	/**
	 *
	 * get RGB color for labels and input fields
	 *
	 */
	function rgb_color($color, $opacity = false) {
		
		$r = hexdec(substr($color, 1 , 2 ));
		
		$g = hexdec(substr($color, 3 , 2 ));
		
		$b = hexdec(substr($color, 5 , 2 ));
		
		$output = ($opacity) ? $r.','.$g.','.$b.','.$opacity : $r.','.$g.','.$b;
		
		return $output;
		
	}
	
	/**
	 *
	 * redirect to dss
	 *
	 */
	function clp_add_rewrite() {
	   global $wp;
	   $wp->add_query_var('clpfile');
	}
	
	function clp_css_template() {
		
		$clpfile = get_query_var('clpfile');
		
		if ('css' == $clpfile) :
		   
			header('Content-type: text/css');
			echo $this->clp_get_the_style();
			
			exit;
		
		endif;
	   
		if ('export' == $clpfile) :
		
			self::$options['log'] = 'original A5 CLP file';
			
			header('Content-Description: File Transfer');
			header('Content-Disposition: attachment; filename="a5-clp-' . str_replace('.','-', $_SERVER['SERVER_NAME']) . '-' . date('Y') . date('m') . date('d') . '.txt"');
			header('Content-Type: text/plain; charset=utf-8');
			
			echo json_encode(self::$options);
			
			exit;
		
		endif;
	}
	
} // end of class

$a5_custom_login_page = new A5_CustomLoginPage;

?>