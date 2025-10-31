# Buses - Implementation Documentation

## **Overview**

This document provides comprehensive instructions for deploying the Buses MVP application built on the CanTicket Laravel 11 foundation.

---

## **Project Structure**

```
Buses/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── VehicleController.php         # Fleet management CRUD
│   │   │   ├── ChecklistController.php       # Safety checklist logic
│   │   │   └── ChecklistReportController.php # Reporting & analytics
│   │   └── Middleware/
│   │       └── RoleMiddleware.php (from CanTicket)
│   ├── Models/
│   │   ├── Vehicle.php                       # Bus/vehicle model
│   │   ├── DailyChecklist.php                # Safety checklist model
│   │   ├── ChecklistItem.php                 # Individual check items
│   │   ├── ChecklistPhoto.php                # Photo attachments
│   │   ├── User.php (from CanTicket)         # Staff/driver users
│   │   ├── TaskTimer.php (from CanTicket)    # Time tracking (shift timer)
│   │   └── Leave.php (from CanTicket)        # Leave requests
│   └── Notifications/
│       └── KidsLeftOnBusAlert.php            # Critical safety notification
├── database/
│   └── migrations/
│       ├── 2025_10_28_000001_create_vehicles_table.php
│       ├── 2025_10_28_000002_create_daily_checklists_table.php
│       ├── 2025_10_28_000003_create_checklist_items_table.php
│       └── 2025_10_28_000004_create_checklist_photos_table.php
├── resources/
│   └── views/
│       ├── admin/pages/
│       │   ├── vehicles/       # Fleet management views
│       │   └── reports/        # Checklist reports
│       ├── managerial/pages/
│       │   └── checklist/      # Manager review interface
│       └── regular/pages/
│           └── checklist/      # Driver checklist forms
├── routes/
│   ├── web.php (from CanTicket)
│   └── buses.php              # Bus-specific routes
└── storage/
    └── app/public/
        └── checklist_photos/  # Uploaded photos storage
```

---

## **Database Setup**

### **1. Create Database**

```sql
CREATE DATABASE buses_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### **2. Configure .env**

```env
APP_NAME="Buses"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://buses.yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=buses_db
DB_USERNAME=buses_user
DB_PASSWORD=secure_password_here

MAIL_MAILER=smtp
MAIL_HOST=smtp.dreamscape.com.au
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="Buses Safety System"

# Alert Configuration
ALERT_EMAIL_KIDS_LEFT=manager@yourdomain.com
CHECKLIST_PHOTO_MAX_SIZE=5120
```

### **3. Run Migrations**

```bash
php artisan migrate --force
```

### **4. Seed Demo Data** (Optional for sales demos)

```bash
php artisan db:seed
```

---

## **Dreamscape Hosting Deployment**

### **Prerequisites**
- Dreamscape VPS or Shared Hosting account
- SSH access enabled
- PHP 8.2+ installed
- Composer installed
- MySQL database created

### **Deployment Steps**

#### **1. Upload Code via FTP/SFTP or Git**

**Option A: FTP Upload**
```
- Upload entire Buses directory to: /home/username/public_html/buses/
- Exclude: /vendor/, /node_modules/, .env
```

**Option B: Git Deployment** (Recommended)
```bash
cd /home/username/public_html/
git clone <your-buses-repo-url> buses
cd buses
```

#### **2. Install Dependencies**

```bash
cd /home/username/public_html/buses
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

#### **3. Set Permissions**

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### **4. Create Symbolic Link for Storage**

```bash
php artisan storage:link
```

#### **5. Configure Web Server**

**Apache (.htaccess in public directory)**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

**Or point document root to `/public` directory in cPanel**

#### **6. Setup SSL Certificate**

- In Dreamscape cPanel → SSL/TLS
- Install Let's Encrypt Free SSL
- Force HTTPS in .htaccess:

```apache
RewriteCond %{HTTPS} !=on
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

#### **7. Configure Cron Jobs**

Add to crontab (cPanel → Cron Jobs):

```cron
* * * * * cd /home/username/public_html/buses && php artisan schedule:run >> /dev/null 2>&1
```

This handles:
- Sending pending notifications
- Cleaning up old sessions
- Auto-completing stale checklists

#### **8. Optimize for Production**

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## **Email Configuration (Dreamscape SMTP)**

### **Settings in .env**

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com.au
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com.au
MAIL_PASSWORD=your_dreamscape_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com.au"
MAIL_FROM_NAME="Buses"
```

