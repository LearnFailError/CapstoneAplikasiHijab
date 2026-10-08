# Hijab Store

Aplikasi toko hijab berbasis Laravel dengan katalog publik, pencarian produk menggunakan Laravel Scout + Typesense, keranjang tamu, checkout COD/transfer manual, dan panel admin.

## Menjalankan aplikasi di Dev Container

Dev Container menyediakan MySQL dan Typesense. Buka folder repositori ini di VS Code, lalu pilih **Reopen in Container**. Di terminal aplikasi:

```bash
cd /workspaces/CapstoneAplikasiHijab/hijab-store
composer install
test -f .env || cp .env.example .env
php artisan config:clear
php artisan key:generate
```

Pastikan koneksi Dev Container menunjuk ke layanan Compose (variabel environment Compose mengesampingkan nilai lokal dalam `.env`):

```dotenv
DB_HOST=db
TYPESENSE_HOST=typesense
SCOUT_DRIVER=typesense
```

### Database MySQL

Di Dev Container, database `hijab_store` dan user `hijab_store` dibuat otomatis oleh Compose. Aplikasi terhubung melalui host `db`; gunakan nilai berikut bila menjalankan aplikasi di layanan Compose:

```dotenv
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=hijab_store
DB_USERNAME=hijab_store
DB_PASSWORD=hijab_store_dev
```

Nilai password di atas hanya untuk development lokal. Untuk MySQL di luar Dev Container, ganti `DB_HOST`, nama database, user, dan password sesuai konfigurasi server MySQL.
Di dalam service aplikasi Compose, host MySQL adalah `db`. Compose juga memublikasikan MySQL ke `127.0.0.1:3306` pada mesin host agar perintah Artisan yang dijalankan dari terminal host dapat terhubung. Port tersebut hanya terikat ke loopback, bukan dibuka ke jaringan.
Jika `.env` yang sudah ada masih menggunakan SQLite, ubah variabel `DB_*` agar sesuai dengan layanan MySQL, lalu jalankan `php artisan config:clear` dan `php artisan migrate --seed`.

Jika terminal menampilkan `docker: command not found`, jangan jalankan perintah Docker dari dalam terminal aplikasi. Pastikan folder repositori `CapstoneAplikasiHijab` (folder yang berisi `.devcontainer`) terbuka di VS Code, lalu pilih **Codespaces: Rebuild Container** di Codespaces atau **Dev Containers: Rebuild and Reopen in Container** untuk container lokal. Compose akan menyalakan service `db` bersama aplikasi. Setelah terminal tersambung kembali ke container aplikasi, jalankan dari folder `hijab-store`:

```bash
php artisan config:clear
php artisan migrate --seed
```

Jika Docker tersedia di terminal host, jalankan perintah berikut dari root repositori, bukan dari folder `hijab-store`:

```bash
docker compose -f .devcontainer/docker-compose.yml up -d db
cd hijab-store
php artisan config:clear
php artisan migrate --seed
```

Di dalam container aplikasi, `php artisan config:show database.connections.mysql.host` seharusnya menampilkan `db`. Jika menampilkan `127.0.0.1`, terminal belum menggunakan environment service Compose; sambungkan/rebuild Dev Container terlebih dahulu.

Migration Laravel membuat tabel-tabel berikut; jangan membuat tabel secara manual:

| Tabel | Data utama |
| --- | --- |
| `users` | Akun admin/pengguna: nama, email, password, dan `is_admin`. |
| `categories` | Kategori hijab dengan nama dan slug unik. |
| `products` | Produk, kategori, deskripsi, bahan, warna, harga, stok, gambar, dan status aktif. `category_id` merujuk ke kategori. |
| `orders` | Data penerima/alamat, metode dan status pembayaran, status pesanan, subtotal, ongkir, total, serta `user_id` opsional untuk mengaitkan pesanan dengan akun pembeli. Checkout tamu tetap didukung. |
| `order_items` | Snapshot nama produk, kuantitas, harga satuan, dan total baris. Terhubung ke pesanan; item tetap menyimpan nama/harga jika produk dihapus. |
| `migrations` | Catatan migration yang sudah dijalankan Laravel. |
| `sessions`, `password_reset_tokens` | Penyimpanan sesi dan token reset password Laravel. |
| `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs` | Cache dan antrean Laravel sesuai konfigurasi aplikasi. |

Setelah masuk ke Dev Container, buat tabel dan data katalog contoh dengan:

