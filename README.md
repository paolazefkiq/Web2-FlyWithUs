# Fly With Us – Web Project (Faza 1)

## Përshkrimi i projektit

Ky projekt është realizuar në kuadër të lëndës “Programimi në ueb nga ana e Serverit” dhe paraqet një aplikacion web për rezervimin e fluturimeve.

Në Fazën 1, aplikacioni është zhvilluar duke përdorur PHP pa databazë, ku të gjitha të dhënat janë të simuluara (dummy). Projekti demonstron përdorimin e koncepteve bazë të PHP-së, programimit të orientuar në objekte, validimit server-side, si dhe menaxhimit të sesioneve dhe cookies.

## Struktura e projektit

Projekti është i organizuar në mënyrë të qartë në folderë sipas funksionalitetit:

* `index.php` – faqja kryesore me formën e rezervimit
* `login.php` – forma për kyçje
* `logout.php` – dalje nga sistemi

Folderët kryesorë:

* `includes/`

  * `config.php` – konfigurimi dhe të dhënat dummy
  * `header.php` – pjesa e sipërme e faqes
  * `nav.php` – navigimi
  * `footer.php` – pjesa e poshtme e faqes

* `classes/`

  * `User.php` – klasa bazë
  * `Admin.php` – klasa për administratorin
  * `Customer.php` – klasa për përdoruesin e zakonshëm

* `pages/`

  * `about.php`
  * `contact.php`
  * `faq.php`
  * `admin-dashboard.php`
  * `customer-dashboard.php`

* `assets/`

  * CSS, JavaScript dhe imazhe

## Ekzekutimi i projektit

1. Instaloni dhe hapni XAMPP
2. Vendosni folderin e projektit në:
   `C:\xampp\htdocs\`
3. Sigurohuni që folderi quhet:
   `Web2-FlyWithUs`
4. Startoni Apache në XAMPP
5. Hapni browser dhe shkoni te:
   `http://localhost/Web2-FlyWithUs`

## Login

Aplikacioni përdor kredenciale statike të ruajtura në kod.

Customer:

* Email: [customer@flywithus.com](mailto:customer@flywithus.com)
* Username: customer1
* Password: Customer123

Admin:

* Email: [admin@flywithus.com](mailto:admin@flywithus.com)
* Username: admin1
* Password: Admin123

## Përputhja me kërkesat e Fazës 1
### 1. Struktura e aplikacionit

* Projekti përmban më shumë se 4 faqe funksionale
* Përdoret include/require për header, navigim dhe footer
* Folderët janë të organizuar në mënyrë të qartë (pages, includes, classes, assets)

### 2. Login/Logout pa databazë

* Kredencialet janë të ruajtura në kod (hardcoded)
* Nuk përdoret databazë
* Përdoret session për ruajtjen e gjendjes së përdoruesit
* Implementohen dy role: admin dhe customer
* Qasja ndryshon në bazë të rolit (dashboard-e të ndryshme)

### 3. Konceptet bazë të PHP

* Përdorim i variablave dhe variablave globale
* Funksione ndihmëse (helper functions)
* Struktura kushtore (if/else)
* Cikle (foreach, for)
* Arrays:

  * numeric arrays
  * associative arrays
  * multidimensional arrays
* Sortime me funksionin usort()

### 4. OOP në PHP

* Klasa të implementuara: User, Admin, Customer
* Konstruktor në klasën bazë
* Metoda get dhe set
* Enkapsulim përmes përdorimit të protected properties
* Trashëgimi (Admin dhe Customer zgjerojnë klasën User)

### 5. Validimi me RegEx

* Validim server-side në PHP
* Validim për:

  * emër
  * email
  * username

### 6. Sessions dhe Cookies

* Përdorimi i session për:

  * ruajtjen e përdoruesit të kyçur
  * të dhënat e rezervimit
  * mesazhe të formave
* Përdorimi i cookies për:

  * ruajtjen e preferencës së qytetit

## Funksionalitetet kryesore
* Formë për rezervim në faqen kryesore
* Validim server-side me shfaqje të gabimeve
* Popup për konfirmimin e rezervimit
* Contact form me validim dhe feedback
* Dashboard për admin dhe customer
* Faqe informative si About dhe FAQ

## Shënime
* Projekti nuk përdor databazë në Fazën 1
* Të gjitha të dhënat janë të simuluara
* Rrugët janë të bazuara në base_url të konfiguruar në projekt

## Dorëzimi
Dorëzimi përfshin:

* Kodin e projektit
* README me udhëzime
* Screenshot ose video demonstrim të funksionaliteteve