### **Test Email Sending**

```bash
php artisan tinker

>>> \Illuminate\Support\Facades\Mail::raw('Test email from Buses', function ($message) {
    $message->to('your-email@example.com')->subject('Test');
});
```

---

## **User Roles & Access**

### **Role Definitions** (from CanTicket `user_access` table)

| Role | Access Level | Can Do |
|------|--------------|--------|
| **Administrator** | Full system access | Manage users, vehicles, view all checklists, system config |
| **Managerial** | Team management | Review checklists, approve/flag, view team reports |
| **Regular** | Staff/Driver | Clock in/out, complete checklists, submit timesheets, request leave |
| **Contractor** | Limited view | View own checklists only |
| **Client** | External access | No access to Buses features (disabled) |

### **Creating Users**

**Via Admin Dashboard:**
1. Login as Administrator
2. Navigate to: Users → Add New
3. Set Role: Regular (for drivers)
4. Assign to Company

**Via Artisan (Command Line):**
```bash
php artisan make:user --role=regular --email=driver@example.com
```

---

## **Key Features Implementation**

### **1. End-of-Day Safety Checklist**

**Flow:**
1. Driver clocks in (CanTicket time tracking)
2. Driver works their shift
3. Before clocking out → **MANDATORY CHECKLIST**
   - Route: `/staff/checklist/create`
   - Form includes: Vehicle selection, tyre checks, fuel level, kids check, photos
4. On submission → Checklist saved, manager notified
5. If "Kids Left" = Yes → **CRITICAL ALERT** sent immediately
6. Manager reviews → Approve or Flag
7. Driver can now clock out

**Blocking Logic:**
- `ChecklistController::canClockOut()` checks if checklist completed
- Frontend clock-out button disabled until checklist done
- AJAX call validates before allowing clock out

### **2. Vehicle Fleet Management**

**Admin Routes:**
- `/admin/vehicles` - List all buses
- `/admin/vehicles/create` - Add new vehicle
- `/admin/vehicles/{id}` - View vehicle details & checklist history
- `/admin/vehicles/{id}/edit` - Update vehicle info

**Vehicle Fields:**
- Bus Number (e.g., "Bus 101")
- Registration Number (e.g., "ABC123")
- Make, Model, Year, Capacity
- Status: Active, Maintenance, Inactive

### **3. Kids Left on Bus Alert**

**Critical Safety Feature:**
- When driver selects "Yes" for kids check
- `KidsLeftOnBusAlert` notification sent immediately
- Email to all managers + in-app notification
- Logged in system with timestamp
- Checklist flagged with red border in UI

**Email Content:**
- Subject: "🚨 CRITICAL ALERT: Kids Left on Bus"
- Details: Vehicle, driver name, time
- Action link to view checklist immediately

### **4. Manager Checklist Review**

**Routes:**
- `/managerial/checklists/review` - List pending checklists
- `/managerial/checklists/{uuid}` - View checklist details
- POST `/managerial/checklists/{uuid}/approve` - Approve
- POST `/managerial/checklists/{uuid}/flag` - Flag for follow-up

**Review Actions:**
- **Approve:** Marks checklist as approved, adds review timestamp
- **Flag:** Requires notes, marks for follow-up, alerts admin

### **5. Reporting & Analytics**

**Available Reports:**
- Checklist completion rate
- Flagged checklists
- Kids alert incidents
- Checklist history by vehicle
- Checklist history by driver
- Export to CSV

**Routes:**
- `/admin/reports/checklists` - Report dashboard
- `/admin/reports/checklists/export` - CSV download

---

## **Extending CanTicket Features**

### **What's Reused from CanTicket (90%)**

