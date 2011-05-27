<?PHP
global $PRO_VERSION;
global $THIS_VERSION;
$PRO_VERSION = "FALSE";		# Changing this only affects the installer and won't enable PRO features.
echo "<div class='wrap'>";
echo "<div id='icon-plugins' class='icon32'></div><h2>PWA+PHP Plugin Settings</h2><br />";
if ($_GET['loc'] == "finish") {
	echo "<div style='width: 71%; margin: 0px 0px 0px 20px; padding: 5px; background-color: #ffffcc; border: #e6e6e6 1px solid;'>Configuration is complete and PWA+PHP is ready for use. Create a page with contents \"[pwaplusphp]\" to see your albums.</div>";
}
echo "<table cellspacing=20><tr><td width='75%' valign=top>";

#==============================================================================================
# Copyright 2010 Scott McCandless (smccandl@gmail.com)
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

function get_gdata_token() {

	$site = $_SERVER['SERVER_NAME'];
	$port = ($_SERVER['SERVER_PORT'] != 80) ? ':' . $_SERVER['SERVER_PORT'] : '';
	$self  = $_SERVER['PHP_SELF'];
	$loc  = urlencode("http://" . $site . $port . $self . "?page=pwaplusphp&loc=return");
	$next = "https://www.google.com/accounts/AuthSubRequest?scope=http%3A%2F%2Fpicasaweb.google.com%2Fdata%2F&session=1&secure=0&next=$loc";
	echo "<h2>Install Step 1: Token Generation</h2>";
	echo "<p>Generating this Google \"GData\" token is a one-time step that allows PWA+PHP to access to your private (unlisted) Picasa albums. Click the link below to continue if you wish to set up PicasaWeb tokens for site: <strong>$site</strong></p>";
	echo "<p>If this is correct, <a href='$next'>";
	echo "Login to your Google Account</a></p>"; 
	echo "</body>\n</html>";

}

