<?php
require_once __DIR__ . '/../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Check if 'phone' column exists in 'contact_forms' table, if not, add it
try {
    $check_column_query = "SHOW COLUMNS FROM contact_forms LIKE 'phone'"; // For MySQL
    $stmt = $db->prepare($check_column_query);
    $stmt->execute();
    $phone_column_exists = $stmt->rowCount() > 0;

    if (!$phone_column_exists) {
        // Add the 'phone' column if it doesn't exist
        $alter_table_query = "ALTER TABLE contact_forms ADD COLUMN phone VARCHAR(20)";
        $db->exec($alter_table_query);
    }
} catch (PDOException $e) {
    // Handle potential errors, e.g., if table doesn't exist yet (though it should be created below)
    // Or if using a different DB that doesn't support PRAGMA (e.g., MySQL SHOW COLUMNS FROM)
    // For MySQL, you would use: SHOW COLUMNS FROM contact_forms LIKE 'phone'
    // If you get an error here, it might mean the table itself doesn't exist, which is handled later.
}

// Veritabanından iletişim bilgilerini çek
try {
    // İletişim bilgilerini çek
    $contact_query = "SELECT title, info, is_visible FROM contact_info";
    $contact_stmt = $db->query($contact_query);
    $contact_data = $contact_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Çalışma saatlerini çek
    $hours_query = "SELECT day_type, hours FROM working_hours";
    $hours_stmt = $db->query($hours_query);
    $hours_data = $hours_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch(PDOException $e) {
    // Hata durumunda varsayılan değerleri kullan
    $contact_data = [
        [
            'title' => 'E-posta',
            'info' => 'info@webdevpro.com',
            'is_visible' => 1
        ],
        [
            'title' => 'Telefon',
            'info' => '+90 (555) 123 4567',
            'is_visible' => 1
        ],
        [
            'title' => 'WhatsApp',
            'info' => '+90 (555) 123 4567',
            'is_visible' => 1
        ],
        [
            'title' => 'Adres',
            'info' => 'Levent Mahallesi<br />Büyükdere Caddesi No:123<br />Şişli, İstanbul',
            'is_visible' => 1
        ]
    ];
    
    $hours_data = [
        'weekday' => 'Pazartesi - Cuma: 09:00 - 18:00',
        'saturday' => 'Cumartesi: 10:00 - 16:00',
        'sunday' => 'Pazar: Kapalı'
    ];
}

// Veritabanından gelen verileri contact_info dizisine dönüştür
$contact_info = [];
foreach ($contact_data as $item) {
    // Görünür olmayan öğeleri atla
    if (!$item['is_visible']) {
        continue;
    }
    
    $icon = '';
    $link = '';
    $link_target = '';
    $button_class = '';
    $show_button = false;
    
    // Başlığa göre ikon belirle
    if ($item['title'] === 'E-posta') {
        $icon = '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>';
        $link = 'mailto:' . $item['info'];
    } elseif ($item['title'] === 'Telefon') {
        $icon = '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>';
        $link = 'tel:' . preg_replace('/[^0-9+]/', '', $item['info']);
    } elseif ($item['title'] === 'WhatsApp') {
        $icon = '<svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" style="color: #25D366;"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>';
        $link = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $item['info']);
        $link_target = '_blank';
        $button_class = 'bg-green-500 hover:bg-green-600';
        $show_button = true;
    } elseif ($item['title'] === 'Adres') {
        $icon = '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>';
    }
    
    $contact_info[] = [
        'icon' => $icon,
        'title' => $item['title'],
        'info' => $item['info'],
        'link' => $link,
        'link_target' => $link_target,
        'button_class' => $button_class,
        'show_button' => $show_button
    ];
}

