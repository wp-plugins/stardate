<?php
/*
Plugin Name: Stardate
Plugin URI: http://seoserpent.com/wordpress/stardate
Version: 1.0
License: GPL2
Description: Convert WordPress dates in your theme to the current stardate.  Also adds a custom function and a few shortcodes for use in themes and posts/pages.
Author: Marty Martin
Author URI: http://seoserpent.com/wordpress/
*/

// Get date/time for current post and create the stardate
function stardate_post() {
	global $post;	
	$options = get_option('stardate_options');
	
	// date variables
	$stardate = '';
	$stardate_ISOdate_string = mysql2date('c', $post->post_date);
	$stardate_ISOdate = strtotime($stardate_ISOdate_string);
	
	$stardate_earthtime_year = date("y", $stardate_ISOdate);
	$stardate_earthtime_year4 = date("Y", $stardate_ISOdate);
	$stardate_earthtime_month = date("m", $stardate_ISOdate);
	$stardate_earthtime_day = date("d", $stardate_ISOdate);
	//$stardate_earthtime_hour = date("G", $stardate_ISOdate);
	//$stardate_earthtime_minute = date("i", $stardate_ISOdate);
	$stardate_earthtime_dayofyear = date("z", $stardate_ISOdate);
	
	// constant: solar days in a year
	$solar_days = 365.2422;
	
	if ($options['prefix']) {
		$stardate .= $options['prefix'] . " ";
	}
	
	switch($options['style']) {
		case 'Classic' :
			$stardate .= "1" . $stardate_earthtime_year . $stardate_earthtime_month . "." . $stardate_earthtime_day;
		break;
		case 'XI' : 
			$stardate .= $stardate_earthtime_year4;
			$sd = round(intval($stardate_earthtime_dayofyear)/$solar_days, 2);
			$stardate .= substr($sd,-3);
		break;
		default :
			$stardate .= "1" . $stardate_earthtime_year . $stardate_earthtime_month . "." . $stardate_earthtime_day . " - default";
	}
	return $stardate;
}

// Get current date/time for display in the theme
function stardate_now($style) {
	$options = get_option('stardate_options');
	
	// date variables
	$stardate = '';
	$stardate_earthtime_year = date("y");
	$stardate_earthtime_year4 = date("Y");
	$stardate_earthtime_month = date("m");
	$stardate_earthtime_day = date("d");
	//$stardate_earthtime_hour = date("G");
	//$stardate_earthtime_minute = date("i");
	$stardate_earthtime_dayofyear = date("z");
	
	// constant: solar days in a year
	$solar_days = 365.2422;
	
	$stardate .= "<span class=\"stardate\">";
	
	switch($style) {
		case 'classic' :
			$stardate .= "1" . $stardate_earthtime_year . $stardate_earthtime_month . "." . $stardate_earthtime_day;
		break;
		case 'XI' : 
			$stardate .= $stardate_earthtime_year4;
			$sd = round(intval($stardate_earthtime_dayofyear)/$solar_days, 2);
			$stardate .= substr($sd,-3);
		break;
		default :
			$stardate .= "1" . $stardate_earthtime_year . $stardate_earthtime_month . "." . $stardate_earthtime_day . " - default";
	}
	
	$stardate .= "</span>";
	
	return $stardate;
}

// Creates the stardate shortcode (ie: [stardate style=""]) See admin page for available styles
function stardate_shortcode($atts) {
	extract(shortcode_atts(array(
		'style' => 'classic'
	), $atts));
	$stardate_shortcode = stardate_now($style);
	return $stardate_shortcode;
}
add_shortcode('stardate','stardate_shortcode');

// Create function wrapper for displaying the stardate in the theme
function stardate_theme($style) {
	echo stardate_now($style);
}

// If theme display is enabled from admin, then add filter to the_time
$options = get_option('stardate_options');
if ($options['option_set1'] === "Enabled") {
	add_filter('the_time', 'stardate_post');
}



// ADMIN PAGES
add_action('admin_menu','stardate_menu');
function stardate_menu() {
	add_options_page('Stardate Setup','Stardate','manage_options','stardate','stardate_admin');
}

add_action('admin_init','stardate_admin_init');
function stardate_admin_init() {
	register_setting('stardate_plugin_options','stardate_options');
	add_settings_section('stardate_image','','stardate_image_html','stardate_options');
	add_settings_section('stardate_theme_settings','Stardate Theme Settings','stardate_section_text','stardate_options');
	add_settings_field('stardate_field','','stardate_field_string','stardate_options','stardate_theme_settings');
	add_settings_field('stardate_style','','stardate_style_string','stardate_options','stardate_theme_settings');
	add_settings_field('stardate_text','','stardate_text_string','stardate_options','stardate_theme_settings');
	add_settings_section('stardate_shortcodes','Stardate Shortcodes','stardate_shortcode_text','stardate_options');
}