function get_options() {
global $PRO_VERSION;
global $THIS_VERSION;

$GDATA_TOKEN		= get_option("pwaplusphp_gdata_token");
$PICASAWEB_USER         = get_option("pwaplusphp_picasa_username");
$IMGMAX                 = get_option("pwaplusphp_image_size","640");
$GALLERY_THUMBSIZE      = get_option("pwaplusphp_thumbnail_size",160);
$ALBUM_THUMBSIZE      	= get_option("pwaplusphp_album_thumbsize",160);
$REQUIRE_FILTER         = get_option("pwaplusphp_require_filter","FALSE");
$IMAGES_PER_PAGE        = get_option("pwaplusphp_images_per_page",0);
$ALBUMS_PER_PAGE		= get_option("pwaplusphp_albums_per_page",0);
$PUBLIC_ONLY            = get_option("pwaplusphp_public_only","FALSE");
$SHOW_ALBUM_DETAILS     = get_option("pwaplusphp_album_details","TRUE");
$CHECK_FOR_UPDATES      = get_option("pwaplusphp_updates","TRUE");
$SHOW_DROP_BOX          = get_option("pwaplusphp_show_dropbox","FALSE");
$TRUNCATE_ALBUM_NAME    = get_option("pwaplusphp_truncate_names","TRUE");
$THIS_VERSION           = get_option("pwaplusphp_version");
$SITE_LANGUAGE          = get_option("pwaplusphp_language","en_us");
$PERMIT_IMG_DOWNLOAD    = get_option("pwaplusphp_permit_download","FALSE");
$SHOW_FOOTER    	= get_option("pwaplusphp_show_footer","FALSE");
$SHOW_IMG_CAPTION	= get_option("pwaplusphp_show_caption","HOVER");
$CAPTION_LENGTH         = get_option("pwaplusphp_caption_length","23");
$DESCRIPTION_LENGTH     = get_option("pwaplusphp_description_length","120");
$CROP_THUMBNAILS	= get_option("pwaplusphp_crop_thumbs","TRUE");
$DATE_FORMAT		= get_option("pwaplusphp_date_format","Y-m-d");
$HIDE_VIDEO             = get_option("pwaplusphp_hide_video","FALSE");
$CACHE_THUMBNAILS	= get_option("pwaplusphp_cache_thumbs","FALSE");
$MAIN_PHOTO_PAGE	= get_option("pwaplusphp_main_photo");
$SHOW_COMMENTS		= get_option("pwaplusphp_show_comments");

if ($PRO_VERSION == "FALSE") {
                $pro_disabled = "disabled";
		$disabled_color = "color: #CCC;";
} else {
		$ACTIVE_LIGHTBOX = getActiveLightbox();
}

	echo "<form name=form1 action='$self?page=pwaplusphp&loc=finish' method='post'>\n";
	echo "<table class='widefat' cellspacing=5 width=700>\n";
	echo "<thead><tr><th valign=top colspan=3>Picasa Access Settings</th></tr></thead>\n";
	echo "<tr><td valign=top style='padding-top: 7px; width: 200px;'><strong>Picasaweb User</strong></td><td valign=top style='padding-top: 7px;'><input style='width: 150px;' type='text' name='pwaplusphp_picasa_username' value='$PICASAWEB_USER'></td><td valign=top style='padding-top: 8px;'><i>Enter your Picasa username.</i></td></tr>";

	echo "<tr><td valign=top style='padding-top: 5px; width: 200px;'><strong>GData Token</strong></td><td valign=top style='padding-top: 5px;'>$GDATA_TOKEN</td>";
	echo "<td valign=top style='padding-top: 5px;'><i>Allows access to unlisted Picasa albums. <a href='options-general.php?page=pwaplusphp&loc=gdata'>Reset Token</a></i></td></tr>";
	echo "<tfoot><tr><th valign=top colspan=3></th></tr></tfoot>\n";
	#
# PUBLIC ALBUMS
#
        echo "<tr><td valign=top style='padding-top: 7px; width: 200px;'><strong>Public Albums Only</strong></td><td valign=top style='padding-top: 7px;'><select name='pwaplusphp_public_only'>";
        if ($PUBLIC_ONLY == "TRUE") {
                $public_true = "selected";
                $public_false= "";
        } else {
                $public_true = "";
                $public_false= "selected";
        }
        echo "<option value='TRUE' $public_true>TRUE</option>";
        echo "<option value='FALSE' $public_false>FALSE</option>";
        echo "</select>\n";
        echo "</td><td valign=top style='padding-top: 8px;'><i>Set to TRUE to hide unlisted albums.</i></td></tr>";
	echo "</table>";
	# -------
	echo "<br />";
        echo "<table class='widefat'>";
        echo "<thead><tr><th valign=top colspan=3 style='$disabled_color'>Pro Features";
	if ($PRO_VERSION == "FALSE") echo " - Disabled";
	echo " </th></tr></thead>\n";
	#------------------------
	
        if ((!is_writable(WP_PLUGIN_DIR . "/pwaplusphp/cache/")) && ($PRO_VERSION == "TRUE")) {
                echo "<tr><td colspan=3 style='color: #FF0000; font-size: 10px;'>Cache directory is not writable! Try chmod 755 " . WP_PLUGIN_DIR . "/pwaplusphp/cache/" . "</td></tr>";
        }
        echo "<tr><td valign=top style='padding-top: 7px; width: 200px; $disabled_color'><strong>Cache Thumbnails</strong></td><td valign=top style='padding-top: 7px;'><select name='pwaplusphp_cache_thumbs' $pro_disabled>";
        if ($CACHE_THUMBNAILS == "FALSE") {
                $cache_true = "";
                $cache_false= "selected";
        } else {
                $cache_true = "selected";
                $cache_false= "";
        }
        echo "<option value='TRUE' $cache_true>Enable</option>";
        echo "<option value='FALSE' $cache_false>Disable</option>";
        echo "</select>\n";
        echo "</td><td valign=top style='$disabled_color padding-top: 7px;'><i>Cache thumbnails on your server for faster page loads.</i></td></tr>";
	#-----------------------------------
	if ($ACTIVE_LIGHTBOX == "NONE") { $pro_disabled = "disabled"; }
	echo "<tr><td valign=top style='padding-top: 7px; width: 200px; $disabled_color'><strong>Comment System</strong></td><td valign=top style='padding-top: 7px;'><select name='pwaplusphp_show_comments' $pro_disabled>";
        if ($SHOW_COMMENTS == "FALSE") {
                $comments_true = "";
                $comments_false= "selected";
        } else {
                $comments_true = "selected";
                $comments_false= "";
        }
        echo "<option value='TRUE' $comments_true>Enable</option>";
        echo "<option value='FALSE' $comments_false>Disable</option>";
        echo "</select>\n";
	if ($ACTIVE_LIGHTBOX == "NONE") {
                echo "</td><td style='color: #FF0000; padding-top: 7px; font-size: 10px;'>Error: Can't find active supported Lightbox.  Check <a href='http://code.google.com/p/pwaplusphp/w/list' target='_BLANK'>the wiki</a> for supported plugins.</td></tr>";
		$pro_disabled="";
        } else {
        	echo "</td><td valign=top style='$disabled_color; padding-top: 8px;'><i>Allow users to comment on photos via API. Requires <a href='http://wordpress.org/extend/plugins/shadowbox-js/'>Shadowbox JS</a> or <a href='http://wordpress.org/extend/plugins/auto-highslide/'>Auto HighSlide</a>.</i></td></tr>";
	}
	#------------------------
        $args = array('selected' => $MAIN_PHOTO_PAGE, 'show_option_none' => "None");
        echo "<tr><td valign=top style='padding-top: 7px; width: 200px; $disabled_color'><strong>Main Photo Page</strong></td><td valign=top style='padding-top: 7px;'>";
        if ($PRO_VERSION == "TRUE") { wp_dropdown_pages($args); }
        echo "</td><td valign=top style='$disabled_color padding-top: 7px;'><i>Create a page with [pwaplusphp] and select it. Required for album cover shortcode.</i></td></tr>";
        #--------------------------
        echo "<tr><td valign=top style='padding-top: 7px; width: 200px; $disabled_color'><strong>jQuery Page Transition</strong></td><td valign=top style='padding-top: 7px;'><select name='pwaplusphp_jq_pagination' $pro_disabled>";;
        $available_styles = array("blindX","blindY","blindZ","cover","curtainX","curtainY","fade","fadeZoom","growX","growY","none","scrollUp","scrollDown","scrollLeft","scrollRight","scrollHorz","scrollVert","shuffle","slideX","slideY","toss","turnUp","turnDown","turnLeft","turnRight","uncover","wipe","zoom");
        foreach ($available_styles as $style) {
                if ($JQ_PAGINATION_STYLE != $style) {
                        echo "<option value='$style'>$style</option>";
                } else {
                        echo "<option value='$style' selected>$style</option>";
                }
        }
        echo "</td><td valign=top style='$disabled_color padding-top: 7px;'><i>Set <a href='http://jquery.malsup.com/cycle/browser.html' target='_BLANK' alt='See Transition Demos' title='See Transition Demos'>page transition style</a>. Use \"none\" to disable.</i></td></tr>";
        #--------------------------
	echo "<tfoot><tr><th valign=top colspan=3></th></tr></tfoot>\n";
	echo "</table>";	
        # -------
	echo "<br />";
	echo "<table class='widefat'>";
	echo "<thead><tr><th valign=top colspan=3>Basic Display Settings</th></tr></thead>\n";
	echo "<tr><td valign=top style='padding-top: 7px; width: 200px;'><strong>Site Language</strong></td><td valign=top style='padding-top: 7px;'><select name='pwaplusphp_language'>";
		
	$dir = dirname(__FILE__)."/lang/";
	// Open a known directory, and proceed to read its contents
	if (is_dir($dir)) {
    		if ($dh = opendir($dir)) {
        		while (($file = readdir($dh)) !== false) {
				list($fn,$ext) = split ('\.',$file);
				if ($ext == "php") {
					if ($fn != $SITE_LANGUAGE) {
						echo "<option value='$fn'>$fn</option>";
					} else {
						echo "<option value='$fn' selected>$fn</option>";
					}
				}
        		}
        		closedir($dh);
    		}
	}
	echo "</select></td><td valign=top style='padding-top: 8px;'><i>Sets the display language.  More may be available <a href='http://code.google.com/p/pwaplusphp/downloads/list'>here</a>.</i></td></tr>";	
	echo "<tr><td valign=top style='padding-top: 7px; width: 200px;'><strong>Album Date Format</strong></td><td valign=top style='padding-top: 7px;'><input type='text' style='width: 50px;'  name='pwaplusphp_date_format' value='$DATE_FORMAT'/>";
        echo "</td><td valign=top style='padding-top: 8px;'><i>Define the <a href='http://php.net/manual/en/function.date.php' target='_BLANK'>date format</a> for albums.  Default setting is Y-m-d, i.e. 2010-03-12. </i></td></tr>";
# -------
        echo "<tr><td valign=top style='padding-top: 7px; width: 200px;'><strong>Display Style</strong></td><td valign=top style='padding-top: 7px;'><select name='pwaplusphp_show_caption'>";
        if ($SHOW_IMG_CAPTION == "ALWAYS") {
                $caption_always = "selected";
                $caption_hover  = "";
                $caption_never  = "";
                $caption_overlay  = "";
                $caption_blank  = "";
        } else if ($SHOW_IMG_CAPTION == "HOVER") {
                $caption_always = "";
                $caption_hover  = "selected";
                $caption_never  = "";
                $caption_overlay  = "";
                $caption_blank  = "";
        } else if ($SHOW_IMG_CAPTION == "OVERLAY") {
                $caption_always = "";
                $caption_hover  = "";
                $caption_never  = "";
                $caption_overlay  = "selected";
                $caption_blank  = "";
        } else if ($SHOW_IMG_CAPTION == "CUSTOM") {
                $caption_always = "";
                $caption_hover  = "";
                $caption_never  = "";
                $caption_overlay  = "";
                $caption_blank  = "selected";
	} else if ($SHOW_IMG_CAPTION == "SLIDESHOW") {
                $caption_always = "";
                $caption_hover  = "";
                $caption_never  = "";
                $caption_overlay  = "";
                $caption_blank  = "";
        } else {
                $caption_always = "";
                $caption_hover  = "";
                $caption_never  = "selected";
                $caption_overlay  = "";
                $caption_blank  = "";
        }
        echo "<option value='ALWAYS' $caption_always>Always Show Caption</option>";
        echo "<option value='HOVER' $caption_hover>Caption On Hover</option>";
        echo "<option value='OVERLAY' $caption_overlay>Overlay Caption</option>";
        echo "<option value='NEVER' $caption_never>Never Show Caption</option>";
        echo "<option value='CUSTOM' $caption_blank>Custom Style</option>";
        echo "</select>\n";
        echo "</td><td valign=top style='padding-top: 8px;'><i>Set display style and placement of captions. Edit CSS for Custom Style.</i></td></tr>";
	#------------------
        # Enabled for WordPress plugin v0.3
        #
        echo "<tr><td valign=top style='padding-top: 7px; width: 200px;'><strong>Permit Image Download</strong></td><td valign=top style='padding-top: 7px;'><select name='pwaplusphp_permit_download'>";
        if ($PERMIT_IMG_DOWNLOAD == "FALSE") {
                $download_true = "";
                $download_false= "selected";
        } else {
                $download_true = "selected";
                $download_false= "";
        }
        echo "<option value='TRUE' $download_true>TRUE</option>";
        echo "<option value='FALSE' $download_false>FALSE</option>";
        echo "</select>\n";
        echo "</td><td valign=top style='padding-top: 8px;'><i>Determines whether the user can download the original full-size image.</i></td></tr>";
	#------------------
	echo "<tr><td valign=top style='padding-top: 7px; width: 200px;'><strong>Images Per Page</strong></td><td valign=top style='padding-top: 7px;'><input type='text' style='width: 50px;'  name='pwaplusphp_images_per_page' value='$IMAGES_PER_PAGE'/>";
	#$per_page = array(0,5,6,8,12,15,16,20,24,25,28,30,32,35,36,40,42,48,50);
        #foreach ($per_page as $ipp) {
        #        if ($IMAGES_PER_PAGE != $ipp) {
        #                echo "<option value='$ipp'>$ipp</option>";
        #        } else {
        #                echo "<option value='$ipp' selected>$ipp</option>";
        #        }
        #}
        #echo "</select>\n";
        echo "</td><td valign=top style='padding-top: 8px;'><i>Thumbnails per page. Zero means don't paginate.</i></td></tr>";
#--------
	echo "<tr><td valign=top style='padding-top: 7px; width: 200px;'><strong>Albums Per Page</strong></td><td valign=top style='padding-top: 7px;'><input type='text' style='width: 50px;'  name='pwaplusphp_albums_per_page' value='$ALBUMS_PER_PAGE'/>";
echo "</td><td valign=top style='padding-top: 8px;'><i>Album thumbnails per page. Zero means don't paginate.</i></td></tr>";
#-------
	echo "<tr><td valign=top style='padding-top: 7px; width: 200px;'><strong>Image Size</strong></td><td valign=top style='padding-top: 7px;'><select name='pwaplusphp_image_size'>";
	$image_sizes = array("1600","1440","1280","1152","1024","912","800","720","640","576","512","400","320","288","200");
	foreach ($image_sizes as $size) {
		if ($IMGMAX != $size) {
			echo "<option value='$size'>$size</option>";
		} else {
			echo "<option value='$size' selected>$size</option>";
		}
	}
	echo "</select>\n";
	echo "</td><td valign=top style='padding-top: 8px;'><i>Sets the size of the image displayed in the Lightbox.</i></td></tr>";
	#-------------------------
	#
# ALBUM DETAILS
#
        echo "<tr><td valign=top style='padding-top: 7px; width: 200px;'><strong>Album Details</strong></td><td valign=top style='padding-top: 7px;'><select name='pwaplusphp_album_details'>";
        if ($SHOW_ALBUM_DETAILS == "FALSE") {
                $details_true = "";
                $details_false= "selected";
        } else {
                $details_true = "selected";
                $details_false= "";
        }
        echo "<option value='TRUE' $details_true>TRUE</option>";
        echo "<option value='FALSE' $details_false>FALSE</option>";
        echo "</select>\n";
        echo "</td><td valign=top style='padding-top: 8px;'><i>Overlay album thumbnail with description on mouse hover?</i></td></tr>";
        # -------
        #echo "<tr><td valign=top style='padding-top: 7px; width: 200px;'><strong>Use Lightbox</strong></td><td valign=top style='padding-top: 7px;'><select name='ul'>";
        #echo "<option value='TRUE'>TRUE</option>";
        #echo "<option value='FALSE'>FALSE</option>";
        #echo "</select>\n";
        #echo "</td></tr>\n";
	#echo "<tr><td valign=top colspan=2><i>Choose whether or not to use <a href='http://www.huddletogether.com/projects/lightbox2/'>Lightbox v2</a>.  It must be installed for this to work. When set to FALSE, full size images are displayed in a pop-up window.</i></td></tr>";
	#echo "<tr><td valign=top style='padding-top: 7px; width: 200px;'><strong>Standalone Mode</strong></td><td valign=top style='padding-top: 7px;'><select name='sm'>";
        #echo "<option value='TRUE' selected>TRUE</option>";
        #echo "<option value='FALSE'>FALSE</option>";
        #echo "</select>\n";
        #echo "</td></tr>\n";
        #echo "<tr><td valign=top colspan=2><i>This option allows you to specify whether this code will run within a CMS (FALSE) or whether the pages will exist outside a CMS (TRUE).  Selecting FALSE suppresses output of &lt;html&gt;, &lt;head&gt; and &lt;body&gt; tags in the source.</i></td></tr>";
        echo "<tr><td valign=top style='padding-top: 7px; width: 200px;'><strong>Album Thumbnail Size</strong></td><td valign=top style='padding-top: 7px;'><select name='pwaplusphp_album_thumbsize'>";
        $thumb_sizes = array("160","150","144","104","72","64","48","32");
        foreach ($thumb_sizes as $size) {
                if ($ALBUM_THUMBSIZE != $size) {
                        echo "<option value='$size'>$size</option>";
                } else {
                        echo "<option value='$size' selected>$size</option>";
                }
        }
        echo "</select>\n";
        echo "</td><td valign=top style='padding-top: 8px;'><i>Sets the album thumbnail size. May need to alter overlay CSS if value < 160.</i></td></tr>";
        #--------------------
        echo "<tr><td valign=top style='padding-top: 7px; width: 200px;'><strong>Photo Thumbnail Size</strong></td><td valign=top style='padding-top: 7px;'><select name='pwaplusphp_thumbnail_size'>";
        $thumb_sizes = array("160","150","144","104","72","64","48","32");
        foreach ($thumb_sizes as $size) {
                if ($GALLERY_THUMBSIZE != $size) {
                        echo "<option value='$size'>$size</option>";
                } else {
                        echo "<option value='$size' selected>$size</option>";
                }
        }
        echo "</select>\n";
        echo "</td><td valign=top style='padding-top: 8px;'><i>Sets the photo thumbnails size.</i></td></tr>";
	#
        # CROP THUMBNAILS
        #
        echo "<tr><td valign=top style='padding-top: 7px; width: 200px;'><strong>Crop Thumbnails</strong></td><td valign=top style='padding-top: 7px;'><select name='pwaplusphp_crop_thumbs'>";
        if ($CROP_THUMBNAILS == "FALSE") {
                $crop_true = "";
                $crop_false= "selected";
        } else {
                $crop_true = "selected";
                $crop_false= "";
        }
        echo "<option value='TRUE' $crop_true>TRUE</option>";
        echo "<option value='FALSE' $crop_false>FALSE</option>";
        echo "</select>\n";
        echo "</td><td valign=top style='padding-top: 8px;'><i>Crop image thumbnails to square size or use actual ratio</i></td></tr>";
	echo "<tfoot><tr><th valign=top colspan=3></th></tr></tfoot>\n";	
	echo "</table>";
	echo "<br />";
	echo "<table class='widefat' cellspacing=5 width=700>\n";
	echo "<thead><tr><th valign=top colspan=3>Advanced Settings</th></tr></thead>\n";
        #------------------------
echo "<tr><td valign=top style='padding-top: 7px; width: 200px;'><strong>Truncate Album Names</strong></td><td valign=top style='padding-top: 7px;'><select name='pwaplusphp_truncate_names'>";
        if ($TRUNCATE_ALBUM_NAME == "FALSE") {
                $truncate_true = "";
                $truncate_false= "selected";
        } else {
                $truncate_true = "selected";
                $truncate_false= "";
        }
        echo "<option value='TRUE' $truncate_true>TRUE</option>";
        echo "<option value='FALSE'$truncate_false>FALSE</option>";
        echo "</select>\n";
        echo "</td><td valign=top style='padding-top: 8px;'><i>Shorten album name to ensure proper display of fluid layout?</i></td></tr>";
	
	#------------
	echo "<tr><td valign=top style='padding-top: 7px; width: 200px;'><strong>Description Length Limit</strong></td><td valign=top style='padding-top: 7px;'><input style='width: 50px;' type='text' name='pwaplusphp_description_length' value='$DESCRIPTION_LENGTH'></td><td valign=top style='padding-top: 8px;'><i>Trim display length of description to specific number of characters</i></td></tr>";
	echo "<tr><td valign=top style='padding-top: 7px; width: 200px;'><strong>Caption Length Limit</strong></td><td valign=top style='padding-top: 7px;'><input style='width: 50px;' type='text' name='pwaplusphp_caption_length' value='$CAPTION_LENGTH'></td><td valign=top style='padding-top: 8px;'><i>Trim display length of captions to specific number of characters</i></td></tr>";
	#-----------
	echo "<tr><td valign=top style='padding-top: 7px; width: 200px;'><strong>Require Filter</strong></td><td valign=top style='padding-top: 7px;'><select name='pwaplusphp_require_filter'>";
        echo "<option value='TRUE'>TRUE</option>";
        echo "<option value='FALSE' selected>FALSE</option>";
        echo "</select>\n";
        echo "</td><td valign=top style='padding-top: 8px;'><i>Is filter required? Most users should select FALSE.</i></td></tr>";
	#-----------
        echo "<tr><td valign=top style='padding-top: 7px; width: 200px;'><strong>Show Drop Box</strong></td><td valign=top style='padding-top: 7px;'><select name='pwaplusphp_show_dropbox'>";
	if ($SHOW_DROP_BOX == "FALSE") {
                $dropbox_true = "";
                $dropbox_false= "selected";
        } else {
                $dropbox_true = "selected";
                $dropbox_false= "";
        }
        echo "<option value='TRUE' $dropbox_true>TRUE</option>";
        echo "<option value='FALSE' $dropbox_false>FALSE</option>";
        echo "</select>\n";
        echo "</td><td valign=top style='padding-top: 8px;'><i>Show the <a target='_BLANK' href='http://picasa.google.com/support/bin/answer.py?hl=en&answer=73970'>Drop Box</a> on all pages?</i></td></tr>";
	#------------------------
        echo "<tr><td valign=top style='padding-top: 7px; width: 200px;'><strong>Hide Video</strong></td><td valign=top style='padding-top: 7px;'><select name='pwaplusphp_hide_video'>";
        if ($HIDE_VIDEO == "FALSE") {
                $hidevideo_true = "";
                $hidevideo_false= "selected";
        } else {
                $hidevideo_true = "selected";
                $hidevideo_false= "";
        }
        echo "<option value='TRUE' $hidevideo_true>TRUE</option>";
        echo "<option value='FALSE' $hidevideo_false>FALSE</option>";
        echo "</select>\n";
        echo "</td><td valign=top style='padding-top: 8px;'><i>Determines whether your videos are displayed within albums</i></td></tr>";
        #------------------------
        #echo "<tr><td valign=top style='padding-top: 7px; width: 200px;'><strong>Check For Updates</strong></td><td valign=top style='padding-top: 7px;'><select name='pwaplusphp_check_updates'>";
	#if ($CHECK_FOR_UPDATES == "FALSE") {
        #        $updates_true = "";
        #        $updates_false= "selected";
        #} else {
        #        $updates_true = "selected";
        #        $updates_false= "";
        #}
        #echo "<option value='TRUE' $updates_true>TRUE</option>";
        #echo "<option value='FALSE' $update_false>FALSE</option>";
        #echo "</select>\n";
        #echo "</td></tr>\n";
        #echo "<tr><td valign=top colspan=2><i>When TRUE, the script will check the server once per month and print a small message at the bottom of the page if a newer version of the code is available.  Set to FALSE to completely disable update checks.</i></td></tr>";
	 echo "<tfoot><tr><th valign=top colspan=3></th></tr></tfoot>\n";
	echo "</table>\n";
?>
<p class="submit">
<input class='button-primary' type="submit" name="Submit" value="<?php _e('Update Options', 'pwaplusphp' ) ?>" />
</p>
<?php
	echo "</form>\n";
}

