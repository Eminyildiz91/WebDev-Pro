<?php
$menu_items = [
    ['href' => '#home', 'text' => 'Ana Sayfa'],
    ['href' => '#services', 'text' => 'Hizmetler'],
    ['href' => '#portfolio', 'text' => 'Portföy'],
    ['href' => '#about', 'text' => 'Hakkımızda'],
    ['href' => '#contact', 'text' => 'İletişim']
];
?>

<header class="fixed w-full top-0 bg-white/95 backdrop-blur-sm border-b border-gray-200 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center space-x-2">
                <div class="bg-blue-600 p-2 rounded-lg">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                    </svg>
                </div>
                <span class="text-xl font-bold text-gray-900">WebDev Pro</span>
            </div>
            
            <nav class="hidden md:flex space-x-8">
                <?php foreach($menu_items as $item): ?>
                    <a href="<?php echo $item['href']; ?>" class="text-gray-700 hover:text-blue-600 transition-colors duration-300">
                        <?php echo $item['text']; ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            
            <div class="hidden md:flex items-center space-x-4">
                <a href="admin/dashboard.php" class="inline-block px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition duration-300">
                    Giriş Yap
                </a>
                     <a href="blog.php" class="inline-block px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition duration-300">
                    Blog
                </a>
              
            
            </div>
            
            <button class="md:hidden mobile-menu-btn" onclick="toggleMobileMenu()">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
        
        <div id="mobile-menu" class="md:hidden hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 bg-white border-t border-gray-200">
                <?php foreach($menu_items as $item): ?>
                    <a href="<?php echo $item['href']; ?>" class="block px-3 py-2 text-gray-700 hover:text-blue-600 transition-colors">
                        <?php echo $item['text']; ?>
                    </a>
                <?php endforeach; ?>
                <a href="admin/index.php" class="block w-full mt-2 px-3 py-2 text-center bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition duration-300">
                    Giriş Yap
                </a>
                
                 </a>
                     <a href="blog.php" class="inline-block px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition duration-300">
                    Blog
                </a>
                
              
            </div>
        </div>
    </div>
</header>