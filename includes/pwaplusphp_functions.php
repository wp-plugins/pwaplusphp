<?PHP

if (!function_exists("setupCaption")) {
function setupCaption($caption,$lightbox,$count) {

        if ($lightbox == "HIGHSLIDE") {
                $return = "onclick=\"return hs.expand(this, { captionText: '$caption' } )\" alt='$caption' title='$caption'";
        } else {
                $return = "alt=\"$caption\" title=\"$caption\"";
        }
        return($return);
}
}

if (!function_exists("check_for_updates")) {
function check_for_updates($my_version) {

        $version = file_get_contents('http://pwaplusphp.smccandl.net/wp-pro-ver.html');
        if ($version !== false) {
                $version=trim($version);
                if ($version > $my_version) {
                        return("<table><tr class='plugin-update-tr'><td class='plugin-update'><div class='update-message'>New Version Available.  <a href='http://code.google.com/p/pwaplusphp/downloads/list'>Get v$version!</a></div></td></tr></table>");
                } else {
                        return("Thanks for your donation!");
                }
        } else {
                # We had an error, fake a high version number so no message is printed.
                $version = "9999";
        }

}
}

if (!function_exists("buildDownloadDiv")) {
function buildDownloadDiv($filename,$orig_href,$style="NULL") {
        if ($style == "NULL") {
                $result  = "<div class='pwaplusphp_download'>\n";
        } else {
                $result  = "<span style='$style'>\n";
        }

        $result .= "\t<a rel='nobox' 'Save $filename' title='Save $filename' href='$orig_href'>\n";
        $result .= "\t<img border=0 style='padding-left: 5px;' src='" . WP_PLUGIN_URL . "/pwaplusphp/images/disk_bw.png' /></a>\n";

        if ($style == "NULL") {
                $result  .= "</div>\n";
        } else {
                $result  .= "</span>\n";
        }
        return($result);
}
}

if (!function_exists("isProActive")) {
function isProActive() {
	if (function_exists("pwaplusphp_pro_validateCachePerms")) {
        	return("TRUE");
	} else {
        	return("FALSE");	
	}
}
}

?>
