<?php
$about_data = [
    'about_text' => 'WebDev Pro olarak, 2019 yılından bu yana dijital dünyada markaları başarıya ulaştırmak için çalışıyoruz. Uzman ekibimiz, en son teknolojileri kullanarak modern, kullanıcı dostu ve performanslı web çözümleri geliştirmektedir.',
    'why_choose_items' => [
        'Modern teknolojiler ve en iyi yazılım pratikleri',
        'Zamanında teslimat ve profesyonel destek',
        'SEO optimizasyonu ve performans odaklı geliştirme',
        'Sürekli bakım ve güncelleme desteği'
    ],
    'stats' => [
        ['value' => '50+', 'label' => 'Mutlu Müşteri'],
        ['value' => '150+', 'label' => 'Tamamlanan Proje'],
        ['value' => '5', 'label' => 'Yıl Tecrübe'],
        ['value' => '100%', 'label' => 'Başarı Oranı']
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



?>

<section id="about" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-4xl font-bold text-gray-900 mb-6">Hakkımızda</h2>
                <p class="text-lg text-gray-600 mb-6 leading-relaxed">
                    <?php echo nl2br(htmlspecialchars($about_data['about_text'])); ?>
                </p>
                
                <div class="grid grid-cols-2 gap-6">
                    <?php foreach($about_data['stats'] as $stat): ?>
                        <div class="text-center p-6 bg-gray-50 rounded-xl hover:bg-blue-50 transition-colors">
                            <div class="text-blue-600 mb-2 flex justify-center"><?php echo $stat['icon'] ?? ''; ?></div>
                            <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo htmlspecialchars($stat['value']); ?></div>
                            <div class="text-sm text-gray-600"><?php echo htmlspecialchars($stat['label']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="relative">
                <div class="gradient-bg rounded-2xl p-8 text-white">
                    <h3 class="text-2xl font-bold mb-4">Neden Bizi Seçmelisiniz?</h3>
                    <ul class="space-y-4">
                        <?php foreach($about_data['why_choose_items'] as $item): ?>
                            <li class="flex items-start space-x-3">
                                <div class="w-2 h-2 bg-white rounded-full mt-2 flex-shrink-0"></div>
                                <span><?php echo htmlspecialchars($item); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="absolute -top-4 -right-4 bg-yellow-400 text-black p-4 rounded-full shadow-lg">
                    <span class="font-bold">5★</span>
                </div>
            </div>
        </div>
    </div>
</section>