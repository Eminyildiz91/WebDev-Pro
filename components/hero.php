<?php
$hero_stats = [
    ['value' => '0+', 'label' => 'Tamamlanan Proje'],
    ['value' => '0+', 'label' => 'Mutlu Müşteri'],
    ['value' => '1', 'label' => 'Yıl Tecrübe']
];
?>

<section id="home" class="pt-16 bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="animate-fade-in-up">
                <h1 class="text-4xl md:text-6xl font-bold text-gray-900 leading-tight">
                    Modern Web 
                    <span class="text-blue-600">Çözümleri</span>
                </h1>
                <p class="text-xl text-gray-600 mt-6 leading-relaxed">
                    Markanızı dijital dünyada öne çıkaracak, kullanıcı odaklı ve modern web siteleri geliştiriyoruz. 
                    En son teknolojilerle, hızlı ve güvenli çözümler sunuyoruz.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 mt-8">
                    <button class="btn-primary text-white px-8 py-4 rounded-lg font-medium flex items-center justify-center space-x-2">
                        <span>Projeni Başlat</span>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                    </button>
                    <button class="border border-gray-300 text-gray-700 px-8 py-4 rounded-lg font-medium hover:bg-gray-50 transition-all duration-300 flex items-center justify-center space-x-2">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293H15M9 10V9a2 2 0 012-2h2a2 2 0 012 2v1M9 10v5a2 2 0 002 2h2a2 2 0 002-2v-5"></path>
                        </svg>
                        <span>İletişim</span>
                    </button>
                </div>
                <div class="grid grid-cols-3 gap-8 mt-12">
                    <?php foreach($hero_stats as $stat): ?>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600"><?php echo $stat['value']; ?></div>
                            <div class="text-sm text-gray-600"><?php echo $stat['label']; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="relative animate-fade-in-right">
                <div class="bg-white rounded-2xl shadow-2xl p-8 transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <div class="bg-gray-100 rounded-lg p-6">
                        <div class="flex items-center space-x-2 mb-4">
                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        </div>
                        <div class="space-y-3">
                            <div class="h-4 bg-blue-200 rounded w-3/4"></div>
                            <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                            <div class="h-4 bg-blue-200 rounded w-2/3"></div>
                            <div class="h-20 bg-gradient-to-r from-blue-100 to-purple-100 rounded-lg"></div>
                        </div>
                    </div>
                </div>
                <div class="absolute -top-4 -right-4 bg-blue-600 text-white p-4 rounded-full shadow-lg">
                    <span class="text-sm font-medium">Responsive</span>
                </div>
            </div>
        </div>
    </div>
</section>