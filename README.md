# ERP POS Plugin - Multi-tenant Point of Sale System for WordPress

[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-Required-purple.svg)](https://woocommerce.com/)
[![React](https://img.shields.io/badge/React-19.0-61dafb.svg)](https://react.dev/)
[![License](https://img.shields.io/badge/License-GPL--2.0-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

WordPress plugin lengkap untuk sistem Point of Sale (POS) multi-tenant yang terintegrasi dengan WooCommerce. Plugin ini memungkinkan Anda menjalankan beberapa tenant/toko dengan satu instalasi WordPress.

---

## 📋 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Teknologi](#-teknologi)
- [Persyaratan](#-persyaratan)
- [Instalasi](#-instalasi)
- [Konfigurasi](#-konfigurasi)
- [Struktur Database](#-struktur-database)
- [REST API Endpoints](#-rest-api-endpoints)
- [Build & Development](#-build--development)
- [Deployment](#-deployment)
- [Penggunaan](#-penggunaan)
- [Troubleshooting](#-troubleshooting)
- [Changelog](#-changelog)

---

## 🚀 Fitur Utama

### Backend Features
- ✅ **Multi-tenant System** - Kelola beberapa toko/kasir dalam satu instalasi
- ✅ **WooCommerce Integration** - Sinkronisasi produk, variasi, stok, dan kategori
- ✅ **User Management** - Role & permissions untuk cashier dan admin
- ✅ **Transaction Management** - Catat dan kelola semua transaksi penjualan
- ✅ **Receipt Generation** - Generate struk pembelian otomatis
- ✅ **Stock Management** - Auto-sync stok dengan WooCommerce
- ✅ **Barcode Support** - Scan barcode untuk mencari produk
- ✅ **Payment Methods** - Support multiple payment methods (Cash, Card, QRIS, dll)
- ✅ **Reports & Analytics** - Laporan penjualan lengkap

### Frontend Features
- ✅ **Modern React UI** - Built dengan React 19 dan Tailwind CSS
- ✅ **Product Grid** - Tampilan produk dengan search dan filter
- ✅ **Shopping Cart** - Keranjang belanja interaktif
- ✅ **Barcode Scanner** - Scan barcode menggunakan kamera atau input manual
- ✅ **Checkout System** - Proses checkout dengan multiple payment methods
- ✅ **Transaction History** - Lihat history transaksi dengan filter
- ✅ **Sales Dashboard** - Dashboard analytics dengan charts
- ✅ **Receipt Printing** - Print atau download struk pembelian
- ✅ **Responsive Design** - Optimized untuk desktop dan tablet

### Admin Features
- ✅ **Admin Dashboard** - Overview penjualan dan statistik
- ✅ **Tenant Management** - CRUD tenants dan assign users
- ✅ **Settings Panel** - Konfigurasi plugin dan receipt template
- ✅ **Transaction Reports** - Laporan transaksi lengkap dengan export
- ✅ **User Assignment** - Assign users ke tenant tertentu

---

## 🛠 Teknologi

### Backend
- **WordPress** - CMS Platform
- **WooCommerce** - E-commerce integration
- **PHP 7.4+** - Server-side language
- **MySQL/MariaDB** - Database

### Frontend
- **React 19.0** - UI Framework
- **Tailwind CSS 3.4** - Styling framework
- **React Hot Toast** - Toast notifications
- **Webpack 5** - Module bundler
- **Babel** - JavaScript compiler

---

## 📦 Persyaratan

### Server Requirements
- WordPress 5.0 atau lebih baru
- WooCommerce 5.0 atau lebih baru
- PHP 7.4 atau lebih baru
- MySQL 5.6 atau MariaDB 10.0 atau lebih baru

### Development Requirements
- Node.js 16.x atau lebih baru
- Yarn atau npm
- Git

---

## 💻 Instalasi

### 1. Clone Repository
```bash
git clone <repository-url>
cd erp-pos-wordpress-plugin
```

### 2. Install Dependencies
```bash
# Install Node.js dependencies
yarn install

# Atau menggunakan npm
npm install
```

### 3. Build Plugin
```bash
# Production build
yarn build

# Development build
yarn build:dev

# Watch mode untuk development
yarn watch
```

### 4. Upload ke WordPress

#### Method A: Upload Folder Plugin
1. Copy folder `wordpress-plugin` ke direktori WordPress Anda:
   ```bash
   cp -r wordpress-plugin /path/to/wordpress/wp-content/plugins/erp-pos-plugin
   ```

2. Login ke WordPress Admin
3. Navigate ke **Plugins → Installed Plugins**
4. Cari "ERP POS Plugin" dan klik **Activate**

#### Method B: Upload ZIP File
1. Compress folder `wordpress-plugin`:
   ```bash
   cd wordpress-plugin
   zip -r erp-pos-plugin.zip .
   ```

2. Login ke WordPress Admin
3. Navigate ke **Plugins → Add New → Upload Plugin**
4. Upload file ZIP dan klik **Install Now**
5. Klik **Activate Plugin**

---

## ⚙️ Konfigurasi

### 1. Aktivasi Plugin

Setelah aktivasi, plugin akan otomatis:
- Membuat 6 database tables
- Membuat custom role "ERP Cashier"
- Menambahkan custom capabilities
- Setup REST API endpoints

### 2. WooCommerce Dependency Check

Plugin **HARUS** menggunakan WooCommerce. Pastikan WooCommerce sudah terinstall dan aktif sebelum mengaktifkan plugin ini.

### 3. Setup Admin Pages

Navigate ke **ERP POS** di WordPress admin menu untuk:
- Manage tenants
- Configure settings
- View transactions
- Generate reports

### 4. Create Tenant

1. Go to **ERP POS → Tenants**
2. Click **Add New Tenant**
3. Isi informasi tenant (name, contact, address)
4. Save tenant

### 5. Assign Users to Tenant

1. Go to **ERP POS → Tenants**
2. Click **Edit** pada tenant yang ingin di-assign
3. Select user dari dropdown
4. Save assignment

### 6. Configure Receipt Settings

1. Go to **ERP POS → Settings**
2. Tab **Receipt Settings**
3. Customize:
   - Store name
   - Store address
   - Store phone
   - Footer message
   - Logo (optional)
4. Save settings

---

## 🗄 Struktur Database

Plugin membuat 6 custom tables:

### 1. `wp_erp_pos_tenants`
Menyimpan informasi tenant/toko

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| name | VARCHAR(255) | Nama tenant |
| contact | VARCHAR(100) | Kontak tenant |
| address | TEXT | Alamat tenant |
| settings | LONGTEXT | Settings JSON |
| created_at | DATETIME | Waktu dibuat |
| updated_at | DATETIME | Waktu update |

### 2. `wp_erp_pos_user_tenants`
Mapping user ke tenant

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| user_id | BIGINT | WordPress user ID |
| tenant_id | BIGINT | Tenant ID |
| role | VARCHAR(50) | Role dalam tenant |
| created_at | DATETIME | Waktu dibuat |

### 3. `wp_erp_pos_transactions`
Transaksi penjualan

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| tenant_id | BIGINT | Tenant ID |
| user_id | BIGINT | Cashier user ID |
| order_id | BIGINT | WooCommerce order ID |
| transaction_number | VARCHAR(50) | Nomor transaksi |
| total_amount | DECIMAL(10,2) | Total transaksi |
| tax_amount | DECIMAL(10,2) | Pajak |
| discount_amount | DECIMAL(10,2) | Diskon |
| payment_method | VARCHAR(50) | Metode pembayaran |
| status | VARCHAR(20) | Status transaksi |
| notes | TEXT | Catatan |
| created_at | DATETIME | Waktu transaksi |

### 4. `wp_erp_pos_transaction_items`
Detail item transaksi

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| transaction_id | BIGINT | Transaction ID |
| product_id | BIGINT | WooCommerce product ID |
| variation_id | BIGINT | Variation ID (optional) |
| quantity | INT | Jumlah |
| unit_price | DECIMAL(10,2) | Harga satuan |
| subtotal | DECIMAL(10,2) | Subtotal |
| tax | DECIMAL(10,2) | Pajak item |
| discount | DECIMAL(10,2) | Diskon item |

### 5. `wp_erp_pos_payments`
Detail pembayaran

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| transaction_id | BIGINT | Transaction ID |
| payment_method | VARCHAR(50) | Metode pembayaran |
| amount | DECIMAL(10,2) | Jumlah bayar |
| reference_number | VARCHAR(100) | Nomor referensi |
| created_at | DATETIME | Waktu pembayaran |

### 6. `wp_erp_pos_settings`
Settings tenant-specific

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| tenant_id | BIGINT | Tenant ID |
| setting_key | VARCHAR(100) | Key setting |
| setting_value | LONGTEXT | Value setting |
| updated_at | DATETIME | Waktu update |

---

## 🌐 REST API Endpoints

Base URL: `/wp-json/erp/v1/`

### Products & Categories

#### Get Products
```
GET /products
GET /products?category=5&search=laptop&stock=instock
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "name": "Product Name",
      "price": 100000,
      "regular_price": 120000,
      "stock_quantity": 50,
      "sku": "PROD-001",
      "barcode": "1234567890123",
      "image": "https://...",
      "categories": [...],
      "variations": [...]
    }
  ]
}
```

#### Get Categories
```
GET /categories
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 5,
      "name": "Electronics",
      "slug": "electronics",
      "count": 150
    }
  ]
}
```

### Orders & Transactions

#### Create Order
```
POST /order/create
```

**Payload:**
```json
{
  "items": [
    {
      "product_id": 123,
      "variation_id": 0,
      "quantity": 2,
      "price": 100000
    }
  ],
  "payment_method": "cash",
  "total": 200000,
  "customer": {
    "name": "John Doe",
    "phone": "081234567890"
  }
}
```

**Response:**
```json
{
  "success": true,
  "order_id": 456,
  "transaction_id": 789,
  "transaction_number": "TRX-20250117-001"
}
```

#### Get Transactions
```
GET /transactions
GET /transactions?start_date=2025-01-01&end_date=2025-01-31&tenant_id=1
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 789,
      "transaction_number": "TRX-20250117-001",
      "total_amount": 200000,
      "payment_method": "cash",
      "status": "completed",
      "created_at": "2025-01-17 10:30:00",
      "items": [...],
      "cashier": {...}
    }
  ],
  "total": 1,
  "page": 1
}
```

### Receipt

#### Get Receipt
```
GET /receipt/{transaction_id}
```

**Response:**
```html
<!-- HTML receipt untuk print -->
<div class="receipt">
  <!-- Receipt content -->
</div>
```

### Reports

#### Get Sales Reports
```
GET /reports
GET /reports?start_date=2025-01-01&end_date=2025-01-31&tenant_id=1
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_sales": 5000000,
    "total_transactions": 150,
    "average_transaction": 33333,
    "top_products": [...],
    "sales_by_day": {...},
    "payment_methods": {...}
  }
}
```

### Payment Methods

#### Get Available Payment Methods
```
GET /payment-methods
```

**Response:**
```json
{
  "success": true,
  "data": [
    { "id": "cash", "name": "Cash" },
    { "id": "card", "name": "Credit/Debit Card" },
    { "id": "qris", "name": "QRIS" },
    { "id": "bank_transfer", "name": "Bank Transfer" }
  ]
}
```

---

## 🔧 Build & Development

### Development Mode

```bash
# Watch mode - auto rebuild on file changes
yarn watch

# Development build (with source maps)
yarn build:dev
```

### Production Build

```bash
# Production build (minified, optimized)
yarn build
```

Output akan disimpan di:
- `wordpress-plugin/assets/erp-pos-app.js`
- `wordpress-plugin/assets/erp-pos-app.css`

### File Structure

```
erp-pos-wordpress-plugin/
├── src/                          # React source files
│   ├── components/              # React components
│   │   ├── ProductGrid.jsx     # Product listing
│   │   ├── CartPanel.jsx       # Shopping cart
│   │   ├── CheckoutModal.jsx   # Checkout form
│   │   ├── BarcodeScanner.jsx  # Barcode scanner
│   │   ├── TransactionHistory.jsx
│   │   └── SalesDashboard.jsx
│   ├── store/                   # State management
│   │   ├── ProductStore.jsx
│   │   ├── CartStore.jsx
│   │   └── TenantStore.jsx
│   ├── App.jsx                  # Main app component
│   ├── index.js                 # Entry point
│   ├── api.js                   # API functions
│   └── index.css                # Global styles
│
├── wordpress-plugin/            # WordPress plugin
│   ├── includes/               # PHP classes
│   │   ├── class-database.php  # Database handler
│   │   ├── class-tenant.php    # Tenant management
│   │   ├── class-permissions.php # User permissions
│   │   ├── class-woocommerce.php # WooCommerce integration
│   │   ├── class-api.php       # REST API endpoints
│   │   └── class-admin.php     # Admin pages
│   ├── admin/                  # Admin UI files
│   ├── assets/                 # Built assets (output)
│   │   ├── erp-pos-app.js
│   │   └── erp-pos-app.css
│   └── erp-pos-plugin.php      # Main plugin file
│
├── package.json                 # Node dependencies
├── webpack.config.js           # Webpack configuration
├── tailwind.config.js          # Tailwind configuration
├── .babelrc                    # Babel configuration
└── README.md                    # This file
```

---

## 🚀 Deployment

### Persiapan untuk Production

1. **Build production files**
   ```bash
   yarn build
   ```

2. **Test plugin di local WordPress**
   - Copy folder `wordpress-plugin` ke `/wp-content/plugins/`
   - Activate plugin
   - Test semua fitur

3. **Create deployment package**
   ```bash
   cd wordpress-plugin
   zip -r erp-pos-plugin-v1.0.0.zip . \
     -x "*.git*" "node_modules/*" "*.DS_Store"
   ```

### Upload ke Production WordPress

1. **Via WordPress Admin**
   - Login ke WordPress Admin
   - Go to Plugins → Add New → Upload Plugin
   - Upload ZIP file
   - Activate plugin

2. **Via FTP/SSH**
   ```bash
   # Upload folder via FTP/SFTP
   # Atau via SSH:
   scp -r wordpress-plugin user@server:/path/to/wordpress/wp-content/plugins/erp-pos-plugin
   ```

3. **Via Git Deployment**
   ```bash
   # Jika menggunakan git deployment
   git push production main
   ```

### Post-Deployment Checklist

- [ ] Plugin activated successfully
- [ ] Database tables created
- [ ] WooCommerce integration working
- [ ] Products loading correctly
- [ ] Cart functionality working
- [ ] Checkout process successful
- [ ] Transaction recorded
- [ ] Receipt generation working
- [ ] Admin pages accessible
- [ ] Reports displaying correctly

---

## 📖 Penggunaan

### Untuk Cashier (POS User)

1. **Login ke WordPress**
   - Gunakan credentials yang telah di-assign ke tenant

2. **Akses POS Interface**
   - Navigate ke page dengan shortcode `[erp_pos_app]`
   - Atau akses direct URL yang ditentukan admin

3. **Proses Transaksi**
   - Browse atau search produk
   - Scan barcode (optional)
   - Add produk ke cart
   - Adjust quantity
   - Klik "Checkout"
   - Pilih payment method
   - Confirm payment
   - Print receipt

4. **View Transaction History**
   - Klik tab "Transactions"
   - Filter by date, status, payment method
   - View detail transaksi
   - Reprint receipt

### Untuk Admin

1. **Manage Tenants**
   - Go to ERP POS → Tenants
   - Add/Edit/Delete tenants
   - Assign users to tenants

2. **View Reports**
   - Go to ERP POS → Reports
   - Select date range
   - Filter by tenant
   - Export reports (CSV/PDF)

3. **Configure Settings**
   - Go to ERP POS → Settings
   - General settings
   - Receipt template
   - Payment methods
   - Tax settings

4. **User Management**
   - Go to Users
   - Assign role "ERP Cashier" untuk cashier
   - Manage capabilities

---

## 🐛 Troubleshooting

### Plugin tidak muncul setelah upload

**Solusi:**
- Pastikan folder plugin bernama `erp-pos-plugin` (bukan `wordpress-plugin`)
- Check file permissions (755 untuk folder, 644 untuk files)
- Check PHP error logs: `/wp-content/debug.log`

### WooCommerce dependency error

**Error:** "ERP POS Plugin requires WooCommerce to be installed and active."

**Solusi:**
- Install dan activate WooCommerce terlebih dahulu
- Pastikan WooCommerce version 5.0+

### Products tidak loading

**Troubleshooting:**
1. Check apakah ada produk di WooCommerce
2. Check REST API:
   ```bash
   curl https://yoursite.com/wp-json/erp/v1/products
   ```
3. Check browser console untuk errors
4. Check PHP error logs

### Asset files (JS/CSS) tidak load

**Solusi:**
1. Rebuild assets:
   ```bash
   yarn build
   ```
2. Check file exists:
   ```bash
   ls wordpress-plugin/assets/
   ```
3. Clear WordPress cache
4. Hard refresh browser (Ctrl+Shift+R)

### Database tables tidak terbuat

**Solusi:**
1. Deactivate dan reactivate plugin
2. Atau jalankan manual via WordPress admin:
   ```php
   // Dalam wp-admin/admin.php?page=custom-page
   ERP_POS_Database::get_instance()->create_tables();
   ```

### Permission issues

**Error:** "You don't have permission to access this resource"

**Solusi:**
1. Check user role memiliki capability `use_erp_pos` atau `manage_erp_pos`
2. Check user sudah di-assign ke tenant
3. Reassign user capabilities:
   ```php
   $role = get_role('erp_cashier');
   $role->add_cap('use_erp_pos');
   ```

### Build errors

**Error:** "Module not found" atau webpack errors

**Solusi:**
1. Delete node_modules dan reinstall:
   ```bash
   rm -rf node_modules
   yarn install
   ```
2. Clear yarn cache:
   ```bash
   yarn cache clean
   ```
3. Update dependencies:
   ```bash
   yarn upgrade
   ```

---

## 📝 Changelog

### Version 1.0.0 (2025-01-17)

**Backend:**
- ✅ Initial release
- ✅ Multi-tenant system implementation
- ✅ WooCommerce full integration
- ✅ 6 database tables structure
- ✅ REST API 10+ endpoints
- ✅ User roles & permissions
- ✅ Receipt generation system
- ✅ Admin pages structure

**Frontend:**
- ✅ React 19 implementation
- ✅ Tailwind CSS styling
- ✅ Product grid dengan search & filter
- ✅ Shopping cart system
- ✅ Barcode scanner integration
- ✅ Checkout dengan multiple payment methods
- ✅ Transaction history view
- ✅ Sales dashboard & analytics
- ✅ Receipt printing

**Build System:**
- ✅ Webpack 5 configuration
- ✅ Production optimization
- ✅ Code splitting & minification
- ✅ Asset management

---

## 🤝 Contributing

Contributions are welcome! Please:
1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📄 License

This plugin is licensed under the GPL-2.0 License. See LICENSE file for details.

---

## 👥 Support

Untuk pertanyaan, bug reports, atau feature requests:
- Create an issue di GitHub repository
- Email: support@example.com
- Documentation: https://docs.example.com

---

## 🎯 Roadmap

### Planned Features (v1.1.0)
- [ ] Multi-language support (i18n)
- [ ] Customer management system
- [ ] Loyalty points program
- [ ] Advanced inventory management
- [ ] Email receipt support
- [ ] SMS notifications
- [ ] Advanced reporting with charts
- [ ] Export data to Excel/PDF
- [ ] Offline mode support
- [ ] Mobile responsive improvements

### Future Enhancements (v2.0.0)
- [ ] Multi-currency support
- [ ] Integration with payment gateways
- [ ] Staff performance tracking
- [ ] Product bundle support
- [ ] Promotion & discount system
- [ ] Kitchen display system (KDS)
- [ ] Table management (for restaurants)
- [ ] API webhooks

---

**Terakhir diupdate:** 17 Januari 2025  
**Version:** 1.0.0  
**Status:** Production Ready ✅
