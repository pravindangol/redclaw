<?php

/**
 *
 * Class A5 FormField
 *
 * @ A5 Plugin Framework
 * Version: 0.9.7 alpha
 *
 * Gets all sort of input fields for plugins by Atelier 5 
 *
 * The fields can be gotten directly from the class or from the field functions, provided with the class.
 *
 */

class A5_FormField {
	
	public $formfield;
	
	function __construct($args){
		
		extract($args);
		
		$eol = "\r\n";
		$tab = "\t";
		
		$id = (isset($field_id) && !is_array($field_id)) ? ' id="'.$field_id.'"' : '';
		$label = (isset($label)) ? '<label for="'.$field_id.'">'.$label.'</label>' : '';
		$name = (isset($field_name)) ? ' name="'.$field_name.'"' : '';
		$atts = '';
		
		// wrapping the field into paragraph tags, if wanted
		
		if (isset($attributes['space'])) :
			
			$space = true;
			
			unset($attributes['space']);
			
		endif;
		
		// getting all extra attributes to the fields (there is no sanitizing at the moment)
		
		if (isset($attributes)) foreach ($attributes as $attribute => $attr_value) $atts .= ' '.$attribute.'="'.$attr_value.'"';
		
		// getting different types of input elements	
		
		switch ($type) :
		
			case 'textarea' :
			
				$this->formfield = $eol.$tab.'<textarea'.$name.$id.$atts.'>'.$value.'</textarea>';
			
				break;
				
			case 'select' :
			
				if (empty($value)) $value[0] = false;
			
				$this->formfield = '<select'.$name.$id.$atts.'>';
				
				if (!empty($default)) $this->formfield .= $eol.$tab.'<option value="" '.selected( $value[0], false, false ).'>'.$default.'</option>';
				
				foreach ($options as $option) :
				
					$selected = (in_array($option[0], $value)) ? ' selected="selected"' : '';
				
					$this->formfield .= $eol.$tab.'<option value="'.$option[0].'"'.$selected.' >'.$option[1].'</option>';
				
				endforeach;
				
				$this->formfield .= $eol.$tab.'</select>';
			
				break;
				
			case 'resize' :
			
				$this->formfield = $eol.'<script type="text/javascript"><!--'.$eol.'jQuery(document).ready(function() {';
																										   
				foreach ($field_id as $field) :
				
					$this->formfield .= $eol.$tab.'jQuery("#'.$field.'").autoResize();';
				
				endforeach;
				
				$this->formfield .= $eol.'});'.$eol.'--></script>'.$eol;
			
				break;
				
			default :
			
				$field_type = (isset($type)) ? ' type="'.$type.'"' : ' type="text"';
				
				if ('img' != $type) :
				
					$value = (isset($value)) ? ' value="'.$value.'"' : ' value=""';
					
				endif;
				
				$this->formfield = '<input'.$name.$id.$field_type.$value.$atts.' />'.$eol;
			
				break;
		
		endswitch;
		
		$this->formfield = (!strstr($type, 'checkbox') && !strstr($type, 'radio')) ? $eol.$tab.$label.$eol.$tab.$this->formfield : $eol.$tab.$this->formfield.$eol.$tab.$label;
		
		$this->formfield = (isset($space)) ? '<p>'.$this->formfield.$eol.'</p>'.$eol : $this->formfield;
		
		return $this->formfield;
		
	}
	
} // A5_FormField

/**
 *
 * Admin Field Functions 
 *
 * @ Class A5 Form Field
 *
 * @ A5 Plugin Framework
 *
 * Gets all sort of input fields for the flexible A5 settings pages, using the A5_OptionPage Class
 *
 * fieldsets of checkboxes and radiobuttons have to be written as well
 * 
 */
 
