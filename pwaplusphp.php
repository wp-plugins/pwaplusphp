<?PHP
/*
Plugin Name: 	PWA+PHP	
Plugin URI: 	http://pwaplusphp.smccandl.net/
Description:	PWA+PHP allows you to display public and private (unlisted) Picasa albums within WordPress in your language using Fancybox, Shadowbox or Lightbox.	
Author: 	Scott McCandless
Version:	0.2
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

function dumpAlbumList($FILTER) {

$USE_LIGHTBOX="TRUE";
$STANDALONE_MODE="TRUE";

$GDATA_TOKEN		= get_option("pwaplusphp_gdata_token");
$PICASAWEB_USER	 	= get_option("pwaplusphp_picasa_username");
$IMGMAX		 	= get_option("pwaplusphp_image_size","640");
$THUMBSIZE	 	= get_option("pwaplusphp_thumbnail_size",160);
$REQUIRE_FILTER  	= get_option("pwaplusphp_require_filter","FALSE");
$IMAGES_PER_PAGE 	= get_option("pwaplusphp_images_per_page",0);
$PUBLIC_ONLY 	 	= get_option("pwaplusphp_public_only","TRUE");
$SHOW_ALBUM_DETAILS  	= get_option("pwaplusphp_album_details","TRUE");
$CHECK_FOR_UPDATES  	= get_option("pwaplusphp_updates","TRUE");
$SHOW_DROP_BOX	 	= get_option("pwaplusphp_show_dropbox","FALSE");
$TRUNCATE_ALBUM_NAME  	= get_option("pwaplusphp_truncate_names","TRUE");
$THIS_VERSION	 	= get_option("pwaplusphp_version");
$SITE_LANGUAGE   	= get_option("pwaplusphp_language","en_us");
$PERMIT_IMG_DOWNLOAD  	= get_option("pwaplusphp_permit_download","FALSE");
$SHOW_FOOTER            = get_option("pwaplusphp_show_footer","FALSE");


#-----------------------------------------------------------------------------------------
# Load Language File 
#-----------------------------------------------------------------------------------------
require_once(dirname(__FILE__)."/lang/$SITE_LANGUAGE.php");

#----------------------------------------------------------------------------
# CONFIGURATION
#----------------------------------------------------------------------------
$OPEN=0;

#----------------------------------------------------------------------------
# Check for required variables from config file
#----------------------------------------------------------------------------
if (!isset($GDATA_TOKEN, $PICASAWEB_USER, $IMGMAX, $THUMBSIZE, $USE_LIGHTBOX, $REQUIRE_FILTER, $STANDALONE_MODE, $IMAGES_PER_PAGE)) {

        echo "<h1>" . $LANG_MISSING_VAR_H1 . "</h1><h3>" . $LANG_MISSING_VAR_H3 . "</h3>";
        exit;
}

#----------------------------------------------------------------------------
# VARIABLES
#----------------------------------------------------------------------------
if ($REQUIRE_FILTER != "FALSE") {
	if ((!isset($FILTER)) || ($FILTER == "")) {
		die($LANG_PERM_FILTER);
	}
}

#----------------------------------------------------------------------------
# Request URL for Album list
#----------------------------------------------------------------------------
$file = "http://picasaweb.google.com/data/feed/api/user/" . $PICASAWEB_USER . "?kind=album";
$vals = doCurlExec($file);


#----------------------------------------------------------------------------
# Iterate over the array and extract the info we want
#----------------------------------------------------------------------------
foreach ($vals as $val) {

	if ($OPEN != 1) {

		if ($val["tag"] == "ENTRY") {

			if ($val["type"] == "open") {

				$OPEN = 1;

			}
		}

	} else {

	   switch ($val["tag"]) {

			case "ENTRY":
				if ($val["type"] == "close") {
					$OPEN=0;
				}
				break;
			case "MEDIA:THUMBNAIL":
				$thumb = trim($val["attributes"]["URL"] . "\n");
				break;	
			case "MEDIA:DESCRIPTION":
				$desc = trim($val["value"] . "\n");
				break;
                        case "MEDIA:TITLE":
                                $title = trim($val["value"]);
                                break;
                        case "LINK":
				if ($val["attributes"]["REL"] == "alternate") {
                                	$href = trim($val["attributes"]["HREF"]);
				}
                                break;
                        case "GPHOTO:NUMPHOTOS":
                                $num = trim($val["value"]);
                                break;
			case "GPHOTO:LOCATION":
                                $loc = trim($val["value"]);
                                break;
			case "PUBLISHED":
                                $published = trim($val["value"]);
				$published = substr($published,0,10);	
                                break;
			case "GPHOTO:ACCESS":
				$access = trim($val["value"]);
				if ($access == "protected") { $daccess = "Private"; }
				else { $daccess = "Public"; }
				break;
			case "GPHOTO:NAME":
				$picasa_name = trim($val["value"]);
				break;
	   }
        }

	#----------------------------------------------------------------------------
	# Once we have all the pieces of info we want, dump the output
	#----------------------------------------------------------------------------
	
	if (isset($thumb) && isset($title) && isset($href) && isset($num) && isset($published)) {
		
		if ($FILTER != "") {
				$pos = strlen(strpos($title,$FILTER));
				$box = strlen(strpos($title,"Drop Box"));
				if ($pos > 0) { 
					$pos = 0; 
				#} else if (($box > 0) && ($SHOW_DROP_BOX == "TRUE")) {	# Added to allow user to control whether
				#	$pos = 0;					# drop box appears in gallery list
				} else { 
					$pos = 1; 
				}
		} else {
				$pos = strlen(strpos($title,"_hide"));
		}
		
		if ($pos == 0) {

                        $thumbwidth = 170;
			$twstyle="width: " . $galdatasize . "px;";
                        list($disp_name,$tags) = split('_',$title);

			# --------------------------------------------------------------------
			# Added via issue 7, known problem: long names can break div layout
			# --------------------------------------------------------------------
			if ((strlen($disp_name) > 23) && ($TRUNCATE_ALBUM_NAME == "TRUE")) {
                                $disp_name = substr($disp_name,0,20) . "...";
                        }
                        $album_count++;
			$total_images = $total_images + $num;
                        $out .= "<div class='thumbnail' style='width: " . $thumbwidth . "px;'>\n";
                        $out .= "<div class='thumbimage' style='width: " . $THUMBSIZE . "px;' id='album$album_count'>\n";
			$uri = $_SERVER["REQUEST_URI"];
                   	if ( get_option('permalink_structure') != '' ) {
                        	# permalinks enabled
                        	$urlchar = '?';
                   	} else {
                        	$urlchar = '&';
                   	}
			$out .= "<a class='overlay' href='" . $_SERVER["REQUEST_URI"] . $urlchar . "album=$picasa_name'><img class='pwaimg' alt='image_from_picasa' src='$thumb'></img>";

			# ------------------------------------------------
			# Overlay album details on thumbnail if requested
			# ------------------------------------------------
			if ($SHOW_ALBUM_DETAILS == "TRUE") {
				if ($desc != "") {
					if (strlen($desc) > 120) {
						$desc = substr($desc,0,117) . "...";
					}
                                        $out .= "<span>";
					$out .= "<p class='overlaypg'>$desc</p>";
					if ($loc != "") {
						$out .= "<p class='overlaystats'>$LANG_WHERE: $loc</p>";
					}
					$out .= "<p class='overlaystats' style='padding-top: 5px;'>$LANG_ACCESS: $daccess</p>";
					$out .= "</span>\n";
				}
                        }	
			$out .= "</a>";
                        $out .= "</div>\n";
                        $out .= "<div class='galdata' style='$twstyle'>\n";
                        $out .= "<p class='titlepg'><a class='album_link' href='" . $_SERVER["REQUEST_URI"] . $urlchar . "album=$picasa_name'>$disp_name</a></p>\n";
                        $out .= "<p class='titlestats'>$published, $num $LANG_IMAGES</p>\n";
                        $out .= "</div>";
                        $out .= "</div>\n";

                }
                #----------------------------------
                # Reset the variables
                #----------------------------------
                unset($thumb);
		unset($title);

        }
}
$out = "<div><span style='font-size: 18px; font-weight: bold;'>$FILTER $LANG_GALLERY</span><span style='font-size: 14px; color: #B0B0B0; margin-left: 10px;'>$total_images $LANG_PHOTOS_IN $album_count $LANG_ALBUMS</span></div>\n" . $out;
if ($SHOW_FOOTER == "TRUE") {
	$out .= "<div id='pwafooter'>$LANG_GENERATED <a href='http://code.google.com/p/pwaplusphp/'>PWA+PHP</a> v" . $THIS_VERSION . ".</div>";
}

   #----------------------------------------------------------------------------
   # Output footer if required
   #----------------------------------------------------------------------------
   #if ($STANDALONE_MODE == "TRUE") {
#
	#$out .= "</div>" . "\n";
#   }

return $out;
}

function showAlbumContents($ALBUM) {

$USE_LIGHTBOX="TRUE";
$STANDALONE_MODE="TRUE";

$GDATA_TOKEN            = get_option("pwaplusphp_gdata_token");
$PICASAWEB_USER         = get_option("pwaplusphp_picasa_username");
$IMGMAX                 = get_option("pwaplusphp_image_size","640");
$THUMBSIZE              = get_option("pwaplusphp_thumbnail_size",160);
$REQUIRE_FILTER         = get_option("pwaplusphp_require_filter","FALSE");
$IMAGES_PER_PAGE        = get_option("pwaplusphp_images_per_page",0);
$PUBLIC_ONLY            = get_option("pwaplusphp_public_only","TRUE");
$SHOW_ALBUM_DETAILS     = get_option("pwaplusphp_album_details","TRUE");
$CHECK_FOR_UPDATES      = get_option("pwaplusphp_updates","TRUE");
$SHOW_DROP_BOX          = get_option("pwaplusphp_show_dropbox","FALSE");
$TRUNCATE_ALBUM_NAME    = get_option("pwaplusphp_truncate_names","TRUE");
$THIS_VERSION           = get_option("pwaplusphp_version");
$SITE_LANGUAGE          = get_option("pwaplusphp_language","en_us");
$PERMIT_IMG_DOWNLOAD    = get_option("pwaplusphp_permit_download","FALSE");
$SHOW_FOOTER		= get_option("pwaplusphp_show_footer","FALSE");

#-----------------------------------------------------------------------------------------
# Load Language File
#-----------------------------------------------------------------------------------------
require_once(dirname(__FILE__)."/lang/$SITE_LANGUAGE.php");

#----------------------------------------------------------------------------
# CONFIGURATION
#----------------------------------------------------------------------------
$uri = $_SERVER["REQUEST_URI"];
if ( get_option('permalink_structure') != '' ) { 
	# permalinks enabled
	list($back_link,$uri_tail) = split('\?',$uri);
} else {
	list($back_link,$uri_tail) = split('\&',$uri);
}
$image_count=0;
$picasa_title="NULL";
$OPEN=0;

#----------------------------------------------------------------------------
# Grab album data from URL
#----------------------------------------------------------------------------

# Reformat the album title for display
list($ALBUM_TITLE,$tags) = split('_',$ALBUM);

#----------------------------------------------------------------------------
# Check for required variables from config file
#----------------------------------------------------------------------------
if (!isset($GDATA_TOKEN, $PICASAWEB_USER, $IMGMAX, $THUMBSIZE, $USE_LIGHTBOX, $REQUIRE_FILTER, $STANDALONE_MODE, $IMAGES_PER_PAGE)) {

	echo "<h1>" . $LANG_MISSING_VAR_H1 . "</h1><h3>" . $LANG_MISSING_VAR_H3 . "</h3>";
	exit;
}

$meta_tag = "";

#----------------------------------------------------------------------------
# VARIABLES FOR PAGINATION
#----------------------------------------------------------------------------
if ($IMAGES_PER_PAGE == 0) {

	$file = "http://picasaweb.google.com/data/feed/api/user/" . $PICASAWEB_USER . "/album/" . $ALBUM . "?kind=photo&thumbsize=" . $THUMBSIZE . "c&imgmax=" . $IMGMAX;

} else {

	$page = $_GET['page'];
	if (!(isset($page))) {
		$page = 1;
	}
	if ($page > 1) {
		$start_image_index = (($page - 1) * $IMAGES_PER_PAGE) + 1;
	} else {
		$start_image_index = 1;
	}

	$file = "http://picasaweb.google.com/data/feed/api/user/" . $PICASAWEB_USER . "/album/" . $ALBUM . "?kind=photo&thumbsize=" . $THUMBSIZE . "c&imgmax=" . $IMGMAX . "&max-results=" . $IMAGES_PER_PAGE . "&start-index=" . $start_image_index;

}

$vals = doCurlExec($file);

# Iterate over the array and extract the info we want
#----------------------------------------------------------------------------
unset($thumb);
unset($title);
unset($href);
unset($path);
unset($url);

foreach ($vals as $val) {

        if ($OPEN != 1) {

	   switch ($val["tag"]) {

		case "ENTRY":
                     if ($val["type"] == "open") {
                         $OPEN=1;
                     }
                     break;

		case "TITLE":
                     if ($picasa_title == "NULL") {
                         $picasa_title = $val["value"];
                     }

		 case "GPHOTO:NUMPHOTOS":
                     # Fix for Issue 12
                     if (!is_numeric($numphotos)) {
                         $numphotos = $val["value"];
                     }
                     break;
	   }

        } else {

           switch ($val["tag"]) {

                        case "ENTRY":
                                if ($val["type"] == "close") {
                                        $OPEN=0;
                                }
                                break;
                        case "MEDIA:THUMBNAIL":
                                $thumb = trim($val["attributes"]["URL"] . "\n");
                                break;
                        case "MEDIA:CONTENT":
                                $href = $val["attributes"]["URL"];
				$orig_href = str_replace("s$IMGMAX","d",$href);
				$filename = basename($href);
                                $imght = $val["attributes"]["HEIGHT"];
                                $imgwd = $val["attributes"]["WIDTH"];
                                break;
                        case "SUMMARY":
                                $text = $val["value"];
                                break;
                        case "GPHOTO:ID":
                                if (!isset($STOP_FLAG)) {
                                        $gphotoid = trim($val["value"]);
                                }
                                break;
	   }
        }

        #----------------------------------------------------------------------------
        # Once we have all the pieces of info we want, dump the output
        #----------------------------------------------------------------------------
        if (isset($thumb) && isset($href) && isset($gphotoid)) {

                if ($STOP_FLAG != 1) {
			list($AT,$tags) = split('_',$picasa_title);
                        $ALBUMS_PER_ROW_LESS_ONE = $ALBUMS_PER_ROW - 1;
                        $out .= "<div id='title'><h2>$AT</h2></div><p><a class='back_to_list' href='" . $back_link . "'>...$LANG_BACK</a></p><p>&nbsp;</p>\n";
                        $STOP_FLAG=1;
                }
                $count++;

                $out .= "<div class='thumbnail'>";
                if ($USE_LIGHTBOX == "TRUE") {

			$text = addslashes($text);
                        if($text != "") {

				# 2010-01-31 - Disabled for WP plugin due to compatibility issues
				#if ($PERMIT_IMG_DOWNLOAD == "TRUE") { $dljs = "onmousedown=\"this.title='<a href=\'$orig_href\'>$text</a>';\""; }
                                $out.= "<a href=\"$href\" $dljs rel=\"lightbox[this]\" title='$text' alt='$text'><img class='pwaimg' src='$thumb' alt='image_from_picasa'></img></a>\n";

                        } else {

				$AT = str_replace("\"", "", $AT);
				$AT = str_replace("'", "",$AT);
				# 2010-01-31 - Disabled for WP plugin due to compatibility issues
				#if ($PERMIT_IMG_DOWNLOAD == "TRUE") { $dljs = "onmousedown=\"this.title='<a href=\'$orig_href\'>$AT - $filename</a>';\""; }
                                $out.= "<a href=\"$href\" $dljs rel=\"lightbox[this]\" alt='$AT - $filename' title=\"$AT - $filename\"><img class='pwaimg' src='$thumb' alt='image_from_picasa'></img></a>\n";

                        }

                } else {

                        $newhref="window.open('$href', 'mywindow','scrollbars=0, width=$imgwd,height=$imght');";
                        $out.= "<a href='#' onclick=\"$newhref\"><img src='$thumb' alt='image_from_picasa'></img></a>\n";

                }
                $out.= "</div>";

                #----------------------------------
                # Reset the variables
                #----------------------------------
                unset($thumb);
                unset($picasa_title);
                unset($href);
                unset($path);
                unset($url);
		unset($text);

        }
}

	#----------------------------------------------------------------------------
	# Show output for pagination
	#----------------------------------------------------------------------------
	if ($IMAGES_PER_PAGE != 0) {

		$out .= "<div id='pages'>";
		$paginate = ($numphotos/$IMAGES_PER_PAGE) + 1;
		$out .= "$LANG_PAGE: ";

		# List pages
		for ($i=1; $i<$paginate; $i++) {

		   $link_image_index=($i - 1) * ($IMAGES_PER_PAGE + 1);
		
		   $uri = $_SERVER["REQUEST_URI"];
		   if ( get_option('permalink_structure') != '' ) {
        		# permalinks enabled
			$urlchar = '?';
			$splitchar = '\?';
		   } else {
			$urlchar = '&';
			$splitchar = $urlchar;
		   }
		   list($uri,$tail) = split($splitchar,$_SERVER['REQUEST_URI']);
		   $href = $uri . $urlchar . "album=$ALBUM&page=$i";
		   

		  # Show current page
		  if ($i == $page) {
			$out .= "<span class='current_page'>$i </span>";
		   } else {
			$out .= "<a class='page_link' href='$href'>$i</a> ";
		   }
		}

		$out .= "</div>";

	}

	unset($picasa_title);

	#if ($STANDALONE_MODE == "TRUE") {
	#
	#}

	return($out);

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
	delete_option("pwaplusphp_require_filter");
	delete_option("pwaplusphp_images_per_page");
	delete_option("pwaplusphp_public_only");
	delete_option("pwaplusphp_album_details");
	delete_option("pwaplusphp_updates");
	delete_option("pwaplusphp_show_dropbox");
	delete_option("pwaplusphp_truncate_names");
	delete_option("pwaplusphp_version");
	delete_option("pwaplusphp_language");
	delete_option("pwaplusphp_permit_download");

}

/**
* Add shortcode for embedding the albums in pages
*/
function pwaplusphp_shortcode( $atts, $content = null ) {

        extract(shortcode_atts(array("album" => 'NULL'), $atts));
	extract(shortcode_atts(array("filter" => ''), $atts));

        if (($album == "NULL") && (!isset($_GET["album"]))) {
                $out = dumpAlbumList($filter);
                return($out);
        } else {
		if ($album != "NULL") {
			if ($album != "random_photo") {
				$out = showAlbumContents($album);
			} else {
				$out = get_include_contents(dirname(__FILE__).'/one_random.php');
			}
                } else if (isset($_GET["album"])) {
                        $album = $_GET["album"];
			$out = showAlbumContents($album);
                }
	return($out);
        }

}

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
	 echo '<head><link rel="stylesheet" type="text/css" href="' . WP_PLUGIN_URL . '/pwaplusphp/css/style.css" /></head>';
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

?>
