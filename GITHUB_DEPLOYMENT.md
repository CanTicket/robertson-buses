# GitHub Deployment Strategy for Buses MVP

## **Overview**

The Buses MVP is built on top of the CanTicket Laravel 11 codebase (90% reuse). This document explains the best process for GitHub deployment and integration.

---

## **Deployment Options**

### **Option 1: Separate Repository (Recommended for MVP)**

**Best for:** Quick deployment, clear separation, easy sales demos

**Structure:**
```
GitHub Repositories:
├── CanTicket/canticket-laravel (base system)
└── CanTicket/buses-mvp (bus-specific files only)
```

**Pros:**
- Clean separation of concerns
- Easy to maintain both codebases
- Can share to clients without exposing CanTicket core
- Simple updates to either system

**Cons:**
- Manual integration required for deployment
- Need to merge files when deploying

---

### **Option 2: Fork CanTicket Repository**

**Best for:** Long-term maintenance, automatic base updates

---

## ⚠️ **IMPORTANT: What "Fork" Actually Means**

### **Fork = Code Copy ONLY (NOT Database)**

A GitHub "fork" is simply a **copy of the source code** to your own GitHub account. Think of it like:
- Taking a photocopy of a recipe book (CanTicket code)
- You get your own copy to modify
- You can add your own recipes (Buses features)
- **The original book stays unchanged**

### **What Fork DOES Include:**
✅ All Laravel PHP code  
✅ Blade view templates  
✅ Routes and controllers  
✅ Migration files (database blueprints)  
✅ Configuration files  
✅ Documentation  

### **What Fork DOES NOT Include:**
❌ **NO Database** - Databases are NEVER copied with code  
❌ **NO User Accounts** - Your fork has zero users  
❌ **NO Data** - No tasks, timesheets, or records  
❌ **NO Uploaded Files** - No photos or attachments  
❌ **NO Environment Config** - You create your own .env file  

---

### **Example: Fork vs Separate Databases**

```
GitHub (CODE ONLY):
├── CanTicket/canticket-laravel (original code)
└── YourAccount/buses-mvp (forked code - your copy)

Databases (COMPLETELY SEPARATE):
├── Server 1: canticket_production_db
│   └── CanTicket live data (clients, timesheets, etc.)
│
└── Server 2: buses_production_db
    └── Buses data (vehicles, checklists, etc.)
    └── ZERO connection to CanTicket database
```

**They NEVER share databases!**

---

### **Structure:**
```
GitHub Repositories (CODE):
├── CanTicket/canticket-laravel (upstream code)
└── CanTicket/buses-mvp (fork with buses features)

Dreamscape Servers (DATABASES - SEPARATE):
├── canticket.com.au → canticket_db
└── buses.yourdomain.com → buses_db (100% independent)
```

**Pros:**
- Can pull code updates from CanTicket upstream (if they improve authentication, you can merge it)
- Full codebase in one repository
- Git tracks all changes
- **Each deployment has its OWN separate database**

**Cons:**
- Larger repository size
- More complex git history
- Harder to isolate bus-specific features

---

## **Recommended: Option 1 - Separate Repository**

### **Step-by-Step Process**

#### **1. Create GitHub Repository**

```bash
# In browser:
# 1. Go to: https://github.com/CanTicket
# 2. Click "New repository"
# 3. Name: buses-mvp
# 4. Description: "Bus company operations and staff management platform"
# 5. Private repository (recommended)
# 6. DO NOT add README, .gitignore, or license (we have our own)
# 7. Click "Create repository"
```

#### **2. Initialize Git in Buses Directory**

```bash
cd /Users/cassandracadorin/Buses

# Initialize git
git init

# Check status
git status
```

#### **3. Create .gitignore**

```bash
# Already created in your directory
# Ensures sensitive files aren't committed:
# - .env (database credentials, API keys)
# - /vendor (composer dependencies)
# - /node_modules (npm dependencies)
# - /storage (logs, uploaded photos)
```

#### **4. Stage and Commit Files**

```bash
# Stage all files
git add .

# Create initial commit
git commit -m "Initial commit: Buses MVP v1.0.0

Features:
- End-of-day safety checklists
- Vehicle fleet management
- Kids left alert system
- Manager review interface
- Checklist reporting & analytics
- Complete documentation

Built on CanTicket Laravel 11 foundation (90% reuse)
Budget: AU$12K-18K | Timeline: 3-4 weeks"
```

#### **5. Add GitHub Remote**

```bash
# Add remote (replace with your actual URL)
git remote add origin https://github.com/CanTicket/buses-mvp.git

# Verify remote
git remote -v
```

#### **6. Push to GitHub**

```bash
# Set main branch
git branch -M main

# Push to GitHub
git push -u origin main
```

**Done!** Your Buses MVP code is now on GitHub.

---

## **Integration with CanTicket for Deployment**

When deploying to Dreamscape, you'll need to **merge** Buses files into the CanTicket codebase:

### **Deployment Integration Process**

#### **Step 1: Clone Both Repositories**

```bash
cd /home/username/public_html/

# Clone CanTicket (base system)
git clone https://github.com/CanTicket/canticket-laravel.git buses
cd buses

# Rename for production
mv canticket-laravel buses
```

#### **Step 2: Add Buses Files**

