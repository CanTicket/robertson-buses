# DEVELOPER HANDOFF - Buses MVP Implementation

## **🎯 Project Overview**

**Project:** Buses - Bus Company Operations & Staff Management Platform  
**Client:** CanTicket (Cassandra Cadorin)  
**Budget:** AU$12,000 - AU$18,000  
**Timeline:** 3-4 weeks  
**Strategy:** Build on CanTicket Laravel 11 foundation (90% reuse) + 10% new bus-specific features  

---

## **📦 What You're Receiving**

This handoff package includes:

1. ✅ **Complete MVP Specification** - Full requirements and feature list
2. ✅ **Database Migrations** - 4 new tables for Buses features
3. ✅ **Models** - Vehicle, DailyChecklist, ChecklistItem, ChecklistPhoto
4. ✅ **Controllers** - VehicleController, ChecklistController, ChecklistReportController
5. ✅ **Blade Views** - Complete UI for all user roles
6. ✅ **Routes** - All bus-specific routes defined
7. ✅ **Documentation** - Deployment guide, user guide, API docs
8. ✅ **Notification System** - Kids left alert (critical safety feature)

---

## **⚡ Quick Start - First 30 Minutes**

### **1. Access the GitHub Repository**

```bash
# Clone the repository
git clone https://github.com/CanTicket/buses-mvp.git
cd buses-mvp

# Review the structure
ls -la
```

### **2. Read Key Documents (Priority Order)**

Read these files IN THIS ORDER:

1. **`PROJECT_SUMMARY.md`** (5 min) - Full project overview, features delivered
2. **`README.md`** (3 min) - Quick setup instructions
3. **`DEPLOYMENT.md`** (10 min) - How to deploy to Dreamscape
4. **`FORK_EXPLAINED.md`** (5 min) - Understanding code vs database separation
5. **`GITHUB_DEPLOYMENT.md`** (5 min) - Git workflow

### **3. Review the Files Created**

Check what's been provided:

```bash
# Models
ls -la app/Models/
# Should see: Vehicle.php, DailyChecklist.php, ChecklistItem.php, ChecklistPhoto.php

# Controllers
ls -la app/Http/Controllers/
# Should see: VehicleController.php, ChecklistController.php, ChecklistReportController.php

# Migrations
ls -la database/migrations/
# Should see: 4 new migration files (2025_10_28_*)

# Views
ls -la resources/views/
# Check admin/pages/vehicles, regular/pages/checklist, managerial/pages/checklist

# Routes
cat routes/buses.php
```

---

## **🔧 Implementation Steps**

### **PHASE 1: Setup CanTicket Base (Week 1, Days 1-2)**

#### **Step 1.1: Get CanTicket Codebase**

**Option A: Access Existing CanTicket Installation**
```bash
# If you have access to CanTicket repository
git clone https://github.com/CanTicket/canticket-laravel.git canticket-base
cd canticket-base
```

**Option B: Request from Client**
```
Contact: cassandra@canticket.com
Request: Access to CanTicket Laravel 11 codebase
Alternative: Get ZIP export from /Users/cassandracadorin/Downloads/canticket-laravel-secondpush/
```

#### **Step 1.2: Verify CanTicket Setup Locally**

```bash
cd canticket-base

# Copy environment
cp .env.example .env

# Configure database
nano .env
# Set: DB_DATABASE=canticket_test

# Install dependencies
composer install
npm install

# Generate key
php artisan key:generate

# Run migrations
php artisan migrate

# Test it works
php artisan serve
# Visit: http://localhost:8000
```

**✅ Checkpoint:** CanTicket runs locally without errors

---

### **PHASE 2: Integrate Buses Files (Week 1, Days 3-5)**

#### **Step 2.1: Copy Buses Files into CanTicket**

```bash
# Assuming:
# - canticket-base/ = Working CanTicket installation
# - buses-mvp/ = This handoff package

cd canticket-base

# Copy Models
cp ../buses-mvp/app/Models/Vehicle.php app/Models/
cp ../buses-mvp/app/Models/DailyChecklist.php app/Models/
cp ../buses-mvp/app/Models/ChecklistItem.php app/Models/
cp ../buses-mvp/app/Models/ChecklistPhoto.php app/Models/

# Copy Controllers
cp ../buses-mvp/app/Http/Controllers/VehicleController.php app/Http/Controllers/
cp ../buses-mvp/app/Http/Controllers/ChecklistController.php app/Http/Controllers/
cp ../buses-mvp/app/Http/Controllers/ChecklistReportController.php app/Http/Controllers/

# Copy Notifications
cp ../buses-mvp/app/Notifications/KidsLeftOnBusAlert.php app/Notifications/

# Copy Migrations
cp ../buses-mvp/database/migrations/2025_10_28_*.php database/migrations/

# Copy Views
cp -r ../buses-mvp/resources/views/admin/pages/vehicles resources/views/admin/pages/
cp -r ../buses-mvp/resources/views/regular/pages/checklist resources/views/regular/pages/
cp -r ../buses-mvp/resources/views/managerial/pages/checklist resources/views/managerial/pages/

# Copy Routes
cp ../buses-mvp/routes/buses.php routes/
```

