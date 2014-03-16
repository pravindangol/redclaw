jQuery(document).ready(function(){
	
//save main_form

	jQuery("#main_form").submit(function()	{
		jQuery("#mainmsg").html('<p class="save">' + message.saving + '</p>');		
		var section = 'main';
		var logo = jQuery("#logo").val();
		var url = jQuery("#url").val();
		var title = jQuery("#title").val();
		var logo_width = jQuery("#logo_width").val();
		var logo_height = jQuery("#logo_height").val();
		var h1_width = jQuery("#h1_width").val();
		var h1_height = jQuery("#h1_height").val();
		var h1_margin = jQuery("#h1_margin").val();
		var h1_padding = jQuery("#h1_padding").val();
		var h1_corner = jQuery("#h1_corner").val();
		var h1_shadow_x = jQuery("#h1_shadow_x").val();
		var h1_shadow_y = jQuery("#h1_shadow_y").val();
		var h1_shadow_softness = jQuery("#h1_shadow_softness").val();
		var h1_shadow_color = jQuery("#h1_shadow_color").val();
		var body_background = jQuery("#body_background").val();
		var body_img_repeat = jQuery("#body_img_repeat").val();
		var body_img_pos = jQuery("#body_img_pos").val();
		var body_bg_color1 = jQuery("#body_bg_color1").val();
		var body_bg_color2 = jQuery("#body_bg_color2").val();
		var body_bg_size = jQuery("#body_bg_size").val();
		var mainnonce = jQuery("#mainnonce").val();
		var _wp_http_referer = jQuery("#main_form #_wp_http_referer").val();
		var data = {
			action: 'clp_save_settings',
			section: section,
			logo: logo,
			url: url,
			title: title,
			logo_width: logo_width,
			logo_height: logo_height,
			h1_width: h1_width,
			h1_height: h1_height,
			h1_margin: h1_margin,
			h1_padding: h1_padding,
			h1_corner: h1_corner,
			h1_shadow_x: h1_shadow_x,
			h1_shadow_y: h1_shadow_y,
			h1_shadow_softness: h1_shadow_softness,
			h1_shadow_color: h1_shadow_color,
			body_background: body_background,
			body_img_repeat: body_img_repeat,
			body_img_pos: body_img_pos,
			body_bg_color1: body_bg_color1,
			body_bg_color2: body_bg_color2,
			body_bg_size: body_bg_size,
			mainnonce: mainnonce,
			_wp_http_referer: _wp_http_referer
		};
		jQuery("#main_save").hide();
		jQuery(".main_save").show();
		jQuery.post(ajaxurl, data,
		function(response){
			jQuery("#mainmsg").html(response);
			jQuery(".main_save").hide();
			jQuery("#main_save").show();
		});
	return false;
	});
	
//save logindiv_form

	jQuery("#logindiv_form").submit(function()	{
		jQuery("#logindivmsg").html('<p class="save">' + message.saving + '</p>');		
		var section = 'logindiv';
		var logindiv_background = jQuery("#logindiv_background").val();
		var logindiv_img_repeat = jQuery("#logindiv_img_repeat").val();
		var logindiv_img_pos = jQuery("#logindiv_img_pos").val();
		var logindiv_bg_color1 = jQuery("#logindiv_bg_color1").val();
		var logindiv_bg_color2 = jQuery("#logindiv_bg_color2").val();
		var logindiv_text_color = jQuery("#logindiv_text_color").val();
		var logindiv_transparency = jQuery("#logindiv_transparency").val();
		var logindiv_border_style = jQuery("#logindiv_border_style").val();
		var logindiv_border_width = jQuery("#logindiv_border_width").val();
		var logindiv_border_color = jQuery("#logindiv_border_color").val();
		var logindiv_border_round = jQuery("#logindiv_border_round").val();
		var logindiv_shadow_x = jQuery("#logindiv_shadow_x").val();
		var logindiv_shadow_y = jQuery("#logindiv_shadow_y").val();
		var logindiv_shadow_softness = jQuery("#logindiv_shadow_softness").val();
		var logindiv_shadow_color = jQuery("#logindiv_shadow_color").val();		
		var logindiv_left = jQuery("#logindiv_left").val();
		var logindiv_top = jQuery("#logindiv_top").val();
		var logindiv_width = jQuery("#logindiv_width").val();
		var logindiv_height = jQuery("#logindiv_height").val();
		var logindiv_padding = jQuery("#logindiv_padding").val();
		var logindiv_margin = jQuery("#logindiv_margin").val();
		
		
		var logindivnonce = jQuery("#logindivnonce").val();
		var _wp_http_referer = jQuery("#logindiv_form #_wp_http_referer").val();
		var data = {
			action: 'clp_save_settings',
			section: section,
			logindiv_background: logindiv_background,
			logindiv_img_repeat: logindiv_img_repeat,
			logindiv_img_pos: logindiv_img_pos,
			logindiv_bg_color1: logindiv_bg_color1,
			logindiv_bg_color2: logindiv_bg_color2,
			logindiv_text_color: logindiv_text_color,
			logindiv_transparency: logindiv_transparency,
			logindiv_border_style: logindiv_border_style,
			logindiv_border_width: logindiv_border_width,
			logindiv_border_color: logindiv_border_color,
			logindiv_border_round: logindiv_border_round,
			logindiv_shadow_x: logindiv_shadow_x,
			logindiv_shadow_y: logindiv_shadow_y,
			logindiv_shadow_softness: logindiv_shadow_softness,
			logindiv_shadow_color: logindiv_shadow_color,
			logindiv_left: logindiv_left,
			logindiv_top: logindiv_top,
			logindiv_width: logindiv_width,
			logindiv_height: logindiv_height,
			logindiv_padding: logindiv_padding,
			logindiv_margin: logindiv_margin,
			logindivnonce: logindivnonce,
			_wp_http_referer: _wp_http_referer
		};
		jQuery("#logindiv_save").hide();
		jQuery(".logindiv_save").show();
		jQuery.post(ajaxurl, data,
		function(response){
			jQuery("#logindivmsg").html(response);
			jQuery(".logindiv_save").hide();
			jQuery("#logindiv_save").show();
		});
	return false;
	})
	
//save loginform_form

	jQuery("#loginform_form").submit(function()	{
		jQuery("#loginmsg").html('<p class="save">' + message.saving + '</p>');		
		var section = 'loginform';
		var loginform_background = jQuery("#loginform_background").val();
		var loginform_img_repeat = jQuery("#loginform_img_repeat").val();
		var loginform_img_pos = jQuery("#loginform_img_pos").val();
		var loginform_bg_color1 = jQuery("#loginform_bg_color1").val();
		var loginform_bg_color2 = jQuery("#loginform_bg_color2").val();
		var loginform_text_color = jQuery("#loginform_text_color").val();
		var loginform_transparency = jQuery("#loginform_transparency").val();
		var loginform_border_style = jQuery("#loginform_border_style").val();
		var loginform_border_width = jQuery("#loginform_border_width").val();
		var loginform_border_color = jQuery("#loginform_border_color").val();
		var loginform_border_round = jQuery("#loginform_border_round").val();
		var loginform_margin = jQuery("#loginform_margin").val();
		var loginform_padding = jQuery("#loginform_padding").val();
		var loginform_shadow_x = jQuery("#loginform_shadow_x").val();
		var loginform_shadow_y = jQuery("#loginform_shadow_y").val();
		var loginform_shadow_softness = jQuery("#loginform_shadow_softness").val();
		var loginform_shadow_color = jQuery("#loginform_shadow_color").val();		
		var loginformnonce = jQuery("#loginformnonce").val();
		var _wp_http_referer = jQuery("#loginform_form #_wp_http_referer").val();
		var data = {
			action: 'clp_save_settings',
			section: section,
			loginform_background: loginform_background,
			loginform_img_repeat: loginform_img_repeat,
			loginform_img_pos: loginform_img_pos,
			loginform_bg_color1: loginform_bg_color1,
			loginform_bg_color2: loginform_bg_color2,
			loginform_text_color: loginform_text_color,
			loginform_transparency: loginform_transparency,
			loginform_border_style: loginform_border_style,
			loginform_border_width: loginform_border_width,
			loginform_border_color: loginform_border_color,
			loginform_border_round: loginform_border_round,
			loginform_margin: loginform_margin,
			loginform_padding: loginform_padding,
			loginform_shadow_x: loginform_shadow_x,
			loginform_shadow_y: loginform_shadow_y,
			loginform_shadow_softness: loginform_shadow_softness,
			loginform_shadow_color: loginform_shadow_color,
			loginformnonce: loginformnonce,
			_wp_http_referer: _wp_http_referer
		};
		jQuery("#loginform_save").hide();
		jQuery(".loginform_save").show();
		jQuery.post(ajaxurl, data,
		function(response){
			jQuery("#loginmsg").html(response);
			jQuery(".loginform_save").hide();
			jQuery("#loginform_save").show();
		});
	return false;
	});
	
//save button_form

	jQuery("#button_form").submit(function()	{
		jQuery("#buttonmsg").html('<p class="save">' + message.saving + '</p>');		
		var section = 'button';
		var button_bg_color1 = jQuery("#button_bg_color1").val();
		var button_bg_color2 = jQuery("#button_bg_color2").val();
		var button_text_color = jQuery("#button_text_color").val();
		var button_border_color = jQuery("#button_border_color").val();
		var btn_hover_bg_color1 = jQuery("#btn_hover_bg_color1").val();
		var btn_hover_bg_color2 = jQuery("#btn_hover_bg_color2").val();
		var btn_hover_text_color = jQuery("#btn_hover_text_color").val();
		var btn_hover_border_color = jQuery("#btn_hover_border_color").val();		
		var buttonnonce = jQuery("#buttonnonce").val();
		var _wp_http_referer = jQuery("#button_form #_wp_http_referer").val();
		var data = {
			action: 'clp_save_settings',
			section: section,
			button_bg_color1: button_bg_color1,
			button_bg_color2: button_bg_color2,
			button_text_color: button_text_color,
			button_border_color: button_border_color,
			btn_hover_bg_color1: btn_hover_bg_color1,
			btn_hover_bg_color2: btn_hover_bg_color2,
			btn_hover_text_color: btn_hover_text_color,
			btn_hover_border_color: btn_hover_border_color,
			buttonnonce: buttonnonce,
			_wp_http_referer: _wp_http_referer
		};
		jQuery("#button_save").hide();
		jQuery(".button_save").show();
		jQuery.post(ajaxurl, data,
		function(response){
			jQuery("#buttonmsg").html(response);
			jQuery(".button_save").hide();
			jQuery("#button_save").show();
		});
	return false;
	});
	
//save message_form

	jQuery("#message_form").submit(function()	{
		jQuery("#messagemsg").html('<p class="save">' + message.saving + '</p>');		
		var section = 'message';
		var loggedout_text_color = jQuery("#loggedout_text_color").val();
		var loggedout_bg_color = jQuery("#loggedout_bg_color").val();
		var loggedout_border_color = jQuery("#loggedout_border_color").val();
		var loggedout_transparency = jQuery("#loggedout_transparency").val();
		var logout_custom_message = jQuery("#logout_custom_message").val();
		var error_text_color = jQuery("#error_text_color").val();
		var error_bg_color = jQuery("#error_bg_color").val();
		var error_border_color = jQuery("#error_border_color").val();
		var error_transparency = jQuery("#error_transparency").val();
		var error_custom_message = jQuery("#error_custom_message").val();
		var input_text_color = jQuery("#input_text_color").val();
		var input_bg_color = jQuery("#input_bg_color").val();
		var input_border_color = jQuery("#input_border_color").val();
		var messagenonce = jQuery("#messagenonce").val();
		var _wp_http_referer = jQuery("#message_form #_wp_http_referer").val();
		var data = {
			action: 'clp_save_settings',
			section: section,
			loggedout_text_color: loggedout_text_color,
			loggedout_bg_color: loggedout_bg_color,
			loggedout_border_color: loggedout_border_color,
			loggedout_transparency: loggedout_transparency,
			logout_custom_message: logout_custom_message,
			error_text_color: error_text_color,
			error_bg_color: error_bg_color,
			error_border_color: error_border_color,
			error_transparency: error_transparency,			
			error_custom_message: error_custom_message,
			input_text_color: input_text_color,
			input_bg_color: input_bg_color,
			input_border_color: input_border_color,
			messagenonce: messagenonce,
			_wp_http_referer: _wp_http_referer
		};
		jQuery("#message_save").hide();
		jQuery(".message_save").show();
		jQuery.post(ajaxurl, data,
		function(response){
			jQuery("#messagemsg").html(response);
			jQuery(".message_save").hide();
			jQuery("#message_save").show();
		});
	return false;
	});
	
//save link_form

	jQuery("#link_form").submit(function()	{
		jQuery("#linkmsg").html('<p class="save">' + message.saving + '</p>');		
		var section = 'link';
		var hide_nav = jQuery("input[name='hide_nav']:checked").val();
		var hide_backlink = jQuery("input[name='hide_backlink']:checked").val();
		var link_text_color = jQuery("#link_text_color").val();
		var link_textdecoration = jQuery("#link_textdecoration").val();
		var link_shadow_x = jQuery("#link_shadow_x").val();
		var link_shadow_y = jQuery("#link_shadow_y").val();
		var link_shadow_softness = jQuery("#link_shadow_softness").val();
		var link_shadow_color = jQuery("#link_shadow_color").val();
		var hover_text_color = jQuery("#hover_text_color").val();
		var hover_textdecoration = jQuery("#hover_textdecoration").val();
		var hover_shadow_x = jQuery("#hover_shadow_x").val();
		var hover_shadow_y = jQuery("#hover_shadow_y").val();
		var hover_shadow_softness = jQuery("#hover_shadow_softness").val();
		var hover_shadow_color = jQuery("#hover_shadow_color").val();
		var link_size = jQuery("#link_size").val();		
		var linknonce = jQuery("#linknonce").val();
		var _wp_http_referer = jQuery("#link_form #_wp_http_referer").val();
		var data = {
			action: 'clp_save_settings',
			section: section,
			hide_nav: hide_nav,
			hide_backlink: hide_backlink,
			link_text_color: link_text_color,
			link_textdecoration: link_textdecoration,
			link_shadow_x: link_shadow_x,
			link_shadow_y: link_shadow_y,
			link_shadow_softness: link_shadow_softness,
			link_shadow_color: link_shadow_color,
			hover_text_color: hover_text_color,
			hover_textdecoration: hover_textdecoration,
			hover_shadow_x: hover_shadow_x,
			hover_shadow_y: hover_shadow_y,
			hover_shadow_softness: hover_shadow_softness,
			hover_shadow_color: hover_shadow_color,
			link_size: link_size,
			linknonce: linknonce,
			_wp_http_referer: _wp_http_referer
		};
		jQuery("#link_save").hide();
		jQuery(".link_save").show();
		jQuery.post(ajaxurl, data,
		function(response){
			jQuery("#linkmsg").html(response);
			jQuery(".link_save").hide();
			jQuery("#link_save").show();
		});
	return false;
	});
	
//save debug_form

	jQuery("#debug_form").submit(function()	{
		jQuery("#debugmsg").html('<p class="save">' + message.saving + '</p>');		
		var section = 'debug';
		var inline = jQuery("input[name='inline']:checked").val();
		var debugnonce = jQuery("#debugnonce").val();
		var _wp_http_referer = jQuery("#debug_form #_wp_http_referer").val();
		var data = {
			action: 'clp_save_settings',
			section: section,
			inline: inline,
			debugnonce: debugnonce,
			_wp_http_referer: _wp_http_referer
		};
		jQuery("#debug_save").hide();
		jQuery(".debug_save").show();
		jQuery.post(ajaxurl, data,
		function(response){
			jQuery("#debugmsg").html(response);
			jQuery(".debug_save").hide();
			jQuery("#debug_save").show();
		});
	return false;
	});	
	
//import settings file

	jQuery("#impex_form").submit(function()	{
		var clp_import = jQuery("#clp_import").val();
		var impexnonce = jQuery("#impexnonce").val();
		var _wp_http_referer = jQuery("#impex_form #_wp_http_referer").val();
		var data = {
			action: 'clp_import_settings',
			clp_import: clp_import,
			impexnonce: impexnonce,
			_wp_http_referer: _wp_http_referer
		};
		jQuery("#impex_save").hide();
		jQuery(".impex_save").show();
		jQuery.post(ajaxurl, data,
		function(response){
			jQuery('#clp_import').val('');
			jQuery("#impexmsg").html(response);
			jQuery(".impex_save").hide();
			jQuery("#impex_save").show();
		});
	return false;
	});	
	
});