function exchangeToken($single_use_token) {


        $ch = curl_init("https://www.google.com/accounts/AuthSubSessionToken");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: AuthSub token="' . $single_use_token . '"'
                ));

        $result = curl_exec($ch);  /* Execute the HTTP command. */

        # See if there has been a curl error
        if(curl_errno($ch)) {

                echo "<p><strong>Error: Could not generate session token! Exiting...</strong></p>";
                die ('Curl error: ' . curl_error($ch));

        }
        curl_close($ch);
        $splitStr = split("=", $result);

        if (strlen($splitStr[1]) > 14) {
                return trim($splitStr[1]);
        } else {
                echo "<p><strong>Error: Could not generate session token! Exiting...</strong></p>";
                die('Unexpected value returned to exchangeToken(): ' . $splitStr[1]);
        }

}

function set_gdata_token() {

	$token = $_GET['token'];
	$newToken = exchangeToken($token);

	update_option("pwaplusphp_gdata_token",$newToken);

	echo "<h2>Install Step 1: Complete!</h2>";
	echo "Token retrieved and saved in WordPress configuration database. Value is '$newToken'.<br />";

	$uri = $_SERVER["REQUEST_URI"];
	list($back_link,$uri_tail) = split('&',$uri);

	echo "Continue to <a href='$back_link'>Step 2</a>...\n";

}