#### **Step 2.2: Register Buses Routes**

Edit `routes/web.php` and add at the end:

```php
// Include Buses-specific routes
require __DIR__.'/buses.php';
```

#### **Step 2.3: Update .env for Buses**

```env
APP_NAME="Buses"

# Buses-specific config
ALERT_EMAIL_KIDS_LEFT=manager@yourdomain.com
CHECKLIST_PHOTO_MAX_SIZE=5120
CHECKLIST_PHOTO_ALLOWED_TYPES=jpg,jpeg,png
```

#### **Step 2.4: Run Buses Migrations**

```bash
# This creates the 4 new tables
php artisan migrate

# Verify tables were created
php artisan tinker
>>> \Schema::hasTable('vehicles')
=> true
>>> \Schema::hasTable('daily_checklists')
=> true
```

**✅ Checkpoint:** All 4 new Buses tables exist in database

---

### **PHASE 3: Testing (Week 2, Days 1-3)**

#### **Step 3.1: Create Test Data**

```bash
php artisan tinker

# Create test vehicle
>>> $vehicle = \App\Models\Vehicle::create([
    'bus_number' => 'Bus 101',
    'registration_number' => 'ABC123',
    'make' => 'Mercedes-Benz',
    'model' => 'Sprinter',
    'year' => 2023,
    'capacity' => 45,
    'status' => 'Active',
    'company_id' => 1
]);

# Verify
>>> \App\Models\Vehicle::count()
=> 1
```

#### **Step 3.2: Test Each Route**

**Test Vehicle Management (Admin):**
```
1. Login as Admin
2. Navigate to: /admin/vehicles
3. Should see: "Fleet Management" page
4. Click: "Add New Vehicle"
5. Fill form and submit
6. Verify: Vehicle appears in list
```

**Test Checklist Flow (Driver):**
```
1. Login as Regular user (driver)
2. Clock in (using CanTicket time tracking)
3. Navigate to: /staff/checklist/create
4. Should see: Checklist form
5. Select vehicle, fill checks
6. Try to clock out WITHOUT completing checklist
   → Should be blocked with message
7. Complete checklist
8. Now try to clock out
   → Should work!
```

**Test Manager Review:**
```
1. Login as Manager
2. Navigate to: /managerial/checklists/review
3. Should see: Pending checklists
4. Click: "Review Checklist"
5. Approve or Flag
6. Verify: Status updates
```

**Test Kids Alert:**
```
1. Complete checklist as driver
2. Select "Yes" for "Kids left on bus?"
3. Submit
4. Check email: Manager should receive CRITICAL ALERT
5. Check database: alert_sent = true
```

#### **Step 3.3: Test Reporting**

```
1. Login as Admin
2. Navigate to: /admin/reports/checklists
3. Select date range
4. Should see: Stats and checklist list
5. Click: "Export to CSV"
6. Verify: CSV downloads with data
```

**✅ Checkpoint:** All features work end-to-end

---

### **PHASE 4: UI Branding (Week 2, Days 4-5)**

#### **Optional: Update Terminology**

If client wants to rebrand from CanTicket terms:

**Find and Replace in Blade Views:**
- "Project" → "Shift" or "Job"
- "Client" → "Vehicle" (where appropriate)
- "Task" → "Shift" (in context of time tracking)

**Update Logo/Colors:**
- Replace CanTicket logo with Buses branding
- Update primary colors in Tailwind config
- Customize email templates

---

### **PHASE 5: Deployment (Week 3)**

#### **Step 5.1: Prepare Dreamscape Server**

**On Dreamscape cPanel:**
1. Create new database: `buses_production_db`
2. Create database user with password
3. Upload code via SFTP or Git
4. Point domain to `/public` directory

#### **Step 5.2: Run Deployment Script**

```bash
# SSH into Dreamscape server
ssh username@server.dreamscape.com.au

cd /home/username/public_html/buses

# Make script executable
chmod +x deploy.sh

# Run deployment
./deploy.sh
```

