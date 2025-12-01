# QR-Menu

MySQL kullanan, çok dilli, admin panelli gelişmiş QR menü scripti.

## Özellikler
- Çok dilli menü: İstediğiniz kadar dil ekleyip kategori/ürün başlık ve açıklamalarını çevirebilirsiniz.
- Admin paneli: Dilleri, kategorileri, ürünleri, çevirileri, stok ve rozetleri yönetebilir, kategori/ürün silebilirsiniz.
- Restoran bilgisi: Wi-Fi adı/şifresi, rezervasyon telefonu, adres, para birimi simgesi, marka renkleri ve logo alanları.
- Diyet/özellik etiketleri: Ürünleri acı, vegan, glutensiz veya şefin önerisi olarak işaretleyin; kalori ve görsel ekleyin.
- QR üretimi: Menü sayfasının bağlantısını otomatik QR kodu olarak üretir (api.qrserver.com kullanılır).
- Mobil uyumlu arayüz: Hem müşteri sayfası hem admin paneli için basit responsive tasarım.
- MySQL altyapısı: InnoDB tabloları ve otomatik kolon eklemeleriyle kolay kurulum.

## Kurulum
1. PHP 8+ ve PDO MySQL eklentisinin yüklü olduğundan emin olun.
2. Bir MySQL veritabanı oluşturun (örn. `qr_menu`) ve kullanıcıya yetki verin.
3. `src/config.php` dosyasında `database.dsn`, `database.user`, `database.password` bilgilerini MySQL sunucunuza göre güncelleyin.
4. Web sunucusunun kök dizini olarak `public` klasörünü ayarlayın veya geliştirme için `php -S localhost:8000 -t public` komutunu çalıştırın.
5. Varsayılan admin bilgileri:
   - Kullanıcı adı: `admin`
   - Şifre: `changeit`
   Şifreyi değiştirmek için `password_hash('yeniSifre', PASSWORD_DEFAULT)` çıktısını `src/config.php` içinde güncelleyin.

## Kullanım
- Müşteri görünümü: `http://localhost:8000` adresine gidip dil seçiciden istediğiniz dili seçin.
- Admin paneli: `http://localhost:8000/admin.php`
  - Dil ekleyin
  - Kategori oluşturun ve öne çıkarma/sıra numarası verin
  - Ürün ekleyin; stok, fiyat, kalori, görsel, diyet ve şefin önerisi rozetlerini ayarlayın
  - Kategori/ürün çevirilerini ekleyin veya güncelleyin
  - Marka renkleri, logo, Wi-Fi ve iletişim bilgilerini düzenleyin

İlk çalıştırmada tablolar ve varsayılan ayarlar otomatik oluşturulur.
