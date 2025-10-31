# Understanding GitHub Fork vs Database Separation

## **Quick Summary**

**Fork = Copy of CODE only**  
**Database = Completely separate for each installation**

---

## **What is a GitHub "Fork"?**

### **Simple Analogy:**

Think of CanTicket code like a **recipe book**:

1. **Original Recipe Book** = CanTicket repository on GitHub
   - Contains all the instructions (code) for making a meal
   - Anyone can read it (if public)

2. **Fork = Photocopy of Recipe Book**
   - You take a copy of the entire book
   - Now you have your own version
   - You can modify your recipes without changing the original
   - You can add new recipes (Buses features)

3. **Database = Your Kitchen Ingredients**
   - The recipe book (code) tells you HOW to cook
   - The ingredients (database) are what you actually use
   - **Everyone has their own kitchen with their own ingredients**
   - Your ingredients never mix with someone else's

---

## **What Happens When You Fork?**

### **When you fork CanTicket repository:**

```bash
# Original repository (CanTicket's account)
https://github.com/CanTicket/canticket-laravel
├── app/
├── database/migrations/  ← Blueprint files (NOT actual data)
├── resources/
└── composer.json

# YOUR forked repository (Your account)
https://github.com/YourAccount/buses-mvp
├── app/
├── database/migrations/  ← Same blueprint files
├── resources/
└── composer.json
```

### **What You Get:**
✅ **All code files** - PHP, Blade templates, JavaScript  
✅ **Migration files** - These are like blueprints/instructions for creating database tables  
✅ **Configuration templates** - .env.example (not the actual .env)  
✅ **Documentation** - README files  

### **What You DON'T Get:**
❌ **No Database** - The actual MySQL database is NOT in GitHub  
❌ **No .env file** - Environment config with passwords/keys  
❌ **No user data** - No accounts, passwords, emails  
❌ **No uploads** - No photos, documents, or user files  
❌ **No logs** - No activity history or records  

---

## **Real-World Example**

### **Scenario: CanTicket has 500 users**

```
CanTicket Production:
├── Code: github.com/CanTicket/canticket-laravel
├── Server: canticket.com.au (Dreamscape Server 1)
├── Database: canticket_production_db
│   ├── users table: 500 users
│   ├── tasks table: 10,000 tasks
│   ├── timesheets table: 5,000 timesheets
│   └── companies table: 50 companies
└── Storage: 5GB of uploaded files
```

### **You Fork CanTicket and Deploy Buses:**

```
Buses Production:
├── Code: github.com/YourAccount/buses-mvp (forked/copied from CanTicket)
├── Server: buses.yourdomain.com (Dreamscape Server 2)
├── Database: buses_production_db
│   ├── users table: 0 users (EMPTY - you create your own)
│   ├── vehicles table: 0 vehicles (NEW table from Buses)
│   ├── daily_checklists table: 0 checklists (NEW)
│   └── companies table: 0 companies
└── Storage: 0MB (empty)
```

**Result:**
- ✅ You have the same CODE structure
- ✅ You have the same TABLE STRUCTURE (from migrations)
- ❌ You have ZERO DATA from CanTicket
- ❌ You have ZERO shared database connection

---

## **How Databases Work in Laravel**

### **Each Installation = Own Database**

Every time you deploy Laravel, you create a NEW database:

**CanTicket Installation:**
```env
# CanTicket .env file (Server 1)
DB_DATABASE=canticket_production_db
DB_USERNAME=canticket_user
DB_PASSWORD=secret123
```

**Buses Installation:**
```env
# Buses .env file (Server 2) - COMPLETELY DIFFERENT
DB_DATABASE=buses_production_db
DB_USERNAME=buses_user
DB_PASSWORD=different456
```

**They NEVER communicate with each other!**

---

## **Visual: Fork vs Database**

```
┌─────────────────────────────────────────┐
│ GITHUB (Code Storage)                   │
│                                         │
│ ┌───────────────────┐                  │
│ │ CanTicket/        │                  │
│ │ canticket-laravel │ ← Original code  │
│ └───────────────────┘                  │
│           ▼ FORK (copy code)           │
│ ┌───────────────────┐                  │
│ │ YourAccount/      │                  │
│ │ buses-mvp         │ ← Your copy      │
│ └───────────────────┘                  │
└─────────────────────────────────────────┘

          ▼ DEPLOY (separate)

┌─────────────────────────────────────────┐
│ DREAMSCAPE SERVER 1                     │
│ ┌─────────────────────────────────────┐ │
│ │ canticket.com.au                    │ │
│ │ Database: canticket_db              │ │
│ │ - 500 users                         │ │
│ │ - 10,000 tasks                      │ │
│ └─────────────────────────────────────┘ │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ DREAMSCAPE SERVER 2                     │
│ ┌─────────────────────────────────────┐ │
│ │ buses.yourdomain.com                │ │
│ │ Database: buses_db ← DIFFERENT!     │ │
│ │ - 0 users (start fresh)             │ │
│ │ - 0 vehicles (new table)            │ │
│ └─────────────────────────────────────┘ │
└─────────────────────────────────────────┘

         ❌ NO CONNECTION BETWEEN DATABASES
```