function set_options() {

	$THIS_VERSION = "0.9.4g";

	update_option("pwaplusphp_picasa_username", $_POST['pwaplusphp_picasa_username']);
	update_option("pwaplusphp_image_size",$_POST['pwaplusphp_image_size']);
	update_option("pwaplusphp_thumbnail_size",$_POST['pwaplusphp_thumbnail_size']);	
	update_option("pwaplusphp_album_thumbsize",$_POST['pwaplusphp_album_thumbsize']);
	update_option("pwaplusphp_require_filter",$_POST['pwaplusphp_require_filter']);
	update_option("pwaplusphp_images_per_page",$_POST['pwaplusphp_images_per_page']);
	update_option("pwaplusphp_albums_per_page",$_POST['pwaplusphp_albums_per_page']);
	update_option("pwaplusphp_public_only",$_POST['pwaplusphp_public_only']);
	update_option("pwaplusphp_album_details",$_POST['pwaplusphp_album_details']);
	update_option("pwaplusphp_show_dropbox",$_POST['pwaplusphp_show_dropbox']);
	update_option("pwaplusphp_truncate_names",$_POST['pwaplusphp_truncate_names']);
	update_option("pwaplusphp_version",$THIS_VERSION);
	update_option("pwaplusphp_language",$_POST['pwaplusphp_language']);
	update_option("pwaplusphp_permit_download",$_POST['pwaplusphp_permit_download']);
	update_option("pwaplusphp_show_footer",$_POST['pwaplusphp_show_footer']);
	update_option("pwaplusphp_show_caption",$_POST['pwaplusphp_show_caption']);
	update_option("pwaplusphp_description_length",$_POST['pwaplusphp_description_length']);
	update_option("pwaplusphp_caption_length",$_POST['pwaplusphp_caption_length']);
	update_option("pwaplusphp_crop_thumbs",$_POST['pwaplusphp_crop_thumbs']);
	update_option("pwaplusphp_date_format",$_POST['pwaplusphp_date_format']);
	update_option("pwaplusphp_hide_video",$_POST['pwaplusphp_hide_video']);
	update_option("pwaplusphp_cache_thumbs",$_POST['pwaplusphp_cache_thumbs']);
	update_option("pwaplusphp_main_photo",$_POST['page_id']);
	update_option("pwaplusphp_show_comments",$_POST['pwaplusphp_show_comments']);

}

