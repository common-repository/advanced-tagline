<?php
/*
 Plugin Name: Advanced Tagline
 Plugin URI: http://kmorey.net/downloads/advanced-tagline-wordpress-plugin
 Description: This plugin gives the option to have multiple taglines for your blog and display them at random or sequentially with each page view
 Author: Kevin Morey
 Version: 1.5.3
 Author URI: http://kmorey.net

 ==
 Copyright 2008-2009  Kevin Morey  (email : kevin@kmorey.net)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

define(ADVTAG_VERSION, "1.5.3");
define(ADVTAG_MODE, "advtag_mode");
define(ADVTAG_TAGPAIRS, "advtag_tags");
define(ADVTAG_TYPE, "advtag_type");

global $advtag_mode;
global $advtag_tags;
global $advtag_type;
$advtag_mode = get_option(ADVTAG_MODE);
$advtag_tags = get_option(ADVTAG_TAGPAIRS);
$advtag_type = get_option(ADVTAG_TYPE);

if (!isset($advtag_tags)) {
	$advtag_tags = array();
}

function advtag_options_page()
{
	global $advtag_tags, $advtag_type, $advtag_mode;

	$message="";
	$error = FALSE;

	//batch import
	if (!empty($_POST['import'])) {
		$fileName = $_FILES['importfile']['tmp_name'];

		if (empty($fileName)) {

			$message = "Import failed: no file specified.";
			$error = TRUE;

		} else {

			$tagLines = advtag_parse_csv($fileName);

			if (!is_array($tagLines)) {
				//error handling
				$message.=__('Parse import file failed. Ensure file is in correct format.', 'advtag');
				$message.="<br />$tagLines";
				$error = TRUE;
			} else {
				if ($_POST['import_type'] == 'replace') { $advtag_tags = array(); }

				foreach ($tagLines as $tagLine) {
					$advtag_tags[] = $tagLine;
				}

				update_option(ADVTAG_TAGPAIRS, $advtag_tags);
				$message.=__('Import successful.', 'advtag');
			}
		}
	}
	else if (!empty($_POST['export'])) {
		advtag_export();
		exit;
	}

	include("settings.php");

	/*
	$script = "<script type='text/javascript'>\n  //<![CDATA[\n";
	//do add stuff
	foreach($advtag_tags as $tagset) {
		$text = $tagset[0];
		$link = $tagset[1];
		$target = $tagset[2];
		$script .= "    advtag_newTagline(\"$text\",\"$link\",\"$target\");\n";
	}
	$script .= "  //]]>\n</script>\n";
	print($script);
*/
}

function advtag_export() {

	$advtag_tags = get_option(advtag_tags);
	$export = '';
	
	switch($_POST['export_type']) {
		case 'csv':
			header('Content-type: text/csv');
			header('Content-Disposition: attachment; filename="export.csv"');
			foreach ($advtag_tags as $tagset) {
				$export .= '"'.stripslashes($tagset[0]).'","'.stripslashes($tagset[1]).'","'.stripslashes($tagset[2]).'"'."\n";
			}
		break;

	}

	header('Content-length: '.strlen($export));
	echo $export;
	exit;
}

function advtag_parse_csv($fileName) {
	$results = array();
	$handle = fopen($fileName, "r");

	while (($data = fgetcsv($handle, 4096)) !== FALSE) {
		//skip blank lines, which return an array comprising a single null field
		if (count($data) > 0 && $data[0] == null) { continue; }

		if (count($data) == 1) {
			$results[] = array($data[0], '', '');
		}
		else if (count($data) == 2) {
			$results[] = array($data[0], $data[1], '');
		}
		else if (count($data) == 3) {
			$results[] = $data;
		} else {
			//error: invalid format
			return "Invalid format (1-3 columns expected, found ".count($data).").";
		}

	}
	fclose($handle);

	return $results;
}

function prepare_for_input($value) {
	return str_replace('"', '&quot;', $value);
}

function advtag_update_option($key, $value) {
	if( get_magic_quotes_gpc() ) {
		$value = stripslashes($value);
	}
	update_option($key, $value);
}

function advtag_admin_menu() {
	if (function_exists('add_options_page'))
	{
		$mypage = add_options_page('Advanced Tagline', 'Advanced Tagline', 8, basename(__FILE__), 'advtag_options_page');
		add_action( "admin_print_scripts-$mypage", 'advtag_admin_head' );
	}
}

