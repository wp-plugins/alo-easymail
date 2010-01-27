=== ALO EasyMail Newsletter ===
Contributors: eventualo
Tags: send, mail, newsletter, widget, subscription, mailing list, subscribe
Requires at least: 2.8.4
Tested up to: 2.9.1
Stable tag: 1.5

To send e-mails and newsletters to your subscribers, to registered users and to other e-mail addresses. Includes a widget to collect subscribers.

== Description ==

With this plugin you can send e-mails and newsletters to your subscribers, to registered users and to other e-mail addresses.
The plugin includes a widget (1) to allow subscription: your registered users can subscribe/unsubscribe using a widget or profile page (1); not-registered users can subscribe using the widget.
You have an admin page to manage subscribers (search, activate, delete).
The blog admin (or editors too, if you setup option (1)) can send newsletters inserting some post/user tags in the content: post title, post excerpt, post content, site link, user first name (1), user name.
You can save an e-mail template and the e-mail addresses for next sending.

(1) added by GREG LAMBERT (greg4@mskiana.com). See `Changelog` for full details. Many thanks!

== Installation ==

= INSTALLATION =
1. Upload `alo-easymail` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the `Plugins` menu in WordPress

= USAGE =
1. Go to `Option > Alo EasyMail` to setup
1. Go to `Appearance > Widget` to add subscription widget
1. Go to `Tools > Alo EasyMail` to send newsletter
1. Go to `Users > Alo EasyMail` to manage subscribers

== Frequently Asked Questions ==

= Can I send e-mail/newsletter to...? =

You can send newletter to your subcribers, to your registered users, to other email addresses you provide.

= How can I receive subscriptions? =

Use the ALO EasyMail widget (`Appearance > Widget`) to let pubblic users to subscribe your newsletter.

= What about subscription/unsubscription procedures? =

The registered users can use a widget to subscribe/unsubscribe. They can do it in profile page too.
The new subscriber can insert name and email address in widget, then he'll receive an email with an activation link and he have to click this link to activate his subscription (the new subscribers will be deleted if don't activate in 4 days). To unsubscribe: at the end of newsletters there is always a link to unsubscribe.

== Screenshots ==

1. A section of the EasyMail panel
2. The subscribers' management page
3. The EasyMail widget for registered (left side) and not-registered (right side) users

== Changelog ==

= 1.5 =
* Added: not-registered visitors can subscribe the newsletter
* Updated: Greg's widget (see 0.9.3 Reloaded) to collect pubblic subscriptions 
* Added: a admin page to manage subscribers
* Added: the result page opens in a popup with report of each recipients
* Added: a [POST-CONTENT] tag 

= 0.9.3 Reloaded by GREG LAMBERT (greg4@mskiana.com) =
* THANKS to Greg for the following important features:
* Added: widget to manage user subscription
* Added: optin/out option to the user's profile 
* Added an option to allow editors to send email 
* Added: a [USER-FIRST-NAME] tag
* Fixed: a couple of warning messages where indexes were not defined
* Tested on WP 2.9.1

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

== Upgrade Notice ==

= 1.5 =
Very important release with new features.


