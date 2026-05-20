# RentACar - Vehicle Condition Assessment Car Rental Management System

> A comprehensive car rental management system built with Laravel 12, Vue 3, and Inertia.js, featuring vehicle condition assessment, damage reporting, and dispute management to reduce vehicle damage disputes.

[![Laravel](https://img.shields.io/badge/Laravel-12-red)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3-green)](https://vuejs.org)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

## 🎯 Project Overview

RentACar is a specialized car rental management system designed to reduce vehicle damage disputes through comprehensive vehicle condition assessment at pickup and return. The system captures detailed condition evidence, compares pickup and return inspections, and manages damage reports and customer disputes through a structured workflow.

### Problem Addressed

Traditional car rental systems lack structured vehicle condition assessment mechanisms, leading to disputes between customers and rental companies over vehicle damage claims. RentACar solves this by:

- Capturing detailed pickup condition assessments with photographic evidence
- Recording return condition assessments with identical parameters
- Automatically comparing pickup and return conditions
- Enabling structured damage report creation linked to inspections
- Providing a dispute resolution workflow for damage claims

---

## 🚀 Core Features

### 👤 Customer Portal
- **Vehicle Browsing** - Browse available vehicles with detailed specifications
- **Reservations** - Create and manage car rental reservations
- **View Reservation History** - Track all past and current bookings
- **Damage Reports** - View damage reports created for their reservations
- **Dispute Management** - Submit disputes for damage claims and track resolution
- **Account Management** - Update profile and security settings
- **Two-Factor Authentication** - Enhanced security option

### 🔐 Admin Dashboard
- **Vehicle Management** - CRUD operations for fleet inventory with condition tracking
- **Reservation Management** - View, approve, and manage customer reservations
- **Pickup Inspection** - Conduct detailed vehicle condition assessment at pickup
- **Return Inspection** - Conduct detailed vehicle condition assessment at return
- **Inspection Comparison** - Automatically compare pickup and return conditions
- **Damage Reports** - Create, review, and manage damage reports
- **Dispute Resolution** - Review and resolve customer damage disputes
- **Reports** - Generate comprehensive reports on:
  - Reservations and bookings
  - Vehicle inspections
  - Damage reports and trends
  - Dispute statistics
  - Vehicle condition history
- **Client Management** - View and manage customer accounts
- **Audit Trail** - Track all administrative actions

### 🔍 Vehicle Condition Assessment System
- **Pickup Inspection Module**
  - Record vehicle mileage and fuel level
  - Document overall condition rating
  - Add detailed condition items for major vehicle areas
  - Upload photographic evidence for each condition item
  - Store vehicle state at time of customer pickup

- **Return Inspection Module**
  - Repeat same assessment parameters as pickup
  - Upload new evidence photos
  - Automatic comparison with pickup inspection
  - Highlight areas with condition changes

- **Inspection Evidence Management**
  - Upload multiple images per condition item
  - Support for JPG, PNG, WEBP formats
  - Organize evidence by inspection type and vehicle area
  - Public storage access for evidence retrieval

### 📊 Damage Report Management
- **Damage Report Creation**
  - Create reports linked to specific reservations
  - Link pickup and return inspections
  - Define damage areas and severity
  - Store detailed damage descriptions

- **Damage Report Workflow**
  - Status: open → under_review → resolved/rejected
  - Audited by admin with timestamp tracking
  - Viewable by customers through portal

### ⚖️ Dispute Management System
- **Customer Dispute Submission**
  - Customers can dispute damage reports
  - Provide detailed dispute reasons
  - Request admin review and resolution

- **Admin Dispute Resolution**
  - Review dispute details and evidence
  - Respond to customer disputes
  - Resolve or reject disputes with audit trail

---

## 🛠 Tech Stack

- **Backend** - Laravel 12 (PHP 8.2+)
- **Frontend** - Vue 3 with TypeScript
- **State Management** - Inertia.js
- **Styling** - Tailwind CSS 4
- **Database** - SQLite (dev) / MySQL (production)
- **File Upload** - FilePond with Laravel integration
- **Reports** - DomPDF for PDF generation
- **Testing** - Pest PHP
- **Authentication** - Laravel Fortify with Two-Factor Authentication

---

## 📋 Requirements

- **PHP** ≥ 8.2
- **Composer** ≥ 2.0
- **Node.js** ≥ 18.x
- **npm** ≥ 10.x
- **Database** - SQLite (included) or MySQL 8.0+
- **Disk Space** - For vehicle images and inspection evidence (1GB+ recommended)

---

## 🔧 Installation & Setup

### Step 1: Clone Repository
```bash
git clone <repository-url>
cd real-rent-car
```

### Step 2: Install PHP Dependencies
```bash
composer install
```

### Step 3: Install Node Dependencies
```bash
npm install
```

### Step 4: Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```

### Step 5: Configure Database (if using MySQL)
Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=real_rent_car
DB_USERNAME=root
DB_PASSWORD=
```

### Step 6: Run Migrations
```bash
php artisan migrate
```

### Step 7: Seed Test Data (Optional)
```bash
php artisan db:seed
```

### Step 8: Setup Storage Link
```bash
php artisan storage:link
```

### Step 9: Build Frontend Assets
```bash
npm run build
```

### Step 10: Start Development Server
```bash
php artisan serve
```

In a separate terminal, start the frontend dev server:
```bash
npm run dev
```

Visit `http://localhost:8000` to access the application.

---

## 🔑 Default Test Accounts

After seeding, the following test accounts are available:

**Admin User:**
- Email: `admin@rentacar.test`
- Password: `password`
- Role: Administrator

**Test Customer:**
- Email: `customer@rentacar.test`
- Password: `password`
- Role: Customer

---

## 📱 Main Application URLs

### Public Pages
- Homepage: `http://localhost:8000/`
- Fleet: `http://localhost:8000/fleet`
- About: `http://localhost:8000/about`
- Contact: `http://localhost:8000/contact`

### Authentication
- Register: `http://localhost:8000/register`
- Login: `http://localhost:8000/login`
- Password Reset: `http://localhost:8000/forgot-password`

### Customer Portal
- My Reservations: `http://localhost:8000/client/reservations`
- Damage Reports: `http://localhost:8000/client/damage-reports`
- Disputes: `http://localhost:8000/client/disputes`
- Support Tickets: `http://localhost:8000/client/support`

### Admin Dashboard
- Admin Home: `http://localhost:8000/admin` → redirects to `/admin/cars`
- Vehicles: `http://localhost:8000/admin/cars`
- Reservations: `http://localhost:8000/admin/reservations`
- Inspections: Within reservation details
- Damage Reports: `http://localhost:8000/admin/damage-reports`
- Disputes: `http://localhost:8000/admin/disputes`
- Reports: `http://localhost:8000/admin/reports`
- Clients: `http://localhost:8000/admin/clients`

---

## 🧪 Database Schema

### Core Tables
- **users** - Customer and admin accounts with role-based access
- **cars** - Vehicle inventory with condition tracking fields
- **reservations** - Car rental bookings with status workflow
- **vehicle_inspections** - Pickup and return inspection records
- **vehicle_condition_items** - Detailed condition assessments per inspection
- **inspection_evidence** - Uploaded photos and files for evidence
- **damage_reports** - Damage claims linked to reservations and inspections
- **disputes** - Customer disputes for damage reports with resolution tracking

### Status Workflows

**Reservation Status:**
```
pending → approved (CONFIRMED) → active (during rental) → completed
         ↓
       rejected (CANCELLED)
```

**Vehicle Inspection Status:**
```
pending → completed → verified
```

**Damage Report Status:**
```
open → under_review → resolved/rejected
```

**Dispute Status:**
```
submitted → reviewing → resolved/rejected
```

---

## 🚀 Usage Workflows

### Customer Workflow
1. Register and create account
2. Browse available vehicles on Fleet page
3. Create reservation with desired dates
4. Wait for admin approval
5. At pickup, admin performs pickup inspection
6. Customer receives vehicle
7. At return, admin performs return inspection
8. If damage reported, view damage report in portal
9. If disputing damage, submit dispute for admin review
10. Track dispute resolution in portal

### Admin Workflow
1. Login to admin dashboard
2. Review pending reservations
3. Approve reservation (changes status to CONFIRMED)
4. On pickup date, create pickup inspection:
   - Record vehicle mileage and fuel level
   - Rate overall condition
   - Document condition items with photos
5. Mark reservation as ACTIVE
6. On return date, create return inspection:
   - Record same parameters as pickup
   - Compare with pickup inspection
7. If damage found, create damage report:
   - Link to both inspections
   - Define damage areas and severity
8. Customer may submit dispute
9. Review dispute details and respond
10. Resolve or reject dispute with comments
11. Generate reports for analysis

---

## 📸 Evidence Management

### File Upload
- Supported formats: JPG, PNG, WEBP
- Max file size: 10MB per file
- Storage: `storage/app/public/inspections/`
- Public access: `/storage/inspections/`

### Evidence Retrieval
- Evidence accessible through vehicle inspection pages
- Organized by inspection type (pickup/return) and vehicle area
- Compare evidence between pickup and return visually

---

## 📊 Reports Available

1. **Reservation Reports**
   - Reservations by date range
   - Status breakdown
   - Revenue analysis

2. **Inspection Reports**
   - Inspection frequency
   - Completion status
   - Inspector performance

3. **Damage Reports**
   - Damage incidents by vehicle
   - Damage types and severity
   - Trends over time

4. **Dispute Reports**
   - Dispute submission rate
   - Resolution status
   - Customer-specific disputes

5. **Vehicle Condition History**
   - Condition changes per vehicle
   - Damage frequency
   - Maintenance recommendations

---

## 🔒 Security & Access Control

### Authentication
- Email-based user registration
- Password hashing with bcrypt
- Session-based authentication
- Two-factor authentication available

### Authorization
- Role-based access control (Customer / Admin)
- Route middleware protection
- Policy-based action authorization
- Request model binding validation

### Data Protection
- CSRF token protection on forms
- SQL injection prevention through ORM
- XSS protection through Vue template escaping
- File upload validation (type and size)

---

## 🐛 Known Limitations

1. **Footer Service Links** - Some service links in footer are placeholder links (#) and should be connected to actual service pages
2. **Email Notifications** - Notifications are logged but not sent via email (configured for local testing)
3. **Multi-language** - Only English language supported
4. **Mobile Responsiveness** - Admin dashboard has limited mobile optimization
5. **Batch Operations** - Admin actions are single-item based (no bulk operations)
6. **API** - No REST API provided (Inertia.js only)

---

## 🚀 Future Improvements

1. **Digital Signatures** - Signature capture for pickup/return inspections
2. **Mobile Application** - Dedicated mobile app for customer and inspection workflows
3. **Email Notifications** - Automated notifications for reservations, approvals, disputes
4. **PDF Reports** - Generate downloadable condition assessment reports
5. **Payment Integration** - Online payment processing for reservations
6. **Advanced Analytics** - Damage analytics and prediction models
7. **QR Codes** - QR code vehicle tracking for inspections
8. **Multi-branch Support** - Support for multiple rental locations
9. **Export Features** - Export reports to Excel/PDF
10. **API Integration** - REST API for third-party integrations

---

## 🧪 Testing

Run the test suite with:
```bash
composer test
```

Or with:
```bash
php artisan test
```

---

## 📝 Contributing

Contributions are welcome! Please follow the Laravel and Vue coding standards.

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 📧 Support

For issues and questions:
- Create an issue in the repository
- Contact the development team
- Use the in-app contact form

---

## 🙏 Acknowledgments

Built with:
- [Laravel Framework](https://laravel.com)
- [Vue.js](https://vuejs.org)
- [Inertia.js](https://inertiajs.com)
- [Tailwind CSS](https://tailwindcss.com)

---

**Last Updated:** May 2026
**Version:** 1.0.0 (MILESTONE 10)
