<?PHP
/*
Plugin Name: 	PWA+PHP Picasa Web Albums for Wordpress
Plugin URI: 	http://pwaplusphp.smccandl.net/
Description:	The best rated Picasa plugin for Wordpress, PWA+PHP, allows you to display public and private (unlisted) Picasa albums on your site in your language!
Author: 	Scott McCandless
Version:	0.9.6
Author URI: 	http://pwaplusphp.smccandl.net/
*/

function doCurlExec($file) {

	$PUBLIC_ONLY = get_option("pwaplusphp_public_only","TRUE");
	#----------------------------------------------------------------------------
	# Curl code to store XML data from PWA in a variable
	#----------------------------------------------------------------------------
	$ch = curl_init();
	$timeout = 0; // set to zero for no timeout
	curl_setopt($ch, CURLOPT_URL, $file);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	
	# Display only public albums if PUBLIC_ONLY=TRUE in config.php
	if ($PUBLIC_ONLY == "FALSE") {
		$GDATA_TOKEN = get_option("pwaplusphp_gdata_token");
	        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
       	         'Authorization: AuthSub token="' . $GDATA_TOKEN . '"'
	        ));
	}

	$addressData = curl_exec($ch);
	curl_close($ch);

	#----------------------------------------------------------------------------
	# Parse the XML data into an array
	#----------------------------------------------------------------------------
	$p = xml_parser_create();
	xml_parse_into_struct($p, $addressData, $vals, $index);
	xml_parser_free($p);

	return ($vals);

}

/**
* Hook to delete PWA+PHP 
*/
if ( function_exists('register_uninstall_hook') )
	register_uninstall_hook(__FILE__, 'pwaplusphp_remove');
 
/**
* Delete PWA+PHP options in database
*/
function pwaplusphp_remove() {
 
	delete_option("pwaplusphp_gdata_token");
	delete_option("pwaplusphp_picasa_username");
	delete_option("pwaplusphp_image_size");
	delete_option("pwaplusphp_thumbnail_size");
	delete_option("pwaplusphp_album_thumbsize");
	delete_option("pwaplusphp_require_filter");
	delete_option("pwaplusphp_images_per_page");
	delete_option("pwaplusphp_albums_per_page");
	delete_option("pwaplusphp_public_only");
	delete_option("pwaplusphp_album_details");
	delete_option("pwaplusphp_updates");
	delete_option("pwaplusphp_show_dropbox");
	delete_option("pwaplusphp_truncate_names");
	delete_option("pwaplusphp_version");
	delete_option("pwaplusphp_language");
	delete_option("pwaplusphp_permit_download");
	delete_option("pwaplusphp_description_length");
	delete_option("pwaplusphp_caption_length");
	delete_option("pwaplusphp_date_format");
	delete_option("pwaplusphp_crop_thumbs");
	delete_option("pwaplusphp_hide_video");

}

/**
* Add shortcode for embedding the albums in pages
*/
function pwaplusphp_shortcode( $atts, $content = null ) {

        extract(shortcode_atts(array("album" => 'NULL'), $atts));
        extract(shortcode_atts(array("filter" => ''), $atts));
		extract(shortcode_atts(array("tag" => 'NULL'), $atts));
		# Overrides
		extract(shortcode_atts(array("images_per_page" => 'NULL'), $atts));
		extract(shortcode_atts(array("image_size" => 'NULL'), $atts));
		extract(shortcode_atts(array("thumbnail_size" => 'NULL'), $atts));
		extract(shortcode_atts(array("picasaweb_user" => 'NULL'), $atts));

		if (($images_per_page != "") && ($images_per_page != "NULL"))
			$overrides_array["images_per_page"] = $images_per_page;
		if (($image_size) && ($image_size != "NULL"))
			$overrides_array["image_size"] = $image_size;
		if (($thumbnail_size) && ($thumbnail_size != "NULL"))
			$overrides_array["thumbnail_size"] = $thumbnail_size;
		if (($picasaweb_user) && ($picasaweb_user != "NULL"))
			$overrides_array["picasaweb_user"] = $picasaweb_user;

        if (($album == "NULL") && (!isset($_GET["album"])) && ($tag == "NULL")) {
                $out = dumpAlbumList($filter);
                return($out);
        } else {
                if ($album != "NULL") {
                        if ($album != "random_photo") {
                                $out = showAlbumContents($album,"TRUE",$tag,$overrides_array);
                        } else {
                                $out = get_include_contents(dirname(__FILE__).'/one_random.php');
                        }
                } else if (isset($_GET["album"])) {
                        $album = $_GET["album"];
                        $out = showAlbumContents($album,"FALSE",$tag,$overrides_array);
                } else if ($tag != "NULL") {
			$out = showAlbumContents($album,"FALSE",$tag,$overrides_array);
		}
        return($out);
        }

}

/**
* Includes
*/
require_once(dirname(__FILE__).'/showAlbumContents.php');
require_once(dirname(__FILE__).'/dumpAlbumList.php');
require_once(dirname(__FILE__).'/includes/pwaplusphp_functions.php');

/**
* Add shortcode for embedding the albums in pages 
*/
add_shortcode('pwaplusphp', 'pwaplusphp_shortcode');

/**
* Installer / Options page
*/
function pwaplusphp_options() {
  echo '<div class="wrap">';
  require_once(dirname(__FILE__).'/install.php');
  echo '</div>';
}

/**
* Define Option settings 
*/
function pwaplusphp_menu() {
  add_options_page('PWA+PHP Options', 'PWA+PHP', 'administrator', 'pwaplusphp', 'pwaplusphp_options');
}

/**
* Add PWA+PHP to Settings Menu 
*/
add_action('admin_menu', 'pwaplusphp_menu');
add_filter('widget_text', 'do_shortcode');

/**
* Setup CSS
**/
add_action('wp_head', 'addHeaderCode');
function addHeaderCode() {
	 echo '<link rel="stylesheet" type="text/css" href="' . WP_PLUGIN_URL . '/pwaplusphp/css/style.css" />';
}

function get_include_contents($filename) {
    if (is_file($filename)) {
        ob_start();
        include $filename;
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }
    return false;
}

/**
 * Add Settings link to plugins - code from GD Star Ratings
 */
add_filter('plugin_action_links', 'add_settings_link', 10, 2 );
function add_settings_link($links, $file) {
	static $this_plugin;
	if (!$this_plugin) $this_plugin = plugin_basename(__FILE__);
	if ($file == $this_plugin){
		$settings_link = '<a href="options-general.php?page=pwaplusphp">'.__("Settings", "pwaplusphp").'</a>';
		array_unshift($links, $settings_link);
	}
	return $links;
}
?>