✅ **Authentication** - Login, password reset, session management
✅ **User Management** - Add/edit/delete users, role assignment
✅ **Time Tracking** - Clock in/out (renamed "Shift Timer" in UI)
✅ **Timesheet Submission** - Staff submit hours, manager approval
✅ **Leave Management** - Request leave, manager approval, leave balance
✅ **Scheduling** - Staff shift scheduling, AI scheduling assistant
✅ **Notifications** - Email + in-app alerts
✅ **Reporting** - Time reports, staff reports, export functionality
✅ **Dashboards** - Role-based dashboards for Admin, Manager, Staff
✅ **Calendar Integration** - Google Calendar sync (optional)

### **What's NEW for Buses (10%)**

🚌 **Vehicle/Fleet Database** - Bus registry and management
🚌 **Daily Checklists** - End-of-day safety inspection forms
🚌 **Checklist Items** - Tyre checks, fuel level, kids check
🚌 **Photo Uploads** - Vehicle condition documentation
🚌 **Kids Alert System** - Critical safety notification
🚌 **Manager Review Interface** - Approve/flag checklists
🚌 **Checklist Reports** - Safety compliance reporting

---

## **Maintenance & Support**

### **Regular Maintenance Tasks**

**Weekly:**
- Review flagged checklists
- Check kids alert logs
- Monitor storage space (photos)

**Monthly:**
- Archive old checklists (>90 days)
- Review inactive vehicles
- Update user access as needed
- Backup database

**Quarterly:**
- Update Laravel dependencies: `composer update`
- Review and update PHP version
- Security audit

### **Backup Strategy**

**Database Backup (Daily):**
```bash
#!/bin/bash
mysqldump -u buses_user -p buses_db > backup_$(date +%Y%m%d).sql
gzip backup_$(date +%Y%m%d).sql
```

**File Backup (Weekly):**
```bash
tar -czf buses_backup_$(date +%Y%m%d).tar.gz /home/username/public_html/buses/
```

**Dreamscape Backup:**
- Enable automatic backups in cPanel
- Frequency: Daily
- Retention: 7 days

---

## **Troubleshooting**

### **Common Issues**

**1. Checklist photos not uploading**
```bash
# Check storage permissions
chmod -R 775 storage/app/public
php artisan storage:link
```

**2. Email notifications not sending**
```bash
# Test SMTP connection
php artisan tinker
>>> Mail::raw('Test', function($m) { $m->to('test@example.com'); });
```

**3. 500 Internal Server Error**
```bash
# Check logs
tail -f storage/logs/laravel.log
```

**4. Routes not working**
```bash
# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## **Security Considerations**

### **Checklist:**

- ✅ `.env` file not accessible via web
- ✅ SSL certificate installed (HTTPS)
- ✅ Database credentials secure
- ✅ File upload validation (images only, max 5MB)
- ✅ CSRF protection enabled (Laravel default)
- ✅ SQL injection protection (Eloquent ORM)
- ✅ XSS protection (Blade escaping)
- ✅ Role-based authorization enforced

### **Photo Storage Security:**

- Stored outside public directory: `storage/app/public/checklist_photos/`
- Accessed via symlink: `public/storage/checklist_photos/`
- Restricted to authenticated users only
- File type validation: JPG, JPEG, PNG only

---

## **Demo Data Seeder**

Create sample data for sales demonstrations:

```bash
php artisan db:seed --class=BusesDemoSeeder
```

**Creates:**
- 3 Admin users
- 5 Manager users
- 15 Driver users
- 10 Vehicles (Bus 101-110)
- 50 Completed checklists (last 30 days)
- 3 Flagged checklists
- 1 Kids alert incident

---

## **Next Steps After Deployment**

1. ✅ Create admin account
2. ✅ Add vehicles to fleet
3. ✅ Create manager accounts
4. ✅ Create driver/staff accounts
5. ✅ Test checklist flow end-to-end
6. ✅ Verify email notifications working
7. ✅ Test kids alert system
8. ✅ Train managers on review interface
9. ✅ Conduct driver training session
10. ✅ Monitor first week of live usage

---

## **Support Contacts**

**Technical Support:** cassandra@canticket.com  
**Documentation:** [Link to internal docs]  
**Emergency Hotline:** [Phone number for critical issues]

---

## **License**

Proprietary - CanTicket Internal Tool
© 2025 CanTicket. All rights reserved.

---

**Last Updated:** October 28, 2025  
**Version:** 1.0.0 MVP



