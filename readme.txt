=== Plugin Name ===
Contributors: smccandl 
Donate link: http://pwaplusphp.smccandl.net/
Tags: picasa web albums, picasa, pwa, lightbox, fancybox, shadowbox
Requires at least: 2.9.1
Tested up to: 2.9.1
Stable tag: trunk

PWA+PHP allows you to display public and private (unlisted) Picasa albums within WordPress in your language using Fancybox, Shadowbox or Lightbox.

== Description ==

[PWA+PHP](http://pwaplusphp.smccandl.net) is a lightweight solution for displaying your private (unlisted) and public Picasa Albums within Wordpress in your language using Fancybox, Shadowbox or Lightbox. The plugin provides a guided installer that helps you generate your gdata token and set display options for your albums. 

PWA+PHP extends the capabilites of Picasa albums, allowing you to [group albums by keywords](http://code.google.com/p/pwaplusphp/wiki/Album_Filtering) in the title.  Using this capability, you can create several photo pages in WordPress and show different groups of albums on each page.  You can then password protect those pages so that certain people can only see certain albums.

PWA+PHP's configuration options allow you to customize the look and feel of your albums, including thumbnail and image size, images per page and display language, without modifying any code. The included CSS file can also be tweaked to your liking for an exact match with your existing website.  The div-based layout is fluid and adjusts automatically to fit your theme.

Check out [the demo](http://pwaplusphp.smccandl.net/pwademo.php) to see the code in action.

== Installation ==

1. Extract the archive within the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Open the Settings section and click on PWA+PHP
1. Complete the setup (token generation and options)
1. Create a new page with contents `[pwaplusphp]` or `[pwaplusphp album="album_name"]` or `[pwaplusphp album="random_photo"]`
1. Check out the [full guide](http://code.google.com/p/pwaplusphp/wiki/WordPressPluginHelp) and [report bugs](http://code.google.com/p/pwaplusphp/issues/entry?template=Defect%20report%20from%20user) as necessary.

== Frequently Asked Questions ==

= What if I don't want to display private (unlisted) albums? =

After the token generation step, simply set the "Public Albums Only" option to TRUE.

= How do I filter albums? =

See [our wiki](http://code.google.com/p/pwaplusphp/w/list)

== Screenshots ==

1. Main gallery view, description on mouse over 
2. View of images in album
3. Full-size image in a Shadowbox
4. Settings page

== Changelog ==

= 0.2 =
* Alpha version - initial release.

== Upgrade Notice ==

= 0.2 =
* Initial release.

== Features ==

* Embed all your public, private and unlisted Picasa web albums on any website
* Group and filter albums [using keywords](http://code.google.com/p/pwaplusphp/wiki/Album_Filtering) in the album title
* Uploaded images via email, web interface or desktop app and see them instantly
* Display image totals for the entire gallery and by album
* Install script guides setup and token generation for private albums
* Interface configuration via 16 variables (image size, thumbnail size, pagination, etc)
* Modify included CSS file to match your site exactly
* Available in 7 languages and extensible to others!