#
# Begin Main Program
#
if  (!(in_array  ('curl', get_loaded_extensions()))) {
	echo "<p><strong>ERROR:</strong> PWA+PHP requires cURL and it is not enabled on your webserver.  Contact your hosting provider to enable cURL support.</p>";
	echo "<p><i>More info is available on the <a href='http://groups.google.com/group/pwaplusphp/browse_thread/thread/49a198c531019706'>PWA+PHP discussion group</a>.</p>";
	exit;
}


$GDATA_TOKEN = get_option("pwaplusphp_gdata_token","NULL");

# Make sure token is set before proceeding.
if (($GDATA_TOKEN == "NULL") && ($_GET['loc'] != "return")) { $loc = "gdata"; }
else { $loc = $_GET['loc']; }

if ($loc == "gdata") {
	get_gdata_token();
} else if ($loc == "return") {
	set_gdata_token();
} else if (($loc != "finish") && ($loc != "gdata")) {
	get_options();	
} else {
        set_options();
	get_options();
} 
#else {
#	if (file_exists($cfg)) {
#		$file = file_get_contents($cfg);
#		if(strpos($file, "GDATA_TOKEN") >= 0) {
#			echo "PWP+PHP is already configured.  Delete $cfg and reload this page to reconfigure.";
#		} else {
#			get_gdata_token();
#		}
#	} else {
#		get_gdata_token();
#	}