function stardate_field_string() {
	$options = get_option('stardate_options');
	$items = array("Enabled","Disabled");
	echo "<tr valign=\"top\">
			<th scope\"row\" valign=\"top\"><label for=\"stardate_field\">Stardates In Theme Posts/Pages:</label></th>
			<td>";
	foreach($items as $item) {
		$checked = ($options['option_set1']==$item) ? ' checked="checked" ' : '';
		echo "<input ".$checked." value='$item' name='stardate_options[option_set1]' type='radio' /> <label>$item</label>&nbsp;&nbsp;";
	}
	echo "<br />Tick \"Enabled\" if you want the post/page publication dates on the public side of your website/blog posts and pages to show the stardate instead of the traditional date.";
	echo "</td></tr>";
}

function stardate_style_string() {
	$options = get_option('stardate_options');
	$items = array("Classic","XI");
	echo "<tr valign=\"top\">
			<th scope\"row\" valign=\"top\"><label for=\"stardate_field\">Stardate Format For Post Times:</label></th>
			<td>";
	foreach($items as $item) {
		$checked = ($options['style']==$item) ? ' checked="checked" ' : '';
		echo "<input ".$checked." value='$item' name='stardate_options[style]' type='radio' /> <label>$item</label>&nbsp;&nbsp;";
	}
	echo '<br />Select the stardate format you prefer to be displayed in place of the traditional date on your site posts/pages.<br />"Classic" refers to the Original Star Trek television series and movies.  "XI" refers to the most recent Star Trek movie released in 2009. (More options coming soon!)';
	echo "</td></tr>";
}

function stardate_text_string() {
	$options = get_option('stardate_options');
	echo "
		<tr valign=\"top\">
			<th scope=\"row\" valign=\"top\"><label for=\"stardate_text_string\">Stardate Prefix Text:</label></th>
			<td><input id='stardate_text_string' name='stardate_options[prefix]' size='40' type='text' value='{$options['prefix']}' /> <label for=\"stardate_options[prefix]\"><br />
			What, if anything, do you want to come before the actual stardate in your posts date line?<br />(ie: instead of just having the actual date \"11001.29\", you could add \"Stardate\" to the above box and get \"Stardate 11001.29\"</td>
		</tr>
	";
}

function stardate_section_text() {
	echo "
	<p>The Stardate Plugin will allow you to display stardates on your WordPress-powered website in your existing theme.</p>
    <p>You can choose to have your post and page publication dates/times replaced with the current stardate in either the classic Star Trek series format or the latest movie (Star Trek XI) format.</p>
	";
}

function stardate_shortcode_text() {
	echo "
	<p>The Stardate Plugin will also allow you to use WordPress shortcodes and/or a function to display the current stardate in a post, page or a custom place in your theme.</p>
    <p>You can use a custom shortcode to display an instance of either the <a href=\"http://mear.is/trekmovies\" target=\"blank\">classic movies</a> / <a href=\"http://mear.is/startrek\" target=\"blank\">television series</a> or <a href=\"http://mear.is/startrekxi\" target=\"blank\">Star Trek XI</a> stardate within a post or page by using:</p>
	<p><code>[stardate style=\"classic\"]</code> or,<br /><code>[stardate style=\"XI\"]</code></p>
	<p>And yes, more options will be coming soon for TNG, etc.</p>
	<p>You can also use one of the following functions to display the current stardate in your theme (for example, in your header or footer):</p>
	<p><code>&lt;?php stardate_theme('classic'); ?&gt;</code><br /><code>&lt;?php stardate_theme('XI'); ?&gt;</code></p>
	";
}

function stardate_image_html() {
	$path_to_plugin = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
	echo '<a href="http://seoserpent.com/wordpress/stardate" target="_blank"><img src="' .$path_to_plugin . 'stardate.png" width="300" height="73" alt="Stardate" border="0" /></a>';
}

function stardate_admin() {
?>
	<div class="wrap">
    <h2>Stardate Display Setup</h2>
    <form action="options.php" method="post">
    <table class="form-table">
    <?php 
		settings_fields('stardate_plugin_options');
		do_settings_sections('stardate_options'); 
	?>
    </table>
    <p class="submit"><input type="submit" name="submit" value="Update options &raquo;" /></p>
    </form>
    </div>
<?php
}

?>