function advtag_scripts() {
	$path = get_option('siteurl')."/wp-content/plugins/advanced-tagline/";
	wp_enqueue_script('advtag', $path.'advtag.js', array('jquery'), '1.0' );
}

function advtag_increment_tagline() {
	global $advtag_type;
	global $advtag_tags;

	//i haven't figured out why yet (not much looking) but the header file is parsed when the
	//.css is imported so skip that
	if ($advtag_type == "sequential" && !preg_match("/\.css$/", $_SERVER['HTTP_REFERER'])) {
		$idx = $_COOKIE["advtag_idx"] + 1;
		if ($idx >= count($advtag_tags)) { $idx = 0; }
		setcookie("advtag_idx", $idx, 0, "/");
	}
}

function advtag_get_tagline($show_link = TRUE, $echo = TRUE) {
	global $advtag_tags;
	global $advtag_type;
	$idx = 0;

	if ($advtag_type == "sequential") {
		$idx = $_COOKIE["advtag_idx"];
	} else {
		$idx = mt_rand(0, count($advtag_tags) - 1);
	}

	if (empty($idx)) { $idx = 0; }

	$text = stripslashes($advtag_tags[$idx][0]);
	$link = stripslashes($advtag_tags[$idx][1]);
	$target = stripslashes($advtag_tags[$idx][2]);

	if ($show_link AND !empty($link)) {
		$t = '';
		if (!empty($target)) { $t = ' target="'.$target.'"'; }

		$output = '<a href="'.$link.'"'.$t.'>'.$text.'</a>';
	} else {
		$output = $text;
	}

	if ($echo) {
		echo $output;
	} else {
		return $output;
	}
}

function advtag_admin_head() {
	$eol = "\n";

	$path = get_option('siteurl')."/wp-content/plugins/advanced-tagline/";
	$html.= "<link rel=\"stylesheet\" href=\"".$path."advtag.css\" type=\"text/css\" media=\"screen\" />\n";
/* 	$html.= "<link rel=\"stylesheet\" href=\"".$path."thickbox.css\" type=\"text/css\" media=\"screen\" />\n";  */
/* 	$html.= '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>'; */
/* 	$html.= "<script type=\"text/javascript\" src=\"".$path."thickbox-compressed.js\"></script>\n"; */
/* 	$html.= "<script type=\"text/javascript\" src=\"".$path."advtag.js\"></script>\n"; */

/*
	$html.="
		<script type=\"text/javascript\">
		//<![CDATA[
			advtag_pluginDir = '".$path."';
			advtag_blogHome = '".get_option('siteurl')."';
			advtag_ajaxUrl = '".get_option('siteurl')."/wp-admin/admin-ajax.php';
		//]]>
		</script>
	";
*/

	
	print($html);
}

function advtag_replace_tagline($info, $show)
{
	global $advtag_mode;
	if (($show == 'advtag') OR ($advtag_mode != 'standalone' AND $show == 'description'))
	{
		$info = advtag_get_tagline(TRUE, FALSE);
	}

	//	return "<!-- (advtag_mode = $advtag_mode, info = $info, show = $show) -->\n".$info;
	return $info;
}

function advtag_widget_control() {
	$options = $newoptions = get_option('advtag_widget');
	if ( $_POST["advtag-submit"] ) {
		$newoptions['title'] = strip_tags(stripslashes($_POST["advtag-title"]));
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('advtag_widget', $options);
	}
	$title = attribute_escape($options['title']);
	?>
<p><label for="advtag-title"><?php _e('Title:'); ?>
	<input class="widefat" id="advtag-title" name="advtag-title" type="text" value="<?php echo $title; ?>" />
	</label>
</p>
<input type="hidden" id="advtag-submit" name="advtag-submit" value="1" />
	<?php
}

function advtag_init_widget() {
	// Check for required functions
	if (!function_exists('register_sidebar_widget'))
		return;

	function advtag_register_widget($args)
	{
		$options = get_option('advtag_widget');

		extract($args);
		echo $before_widget;
		echo $before_title.$options['title'].$after_title;
		advtag_get_tagline(TRUE, TRUE);
		echo $after_widget;
	}

	register_sidebar_widget('Advanced Tagline','advtag_register_widget');
	register_widget_control('Advanced Tagline','advtag_widget_control');
}

