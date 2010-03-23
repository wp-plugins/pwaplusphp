<?PHP
/*
Plugin Name: 	PWA+PHP	
Plugin URI: 	http://pwaplusphp.smccandl.net/
Description:	PWA+PHP allows you to display public and private (unlisted) Picasa albums within WordPress in your language using Highslide, Fancybox, Shadowbox or Lightbox.	
Author: 	Scott McCandless
Version:	0.6
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
#$IMGMAX		 	= get_option("pwaplusphp_image_size","640");
#$GALLERY_THUMBSIZE 	= get_option("pwaplusphp_thumbnail_size",160);
$ALBUM_THUMBSIZE	= get_option("pwaplusphp_album_thumbsize",160);
$REQUIRE_FILTER  	= get_option("pwaplusphp_require_filter","FALSE");
#$IMAGES_PER_PAGE 	= get_option("pwaplusphp_images_per_page",0);
$PUBLIC_ONLY 	 	= get_option("pwaplusphp_public_only","TRUE");
$SHOW_ALBUM_DETAILS  	= get_option("pwaplusphp_album_details","TRUE");
#$CHECK_FOR_UPDATES  	= get_option("pwaplusphp_updates","TRUE");
$SHOW_DROP_BOX	 	= get_option("pwaplusphp_show_dropbox","FALSE");
$TRUNCATE_ALBUM_NAME  	= get_option("pwaplusphp_truncate_names","TRUE");
$THIS_VERSION	 	= get_option("pwaplusphp_version");
$SITE_LANGUAGE   	= get_option("pwaplusphp_language","en_us");
$PERMIT_IMG_DOWNLOAD  	= get_option("pwaplusphp_permit_download","FALSE");
$SHOW_FOOTER            = get_option("pwaplusphp_show_footer","FALSE");
#$SHOW_IMG_CAPTION	= get_option("pwaplusphp_show_caption","HOVER");
$CAPTION_LENGTH         = get_option("pwaplusphp_caption_length","23");
$DESCRIPTION_LENGTH     = get_option("pwaplusphp_description_length","120");
$DATE_FORMAT		= get_option("pwaplusphp_date_format","Y-m-d");


#-----------------------------------------------------------------------------------------
# Load Language File 
#-----------------------------------------------------------------------------------------
require_once(dirname(__FILE__)."/lang/$SITE_LANGUAGE.php");

#----------------------------------------------------------------------------
# CONFIGURATION
#----------------------------------------------------------------------------
$TRUNCATE_FROM = $CAPTION_LENGTH;       # Should be around 25, depending on font and thumbsize
$TRUNCATE_TO   = $CAPTION_LENGTH - 3;   # Should be $TRUNCATE_FROM minus 3
$DESCRIPTION_LENGTH_TO = $DESCRIPTION_LENGTH - 3;
$OPEN=0;
$TW20 = $ALBUM_THUMBSIZE + round($ALBUM_THUMBSIZE * .1);
$TWM10 = $ALBUM_THUMBSIZE - 8;

$overlay_class = "class = 'overlay'";

#----------------------------------------------------------------------------
# Check for required variables from config file
#----------------------------------------------------------------------------
if (!isset($GDATA_TOKEN, $PICASAWEB_USER, $ALBUM_THUMBSIZE, $USE_LIGHTBOX, $REQUIRE_FILTER, $STANDALONE_MODE)) {

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
$file = "http://picasaweb.google.com/data/feed/api/user/" . $PICASAWEB_USER . "?kind=album&thumbsize=" . $ALBUM_THUMBSIZE . "c";
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
			#case "PUBLISHED":
                        #        $published = trim($val["value"]);
			#	$published = substr($published,0,10);	
                        #        break;
			case "GPHOTO:TIMESTAMP":
				$epoch = $val["value"];
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
	
	if (isset($thumb) && isset($title) && isset($href) && isset($num) && isset($epoch)) {
		
		if ($FILTER != "") {
				$pos = strlen(strpos($title,$FILTER));
				$box = strlen(strpos($title,"Drop Box"));
				if ($pos > 0) { 
					$pos = 0; 
				} else if (($box > 0) && ($SHOW_DROP_BOX == "TRUE")) {	# Added to allow user to control whether
					$pos = 0;					# drop box appears in gallery list
				} else { 
					$pos = 1; 
				}
		} else {
				$pos = strlen(strpos($title,"_hide"));
		}
		
		if ($pos == 0) {

			$twstyle="width: " . $TW20 . "px;";
                        list($disp_name,$tags) = split('_',$title);

			# --------------------------------------------------------------------
			# Added via issue 7, known problem: long names can break div layout
			# --------------------------------------------------------------------
			if ((strlen($disp_name) > $TRUNCATE_FROM) && ($TRUNCATE_ALBUM_NAME == "TRUE")) {
                                $disp_name = substr($disp_name,0,$TRUNCATE_TO) . "...";
                        }
                        $album_count++;
			$total_images = $total_images + $num;
                        $out .= "<div class='thumbnail' style='width: " . $TW20 . "px; float: left;'>\n";
                        $out .= "<div class='thumbimage' style='width: " . $TWM10 . "px; float: left;'  id='album$album_count'>\n";
			$uri = $_SERVER["REQUEST_URI"];
                   	if ( get_option('permalink_structure') != '' ) {
                        	# permalinks enabled
                        	$urlchar = '?';
                   	} else {
                        	$urlchar = '&';
                   	}
			$out .= "<a $overlay_class href=\"" . $_SERVER["REQUEST_URI"] . $urlchar . "album=$picasa_name\"><img class='pwaimg' alt='$picasa_name' title='$picasa_name' src=\"$thumb\" />";

			$trim_epoch = substr($epoch,0,10);
			$published = date($DATE_FORMAT, $trim_epoch);

			# ------------------------------------------------
			# Overlay album details on thumbnail if requested
			# ------------------------------------------------
			if ($SHOW_ALBUM_DETAILS == "TRUE") {
				if ($desc != "") {
					if (strlen($desc) > $DESCRIPTION_LENGTH) {
						$desc = substr($desc,0,$DESCRIPTION_LENGTH_TO) . "...";
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
			$out .= "<div class='galdata' style='$twstyle; float:left;'>\n";
                        $out .= "<a href='" . $_SERVER["REQUEST_URI"] . $urlchar . "album=$picasa_name'>$disp_name</a>\n";
                        $out .= "<p style='padding-bottom: 15px;'>$published, $num $LANG_IMAGES</p>\n";
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
$out = "<div style='padding-bottom: 20px'><span style='font-size: 18px; font-weight: bold;'>$FILTER $LANG_GALLERY</span><span style='font-size: 14px; color: #B0B0B0; margin-left: 10px;'>$total_images $LANG_PHOTOS_IN $album_count $LANG_ALBUMS</span></div>\n" . $out;
if ($SHOW_FOOTER == "TRUE") {
	$out .= "<div id='pwafooter' style='padding-top: 75px;'>$LANG_GENERATED <a href='http://code.google.com/p/pwaplusphp/'>PWA+PHP</a> v" . $THIS_VERSION . ".</div>";
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

function showAlbumContents($ALBUM,$IN_POST = null) {

$USE_LIGHTBOX="TRUE";
$STANDALONE_MODE="TRUE";

$GDATA_TOKEN            = get_option("pwaplusphp_gdata_token");
$PICASAWEB_USER         = get_option("pwaplusphp_picasa_username");
$IMGMAX                 = get_option("pwaplusphp_image_size","640");
$GALLERY_THUMBSIZE      = get_option("pwaplusphp_thumbnail_size",160);
#$ALBUM_THUMBSIZE	= get_option("pwaplusphp_album_thumbsize",160);
$REQUIRE_FILTER         = get_option("pwaplusphp_require_filter","FALSE");
$IMAGES_PER_PAGE        = get_option("pwaplusphp_images_per_page",0);
#$PUBLIC_ONLY            = get_option("pwaplusphp_public_only","TRUE");
#$SHOW_ALBUM_DETAILS     = get_option("pwaplusphp_album_details","TRUE");
#$CHECK_FOR_UPDATES      = get_option("pwaplusphp_updates","TRUE");
#$SHOW_DROP_BOX          = get_option("pwaplusphp_show_dropbox","FALSE");
$TRUNCATE_ALBUM_NAME    = get_option("pwaplusphp_truncate_names","TRUE");
#$THIS_VERSION           = get_option("pwaplusphp_version");
$SITE_LANGUAGE          = get_option("pwaplusphp_language","en_us");
$PERMIT_IMG_DOWNLOAD    = get_option("pwaplusphp_permit_download","FALSE");
$SHOW_FOOTER		= get_option("pwaplusphp_show_footer","FALSE");
$SHOW_IMG_CAPTION	= get_option("pwaplusphp_show_caption","HOVER");
$CAPTION_LENGTH         = get_option("pwaplusphp_caption_length","23");
#$DESCRIPTION_LENGTH     = get_option("pwaplusphp_description_length","120");
$CROP_THUMBNAILS	= get_option("pwaplusphp_crop_thumbs","TRUE");
$HIDE_VIDEO		= get_option("pwaplusphp_hide_video","FALSE");

#-----------------------------------------------------------------------------------------
# Load Language File
#-----------------------------------------------------------------------------------------
require_once(dirname(__FILE__)."/lang/$SITE_LANGUAGE.php");

#----------------------------------------------------------------------------
# CONFIGURATION
#----------------------------------------------------------------------------
$TZ10 = $GALLERY_THUMBSIZE + round($GALLERY_THUMBSIZE * .06);
$TZ20 = $GALLERY_THUMBSIZE + round($GALLERY_THUMBSIZE * .15);
$TZ30 = $GALLERY_THUMBSIZE + round($GALLERY_THUMBSIZE * .25);
$TZM10 = $GALLERY_THUMBSIZE - round($GALLERY_THUMBSIZE * .06);
$TZM20 = $GALLERY_THUMBSIZE - round($GALLERY_THUMBSIZE * .09);
$TZM2 = $GALLERY_THUMBSIZE - 2;
$TZM2W = $GALLERY_THUMBSIZE - 2;
$TZM2H = $GALLERY_THUMBSIZE - 2;
$TZM2M10 = $TZM2 - 10;
$TZM2M10W = $TZM2W - 10;
$TZM2M10H = $TZM2H - 10;
$twid = $GALLERY_THUMBSIZE;
$thei = $GALLERY_THUMBSIZE;

$uri = $_SERVER["REQUEST_URI"];
$useragent = $_SERVER['HTTP_USER_AGENT']; # Check useragent to suppress hover for IE6
if(strchr($useragent,"MSIE 6.0")) { $USING_IE_6 = "TRUE"; }

if ( get_option('permalink_structure') != '' ) { 
	# permalinks enabled
	list($back_link,$uri_tail) = split('\?',$uri);
} else {
	list($back_link,$uri_tail) = split('\&',$uri);
}
$image_count=0;
$picasa_title="NULL";
$OPEN=0;
$TRUNCATE_FROM = $CAPTION_LENGTH;       # Should be around 22, depending on font and thumbsize
$TRUNCATE_TO   = $CAPTION_LENGTH - 3;   # Should be $TRUNCATE_FROM minus 3
#----------------------------------------------------------------------------
# Grab album data from URL
#----------------------------------------------------------------------------

# Reformat the album title for display
list($ALBUM_TITLE,$tags) = split('_',$ALBUM);

#----------------------------------------------------------------------------
# Check for required variables from config file
#----------------------------------------------------------------------------
if (!isset($GDATA_TOKEN, $PICASAWEB_USER, $IMGMAX, $GALLERY_THUMBSIZE, $USE_LIGHTBOX, $REQUIRE_FILTER, $STANDALONE_MODE, $IMAGES_PER_PAGE)) {

	echo "<h1>" . $LANG_MISSING_VAR_H1 . "</h1><h3>" . $LANG_MISSING_VAR_H3 . "</h3>";
	exit;
}

$meta_tag = "";

#----------------------------------------------------------------------------
# VARIABLES FOR PAGINATION
#----------------------------------------------------------------------------
if ($IN_POST == "TRUE") {
        $IMAGES_PER_PAGE = 0;
}

if ($CROP_THUMBNAILS == "TRUE") { $CROP_CHAR = "c"; }
else { $CROP_CHAR = "u"; }

if ($IMAGES_PER_PAGE == 0) {

	$file = "http://picasaweb.google.com/data/feed/api/user/" . $PICASAWEB_USER . "/album/" . $ALBUM . "?kind=photo&thumbsize=" . $GALLERY_THUMBSIZE . $CROP_CHAR . "&imgmax=" . $IMGMAX;

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

	$file = "http://picasaweb.google.com/data/feed/api/user/" . $PICASAWEB_USER . "/album/" . $ALBUM . "?kind=photo&thumbsize=" . $GALLERY_THUMBSIZE . $CROP_CHAR . "&imgmax=" . $IMGMAX . "&max-results=" . $IMAGES_PER_PAGE . "&start-index=" . $start_image_index;

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
				$twid  = $val["attributes"]["WIDTH"];
				$thei  = $val["attributes"]["HEIGHT"];
				$TZM2W = $twid - 2;
				$TZM2H = $thei - 2;
				$TZM2M10W = $TZM2W - 10;
				$TZM2M10H = $TZM2H - 10;
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

		# Grab the album title once
                if ($STOP_FLAG != 1) {
			list($AT,$tags) = split('_',$picasa_title);
			$AT = str_replace("\"", "", $AT);
                        $AT = str_replace("'", "",$AT);
			if ($IN_POST != "TRUE") {
                                $out .= "<div id='title'><h2>$AT</h2></div><p style='padding-bottom: 10px;'><a class='back_to_list' href='" . $back_link . "'>...$LANG_BACK</a></p>\n";
                        }
                        $STOP_FLAG=1;
                }
		
		# Set image caption
                if ($text != "") {
                        #$text = addslashes($text);
                        $caption = $text;
                } else {
                        $caption = $AT . " - " . $filename;
                }
                # Keep count of images
                $count++;

                # Shorten caption as necessary
                if ((strlen($caption) > $TRUNCATE_FROM) && ($TRUNCATE_ALBUM_NAME == "TRUE")) {
                        $short_caption = substr($caption,0,$TRUNCATE_TO) . "...";
                        if (strlen($short_caption) > $TRUNCATE_FROM) {
                                $short_caption = substr($filename,0,$TRUNCATE_FROM);
                        }
                } else {
                        $short_caption = $caption;
                }

                # Hide Videos
                $vidpos = stripos($href, "googlevideo");

                if (($vidpos == "") || ($HIDE_VIDEO == "FALSE")) {

                   # CASE: CAPTION = OVERLAY
                   if ($SHOW_IMG_CAPTION == "OVERLAY") {

			$out .= "<p class='blocPhoto' style='width: " . $GALLERY_THUMBSIZE . "px; height: " . $GALLERY_THUMBSIZE . "px; padding-right: 10px;'>";

                        if ($PERMIT_IMG_DOWNLOAD == "TRUE") {
                                $out .= "<a class='dl_link' href='$filename' title='Download: $filename'><img src='" . WP_PLUGIN_URL . "/pwaplusphp/images/disk_bw.png' alt='' /></a>";
                        }

                        $out .= "<a style=\"width: " . $TZM2W . "px; height: " . $TZM2H . "px; background-image: url('$thumb');\" class='photo' title='$caption' href='$href'>";
                        $out .= "<span class='border' style='width: " . $TZM2M10W . "px; height: " . $TZM2M10H . "px;'>";
                        $out .= "<span class='title' style='width: " . $TZM2M10W . "px; color: #FFF;'><span>$short_caption</span></span>";
                        $out .= "</span> ";
                        $out .= "</a>";
                        $out .= "</p>";

		   # CASE: CAPTION = HOVER & IE6 = TRUE
                   } else if (($SHOW_IMG_CAPTION == "HOVER") && ($USING_IE_6 != "TRUE")){

			# ONLY WANT HEIGHT IF NON-CROPPED THUMBNAILS
			$out .= "<div class='thumbnail' style='width: " . $TZ10 . "px; ";

			if ($CROP_THUMBNAILS == "FALSE") {
                                $out .= "height: " . $TZ30 . "px; ";
                        }

			$out .= "text-align: center;'>\n";
                        $out .= " <a href='$href' alt='$caption'><img class='pwaimg' src='$thumb' alt='$caption' /></a>\n";
                        $out .= " <div id='options' style='width:" . $TZ10 . "px;'>\n";
                        $out .= "  <span style='padding-top: 3px;'>$short_caption</span>\n";

                        # Download Icon
                        $download_div  = "<span style='margin-left: " . $TZM20 . "px; padding-top: 3px;'>\n";
                        $download_div .= "<a rel='shadowbox[post-0000];player=img;' Save $filename' title='Save $filename' href='$orig_href'>\n";
                        $download_div .= "<img border=0 style='padding-left: 5px;' src='" . WP_PLUGIN_URL . "/pwaplusphp/images/disk_bw.png' />\n";
                        $download_div .= "</a></span>\n";

                        # Show Download Icon
                        if ($PERMIT_IMG_DOWNLOAD == "TRUE") {
                                $out .= $download_div;
                        }
                        $out .= "</div>\n";
                        $out .= "</div>";

		   # CASE: CAPTION = ALWAYS && DOWNLOAD = TRUE
		   #       CAPTION = HOVER & IE6 = TRUE 
                   } else if ((($SHOW_IMG_CAPTION == "ALWAYS") && ($PERMIT_IMG_DOWNLOAD == "TRUE")) || (($SHOW_IMG_CAPTION == "HOVER") && ($USING_IE_6 == "TRUE"))){

			# ONLY WANT HEIGHT IF NON-CROPPED THUMBNAILS
                        $out .= " <div class='thumbnail' style='width:" . $TZ10 . "px; ";

			if ($CROP_THUMBNAILS == "FALSE") {
				$out .= "height: " . $TZ30 . "px; ";
			}

			$out .= "text-align: center; padding-bottom: 10px;'>\n";
			$out .= " <a href='$href alt='$caption'><img class='pwaimg' src='$thumb' alt='$caption' /></a>\n";
                        $out .= "  <span style='float: left; padding-top: 3px; font-size: 10px;'>$short_caption</span>\n";

                        # Download Icon
                        $download_div  = "<span style='float: right; padding-top: 3px;'>\n";
                        $download_div .= "<a 'Save $filename' title='Save $filename' href='$orig_href'>\n";
                        $download_div .= "<img border=0 style='padding-left: 5px;' src='" . WP_PLUGIN_URL . "/pwaplusphp/images/disk_bw.png' />\n";
                        $download_div .= "</a></span>\n";

			# Show Download Icon
                	if ($PERMIT_IMG_DOWNLOAD == "TRUE") {
                        	$out .= $download_div;
                	}
			$out .= "</div>\n";

		   # CASE: CAPTION = NEVER, OR
		   #       CAPTION = ALWAYS & DOWNLOAD = FALSE
		   } else {

			$out .= "<p class='pwathumb' style='float: left; width: " . $TZ20 . "px; ";

			if ($CROP_THUMBNAILS == "FALSE") {
                                $out .= "height: " . $TZ30 . "px; ";
			}

			$out .= "padding-right: 10px;'><span style='font-size: 11px;'>";
                        
			$out .= "<a href='$href' alt='$caption'><img style='padding: 5px; margin-bottom: 5px; background: #F1F1F1; border: 1px solid #CCC;' src='$thumb' /></a>";
			if ($SHOW_IMG_CAPTION == "ALWAYS") {
				$out .= "<br />$short_caption";
			}
		 	$out .= "</span></p>";
                   }
		}

                #$out .= "</div>\n";

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
			$out .= "<strong>$i </strong>";
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
	delete_option("pwaplusphp_album_thumbsize");
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

        if (($album == "NULL") && (!isset($_GET["album"]))) {
                $out = dumpAlbumList($filter);
                return($out);
        } else {
		if ($album != "NULL") {
			if ($album != "random_photo") {
				$out = showAlbumContents($album,"TRUE");
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
