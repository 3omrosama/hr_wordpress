=== NDS HR — WordPress HR Management System ===
Contributors: ndshr
Donate link: https://nds-hr.local
Tags: hr, human resources, employee management, employees, staff, portal, payroll
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Complete, modern HR Management System foundation for WordPress featuring custom database architecture, RBAC roles, audit logs, and employee portal.

== Description ==

NDS HR is a production-grade Human Resources Management System designed specifically for WordPress. Built with enterprise standards, high performance, and deep security, NDS HR turns your WordPress installation into a full-featured HR platform without relying on posts or wp_options for transactional data.

= Phase 1 Features: Foundation & Employee Management =

* **Custom Database Architecture:** Dedicated custom database tables with indexes for high scalability (`wp_nds_hr_employees`, `wp_nds_hr_departments`, `wp_nds_hr_positions`, `wp_nds_hr_audit_logs`).
* **Automated Employee ID:** Safe, consecutive, atomic Employee ID generation (e.g. `NDS-00001`) that never duplicates.
* **WordPress User Account Integration:** Create a linked WordPress user account during employee onboarding, or link an existing WordPress account.
* **Granular Role-Based Access Control:** Pre-configured roles (`HR Administrator`, `HR Manager`, `Employee`) with specific capabilities.
* **Modern SaaS Admin Interface:** Clean interface built with modern CSS custom properties and full responsive behavior.
* **Employee Management:** Searchable, filterable, and paginated employee directory. Add, edit, view, deactivate, and reactivate employees.
* **Employee Self-Service Portal:** Frontend portal accessible at `/employee/` where employees can securely view their personal profile, employee ID, department, and account information.
* **Security First:** Strict capability checks, nonces on all actions, SQL parameter preparation, input sanitization, and ownership verification.
* **Comprehensive Audit Logging:** Automatically logs employee lifecycle events, user account associations, and administrative changes.
* **Full Bilingual Support (EN / AR):** Built-in English and Arabic translations with dynamic RTL/LTR layout switching.

== Installation ==

1. Upload the `nds-hr` folder to the `/wp-content/plugins/` directory, or install the ZIP file via Plugins > Add New > Upload Plugin in the WordPress admin.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Upon activation, custom database tables and roles will be created automatically.
4. Navigate to **NDS HR** in the admin sidebar to access the HR dashboard and manage employees.
5. Employees can access their self-service portal at `your-site.com/employee/`.

== Frequently Asked Questions ==

= Does NDS HR modify existing WordPress users or data on deactivation? =
No. Data preservation is guaranteed. Deactivation retains all custom database tables, employees, departments, and user accounts intact.

= Does NDS HR use posts or custom post types for employees? =
No. NDS HR uses dedicated custom database tables with optimal indexes designed to handle thousands of employee records efficiently.

= What are the system requirements? =
* WordPress 6.0 or higher
* PHP 7.4 or higher (fully tested on PHP 8.2+)
* MySQL 5.7+ or MariaDB 10.3+

== Changelog ==

= 1.0.0 =
* Initial Phase 1 release: Foundation & Employee Management.
* Custom database tables schema with versioning and migrations.
* RBAC roles (HR Administrator, HR Manager, Employee) and granular capabilities.
* Employee repository, service, and data validation layers.
* SaaS-style Admin Dashboard with key HR metrics.
* Employee Directory with search, status filters, and pagination.
* Employee onboarding with automatic Employee ID (`NDS-00001`) and WordPress user account creation.
* Frontend Employee Self-Service Portal.
* Security hardening, nonces, and ownership validation.
* Audit logging engine.
* English and Arabic localization with RTL stylesheet support.
