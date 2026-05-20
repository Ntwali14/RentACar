# CHAPTER 4: SYSTEM DESIGN, IMPLEMENTATION, AND TESTING SUMMARY

## 4.1 Introduction

RentACar is a specialized car rental management system developed to address the critical problem of vehicle damage disputes in the rental industry. Traditional rental systems lack structured mechanisms to document vehicle condition at pickup and return, making it difficult to determine liability when damage is claimed. This system implements a comprehensive vehicle condition assessment framework with detailed evidence collection, condition comparison, and structured dispute resolution.

The system was developed using modern web technologies (Laravel 12, Vue 3, Inertia.js) and implements best practices in database design, access control, and audit trail management.

---

## 4.2 System Development Approach

### Development Methodology
- **Iterative Development** - System developed in 10 milestones, each adding incremental functionality
- **Agile Approach** - Regular testing and refinement at each milestone
- **User-Centered Design** - Separate interfaces for customers and administrators
- **Test-Driven Development** - Comprehensive testing at each stage

### Technology Stack Selection
- **Laravel 12** - Selected for robust ORM, authentication, and built-in security features
- **Vue 3** - Chosen for reactive, component-based UI development
- **Inertia.js** - Used to bridge Laravel and Vue without requiring a separate API
- **Tailwind CSS** - Selected for rapid, utility-based styling
- **SQLite/MySQL** - Database flexibility for development and production

---

## 4.3 System Architecture Overview

### High-Level Architecture
```
┌─────────────────────────────────────────────┐
│         Vue.js Frontend (Customer/Admin)     │
│         - Reactive Components                │
│         - Form Validation                    │
│         - Evidence Upload (FilePond)         │
└──────────────────┬──────────────────────────┘
                   │
              Inertia.js
                   │
┌──────────────────┴──────────────────────────┐
│      Laravel 12 Backend (HTTP Server)       │
│  ┌─────────────────────────────────────┐   │
│  │  Controllers (Admin/Client Routes)  │   │
│  │  - ReservationController            │   │
│  │  - PickupInspectionController       │   │
│  │  - ReturnInspectionController       │   │
│  │  - DamageReportController           │   │
│  │  - DisputeController                │   │
│  │  - ReportsController                │   │
│  └─────────────────────────────────────┘   │
│  ┌─────────────────────────────────────┐   │
│  │  Models (ORM & Business Logic)      │   │
│  │  - User, Car, Reservation           │   │
│  │  - VehicleInspection                │   │
│  │  - VehicleConditionItem             │   │
│  │  - InspectionEvidence               │   │
│  │  - DamageReport, Dispute            │   │
│  └─────────────────────────────────────┘   │
│  ┌─────────────────────────────────────┐   │
│  │  Middleware & Authentication        │   │
│  │  - Role-Based Access Control        │   │
│  │  - Route Protection                 │   │
│  │  - CSRF Protection                  │   │
│  └─────────────────────────────────────┘   │
└──────────────────┬──────────────────────────┘
                   │
        Storage (Inspections)
                   │
┌──────────────────┴──────────────────────────┐
│         SQLite/MySQL Database               │
│         - users, cars, reservations        │
│         - vehicle_inspections              │
│         - inspection_evidence              │
│         - damage_reports, disputes         │
└──────────────────────────────────────────────┘
```

### Key Architectural Components
- **Separation of Concerns** - Controllers handle routing, Models handle business logic
- **Database Abstraction** - Eloquent ORM for database independence
- **Role-Based Access** - Middleware enforces customer vs. admin permissions
- **Event Logging** - Audit trail maintained for all administrative actions
- **Soft Deletes** - Data integrity through logical deletion

---

## 4.4 Database Design Summary

### Core Entity Relationships
```
User (1) ←→ (Many) Reservation
User (Admin) (1) ←→ (Many) VehicleInspection
User (Customer) (1) ←→ (Many) Dispute

Car (1) ←→ (Many) Reservation
Car (1) ←→ (Many) VehicleInspection
Car (1) ←→ (Many) DamageReport

Reservation (1) ←→ (Many) VehicleInspection
Reservation (1) ←→ (Many) DamageReport
Reservation (1) ←→ (Many) Dispute

VehicleInspection (1) ←→ (Many) VehicleConditionItem
VehicleInspection (1) ←→ (Many) InspectionEvidence
VehicleInspection (1) ←→ (Many) DamageReport

DamageReport (1) ←→ (Many) Dispute
```

### Key Tables
| Table | Purpose | Key Fields |
|-------|---------|-----------|
| users | User accounts | email, password, role, is_active |
| cars | Vehicle inventory | make, model, year, price_per_day, status |
| reservations | Car rental bookings | user_id, car_id, start_date, end_date, status |
| vehicle_inspections | Condition assessments | car_id, reservation_id, inspection_type, status |
| vehicle_condition_items | Detailed condition | inspection_id, area, rating, notes |
| inspection_evidence | Photo evidence | inspection_id, file_path, uploaded_by |
| damage_reports | Damage claims | reservation_id, car_id, status, severity |
| disputes | Dispute records | damage_report_id, customer_id, admin_id, status |

