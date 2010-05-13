<?PHP

function setupCaption($caption,$lightbox,$count) {

        if ($lightbox == "HIGHSLIDE") {
                $return = "onclick=\"return hs.expand(this, { captionText: '$caption' } )\" alt='$caption' title='$caption'";
        } else {
                $return = "alt=\"$caption\" title=\"$caption\"";
        }
        return($return);
}

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

?>
