<?PHP
# For non-PHP5 users
if (!function_exists("stripos")) {
   function stripos($str,$needle,$offset=0) {
     return strpos(strtolower($str),strtolower($needle),$offset);
    }
}

function showAlbumContents($ALBUM,$IN_POST = null,$TAG,$overrides_array) {

$USE_LIGHTBOX="TRUE";
$STANDALONE_MODE="TRUE";

$GDATA_TOKEN            = get_option("pwaplusphp_gdata_token");
$PICASAWEB_USER         = get_option("pwaplusphp_picasa_username");
$IMGMAX                 = get_option("pwaplusphp_image_size","640");
$GALLERY_THUMBSIZE      = get_option("pwaplusphp_thumbnail_size",160);
$REQUIRE_FILTER         = get_option("pwaplusphp_require_filter","FALSE");
$IMAGES_PER_PAGE        = get_option("pwaplusphp_images_per_page",0);
$TRUNCATE_ALBUM_NAME    = get_option("pwaplusphp_truncate_names","TRUE");
$SITE_LANGUAGE          = get_option("pwaplusphp_language","en_us");
$PERMIT_IMG_DOWNLOAD    = get_option("pwaplusphp_permit_download","FALSE");
$SHOW_FOOTER		= get_option("pwaplusphp_show_footer","FALSE");
$SHOW_IMG_CAPTION	= get_option("pwaplusphp_show_caption","HOVER");
$CAPTION_LENGTH         = get_option("pwaplusphp_caption_length","23");
$CROP_THUMBNAILS	= get_option("pwaplusphp_crop_thumbs","TRUE");
$HIDE_VIDEO		= get_option("pwaplusphp_hide_video","FALSE");

if ($overrides_array["images_per_page"] != "") { $IMAGES_PER_PAGE = $overrides_array["images_per_page"];}
if ($overrides_array["image_size"]) { $IMGMAX = $overrides_array["image_size"];}
if ($overrides_array["thumbnail_size"]) { $GALLERY_THUMBSIZE = $overrides_array["thumbnail_size"];}
if ($overrides_array["picasaweb_user"]) { $PICASAWEB_USER = $overrides_array["picasaweb_user"];}

# Added to support format adjustments when using wptouch, need to check if wptouch is enabled first
global $wptouch_plugin;

if ($wptouch_plugin->applemobile == "1") {

        $SHOW_ALBUM_DETAILS = "FALSE";
        $PERMIT_IMG_DOWNLOAD = "FALSE";
	$SHOW_IMG_CAPTION = "NEVER";
	
}

#-----------------------------------------------------------------------------------------
# Load Language File
#-----------------------------------------------------------------------------------------
require(dirname(__FILE__)."/lang/$SITE_LANGUAGE.php");

#----------------------------------------------------------------------------
# VARIABLES 
#----------------------------------------------------------------------------
global $TZM30, $TZM10;
$TZ10 = $GALLERY_THUMBSIZE + round($GALLERY_THUMBSIZE * .06);
$TZ20 = $GALLERY_THUMBSIZE + round($GALLERY_THUMBSIZE * .15);
$TZ30 = $GALLERY_THUMBSIZE + round($GALLERY_THUMBSIZE * .25);
$TZM10 = $GALLERY_THUMBSIZE - round($GALLERY_THUMBSIZE * .06);
$TZM20 = $GALLERY_THUMBSIZE - round($GALLERY_THUMBSIZE * .09);
$TZM30 = $GALLERY_THUMBSIZE - round($GALLERY_THUMBSIZE * .22);
$TZM2 = $GALLERY_THUMBSIZE - 2;
$TZP10 = $GALLERY_THUMBSIZE + 10;
$image_count=0;
$picasa_title="NULL";
$count=0;
$OPEN=0;
$TRUNCATE_FROM = $CAPTION_LENGTH;       # Should be around 22, depending on font and thumbsize
$TRUNCATE_TO   = $CAPTION_LENGTH - 3;   # Should be $TRUNCATE_FROM minus 3
$uri = $_SERVER["REQUEST_URI"];
$useragent = $_SERVER['HTTP_USER_AGENT']; # Check useragent to suppress hover for IE6
if(strchr($useragent,"MSIE 6.0")) { $USING_IE_6 = "TRUE"; }
$gphotoid="1234678";

#----------------------------------------------------------------------------
# Check Permalink Structure 
#----------------------------------------------------------------------------
if ( get_option('permalink_structure') != '' ) { 
	# permalinks enabled
	list($back_link,$uri_tail) = split('\?',$uri);
	$urlchar = '?';
        $splitchar = '\?';
} else {
	list($back_link,$uri_tail) = split('\&',$uri);
	$urlchar = '&';
        $splitchar = $urlchar;
}

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
} else if ($IN_POST == "SLIDESHOW") {
	$IMGMAX = "d";	
	$SHOW_IMG_CAPTION = "SLIDESHOW";
}

