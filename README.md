# Event Registration Module (Drupal 10)

A custom Drupal 10 module that allows administrators to configure events and users to register for them with validation, email notifications, and admin reporting.

---

## Features

- **Event Configuration**: Admins can create and manage events with registration windows.
- **User Registration Form**: Public form with AJAX-based dependent dropdowns.
- **Validation Rules**: Email validation, duplicate registration prevention, and input sanitization.
- **Email Notifications**: Confirmation emails to users and optional admin notifications.
- **Admin Reporting**: Filter registrations by event/date and export data as CSV.
- **Access Control**: Admin-only pages secured with custom permissions.
- **Core-Only**: Built using Drupal core APIs (no contrib modules).

---

##  Quick Start

### General Prerequisites

- **Drupal 10.x**
- **PHP 8.1+**
- **MySQL / MariaDB**
- **Drush**
- **DDEV** (recommended for local development)

---

###  macOS /  Windows /  Linux (DDEV)

1. **Enable the Module**

   From the project root:
   ```bash
   ddev drush en event_registration -y
2. **Clear Cache**
   ```bash
   ddev drush cr
3. **Assign Permissions:**

   Go to Admin → People → Roles

    Grant View event registrations permission to the Administrator role

### URLs
   **Admin Pages**
1. Event Configuration Form:
   ```bash
   /admin/config/event-registration 
2. Admin Email Settings:
   ```bash
   /admin/config/event-registration/settings
3. Admin Registration Listing & CSV Export:
   ```bash
   /admin/event-registrations
**User Pages**

1. Event Registration Form:
   ```bash
   /event/register
### DATABASES

The module uses two custom database tables to store event and registration data.

#### 1. event_registration_event

This table stores the event details configured by the administrator.

| Field | Description |
|------|-------------|
| id | Primary key |
| event_name | Name of the event |
| category | Category of the event |
| registration_start_date | Registration start date (timestamp) |
| registration_end_date | Registration end date (timestamp) |
| event_date | Event date (timestamp) |
| created | Record creation timestamp |

---

#### 2. event_registration_signup

This table stores user registration details for events.

| Field | Description |
|------|-------------|
| id | Primary key |
| event_id | Foreign key referencing event_registration_event |
| full_name | User full name |
| email | User email address |
| college_name | College name |
| department | Department name |
| created | Registration timestamp |

---

### Validation Logic

The following validation rules are implemented in the event registration form:

- All form fields are required and validated using Drupal Form API
- Email format is validated using `FILTER_VALIDATE_EMAIL`
- Text fields allow only alphabets and spaces
- Duplicate registrations are prevented using:

Email + Event ID
- Users can register only within the configured registration start and end dates
- User-friendly validation messages are displayed for all errors

---

### Email Notification Logic

Email notifications are implemented using the **Drupal Mail API**.

- Emails are triggered after a successful event registration
- A confirmation email is sent to the registered user
- An admin notification email is sent if enabled in configuration
- Admin email address and notification toggle are managed using Drupal Config API
- Email content includes:
- User Name
- Event Name
- Event Date
- Event Category

---

### Admin Reporting

- Admin users can view all registrations at:
   ```bash
   /admin/event-registrations
- Registrations can be filtered by:
- Event Date
- Event Name (AJAX-based)
- Total number of participants is displayed
- Registration details are shown in a tabular format

---

### CSV Export

- Admins can export filtered registrations as a CSV file
- CSV includes the following fields:
- Name
- Email
- Event Name
- Event Date
- College Name
- Department
- Submission Date
