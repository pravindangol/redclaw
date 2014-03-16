<?php

/**
 *
 * Class A5 Option Page
 *
 * @ A5 Plugin Framework
 *
 * Gets all sort of input fields and containers for the flexible A5 settings pages
 *
 * The fields are used in the Wp Settings and the Widgets Settings as well
 *
 */

class A5_OptionPage {
	
	private $page_item;
	
	public function open_container($args){
		
		extract($args);
		
		$eol = "\r\n";
		$tab = "\t";
		
		$id = (isset($id)) ? ' id="'.$id.'"' : '';
		$style = (isset($style)) ? ' style="'.$style.'"' : '';
		$class = (isset($class)) ? ' class="'.$class.'"' : '';
		
		$this->page_item = $eol.$tab.'<div'.$id.$class.$style.'>';
		
		if ($echo === true) : 
	
			echo $this->page_item;
			
		else : 
		
			return $this->page_item;
			
		endif;
		
	} // container
	
} // A5_OptionPage

/***************************************************************************************************
 
	List of page functions with their parameters:
	
	a5_navigation($id, array($tabs), [$echo])

	a5_open_page($id, [$echo])
	
	a5_next_page($id, [$echo])
	
	a5_close_page([$echo])
	
	a5_open_section([$echo])
	
	a5_next_section([$echo])
	
	a5_close_section([$echo])
	
	a5_container_left(array($fields), [$echo])
	
	a5_container_right($headline, array($text), [$special], [array($message, $priority)], [$echo])
	
	a5_container_full($headline, array($text), [$special], [array($message, $priority)], [$echo]) ¡¡¡to do!!!
	
	a5_submit_container($name, $button_text, [$text], [$echo])
	
	a5_nav_js([$echo])
	
/**************************************************************************************************/

$a5_option_page = new A5_OptionPage;

/**
 *
 * navigation menu
 *
 */

function a5_navigation($id, $menuitems, $echo = true){
	
	$eol = "\r\n";
	$tab = "\t";
	
	$menu = $eol.'<ul id="'.$id.'">';
	
	foreach ($menuitems as $menuitem) :
	
		$class = (isset($menuitem[2])) ? ' class="selected"' : '';
		
		$menu .= $eol.$tab.'<li><a href="#" id="'.$menuitem[0].'-tab" rel="'.$menuitem[0].'"'.$class.'>'.$menuitem[1].'</a></li>';
	
	endforeach;
	
	$menu .= $eol.'</ul>';
	
	if ($echo === false) return $menu;
	
	echo $menu;
	
}

/**
 *
 * open a page
 *
 */
 
function a5_open_page($id, $echo = true){
	
	$eol = "\r\n";
	$tab = "\t";
	
	global $a5_option_page;
	
	$args = array ( 'id' => $id,
					'class' => 'tabcontent',
					'echo' => false
					);
	
	$page_start = $a5_option_page->open_container($args).' <!-- page \''.$id.'\' -->';
	
	$page_start.= $eol.$tab.'<form method="post" name="'.$id.'_form" id="'.$id.'_form" action="">';
	
	if ($echo === false) return $page_start;
	
	echo $page_start;
	
}

/**
 *
 * change to next page
 *
 */
 
function a5_next_page($id, $echo = true){
	
	$output = a5_close_page(false).a5_open_page($id, false);
	
	if ($echo === false) return $output;
	
	echo $output;
	
}

/**
 *
 * close page
 *
 */
 
function a5_close_page($echo = true){
	
	$eol = "\r\n";
	$tab = "\t";
	
	$page_end = $eol.$tab.'</form>'.$eol.$tab.'</div> <!-- / page -->';
	
	if ($echo === false) return $page_end;
	
	echo $page_end;
	
}

/**
 *
 * open a section
 *
 */
 
function a5_open_section($echo = true){
	
	global $a5_option_page;
	
	$args = array ( 'class' => 'a5-option-container',
					'echo' => $echo
					);
	
	$section_begin = $a5_option_page->open_container($args).' <!-- section -->';
	
	if ($echo === false) return $section_begin;
	
	echo $section_begin;
	
}

/**
 *
 * change to next section
 *
 */
 
