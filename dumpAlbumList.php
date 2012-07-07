<?PHP
function dumpAlbumList($FILTER,$COVER = "FALSE") {

$USE_LIGHTBOX="TRUE";
$STANDALONE_MODE="TRUE";

$GDATA_TOKEN		= get_option("pwaplusphp_gdata_token");
$PICASAWEB_USER	 	= get_option("pwaplusphp_picasa_username");
#$IMGMAX		 	= get_option("pwaplusphp_image_size","640");
#$GALLERY_THUMBSIZE 	= get_option("pwaplusphp_thumbnail_size",160);
$ALBUM_THUMBSIZE	= get_option("pwaplusphp_album_thumbsize",160);
$REQUIRE_FILTER  	= get_option("pwaplusphp_require_filter","FALSE");
$ALBUMS_PER_PAGE 	= get_option("pwaplusphp_albums_per_page",0);
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
$CACHE_THUMBNAILS       = get_option("pwaplusphp_cache_thumbs","FALSE");
$MAIN_PHOTO_PAGE        = get_option("pwaplusphp_main_photo");

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

# Added to support format adjustments when using wptouch, need to check if wptouch is enabled first
global $wptouch_plugin;
#echo "WP: $wptouch_plugin->applemobile";

if ($wptouch_plugin->applemobile == "1") {

	$SHOW_ALBUM_DETAILS = "FALSE";
	$PERMIT_IMG_DOWNLOAD = "FALSE";
	$CAPTION_LENGTH = "15";

}

#-----------------------------------------------------------------------------------------
# Load Language File 
#-----------------------------------------------------------------------------------------
require(dirname(__FILE__)."/lang/$SITE_LANGUAGE.php");

#----------------------------------------------------------------------------
# CONFIGURATION
#----------------------------------------------------------------------------
$TRUNCATE_FROM = $CAPTION_LENGTH;       # Should be around 25, depending on font and thumbsize
$TRUNCATE_TO   = $CAPTION_LENGTH - 3;   # Should be $TRUNCATE_FROM minus 3
$DESCRIPTION_LENGTH_TO = $DESCRIPTION_LENGTH - 3;
$OPEN=0;
$TW20 = $ALBUM_THUMBSIZE + round($ALBUM_THUMBSIZE * .1);
$TWM10 = $ALBUM_THUMBSIZE - 8;

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

#----------------------------------------------------------------------------
# Pagination for Album list
#----------------------------------------------------------------------------
if ($ALBUMS_PER_PAGE != 0) {

	$page = $_GET['pg'];
	if (!(isset($page))) {
		$page = 1;
	}
	if ($page > 1) {
		$start_image_index = (($page - 1) * $ALBUMS_PER_PAGE) + 1;
	} else {
		$start_image_index = 1;
	}

	$file .= "&max-results=" . $ALBUMS_PER_PAGE . "&start-index=" . $start_image_index;

}

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
		} else if ($val["tag"] == "OPENSEARCH:TOTALRESULTS") {

			$ALBUM_COUNT = $val["value"];
			$random_album = rand(0,$ALBUM_COUNT);

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
		
		if (($FILTER != "") && ($FILTER != "RANDOM")) {
				$pos = strlen(strpos($title,$FILTER));
				$box = strlen(strpos($title,"Drop Box"));
				if ($pos > 0) { 
					$pos = 0; 
				} else if (($box > 0) && ($SHOW_DROP_BOX == "TRUE")) {	# Added to allow user to control whether
					$pos = 0;					# drop box appears in gallery list
				} else { 
					$pos = 1; 
				}
				if ($FILTER == $picasa_name) { $pos = 0; }
		} else {
				$pos = strlen(strpos($title,"_hide"));
		}
		
		if ($pos == 0) {

		   $album_count++;

		   if ((($FILTER == "RANDOM") && ($random_album == $album_count)) || ($FILTER != "RANDOM")) {

                        list($disp_name,$tags) = split('_',$title);

			# --------------------------------------------------------------------
			# Added via issue 7, known problem: long names can break div layout
			# --------------------------------------------------------------------
			if ((strlen($disp_name) > $TRUNCATE_FROM) && ($TRUNCATE_ALBUM_NAME == "TRUE")) {
                                $disp_name = substr($disp_name,0,$TRUNCATE_TO) . "...";
                        }
			$total_images = $total_images + $num;
                        $out .= "<div class='pwaplusphp_albumcover'>\n";
			$uri = $_SERVER["REQUEST_URI"];
			list($back_link,$uri_tail) = split('\?',$uri);
                   	if ( get_option('permalink_structure') != '' ) {
                        	# permalinks enabled
				$permalinks_on = 1;
                        	$urlchar = '?';
                   	} else {
				$permalinks_on = 0;
                        	$urlchar = '&';
                   	}
			if (($FILTER == "RANDOM") || ($COVER == "TRUE")){
				$blog_url = get_bloginfo('url');
				$RANDOM_URI = $blog_url . "/?page_id=" . $MAIN_PHOTO_PAGE;
				$out .= "<a style='width: " . $TWM10 . "px;' class='overlay' href=\"" . $RANDOM_URI . "&album=$picasa_name\"><img class='pwaplusphp_img' alt='$picasa_name' title='$picasa_name' src=\"$thumb\" />";
			} else {
				list($paged_head,$paged_tail) = split('\?',$_SERVER['REQUEST_URI']);
				if ($permalinks_on) {
                                        $out .= "<a style='width: " . $TWM10 . "px;' class='overlay' href=\"" . $paged_head . $urlchar . "album=$picasa_name\"><img class='pwaplusphp_img' alt='$picasa_name' title='$picasa_name' src=\"$thumb\" />";
                                } else {
                                        $out .= "<a style='width: " . $TWM10 . "px;' class='overlay' href='" . $_SERVER["REQUEST_URI"] . $urlchar . "album=" . $picasa_name. "'><img class='pwaplusphp_img' alt='$picasa_name' title='$picasa_name' src=\"$thumb\" />";
                                }
			}

			$trim_epoch = substr($epoch,0,10);
			$published = date($DATE_FORMAT, $trim_epoch);

			# ------------------------------------------------
			# Overlay album details on thumbnail if requested
			# ------------------------------------------------
			if (($SHOW_ALBUM_DETAILS == "TRUE") && ($COVER != "TRUE")) {
				if ($desc != "") {
					if (strlen($desc) > $DESCRIPTION_LENGTH) {
						$desc = substr($desc,0,$DESCRIPTION_LENGTH_TO) . "...";
					}
                                        $out .= "<span>";
					$out .= "<p class='overlaypg'>$desc</p>";
					if ($loc != "") {
						$out .= "<p class='overlaystats'>$LANG_WHERE: $loc</p>";
					}
					$out .= "<p class='overlaystats'>$LANG_ACCESS: $daccess</p>";
					$out .= "</span>\n";
				}
                        }	
			$out .= "</a>";
                        $out .= "<div class='pwaplusphp_galdata'>\n";
			if (($FILTER == "RANDOM") || ($COVER == "TRUE")) {
				if ($COVER != "TRUE") {
					$RANDOM_URI = $back_link . "?page_id=" . $MAIN_PHOTO_PAGE;
                        		$out .= "<a class='album_link' href='" . $RANDOM_URI . $urlchar . "album=$picasa_name'>$disp_name</a>\n";
				}
			} else {
                                list($paged_head,$paged_tail) = split('\?',$_SERVER['REQUEST_URI']);
                                if ($permalinks_on) {
                                        $out .= "<a class='album_link' href='" . $paged_head . $urlchar . "album=$picasa_name'>$disp_name</a>\n";
                                } else {
                                        $out .= "<a class='album_link' href='" . $_SERVER["REQUEST_URI"] . $urlchar . "album=$picasa_name'>$disp_name</a>\n";
                                }
			}
			if (($wptouch_plugin->applemobile != "1") && ($COVER != "TRUE")) {
                        $out .= "<span class='pwaplusphp_albstat'>$published, $num $LANG_IMAGES</span>\n";
			} else {
			$out .= "<span class='albstat-wpt'>&nbsp;</span>\n";
			}
                        $out .= "</div>";
                        $out .= "</div>\n";

		   }

                }
                #----------------------------------
                # Reset the variables
                #----------------------------------
                unset($thumb);
		unset($title);

        }
}

   if ( ($FILTER != "RANDOM") && (strtoupper($COVER) != "TRUE")) {
	$header = "<div id='pwaheader'>";
	if ($wptouch_plugin->applemobile != "1") {
		$header .= "<span class='lang_gallery'>$FILTER $LANG_GALLERY</span>";
		$header .= "<span class='total_images'>$total_images $LANG_PHOTOS_IN $album_count $LANG_ALBUMS</span></div>\n";
	} else { 
                $header .= "<span class='total_images_wpt'>$total_images $LANG_PHOTOS_IN $album_count $LANG_ALBUMS</span></div>\n";
	}

	$out = $header . $out;
		
	if ($SHOW_FOOTER == "TRUE") {
		$out .= "<div id='pwafooter'>$LANG_GENERATED <a href='http://code.google.com/p/pwaplusphp/'>PWA+PHP</a> v" . $THIS_VERSION . ".</div>";
	}
   }

	#----------------------------------------------------------------------------
	# Show output for pagination
	#----------------------------------------------------------------------------
	if (($ALBUMS_PER_PAGE != 0) && ($ALBUM_COUNT > $ALBUMS_PER_PAGE) && ($COVER != "TRUE")){

		$out .= "<div id='pages'>";
		$paginate = ($ALBUM_COUNT/$ALBUMS_PER_PAGE) + 1;
		$out .= "$LANG_PAGE: ";

		# List pages
		for ($i=1; $i<$paginate; $i++) {

		   $link_image_index=($i - 1) * ($ALBUMS_PER_PAGE + 1);
		
		   $uri = $_SERVER["REQUEST_URI"];
		   list($uri,$tail) = split($splitchar,$_SERVER['REQUEST_URI']);
		   $href = $uri . $urlchar . "pg=$i";

		  # Show current page
		  if ($i == $page) {
			$out .= "<strong>$i </strong>";
		   } else {
			$out .= "<a class='page_link' href='$href'>$i</a> ";
		   }
		}

		$out .= "</div>";

	}

   $out .= "<div style='clear: both'></div>"; # Ensure PWA+PHP doesn't break theme layout
   #----------------------------------------------------------------------------
   # Output footer if required
   #----------------------------------------------------------------------------
   #if ($STANDALONE_MODE == "TRUE") {
#
	#$out .= "</div>" . "\n";
#   }

return $out;
}
?>
