# University Canteen — Database Setup Guide

## Quick Setup (XAMPP / MySQL)

1. Start **Apache** and **MySQL** in XAMPP Control Panel.
2. Open phpMyAdmin (`http://localhost/phpmyadmin`) or the MySQL CLI and import [`db_structure.sql`](db_structure.sql):
   ```bash
   mysql -u root < db_structure.sql
   ```
   This creates the `food_ordering_system` database, all 6 tables (`users`, `menu_items`, `orders`, `order_items`, `notifications`, `daily_reports`), and starter menu items if `menu_items` is empty.
3. If you already had an older `food_ordering_system` database, `config/database.php` automatically upgrades missing columns (`menu_items.category`, `menu_items.prep_minutes`, `menu_items.image_url`, `orders.notes`, `orders.updated_at`, `notifications.created_at`, and widens `users.password` to `VARCHAR(255)`) without deleting your existing records.
4. Visit `setup_admin.php` **once** (if no admin exists yet) to create the starter accounts:
   - **Admin:** `admin@canteen.com` / `admin123`
   - **Staff:** `staff@canteen.com` / `admin123`

## Password Security & Backward Compatibility

- New registrations and staff accounts use `password_hash()` (bcrypt).
- Existing accounts with older SHA-256 hashes still work on login and are automatically upgraded to `password_hash()` after signing in.
