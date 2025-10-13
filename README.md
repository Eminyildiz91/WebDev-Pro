# Web Projesi (PHP & React)

Bu proje, PHP tabanlı bir web sitesi ile Vite tarafından yönetilen modern bir React ön yüz (frontend) uygulamasını bir araya getiren hibrit bir yapıdır. Sitenin blog, portfolyo, hizmetler gibi bölümleri ve içerik yönetimi için bir admin paneli bulunmaktadır.

## Özellikler

- **Blog Sistemi:** Yazı ekleme, düzenleme ve silme.
- **Portfolyo Yönetimi:** Projeleri sergilemek için bir bölüm.
- **Hizmetler Sayfası:** Sunulan hizmetlerin listelendiği bir alan.
- **İletişim Formu:** Ziyaretçilerden gelen mesajları toplama ve admin panelinde görüntüleme.
- **Admin Paneli:** Sitenin genel içeriğini (Hakkında, İletişim, Projeler vb.) yönetmek için bir arayüz.
- **SQLite Veritabanı:** Kolay kurulum ve taşınabilirlik için dosya tabanlı veritabanı.

## Kullanılan Teknolojiler

- **Backend:**
  - PHP
  - SQLite
- **Frontend (Klasik PHP Yapısı):**
  - HTML
  - CSS
  - JavaScript
- **Frontend (Modern Yapı):**
  - [React.js](https://reactjs.org/)
  - [Vite](https://vitejs.dev/)
  - [TypeScript](https://www.typescriptlang.org/)
  - [Tailwind CSS](https://tailwindcss.com/)

## Kurulum

Bu projeyi yerel makinenizde çalıştırmak için aşağıdaki adımları izleyin.

### Gereksinimler

- [MAMP](https://www.mamp.info/en/windows/), [XAMPP](https://www.apachefriends.org/index.html) veya benzeri bir yerel sunucu ortamı (PHP desteğiyle).
- [Node.js](https://nodejs.org/en/) ve npm.

### Adımlar

1.  **PHP Backend Kurulumu:**
    - Bu projeyi klonlayın veya indirin.
    - Proje dosyalarını MAMP/XAMPP içerisindeki `htdocs` veya `www` gibi web sunucusu kök dizinine yerleştirin.
    - Web sunucunuzu (Apache & MySQL) başlatın.
    - Tarayıcınızdan `http://localhost/web` (veya projenin bulunduğu klasör adı) adresine giderek siteyi görüntüleyin.
    - Admin paneline `http://localhost/web/admin` adresinden erişebilirsiniz. Kulanıcı ve Şifre admin admin123

2.  **React Frontend Kurulumu (Geliştirme için):**
    - Projenin ana dizininde bir terminal açın.
    - Gerekli Node.js paketlerini yüklemek için aşağıdaki komutu çalıştırın:
      ```bash
      npm install
      ```
    - Geliştirme sunucusunu başlatmak için:
      ```bash
      npm run dev
      ```
    - Bu komut, Vite geliştirme sunucusunu başlatacak ve genellikle `http://localhost:5173` gibi bir adreste çalışacaktır.

## Lisans

Bu proje [MIT Lisansı](LICENSE) ile lisanslanmıştır.
