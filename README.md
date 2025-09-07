# 📘 CodeIgniter 3 Learning Project

## 🔹 Overview

Project ini dibuat untuk **belajar CodeIgniter 3** dengan PostgreSQL.  
Fitur yang dipelajari dan dibangun step by step:

- ✅ **User Management** → login, logout, role admin/staff.
- ✅ **Master Data Module** → users, customers, products, categories, suppliers, units.
- ✅ **Transaction Module** → contoh: _Sales Order_.
- ✅ **Reporting** → daftar & detail Sales Order.

---

## 🔹 Database Design

### Master Data Tables

- **users** → data internal (admin/staff).
- **customers** → pelanggan yang membeli produk.
- **products** → barang yang dijual.
- **categories** → kategori produk.
- **suppliers** → pemasok barang/bahan.
- **units** → satuan produk (pcs, box, dll).

### Transaction Tables

- **sales_orders** → header pesanan.
- **sales_order_details** → detail produk dalam pesanan.

## 🔹 Learning Steps

### 1. Setup Project

1. Install CodeIgniter 3.
2. Konfigurasi database di `application/config/database.php` untuk PostgreSQL.
3. Aktifkan migration (`$config['migration_enabled'] = TRUE;`).

### 2. User Management

- Buat migration tabel `users`.
- Implementasi login/logout.
- Simpan data user di session setelah login.

### 3. Master Data Module

- Migration & CRUD untuk:
  - `customers`
  - `products`
  - `categories`
  - `suppliers`
  - `units`

### 4. Sales Order Module

- Migration untuk `sales_orders` dan `sales_order_details`.
- Form input:
  - Pilih customer.
  - Tambahkan produk.
  - Simpan transaksi dengan `created_by` = user yang login.

### 5. Reporting

- Halaman daftar Sales Order.
- Detail Sales Order (produk, qty, harga).
- Filter by tanggal / customer.
- Export ke Excel / PDF (opsional).

---

## 🔹 Example Workflow

1. **Admin** login.
2. Admin input master data (kategori, produk, customer).
3. **Staff** login.
4. Staff buat Sales Order → pilih customer + produk.
5. Sistem simpan ke database.
6. Staff/Admin bisa lihat daftar pesanan & detailnya.

---

## 🔹 Next Learning Goals

- 🔒 Tambah **CSRF protection**.
- 👥 Role-based access control (admin vs staff).
- 🚀 Deploy ke server (Heroku / VPS / hosting).

---

## 🔹 Tech Stack

- **CodeIgniter 3**
- **PostgreSQL**
- **Bootstrap** (UI)
- **jQuery / DataTables** (frontend interaktif)

---

✍️ Dibuat untuk pembelajaran pribadi.