/***************************************************************************************************
 
	List of field functions their parameters:
	
	
	html 4 (supported by all browsers):

	a5_textarea($field_id, $field_name, [$value], [$label], [array($attributes)], [$echo])

	a5_checkbox($field_id, $field_name, [$value], [$label], [array($attributes)], [$checked], [$echo])

	a5_radio($field_id, $field_name, [$value], [$label], [array($attributes)], [$checked], [$echo])

	a5_select($field_id, $field_name, array($options), [$value], [$label], [$default], [array($attributes)], [$echo])

	a5_hidden_field($field_id, $field_name, [$value], [$echo])

	a5_text_field($field_id, $field_name, [$value], [$label], [array($attributes)], [$echo])
	
	a5_file_field($field_id, $field_name, [$value], [$label], [array($attributes)], [$echo])
	
	a5_image_field($field_id, $field_name, $scr, [$value], [$label], [array($attributes)], [$echo])
	
	a5_button($field_id, $field_name, $scr, [$value], [$label], [array($attributes)], [$echo])
	
	a5_submit($field_id, $field_name, $scr, [$value], [$label], [array($attributes)], [$echo])
	
	a5_reset($field_id, $field_name, $scr, [$value], [$label], [array($attributes)], [$echo])
	
	
	html 5 (not supported by every browser yet):

	a5_color_field($field_id, $field_name, [$value], [$label], [array($attributes)], [$echo])

	a5_date_field($field_id, $field_name, [$value], [$label], [array($attributes)], [$echo])

	a5_datetime_field($field_id, $field_name, [$value], [$label], [array($attributes)], [$echo])

	a5_datetime_local_field($field_id, $field_name, [$value], [$label], [array($attributes)], [$echo])

	a5_email_field($field_id, $field_name, [$value], [$label], [array($attributes)], [$echo])

	a5_month_field($field_id, $field_name, [$value], [$label], [array($attributes)], [$echo])

	a5_number_field($field_id, $field_name, [$value], [$label], [array($attributes)], [$echo])

	a5_range_field($field_id, $field_name, $min, $max, [$value], [$label], [array($attributes)], [$echo])

	a5_search_field($field_id, $field_name, [$value], [$label], [array($attributes)], [$echo])

	a5_tel_field($field_id, $field_name, [$value], [$label], [array($attributes)], [$echo])

	a5_time_field($field_id, $field_name, [$value], [$label], [array($attributes)], [$echo])

	a5_url_field($field_id, $field_name, [$value], [$label], [array($attributes)], [$echo])

	a5_week_field($field_id, $field_name, [$value], [$label], [array($attributes)], [$echo])
	
/**************************************************************************************************/ 


/**
 *
 * function to get text area
 *
 */
 
function a5_textarea($field_id, $field_name, $value = false, $label = false, $attributes = false, $echo = true) {
	
	$args = array ( 'type' => 'textarea',
					'field_id' => $field_id,
					'field_name' => $field_name,
					'value' => $value,
					'label' => $label,
					'attributes' => (array) $attributes
					);
					
	$textarea = new A5_FormField($args);
	
	if (false === $echo) return $textarea->formfield;
	
	echo $textarea->formfield;
}

/**
 *
 * function to get checkbox
 *
 */
 
function a5_checkbox($field_id, $field_name, $value = false, $label = false, $attributes = false, $checked = false, $echo = true) {
	
	if (false == $checked) :
		
		$checked = $value;
		
		$value = true;
		
	endif;
	
	if ($checked == $value) $attributes['checked'] = 'checked';
	
	$args = array ( 'type' => 'checkbox',
					'field_id' => $field_id,
					'field_name' => $field_name,
					'value' => $value,
					'label' => $label,
					'attributes' => (array) $attributes
					);
					
	$checkbox = new A5_FormField($args);
	
	if (false === $echo) return $checkbox->formfield;
	
	echo $checkbox->formfield;	
	
}

/**
 *
 * function to get radio button
 *
 */
 
