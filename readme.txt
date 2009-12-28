=== ALO EasyMail ===
Contributors: eventualo
Tags: send, mail, newsletter
Requires at least: 2.6.2
Tested up to: 2.9
Stable tag: 0.9.3

A simple plugin to send e-mails and newsletters to your registered users and to other e-mail addresses.

== Description ==

The plugin lets you to send e-mails and newsletters, with these features:

* you can send to registered users and/or to other e-mail addresses
* you can use some tags in the e-mail content (post title, post excerpt, user name, site link)
* you can save an e-mail template and the e-mail addresses for future sendings

== Installation ==

1. Upload `alo-easymail` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the `Plugins` menu in WordPress
1. Go to `Tools > Alo EasyMail` to start

== Frequently Asked Questions ==

= Can I send e-mail to my registered users and/or other e-mail addresses? =

Sure, it's the only thing this plugin does.

== Screenshots ==

1. The EasyMail panel

== Changelog ==

= 0.9.3 =
* Fixed: delete line breaks in tag POST-EXCERPT
* Tested with WP 2.9

= 0.9.2 =
* Added: using wp_mail() function instead of mail()
* Added: saving emails' list for next sending

= 0.9.1 =
* Updated: tinymce's media buttons compatible with WP v.2.8.4
* Fixed: correct stripslashing when updating content in option page

= 0.9 =
* First release

