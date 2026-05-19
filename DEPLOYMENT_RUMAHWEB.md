# Deploy Backend Aurevina ke Rumahweb

## Repository

Gunakan repository backend:

```text
https://github.com/bajrulhakimi/AurevinaBE
```

## Struktur Hosting Rumahweb

Backend Laravel harus diarahkan ke folder:

```text
public
```

Jika memakai subdomain seperti `api.aurevina.com`, atur document root subdomain di cPanel/Rumahweb ke:

```text
/path-ke-project-aurevinabe/public
```

Jangan arahkan document root ke folder root Laravel, karena file seperti `.env`, `storage`, dan `vendor` tidak boleh terbuka publik.

## Environment Production

Buat file `.env` di server dari `.env.example`, lalu isi sesuai hosting Rumahweb:

```env
APP_NAME=Aurevina
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api-domain-rumahweb-anda.com
FRONTEND_URL=https://aurevina.vercel.app

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nama_database_rumahweb
DB_USERNAME=user_database_rumahweb
DB_PASSWORD=password_database_rumahweb

FILESYSTEM_DISK=public
QUEUE_CONNECTION=database
```

Untuk email verifikasi, jangan pakai `MAIL_MAILER=log` di production. Pakai SMTP Rumahweb atau SMTP lain:

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.domain-anda.com
MAIL_PORT=587
MAIL_USERNAME=email@domain-anda.com
MAIL_PASSWORD=password_email
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=email@domain-anda.com
MAIL_FROM_NAME="${APP_NAME}"
```

Jika Midtrans sudah siap:

```env
MIDTRANS_SERVER_KEY=server_key_anda
MIDTRANS_CLIENT_KEY=client_key_anda
MIDTRANS_IS_PRODUCTION=true
```

Notification URL Midtrans:

```text
https://api-domain-anda.com/api/v1/payment/midtrans/notification
```

## Command Setelah Upload

Jalankan melalui SSH/Terminal Rumahweb:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Pastikan folder berikut writable:

```text
storage
bootstrap/cache
```

## CORS

Pastikan `FRONTEND_URL` sama persis dengan domain Vercel frontend.

Contoh:

```env
FRONTEND_URL=https://aurevina.vercel.app
```

Kalau frontend memakai custom domain:

```env
FRONTEND_URL=https://aurevina.com
```

Jika memakai lebih dari satu domain, pisahkan dengan koma:

```env
FRONTEND_URL=https://aurevina.com,https://aurevina.vercel.app
```

## Tes Setelah Deploy

Cek API:

```text
https://api-domain-rumahweb-anda.com/api/v1/products
https://api-domain-rumahweb-anda.com/api/v1/store-stats
```

Kalau muncul JSON, backend sudah aktif.

Setelah itu buka frontend Vercel dan cek:

- Beranda
- Produk
- Detail produk
- Login/register
- Keranjang
- Checkout
- Admin dashboard