if ($CROP_THUMBNAILS == "TRUE") { $CROP_CHAR = "c"; }
else { $CROP_CHAR = "u"; }


$file = "http://picasaweb.google.com/data/feed/api/user/" . $PICASAWEB_USER;

if ($ALBUM != "NULL") { $file .= "/album/" . $ALBUM; }

$file.= "?kind=photo&thumbsize=" . $GALLERY_THUMBSIZE . $CROP_CHAR . "&imgmax=" . $IMGMAX;	

if ($TAG != "NULL") { $file .= "&tag=$TAG"; }


if ($IMAGES_PER_PAGE != 0) {

	$page = $_GET['pg'];
	if (!(isset($page))) {
		$page = 1;
	}
	if ($page > 1) {
		$start_image_index = (($page - 1) * $IMAGES_PER_PAGE) + 1;
	} else {
		$start_image_index = 1;
	}

	$file .= "&max-results=" . $IMAGES_PER_PAGE . "&start-index=" . $start_image_index;

}
$vals = doCurlExec($file);

# Iterate over the array and extract the info we want
#----------------------------------------------------------------------------
unset($thumb);
unset($title);
unset($href);
unset($path);
unset($url);
unset($gphotoid);

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

		 case "GPHOTO:ID":
                     $albumid = $val["value"];
                     break;

		 case "OPENSEARCH:TOTALRESULTS":
                     $result_count = $val["value"];
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
    				$tnht = $val["attributes"]["HEIGHT"];
    				$tnwd = $val["attributes"]["WIDTH"];
    				// Temporary? fix for google api bug 2011-05-28
    				if (($tnht == $GALLERY_THUMBSIZE) || ($tnwd == $GALLERY_THUMBSIZE)) {
        				$thumb = trim($val["attributes"]["URL"] . "\n");
    				}
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
                                #if (!isset($gphotoid)) {
                                        $gphotoid = trim($val["value"]);
                                #}
                                break;
	   }
        }


        #----------------------------------------------------------------------------
        # Once we have all the pieces of info we want, dump the output
        #----------------------------------------------------------------------------
        if (isset($thumb) && isset($href) &&  isset($gphotoid)) {

		$add_s = "";
		# Grab the album title once
                if ($STOP_FLAG != 1) {
			list($AT,$tags) = split('_',$picasa_title);
			$AT = str_replace("\"", "", $AT);
                        $AT = str_replace("'", "",$AT);
			if (($IN_POST != "TRUE") && ($IN_POST != "SLIDESHOW")) {
				if (($TAG == "") || ($TAG == "NULL")) {
                                	$out .= "<div id='title'><h2>$AT</h2>";
				} else {
					$out .= "<div id='title'><h2>Photos tagged '$TAG'</h2>";
				}
				$out .= "<span><a class='back_to_list' href='" . $back_link . "'>...$LANG_BACK</a></span></div>\n";
                        }
                        $STOP_FLAG=1;
                }
		
		# Set image caption
                if ($text != "") {
                        $caption = htmlentities( $text , ENT_QUOTES );
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

		   if ($SHOW_IMG_CAPTION == "SLIDESHOW") {

                        echo "<img src='" . $href . "' width='" . $imgwd . "' height='" . $imght . "' />\n";

		   # CASE: CAPTION = HOVER & IE6 = FALSE
                   } else if (($SHOW_IMG_CAPTION == "HOVER") && ($USING_IE_6 != "TRUE")){

			# ONLY WANT HEIGHT IF NON-CROPPED THUMBNAILS
			$out .= "<div class='pwaplusphp_thumbnail' style='width: " . $TZ10 . "px; ";

			if ($CROP_THUMBNAILS == "FALSE") {
                                $out .= "height: " . $TZ30 . "px; ";
                        }

			$out .= "text-align: center;'>\n";
                        $caption_link_tweak = setupCaption($caption,$ACTIVE_LIGHTBOX,$count);
                        $out .= " <a $caption_link_tweak href='$href'><img class='pwaplusphp_img' src='$thumb' alt='$caption' /></a>\n";
                        $out .= " <div id='options' style='width:" . $TZ10 . "px;'>\n";
                        $out .= "  <span class='short_caption'>$short_caption</a></span>\n";

                        # Show Download Icon
                        if ($PERMIT_IMG_DOWNLOAD == "TRUE") {
				$out .= buildDownloadDiv($filename,$orig_href,"margin-left: " . $TZM20 . "px; padding-top: 3px;");
                       	} 
                        $out .= "</div>\n";
                        $out .= "</div>";

		   # CASE: CAPTION = ALWAYS && DOWNLOAD = TRUE
		   #       CAPTION = HOVER & IE6 = TRUE 
                   } else if (($SHOW_IMG_CAPTION == "ALWAYS") || (($SHOW_IMG_CAPTION == "HOVER") && ($USING_IE_6 == "TRUE"))) {

			# ONLY WANT HEIGHT IF NON-CROPPED THUMBNAILS
                        $out .= " <div class='pwaplusphp_thumbnail' style='width:" . $TZ10 . "px; ";

			if ($CROP_THUMBNAILS == "FALSE") {
				$out .= "height: " . $TZ30 . "px; ";
			}

			$out .= "text-align: center; padding-bottom: 10px;'>\n";
                        $caption_link_tweak = setupCaption($caption,$ACTIVE_LIGHTBOX,$count);
                        $out .= " <a $caption_link_tweak href='$href'><img class='pwaplusphp_img' src='$thumb' alt='$caption' /></a>\n";
                        $out .= "  <span class='short_caption2'>$short_caption</span>\n";

			# Show Download Icon
                	if ($PERMIT_IMG_DOWNLOAD == "TRUE") {
                        	$out .= buildDownloadDiv($filename,$orig_href,"float: right; padding-top: 3px;");
                	}
			
			$out .= "</div>\n";

		   # CASE CUSTOM STYLE
                   } else if ($SHOW_IMG_CAPTION == "CUSTOM") {
                        $out .= "<div class='pwaplusphp_thumbnail'>\n";
                                $out .= "\t<a class='pwaplusphp_imglink' href='$href'><img class='pwaplusphp_img' src='$thumb' alt='$caption'></a>\n";
                                $out .= "\t<div class='pwaplusphp_caption'><p class='pwaplusphp_captext'>$short_caption</p>\n";

                        # Show Download Icon
                        if ($PERMIT_IMG_DOWNLOAD == "TRUE") {
                                $out .= buildDownloadDiv($filename,$orig_href);
                        }

                        $out .= "</div></div>";

		   } else {

                        $out .= "<p class='blocPhoto' style='width: " . $TZ10 . "px; height: " . $TZ20 . "px;'>";

                        if ($PERMIT_IMG_DOWNLOAD == "TRUE") {
                                $out .= "<a class='dl_link' rel='nobox' href='$orig_href' title='Download $filename'><img border='0' src='" . WP_PLUGIN_URL . "/pwaplusphp/images/disk_bw.png' alt='' /></a>";
                        }

			$caption_link_tweak = setupCaption($caption,$ACTIVE_LIGHTBOX,$count);
                        $out .= "<a style=\"width: " . $TZP10 . "px; height: " . $TZP10 . "px;\" class='photo' $caption_link_tweak href='$href'>";
                        $out .= "<span class='border' style='width: " . $GALLERY_THUMBSIZE . "px; height: " . $GALLERY_THUMBSIZE . "px;'><img src='$thumb' />";
			if ($SHOW_IMG_CAPTION != "NEVER") {
                        	$out .= "<span class='title' style='width: " . $GALLERY_THUMBSIZE . "px;'><span>$short_caption</span></span>";
			}
                        $out .= "</span></a>";
                        $out .= "</p>";

                   }
		}

                #----------------------------------
                # Reset the variables
                #----------------------------------
                unset($thumb);
                unset($picasa_title);
                unset($href);
                unset($path);
                unset($url);
		unset($text);
		unset($gphotoid);
        }
}

	#----------------------------------------------------------------------------
	# Show output for pagination
	#----------------------------------------------------------------------------
	if (($IMAGES_PER_PAGE != 0) && ($result_count > $IMAGES_PER_PAGE)){

		$out .= "<div id='pages'>";
		$paginate = ($result_count/$IMAGES_PER_PAGE) + 1;
		$out .= "$LANG_PAGE: ";

		# List pages
		for ($i=1; $i<$paginate; $i++) {

		   $link_image_index=($i - 1) * ($IMAGES_PER_PAGE + 1);
		
		   $uri = $_SERVER["REQUEST_URI"];
		   list($uri,$tail) = split($splitchar,$_SERVER['REQUEST_URI']);
		   $href = $uri . $urlchar . "album=$ALBUM&pg=$i";
		   

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

	$out .= "<div style='clear: both'></div>"; # Ensure PWA+PHP doesn't break theme layout
	return($out);

}

?>