function a5_next_section($echo = true){
	
	$output = a5_close_section(false).a5_open_section(false);
	
	if ($echo === false) return $output;
	
	echo $output;
	
}

/**
 *
 * close section
 *
 */
 
function a5_close_section($echo = true){
	
	$eol = "\r\n";
	$tab = "\t";
	
	$section_end = $eol.$tab.'</div> <!-- / section -->';
	
	if ($echo === false) return $section_end;
	
	echo $section_end;
	
}

/**
 *
 * building the left container
 *
 */
 
function a5_container_left($fields, $echo = true){
	
	global $a5_option_page;
	
	$eol = "\r\n";
	$tab = "\t";
	
	$args = array ( 'class' => 'a5-option-container-left',
					'echo' => false
					);
	
	$container = $a5_option_page->open_container($args);
	
	foreach ($fields as $field) $container.=$field;
	
	$container.=$eol.$tab.'</div>';
	
	if ($echo === false) return $container;
		
	echo $container;
	
}

/**
 *
 * building the right container
 *
 */
 
function a5_container_right($headline, $text, $special = false, $message = false, $echo = true){
	
	global $a5_option_page;
	
	$eol = "\r\n";
	$tab = "\t";
	
	if ($message !== false) :
	
		$count = 2;
	
		$priority = $message[1];
	
		$message = $eol.$tab.'<div id="'.$message[0].'"></div>';
		
	endif;
	
	$special = ($special) ? $eol.$tab.'<p><i>'.$special.'</i></p>' : '';
	
	$args = array ( 'class' => 'a5-option-container-right',
					'echo' => false
					);
	
	$container = $a5_option_page->open_container($args);
	
	$priority = (isset($priority)) ? $priority : 0;
	
	if ($priority == 1) $container.=$message;
	
	$container.=$eol.$tab.'<h2>'.$headline.'</h2>';
	
	if ($priority == 2) $container.=$message;
	
	$count = 0;
	
	foreach ($text as $schnummschnick) :
	
		$container.=$eol.$tab.'<p>'.$schnummschnick.'</p>';
		
		$count++;
		
		if ($priority == $count) $container.=$message;
		
	endforeach;
	
	$container.=$special;
					
	$container.=$eol.$tab.'</div>';
	
	$container.=$eol.$tab.'<div style="clear: both;"></div>';
	
	if ($echo === false) return $container;
		
	echo $container;
	
}

/**
 *
 * the submit section
 *
 */
 
function a5_submit_container($name, $button_text, $text = false, $echo = true){
	
	$eol = "\r\n";
	$tab = "\t";
	
	$text = ($text) ? $eol.$tab.'<span style="font-weight: bold; color:#243e1f">'.$text.'</span>' : '';
	
	$output = $eol.$tab.'<div id="submit-container"> <!-- submit -->'.$eol.$tab.'<p class="submit">'.$eol.$tab;
	$output.= '<input class="save-tab" name="'.$name.'" id="'.$name.'" value="'.$button_text.'" type="submit">'.$eol.$tab;
	$output.= '<img src="'.admin_url('/images/wpspin_light.gif').'" alt="" class="'.$name.'" style="display: none;" />'.$eol.$tab;
	$output.= $text.'</p>'.$eol.$tab.'</div> <!-- / submit -->';
	
	if ($echo === false) return $output;
		
	echo $output;
	
}

/**
 *
 * the javascript at the end of the whole thing
 *
 */
function a5_nav_js($plugin_shortname, $echo = true){
	
	$eol = "\r\n";
	$tab = "\t";
	
	$output = $eol.$tab.'<script type="text/javascript">'.$eol.$tab.'var pages=new ddtabcontent("'.$plugin_shortname.'-pagetabs") //enter ID of Tab Container';
	$output.= $eol.$tab.'pages.setpersist(true) //toogle persistence of the tabs&#39; state'.$eol.$tab.'pages.setselectedClassTarget("link") //"link" or "linkparent"';
	$output.= $eol.$tab.'pages.init()'.$eol.$tab.'</script>'.$eol.$tab;
	
	if ($echo === false) return $output;
		
	echo $output;
	
}

?>