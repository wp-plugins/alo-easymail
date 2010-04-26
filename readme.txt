=== ALO EasyMail Newsletter ===
Contributors: eventualo
Donate link: http://www.eventualo.net/blog/wp-alo-easymail-newsletter/
Tags: send, mail, newsletter, widget, subscription, mailing list, subscribe, cron, batch, mail throttling
Requires at least: 2.8.4
Tested up to: 3.0-beta1  
Stable tag: 1.6

To send e-mails and newsletters. Including an ajax widget and a page to collect subscribers. Using a cron batch sending.

== Description ==

ALO EasyMail newsletter lets you write and send newsletters, collect and manage subcribers. 

**Admin side Features**

* The administrator users (and, if set, even the editor users) have access to a page to **send the newsletter** very quickly by choosing: recipient, subject, main text.
* The **recipients** are: the registered users, public subscribers (not registered users), other e-mail addresses entered by the administrator.
* You can select a published post and use **some tags** to enter information in the text of the newsletter: title, excerpt, content.
* Tags are also available for recipients (username, firstname) and for the blog (link to homepage).
* To send the newsletter the plugin uses the **WP cron system**: it makes partial sendings at small time intervals (10 minutes) until it sends to all recipients. It allows you to send a large amount of email without overloading the server or exceed the limits imposed by the provide.
* After the sending is available a **report** with the outcome of transmission to each recipient.
* Administrator users have access to a page for **managing subscribers** (search, enable/disable, delete) and the general options for the plugin.

**Pubblic side Features**

* To manage the subscriptions the plugin uses **a widget plugin (ajax)** and a dedicated page for registered and unregistered users.
* Registered users can subscribe / unsubscribe through an option on the dedicated page, in the widget and in their profile page.
* Instead, public visitors (not registered users) use a simple form (name, email) to register in the widget and the dedicated page. After sending their data the new subscribers will receive an activation email with a link to click to confirm the subscription. To unsubscribe the subscribers can click on the link attached at the bottom of every newsletter.

**IMPORTANT NOTE:** there is a BUG in the cron of some WP last versions and the plugin could not work on them. See See `Faq` for details.

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

= Why won't my newsletter be sent? (Problem with cron batch sending) =
Maybe you are using a WP version with a known BUG in the wp_cron system (a WP bug, not an EasyMail bug). The EasyMail v.1.6 (the first with cron batch sending) **works** on WP 2.9.1 and 3.0-beta1. It **DOESN'T work** on WP 2.9 and 2.9.2. (It seems to work with WP 2.8.x series, but I've not tested it enough and however I advise to upgrade). Maybe you could try to run it on these WP versions (for details read [here](http://wordpress.org/support/topic/343174) and [here](http://wordpress.org/support/topic/296236?replies=13#post-1175405)) or try and install another WP version. 

= What about subscription/unsubscription procedures? =
Registered users can subscribe/unsubscribe either using a widget or through their profile page.
If you are a new subscriber you can insert your name and email address in widget: you'll receive an email with an activation link; click on this link to activate your subscription (the new subscribers will be deleted if they don't activate the subscription in 5 days). To unsubscribe: at the end of newsletters there is always a link to unsubscribe.

= Why don't I see the subscription form in thw widget, but yes/no radio buttons ? =
The widget shows different contents according to whether you are logged in or not. If you are registered and logged in (as admin too) the widget shows radio buttons (yes/no). If you are not registered or not logged in, the widget shows the subscription form (name, email) and the submit button.

= Can I modify the style of the subscription form? =
Yes, you can use some available css id and class.
About the form:

`#alo_easymail_widget_form { /* the form */`
`	color:#000;`
`}`
`.alo_easymail_widget_error { /* error msg */`
`	color:#f00;`
`}`
`.alo_easymail_widget_ok { /* success msg */`
`	color:#0f0;`
`}`
Then, the form is included in the widget and in the *Newsletter subscription* page. If you want to use different styles, use the id of the container div that include form: for the page the id is always *alo_easymail_page*, for the widget is something like *alo-easymail-widget-[n]* where [n] is the widget number.

= Wonderful plugin! / I'm using it in all my sites! / It would be great if it also had this feature...  =
You can support me by donating some money.

= And...? =
For more info, please visit [plugin homepage](http://www.eventualo.net/blog/wp-alo-easymail-newsletter/).

== Screenshots ==

1. A section of the sending panel
2. The subscribers' management page
3. The EasyMail widget for registered (left side) and not-registered (right side) users
4. The list of newsletters in queue and already sent

== Changelog ==

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


