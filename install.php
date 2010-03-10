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

function get_gdata_token() {

	$site = $_SERVER['SERVER_NAME'];
	$port = ($_SERVER['SERVER_PORT'] != 80) ? ':' . $_SERVER['SERVER_PORT'] : '';
	$self  = $_SERVER['PHP_SELF'];
	$loc  = urlencode("http://" . $site . $port . $self . "?page=pwaplusphp&loc=return");
	$next = "http://www.google.com/accounts/AuthSubRequest?scope=http%3A%2F%2Fpicasaweb.google.com%2Fdata%2F&session=1&secure=0&next=$loc";
	echo "<h2>Install Step 1: Token Generation</h2>";
	echo "<p>Generating this Google \"GData\" token is a one-time step that allows PWA+PHP to access to your private (unlisted) Picasa albums. Click the link below to continue if you wish to set up PicasaWeb tokens for site: <strong>$site</strong></p>";
	echo "<p>If this is correct, <a href='$next'>";
	echo "Login to your Google Account</a></p>"; 
	echo "</body>\n</html>";

}

function get_options() {

$GDATA_TOKEN		= get_option("pwaplusphp_gdata_token");
$PICASAWEB_USER         = get_option("pwaplusphp_picasa_username");
$IMGMAX                 = get_option("pwaplusphp_image_size","640");
$GALLERY_THUMBSIZE      = get_option("pwaplusphp_thumbnail_size",160);
$ALBUM_THUMBSIZE      	= get_option("pwaplusphp_album_thumbsize",160);
$REQUIRE_FILTER         = get_option("pwaplusphp_require_filter","FALSE");
$IMAGES_PER_PAGE        = get_option("pwaplusphp_images_per_page",0);
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
#$HIDE_VIDEO             = get_option("pwaplusphp_hide_video","FALSE");

	echo "<h2>PWA+PHP Settings Panel</h2>";
	echo "<form name=form1 action='$self?page=pwaplusphp&loc=finish' method='post'>\n";
	echo "<table cellspacing=5 width=700>\n";
	echo "<tr><td valign=top colspan=3><h3>Picasa Access Settings</h3></td></tr>\n";
	echo "<tr><td valign=top style='padding-bottom: 20px; width: 200px;'><strong>Picasaweb User</strong></td><td valign=top style='padding-bottom: 20px;'><input style='width: 150px;' type='text' name='pwaplusphp_picasa_username' value='$PICASAWEB_USER'></td><td valign=top><i>Enter your Picasa username.</i></td></tr>";

	echo "<tr><td valign=top style='padding-bottom: 20px; width: 200px;'><strong>GData Token</strong></td><td valign=top style='padding-bottom: 20px;'>$GDATA_TOKEN</td>";
	echo "<td valign=top><i>Allows access to your unlisted Picasa albums.</i></td></tr>";
	#
# PUBLIC ALBUMS
#
        echo "<tr><td valign=top style='padding-bottom: 20px; width: 200px;'><strong>Public Albums Only</strong></td><td valign=top style='padding-bottom: 20px;'><select name='pwaplusphp_public_only'>";
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
        echo "</td><td valign=top><i>Set to TRUE to hide unlisted albums.</i></td></tr>";
        # -------

	echo "<tr><td valign=top colspan=3><h3>Basic Display Settings</h3></td></tr>\n";
	echo "<tr><td valign=top style='padding-bottom: 20px; width: 200px;'><strong>Site Language</strong></td><td valign=top style='padding-bottom: 20px;'><select name='pwaplusphp_language'>";
		
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
	echo "</select></td><td valign=top><i>Sets the display language.  More may be available <a href='http://code.google.com/p/pwaplusphp/downloads/list'>here</a>.</i></td></tr>";	

	echo "<tr><td valign=top style='padding-bottom: 20px; width: 200px;'><strong>Images Per Page</strong></td><td valign=top style='padding-bottom: 20px;'><input type='text' style='width: 50px;'  name='pwaplusphp_images_per_page' value='$IMAGES_PER_PAGE'/>";
	#$per_page = array(0,5,6,8,12,15,16,20,24,25,28,30,32,35,36,40,42,48,50);
        #foreach ($per_page as $ipp) {
        #        if ($IMAGES_PER_PAGE != $ipp) {
        #                echo "<option value='$ipp'>$ipp</option>";
        #        } else {
        #                echo "<option value='$ipp' selected>$ipp</option>";
        #        }
        #}
        #echo "</select>\n";
        echo "</td><td valign=top><i>Number of thumbnails per page. Zero means don't paginate. Larger number = more memory.</i></td></tr>";
	echo "<tr><td valign=top style='padding-bottom: 20px; width: 200px;'><strong>Image Size</strong></td><td valign=top style='padding-bottom: 20px;'><select name='pwaplusphp_image_size'>";
	$image_sizes = array("1600","1440","1280","1152","1024","912","800","720","640","576","512","400","320","288","200");
	foreach ($image_sizes as $size) {
		if ($IMGMAX != $size) {
			echo "<option value='$size'>$size</option>";
		} else {
			echo "<option value='$size' selected>$size</option>";
		}
	}
	echo "</select>\n";
	echo "</td><td valign=top><i>Sets the size of the image displayed in the Lightbox.</i></td></tr>";
	#
	# CROP THUMBNAILS
	#
        echo "<tr><td valign=top style='padding-bottom: 20px; width: 200px;'><strong>Crop Thumbnails</strong></td><td valign=top style='padding-bottom: 20px;'><select name='pwaplusphp_crop_thumbs'>";
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
        echo "</td><td valign=top><i>Crop image thumbnails to square size or use actual ratio</i></td></tr>";
	#--------------
        echo "<tr><td valign=top style='padding-bottom: 20px; width: 200px;'><strong>Album Thumbnail Size</strong></td><td valign=top style='padding-bottom: 20px;'><select name='pwaplusphp_album_thumbsize'>";
        $thumb_sizes = array("160","150","144","104","72","64","48","32");
        foreach ($thumb_sizes as $size) {
                if ($ALBUM_THUMBSIZE != $size) {
                        echo "<option value='$size'>$size</option>";
                } else {
                        echo "<option value='$size' selected>$size</option>";
                }
        }
        echo "</select>\n";
        echo "</td><td valign=top><i>Sets the album thumbnail size. May need to alter overlay CSS if value < 160.</i></td></tr>";
	#--------------------
	echo "<tr><td valign=top style='padding-bottom: 20px; width: 200px;'><strong>Photo Thumbnail Size</strong></td><td valign=top style='padding-bottom: 20px;'><select name='pwaplusphp_thumbnail_size'>";
	$thumb_sizes = array("160","150","144","104","72","64","48","32");
        foreach ($thumb_sizes as $size) {
                if ($GALLERY_THUMBSIZE != $size) {
                        echo "<option value='$size'>$size</option>";
                } else {
                        echo "<option value='$size' selected>$size</option>";
                }
        }
	echo "</select>\n";
	echo "</td><td valign=top><i>Sets the photo thumbnails size.</i></td></tr>";
#--------------------
        echo "<tr><td valign=top style='padding-bottom: 20px; width: 200px;'><strong>Album Date Format</strong></td><td valign=top style='padding-bottom: 20px;'><input type='text' style='width: 50px;'  name='pwaplusphp_date_format' value='$DATE_FORMAT'/>";
        echo "</td><td valign=top><i>Define the <a href='http://php.net/manual/en/function.date.php' target='_BLANK'>date format</a> for albums.  Default setting is Y-m-d, i.e. 2010-03-12. </i></td></tr>";
	#-------------------------
        #echo "<tr><td valign=top style='padding-bottom: 20px; width: 200px;'><strong>Use Lightbox</strong></td><td valign=top style='padding-bottom: 20px;'><select name='ul'>";
        #echo "<option value='TRUE'>TRUE</option>";
        #echo "<option value='FALSE'>FALSE</option>";
        #echo "</select>\n";
        #echo "</td></tr>\n";
	#echo "<tr><td valign=top colspan=2><i>Choose whether or not to use <a href='http://www.huddletogether.com/projects/lightbox2/'>Lightbox v2</a>.  It must be installed for this to work. When set to FALSE, full size images are displayed in a pop-up window.</i></td></tr>";
	#echo "<tr><td valign=top style='padding-bottom: 20px; width: 200px;'><strong>Standalone Mode</strong></td><td valign=top style='padding-bottom: 20px;'><select name='sm'>";
        #echo "<option value='TRUE' selected>TRUE</option>";
        #echo "<option value='FALSE'>FALSE</option>";
        #echo "</select>\n";
        #echo "</td></tr>\n";
        #echo "<tr><td valign=top colspan=2><i>This option allows you to specify whether this code will run within a CMS (FALSE) or whether the pages will exist outside a CMS (TRUE).  Selecting FALSE suppresses output of &lt;html&gt;, &lt;head&gt; and &lt;body&gt; tags in the source.</i></td></tr>";
	echo "<tr><td valign=top colspan=3><h3>Caption & Description Settings</h3></td></tr>\n";
#
# ALBUM DETAILS
#
        echo "<tr><td valign=top style='padding-bottom: 20px; width: 200px;'><strong>Album Details</strong></td><td valign=top style='padding-bottom: 20px;'><select name='pwaplusphp_album_details'>";
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
        echo "</td><td valign=top><i>Overlay album thumbnail with description on mouse hover?</i></td></tr>";
        # -------
echo "<tr><td valign=top style='padding-bottom: 20px; width: 200px;'><strong>Truncate Album Names</strong></td><td valign=top style='padding-bottom: 20px;'><select name='pwaplusphp_truncate_names'>";
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
        echo "</td><td valign=top><i>Shorten album name to ensure proper display of fluid layout?</i></td></tr>";
	
	#------------
	echo "<tr><td valign=top style='padding-bottom: 20px; width: 200px;'><strong>Description Length Limit</strong></td><td valign=top style='padding-bottom: 20px;'><input style='width: 50px;' type='text' name='pwaplusphp_description_length' value='$DESCRIPTION_LENGTH'></td><td valign=top><i>Trim display length of description to specific number of characters</i></td></tr>";
	# -------
        echo "<tr><td valign=top style='padding-bottom: 20px; width: 200px;'><strong>Show Photo Caption</strong></td><td valign=top style='padding-bottom: 20px;'><select name='pwaplusphp_show_caption'>";
        if ($SHOW_IMG_CAPTION == "ALWAYS") {
                $caption_always = "selected";
                $caption_hover  = "";
                $caption_never  = "";
                $caption_overlay  = "";
        } else if ($SHOW_IMG_CAPTION == "HOVER") {
                $caption_always = "";
                $caption_hover  = "selected";
                $caption_never  = "";
                $caption_overlay  = "";
        } else if ($SHOW_IMG_CAPTION == "OVERLAY") {
                $caption_always = "";
                $caption_hover  = "";
                $caption_never  = "";
                $caption_overlay  = "selected";
        } else {
                $caption_always = "";
                $caption_hover  = "";
                $caption_never  = "selected";
                $caption_overlay  = "";
        }
        echo "<option value='ALWAYS' $caption_always>Always</option>";
        echo "<option value='HOVER' $caption_hover>On Hover</option>";
        echo "<option value='OVERLAY' $caption_overlay>Overlay</option>";
        echo "<option value='NEVER' $caption_never>Never</option>";
        echo "</select>\n";
        echo "</td><td valign=top><i>Show captions under photos? Works best with larger thumbnails.</i></td></tr>";
	#------------
	echo "<tr><td valign=top style='padding-bottom: 20px; width: 200px;'><strong>Caption Length Limit</strong></td><td valign=top style='padding-bottom: 20px;'><input style='width: 50px;' type='text' name='pwaplusphp_caption_length' value='$CAPTION_LENGTH'></td><td valign=top><i>Trim display length of captions to specific number of characters</i></td></tr>";
	#-----------
	echo "<tr><td valign=top colspan=3><h3>Additional Display Settings</h3></td></tr>\n";
	#-----------
	echo "<tr><td valign=top style='padding-bottom: 20px; width: 200px;'><strong>Require Filter</strong></td><td valign=top style='padding-bottom: 20px;'><select name='pwaplusphp_require_filter'>";
        echo "<option value='TRUE'>TRUE</option>";
        echo "<option value='FALSE' selected>FALSE</option>";
        echo "</select>\n";
        echo "</td><td valign=top><i>Is filter required? Most users should select FALSE.</i></td></tr>";
	#-----------
        echo "<tr><td valign=top style='padding-bottom: 20px; width: 200px;'><strong>Show Drop Box</strong></td><td valign=top style='padding-bottom: 20px;'><select name='pwaplusphp_show_dropbox'>";
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
        echo "</td><td valign=top><i>Show the <a target='_BLANK' href='http://picasa.google.com/support/bin/answer.py?hl=en&answer=73970'>Drop Box</a> on all pages?</i></td></tr>";
	# -------
	# Enabled for WordPress plugin v0.3
	#
        echo "<tr><td valign=top style='padding-bottom: 20px; width: 200px;'><strong>Permit Image Download</strong></td><td valign=top style='padding-bottom: 20px;'><select name='pwaplusphp_permit_download'>";
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
        echo "</td><td valign=top><i>Determines whether the user can download the original full-size image.</i></td></tr>";
	# -------
	# -------
        echo "<tr><td valign=top style='padding-bottom: 20px; width: 200px;'><strong>Show Footer Message</strong></td><td valign=top style='padding-bottom: 20px;'><select name='pwaplusphp_show_footer'>";
        if ($SHOW_FOOTER == "TRUE") {
                $footer_true = "selected";
                $footer_false= "";
        } else {
                $footer_true = "";
                $footer_false= "selected";
        }
        echo "<option value='TRUE' $footer_true>TRUE</option>";
        echo "<option value='FALSE' $footer_false>FALSE</option>";
        echo "</select>\n";
        echo "</td><td valign=top><i>Allow PWA+PHP to display a 'generated by' message at the bottom of the page?</i></td></tr>";
	#------------------------
#	#echo "<tr><td valign=top style='padding-bottom: 20px; width: 200px;'><strong>Hide Video</strong></td><td valign=top style='padding-bottom: 20px;'><select name='pwaplusphp_hide_video'>";
#        if ($HIDE_VIDEO == "FALSE") {
#                $hidevideo_true = "";
#                $hidevideo_false= "selected";
#        } else {
#                $hidevideo_true = "selected";
#                $hidevideo_false= "";
#        }
#        echo "<option value='TRUE' $hidevideo_true>TRUE</option>";
#        echo "<option value='FALSE' $hidevideo_false>FALSE</option>";
#        echo "</select>\n";
#        echo "</td><td valign=top><i>Determines whether your videos are displayed within albums</i></td></tr>";
	#------------------------
        #echo "<tr><td valign=top style='padding-bottom: 20px; width: 200px;'><strong>Check For Updates</strong></td><td valign=top style='padding-bottom: 20px;'><select name='pwaplusphp_check_updates'>";
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
	echo "</table>\n";
?>
<p class="submit">
<input type="submit" name="Submit" value="<?php _e('Update Options', 'pwaplusphp' ) ?>" />
</p>
<?
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

        curl_close($ch);

        $splitStr = split("=", $result);

        return trim($splitStr[1]);

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

	$THIS_VERSION = "0.5";

	update_option("pwaplusphp_picasa_username", $_POST['pwaplusphp_picasa_username']);
	update_option("pwaplusphp_image_size",$_POST['pwaplusphp_image_size']);
	update_option("pwaplusphp_thumbnail_size",$_POST['pwaplusphp_thumbnail_size']);	
	update_option("pwaplusphp_album_thumbsize",$_POST['pwaplusphp_album_thumbsize']);
	update_option("pwaplusphp_require_filter",$_POST['pwaplusphp_require_filter']);
	update_option("pwaplusphp_images_per_page",$_POST['pwaplusphp_images_per_page']);
	update_option("pwaplusphp_public_only",$_POST['pwaplusphp_public_only']);
	update_option("pwaplusphp_album_details",$_POST['pwaplusphp_album_details']);
	#update_option("pwaplusphp_updates",$_POST['pwaplusphp_check_updates']);
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

	
	echo "<h2>Options Saved</h2>";
	echo "Configuration is complete and PWA+PHP is ready for use. To get started, create a new page with contents \"[pwaplusphp]\"."; 

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