This script will:
- Install composer dependencies
- Generate app key
- Set permissions
- Run migrations
- Cache config/routes/views

#### **Step 5.3: Configure Production .env**

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://buses.yourdomain.com

DB_DATABASE=buses_production_db
DB_USERNAME=buses_user
DB_PASSWORD=secure_production_password

MAIL_HOST=smtp.dreamscape.com.au
MAIL_FROM_ADDRESS=noreply@yourdomain.com
```

#### **Step 5.4: Setup Cron Job**

In cPanel → Cron Jobs, add:
```
* * * * * cd /home/username/public_html/buses && php artisan schedule:run >> /dev/null 2>&1
```

#### **Step 5.5: Setup SSL**

1. cPanel → SSL/TLS
2. Install Let's Encrypt certificate
3. Force HTTPS in `.htaccess`

**✅ Checkpoint:** Production site accessible at https://buses.yourdomain.com

---

### **PHASE 6: User Training & Go-Live (Week 4)**

#### **Step 6.1: Create Initial Data**

```bash
# SSH to production
php artisan tinker

# Create admin account
>>> $admin = \App\Models\User::create([
    'first_name' => 'Admin',
    'last_name' => 'User',
    'email_address' => 'admin@yourdomain.com',
    'user_access_id' => 'ADMIN_ID',
    'company_id' => 1,
    'user_active_status' => 'Active'
]);

# Create vehicles
>>> \App\Models\Vehicle::create([...]);
```

#### **Step 6.2: Conduct Training**

Use `USER_GUIDE.md` as training material:
- 2 hour session for managers
- 1 hour session for drivers
- Hands-on practice with test data

#### **Step 6.3: Monitor First Week**

- Check logs daily: `tail -f storage/logs/laravel.log`
- Monitor checklist completion rate
- Respond to user feedback quickly
- Fix any UX issues

---

## **📋 Developer Checklist**

Copy this checklist and check off as you complete:

### **Setup & Integration**
- [ ] Cloned Buses MVP repository
- [ ] Read all documentation (PROJECT_SUMMARY, DEPLOYMENT, etc.)
- [ ] Got access to CanTicket codebase
- [ ] Set up local CanTicket installation
- [ ] Copied all Buses files into CanTicket
- [ ] Registered Buses routes in web.php
- [ ] Updated .env with Buses config
- [ ] Ran Buses migrations (4 new tables)
- [ ] Verified tables exist in database

### **Local Testing**
- [ ] Created test vehicle
- [ ] Tested vehicle CRUD (create, read, update, delete)
- [ ] Tested checklist form loads
- [ ] Tested mandatory checklist logic (cannot clock out without checklist)
- [ ] Tested "Kids left" alert sends email
- [ ] Tested manager review interface
- [ ] Tested approve checklist
- [ ] Tested flag checklist
- [ ] Tested reporting dashboard
- [ ] Tested CSV export

### **Deployment**
- [ ] Created production database on Dreamscape
- [ ] Uploaded code to Dreamscape
- [ ] Configured production .env
- [ ] Ran deployment script
- [ ] Verified site loads with HTTPS
- [ ] Set up cron job for scheduled tasks
- [ ] Tested email sending in production

### **Go-Live**
- [ ] Created admin account
- [ ] Added initial vehicles
- [ ] Created manager accounts
- [ ] Created driver accounts
- [ ] Conducted training sessions
- [ ] Collected user feedback
- [ ] Fixed any issues
- [ ] 30-day support period active

---

## **🆘 Troubleshooting Guide**

### **Issue: Migration fails**

**Error:** `SQLSTATE[42S01]: Base table or table already exists`

**Solution:**
```bash
# Drop and recreate database
php artisan migrate:fresh
# Warning: This deletes ALL data!
```

---

### **Issue: Routes not found (404)**

**Error:** Route `admin.vehicles.index` not found

**Solution:**
```bash
# Clear route cache
php artisan route:clear
php artisan route:cache

# Verify routes registered
php artisan route:list | grep vehicle
```

---

### **Issue: Photos not uploading**

**Error:** `The file "photo.jpg" was not uploaded due to an unknown error`

**Solution:**
```bash
# Check storage permissions
chmod -R 775 storage/app/public
php artisan storage:link

# Verify symlink exists
ls -la public/storage
```

---

### **Issue: Email not sending**

**Error:** `Connection could not be established with host smtp.dreamscape.com.au`

**Solution:**
```env
# Verify SMTP settings in .env
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com.au
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com.au
MAIL_PASSWORD=correct_password
MAIL_ENCRYPTION=tls
```

Test:
```bash
php artisan tinker
>>> \Mail::raw('Test', function($m) { $m->to('test@example.com'); });
```

---

### **Issue: Kids alert not sending**

**Check:**
1. Is queue worker running? `php artisan queue:work`
2. Is QUEUE_CONNECTION set to `database` in .env?
3. Check `jobs` table for failed jobs

**Solution:**
```bash
# Process queue manually
php artisan queue:work --once

