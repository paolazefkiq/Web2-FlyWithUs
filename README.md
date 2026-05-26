# Fly With Us - Web Project (Faza 2)

## Pershkrimi

`Fly With Us` eshte nje aplikacion i thjeshte web per shfaqjen e ofertave, rezervimin e fluturimeve dhe menaxhimin e te dhenave nga administratori. Projekti eshte realizuar ne kuader te lendes `Programimi ne ueb nga ana e Serverit`.

## Teknologjite

- PHP
- MySQL / MariaDB
- PDO me prepared statements
- HTML / CSS
- JavaScript
- XAMPP

## Struktura

- `index.php` - faqja kryesore me ofertat
- `login.php` - kycja
- `logout.php` - dalja
- `includes/` - konfigurimi, databaza, header, nav, footer, helper functions
- `classes/` - `User`, `Admin`, `Customer`
- `pages/` - faqet publike, dashboard-et dhe CRUD faqet
- `assets/` - CSS, JavaScript dhe imazhet
- `database/flywithus.sql` - databaza dhe te dhenat fillestare

## Setup

1. Vendoseni projektin ne:
   `C:\xampp\htdocs\Web2-FlyWithUs`
2. Startoni `Apache` dhe `MySQL` nga XAMPP.
3. Hapni phpMyAdmin dhe krijoni databazen:
   `flywithus`
4. Importoni skedarin:
   `database/flywithus.sql`
5. Sigurohuni qe MySQL po perdor portin `3307`, sepse kjo eshte porta e konfiguruar ne `includes/db.php`.
6. Hapni projektin ne browser:
   `http://localhost/Web2-FlyWithUs`

## Kredencialet demo

### Customer

- Email: `customer@flywithus.com`
- Username: `customer1`
- Password: `Customer123`

### Customer 2

- Email: `customer2@flywithus.com`
- Username: `customer2`
- Password: `Customer123`

### Admin

- Email: `admin@flywithus.com`
- Username: `admin1`
- Password: `Admin123`

## Databaza

Tabelat kryesore:

- `users`
- `destinations`
- `origin_cities`
- `routes`
- `bookings`
- `contact_messages`

Lidhjet kryesore:

- `users (1) -> (N) bookings`
- `users (1) -> (N) contact_messages`
- `origin_cities (1) -> (N) routes`
- `destinations (1) -> (N) routes`
- `routes (1) -> (N) bookings`

Tabela `bookings` perdor edhe kolonen `status` me vlerat `active` dhe `cancelled`.

## Funksionalitetet kryesore

### Pjesa publike

- Shfaq ofertat nga databaza
- Rendit ofertat sipas cmimit me te ulet
- Lejon zgjedhjen e qytetit te nisjes, destinacionit, datave dhe numrit te pasagjereve
- Llogarit cmimin dhe ruan rezervimin ne databaze
- Shfaq motin aktual per destinacionin me `Open-Meteo Weather API`

### Pjesa e customer

- Kyqje si customer
- Shfaq rezervimet ne dashboard
- Anulon rezervimin pa refresh te faqes
- Dergon mesazh nga forma e kontaktit

### Pjesa e admin

- Shfaq rezervimet, mesazhet dhe aktivitetin e fundit
- CRUD per `destinations`
- CRUD per `origin_cities`
- CRUD per `routes`
- Upload i imazheve per destinacionet

## Email

Forma e kontaktit perdoret nga customer-i i kycur.

Kur dergohet forma:

1. mesazhi ruhet ne `contact_messages`
2. projekti tenton dergimin e emailit me PHPMailer dhe Gmail SMTP

Per konfigurim duhen plotesuar ne `includes/config.php`:

- `smtp_username`
- `smtp_password`
- `smtp_from_email`
- `contact_inbox_email`

`contact_inbox_email` eshte inbox-i ku pranohen mesazhet.

## AJAX

Operacioni pa refresh eshte anulimi i rezervimit nga customer dashboard permes `fetch()`.

## Siguria dhe validimi

- `password_hash()` / `password_verify()`
- prepared statements me PDO
- output escaping me helper-in `e()`
- validim server-side
- role-based access per admin/customer
- sessione per auth dhe flash messages
- cookie per `preferred_city`

## Shenime

- Te dhenat dinamike vijne nga databaza.
- Te dhenat statike si FAQ, emri i faqes dhe kontaktet ruhen ne `includes/config.php`.
- Projekti perfshin PHPMailer ne folderin `vendor/`, prandaj nuk ka nevoje per instalim shtese nese ky folder eshte i pranishem.
