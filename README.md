<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Local Development for This Project

Project ini sudah disiapkan untuk dijalankan lokal lewat Docker Compose.
Database lokal sekarang diarahkan ke PostgreSQL host container `postgres_alpine` dengan kredensial:

```text
host: host.docker.internal
port: 5432
database: postgres
username: postgres
password: postgres
```

Cara paling praktis adalah memakai helper script `dev.sh`, jadi Anda tidak perlu menghafal command Docker:

```bash
bash dev.sh up-build
```

Perintah yang tersedia:

```bash
bash dev.sh up
bash dev.sh up-build
bash dev.sh down
bash dev.sh reset
bash dev.sh test
bash dev.sh logs
bash dev.sh artisan migrate:status
bash dev.sh composer install
bash dev.sh npm run build
```

Admin panel akan tersedia di:

```text
http://localhost:8000/admin
```

Login default:

```text
email: admin@example.com
password: password
```

Vite dev server juga ikut jalan di:

```text
http://localhost:5173
```

Kalau tetap ingin memakai Docker Compose secara langsung:

```bash
docker compose up --build
docker compose run --rm app php artisan migrate:fresh --seed
docker compose run --rm app php artisan test
```

## Deploy ke Render

Project ini sudah disiapkan untuk deploy ke Render lewat Blueprint di [render.yaml](/home/abichos/Codes/project_yahya/emploi/render.yaml:1) dan runtime Docker production di [Dockerfile.render](/home/abichos/Codes/project_yahya/emploi/Dockerfile.render:1).

Yang akan dibuat di Render:

- `emploi-web` untuk web app Laravel/Filament
- `emploi-worker` untuk queue worker import/export
- `emploi-db` untuk PostgreSQL

### 1. Push repo ke GitHub/GitLab

Render akan membaca `render.yaml` dari root repo ini.

### 2. Buat APP_KEY

Generate key dari lokal:

```bash
bash dev.sh artisan key:generate --show
```

Simpan hasilnya. Nilai ini nanti dimasukkan ke env var `APP_KEY` saat sync Blueprint pertama.

### 3. Deploy Blueprint di Render

Di Render:

1. Klik `New +`
2. Pilih `Blueprint`
3. Hubungkan repo ini
4. Saat diminta, isi:
   - `APP_KEY`
   - `APP_URL`

Catatan:

- `APP_URL` isi dengan URL service web Anda, misalnya `https://emploi-web.onrender.com`
- `APP_URL` tidak bisa diisi otomatis dari `render.yaml`, jadi Anda perlu mengisinya sendiri

### 4. Cara kerja deploy production

- Web service memakai `sh ./docker/render/start-web.sh`
- Worker memakai `sh ./docker/render/start-worker.sh`
- Sebelum setiap deploy web, Render menjalankan:

```bash
sh ./docker/render/pre-deploy.sh
```

Script ini akan:

- menjalankan package discovery
- menjalankan migration production dengan `php artisan migrate --force`

### 5. Buat admin pertama di production

Setelah deploy sukses, buat user admin dari shell/one-off command Render:

```bash
php artisan tinker --execute="App\Models\User::updateOrCreate(['email' => 'admin@example.com'], ['name' => 'Admin', 'password' => 'password'])"
```

Karena model `User` memakai cast `hashed`, password plaintext di atas akan otomatis di-hash saat disimpan. Setelah login pertama, ganti password itu.

### 6. Verifikasi setelah deploy

Hal yang perlu dicek:

- buka `/up` untuk health check
- buka `/admin`
- login dengan user admin yang Anda buat
- coba create/edit data
- coba import CSV lalu pastikan worker memproses queue

### Catatan penting

- Local PostgreSQL `postgres_alpine` hanya untuk development lokal
- Production Render memakai database `emploi-db`
- Saat ini app belum menyimpan upload file dokumen, jadi storage object seperti S3 belum dibutuhkan
- Session, cache, dan queue tetap memakai database agar setup tetap sederhana