---

## **Why Fork at All Then?**

### **Benefits of Forking:**

**1. Start with Proven Code**
- CanTicket already has authentication, time tracking, leave management
- You don't rebuild these from scratch
- Saves 8-10 weeks development time

**2. Get Updates** (Optional)
- If CanTicket fixes a security bug, you can merge that fix
- If CanTicket adds a new feature, you can optionally include it

**3. Maintain Separate Features**
- You add Buses-specific features (checklists, vehicles)
- CanTicket keeps their own features
- No conflicts

---

## **Recommended Approach for Buses**

### **Option A: Fork on GitHub (Code) + Separate Database**

```bash
# 1. Fork CanTicket code to your GitHub
https://github.com/CanTicket/canticket-laravel
→ Fork to: https://github.com/YourAccount/buses-mvp

# 2. Clone YOUR fork to your computer
git clone https://github.com/YourAccount/buses-mvp.git

# 3. Add Buses-specific files
- Add Vehicle model
- Add Checklist model
- Add new migrations
- Add new views

# 4. Deploy to Dreamscape with NEW database
- Create NEW database: buses_production_db
- Run migrations (creates empty tables)
- Add YOUR users, vehicles, data
- Zero connection to CanTicket database
```

**Result:**
✅ You have CanTicket's code foundation  
✅ You add Buses features on top  
✅ **Completely separate database**  
✅ Can deploy multiple times with different databases  

---

## **Multiple Deployments Example**

You can use the SAME code with DIFFERENT databases:

```
Your Code (GitHub):
└── buses-mvp (ONE codebase)

Your Deployments (Multiple):
├── Demo Server (Dreamscape Server 1)
│   ├── demo.buses.com
│   ├── Database: buses_demo_db
│   └── Data: Sample vehicles, fake checklists
│
├── Client 1 (Dreamscape Server 2)
│   ├── acmebus.com
│   ├── Database: acmebus_production_db
│   └── Data: Acme's vehicles, their checklists
│
└── Client 2 (Dreamscape Server 3)
    ├── citybus.com
    ├── Database: citybus_production_db
    └── Data: City's vehicles, their checklists
```

**Same code, 3 different databases - zero data sharing!**

---

## **FAQs**

**Q: If I fork CanTicket, will my clients see CanTicket data?**  
**A:** NO! Fork only copies code. Each deployment gets its own empty database.

**Q: If CanTicket adds a user, will it appear in my Buses database?**  
**A:** NO! Databases are completely separate. You control your own data.

**Q: Can CanTicket access my Buses database?**  
**A:** NO! They have zero access. You control your own server and database credentials.

**Q: If I change Buses code, does it affect CanTicket?**  
**A:** NO! Your fork is independent. Changes only affect your copy.

**Q: Do I need to fork, or can I just copy the code?**  
**A:** Either works! Fork keeps connection to original (can get updates). Copy is completely independent.

---

## **For Buses MVP: Recommended Strategy**

### **Best Approach:**

**1. Create Buses repository from scratch** (Don't fork)
   - Start with CanTicket files as base
   - Add Buses-specific features
   - No connection to CanTicket repository

**2. Deploy with brand new database**
   - Create `buses_production_db`
   - Run migrations (empty tables)
   - Add vehicles, users, data yourself

**3. Result:**
   - ✅ Clean codebase
   - ✅ Independent from CanTicket
   - ✅ Easy to customize
   - ✅ Easy to sell/share with clients
   - ✅ **ZERO database sharing**

---

## **Summary**

| Aspect | CanTicket | Buses |
|--------|-----------|-------|
| **Code (GitHub)** | Original repository | Your own repository (fork or copy) |
| **Database** | canticket_db | buses_db ← **DIFFERENT!** |
| **Server** | canticket.com.au | buses.yourdomain.com |
| **Users** | CanTicket users | Buses users (separate) |
| **Data** | CanTicket data | Buses data (separate) |
| **Connection** | - | **NONE** |

---

**Bottom Line:**

🎯 **Fork = Copy CODE, NOT data**  
🎯 **Each deployment = Own DATABASE**  
🎯 **Zero connection between databases**  
🎯 **You control your own data 100%**

---

**Questions?** Contact: cassandra@canticket.com



