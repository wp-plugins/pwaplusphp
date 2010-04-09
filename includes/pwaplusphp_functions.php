<?PHP

function setupCaption($caption,$lightbox,$count) {

        if ($lightbox == "HIGHSLIDE") {
                $return = "onclick=\"return hs.expand(this, { captionText: '$caption' } )\" alt='$caption' title='$caption'";
        } else {
                $return = "alt='$caption' title='$caption'";
        }
        return($return);
}

function buildDownloadDiv($filename,$orig_href,$style) {
        $result  = "<span style='$style'>\n";
        $result .= "<a rel='nobox' 'Save $filename' title='Save $filename' href='$orig_href'>\n";
        $result .= "<img border=0 style='padding-left: 5px;' src='" . WP_PLUGIN_URL . "/pwaplusphp/images/disk_bw.png' />\n";
        $result .= "</a></span>\n";
        return($result);
}

?>
