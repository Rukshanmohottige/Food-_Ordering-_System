# Free Hosting Guide — InfinityFree (PHP 8.2+ & MySQL)

Your deployment package (`D:\Projects\food_order_deploy.zip`) is ready to upload without changing any application code.

## Step 1 — Create Free Hosting Account
1. Go to **https://www.infinityfree.com** and register / sign in.
2. Click **Create Account**, pick a free subdomain (for example `mycampus-canteen.infinityfreeapp.com`), and finish creating the hosting account.
3. Open **Control Panel** → **Alter PHP Config** (or **Select PHP Version**) and ensure **PHP 8.2** (or **8.3**) is active.

## Step 2 — Create Your MySQL Database
1. In the **Control Panel**, open **MySQL Databases**.
2. Create a database (for example `canteen`) and copy these 4 details:
   - **MySQL Host Name** (e.g. `sql123.infinityfree.com`)
   - **MySQL User Name** (e.g. `if0_12345678`)
   - **MySQL Password** (your vPanel/hosting password)
   - **MySQL Database Name** (e.g. `if0_12345678_canteen`)

## Step 3 — Import the Database Tables
1. Next to your new database in **MySQL Databases**, click **phpMyAdmin**.
2. Click the **Import** tab, choose **`db_structure_shared_hosting.sql`**, and click **Import** (or **Go**).
   - This creates all 6 tables (`users`, `menu_items`, `orders`, `order_items`, `notifications`, `daily_reports`) plus the 8 menu dishes and their image paths.

## Step 4 — Upload Files to `htdocs/`
1. Extract **`D:\Projects\food_order_deploy.zip`** to a temporary folder on your PC.
2. Connect using **FileZilla** (using your InfinityFree FTP Host, Username, Password from Client Area) or open the online **File Manager**.
3. Upload **everything inside** the extracted folder directly into **`htdocs/`** (so `htdocs/index.php`, `htdocs/public/`, `htdocs/admin/`, `htdocs/assets/`, etc. sit directly inside `htdocs/`).
   - *Tip: FileZilla is much faster and more reliable than the web File Manager when uploading images.*

## Step 5 — Connect Database Credentials
1. Inside `htdocs/config/`, rename `db_production.example.php` to **`db_production.php`** (or create `config/db_production.php`).
2. Edit the 4 values inside `htdocs/config/db_production.php`:
   ```php
   return [
       'host' => 'YOUR_MYSQL_HOST',
       'user' => 'YOUR_MYSQL_USER',
       'pass' => 'YOUR_MYSQL_PASSWORD',
       'db'   => 'YOUR_MYSQL_DB_NAME',
   ];
   ```

## Step 6 — Initialize Accounts & Test
1. Visit `https://your-subdomain.infinityfreeapp.com/setup_admin.php` **once** in your browser.
   - Creates `admin@canteen.com` (`admin123`), `staff@canteen.com` (`admin123`), and `student@canteen.com` (`student123`).
   - `setup_admin.php` locks itself automatically (`403`) once an admin exists.
2. Visit `https://your-subdomain.infinityfreeapp.com/` and sign in to the Admin Portal to change the default passwords.