// ajax methods
function advtag_save() {
	global $advtag_tags;

	$index = (int)$_POST['index'];
	$text = $_POST['text'];
	$link = $_POST['link'];
	$target = $_POST['target'];
	
	$data = array(
		'code' => 200,
		'data' => 'Success'
	);
	
	// TODO: error checking
	
	$arr = array($text, $link, $target);
	
	if ($index !== -1) {
		$advtag_tags[$index] = $arr;
	}
	else {
		$advtag_tags[] = $arr;
	}
	
	update_option(ADVTAG_TAGPAIRS, $advtag_tags);
	
	array_walk_recursive($advtag_tags, arr_stripslashes);
/* 	$data['debug'] = $advtag_tags; */
	
	echo json_encode($data);
	exit;
}

function arr_stripslashes(&$item, $key)
{
	$item = stripslashes($item);
}

function advtag_moveUp(){
	global $advtag_tags;
	
	$index = (int)$_POST['index'];

	$data = array(
		'code' => 200,
		'data' => 'Success'
	);

	if ($index == 0 || $index >= count($advtag_tags)) {
		$data['code'] = 400;
		$data['data'] = 'bad index: '.$index;
	}
	else {
		$temp = $advtag_tags[$index-1];
		$advtag_tags[$index-1] = $advtag_tags[$index];
		$advtag_tags[$index] = $temp;
	
		update_option(ADVTAG_TAGPAIRS, $advtag_tags);
	}
	
	$data['debug'] = $advtag_tags;
	
	echo json_encode($data);
	exit;
}

function advtag_moveDown(){
	global $advtag_tags;
	
	$index = (int)$_POST['index'];

	$data = array(
		'code' => 200,
		'data' => 'Success'
	);

	if ($index < 0 || $index >= count($advtag_tags) - 1) {
		$data['code'] = 400;
		$data['data'] = 'bad index: '.$index;
	}
	else {
		$temp = $advtag_tags[$index+1];
		$advtag_tags[$index+1] = $advtag_tags[$index];
		$advtag_tags[$index] = $temp;
	
		update_option(ADVTAG_TAGPAIRS, $advtag_tags);
	}
	
	$data['debug'] = $advtag_tags;
	
	echo json_encode($data);
	exit;
}

function advtag_remove(){
	global $advtag_tags;
	
	$index = (int)$_POST['index'];

	$data = array(
		'code' => 200,
		'data' => 'Success'
	);

	if ($index < 0 || $index >= count($advtag_tags)) {
		$data['code'] = 400;
		$data['data'] = 'bad index: '.$index;
	}
	else {
		unset($advtag_tags[$index]);
		//unsetting an index will json_encode to assoc array, so use array_values to reset indices
		$advtag_tags = array_values($advtag_tags); 
		update_option(ADVTAG_TAGPAIRS, $advtag_tags);
	}
	
	$data['debug'] = $advtag_tags;
	
	echo json_encode($data);
	exit;
}

function advtag_save_options()
{
	$advtag_mode = $_POST[ADVTAG_MODE];
	$advtag_type = $_POST[ADVTAG_TYPE];

	update_option(ADVTAG_MODE, $advtag_mode);
	update_option(ADVTAG_TYPE, $advtag_type);

	$message.=__('Save successful.', 'advtag');
	
	$data = array(
		'code' => 200,
		'data' => $message
	);
	
	echo json_encode($data);
	exit;
}

function advtag_fetch_taglines()
{
	global $advtag_tags;
	
	array_walk_recursive($advtag_tags, arr_stripslashes);
	echo json_encode($advtag_tags);
	exit;
}

// add ajax methods
add_action('wp_ajax_advtag_moveUp', 'advtag_moveUp');
add_action('wp_ajax_advtag_moveDown', 'advtag_moveDown');
add_action('wp_ajax_advtag_save', 'advtag_save');
add_action('wp_ajax_advtag_save_options', 'advtag_save_options');
add_action('wp_ajax_advtag_export', 'advtag_export');
add_action('wp_ajax_advtag_remove', 'advtag_remove');
add_action('wp_ajax_advtag_fetch_taglines', 'advtag_fetch_taglines');

// Delay plugin execution until sidebar is loaded
add_action('widgets_init', 'advtag_init_widget');

add_action('init', 'advtag_scripts');
add_action('admin_menu', 'advtag_admin_menu');
add_action('get_header', 'advtag_increment_tagline');
add_filter('bloginfo','advtag_replace_tagline',10,2);


?>
