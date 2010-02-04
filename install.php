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
$THUMBSIZE              = get_option("pwaplusphp_thumbnail_size",160);
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

	echo "<h2>Set PWA+PHP Options</h2>";
	echo "<form name=form1 action='$self?page=pwaplusphp&loc=finish' method='post'>\n";
	echo "<table width=700>\n";
	echo "<tr><td style='padding-top: 20px; width: 200px;'><strong>Picasaweb User</strong></td><td style='padding-top: 20px;'><input type='text' name='pwaplusphp_picasa_username' value='$PICASAWEB_USER'></td></tr>\n";
	echo "<tr><td colspan=2><i>Enter your Picasaweb username.  This is the username you use to login to view your albums.</i></td></tr>";

	echo "<tr><td style='padding-top: 20px; width: 200px;'><strong>GData Token</strong></td><td style='padding-top: 20px;'>$GDATA_TOKEN</td></tr>";
	echo "<tr><td colspan=2><i>This token was generated during Step 1 and it allows PWA+PHP to access and display your private (unlisted) Picasa albums. The value cannot be changed.</i></td></tr>";

	echo "<tr><td style='padding-top: 20px; width: 200px;'><strong>Site Language</strong></td><td style='padding-top: 20px;'><select name='pwaplusphp_language'>";
		
	$dir = dirname(__FILE__)."/lang/";
	// Open a known directory, and proceed to read its contents
	if (is_dir($dir)) {
    		if ($dh = opendir($dir)) {
        		while (($file = readdir($dh)) !== false) {
				list($fn,$ext) = split ('\.',$file);
				if ($ext == "php") {
					echo "<option value='$fn'>$fn</option>";
				}
        		}
        		closedir($dh);
    		}
	}
	echo "</select></td></tr>";
	echo "<tr><td colspan=2><i>Select the language you wish to use.  More may be available <a href='http://code.google.com/p/pwaplusphp/downloads/list'>on the PWA+PHP download page</a></i></td></tr>";	

	echo "<tr><td style='padding-top: 20px; width: 200px;'><strong>Images Per Page</strong></td><td style='padding-top: 20px;'><select name='pwaplusphp_images_per_page'>";
	$per_page = array(5,6,8,12,15,16,20,24,25,28,30,32,35,36,40,42,48,50);
        foreach ($per_page as $ipp) {
                if ($IMAGES_PER_PAGE != $ipp) {
                        echo "<option value='$ipp'>$ipp</option>";
                } else {
                        echo "<option value='$ipp' selected>$ipp</option>";
                }
        }
        echo "</select>\n";
        echo "</td></tr>\n";
        echo "<tr><td colspan=2><i>Set the number of thumbnails to display per page. Value of 0 means don't paginate.</i></td></tr>";
	echo "<tr><td style='padding-top: 20px; width: 200px;'><strong>Image Size</strong></td><td style='padding-top: 20px;'><select name='pwaplusphp_image_size'>";
	$image_sizes = array("1600","1440","1280","1152","1024","912","800","720","640","576","512","400","320","288","200");
	foreach ($image_sizes as $size) {
		if ($IMGMAX != $size) {
			echo "<option value='$size'>$size</option>";
		} else {
			echo "<option value='$size' selected>$size</option>";
		}
	}
	echo "</select>\n";
	echo "</td></tr>\n";
	echo "<tr><td colspan=2><i>Set the display size for full images.  These values are supported by the Picasaweb API.</i></td></tr>";
	echo "<tr><td style='padding-top: 20px; width: 200px;'><strong>Thumbnail Size</strong></td><td style='padding-top: 20px;'><select name='pwaplusphp_thumbnail_size'>";
	$thumb_sizes = array("160","150","144","104","72","64","48","32");
        foreach ($thumb_sizes as $size) {
                if ($THUMBSIZE != $size) {
                        echo "<option value='$size'>$size</option>";
                } else {
                        echo "<option value='$size' selected>$size</option>";
                }
        }
	echo "</select>\n";
	echo "</td></tr>\n";
	echo "<tr><td colspan=2><i>Set the thumbnail size. These values are supported by the Picasaweb API.</i></td></tr>";
        #echo "<tr><td style='padding-top: 20px; width: 200px;'><strong>Use Lightbox</strong></td><td style='padding-top: 20px;'><select name='ul'>";
        #echo "<option value='TRUE'>TRUE</option>";
        #echo "<option value='FALSE'>FALSE</option>";
        #echo "</select>\n";
        #echo "</td></tr>\n";
	#echo "<tr><td colspan=2><i>Choose whether or not to use <a href='http://www.huddletogether.com/projects/lightbox2/'>Lightbox v2</a>.  It must be installed for this to work. When set to FALSE, full size images are displayed in a pop-up window.</i></td></tr>";
	#echo "<tr><td style='padding-top: 20px; width: 200px;'><strong>Standalone Mode</strong></td><td style='padding-top: 20px;'><select name='sm'>";
        #echo "<option value='TRUE' selected>TRUE</option>";
        #echo "<option value='FALSE'>FALSE</option>";
        #echo "</select>\n";
        #echo "</td></tr>\n";
        #echo "<tr><td colspan=2><i>This option allows you to specify whether this code will run within a CMS (FALSE) or whether the pages will exist outside a CMS (TRUE).  Selecting FALSE suppresses output of &lt;html&gt;, &lt;head&gt; and &lt;body&gt; tags in the source.</i></td></tr>";
	echo "<tr><td style='padding-top: 20px; width: 200px;'><strong>Require Filter</strong></td><td style='padding-top: 20px;'><select name='pwaplusphp_require_filter'>";
        echo "<option value='TRUE'>TRUE</option>";
        echo "<option value='FALSE' selected>FALSE</option>";
        echo "</select>\n";
        echo "</td></tr>\n";
        echo "<tr><td colspan=2><i>Set this to FALSE unless you want to *require* a search filter in the URL -- you can still filter albums with this set to FALSE.  Setting to TRUE *requires* a filter string in the URL to prevent certain users from seeing certain albums.</i></td></tr>";