// Form işleme
$message = '';
if ($_POST) {
    $name = htmlspecialchars($_POST['name'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $phone = htmlspecialchars($_POST['phone'] ?? '');
    $subject = htmlspecialchars($_POST['subject'] ?? '');
    $user_message = htmlspecialchars($_POST['message'] ?? '');
    
    if ($name && $email && $subject && $user_message) {
        try {
            // Önce contact_forms tablosunun var olup olmadığını kontrol et
            $check_table = "SHOW TABLES LIKE 'contact_forms'";
            $table_result = $db->query($check_table);
            
            if ($table_result->rowCount() == 0) {
                // Tablo yoksa oluştur
                $create_table = "CREATE TABLE contact_forms (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(100) NOT NULL,
                    phone VARCHAR(20),
                    subject VARCHAR(200) NOT NULL,
                    message TEXT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                $db->exec($create_table);
            }
            
            // Mesajı veritabanına kaydet
            $query = "INSERT INTO contact_forms (name, email, phone, subject, message) VALUES (:name, :email, :phone, :subject, :message)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':subject', $subject);
            $stmt->bindParam(':message', $user_message);
            
            if ($stmt->execute()) {
                $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">Mesajınız başarıyla gönderildi!</div>';
                // Form alanlarını temizle
                $_POST = [];
            } else {
                $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">Mesajınız gönderilirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.</div>';
            }
        } catch(PDOException $e) {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">Veritabanı hatası: ' . $e->getMessage() . '</div>';
        }
    } else {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">Lütfen tüm alanları doldurun.</div>';
    }
}
?>

<section id="contact" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">İletişim</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Projeniz hakkında konuşmak ve size nasıl yardımcı olabileceğimizi öğrenmek için bize ulaşın.
            </p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <div>
                <h3 class="text-2xl font-bold text-gray-900 mb-8">Bizimle İletişime Geçin</h3>
                
                <div class="space-y-6">
                    <?php foreach($contact_info as $info): ?>
                        <div class="flex items-center space-x-4">
                            <div class="bg-blue-600 text-white p-3 rounded-lg">
                                <?php echo $info['icon']; ?>
                            </div>
                            <div class="flex-grow">
                                <h4 class="font-semibold text-gray-900"><?php echo $info['title']; ?></h4>
                                <?php if (!empty($info['link'])): ?>
                                    <a href="<?php echo $info['link']; ?>" <?php echo !empty($info['link_target']) ? 'target="' . $info['link_target'] . '"' : ''; ?> class="text-gray-600 hover:text-blue-600 transition-colors">
                                        <?php echo $info['info']; ?>
                                    </a>
                                <?php else: ?>
                                    <p class="text-gray-600"><?php echo $info['info']; ?></p>
                                <?php endif; ?>
                            </div>
                            <?php if ($info['show_button']): ?>
                                <a href="<?php echo $info['link']; ?>" <?php echo !empty($info['link_target']) ? 'target="' . $info['link_target'] . '"' : ''; ?> class="px-4 py-2 rounded-lg text-white text-sm font-medium <?php echo $info['button_class'] ?: 'bg-blue-600 hover:bg-blue-700'; ?> transition-colors">
                                    Mesaj Gönder
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-8 p-6 bg-blue-50 rounded-xl">
                    <h4 class="font-semibold text-gray-900 mb-2">Çalışma Saatleri</h4>
                    <p class="text-gray-600">
                        <?php echo $hours_data['weekday'] ?? 'Pazartesi - Cuma: 09:00 - 18:00'; ?><br />
                        <?php echo $hours_data['saturday'] ?? 'Cumartesi: 10:00 - 16:00'; ?><br />
                        <?php echo $hours_data['sunday'] ?? 'Pazar: Kapalı'; ?>
                    </p>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl p-8 shadow-lg">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">Proje Teklifi Alın</h3>
                
                <?php echo $message; ?>
                
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Adınız
                            </label>
                            <input
                                type="text"
                                name="name"
                                value="<?php echo $_POST['name'] ?? ''; ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                required
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                E-posta
                            </label>
                            <input
                                type="email"
                                name="email"
                                value="<?php echo $_POST['email'] ?? ''; ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                required
                            />
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Telefon Numarası
                        </label>
                        <input
                            type="tel"
                            name="phone"
                            value="<?php echo $_POST['phone'] ?? ''; ?>"
                            placeholder="+90 (555) 123-4567"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Konu
                        </label>
                        <input
                            type="text"
                            name="subject"
                            value="<?php echo $_POST['subject'] ?? ''; ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            required
                        />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Mesajınız
                        </label>
                        <textarea
                            name="message"
                            rows="5"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            required
                        ><?php echo $_POST['message'] ?? ''; ?></textarea>
                    </div>
                    
                    <button
                        type="submit"
                        class="w-full btn-primary text-white py-4 px-6 rounded-lg font-medium flex items-center justify-center space-x-2"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        <span>Mesaj Gönder</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>