#}

global $THIS_VERSION;
echo "</td><td width='25%' valign=top style='padding-top: 0px;'>";

echo "<table class='widefat' width='100%'>";
echo "<thead><tr><th valign=top colspan=3>Help & Support</th></tr></thead>\n";
echo "<tr><td>Support is available <a href='http://code.google.com/p/pwaplusphp/w/list' target='_BLANK'>on the wiki</a> and our <a href='http://groups.google.com/group/pwaplusphp' target='_BLANK'>discussion group</a> is a good place to ask questions. You can also submit and review <a href='http://code.google.com/p/pwaplusphp/issues/entry?template=Defect%20report%20from%20user' target='_BLANK'>Bug Reports</a> and <a href='http://code.google.com/p/pwaplusphp/issues/entry?template=Enhancement%20Request' target='_BLANK'>Enhancement Requests</a> on the <a href='http://code.google.com/p/pwaplusphp/issues/list' target='_BLANK'>Issues Page</a>.</td></tr>";
echo "<tfoot><tr><th valign=top colspan=3></th></tr></tfoot>\n";
echo "</table>";
echo "<br />";
echo "<table class='widefat' width='100%'>";
echo "<thead><tr><th valign=top colspan=3>News & Announcements</th></tr></thead>\n";
echo "<tr><td>";
	// Get RSS Feed(s) 
	include_once(ABSPATH . WPINC . '/feed.php'); 
	// Get a SimplePie feed object from the specified feed source. 
	$dateu = date("U");
	$rss = fetch_feed("http://wordpress.org/support/rss/tags/pwaplusphp&$dateu");
 	if (!is_wp_error( $rss ) ) :
 		// Checks that the object is created correctly      
		// Figure out how many total items there are, but limit it to 5.
		$count=0;      
		$maxitems = $rss->get_item_quantity(50);      
		
		// Build an array of all the items, starting with element 0 (first element).     
		$rss_items = $rss->get_items(0, $maxitems);  
		endif; ?> 
		<ul>     
		<?php 
			if ($maxitems == 0) {
				echo '<li>No items.</li>';     
			} else {     
				// Loop through each feed item and display each item as a hyperlink.     
				foreach ( $rss_items as $item ) {
					$title = $item->get_title();
					$author = substr($title,0,8);
					$title = substr($title,36);
					$title = substr($title,0,-6);	// Removes &quote; from the end
					$news = substr($title,-6);
					$title = substr($title,0,-6);
					if (($author == "smccandl") && ($count <= 5) && ($news == "[News]")) { 
					$count++;
					?>
						<li>
							<a target='_BLANK' href='<?php echo $item->get_permalink(); ?>' title='<?php echo 'Posted '.$item->get_date('j F Y | g:i a'); ?>'>
				       		<?php echo $title ?></a>
						</li>
					<?php } 
				}
			 } ?> 
		</ul>