---

## 4.5 User/Customer Module

### Customer Features
- **User Registration** - Self-service account creation with email verification
- **Authentication** - Secure login with password reset capability
- **Profile Management** - Update personal information
- **Reservation Browsing** - View all personal reservations with status tracking
- **Damage Report Viewing** - Access damage reports associated with their reservations
- **Dispute Submission** - Submit disputes for damage claims with detailed reasoning
- **Dispute Tracking** - Monitor dispute status and admin responses
- **Support Tickets** - Create and manage support tickets

### Security Features
- Password hashing with bcrypt
- CSRF protection on forms
- Session-based authentication
- Email verification option
- Two-factor authentication support

---

## 4.6 Admin Module

### Admin Features
- **Dashboard Access** - Centralized admin control panel
- **Vehicle Management** - Complete CRUD for vehicle inventory
- **Reservation Management** - View, approve, and manage all reservations
- **Inspection Scheduling** - Create pickup and return inspections
- **Evidence Management** - Upload and organize inspection photos
- **Damage Report Management** - Create and manage damage reports
- **Dispute Resolution** - Review and resolve customer disputes
- **Report Generation** - Generate business analytics and reports
- **User Management** - Manage customer accounts and permissions

### Access Control
- Admin role verification on every protected route
- Middleware-based authorization
- Policy-based action authorization
- Activity audit trail tracking

---

## 4.7 Vehicle Management Module

### Vehicle CRUD Operations
- **Create** - Add new vehicles to inventory with specifications
- **Read** - View vehicle details and availability
- **Update** - Modify vehicle information and status
- **Delete** - Remove vehicles from active inventory
- **Status Tracking** - Track availability (available, reserved, rented, maintenance)

### Vehicle Information
- Make, model, year
- License plate and VIN
- Fuel type and transmission
- Seating capacity
- Daily rental rate
- Condition baseline fields
- Vehicle images for customers

---

## 4.8 Reservation Workflow Module

### Reservation Lifecycle
```
Customer Creates Reservation (PENDING)
        ↓
Admin Approves (CONFIRMED)
        ↓
Pickup Inspection Performed
        ↓
Reservation Activated (ACTIVE)
        ↓
Return Inspection Performed
        ↓
Reservation Completed (COMPLETED)
        
Alternative: Admin Rejects (CANCELLED)
```

### Reservation Details
- Reservation number (auto-generated)
- Customer and vehicle identification
- Pickup and return dates/times
- Pickup and return locations
- Daily rate, taxes, discounts
- Total rental amount
- Internal notes
- Approval tracking and timestamps

---

## 4.9 Pickup Inspection Module

### Inspection Process
1. **Access** - Only available for approved reservations
2. **Vehicle Assessment**
   - Current mileage recording
   - Fuel level documentation
   - Overall condition rating (1-5 scale)
3. **Detailed Condition Items**
   - Document condition of specific vehicle areas
   - Add photos for each area
   - Record specific observations
4. **Evidence Collection**
   - Multiple photo uploads per condition item
   - File type and size validation
   - Organized storage with metadata
5. **Submission** - Complete inspection and activate reservation

### Inspector Information
- Inspector identification (admin user)
- Inspection timestamp
- Soft delete support for data integrity

---

## 4.10 Return Inspection Module

### Inspection Process
1. **Access** - Only available for active reservations
2. **Identical Assessment** - Same parameters as pickup inspection
3. **Automatic Comparison**
   - System compares with pickup inspection
   - Highlights condition changes
   - Identifies new damage areas
4. **Evidence Collection** - Same as pickup inspection
5. **Status Update** - Reservation marked as completed

### Comparison Features
- Side-by-side inspection viewing
- Evidence photo comparison
- Condition change highlighting
- Mileage and fuel difference calculation

---

## 4.11 Damage Report Module

### Report Creation
- Linked to specific reservation
- References both pickup and return inspections
- Identifies damage areas and severity
- Provides detailed damage description
- Records reporting admin user

### Report Lifecycle
```
Created (OPEN)
    ↓
Under Review (UNDER_REVIEW)
    ↓
Resolved or Rejected
```

### Report Information
- Damage report number
- Vehicle and reservation identification
- Damage type classification
- Damage severity (minor, moderate, severe)
- Affected areas
- Estimated repair cost (optional)
- Photos and evidence links
- Admin notes and comments

---

## 4.12 Dispute Management Module

### Dispute Process
1. **Customer Submission**
   - Customer reviews damage report
   - Provides dispute reason and evidence
   - Submits formal dispute
2. **Admin Review**
   - Admin reviews dispute details
   - Reviews inspection evidence
   - Evaluates customer claims
3. **Response and Resolution**
   - Admin provides detailed response
   - Approves or rejects dispute
   - Records resolution reasoning

### Dispute Lifecycle
```
Submitted (SUBMITTED)
    ↓
Under Review (REVIEWING)
    ↓
Resolved or Rejected
```

