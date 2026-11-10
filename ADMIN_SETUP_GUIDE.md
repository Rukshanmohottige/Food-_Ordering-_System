# Admin & Staff Guide

## Key Pages

- **Storefront:** `index.php` -> `public/index.php`
- **Menu & Cart:** `public/menu.php` and `public/cart.php`
- **Customer Orders & Alerts:** `public/my_orders.php`, `public/order.php`, `public/notifications.php`
- **Staff / Admin Login:** `admin/login.php`
- **Dashboard:** `admin/admin_dashboard.php`
- **Manage Menu (Add / Edit / Toggle / Delete):** `admin/menu_manage.php`
- **Manage Orders (Filter & Update Status):** `admin/orders_manage.php`
- **Sales Reports & Daily Snapshots:** `admin/reports.php`
- **Manage Staff & Admin Accounts (Admin only):** `admin/admin_register.php`

## Security Improvements

- All forms and state-changing actions (`cart`, `place_order`, `cancel_order`, `update_order_status`, `menu_manage`, `admin_register`) require `POST` + CSRF tokens.
- Staff/Admin pages and status update actions verify authenticated `admin` or `staff` roles via `includes/auth.php`.
- `admin/admin_register.php` is restricted to logged-in `admin` accounts.
- `setup_admin.php` automatically locks once at least one `admin` account exists.