<?php
echo "</td></tr>";
echo "<tfoot><tr><th valign=top colspan=3></th></tr></tfoot>\n";
echo "</table>";
echo "<br />";
echo "<table class='widefat' width='100%'>";
if ($PRO_VERSION == "TRUE") {
	$pro_version_msg = check_for_updates($THIS_VERSION);
	$pv = "Pro";
	$pro_title = "You are using PWA+PHP Pro";
} else {
	$pv = "Basic";
	$pro_version_msg = "The <a href='http://pwaplusphp.smccandl.net/pro/' target='_BLANK'>Pro Version</a> offers advanced features including: support for comments, thumbnail and XML caching for dramatically faster page loads, jQuery effects and extra short codes.";
	$pro_version_msg .= "<form action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\">";
$pro_version_msg .= "<input type=\"hidden\" name=\"cmd\" value=\"_s-xclick\">";
$pro_version_msg .= "<input type=\"hidden\" name=\"encrypted\" value=\"-----BEGIN PKCS7-----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-----END PKCS7-----\">";
$pro_version_msg .= "<input style='margin-top: 7px; float: right;' type='submit' value='Donate Now!' class='button-secondary' />";
$pro_version_msg .= "</form>";
	$pro_title = "24x Faster Page Loads with Pro!";
}

