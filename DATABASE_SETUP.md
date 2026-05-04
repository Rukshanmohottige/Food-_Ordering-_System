# Database Setup & Troubleshooting Guide

## Database Issues Fixed:

✓ Fixed all incorrect path references (removed `/food_order_system/`)
✓ Fixed database connection error messages
✓ Added proper error handling for queries
✓ Fixed all redirect paths to use relative paths
✓ Added validation for registration (password min 6 chars)
✓ Improved duplicate email detection

## Step-by-Step Setup:

### 1. Create Database
Open phpMyAdmin or MySQL command line and run:

```sql
CREATE DATABASE IF NOT EXISTS food_ordering_system;
USE food_ordering_system;
```

### 2. Create Tables
Copy and paste the entire contents of `db_structure.sql` into phpMyAdmin SQL tab and execute.

Or from MySQL command line:
```bash
mysql -u root -p food_ordering_system < db_structure.sql
```

### 3. Create Admin Users
Visit: `http://localhost/ORR/setup_admin.php`

This will automatically create:
- Admin: admin@canteen.com / admin123
- Staff: staff@canteen.com / admin123

**Delete setup_admin.php after this!**

## Database Connection Test

If you see "Database Connection Error", check:

1. **MySQL is running**
   - Windows: Check Services (search "Services" in taskbar)
   - Or start XAMPP and click Start Apache & MySQL

2. **Database exists**
   - Open phpMyAdmin at http://localhost/phpmyadmin
   - Look for "food_ordering_system" in left panel
   - If missing, run db_structure.sql

3. **Tables are created**
   - In phpMyAdmin, expand "food_ordering_system"
   - You should see: users, menu_items, orders, order_items, notifications, daily_reports

## Common Issues & Solutions

### Issue: "Connection failed"
**Solution:** 
- Make sure MySQL is running
- Check username/password in `config/database.php`

### Issue: "Table doesn't exist"
**Solution:**
- Run the db_structure.sql file in phpMyAdmin

### Issue: "Duplicate entry for email"
**Solution:**
- Email already registered - use different email or reset users table

### Issue: Login not working
**Solution:**
- Check if admin user was created: Visit `setup_admin.php`
- Make sure to delete old admin accounts first

## Database Structure

| Table | Purpose |
|-------|---------|
| users | Stores user accounts (student, staff, admin) |
| menu_items | Food items in menu |
| orders | Order records |
| order_items | Items in each order |
| notifications | User notifications |
| daily_reports | Daily sales reports |

## Important Notes

⚠️ **Security Note:** After setup, delete these files:
- setup_admin.php
- create_admin.sql

🔒 **Password Hashing:** All passwords are hashed with SHA256 before storage

📝 **Backup:** Always backup your database before making major changes

## Test the System

1. **Register new user:**
   - Go to http://localhost/ORR/public/register.php
   - Create account with test@example.com / password123

2. **Login as user:**
   - Go to http://localhost/ORR/public/login.php
   - Use credentials from step 1

3. **Place order:**
   - Go to Menu
   - Add menu items first (admin dashboard)
   - Place order from menu

4. **Admin panel:**
   - Go to http://localhost/ORR/admin/login.php
   - Use admin@canteen.com / admin123
   - Manage menu and orders

## Still Having Issues?

Check the error logs:
- XAMPP error log: `xampp/apache/logs/error.log`
- MySQL error log: `xampp/mysql/data/mysql_error.log`

Or check browser console (F12) for JavaScript errors.
