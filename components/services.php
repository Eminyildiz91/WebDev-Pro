<?php
$services_data = [
    [
        'icon' => '<svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path></svg>',
        'title' => 'Kurumsal Web Sitesi',
        'description' => 'Markanızı en iyi şekilde yansıtan, profesyonel ve modern web siteleri geliştiriyoruz.',
        'features' => ['Responsive Tasarım', 'SEO Optimizasyonu', 'Hızlı Yükleme']
    ],
    [
        'icon' => '<svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 3H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6.28l2.293-2.293c.63-.63 1.707-.184 1.707.707V17"></path></svg>',
        'title' => 'E-Ticaret Çözümleri',
        'description' => 'Satışlarınızı artıracak, güvenli ve kullanıcı dostu e-ticaret platformları.',
        'features' => ['Ödeme Entegrasyonu', 'Stok Yönetimi', 'Mobil Uyumlu']
    ],
    [
        'icon' => '<svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>',
        'title' => 'Mobil Uygulama',
        'description' => 'iOS ve Android için native ve hibrit mobil uygulamalar geliştiriyoruz.',
        'features' => ['Cross-Platform', 'Push Notification', 'Offline Destek']
    ],
    [
        'icon' => '<svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>',
        'title' => 'SEO Optimizasyonu',
        'description' => 'Google\'da üst sıralarda yer almanızı sağlayacak SEO stratejileri uyguluyoruz.',
        'features' => ['Anahtar Kelime Analizi', 'Teknik SEO', 'İçerik Optimizasyonu']
    ],
    [
        'icon' => '<svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>',
        'title' => 'Performans Optimizasyonu',
        'description' => 'Web sitenizin hızını artırıp, kullanıcı deneyimini iyileştiriyoruz.',
        'features' => ['Hız Optimizasyonu', 'Kod Minifikasyonu', 'CDN Entegrasyonu']
    ],
    [
        'icon' => '<svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>',
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

$service_icons = [
    'Kurumsal Web Sitesi' => '<svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path></svg>',
    'E-Ticaret Çözümleri' => '<svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 3H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6.28l2.293-2.293c.63-.63 1.707-.184 1.707.707V17"></path></svg>',
    'Mobil Uygulama' => '<svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>',
    'SEO Optimizasyonu' => '<svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>',
    'Performans Optimizasyonu' => '<svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>',
    'Güvenlik & Bakım' => '<svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>'
];
?>

<section id="services" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Hizmetlerimiz</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Dijital dünyadaki tüm ihtiyaçlarınız için kapsamlı çözümler sunuyoruz. 
                Modern teknolojiler ve uzman ekibimizle yanınızdayız.
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach($services_data as $service): ?>
                <div class="bg-gray-50 rounded-2xl p-8 card-hover group">
                    <div class="bg-blue-600 text-white p-3 rounded-lg inline-block mb-6 group-hover:scale-110 transition-transform">
                        <?php echo $service_icons[$service['title']] ?? ''; ?>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4"><?php echo htmlspecialchars($service['title']); ?></h3>
                    <p class="text-gray-600 mb-6 leading-relaxed"><?php echo htmlspecialchars($service['description']); ?></p>
                    <ul class="space-y-2">
                        <?php foreach($service['features'] as $feature): ?>
                            <li class="flex items-center text-sm text-gray-700">
                                <div class="w-2 h-2 bg-blue-600 rounded-full mr-3"></div>
                                <?php echo htmlspecialchars($feature); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>