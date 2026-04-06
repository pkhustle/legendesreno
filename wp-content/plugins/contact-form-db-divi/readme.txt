===Contact Form DB Divi===
Contributors: themeythemes
Tags: divi, divi contact form db, divi contact form database, contact form database
Requires at least: 5.0
Tested up to: 6.2
Requires PHP: 5.6
Stable tag: 1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The Contact Form DB plugin is designed to provide an easy way to store and manage form submissions on your Divi website

== Description ==

The Contact Form DB Divi plugin is designed to provide an easy way to store and manage form submissions on your Divi website. The plugin stores all Divi contact form submissions in the WordPress database.

Want to get started? Check out our Getting Started article, "[Saving Form Submission in the Database](https://www.learnhowwp.com/save-divi-contact-form-submission-database/)", to learn how to start using the plugin and make the most of its features.

Once installed, the plugin creates a new Menu Item in the WordPress backend, called “Divi Form DB”.

When a user submits a form on your website (using a Divi form), the data from the form is automatically saved here in the “Divi Form DB” menu item. This makes it easy to keep track of all form submissions in one place and manage them using WordPress's built-in tools.

The plugin stores a range of data for each form submission, including all form submission values (i.e. the data submitted via the form), the page that the form was submitted on, the date and time that the form was submitted, and the date and time that the submission was read (if applicable).

**Free Version Features**

* Stores every submission in the database
* Ability to view submissions in the WordPress Dashboard
* Only supports specific form fields with field ID 'name', 'email', and 'message'.

**Pro Version Features**

Upgrade to the Pro version of Contact Form DB to unlock even more powerful features, including:

* Stores all the form fields in the database
* Export form submissions in CSV format
* Tracks the page on which the form was submitted
* Records the date and time of each form submission
* Tracks when the submission was "read"

Upgrade to the Pro Version for only **$9** to take advantage of all these additional features and more!

Don't miss out – [Upgrade for only $9 Today!](https://www.learnhowwp.com/divi-contact-form-db/)

Looking for more information on the plugin or want to buy it on my website? Check out [Divi Contact Form DB](https://www.learnhowwp.com/divi-contact-form-db/) page on our website for more information.

Or, buy the plugin on [Divi Marketplace](https://www.elegantthemes.com/marketplace/divi-contact-form-db/), a trusted marketplace for Divi-related products.

https://www.youtube.com/watch?v=02jkCpG1kXA

**Other Free Divi Plugins**
[Divi Overlay on Images Module](https://wordpress.org/plugins/overlay-image-divi-module/)
[Divi Post Carousel Module](https://wordpress.org/plugins/post-carousel-divi/)
[Divi Menu Cart Module](https://wordpress.org/plugins/menu-cart-divi/)
[Divi Flip Cards Module](https://wordpress.org/plugins/flip-cards-module-divi/)
[Divi Image Carousel](https://wordpress.org/plugins/image-carousel-divi/)
[Divi Breadcrumbs Module](https://wordpress.org/plugins/breadcrumbs-divi-module/)

If you have any questions or feature ideas please create a new thread in Support.

== Installation ==
1. Upload the plugin `.zip` file to the `/wp-content/plugins/` directory
2. Activate the plugin through the `Plugins` page in your `WordPress Dashboard`.

== Frequently Asked Questions ==

= Where can I access the form submissions? =
After you activate the plugin, a new menu item will appear in the WordPress Dashboard by the name of "Divi Form DB."

= What is the difference between Free and Pro versions? =
The free version of the plugin offers basic form submission and supports only specific form fields with Field IDs 'name', 'email', and 'message', which are the default fields in a Divi Contact Form. Other custom fields in the form will not be saved with the free version.

= How can I view the form submission? =
You can easily view any form submission by simply clicking the "View Form Submission" link on the Divi Form DB page.

= Why can't I see all the fields in Form Submission? =
The free version of the plugin supports only specific fields with Field IDs 'name', 'email', and 'message'. To view and save all form fields, please upgrade to the Pro version. If you are already using the Pro version of the plugin and still facing issues, please create a support request through the plugin settings.

= Can I use Contact Form DB with non-Divi forms on my website? =
Unfortunately, the Contact Form DB plugin is only compatible with forms created using Divi. It does not support other form plugins or custom-built forms.

= How many form submissions can the Contact Form DB plugin store? =
The Contact Form DB plugin can store an unlimited number of form submissions in your WordPress database.

= Can I export form submissions from Contact Form DB? =
Starting from version 1.1 and above of the premium version, you can now export form submissions directly within the plugin.

= Is there an option to export in a format other than CSV? =
No, currently there is no alternative export format available. The plugin supports CSV format exclusively for exporting form submissions.

= How do I upgrade to the Pro version of Contact Form DB? =
To upgrade to the Pro version of the plugin, simply navigate to the upgrade page in the plugin settings and follow the instructions provided.

= Can I customize the data fields stored by the Contact Form DB plugin? =
In the Pro version of the Contact Form DB plugin, all form fields are automatically stored in the database. There is no need for any customization or configuration.

= Is Contact Form DB GDPR compliant? =
The plugin is designed to store form submissions in the WordPress database and does not send data to external websites. However, it is the responsibility of the website owner to ensure that their website and any plugins used, including Contact Form DB, are compliant with GDPR regulations. We recommend consulting with a legal professional or GDPR expert to ensure compliance.

= How do I delete old or unnecessary form submissions from the Contact Form DB database? =
To delete old or unnecessary form submissions from the Contact Form DB database, simply locate the submission you wish to delete and click the "Trash" link next to it. This will move the submission to the trash, where it will be permanently deleted after 30 days (or sooner if you empty the trash manually).

== Changelog ==

= 1.2 =
* Added read status column on the contact form submission list page
* Added unread form submissions count to menu item
* Refactored the storage mechanism for the contact form's read status, read date and page ID now storing them as a separate meta key field in the database
* Update version of Freemius SDK

= 1.1 =
* Implemented the display of the contact form's unique ID field within the contact form module
* Refactored the storage mechanism for the contact form's unique ID, now storing it as a separate meta key field in the database
* Introduced an export function exclusively available in the premium version of the plugin

= 1.0.1 =
* Fixed issue where empty entries were created when there was an error on contact form submission
* Fixed issue where warning message was shown if data was not defined on the entries page

= 1.0 =
* First Release