# Check failed jobs
php artisan queue:failed
```

---

## **🔗 Important Resources**

### **Documentation Files**
- `PROJECT_SUMMARY.md` - Complete project overview
- `DEPLOYMENT.md` - Step-by-step deployment guide (508 lines)
- `USER_GUIDE.md` - Training manual for all user roles (421 lines)
- `FORK_EXPLAINED.md` - Understanding fork vs database
- `GITHUB_DEPLOYMENT.md` - Git workflow and strategy

### **Technical References**
- Laravel 11 Docs: https://laravel.com/docs/11.x
- CanTicket Codebase: Contact cassandra@canticket.com
- Dreamscape Support: https://www.dreamscape.com.au/support/

### **Communication**
- **Client Contact:** Cassandra Cadorin (cassandra@canticket.com)
- **Project Manager:** [Assign PM]
- **Technical Questions:** Refer to this handoff document first
- **Emergency Issues:** [Define escalation process]

---

## **💰 Budget & Timeline Tracking**

| Phase | Estimated Hours | Budget Allocation |
|-------|----------------|-------------------|
| Setup & Integration | 16-20 hours | AU$2,000-2,500 |
| Local Testing | 12-16 hours | AU$1,500-2,000 |
| UI Branding (optional) | 8-12 hours | AU$1,000-1,500 |
| Deployment | 8-12 hours | AU$1,000-1,500 |
| Training & Support | 16-20 hours | AU$2,000-2,500 |
| Buffer | 20-40 hours | AU$4,500-8,000 |
| **TOTAL** | **80-120 hours** | **AU$12,000-18,000** |

**Hourly Rate:** AU$100-150/hour (Senior Laravel Developer)

---

## **✅ Definition of Done**

The project is complete when:

1. ✅ All 4 new database tables created and populated
2. ✅ Vehicle management CRUD fully functional
3. ✅ Checklist form works with photo upload
4. ✅ Drivers CANNOT clock out without completing checklist
5. ✅ "Kids left" alert sends email to managers immediately
6. ✅ Manager can approve/flag checklists
7. ✅ Reporting dashboard shows stats and allows CSV export
8. ✅ Production site live at https://buses.yourdomain.com
9. ✅ SSL certificate installed and HTTPS enforced
10. ✅ Cron job running for scheduled tasks
11. ✅ Initial vehicles and users created
12. ✅ Training sessions completed
13. ✅ USER_GUIDE.md distributed to all users
14. ✅ 30-day support period commenced

---

## **📞 Next Steps After Reading This**

### **For the Developer:**

1. **First Hour:**
   - [ ] Clone repository: `git clone https://github.com/CanTicket/buses-mvp.git`
   - [ ] Read PROJECT_SUMMARY.md (5 min)
   - [ ] Read DEPLOYMENT.md (15 min)
   - [ ] Request access to CanTicket codebase from Cassandra

2. **First Day:**
   - [ ] Set up local CanTicket installation
   - [ ] Run CanTicket successfully
   - [ ] Copy Buses files into CanTicket
   - [ ] Run Buses migrations
   - [ ] Create first test vehicle

3. **First Week:**
   - [ ] Complete all local testing
   - [ ] Fix any bugs found
   - [ ] Verify all features work end-to-end
   - [ ] Update any documentation gaps

4. **Week 2-3:**
   - [ ] Deploy to Dreamscape staging
   - [ ] Client review and feedback
   - [ ] Deploy to production
   - [ ] Conduct training

### **For the Project Manager:**

1. [ ] Assign developer to project
2. [ ] Schedule kickoff call with developer and Cassandra
3. [ ] Set up weekly progress check-ins
4. [ ] Track hours against budget
5. [ ] Coordinate user training schedule

---

## **🎯 Success Criteria**

This project will be considered successful when:

- 100% of drivers complete checklists before clocking out
- Zero missed "kids left" alerts (system sends within 5 minutes)
- Managers review checklists within 24 hours
- Bus company reports 50%+ reduction in paper-based processes
- Client satisfaction rating ≥ 4.5/5

---

**Project Status:** Ready for Development  
**Prepared By:** AI Development Team  
**Date:** October 28, 2025  
**Version:** 1.0  

**Good luck! You've got this! 🚀**