```bash
# Clone Buses MVP to temp directory
git clone https://github.com/CanTicket/buses-mvp.git /tmp/buses-mvp

# Copy bus-specific files into CanTicket
cp -r /tmp/buses-mvp/app/Models/Vehicle.php app/Models/
cp -r /tmp/buses-mvp/app/Models/DailyChecklist.php app/Models/
cp -r /tmp/buses-mvp/app/Models/ChecklistItem.php app/Models/
cp -r /tmp/buses-mvp/app/Models/ChecklistPhoto.php app/Models/

cp -r /tmp/buses-mvp/app/Http/Controllers/VehicleController.php app/Http/Controllers/
cp -r /tmp/buses-mvp/app/Http/Controllers/ChecklistController.php app/Http/Controllers/
cp -r /tmp/buses-mvp/app/Http/Controllers/ChecklistReportController.php app/Http/Controllers/

cp -r /tmp/buses-mvp/app/Notifications/KidsLeftOnBusAlert.php app/Notifications/

# Copy migrations
cp -r /tmp/buses-mvp/database/migrations/2025_10_28_*.php database/migrations/

# Copy views
cp -r /tmp/buses-mvp/resources/views/admin/pages/vehicles resources/views/admin/pages/
cp -r /tmp/buses-mvp/resources/views/regular/pages/checklist resources/views/regular/pages/
cp -r /tmp/buses-mvp/resources/views/managerial/pages/checklist resources/views/managerial/pages/

# Copy routes
cp /tmp/buses-mvp/routes/buses.php routes/

# Clean up
rm -rf /tmp/buses-mvp
```

#### **Step 3: Update Route Registration**

Add to `routes/web.php`:

```php
// Include bus-specific routes
require __DIR__.'/buses.php';
```

#### **Step 4: Run Migrations**

```bash
php artisan migrate --force
```

#### **Step 5: Configure Environment**

```bash
# Copy .env.example and configure
cp .env.example .env
nano .env

# Update:
APP_NAME="Buses"
DB_DATABASE=buses_db
ALERT_EMAIL_KIDS_LEFT=manager@yourdomain.com
```

#### **Step 6: Deploy**

```bash
# Run deployment script
chmod +x deploy.sh
./deploy.sh
```

---

## **Branch Strategy**

### **Recommended Git Workflow**

```
main (production-ready)
├── develop (active development)
├── feature/vehicle-crud
├── feature/checklist-forms
├── feature/kids-alert
└── hotfix/critical-bug
```

**Commands:**

```bash
# Create feature branch
git checkout -b feature/new-feature

# Make changes, commit
git add .
git commit -m "Add new feature"

# Push to GitHub
git push origin feature/new-feature

# Create Pull Request on GitHub
# After review, merge to develop
# After testing, merge develop to main
```

---

## **GitHub Repository Settings**

### **Recommended Configuration**

**1. Branch Protection Rules**

Settings → Branches → Add rule:
- Branch name pattern: `main`
- ✅ Require pull request reviews before merging
- ✅ Require status checks to pass before merging
- ✅ Include administrators

**2. Collaborators**

Settings → Collaborators:
- Add developers with appropriate access
- Admin: Full access
- Write: Push to branches
- Read: Clone and pull only

**3. Secrets** (for CI/CD)

Settings → Secrets and variables → Actions:
- `DREAMSCAPE_HOST`
- `DREAMSCAPE_USER`
- `DREAMSCAPE_PASSWORD`
- (Use for automated deployment)

**4. Topics**

Add repository topics:
- `laravel`
- `php`
- `bus-management`
- `safety-checklist`
- `fleet-management`

---

## **Automated Deployment with GitHub Actions** (Optional)

Create `.github/workflows/deploy.yml`:

```yaml
name: Deploy to Dreamscape

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Deploy to Dreamscape
      uses: easingthemes/ssh-deploy@main
      env:
        SSH_PRIVATE_KEY: ${{ secrets.SSH_PRIVATE_KEY }}
        REMOTE_HOST: ${{ secrets.DREAMSCAPE_HOST }}
        REMOTE_USER: ${{ secrets.DREAMSCAPE_USER }}
        TARGET: /home/username/public_html/buses/
        
    - name: Run migrations
      run: |
        ssh ${{ secrets.DREAMSCAPE_USER }}@${{ secrets.DREAMSCAPE_HOST }} \
          "cd /home/username/public_html/buses && php artisan migrate --force"
```

---

## **Keeping Documentation Updated**

### **Files to Update on Changes**

When adding features:
- ✅ Update `README.md` with new features
- ✅ Update `DEPLOYMENT.md` with new steps
- ✅ Update `USER_GUIDE.md` with new user instructions
- ✅ Update `PROJECT_SUMMARY.md` with completion status
- ✅ Commit with clear message

---

## **Quick Reference Commands**

```bash
# Check git status
git status

# Stage changes
git add .

# Commit changes
git commit -m "Description of changes"

# Push to GitHub
git push origin main

# Pull latest from GitHub
git pull origin main

# Create new branch
git checkout -b feature-name

# Switch branches
git checkout main

# View commit history
git log --oneline

# View remote URL
git remote -v
```

---

## **Summary**

**Best Process:**

1. ✅ Create GitHub repository (buses-mvp)
2. ✅ Initialize git in `/Users/cassandracadorin/Buses`
3. ✅ Add, commit, push to GitHub
4. ✅ Keep Buses repo separate from CanTicket
5. ✅ Merge files during Dreamscape deployment
6. ✅ Use branches for new features
7. ✅ Enable branch protection on main

**Result:**
- Clean, maintainable codebase
- Easy to share with clients
- Simple deployment process
- Full version control history

---

**Questions?** Contact: cassandra@canticket.com

