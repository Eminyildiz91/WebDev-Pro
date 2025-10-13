<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Veritabanı bağlantısını dahil et
require_once '../config/database.php';

try {
    // Hata ayıklama mesajı
    
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Veritabanı bağlantısı kurulamadı.");
    }
} catch(Exception $e) {
    $_SESSION['error_message'] = 'Veritabanı bağlantı hatası: ' . $e->getMessage();
    // Bağlantı hatası durumunda varsayılan değerlerle devam et
    $db = null;
}

// Mevcut verileri veritabanından çek
if ($db) {
    try {
        // Önce contact_info tablosunda is_visible sütununun varlığını kontrol et
        $column_exists = false;
        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) == 'mysql') {
            $check_column = $db->query("SHOW COLUMNS FROM contact_info LIKE 'is_visible'");
            $column_exists = $check_column->rowCount() > 0;
        } elseif ($db->getAttribute(PDO::ATTR_DRIVER_NAME) == 'sqlite') {
            $check_column = $db->query("PRAGMA table_info(contact_info)");
            foreach ($check_column->fetchAll(PDO::FETCH_ASSOC) as $column) {
                if ($column['name'] == 'is_visible') {
                    $column_exists = true;
                    break;
                }
            }
        }
        
        // İletişim bilgilerini çek
        if ($column_exists) {
            $contact_query = "SELECT title, info, is_visible FROM contact_info";
        } else {
            $contact_query = "SELECT title, info FROM contact_info";
        }
        
        $contact_stmt = $db->query($contact_query);
        $contact_data = $contact_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // is_visible sütunu yoksa, varsayılan olarak görünür yap
        if (!$column_exists) {
            foreach ($contact_data as &$item) {
                $item['is_visible'] = 1;
            }
        }
        
        // Çalışma saatlerini çek
        $hours_query = "SELECT day_type, hours FROM working_hours";
        $hours_stmt = $db->query($hours_query);
        $hours_data = $hours_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch(PDOException $e) {
        $_SESSION['error_message'] = 'Veri çekme sırasında bir hata oluştu: ' . $e->getMessage();
        $contact_data = [
            ['title' => 'E-posta', 'info' => '', 'is_visible' => 1],
            ['title' => 'Telefon', 'info' => '', 'is_visible' => 1],
            ['title' => 'WhatsApp', 'info' => '', 'is_visible' => 1],
            ['title' => 'Adres', 'info' => '', 'is_visible' => 1]
        ];
        $hours_data = [
            'weekday' => '',
            'saturday' => '',
            'sunday' => ''
        ];
    }
} else {
    // Veritabanı bağlantısı yoksa varsayılan değerleri kullan
    if (!isset($_SESSION['error_message'])) {
        $_SESSION['error_message'] = 'Veritabanı bağlantısı kurulamadı. Varsayılan değerler gösteriliyor.';
    }
    $contact_data = [
        ['title' => 'E-posta', 'info' => 'info@webdevpro.com', 'is_visible' => 1],
        ['title' => 'Telefon', 'info' => '+90 (555) 123 4567', 'is_visible' => 1],
        ['title' => 'WhatsApp', 'info' => '+90 (555) 123 4567', 'is_visible' => 1],
        ['title' => 'Adres', 'info' => 'Levent Mahallesi\nBüyükdere Caddesi No:123\nŞişli, İstanbul', 'is_visible' => 1]
    ];
    $hours_data = [
        'weekday' => '09:00 - 18:00',
        'saturday' => '10:00 - 16:00',
        'sunday' => 'Kapalı'
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Form verilerini al
    $contact_info = [
        [
            'title' => 'E-posta',
            'info' => $_POST['email'],
            'is_visible' => isset($_POST['email_visible']) ? 1 : 0
        ],
        [
            'title' => 'Telefon',
            'info' => $_POST['phone'],
            'is_visible' => isset($_POST['phone_visible']) ? 1 : 0
        ],
        [
            'title' => 'WhatsApp',
            'info' => $_POST['whatsapp'],
            'is_visible' => isset($_POST['whatsapp_visible']) ? 1 : 0
        ],
        [
            'title' => 'Adres',
            'info' => $_POST['address'],
            'is_visible' => isset($_POST['address_visible']) ? 1 : 0
        ]
    ];
    
    $working_hours = [
        'weekday' => $_POST['weekday_hours'],
        'saturday' => $_POST['saturday_hours'],
        'sunday' => $_POST['sunday_hours']
    ];
    
    if ($db) {
        try {

            
            // Önce contact_info tablosunda is_visible sütununun varlığını kontrol et
            $column_exists = false;
            if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) == 'mysql') {
                $check_column = $db->query("SHOW COLUMNS FROM contact_info LIKE 'is_visible'");
                $column_exists = $check_column->rowCount() > 0;

            } elseif ($db->getAttribute(PDO::ATTR_DRIVER_NAME) == 'sqlite') {
                $check_column = $db->query("PRAGMA table_info(contact_info)");
                $columns = $check_column->fetchAll(PDO::FETCH_ASSOC);

                
                foreach ($columns as $column) {
                    if ($column['name'] == 'is_visible') {
                        $column_exists = true;
                        break;
                    }
                }

            }
            
            // İletişim bilgilerini güncelle

            
            // Önce mevcut iletişim bilgilerini kontrol et
            $check_contact_query = "SELECT id, title, info" . ($column_exists ? ", is_visible" : "") . " FROM contact_info";
            $check_contact_stmt = $db->query($check_contact_query);
            $current_contacts = $check_contact_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Hata ayıklama için log dosyasına yaz
            $log_file = fopen(__DIR__ . '/../contact_update_log.txt', 'a');
            fwrite($log_file, "\n\n" . date('Y-m-d H:i:s') . " - Güncelleme başlatıldı\n");
            fwrite($log_file, "Mevcut iletişim bilgileri: " . json_encode($current_contacts) . "\n");
            
            if ($column_exists) {
                $contact_query = "UPDATE contact_info SET info = :info, is_visible = :is_visible WHERE title = :title";
                $contact_stmt = $db->prepare($contact_query);
                
                foreach ($contact_info as $item) {
                    $title = $item['title'];
                    $info = $item['info'];
                    $is_visible = $item['is_visible'];
                    
                    $contact_stmt->bindParam(':title', $title);
                    $contact_stmt->bindParam(':info', $info);
                    $contact_stmt->bindParam(':is_visible', $is_visible);
                    
                    $result = $contact_stmt->execute();
                    $affected_rows = $contact_stmt->rowCount();
                    
                    // Etkilenen satır yoksa, doğrudan ID ile güncellemeyi dene
                    if ($affected_rows == 0 && $result) {
                        // title değerine göre ID'yi bul
                        $id = 0;
                        foreach ($current_contacts as $contact) {
                            if ($contact['title'] == $title) {
                                $id = $contact['id'];
                                break;
                            }
                        }
                        
                        if ($id > 0) {
                            $direct_query = "UPDATE contact_info SET info = :info, is_visible = :is_visible WHERE id = :id";
                            $direct_stmt = $db->prepare($direct_query);
                            $direct_stmt->bindParam(':info', $info);
                            $direct_stmt->bindParam(':is_visible', $is_visible);
                            $direct_stmt->bindParam(':id', $id);
                            $direct_result = $direct_stmt->execute();
                            $direct_affected = $direct_stmt->rowCount();
                            

                            fwrite($log_file, "ID ile güncelleniyor: {$title} (ID: {$id}) = {$info}, görünür: {$is_visible}, sonuç: " . ($direct_result ? 'başarılı' : 'başarısız') . " (Etkilenen: {$direct_affected})\n");
                        } else {
    
                            fwrite($log_file, "HATA: {$title} için ID bulunamadı!\n");
                        }
                    }
                    
                    // Hata ayıklama için log dosyasına yaz
                    fwrite($log_file, "Güncelleniyor: {$title} = {$info}, görünür: {$is_visible}, sonuç: " . ($result ? 'başarılı' : 'başarısız') . " (Etkilenen: {$affected_rows})\n");
                    if (!$result) {
                        fwrite($log_file, "Hata: " . implode(", ", $contact_stmt->errorInfo()) . "\n");
                    }
                }
            } else {
                $contact_query = "UPDATE contact_info SET info = :info WHERE title = :title";
                $contact_stmt = $db->prepare($contact_query);
                
                foreach ($contact_info as $item) {
                    $title = $item['title'];
                    $info = $item['info'];
                    
                    $contact_stmt->bindParam(':title', $title);
                    $contact_stmt->bindParam(':info', $info);
                    $result = $contact_stmt->execute();
                    $affected_rows = $contact_stmt->rowCount();
                    
                    // Etkilenen satır yoksa, doğrudan ID ile güncellemeyi dene
                    if ($affected_rows == 0 && $result) {
                        // title değerine göre ID'yi bul
                        $id = 0;
                        foreach ($current_contacts as $contact) {
                            if ($contact['title'] == $title) {
                                $id = $contact['id'];
                                break;
                            }
                        }
                        
                        if ($id > 0) {
                            $direct_query = "UPDATE contact_info SET info = :info WHERE id = :id";
                            $direct_stmt = $db->prepare($direct_query);
                            $direct_stmt->bindParam(':info', $info);
                            $direct_stmt->bindParam(':id', $id);
                            $direct_result = $direct_stmt->execute();
                            $direct_affected = $direct_stmt->rowCount();
                            

                            fwrite($log_file, "ID ile güncelleniyor: {$title} (ID: {$id}) = {$info}, sonuç: " . ($direct_result ? 'başarılı' : 'başarısız') . " (Etkilenen: {$direct_affected})\n");
                        } else {
    
                            fwrite($log_file, "HATA: {$title} için ID bulunamadı!\n");
                        }
                    }
                    
                    // Hata ayıklama için log dosyasına yaz
                    fwrite($log_file, "Güncelleniyor: {$title} = {$info}, sonuç: " . ($result ? 'başarılı' : 'başarısız') . " (Etkilenen: {$affected_rows})\n");
                    if (!$result) {
                        fwrite($log_file, "Hata: " . implode(", ", $contact_stmt->errorInfo()) . "\n");
                    }
                }
            }
            
            if (isset($log_file) && is_resource($log_file)) {
                fclose($log_file);
            }
            
            // Çalışma saatlerini güncelle
            $hours_query = "UPDATE working_hours SET hours = :hours WHERE day_type = :day_type";
            $hours_stmt = $db->prepare($hours_query);
            
            // Hata ayıklama için log dosyasına yaz
            if (isset($log_file) && is_resource($log_file)) {
                fwrite($log_file, "\nÇalışma saatleri güncelleniyor:\n");
            } else {
                $log_file = fopen(__DIR__ . '/../contact_update_log.txt', 'a');
                fwrite($log_file, "\n\n" . date('Y-m-d H:i:s') . " - Çalışma saatleri güncelleniyor\n");
            }
            
            // Önce mevcut çalışma saatlerini kontrol et
            $check_query = "SELECT id, day_type, hours FROM working_hours";
            $check_stmt = $db->query($check_query);
            $current_hours = $check_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($working_hours as $day_type => $hours) {
                $hours_stmt->bindParam(':day_type', $day_type);
                $hours_stmt->bindParam(':hours', $hours);
                $result = $hours_stmt->execute();
                $affected_rows = $hours_stmt->rowCount();
                
                // Etkilenen satır yoksa, doğrudan ID ile güncellemeyi dene
                if ($affected_rows == 0 && $result) {
                    // day_type değerine göre ID'yi bul
                    $id = 0;
                    foreach ($current_hours as $hour) {
                        if ($hour['day_type'] == $day_type) {
                            $id = $hour['id'];
                            break;
                        }
                    }
                    
                    if ($id > 0) {
                        $direct_query = "UPDATE working_hours SET hours = :hours WHERE id = :id";
                        $direct_stmt = $db->prepare($direct_query);
                        $direct_stmt->bindParam(':hours', $hours);
                        $direct_stmt->bindParam(':id', $id);
                        $direct_result = $direct_stmt->execute();
                        $direct_affected = $direct_stmt->rowCount();
                        

                        fwrite($log_file, "ID ile güncelleniyor: {$day_type} (ID: {$id}) = {$hours}, sonuç: " . ($direct_result ? 'başarılı' : 'başarısız') . " (Etkilenen: {$direct_affected})\n");
                    } else {

                        fwrite($log_file, "HATA: {$day_type} için ID bulunamadı!\n");
                    }
                }
                
                // Hata ayıklama için log dosyasına yaz
                fwrite($log_file, "Güncelleniyor: {$day_type} = {$hours}, sonuç: " . ($result ? 'başarılı' : 'başarısız') . " (Etkilenen: {$affected_rows})\n");
                if (!$result) {
                    fwrite($log_file, "Hata: " . implode(", ", $hours_stmt->errorInfo()) . "\n");
                }
            }
            
            if (isset($log_file) && is_resource($log_file)) {
                fclose($log_file);
            }
            
            $_SESSION['success_message'] = 'İletişim bilgileri başarıyla güncellendi.';
        } catch(PDOException $e) {
            $_SESSION['error_message'] = 'Güncelleme sırasında bir hata oluştu: ' . $e->getMessage();
        }
    } else {
        // Veritabanı bağlantısı yoksa
        $_SESSION['error_message'] = 'Veritabanı bağlantısı kurulamadığı için değişiklikler kaydedilemedi. ' . 
                                    'Lütfen veritabanı bağlantınızı kontrol edin veya sistem yöneticisiyle iletişime geçin.';
        
        // Kullanıcının girdiği verileri session'a kaydedelim, böylece sayfa yenilendiğinde kaybolmaz
        $_SESSION['form_data'] = [
            'contact_info' => $contact_info,
            'working_hours' => $working_hours
        ];
    }
    
    header('Location: edit-contact.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İletişim Düzenle - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900">İletişim Düzenle</h1>
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="text-gray-600 hover:text-gray-900">Dashboard'a Dön</a>
                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-red-600 hover:text-red-900">Çıkış Yap</a>
                    <form id="logout-form" action="index.php" method="POST" class="hidden">
                        <input type="hidden" name="logout" value="1">
                    </form>
                </div>
            </div>
        </header>

        <!-- Ana İçerik -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['success_message']); ?></span>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['error_message']); ?></span>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['debug_message'])): ?>
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">Debug: <?php echo htmlspecialchars($_SESSION['debug_message']); ?></span>
                </div>
                <?php unset($_SESSION['debug_message']); ?>
            <?php endif; ?>

            <form action="edit-contact.php" method="POST" class="space-y-6">
                <!-- İletişim Bilgileri -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-6">İletişim Bilgileri</h2>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex-grow">
                                <label class="block text-sm font-medium text-gray-700">E-posta</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($contact_data[0]['info'] ?? ''); ?>" class="mt-1 block w-full border rounded-md shadow-sm p-2" required>
                            </div>
                            <div class="ml-4 flex items-center">
                                <input type="checkbox" name="email_visible" id="email_visible" <?php echo ($contact_data[0]['is_visible'] ?? 1) ? 'checked' : ''; ?> class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="email_visible" class="ml-2 block text-sm text-gray-700">Görünür</label>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex-grow">
                                <label class="block text-sm font-medium text-gray-700">Telefon</label>
                                <input type="text" name="phone" value="<?php echo htmlspecialchars($contact_data[1]['info'] ?? ''); ?>" class="mt-1 block w-full border rounded-md shadow-sm p-2" required>
                            </div>
                            <div class="ml-4 flex items-center">
                                <input type="checkbox" name="phone_visible" id="phone_visible" <?php echo ($contact_data[1]['is_visible'] ?? 1) ? 'checked' : ''; ?> class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="phone_visible" class="ml-2 block text-sm text-gray-700">Görünür</label>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex-grow">
                                <label class="block text-sm font-medium text-gray-700">WhatsApp</label>
                                <input type="text" name="whatsapp" value="<?php echo htmlspecialchars($contact_data[2]['info'] ?? ''); ?>" class="mt-1 block w-full border rounded-md shadow-sm p-2" required>
                            </div>
                            <div class="ml-4 flex items-center">
                                <input type="checkbox" name="whatsapp_visible" id="whatsapp_visible" <?php echo ($contact_data[2]['is_visible'] ?? 1) ? 'checked' : ''; ?> class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="whatsapp_visible" class="ml-2 block text-sm text-gray-700">Görünür</label>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex-grow">
                                <label class="block text-sm font-medium text-gray-700">Adres</label>
                                <textarea name="address" rows="3" class="mt-1 block w-full border rounded-md shadow-sm p-2" required><?php echo htmlspecialchars($contact_data[3]['info'] ?? ''); ?></textarea>
                            </div>
                            <div class="ml-4 flex items-center">
                                <input type="checkbox" name="address_visible" id="address_visible" <?php echo ($contact_data[3]['is_visible'] ?? 1) ? 'checked' : ''; ?> class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="address_visible" class="ml-2 block text-sm text-gray-700">Görünür</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Çalışma Saatleri -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-6">Çalışma Saatleri</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Pazartesi - Cuma</label>
                            <input type="text" name="weekday_hours" value="<?php echo htmlspecialchars($hours_data['weekday'] ?? ''); ?>" class="mt-1 block w-full border rounded-md shadow-sm p-2" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cumartesi</label>
                            <input type="text" name="saturday_hours" value="<?php echo htmlspecialchars($hours_data['saturday'] ?? ''); ?>" class="mt-1 block w-full border rounded-md shadow-sm p-2" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Pazar</label>
                            <input type="text" name="sunday_hours" value="<?php echo htmlspecialchars($hours_data['sunday'] ?? ''); ?>" class="mt-1 block w-full border rounded-md shadow-sm p-2" required>
                        </div>
                    </div>
                </div>

                <!-- Butonlar -->
                <div class="flex justify-between items-center">
                    <a href="dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Geri Dön
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md">
                        Değişiklikleri Kaydet
                    </button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>