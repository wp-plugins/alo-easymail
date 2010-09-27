=== ALO EasyMail Newsletter ===
Contributors: eventualo
Donate link: http://www.eventualo.net/blog/wp-alo-easymail-newsletter/
Tags: send, mail, newsletter, widget, subscription, mailing list, subscribe, cron, batch sending, mail throttling, signup
Requires at least: 2.8.4
Tested up to: 3.0  
Stable tag: 1.8

To send e-mails and newsletters. Features: subscription on registration or with an ajax widget, mailing lists, cron batch sending.

== Description ==

ALO EasyMail Newsletter is a plugin for WordPress that allows to write and send newsletters, and to gather and manage the subscribers. It supports internationalization.

Plugin links: [homepage](http://www.eventualo.net/blog/wp-alo-easymail-newsletter/) | [guide](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-guide/) | [faq](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/) | [forum](http://www.eventualo.net/forum/forum/1) | [news](http://www.eventualo.net/blog/category/alo-easymail-newsletter/)


**Admin side Features**

* **write an html newsletter, choose a post, use post/subscribers/site tags and send to your recipients** (registered users, subscribers, mailing lists, any other email addresses)
* **batch sending using WP cron system**: it sends a number of emails every 10 minutes, until all recipients have been included
* **collect subscribers**: on registration form and with an ajax widget/page
* **import subscribers**: from existing registered users or from a CSV file
* **create and manage mailing lists**: only admin can assign subscribers to them, or subscribers can freely choose them
* **manage subscribers**: search, delete, edit subscription to mailing lists 
* **manage capabilities**: choose the roles that can send newsletter, manage subscribers and settings
* **view sending report**: how many subscribers have opened the newsletter

**Pubblic side Features**

* **subscription on registration form**: including mailing lists choice
* **subscription using an ajax widget**: after sending their data using the widget, they will receive an email with an activation link
* **handy for registered users**: they have the optin/optout (including mailing lists choice) in their profile page and in an ajax widget
* **easy subscription management for all subscribers**: to modify their subscription to mailing lists or to unsubscribe they can use a page reachable by a link at the bottom of each newsletter


**Internationalization**

Available languages:

* Brazilian Portuguese v.1.7 - pt_BR (by Douglas Venâncio Crispim)
* Dutch v.1.8 - nl_NL (by Marius Gunu Siroen, Arnoud Huberts)
* English v.1.8 (by Francesca Bovone)
* Farsi v.1.8 - fa_IR (by Ka1 Bashiri)
* French v.1.8 - fr_FR (by EricSvl, Nicolas Trubert, Therese Lachance)
* German v.1.8 - de_DE (by Thomas Kokusnuss)
* Hungarian v.1.8 - hu_HU (by Daniel Bozo)
* Italian v.1.8 - it_IT
* Polish v.1.7 - pl_PL (by [Danny D](http://www.ddfoto.pl))
* Romanian v.1.8 - ro_RO (by Richard Vencu)
* Spanish v.1.8 - es_ES (by Mauro Macchiaroli)

You can add or update the translation in your language. You can send [gettext PO and MO files](http://codex.wordpress.org/Translating_WordPress) to me so that I can bundle it into the plugin. You can download [the latest POT file from here](http://svn.wp-plugins.org/alo-easymail/trunk/languages/alo-easymail.pot) and existing language files [from here](http://svn.wp-plugins.org/alo-easymail/trunk/languages/).

IMPORTANT NOTE - *Some of the latest WP versions have a known bug in the wp_cron system (a WP bug, not an EasyMail bug). The EasyMail (v.1.7 and newer) properly WORKS on WP 3.0 and 2.9.1. It probably DOESN'T work on WP 2.9 and 2.9.2. (It seems to work with WP 2.8.x series, but I advise to upgrade). More info on [plugin FAQ page](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/).*

== Installation ==

= INSTALLATION =
1. Upload `alo-easymail` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the `Plugins` menu in WordPress
1. (If you are upgrading an EasyMail previous version, be sure to upload all files and to activate the plugin again)

= QUICK START =
1. Go to `Appearance > Widget` to add subscription widget
1. Go to `Tools > Send newsletter` to send newsletter

= MORE OPTIONS =
1. Go to `Option > Newsletter` to setup options
1. Go to `Users > Newsletter subscribers` to manage subscribers

More info on [plugin homepage](http://www.eventualo.net/blog/wp-alo-easymail-newsletter/) you can find: 
[the guide](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-guide/), 
[the FAQ](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/), 
[the forum](http://www.eventualo.net/forum/forum/1).

== Frequently Asked Questions ==

On [plugin homepage](http://www.eventualo.net/blog/wp-alo-easymail-newsletter/) you can find: 
[the guide](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-guide/), 
[the FAQ](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/), 
[the forum](http://www.eventualo.net/forum/forum/1).

== Screenshots ==

1. The subscription option on registration form
2. The widget for registered (left side) and not-registered (right side) users
3. A section of the sending panel
4. List of newsletters scheduled for sending and already sent
5. Report of sent newsletter
6. The subscribers' management page
7. The widget on administration dashboard 

== Changelog ==

= 1.8 =
* NEW FEATURES
* Added: mailing lists
* Added: subscription choice on registration form
* Added: tracking system when subscribers open newsletter
* Added: subscribers importation from existing members or from a csv file
* Added: use capabilities and not user_level, so better permission managing
* MINOR CHANGES
* Added: an option to show subscription page
* Added: dashboard widget and favorite menu link
* Updated: a better formatting in admin side
* Fixed: now admin can modify subscription on user profile page
* Fixed: now easymail page and its option are properly deleted on deactivation
* Fixed: encode entities in newsletter header and subject
* Fixed: a lot of php warnings and wp notices

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
New feature: now the plugin uses the wp_cron system. Please read about a known bug of the wp_cron of some WP versions.

= 1.7 =
Upgrade your WP installation to 3.0: the wp_cron bug seems to be solved. New feature: internationalization.

= 1.8 =
New features: mailing lists, subscribers importation, tracking system.