function a5_radio($field_id, $field_name, $value = false, $label = false, $attributes = false, $checked = false, $echo = true) {
	
	if (false == $checked) :
		
		$checked = $value;
		
		$value = true;
		
	endif;
	
	if ($checked == $value) $attributes['checked'] = 'checked';
	
	$args = array ( 'type' => 'radio',
					'field_id' => $field_id,
					'field_name' => $field_name,
					'value' => $value,
					'label' => $label,
					'attributes' => (array) $attributes
					);
					
	$radio = new A5_FormField($args);
	
	if (false === $echo) return $radio->formfield;
	
	echo $radio->formfield;
	
}

/**
 *
 * function to get pulldown menu
 *
 */
 
function a5_select($field_id, $field_name, $options, $value = false, $label = false, $default = false, $attributes = false, $echo = true) {
	
	$args = array ( 'type' => 'select',
					'field_id' => $field_id,
					'field_name' => $field_name,
					'options' => (array) $options,
					'value' => (array) $value,
					'label' => $label,
					'default' => $default,
					'attributes' => (array) $attributes
					);
					
	$select = new A5_FormField($args);
	
	if (false === $echo) return $select->formfield;
	
	echo $select->formfield;
	
}

/**
 *
 * function to get a hidden input field
 *
 */
 
function a5_hidden_field($field_id, $field_name, $value = false, $echo = true) {
	
	$args = array ( 'type' => 'hidden',
					'field_id' => $field_id,
					'field_name' => $field_name,
					'value' => $value
					);
					
	$hidden_field = new A5_FormField($args);
	
	if (false === $echo) return $hidden_field->formfield;
	
	echo $hidden_field->formfield;
	
}

/**
 *
 * function to get a text input field
 *
 */
 
function a5_text_field($field_id, $field_name, $value = false, $label = false, $attributes = false, $echo = true) {
	
	$args = array ( 'type' => 'text',
					'field_id' => $field_id,
					'field_name' => $field_name,
					'value' => $value,
					'label' => $label,
					'attributes' => (array) $attributes
					);
					
	$text_field = new A5_FormField($args);
	
	if (false === $echo) return $text_field->formfield;
	
	echo $text_field->formfield;
	
}

/**
 *
 * function to get a file input field
 *
 */
 
function a5_file_field($field_id, $field_name, $value = false, $label = false, $attributes = false, $echo = true) {
	
	$args = array ( 'type' => 'file',
					'field_id' => $field_id,
					'field_name' => $field_name,
					'value' => $value,
					'label' => $label,
					'attributes' => (array) $attributes
					);
					
	$file_field = new A5_FormField($args);
	
	if (false === $echo) return $file_field->formfield;
	
	echo $file_field->formfield;
	
}

/**
 *
 * function to get an image input field
 *
 */
 
function a5_image_field($field_id, $field_name, $scr, $value = false, $label = false, $attributes = false, $echo = true) {
	
	$attributes['scr'] = $scr;
	
	$args = array ( 'type' => 'img',
					'field_id' => $field_id,
					'field_name' => $field_name,
					'value' => $value,
					'label' => $label,
					'attributes' => (array) $attributes
					);
					
	$img_field = new A5_FormField($args);
	
	if (false === $echo) return $img_field->formfield;
	
	echo $img_field->formfield;
	
}

/**
 *
 * function to get a button input field
 *
 */
 
function a5_button($field_id, $field_name, $value = false, $label = false, $attributes = false, $echo = true) {
	
	$args = array ( 'type' => 'button',
					'field_id' => $field_id,
					'field_name' => $field_name,
					'value' => $value,
					'label' => $label,
					'attributes' => (array) $attributes
					);
					
	$button = new A5_FormField($args);
	
	if (false === $echo) return $button->formfield;
	
	echo $button->formfield;
	
}

/**
 *
 * function to get submit button
 *
 */
 
function a5_submit($field_id, $field_name, $value = false, $label = false, $attributes = false, $echo = true) {
	
	$args = array ( 'type' => 'submit',
					'field_id' => $field_id,
					'field_name' => $field_name,
					'value' => $value,
					'label' => $label,
					'attributes' => (array) $attributes
					);
					
	$submit = new A5_FormField($args);
	
	if (false === $echo) return $submit->formfield;
	
	echo $submit->formfield;
	
}