#
# PUBLIC ALBUMS
#
	echo "<tr><td style='padding-top: 20px; width: 200px;'><strong>Public Albums Only</strong></td><td style='padding-top: 20px;'><select name='pwaplusphp_public_only'>";
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
        echo "</td></tr>\n";
        echo "<tr><td colspan=2><i>This option allows you to specify whether to display only public or both public and private albums of the user specified above. </i></td></tr>";
#
# ALBUM DETAILS
#
	echo "<tr><td style='padding-top: 20px; width: 200px;'><strong>Album Details</strong></td><td style='padding-top: 20px;'><select name='pwaplusphp_album_details'>";
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
        echo "</td></tr>\n";
        echo "<tr><td colspan=2><i>Setting this to TRUE will overlay the album details on the gallery thumbnails when the user hovers the mouse over the image.</i></td></tr>";
	# -------
	echo "<tr><td style='padding-top: 20px; width: 200px;'><strong>Truncate Album Names</strong></td><td style='padding-top: 20px;'><select name='pwaplusphp_truncate_names'>";
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
        echo "</td></tr>\n";
	echo "<tr><td colspan=2><i>Album names are truncated by default on the gallery page to ensure the div-based layout displays properly.  Set this to FALSE to show the full album name.</i></td></tr>";
	# -------
        echo "<tr><td style='padding-top: 20px; width: 200px;'><strong>Show Drop Box</strong></td><td style='padding-top: 20px;'><select name='pwaplusphp_show_dropbox'>";
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
        echo "</td></tr>\n";
	echo "<tr><td colspan=2><i>The Drop Box is a special Picasa album that stores images uploaded by email. <a target='_BLANK' href='http://picasa.google.com/support/bin/answer.py?hl=en&answer=73970'>More info here</a>.  Setting this variable to TRUE forces diplay of the drop box on all pages, even when a filter is specified.</i></td></tr>";
	# -------
	echo "<tr><td style='padding-top: 20px; width: 200px;'><strong>Show Footer Message</strong></td><td style='padding-top: 20px;'><select name='pwaplusphp_show_footer'>";
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
        echo "</td></tr>\n";
        echo "<tr><td colspan=2><i>Setting this to true allows PWA+PHP to display a 'generated by' message at the bottom of the page, so other users can learn about it.</i></td></tr>";
	# -------
	# Disabled for WordPress initial release - not tested
	#
        #echo "<tr><td style='padding-top: 20px; width: 200px;'><strong>Permit Image Download</strong></td><td style='padding-top: 20px;'><select name='pwaplusphp_permit_download'>";
	#if ($PERMIT_IMG_DOWNLOAD == "FALSE") {
        #        $download_true = "";
        #        $download_false= "selected";
        #} else {
        #        $download_true = "selected";
        #        $download_false= "";
        #}
        #echo "<option value='TRUE' $download_true>TRUE</option>";
        #echo "<option value='FALSE' $download_false>FALSE</option>";
        #echo "</select>\n";
        #echo "</td></tr>\n";
        #echo "<tr><td colspan=2><i>This option determines whether the user can download the original full-size image by clicking on the caption under the photo. Set to TRUE to enable and FALSE to disable.</i></td></tr>";
	# -------
        #echo "<tr><td style='padding-top: 20px; width: 200px;'><strong>Check For Updates</strong></td><td style='padding-top: 20px;'><select name='pwaplusphp_check_updates'>";
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
        #echo "<tr><td colspan=2><i>When TRUE, the script will check the server once per month and print a small message at the bottom of the page if a newer version of the code is available.  Set to FALSE to completely disable update checks.</i></td></tr>";
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

	$THIS_VERSION = "0.2";

	update_option("pwaplusphp_picasa_username", $_POST['pwaplusphp_picasa_username']);
	update_option("pwaplusphp_image_size",$_POST['pwaplusphp_image_size']);
	update_option("pwaplusphp_thumbnail_size",$_POST['pwaplusphp_thumbnail_size']);	
	update_option("pwaplusphp_require_filter",$_POST['pwaplusphp_require_filter']);
	update_option("pwaplusphp_images_per_page",$_POST['pwaplusphp_images_per_page']);
	update_option("pwaplusphp_public_only",$_POST['pwaplusphp_public_only']);
	update_option("pwaplusphp_album_details",$_POST['pwaplusphp_album_details']);
	#update_option("pwaplusphp_updates",$_POST['pwaplusphp_check_updates']);
	update_option("pwaplusphp_show_dropbox",$_POST['pwaplusphp_show_dropbox']);
	update_option("pwaplusphp_truncate_names",$_POST['pwaplusphp_truncate_names']);
	update_option("pwaplusphp_version",$THIS_VERSION);
	update_option("pwaplusphp_language",$_POST['pwaplusphp_language']);
	#update_option("pwaplusphp_permit_download",$_POST['pwaplusphp_permit_download']);
	update_option("pwaplusphp_show_footer",$_POST['pwaplusphp_show_footer']);

	
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


