=== Bookit — Booking & Appointment Calendar ===
Contributors: theeventscalendar, bordoni
Donate link: https://theeventscalendar.com/
Tags: booking calendar, appointment booking, appointment calendar, booking, calendar
Requires at least: 6.3
Requires PHP: 7.4
Tested up to: 6.9
Stable tag: 2.5.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Appointment booking and event calendar for WordPress. Services, staff, availability, shortcodes, and email notifications. Prevents double-booking.

== Description ==

Bookit adds an appointment booking system and event calendar to your WordPress site. You define services, staff, working hours, and prices; visitors pick a date and time and submit a booking. The plugin prevents double-booking and sends configurable email notifications.

**What you can do:**

* Define multiple services with duration and price; assign staff and set their working hours and days.
* Show availability in daily, weekly, or monthly calendar views.
* Place a booking form on any page via shortcode or with Elementor and WPBakery widgets.
* Let customers choose a service and staff, see available slots, and submit a booking (no account required unless you enable it).
* Receive and send email notifications for new, updated, or upcoming appointments; templates are editable in settings.
* Manage all appointments from the admin dashboard; optional Stripe payments in the free version.

Optional [BookIt Pro add-ons](https://bookitwp.com/bookit-pro/) add WooCommerce integration, Google Calendar sync, drag-and-drop custom fields, and additional payment options (e.g. PayPal). Documentation and support are available at [bookitwp.com](https://bookitwp.com/).

== Frequently Asked Questions ==

= How many booking forms can I create? =
You can create as many booking forms as you need. The plugin does not limit you allowing to create the desired number of calendars.

= Can I set up notifications? =
With BookIt you can set up custom notifications and adjust email templates. In the plugin settings, you are able to prepare the email notifications for various activities on the website such as new appointment submission, appointment update, and others. Add all the needed elements to create a perfect email template.

= Can I get free updates? =
Definitely. Once you installed the plugin, you can get free updates every time there is a new version released. Find more information on how to update BookIt in the plugin

= Do I need to have coding skills to use BookIt? =
Even if you are not familiar with coding, you can easily use our booking plugin. BookIt was developed for a wide range of users with different skills, an easy-to-use dashboard and a neat pack of settings make it very simple to navigate the plugin and create appointment calendars.

= What payment methods the plugin supports? =
The PRO version of the plugin supports several online payment methods. Among them PayPal and Stripe. Also, BookIt is fully integrated with the WooCommerce plugin, which also allows users to checkout via WooCommerce.

= Can I create different calendars for different services? =
You can create booking forms for any service. For more convenience, you can surely build separate calendars for the services you provide. This also will be more comfortable for your users.

= Is it possible to add a booking form to any page? =
Yes, you can insert the calendar into any page of your WordPress site. We have provided several ways for that: you can use either a unique shortcode, which can be easily generated for the calendar or use the page builders’ widgets. Both methods take minutes to complete the form integration.

= Found a security vulnerability? =

Make sure you are reporting in a safe and responsible way. We take security very seriously. If you discover a security issue, please bring it to our attention right away! Below you will find all the methods to report security vulnerabilities:

* [Report security bugs through the Patchstack Vulnerability Disclosure Program](https://patchstack.com/database/vdp/bookit)
* Check our [Bug Bounty Program](https://www.liquidweb.com/policies/bug-bounty-program/)
* Reach out directly to us on `security [at] stellarwp.com`.

== Installation ==
This section describes how to install the plugin and get it working.

1. Upload the plugin files to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Please find more details on Plugin Installation in documentation
4. Set Up Page in Menu -> BookIt.


== Changelog ==

= 2.5.4 2026-03-11 =

* Security - SQL injection vulnerabilities in AJAX endpoints. [SVUL-55]

= 2.5.3 2026-02-26 =

* Fix - Update plugin display name to comply with WordPress.org naming guidelines and improve clarity. [BI-42]
* Fix - Correct gettext usage to ensure all translatable strings use static text and the proper text domain for full compatibility with WordPress translation tools. [BI-45]
* Tweak - Upgrade Freemius SDK to the latest stable version to improve security, compatibility, and support. [BI-44]
* Tweak - Replace bundled "freemius" package folder with Composer-based autoloading to follow WordPress.org best practices and reduce potential library conflicts. [BI-47]
* Tweak - Add missing composer.json file to the plugin root to improve transparency and align with open-source standards. [BI-43]
* Tweak - Change the description on readme.txt to comply with WordPress.org guidelines. [BI-46]

= 2.5.2 2026-01-13 =

* Fix - Copy button for the PayPal IPN now copies the correct URL.
* Fix - Add phone number validation on staff and customer add/edit forms.
* Fix - "Add to Calendar" feature now includes timezone information, ensuring calendar apps show the correct local time.

= 2.5.1 2025-11-08 =

* Security - Add authorization check to Stripe Connect REST API endpoint to prevent unauthorized account connections. [SVUL-29]
* Tweak - Update GitHub Actions cache from deprecated v2 to v4 for CI/CD workflows.
* Tweak - Update tested WordPress version to 6.8.
* Tweak - Replace wp_redirect with wp_safe_redirect to improve security compliance.
* Tweak - Add allowed_redirect_hosts filter for WhoDat domain in Stripe Connect disconnect flow.
* Fix - Add missing 'bookit' text domain to 16 internationalization function calls across 4 files.
* Fix - Add translator comments for strings with placeholders to improve translation context.
* Fix - Escape SQL table names properly in all database queries to improve security.
* Fix - Escape exception messages in Container.php to prevent potential XSS vulnerabilities.

= 2.5.0 2024-07-18 =

* Feature - Add Stripe Connect as a payment option. [BI-13]
* Feature - Add PayPal Legacy as a payment option. [BI-13]
* Fix - Fix settings import that failed on the file type. [BI-4]
* Fix - Prevent fatal error in PHP 8.0+ on Add-ons page if service returns no add-ons. [BI-6]
* Fix - Fix date and text search in the Appointments admin list. [BI-8]
* Fix - Add phone validation to appointment forms. [BI-5]
* Fix - Sanitize inputs of the appointment form in VUE. [BI-5]


= 2.4.6 =
**fixed**: Minor bug fixes.

= 2.4.5 =
**fixed**: Minor bug fixes.

= 2.4.4 =
**fixed**: Resolved vulnerability based on a report from PatchStack
**fixed**: Minor bug fixes

= 2.4.3 =
**fixed**: Minor bug fixes.

= 2.4.2 =
**fixed**: Resolved vulnerability based on a report from PatchStack

= 2.4.1 =
**updated**: Compatibility with WordPress 6.3

= 2.4.0 =
**updated**: Freemius SDK update.

= 2.3.9 =
**updated**: Security update.

= 2.3.8 =
**fixed**: The "Authentication vulnerability" reported by Wordfence is fixed.

= 2.3.7 =
**fixed**: The authentication Bypass vulnerability is fixed.
**fixed**: The demo import did not complete and returned a 500 error  on the console.

= 2.3.6 =
**fixed**: Minor bug fixes.

= 2.3.5 =
**fixed**: Compatibility with PHP 8 to ensure appointments can be created successfully.

= 2.3.4 =
**fixed**: Deprecated functions of Elementor are replaced with actual ones.

= 2.3.3 =
**fixed**: Minor bug fixes.

= 2.3.2 =
**fixed**: Deprecated Elementor methods.
**fixed**: Appointment Statuses PHP Error.

= 2.3.1 =
**new**: Quick premium support button in WP dashboard (for applying the issue tickets) and personal support account creation.

= 2.3.0 =
**updated**: Compatibility with WordPress 6.0
**fixed**: Inappropriate load of graphical elements on "Contact Us" page

= 2.2.9 =
**updated**: Security update

= 2.2.8 =
**updated**: Dashboard translations for static strings
**fixed**: Bug with staff before loading the services for them
**fixed**: Wordpress 5.9 'twenty twenty two' theme style fixes
**fixed**: AWS loader confilict fix
**fixed**: Dashboard style errors are fixed

= 2.2.7 =
**new**: Added new feature roadmap for Bookit
**updated**: Freemius SDK 2.4.2
**fixed**: Show minimal price in step-by-step Bookit form
**fixed**: For the same price, remove the word From
**fixed**: Not show "from' If price is equal for the staff

= 2.2.6 =
**added**: Use Wordpress time format for appointment time
**updated**: Translations for step by step view, updated pot file
**fixed**: Show client comment from appointment form
**fixed**: Change appointment status from customer tab
**fixed**: Send notification to admin email if it was changed from Settings
**fixed**: Import Bookit data from file fixes

= 2.2.5 =
**fixed**: Admin Dashboard notifications lag

= 2.2.4 =
**updated**: Admin Dashboard notifications updated

= 2.2.3 =
**fixed**: Shortcode logic issues ( correct data for fields depends on choosen values in admin ; frontend - set staff services and categories if staff ID in shortcode, etc.)
**fixed**: Сonnect staff to google calendar button style (*Google Calendar addon)
**fixed**: Min height for appointment on dashboard
**fixed**: Show day off by black color
**removed**: Hover/focus on inactive days
**removed**: All fonts from frontend
**added**: Close option for date and time blocks after selection (mobile devices)

= 2.2.2 =
**fixed**: The 'show currency symbol' setting on service step for the step-by-step view.
**updated**: The WordPress user with Administrator role can not be connected to BookIt staff

= 2.2.1 =
**added**: Services that are not assigned to any Categories will not display in the booking calendar.
**added**: Add to Calendar button added on the last step of appointment creation for Standard Calendar Template
**added**: Admin Dashboard notification
**fixed**: Step by step Calendar Template style fixes

= 2.2.0 =
**new**: Step by step calendar template with six stages of making appointments: Category step; Date & Time step; Details step; Payment step; Confirmation step.
**added**: Categories without any Service will not be displayed on the appointment booking process.
**added**: Step by step calendar template is set by default for mobile devices
**added**: Calendar templates section with Default and Step by step calendar templates
**added**: Add to calendar button added on the last step of appointment creation
**added**: 'Clean all on delete' option that deletes all database tables and plugin settings on plugin uninstallation.
**updated**: Dashboard General Settings style updated
**added**: Woocommerce custom title and custom icon for step by step calendar template (Pro)

= 2.1.9 =
**fixed**: Bookit Payments add-on deactivation issue

= 2.1.8 =
**added**: Links to purchase add-ons on the landing page.
**fixed**: Bookit Payments add-on icon

= 2.1.7 =
**new**:  WordPress user roles for bookit staff and bookit customer
**new**: Sender Name and Sender Email fields on Settings for changing default WordPress sender details in notification emails
**added**: Staff assignment as a  WordPress Users
**added**: WPML translations for email templates
**added**: Google Calendar add-on section on Settings
**added**: New tab to buy add-ons in the free version of the plugin
**added**: Confirmation email for appointments for Staff
**fixed**: Style fixes in Appointments section
**fixed**: Date/Time issue in Appointments section
**fixed**: The Staff disappeared if service written in cyrillic

= 2.1.6 =
**fixed**: CSRF issue fixed in appointment actions

= 2.1.5 =
**added**: Feedback module inside BookIt settings
**added**: Roadmap voting in BookIt settings
**added**: New payment type “free” for free services.

= 2.1.4 =
**fixed**: Book appointment bugfix

= 2.1.3 =
**added**: Two or more calendars on one page
**added**: Notification alert if WooCommerce is not installed
**added**: Create appointment from dashboard appointment list section
**added**: Create appointment from dashboard calendar section by clicking on chosen date
**added**: Create customer while creating an appointment in dashboard
**added**: Customer autocomplete field while creating appointment in dashboard
**added**: Notification alert before deleting staff, service, customer or category that shows all related data to the object that will be deleted
**added**: Time slot duration setting
**fixed**: Style issues in plugin dashboard
**fixed**: Style issues on service list and booking form
**fixed**: Use staff price while creating or editing appointment
**fixed**: Appointment creation after dynamic authorization
**fixed**: Edit appointment without staff
**fixed**: Custom price for each staff
**fixed**: Show error if service time is not available
**fixed**: Edit/Create appointment blocked open accordion
**fixed**: Check is email exist before create customer
**updated**: Database structure changed - payments tables separated from appointment table

= 2.1.2 =
**fixed**: Styles for calendar week view

= 2.1.1 =
**added**: Calendar views added ( Day, Week, Month )
**added**: 'customer_phone' and 'customer_email' in email templates settings
**fixed**: Demo import data updated
**fixed**: Ability for editing appointments created for the staff member that was deleted
**updated**: Currency list
**updated**: Phone validation , minimum 8 symbols
**updated**: Appointment creation for a free service
**updated**: While delete customer save customer info for appointment

= 2.1.0 =
**updated**: Default time slot range set to 15 minutes
**updated**: Correct time slots for staff members
**added**: Show new phone in appointments If user logged in and input new phone in the booking form
**added**: New notification when changing service duration - "Changing of the service duration will not affect the existing appointments and will be applied only to new appointments"
**added**: 'Delete appointment' button
**added**: Notification email settings for deleted appointments
**added: Email template for "Delete appointment" notification
**updated**: 'Update appointment' button, update appointment status, payment status and other fields
**fixed**: Date input for Chrome
**removed**: Autofill working hours end time and breaks for staff members
**fixed**: Inability to create free service
**fixed**: Style issues on category/service slider

= 2.0.9 =
- **fixed**: Scrollbar for booking details modal window on small screens
- **fixed**: The booking form doesn't work if no active payment methods set
- **added**: Validation for add category form
- **added**: Validation for staff form
- **added**: Validation for services form
- **added**: Validation for customer form
- **fixed**: Bottom scrollbar appearance
- **fixed**: View full letters inside the input fields
- **fixed**: Border appearance on active field
- **fixed**: Rows height in booking details modal
- **updated**: Demo import data updated
- **added**: Icon for export JSON on Settings page -> import/export tab
- **fixed**: Buttons alignment on Settings page -> import/export tab
- **fixed**: Special symbols for the translation
- **fixed**:  ‘Day Off’ value saved incorrectly 00:00:00 instead of Null while adding and updating working hours
- **fixed**: Arrows appearance for service scrolling
- **fixed**: Last service elements appearance from the list
- **added**: Hide services that have no staff assigned to it
- **fixed**: Show correct tab on settings page after page refresh
- **added**: Show selected file on import JSON with styles
- **added**: Error messages from the server if import JSON or import demo returned error
- **fixed**: Select file button pseudo-element style
- **updated**: Currency in main settings now applied for all payment types (autocomplete by name and currency code, can choose by buttons)
- **added**: Bookit form validation for user “book appointment”
    - check the full name ( from 3 - 25 letters)
    - check phone if exist
    - check email (email is required if booking type = registered_user)
    - check is exist phone or email if booking_type = guest
    - check password and password confirmation fields if booking_type = registered user
- **fixed**: Styles for icons on the calendar on hover for small screens
- **fixed**: Text alert style for redirect URL info
- **added**: Security features

= 2.0.8 =
- **added**: Stripe Strong Customer Authentication (3D Secure)

= 2.0.7 =
- **fixed**: Copyright text removed

= 2.0.6 =
- **fixed**: Copyright disabled for Pro Plugin

= 2.0.5 =
- **added**: Plugin Copyright
- **added**: Import / Export feature
- **fixed**: Print Appointment Confirmation bug

= 2.0.4 =
- **added**: Stylemix announcements in admin dashboard

= 2.0.2 =
* Guest Booking bug fixed

= 2.0.1 =
* Icon field added to Services
* Trimming titles issue fixed

= 2.0.0 =
* Plugin Refactoring

= 1.2.2 =
* Minor bug fixes.

= 1.1 =
* Minor bug fixes.

= 1.0.3 =
* Minor bug fixes.

= 1.0.2 =
* Features improved.

= 1.0.1 =
* Minor bug fixes.

= 1.0 =
* First Version of Plugin.