echo "<thead><tr><th valign=top colspan=3>$pro_title</th></tr></thead>\n";
echo "<tr><td>$pro_version_msg";
echo "</td></tr>";
echo "<tfoot><tr><th valign=top colspan=3></th></tr></tfoot>\n";
echo "</table>";
echo "<br />";
if ($PRO_VERSION == "TRUE") {
	echo "<table class='widefat' width='100%'>";
	echo "<thead><tr><th valign=top colspan=3>Disclaimer</th></tr></thead>\n";
	echo "<tr><td>All Rights Reserved. PWA+PHP is provided as-is with no guarantee or warranty. You may not copy, redistribute or sell PWA+PHP Pro. Use of the software constitues agreement to these conditions.</td></tr>";
	echo "<tfoot><tr><th valign=top colspan=3></th></tr></tfoot>\n";
	echo "</table>";
	echo "<br />";
}
echo "<table class='widefat' width='100%'>";
echo "<thead><tr><th valign=top colspan=3>Server Information</th></tr></thead>\n";
echo "<tr><td>";
echo "<table cellspacing=0 width='100%'>";
echo "<tr><th>PWA+PHP</th><td>v" . $THIS_VERSION . " $pv</td></tr>";
echo "<tr><th>Hostname</th><td>" . $_SERVER['SERVER_NAME'] . "</td></tr>";
list($ws,$os) = split(' ',$_SERVER['SERVER_SOFTWARE']);
$curlver = curl_version();
echo "<tr><th valign=top>Webserver</th><td>" . $ws . " " .$os . "</td></tr>";
echo "<tr><th valign=top>PHP/cURL</th><td>v" . phpversion() . " / v" . $curlver["version"] . "</td></tr>";
echo "</table>";
echo "<td></tr>";
echo "<tfoot><tr><th valign=top colspan=3></th></tr></tfoot>\n";
echo "</table>";
echo "<p><img src='http://code.google.com/apis/picasaweb/images/wwpicasa120x60.gif' /></p>";
echo "</td></tr></table>";
echo "</div>";
?>
