<?php
/**
 * Plugin Name: Flash Image
 * Plugin URI: http://djken2006.googlepages.com/
 * Description: This plugin dispaly images randomly using a flash container
 * Version: 1.1.1
 * Author: Djken
 * Author URI: http://djken2006.googlepages.com/
 */

/*
Copyright 2008  Djken

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

//error_reporting(E_ALL);

define('SITE_URL', get_option('siteurl'));
define('FLASH_IMAGE_PLUGIN_URL', SITE_URL . '/' . str_replace(str_replace('\\', '/', ABSPATH), '', str_replace('\\', '/', dirname(__FILE__))));
define('USER_LEVEL', 8);	//user access level

register_activation_hook(__FILE__, 'fi_activation');
register_deactivation_hook(__FILE__, 'fi_deactivation');

add_action('widgets_init', 'fi_register_widget');

/**
 * Add default options of image widget
 */
function fi_activation() {
	$fi_image_option = array(
	'image_title' => '',
	'image_path' => 'wp-content/images',
	'image_height' => 150,
	'image_width' => 200,
	'image_interval' => 5,
	'image_num' => 3
	);

	update_option('fi_image_option', $fi_image_option);
}

/**
 * Remove options of flash image widget
 */
function fi_deactivation() {
	delete_option('fi_image_option');
}

function fi_register_widget() {
	if (!function_exists('register_widget_control') || !function_exists('register_sidebar_widget')) return ;

	register_sidebar_widget('Flash Image', 'fi_display_images');
	register_widget_control('Flash Image', 'fi_image_control', 400, 300);
}

function fi_fetch_images($path) {

	$abs_path = ABSPATH . $path;

	$images = array();

	if ($handle = opendir($abs_path)) {
		while (($file = readdir($handle)) !== false) {
			$ext = substr($file, strrpos($file, '.')+1);  //get the extension of a file
			if (in_array($ext, array('jpg', 'gif', 'png'))) {
				$images[] = urlencode(SITE_URL . str_replace('\\', '/', "/$path/$file")); //compose the full path of an image
			}
		}

		closedir($handle);
	}

	shuffle($images); //make the images randomly
	return $images;
}

function fi_display_images($args) {

	extract($args);

	echo $before_widget;

	//get flash image's options
	$image_option = get_option('fi_image_option');

	$image_title = $image_option['image_title'];
	$image_path = $image_option['image_path'];
	$image_width = $image_option['image_width'];
	$image_height = $image_option['image_height'];
	$image_num = $image_option['image_num'];
	$image_interval = $image_option['image_interval'];

	if (!empty($image_title)) {
		echo  $before_title . $image_title . $after_title;
	}

	//get all images in that folder
	$images = fi_fetch_images($image_path);

	//we only need several images in the array
	$images = array_slice($images, 0, $image_num);
	$image_url = implode('|', $images);

	//output the html tag to display the flash
	echo '<div id="flash_image">';
	echo '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="'.$image_width.'" height="'.$image_height.'">';
	echo '<param name="movie" value="'.FLASH_IMAGE_PLUGIN_URL.'/pixviewer.swf'.'">';
	echo '<param name="quality" value="high">';
	echo '<param name="flashvars" value="pics='.$image_url.'&borderwidth='.$image_width.'&borderheight='.$image_height.'&textheight=0&interval_time='.$image_interval.'">';
	echo '<embed src="'.FLASH_IMAGE_PLUGIN_URL.'/pixviewer.swf" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" ';
	echo 	'width="'.$image_width.'" height="'.$image_height.'" ';
	echo 	'flashvars="pics='.$image_url.'&borderwidth='.$image_width.'&borderheight='.$image_height.'&textheight=0&interval_time='.$image_interval.'">';
	echo '</embed>';
	echo '</object>';
	echo '</div>';

	//
	echo $after_widget;

}

function fi_image_control() {

	$fi_image_option = get_option('fi_image_option');

	//The option is not set
	if (empty($fi_image_option)) {
		$fi_image_option = array(
		'image_title' => '',
		'image_path' => 'wp-content/images',
		'image_height' => 150,
		'image_width' => 200,
		'image_interval' => 5,
		'image_num' => 3
		);
	}

	if (isset($_POST['submit'])) {
		$fi_image_option['image_title'] = stripslashes(trim($_POST['image_title']));
		$fi_image_option['image_path'] = stripslashes(trim($_POST['image_path']));
		$fi_image_option['image_height'] = intval($_POST['image_height']);
		$fi_image_option['image_width'] = intval($_POST['image_width']);
		$fi_image_option['image_interval'] = intval($_POST['image_interval']);
		$fi_image_option['image_num'] = intval($_POST['image_num']);

		update_option('fi_image_option', $fi_image_option);
	}
?>
 <input type="hidden" name="submit" value="1"/>
<p>
  <label>Title:(Optional)</label>
  <input type="text" name="image_title" value="<?php echo $fi_image_option['image_title']; ?>" />
</p>
<p>
  <label>Relative path of image directory:(e.g. wp-content/images)</label>
</p>
<p><?php echo ABSPATH; ?>
  <input type="text" name="image_path" value="<?php echo $fi_image_option['image_path']; ?>" size="25"/>
</p>
<p>
  <label>The size of the flash container:</label>
  <br>
  Height:
  <input type="text" name="image_height" size="5" value="<?php echo $fi_image_option['image_height']; ?>">
  Width:
  <input type="text" name="image_width" size="5" value="<?php echo $fi_image_option['image_width']; ?>">
</p>
<p>
  <label>Interval of the flash(second):</label>
  <input type="text" name="image_interval" size="5" value="<?php echo $fi_image_option['image_interval']; ?>">
</p>
<p>
  <label>How many images to display:</label>
  <select name="image_num">
    <?php
    for ($i = 2; $i <= 8; $i++) {
    	echo "<option value='$i' ". ($fi_image_option['image_num']==$i?'selected':'') . ">$i</option>";
    }
	?>
  </select>
</p>
<?php
}
