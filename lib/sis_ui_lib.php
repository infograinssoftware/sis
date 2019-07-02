<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains main functions for RCYCI Module
 *
 * @since     Moodle 2.0
 * @package   format_rcyci
 * @copyright Muhammd Rafiq
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
   This file contain all the global functions for RCYCI module
*/

// This is the library for custom user interface
defined('MOODLE_INTERNAL') || die();

function sis_ui_page_title($text, $ret = false)
{	
    $str = html_writer::tag('h3', $text, array());
	$str = $str . '<hr />';
	if($ret)
		return $str;
	else
		echo $str;
}

//table is the standard moodle table object
function sis_ui_print_table($table, $responsive = true, $ret = false)
{
	$str = html_writer::table($table);
	if($responsive)
		$str = '<div class="table-responsive-md">' . $str . '</div>';
	if($ret)
		return $str;
	else
		echo $str;
}
//This create the title for the rc box
function sis_ui_box_heading($text, $id = null) 
{
	$level = 5;
	$classes = 'sis_block_heading';
    $level = (integer) $level;
    return html_writer::tag('h' . $level, $text, array('id' => $id, 'class' => renderer_base::prepare_classes($classes)));
}

//start of a box
function sis_ui_box_start($isPlain = false)
{
	global $OUTPUT;
	if($isPlain)
		$cls = 'sis_box';
	else
		$cls = 'sis_box_frontpage';
	return $OUTPUT->box_start($cls);
}

//start of a box
function sis_ui_box_end()
{
	global $OUTPUT;
	return $OUTPUT->box_end();
}

//this function print the box in one go
function sis_ui_box($text, $header = '', $ret=false)
{
	$str = '';
	if($header != '') //has header
	{
		$str = $str . sis_ui_box_heading($header);
		$isPlain = false;
	}
	else
		$isPlain = true;
	if($text != '')
	{
		$str = $str . sis_ui_box_start($isPlain);
		$str = $str . $text;
		$str = $str . sis_ui_box_end();
	}
	if(!$ret)
		echo $str;
	else
		return $str;
}

//print a square box
function sis_ui_square($text, $ret=false)
{
	global $OUTPUT;
	$str = $OUTPUT->box_start('sis_box_plain');
	$str = $str . $text;
	$str = $str . $OUTPUT->box_end();
	if(!$ret)
		echo $str;
	else
		return $str;
}

//$option = primary, secondary, success, danger, warning, info, light, dark
function sis_ui_alert($text, $option, $title = '', $close = true, $ret = false)
{
	$str = '
		<div class="alert alert-' . $option . '" role="alert">
	';
	if($close)
	{
		$str = $str . '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
	}
	if($title != '')
		$str = $str . '<h4 class="alert-heading">' . $title . '</h4>';
	$str = $str . '<p>' . $text . '</p>';
	$str = $str . '</div>';

	if($ret)
		return $str;
	else
		echo $str;
}

//$option = primary, secondary, success, danger, warning, info, light, dark
function sis_ui_label($text, $option, $ret = false)
{
	$option = 'label-' . $option;
	$str = '<span class="label '.$option.'">'.$text.'</span>';
	if($ret)
		return $str;
	else
		echo $str;
}

//$option = primary, secondary, success, danger, warning, info, light, dark
function sis_ui_badge($text, $option, $ret = false)
{
	$option = 'badge-' . $option;
	$str = '<span class="badge '.$option.'">'.$text.'</span>';
	if($ret)
		return $str;
	else
		echo $str;
}

function sis_ui_space($count = 1, $ret = true)
{
	$output = '';
	for($i = 0; $i < $count; $i++)
		$output .= '&nbsp;';
	if(!$ret)
		echo $output;
	else
		return $output;
}

//quick way to display a button
//primary, secondary, success, info, warning, danger, link
function sis_ui_button($text, $url, $type = 'info', $icon = '', $extra_class = '', $ret = true)
{
	$btn_text = $text;
	if($icon != '')
		$btn_icon = '<i class="fa fa-'.$icon.'" aria-hidden="true"></i>' . ' ';
	else
		$btn_icon = '';
	$btn_class = 'btn btn-' . $type;
	if($extra_class != '')
		$btn_class = $btn_class . ' ' . $extra_class;
	$output = html_writer::link($url, $btn_icon . $btn_text, array(
			'class' => $btn_class,
			'aria-label' => $btn_text,
			));
	if($ret)
		return $output;
	else
		echo $output;
}

//type: info, success, primary, danger, warning. ... (standard bootstrap)
function sis_ui_button_link($url, $text, $type)
{
	return html_writer::link($url, $text, array('title' => $text, 'class' => 'btn btn-' . $type));
}
/////////Form helper function///////////////////////
//element is an associative array of value => label
function sis_ui_radio($name, $element, $selected)
{
	$str = '';
	foreach($element as $key => $value)
	{
		if($str != '')
			$str = $str . '&nbsp;';
		if($selected == $key)
			$checked = 'checked';
		else
			$checked = '';
		$str = $str . '<input type="radio" name="'.$name.'" value="'.$key.'" '.$checked.' /> ' . $value;
	}
	return $str;
}

function sis_ui_input($name, $size, $value = '', $onkeypress='')
{
	if($onkeypress != '')
		$okp = 'onkeypress="'.$onkeypress.'"';
	else
		$okp = '';
	$str = '<input name="'.$name.'" type="text" id="'.$name.'" size="'.$size.'" value="'.$value.'" '.$okp.'/>';
	return $str;
}

function sis_ui_textarea($name, $rows, $cols, $value = '', $onkeypress='')
{
	if($onkeypress != '')
		$okp = 'onkeypress="'.$onkeypress.'"';
	else
		$okp = '';
	$str = '<textarea name="'.$name.'" id="'.$name.'" rows="'.$rows.'" cols="'.$cols.'" '.$okp.'/>' . $value . '</textarea>';
	return $str;
}

function sis_ui_select($name, $options, $value = '', $onchange = '', $styles = '')
{
	if($onchange != '')
		$oc = 'onchange="'.$onchange.'"';
	else
		$oc = '';
	if($styles != '')
		$st = 'style="'.$styles.'"';
	else
		$st = '';
	$str = '<select class="custom-select" name="'.$name.'" id="'.$name.'" '.$oc.' '.$st.'>';
	foreach($options as $key => $label)
	{
		if($key == $value)
			$selected = 'selected';
		else
			$selected = '';
		$str = $str . '<option value="'.$key.'" '.$selected.'>'.$label.'</option>';
	}
	$str = $str . '</select>';
	return $str;
}

function sis_ui_hidden($name, $value)
{
	$str = '<input type="hidden" name="'.$name.'" id="'.$name.'" value="'.$value.'">';
	return $str;
}

function sis_week_dropdown($name, $value='', $onchange='')
{	
	$weeks = sis_academic_week();
	return sis_ui_select($name, $weeks, $value, $onchange);
}