/**
 *
 * function to get reset button
 *
 */
 
function a5_reset($field_id, $field_name, $value = false, $label = false, $attributes = false, $echo = true) {
	
	$args = array ( 'type' => 'reset',
					'field_id' => $field_id,
					'field_name' => $field_name,
					'value' => $value,
					'label' => $label,
					'attributes' => (array) $attributes
					);
					
	$reset = new A5_FormField($args);
	
	if (false === $echo) return $reset->formfield;
	
	echo $reset->formfield;
	
}

/***************************************************************************************************

	HTML 5 input fields (not supported by all browsers yet)

/**
 *
 * function to get a color input field
 *
 */
 
function a5_color_field($field_id, $field_name, $value = false, $label = false, $attributes = false, $echo = true) {
	
	$args = array ( 'type' => 'color',
					'field_id' => $field_id,
					'field_name' => $field_name,
					'value' => $value,
					'label' => $label,
					'attributes' => (array) $attributes
					);
					
	$color_field = new A5_FormField($args);
	
	if (false === $echo) return $color_field->formfield;
	
	echo $color_field->formfield;
	
}

/**
 *
 * function to get a date input field
 *
 */
 
function a5_date_field($field_id, $field_name, $value = false, $label = false, $attributes = false, $echo = true) {
	
	$args = array ( 'type' => 'date',
					'field_id' => $field_id,
					'field_name' => $field_name,
					'value' => $value,
					'label' => $label,
					'attributes' => (array) $attributes
					);
					
	$date_field = new A5_FormField($args);
	
	if (false === $echo) return $date_field->formfield;
	
	echo $date_field->formfield;
	
}

/**
 *
 * function to get a datetime input field
 *
 */
 
function a5_datetime_field($field_id, $field_name, $value = false, $label = false, $attributes = false, $echo = true) {
	
	$args = array ( 'type' => 'datetime',
					'field_id' => $field_id,
					'field_name' => $field_name,
					'value' => $value,
					'label' => $label,
					'attributes' => (array) $attributes
					);
					
	$datetime_field = new A5_FormField($args);
	
	if (false === $echo) return $datetime_field->formfield;
	
	echo $datetime_field->formfield;
	
}

/**
 *
 * function to get a datetime-locat input field
 *
 */
 
function a5_datetime_local_field($field_id, $field_name, $value = false, $label = false, $attributes = false, $echo = true) {
	
	$args = array ( 'type' => 'datetime-local',
					'field_id' => $field_id,
					'field_name' => $field_name,
					'value' => $value,
					'label' => $label,
					'attributes' => (array) $attributes
					);
					
	$datetime_local_field = new A5_FormField($args);
	
	if (false === $echo) return $datetime_local_field->formfield;
	
	echo $datetime_local_field->formfield;
	
}

/**
 *
 * function to get an email input field
 *
 */
 
function a5_email_field($field_id, $field_name, $value = false, $label = false, $attributes = false, $echo = true) {
	
	$args = array ( 'type' => 'email',
					'field_id' => $field_id,
					'field_name' => $field_name,
					'value' => $value,
					'label' => $label,
					'attributes' => (array) $attributes
					);
					
	$email_field = new A5_FormField($args);

	if (false === $echo) return $email_field->formfield;
	
	echo $email_field->formfield;	
	
}


/**
 *
 * function to get a month input field
 *
 */
 
function a5_month_field($field_id, $field_name, $value = false, $label = false, $attributes = false, $echo = true) {
	
	$args = array ( 'type' => 'month',
					'field_id' => $field_id,
					'field_name' => $field_name,
					'value' => $value,
					'label' => $label,
					'attributes' => (array) $attributes
					);
					
	$month_field = new A5_FormField($args);
	
	if (false === $echo) return $month_field->formfield;
	
	echo $month_field->formfield;
	
}

/**
 *
 * function to get a number input field
 *
 */
 
