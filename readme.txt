=== ALO EasyMail Newsletter ===
Contributors: eventualo
Donate link: http://www.eventualo.net/blog/wp-alo-easymail-newsletter/
Tags: send, mail, newsletter, widget, subscription, mailing list, subscribe, cron, batch sending, mail throttling, signup, multilanguage
Requires at least: 3.0
Tested up to: 3.2
Stable tag: 2.0.3

To send newsletters. Features: collect subcribers on registration or with an ajax widget, mailing lists, cron batch sending, multilanguage.

== Description ==

ALO EasyMail Newsletter is a plugin for WordPress that allows to write and send newsletters, and to gather and manage the subscribers. It supports internationalization and multilanguage.

**Here you are a short screencast:** [How to create and send a newsletter](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-guide/)

Plugin links: [homepage](http://www.eventualo.net/blog/wp-alo-easymail-newsletter/) | [guide](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-guide/) | [faq](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/) | [for developers](http://www.eventualo.net/blog/easymail-newsletter-for-developers/) | [forum](http://www.eventualo.net/forum/) | [news](http://www.eventualo.net/blog/category/alo-easymail-newsletter/)

**Before upgrading from v.1 to v.2, [read this info](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/#faq-upgrade-2)**

**Features:**

* **write and send html/text newsletters, simple like writing posts**
* **select the recipients to send to**: registered users, subscribers, mailing lists
* **batch sending using WP cron system**: it sends a number of emails every 10 minutes, until all recipients have been included
* **collect subscribers**: on registration form and with an ajax widget/page
* **import/export subscribers**: import from existing registered users or from a CSV file
* **create and manage mailing lists**: only admin can assign subscribers to them, or subscribers can freely choose them
* **manage subscribers**: search, delete, edit subscription to mailing lists 
* **manage capabilities**: choose the roles that can send newsletter, manage subscribers and settings
* **view sending report**: how many subscribers have opened the newsletter
* **multilanguage**: set all texts and options, you can write multilanguage newsletters - full integration with [qTranslate](http://wordpress.org/extend/plugins/qtranslate/)
* **debug tool**: rather than the recipients, you can send all emails of a newsletter to the author or you can have them recorded into a log file

Improvements in **v.2** over v.1:

* now newsletter is a custom post type, using the standard WordPress GUI and API
* no more need to hack WordPress core files or php/cron timeouts
* no more multiple or missing sendings to recipients
* now you can send to a huge number or recipients: it uses a ajax long polling engine to create recipient list
* some action and filter hooks useful for developer

**Internationalization**

Available languages:

* Brazilian v.1.8.7 - pt_BR (by Rodolfo Buaiz)
* Dutch v.1.8.3 - nl_NL (by Marius Gunu Siroen, Arnoud Huberts)
* English v.2.0.3 (by Francesca Bovone)
* Farsi v.1.8.4 - fa_IR (by Ka1 Bashiri)
* French v.1.8.4 - fr_FR (by Dominique Corbex, Eric Savalli, Nicolas Trubert)
* German v.2.0.1 - de_DE (by Norman Schlorke, Thomas Kokusnuss)
* Hungarian v.1.8.4 - hu_HU (by [Tamas Koos](http://www.asicu.com), Daniel Bozo)
* Italian v.2.0.3 - it_IT (by eventualo)
* Polish v.1.7 - pl_PL (by [Danny D](http://www.ddfoto.pl))
* Portuguese v.1.8.7 - pt_PT (by Alexandre de Menezes)
* Romanian v.1.8.4 - ro_RO (by Richard Vencu)
* Spanish v.1.8.7 - es_ES (by Mauro Macchiaroli)

You can add or update the translation in your language. You can send [gettext PO and MO files](http://codex.wordpress.org/Translating_WordPress) to me so that I can bundle it into the plugin. You can download [the latest POT file from here](http://svn.wp-plugins.org/alo-easymail/trunk/languages/alo-easymail.pot) and existing language files [from here](http://svn.wp-plugins.org/alo-easymail/trunk/languages/).

== Installation ==

= INSTALLATION =
1. Upload `alo-easymail` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the `Plugins` menu in WordPress
1. (If you are **upgrading** an EasyMail previous version, be sure to **upload all files** and to **activate the plugin again**)
1. (If you are upgrading from **1.x to 2.x**, make a backup of plugin db tables)

= QUICK START =
1. Go to `Appearance > Widget` to add subscription widget
1. Go to `Newsletters > Add new` to write a newsletter
1. Go to `Newsletters > Newsletters` to create recipient list and start newsletter sending

= MORE OPTIONS =
1. Go to `Newsletters > Settings` to setup options
1. Go to `Newsletters > Subscribers` to manage subscribers

Plugin links: [homepage](http://www.eventualo.net/blog/wp-alo-easymail-newsletter/) | [guide](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-guide/) | [faq](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/) | [for developers](http://www.eventualo.net/blog/easymail-newsletter-for-developers/) | [forum](http://www.eventualo.net/forum/) | [news](http://www.eventualo.net/blog/category/alo-easymail-newsletter/)

== Frequently Asked Questions ==

Plugin links: [homepage](http://www.eventualo.net/blog/wp-alo-easymail-newsletter/) | [guide](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-guide/) | [faq](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/) | [for developers](http://www.eventualo.net/blog/easymail-newsletter-for-developers/) | [forum](http://www.eventualo.net/forum/) | [news](http://www.eventualo.net/blog/category/alo-easymail-newsletter/)

== Screenshots ==

1. The subscription option on registration form
2. The widget for registered (left side) and not-registered (right side) users
3. You can add recipients to sending queue or you can send newsletter immediately
4. The ajax engine to generate list of recipients
5. The widget on administration dashboard 

== Changelog ==

= 2.0.3 =
* Fixed: now loading "registration.php", it solves a bug about creation of recipients' list and batch sending
* Fixed: "load_plugin_textdomain" now runs properly before "register_post_type"
* New css in Settings screen, more like WP style (thanks to iwan!)

= 2.0.2 =
* Added: option to load only plugin js on creation list of recipients thickbox
* Fixed: some bugs

= 2.0.1 =
* Fixed: some bugs

= 2.0 =
* Re-written the code about creation, editing and sending of newsletters
* Now Newsletter is custom post type
* Ajax long polling engine to create list of recipients
* New database plugin tables to decrease memory usage
* Solved the bug about sending to a large numeber or recipients (multiple/missing sendings)
* Added action and filter hooks, useful for developers

= 1.8.7 =
* Added: some css samples in alo-easymail.css
* Added: a couple of options to use or not text filters and shortcodes in newsletter text
* Added: when a user changes email or name, the subscription is updated too
* Added: css classes in form for registered and not-registered users
* Fixed: add-link thickbox in editor on WP 3.1
* Fixed: when plugin re-activated, no update of subscription page's texts
* Fixed: users with only "send_easymail_newsletter" capability view properly the sending page
* Fixed: now admin-bar works and newsletter submenu depends on user role
* Fixed: deleted size attributes from form inputs

= 1.8.6 =
* NEW FEATURES
* Direct subscription without activation e-mail now available
* Time interval between emails of same batch
* Debug newsletters: send all emails to author or write them into a log file
* MINOR CHANGES
* Fixed: ALO_em_get_recipients_registered() gets properly members (definitely, I hope)
* Fixed: checkboxes properly work in Settings
* Added: "open in a new window" button in report thickbox
* Added: alert and help about timeout to increase

= 1.8.5 =
* NEW FEATURES
* Customisation of available languages
* New options on importation: lists, languages
* Policy claim at widget/page bottom
* MINOR CHANGES
* Added: newsletter menu in Admin bar (WP 3.1)
* Added: [POST-TITLE] shows the post title in reports
* Added: css classes and ids in forms
* Added: contextual help and credit banners in back-end
* Fixed: now compatible with WP_CONTENT_URL and WP_PLUGIN_DIR
* Fixed: ALO_em_get_recipients_registered() gets properly members
* Fixed: custom English texts should work when English is the only available language

= 1.8.4 =
* NEW FEATURES
* Native multilanguage functionality in back-end and front-end
* Full integration with qTranslate multilanguage plugin
* MINOR CHANGES
* Added: "post-title" tag now works in newsletter subject
* Added: sender's name option 
* Added: activation edit bulk action in subscriber manage page
* Updated: no file extension check on csv importation
* Updated: the subscription page is deleted only if complete uninstall is required
* Fixed: registered user importation on multisite (thanks to RavanH)
* FIxed: "user-name" and "user-first-name" tags now should work properly

= 1.8.3 =
* Fixed: the newsletter content-type
* Fixed: some escape/stripslashes

= 1.8.2 =
* NEW FEATURES
* Added: newsletter templates
* Added: embed css file for styling plugin
* Added: subscribers exportation
* MINOR CHANGES
* Added: an option about max sendings per batch
* Added: an option to choose the subscription page
* Fixed: newsletter datetimes now use GMT blog datetime
* Fixed: alert on subscription if email address is already subscribed
* Fixed: remove -br- in content when rendering an html table
* Fixed: all plugin paths and urls
* Updated: now newsletter transfer encoding is 8bit
* Updated: send multipart newsletters (html and text) to make them less spamish
* Updated: new tab layout on sending page

= 1.8.1 =
* Fixed: the "updating..." msg should not get stuck anymore
* Fixed: in subscription page the list form appears only if there is at least a list
* Fixed: the csv importation system should work better
* The subscription page generated on installation now is titled simply "Newsletter"

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

= 1.8.1 =
Release to fix some bugs.

= 1.8.2 =
New features: templates, subscribers exportation, a lot of improvements.

= 1.8.3 =
Release to fix some bugs.

= 1.8.4 =
New features: multilanguage, integration with qTranslate plugin.

= 1.8.5 =
Some new features and bug fixes.

= 1.8.6 =
Some new features and bug fixes.

= 1.8.7 =
Some new features and bug fixes.

= 2.0 =
Re-written the code about newsletters. Solved the bug about sending. If you are upgrading from 1.x, before start make a backup of plugin db tables.

= 2.0.3 =
Fixed some important bugs.
