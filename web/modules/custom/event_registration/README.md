Event Registration Module (Drupal 10)
Overview

The Event Registration module is a custom Drupal 10 module that allows administrators to configure events and users to register for those events through a custom form.
The module supports event-based registrations, validation, email notifications, admin reporting, and CSV export using Drupal core APIs only (no contrib modules).

Features

Admin event configuration form

User event registration form

Registration availability based on start and end dates

AJAX-based dependent dropdowns

Duplicate registration prevention

Email notifications using Drupal Mail API

Admin listing with filters and CSV export

Custom permissions

Uses Config API and Schema API

No hard-coded values

Installation Steps

Place the module in:
web/modules/custom/event_registration

Enable the module:

ddev drush en event_registration -y


Clear cache:

ddev drush cr


Assign permissions:

Go to Admin → People → Roles

Grant View event registrations permission to the Administrator role

URLs
Admin Pages

Event Configuration Form
/admin/config/event-registration

Admin Settings (Email Configuration)
/admin/config/event-registration/settings

Admin Registration Listing & CSV Export
/admin/event-registrations

User Page

Event Registration Form
/event/register

Database Tables
event_registration_event

Stores event configuration details.

Field	Description
id	Primary key
event_name	Name of the event
category	Event category
registration_start_date	Registration start timestamp
registration_end_date	Registration end timestamp
event_date	Event date timestamp
created	Created timestamp
event_registration_signup

Stores user registrations.

Field	Description
id	Primary key
event_id	Foreign key to event_registration_event
full_name	User full name
email	User email
college_name	College name
department	Department name
created	Registration timestamp
Event Configuration Logic (Admin)

Admin creates events using a custom Form API form

Events include:

Event name

Category

Registration start date

Registration end date

Event date

Date validation ensures:

Registration end date is after start date

Event date is after registration end date

Event data is stored in event_registration_event

User Registration Logic

Registration form is available only between:
registration_start_date ≤ current_date ≤ registration_end_date

Fields:

Full Name

Email Address

College Name

Department

Category (from DB)

Event Date (AJAX)

Event Name (AJAX)

Dependent dropdowns:

Category → Event Date

Event Date → Event Name

Data stored in event_registration_signup

Validation Logic

Required field validation using Form API

Email validation using FILTER_VALIDATE_EMAIL

Text fields allow only alphabets and spaces

Duplicate registration prevention using:
Email + Event ID

User-friendly validation messages displayed

Email Notification Logic

Uses Drupal Mail API

Triggered after successful registration

Emails sent:

Confirmation email to user

Notification email to admin (optional)

Admin email and enable/disable toggle managed using Config API

No hard-coded email addresses

Email content includes:

Name

Event Name

Event Date

Category

Admin Listing Page

Accessible only to users with custom permission

Features:

Event Date dropdown

Event Name dropdown (AJAX-based)

Participant count

Tabular listing of registrations

Displays:

Name

Email

Event Date

College Name

Department

Submission Date

CSV Export

Admin can export filtered registrations as CSV

CSV includes:

Name

Email

Event Name

Event Date

College Name

Department

Submission Date

Configuration Management

Uses Drupal Config API

Configurable values:

Admin notification email

Enable/disable admin notifications

No hard-coded configuration values

Technical Details

Drupal Version: 10.x

No contrib modules used

Follows:

PSR-4 autoloading

Dependency Injection

Drupal coding standards

Database schema created using Schema API

Configuration handled using Config API

SQL dump file included for evaluation

Development & Testing Notes

Module developed incrementally with regular Git commits

Email functionality tested using:

Real email addresses

Drupal core Mail API

Watchdog logs / MailHog (development environment)