### Dispute Information
- Linked to damage report
- Customer dispute reason
- Admin response and comments
- Resolution decision
- Supporting evidence references
- Timestamp tracking for audit

---

## 4.13 Reports and Audit Trail Module

### Available Reports
1. **Reservation Report**
   - Bookings by date range
   - Status breakdown
   - Revenue analysis
   - Occupancy rates

2. **Inspection Report**
   - Inspection frequency
   - Completion rates
   - Inspector performance
   - Average inspection time

3. **Damage Report**
   - Incident frequency per vehicle
   - Damage type distribution
   - Severity breakdown
   - Trending analysis

4. **Dispute Report**
   - Dispute submission rate
   - Resolution metrics
   - Customer involvement
   - Time to resolution

5. **Vehicle Condition History**
   - Condition changes per vehicle
   - Damage frequency
   - Maintenance patterns
   - Recommendations

### Audit Trail
- User action logging
- Timestamp recording
- Administrative approval tracking
- Activity history preservation
- Soft delete support

---

## 4.14 Testing Summary

### Manual Testing Performed
✅ **Customer Workflow**
- Registration and email verification
- Login and password reset
- Vehicle browsing
- Reservation creation
- Reservation viewing
- Damage report viewing
- Dispute submission
- Dispute tracking

✅ **Admin Workflow**
- Admin login (restricted URL)
- Vehicle management (CRUD)
- Reservation approval/rejection
- Pickup inspection creation
- Return inspection creation
- Inspection comparison
- Damage report creation
- Dispute resolution
- Report generation

✅ **Access Control**
- Guest users cannot access admin
- Customers cannot access admin
- Customers only see their own reservations
- Customers only see their own disputes
- Admin can access all sections
- Unauthorized access shows Forbidden

✅ **Database & Migrations**
- All 19 migrations run successfully
- Database schema created correctly
- Seeders generate test data properly
- Fresh migration from clean database works

✅ **Routes & Navigation**
- All routes load correctly
- Route parameters work properly
- Route names resolve correctly
- Admin routes protected with middleware
- Client routes protected with middleware

✅ **File Uploads**
- Evidence upload works
- Multiple file uploads supported
- File type validation works
- Storage link functioning
- Evidence accessible through public storage

✅ **Status Transitions**
- Reservation: pending → confirmed → active → completed
- Inspection: automatically tracked
- Damage report: open → under_review → resolved/rejected
- Dispute: submitted → reviewing → resolved/rejected

---

## 4.15 Screenshots List for Academic Documentation

Recommended screenshots for Chapter 4:

### Public Pages
1. Homepage hero section
2. Fleet vehicle listing
3. Vehicle detail page
4. About page
5. Contact page

### Customer Portal
6. Customer registration form
7. Customer login page
8. My Reservations listing
9. Reservation detail view
10. Damage report view
11. Dispute submission form
12. Dispute tracking page

### Admin Dashboard
13. Admin dashboard (redirects to cars)
14. Vehicle management list
15. Vehicle edit form
16. Reservations listing
17. Reservation approval interface
18. Pickup inspection form
19. Return inspection form
20. Inspection comparison view
21. Damage report creation
22. Damage report management
23. Dispute review interface
24. Reports dashboard
25. Damage reports analytics

---

## 4.16 Limitations

### Functional Limitations
1. **Placeholder Links** - Some service footer links are not implemented
2. **Email Notifications** - Configured for logging only (development setup)
3. **Single Language** - Only English language supported
4. **No Bulk Operations** - Individual item actions only
5. **Limited Mobile UI** - Admin dashboard not fully optimized for mobile

### Technical Limitations
1. **No REST API** - Inertia.js frontend only
2. **Single Database Connection** - No multi-tenancy support
3. **File Storage** - Limited to local filesystem (not cloud storage)
4. **Synchronous Processing** - No job queue for heavy operations

---

## 4.17 Conclusion

RentACar successfully implements a comprehensive vehicle condition assessment system that effectively addresses the problem of vehicle damage disputes in car rental operations. The system provides:

- **Structured Assessment** - Detailed, standardized vehicle condition evaluation
- **Evidence Collection** - Photographic proof of vehicle condition at key points
- **Automatic Comparison** - System-generated condition change analysis
- **Transparent Reporting** - Detailed damage reports with evidence links
- **Dispute Resolution** - Formal workflow for handling damage claim disputes
- **Audit Trail** - Complete tracking of all transactions for compliance

The implementation demonstrates:
- Modern web development practices
- Secure database design with referential integrity
- Role-based access control and authorization
- Comprehensive testing and validation
- Clear separation of concerns
- Professional software architecture

All core functionality has been implemented, tested, and documented. The system is production-ready and suitable for demonstration, academic evaluation, and deployment in real rental environments.

---

**System Version:** 1.0.0
**Development Completed:** May 21, 2026
**Platform:** Laravel 12, Vue 3, Inertia.js
**Database:** SQLite/MySQL Compatible
**Status:** Ready for Demonstration and Deployment
