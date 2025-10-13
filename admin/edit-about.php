<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

$about_data = [
    'about_text' => '',
    'why_choose_items' => ['', '', '', ''],
    'stats' => [
        ['value' => '', 'label' => 'Mutlu Müşteri'],
        ['value' => '', 'label' => 'Tamamlanan Proje'],
        ['value' => '', 'label' => 'Yıl Tecrübe'],
        ['value' => '', 'label' => 'Başarı Oranı']
    ]
];

$file_path = __DIR__ . '/../data/about.json';
if (file_exists($file_path)) {
    $json_content = file_get_contents($file_path);
    $decoded_data = json_decode($json_content, true);
    if ($decoded_data) {
        $about_data = $decoded_data;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form verilerini al
    $about_text = $_POST['about_text'];
    $why_choose_items = $_POST['why_choose_items'];
    $stats = [];
    foreach ($_POST['stats'] as $stat_data) {
        $stats[] = [
            'value' => $stat_data['value'],
            'label' => $stat_data['label'],
            'icon' => $stat_data['icon'] ?? ''
        ];
    }
    
    // Verileri bir dosyaya kaydet
    $data = [
        'about_text' => $about_text,
        'why_choose_items' => $why_choose_items,
        'stats' => $stats
    ];
    
    $json_data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $file_path = __DIR__ . '/../data/about.json';
    
    // data dizini yoksa oluştur
    if (!is_dir(dirname($file_path))) {
        mkdir(dirname($file_path), 0777, true);
    }

    if (file_put_contents($file_path, $json_data)) {
        $_SESSION['success_message'] = 'Hakkımızda içeriği başarıyla güncellendi.';
    } else {
        $_SESSION['error_message'] = 'Hakkımızda içeriği güncellenirken bir hata oluştu.';
    }
    header('Location: edit-about.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hakkımızda Düzenle - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900">Hakkımızda Düzenle</h1>
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

            <form action="edit-about.php" method="POST" class="space-y-8">
                <!-- Hakkımızda Metni -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Hakkımızda Metni</h2>
                    <textarea name="about_text" rows="6" class="w-full p-2 border rounded-md" required><?php echo htmlspecialchars($about_data['about_text']); ?></textarea>
                </div>

                <!-- İstatistikler -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">İstatistikler</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4">
                        <?php foreach ($about_data['stats'] as $i => $stat): ?>
                            <div class="col-span-1">
                                <label for="stat_value_<?php echo $i; ?>" class="block text-sm font-medium text-gray-700">İstatistik Değeri <?php echo $i + 1; ?></label>
                                <input type="text" name="stats[<?php echo $i; ?>][value]" id="stat_value_<?php echo $i; ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" value="<?php echo htmlspecialchars($stat['value'] ?? ''); ?>" required>
                            </div>
                            <div class="col-span-1">
                                <label for="stat_label_<?php echo $i; ?>" class="block text-sm font-medium text-gray-700">İstatistik Etiketi <?php echo $i + 1; ?></label>
                                <input type="text" name="stats[<?php echo $i; ?>][label]" id="stat_label_<?php echo $i; ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" value="<?php echo htmlspecialchars($stat['label'] ?? ''); ?>" required>
                            </div>
                            <div class="col-span-2">
                                <label for="stat_icon_<?php echo $i; ?>" class="block text-sm font-medium text-gray-700">İstatistik İkonu (SVG) <?php echo $i + 1; ?></label>
                                <textarea name="stats[<?php echo $i; ?>][icon]" id="stat_icon_<?php echo $i; ?>" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"><?php echo htmlspecialchars($stat['icon'] ?? ''); ?></textarea>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Neden Bizi Seçmelisiniz -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Neden Bizi Seçmelisiniz?</h2>
                    <div class="space-y-4">
                        <?php foreach ($about_data['why_choose_items'] as $index => $item): ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Madde <?php echo $index + 1; ?></label>
                                <input type="text" name="why_choose_items[]" value="<?php echo htmlspecialchars($item); ?>" class="mt-1 block w-full border rounded-md shadow-sm p-2" required>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

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
</body>
</html>