function a5_number_field($field_id, $field_name, $value = false, $label = false, $attributes = false, $echo = true) {
	
	$args = array ( 'type' => 'number',
					'field_id' => $field_id,
					'field_name' => $field_name,
					'value' => $value,
					'label' => $label,
					'attributes' => (array) $attributes
					);
					
	$number_field = new A5_FormField($args);
	
	if (false === $echo) return $number_field->formfield;
	
	echo $number_field->formfield;
	
}

/**
 *
 * function to get a range input field
 *
 */
 
function a5_range_field($field_id, $field_name, $min, $max, $value = false, $label = false, $attributes = false, $echo = true) {
	 
	$attributes['min'] = $min;
	
	$attributes['min'] = $max;
	
	$args = array ( 'type' => 'range',
					'field_id' => $field_id,
					'field_name' => $field_name,
					'value' => $value,
					'label' => $label,
					'attributes' => (array) $attributes
					);
					
	$range_field = new A5_FormField($args);
	
	if (false === $echo) return $range_field->formfield;
	
	echo $range_field->formfield;
	
}

/**
 *
 * function to get a search input field
 *
 */
 
function a5_search_field($field_id, $field_name, $value = false, $label = false, $attributes = false, $echo = true) {
	
	$args = array ( 'type' => 'search',
					'field_id' => $field_id,
					'field_name' => $field_name,
					'value' => $value,
					'label' => $label,
					'attributes' => (array) $attributes
					);
					
	$search_field = new A5_FormField($args);
	
	if (false === $echo) return $search_field->formfield;
	
	echo $search_field->formfield;
	
}

/**
 *
 * function to get a tel input field
 *
 */
 
function a5_tel_field($field_id, $field_name, $value = false, $label = false, $attributes = false, $echo = true) {
	
	$args = array ( 'type' => 'tel',
					'field_id' => $field_id,
					'field_name' => $field_name,
					'value' => $value,
					'label' => $label,
					'attributes' => (array) $attributes
					);
					
	$tel_field = new A5_FormField($args);
	
	if (false === $echo) return $tel_field->formfield;
	
	echo $tel_field->formfield;
	
}

/**
 *
 * function to get a time input field
 *
 */
 
function a5_time_field($field_id, $field_name, $value = false, $label = false, $attributes = false, $echo = true) {
	
	$args = array ( 'type' => 'time',
					'field_id' => $field_id,
					'field_name' => $field_name,
					'value' => $value,
					'label' => $label,
					'attributes' => (array) $attributes
					);
					
	$time_field = new A5_FormField($args);
	
	if (false === $echo) return $time_field->formfield;
	
	echo $time_field->formfield;
	
}

/**
 *
 * function to get a url input field
 *
 */
 
function a5_url_field($field_id, $field_name, $value = false, $label = false, $attributes = false, $echo = true) {
	
	$args = array ( 'type' => 'url',
					'field_id' => $field_id,
					'field_name' => $field_name,
					'value' => $value,
					'label' => $label,
					'attributes' => (array) $attributes
					);
					
	$url_field = new A5_FormField($args);
	
	if (false === $echo) return $url_field->formfield;
	
	echo $url_field->formfield;
	
}

/**
 *
 * function to get a week input field
 *
 */
 
function a5_week_field($field_id, $field_name, $value = false, $label = false, $attributes = false, $echo = true) {
	
	$args = array ( 'type' => 'week',
					'field_id' => $field_id,
					'field_name' => $field_name,
					'value' => $value,
					'label' => $label,
					'attributes' => (array) $attributes
					);
					
	$week_field = new A5_FormField($args);
	
	if (false === $echo) return $week_field->formfield;
	
	echo $week_field->formfield;
	
}

/***************************************************************************************************

	These are the specials:
	
	Functions to resize textareas or building fieldsets with checkboxes or radiobuttons
	
	a5_checkgroup($fieldset_id, $fieldset_name, $options, [$legend], [$checkall], [$attributes], [$echo])

	a5_resize_textarea(array($field_id), [$echo])

/***************************************************************************************************

/**
 *
 * function to get a group of checkboxes
 *
 */
 
