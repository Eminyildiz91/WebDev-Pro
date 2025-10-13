<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

$services_data = [
    [
        'title' => 'Kurumsal Web Sitesi',
        'description' => 'Markanızı en iyi şekilde yansıtan, profesyonel ve modern web siteleri geliştiriyoruz.',
        'features' => ['Responsive Tasarım', 'SEO Optimizasyonu', 'Hızlı Yükleme']
    ],
    [
        'title' => 'E-Ticaret Çözümleri',
        'description' => 'Satışlarınızı artıracak, güvenli ve kullanıcı dostu e-ticaret platformları.',
        'features' => ['Ödeme Entegrasyonu', 'Stok Yönetimi', 'Mobil Uyumlu']
    ],
    [
        'title' => 'Mobil Uygulama',
        'description' => 'iOS ve Android için native ve hibrit mobil uygulamalar geliştiriyoruz.',
        'features' => ['Cross-Platform', 'Push Notification', 'Offline Destek']
    ],
    [
        'title' => 'SEO Optimizasyonu',
        'description' => 'Google\'da üst sıralarda yer almanızı sağlayacak SEO stratejileri uyguluyoruz.',
        'features' => ['Anahtar Kelime Analizi', 'Teknik SEO', 'İçerik Optimizasyonu']
    ],
    [
        'title' => 'Performans Optimizasyonu',
        'description' => 'Web sitenizin hızını artırıp, kullanıcı deneyimini iyileştiriyoruz.',
        'features' => ['Hız Optimizasyonu', 'Kod Minifikasyonu', 'CDN Entegrasyonu']
    ],
    [
        'title' => 'Güvenlik & Bakım',
        'description' => 'Web sitenizin güvenliğini sağlıyor ve düzenli bakımını yapıyoruz.',
        'features' => ['SSL Sertifikası', 'Düzenli Backup', 'Güvenlik Güncellemeleri']
    ]
];

$file_path = __DIR__ . '/../data/services.json';
if (file_exists($file_path)) {
    $json_content = file_get_contents($file_path);
    $decoded_data = json_decode($json_content, true);
    if ($decoded_data) {
        $services_data = $decoded_data;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form verilerini al ve işle
    $services = [];
    for ($i = 0; $i < 6; $i++) {
        if (!empty($_POST['title'][$i])) {
            $services[] = [
                'title' => $_POST['title'][$i],
                'description' => $_POST['description'][$i],
                'features' => array_filter(explode('\n', $_POST['features'][$i]))
            ];
        }
    }
    
    // Verileri bir dosyaya kaydet
    $json_data = json_encode($services, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $file_path = __DIR__ . '/../data/services.json';
    
    // data dizini yoksa oluştur
    if (!is_dir(dirname($file_path))) {
        mkdir(dirname($file_path), 0777, true);
    }

    if (file_put_contents($file_path, $json_data)) {
        $_SESSION['success_message'] = 'Hizmetler başarıyla güncellendi.';
    } else {
        $_SESSION['error_message'] = 'Hizmetler güncellenirken bir hata oluştu.';
    }
    header('Location: edit-services.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hizmetler Düzenle - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900">Hizmetler Düzenle</h1>
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
                    <span class="block sm:inline"><?php echo $_SESSION['success_message']; ?></span>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <form action="edit-services.php" method="POST" class="space-y-6">
                <?php foreach ($services_data as $index => $service): ?>
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-medium text-gray-900">Hizmet <?php echo $index + 1; ?></h2>
                        <div class="flex items-center space-x-2">
                            <button type="button" class="text-gray-400 hover:text-gray-500" onclick="moveService(<?php echo $index; ?>, 'up')">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                </svg>
                            </button>
                            <button type="button" class="text-gray-400 hover:text-gray-500" onclick="moveService(<?php echo $index; ?>, 'down')">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Başlık</label>
                            <input type="text" name="title[]" value="<?php echo htmlspecialchars($service['title'] ?? ''); ?>" class="mt-1 block w-full border rounded-md shadow-sm p-2" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Açıklama</label>
                            <textarea name="description[]" rows="3" class="mt-1 block w-full border rounded-md shadow-sm p-2" required><?php echo htmlspecialchars($service['description'] ?? ''); ?></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Özellikler (Her satıra bir özellik)</label>
                            <textarea name="features[]" rows="4" class="mt-1 block w-full border rounded-md shadow-sm p-2" required><?php echo htmlspecialchars(implode("\n", $service['features'] ?? [])); ?></textarea>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Butonlar -->
                <div class="flex justify-between items-center">
                    <a href="edit-content.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md inline-flex items-center">
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

    <script>
    function moveService(index, direction) {
        const form = document.querySelector('form');
        const services = form.querySelectorAll('.bg-white.shadow');
        
        if (direction === 'up' && index > 0) {
            services[index].parentNode.insertBefore(services[index], services[index - 1]);
        } else if (direction === 'down' && index < services.length - 1) {
            services[index].parentNode.insertBefore(services[index + 1], services[index]);
        }
    }
    </script>
</body>
</html>