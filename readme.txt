=== ALO EasyMail Newsletter ===
Contributors: eventualo
Donate link: http://www.eventualo.net/blog/wp-alo-easymail-newsletter/
Tags: send, mail, newsletter, widget, subscription, mailing list, subscribe, cron, batch, mail throttling
Requires at least: 2.8.4
Tested up to: 3.0-beta2  
Stable tag: 1.7

To send e-mails and newsletters. Including an ajax widget and a page to collect subscribers. Using a cron batch sending.

== Description ==

ALO EasyMail Newsletter is a plugin for WordPress that allows to write and send newsletters, and to gather and manage the subscribers. It supports internationalization.

**Admin side Features**

* Administrator users (and, if previously set, also editor users) can use a page to **send newsletters** very quickly and choosing recipients, subject and main text.
* **Recipients** can be: registered users, public subscribers (i.e. non registered users) and any other email addresses chosen by the administrator.
* You may choose one of the latest published articles and use **some tags** to enter information about it: title, excerpt, content. 
* Tags for recipients (username, first name) and for the blog (link to the home page) are also available.
* The plugin sends the newsletter by means of the **WordPress cron system**, so as to send a number of emails every 10 minutes, until all recipients have been included. This system allows to send an enormous number of emails without overloading the server or going beyond the limits set by providers. 
* At the end of the sending the plugin provides a **report** of the outcome of the sending to each recipient.
* Administrator users also have a page to **manage subscribers** (search, activate/disactivate, cancel) and another one for the plugin general options.

**Pubblic side Features**

* To manage subscriptions to the newsletters the plugin uses a **widget (in ajax)** and a **page** that behaves differently for registered and non-registered users.
* **Registered users** can subscribe/unsubscribe through an option that can be found in the page, in the widget and in their user profile.
* **Public (non-registered) users** can use a simple form (name and email) in order to subscribe; the form is in the widget and in the page. Safter sending their data, they will receive an email with an activation link. To confirm their subscription they just have to click the link.
* In order to unsubscribe, the users can simply click the link they find at the bottom of every newsletter.

**Internationalization**

Available languages:

* English
* Italian

You can add or update the translation in your language. You can send [gettext PO and MO files](http://codex.wordpress.org/Translating_WordPress) to me so that I can bundle it into the plugin. You can download [the latest POT file from here](http://svn.wp-plugins.org/alo-easymail/trunk/languages/alo-easymail.pot).

**IMPORTANT NOTE** - Some of the latest WP versions have a known bug in the wp_cron system (a WP bug, not an EasyMail bug). The latest EasyMail versions (v.1.6.x and v.1.7.x) WORK on WP 2.9.1 and 3.0-beta2. They probably DON'T work on WP 2.9 and 2.9.2. (It seems to work with WP 2.8.x series, but I advise to upgrade). More info on [plugin FAQ page](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/).

== Installation ==

= INSTALLATION =
1. Upload `alo-easymail` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the `Plugins` menu in WordPress
1. (If you are upgrading an EasyMail previous version, be sure to upload all files and to activate the plugin again)

= QUICK START =
1. Go to `Appearance > Widget` to add subscription widget
1. Go to `Tools > EasyMail Newsletter` to send newsletter

= MORE OPTIONS =
1. Go to `Option > EasyMail Newsletter` to setup options
1. Go to `Users > Subscribers` to manage subscribers

== Frequently Asked Questions ==

On [plugin homepage](http://www.eventualo.net/blog/wp-alo-easymail-newsletter/) you can find: 
[the guide](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-guide/), 
[the FAQ](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/), 
[the forum](http://www.eventualo.net/forum/forum/1).

== Screenshots ==

1. A section of the sending panel
2. The subscribers' management page
3. The EasyMail widget for registered (left side) and not-registered (right side) users
4. The list of newsletters in queue and already sent

== Changelog ==

= 1.7 =
* NEW FEATURES
* Added: internationalization (with .mo and .po files)
* MINOR CHANGES
* Added: tabs navigation in options page
* Fixed: forced collation on db tables installation
* Fixed: optin/optout texts move from widget options to main option page

= 1.6.1 =
* Fixed: now unsubscribe link is printed
* Added: the mail charset is not UTF-8 but the same of the blog

= 1.6 =
* NEW FEATURES
* Added: a configurable cron batch to send newsletter in scheduled sendings
* Added: a report with delivery summary for each newsletter
* Added: a bit of ajax in the widget
* Added: the subscription form in the newsletter page
* MINOR CHANGES
* Added: an option to choose sender email address
* Added: css classes to format texts, to be specified in theme css
* Added: a link to the post in the [POST-TITLE] tag
* Added: address list and tamplate are now usermeta and not blog option
* Fixed: WP is_email() function instead of ereg()
* Fixed: the unsubscribe link now uses 'home' option instead of 'siteurl' option

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

= 1.6 =
Now the plugin uses the wp_cron system. Please read about a known bug of the wp_cron of some WP versions.