function a5_checkgroup($fieldset_id, $fieldset_name, $item_options, $legend = false, $checkall = false, $attributes = false, $echo = true) {
	
	$eol = "\r\n";
	
	$boxes = '';
	
	$atts = '';
	
	if ($fieldset_id) $attributes['id'] = $fieldset_id;
	if ($fieldset_name) $attributes['name'] = $fieldset_name;
	
	$legend = ($legend) ? '<legend>'.$legend.'</legend>'.$eol : '';
	
	if ($attributes) foreach ($attributes as $key => $val) $atts .= ' '.$key.'="'.$val.'"';
	
	foreach($item_options as $options) :
	
		if (!isset($options[4])) :
			
			$options[4] = $options[2];
			
			$options[2] = true;
			
		endif;
		
		if ($options[4] == $options[2]) $options[5]['checked'] = 'checked';
		
		$attributes = (empty($options[5])) ? array() : (array) $options[5]; 
		
		$args = array ( 'type' => 'checkbox',
						'field_id' => $options[0],
						'field_name' => $options[1],
						'value' => $options[2],
						'label' => $options[3],
						'attributes' => $attributes
						);
						
		$checkbox = new A5_FormField($args);
		
		$boxes .= $checkbox->formfield;
		
	endforeach;
	
	if ($checkall) :
		
		$args = array ( 'type' => 'checkbox',
					'field_id' => $checkall[0],
					'field_name' => $checkall[1],
					'label' => $checkall[2],
					'attributes' => array ('space' => true)
					);
					
		$checkbox = new A5_FormField($args);
	
		$boxes .= $checkbox->formfield.'</ br>';
	
	endif;
	
	$boxes = str_replace('</label>', '</label></br>', $boxes); 
	
	$output = '<fieldset'.$atts.'>'.$eol.$legend.'<p>'.$eol.$boxes.$eol.'</p>'.$eol.'</fieldset>'.$eol;
	
	if (false === $echo) return $output;
	
	echo $output;
	
}

/**
 *
 * function to get a group of radio buttons
 *
 */
 
function a5_radiogroup($fieldset_id, $fieldset_name, $item_options, $legend = false, $attributes = false, $echo = true) {
	
	$eol = "\r\n";
	
	if ($fieldset_id) $attributes['id'] = $fieldset_id;
	if ($fieldset_name) $attributes['name'] = $fieldset_name;
	
	$legend = ($legend) ? '<legend>'.$legend.'</legend>'.$eol : '';
	
	if ($attributes) foreach ($attributes as $key => $val) $atts .= ' '.$key.'="'.$val.'"';
	
	foreach($item_options as $options) :
	
		if (false == $options[4]) :
			
			$options[4] = $options[2];
			
			$options[2] = true;
			
		endif;
		
		if ($options[4] == $options[2]) $options[5]['checked'] = 'checked';
		
		$args = array ( 'type' => 'radio',
						'field_id' => $options[0],
						'field_name' => $options[1],
						'value' => $options[2],
						'label' => $options[3],
						'attributes' => (array) $options[5]
						);
						
		$radio = new A5_FormField($args);
		
		$radios .= $radio->formfield;
		
	endforeach;
	
	$radios = str_replace('</label>', '</label></br>', $radios); 
	
	$output = '<fieldset'.$atts.'>'.$eol.$legend.'<p>'.$eol.$radios.$eol.'</p>'.$eol.'</fieldset>'.$eol;
	
	if (false === $echo) return $output;
	
	echo $output;
	
}
 
 /**
 *
 * function to resize text areas
 *
 */
 
function a5_resize_textarea($field_id, $echo = true) {
	
	$args = array ( 'type' => 'resize',
					'field_id' => (array) $field_id,
					'echo' => $echo
					);
					
	$resize = new A5_FormField($args);
	
	if (false === $echo) return $resize->formfield;
	
	echo $resize->formfield;

}

?>