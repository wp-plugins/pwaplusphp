<?PHP

#==============================================================================================
# Copyright 2009 Scott McCandless (smccandl@gmail.com)
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
# http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
#==============================================================================================

#----------------------------------------------------------------------------
# CONFIGURATION
#----------------------------------------------------------------------------
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
$SHOW_FOOTER            = get_option("pwaplusphp_show_footer","FALSE");

#-----------------------------------------------------------------------------------------
# Load Language File
#-----------------------------------------------------------------------------------------

$album_array = array();
$thumb_array = array();
$href_array  = array();
$text_array  = array();
$title_array = array();

#----------------------------------------------------------------------------
# Check for required variables from config file
#----------------------------------------------------------------------------
if ( (!isset($GDATA_TOKEN)) || (!isset($PICASAWEB_USER)) || (!isset($IMGMAX)) || (!isset($THUMBSIZE)) || (!isset($USE_LIGHTBOX)) || (!isset($REQUIRE_FILTER)) || (!isset($STANDALONE_MODE)) || (!isset($IMAGES_PER_PAGE)) ) {

        echo "<h1>" . $LANG_MISSING_VAR_H1 . "</h1><h3>" . $LANG_MISSING_VAR_H3 . "</h3>";
        exit;
}

$file = "http://picasaweb.google.com/data/feed/api/user/" . $PICASAWEB_USER . "?kind=album";

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

#----------------------------------------------------------------------------
# Iterate over the array and extract the info we want
#----------------------------------------------------------------------------
unset($thumb);
unset($title);
unset($href);
unset($num);
unset($description);
foreach ($vals as $val) {

	switch ($val["tag"]) {

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

	#----------------------------------------------------------------------------
	# Once we have all the pieces of info we want, dump the output
	#----------------------------------------------------------------------------
	
	if (isset($thumb) && isset($title) && isset($href) && isset($num) && isset($published)) {
		
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
			array_push($album_array,$picasa_name);

                }
                #----------------------------------
                # Reset the variables
                #----------------------------------
                unset($thumb);
                unset($title);
                unset($href);
                unset($num);
                unset($description);

        }
}
unset($title);
$random_int = rand(0,$album_count);
$ALBUM = $album_array[$random_int];

$file = "http://picasaweb.google.com/data/feed/api/user/" . $PICASAWEB_USER . "/album/" . $ALBUM . "?kind=photo&thumbsize=" . $THUMBSIZE . "c&imgmax=" . $IMGMAX;

#----------------------------------------------------------------------------
# Curl code to store XML data from PWA in a variable
#----------------------------------------------------------------------------
$ch = curl_init();
$timeout = 0; // set to zero for no timeout
curl_setopt($ch, CURLOPT_URL, $file);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: AuthSub token="' . $GDATA_TOKEN . '"'
  ));
$addressData = curl_exec($ch);
curl_close($ch);

#----------------------------------------------------------------------------
# Parse the XML data into an array
#----------------------------------------------------------------------------
$p = xml_parser_create();
xml_parse_into_struct($p, $addressData, $vals, $index);
xml_parser_free($p);

$image_count=0;
$picasa_title="NULL";
foreach ($vals as $val) {

        switch ($val["tag"]) {

                        case "MEDIA:THUMBNAIL":
                                $thumb = trim($val["attributes"]["URL"] . "\n");
                                break;
                        case "MEDIA:TITLE":
                                $title = trim($val["value"]);
                                break;
                        case "MEDIA:DESCRIPTION":
                                if ($val["attributes"]["REL"] == "alternate") {
                                        $href = trim($val["attributes"]["HREF"]);
                                }
                                break;
                        case "MEDIA:CONTENT":
                                $href = $val["attributes"]["URL"];
                                $imght = $val["attributes"]["HEIGHT"];
                                $imgwd = $val["attributes"]["WIDTH"];
                                break;
                        case "SUMMARY":
                                $text = $val["value"];
                                break;
                        case "TITLE":
                                if ($picasa_title == "NULL") {
                                        $picasa_title = $val["value"];
                                }
                        case "GPHOTO:NUMPHOTOS":
                                $numphotos = $val["value"];
                                break;
                        case "GPHOTO:ID":
                                if (!isset($STOP_FLAG)) {
                                        $gphotoid = trim($val["value"]);
                                }
                                break;
        }

        #----------------------------------------------------------------------------
        # Once we have all the pieces of info we want, dump the output
        #----------------------------------------------------------------------------
        if (isset($thumb) && isset($title) && isset($href) && isset($gphotoid)) {

                $count++;
		array_push($href_array,$href);
		array_push($thumb_array,$thumb);
		array_push($text_array,$text);
		array_push($title_array,$picasa_title);

                #----------------------------------
                # Reset the variables
                #----------------------------------
                unset($thumb);
                unset($title);
                unset($href);
                unset($path);
                unset($url);
                unset($text);

        }
}

$random_image = rand(0,$count);
$href=$href_array[$random_image];
$thumb=$thumb_array[$random_image];
$picasa_title=$title_array[$random_image];
$text = $text_array[$random_image];

echo "<div class='thumbnail'>";
                if ($USE_LIGHTBOX == "TRUE") {

                        $text = addslashes($text);
			list($AT,$PT) = split('_',$picasa_title);

                        if($text != "") {
                                echo "<a href=\"$href\" class=\"lightbox\" rel=\"lightbox[this]\" title=\"$text\"><img class='pwaimg' src='$thumb' alt='image_from_picasa'></img></a>\n";
                        } else {
                                echo "<a href=\"$href\" class=\"lightbox\" rel=\"lightbox[this]\" title=\"$AT\"><img class='pwaimg' src='$thumb' alt='image_from_picasa'></img></a>\n";
                        }

                } #else {

                        #$newhref="window.open('$href', 'mywindow','scrollbars=0, width=$imgwd,height=$imght');";
                        #echo "<a href='#' onclick=\"$newhref\"><img src='$thumb' alt='image_from_picasa'></img></a>\n";

                #}
                echo "</div>";

#----------------------------------------------------------------------------
# Output footer if required
#----------------------------------------------------------------------------
#if ($STANDALONE_MODE == "TRUE") {
#
#	echo "</div>" . "\n";
#        echo "</body>" . "\n";
#        echo "</html>" . "\n";
#}
?>