```bash
php artisan config:clear
php artisan migrate --seed
php artisan migrate:status
```

Seeder mengisi kategori dan produk contoh. Akun admin hanya dibuat jika `ADMIN_EMAIL` dan `ADMIN_PASSWORD` sudah diatur.

Siapkan aplikasi dan katalog contoh:

```bash
composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan scout:import "App\\Models\\Product"
php artisan serve --host=0.0.0.0 --port=8000
```

Buka port 8000. Typesense tersedia di port 8108 untuk kebutuhan pengembangan; API key `xyz` hanya untuk lingkungan lokal. Pencarian menampilkan respons HTTP 503 yang informatif jika Typesense sedang tidak bisa dihubungi; halaman katalog tetap dapat dibuka.

Jika perubahan skema indeks Typesense menyebabkan impor produk gagal, backup/pastikan indeks bukan data produksi, hapus indeks pencarian lama lalu bangun ulang:

```bash
php artisan scout:delete-index products_index
php artisan scout:import "App\\Models\\Product"
```

Indeks adalah salinan data pencarian; database MySQL tetap sumber data utama.

### Cara kerja pencarian pintar

Model `Product` memakai Laravel Scout untuk menyinkronkan data produk ke koleksi `products_index` di Typesense. Nama, deskripsi, bahan, warna, dan kategori menjadi kolom pencarian; toleransi typo membantu menemukan produk saat kata kunci tidak diketik persis. Jenis kategori, bahan, warna, dan rentang harga diterapkan sebagai filter, sedangkan facet merangkum pilihan yang tersedia. MySQL tetap menjadi sumber data utama untuk katalog dan transaksi, sementara Typesense hanya mempercepat pencarian. Saat produk dikelola admin, Scout menyinkronkan perubahan ke indeks; `scout:import` membangun ulang indeks jika diperlukan.

## Menyiapkan akun admin

Atur kredensial admin lokal di `.env` sebelum menjalankan seeder. Gunakan kata sandi kuat dan jangan commit `.env`:

```dotenv
ADMIN_EMAIL=admin@contoh.test
ADMIN_PASSWORD=ganti-dengan-kata-sandi-kuat
```

Pendaftaran melalui `/register` selalu membuat akun pembeli; peran admin hanya diberikan melalui seeder dengan kredensial di atas. Pembeli dan admin masuk melalui halaman terpisah (`/login` dan `/admin/login`), dan akses ke halaman masing-masing dibatasi berdasarkan peran.

Lalu jalankan:

```bash
php artisan db:seed
```

Masuk melalui `/admin/login`. Seeder tidak membuat akun admin apabila salah satu variabel admin belum diatur. Akun pelanggan tidak memiliki akses panel.

## Alur aplikasi

- Pengunjung melihat produk/kategori, mencari dan memfilter katalog, serta melihat detail produk.
- Pengunjung dapat menambah/mengubah/menghapus isi keranjang tanpa membuat akun. Pembeli dapat mendaftar dan masuk melalui `/register` dan `/login`, lalu melihat riwayat pesanan akunnya di `/account`.
- Checkout meminta detail penerima dan pilihan COD atau transfer manual. Pesanan dan snapshot harga/item disimpan dalam transaksi database; stok dikunci dan dikurangi saat pesanan berhasil dibuat.
- Admin mengelola kategori, produk, stok, status pesanan, dan status pembayaran melalui `/admin`. Pesanan dapat dibatalkan untuk menolak permintaan; stok dikembalikan satu kali. Pesanan batal yang belum dibayar dapat dihapus permanen dari panel admin; order yang sudah dibayar tetap disimpan sebagai riwayat.
- Ongkos kirim belum terintegrasi dengan kurir; jumlahnya ditampilkan sebagai belum dikonfirmasi dan disepakati admin dengan pelanggan.
- Transfer manual belum memverifikasi pembayaran otomatis; admin menandai status pembayaran setelah memeriksanya.

## Pengujian

```bash
php artisan test --compact
```

Tes memakai SQLite sementara dan driver Scout `null`, sehingga tidak membutuhkan layanan MySQL/Typesense. Jika variabel shell `DB_CONNECTION=mysql` menimpa konfigurasi PHPUnit, jalankan tes dengan `DB_CONNECTION=sqlite DB_DATABASE=:memory: DB_URL= SCOUT_DRIVER=null php artisan test --compact`. Jalankan migrasi ke MySQL dan pastikan kedua layanan sehat sebelum uji coba aplikasi secara lokal.
