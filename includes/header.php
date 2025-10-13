<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>WebDev Pro</title>
    <meta name="description" content="<?php echo isset($page_description) ? $page_description : 'Profesyonel web geliştirme hizmetleri'; ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .prose {
            max-width: none;
        }
        .prose p {
            margin-bottom: 1rem;
            line-height: 1.7;
        }
        .prose h1, .prose h2, .prose h3, .prose h4, .prose h5, .prose h6 {
            margin-top: 2rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        .prose ul, .prose ol {
            margin-bottom: 1rem;
            padding-left: 1.5rem;
        }
        .prose li {
            margin-bottom: 0.5rem;
        }

            :root {
                --primary-theme-color: <?php echo isset($settings_data['theme_color']) ? htmlspecialchars($settings_data['theme_color']) : '#3490dc'; ?>;
            }
            .bg-theme {
                background-color: var(--primary-theme-color);
            }
            .text-theme {
                color: var(--primary-theme-color);
            }
        </style>
    </style>
</head>
<body class="bg-white text-gray-900 min-h-screen flex flex-col">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center space-x-2">
                    <div class="bg-blue-600 p-2 rounded-lg">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                        </svg>
                    </div>
                    <a href="index.php" class="text-xl font-bold text-gray-900">WebDev Pro</a>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="index.php" class="text-gray-700 hover:text-blue-600 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'text-blue-600 font-medium' : ''; ?>">
                        Ana Sayfa
                    </a>
                    <a href="about.php" class="text-gray-700 hover:text-blue-600 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'text-blue-600 font-medium' : ''; ?>">
                        Hakkımızda
                    </a>
                    <a href="services.php" class="text-gray-700 hover:text-blue-600 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'services.php' ? 'text-blue-600 font-medium' : ''; ?>">
                        Hizmetler
                    </a>
                    <a href="portfolio.php" class="text-gray-700 hover:text-blue-600 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'portfolio.php' ? 'text-blue-600 font-medium' : ''; ?>">
                        Portföy
                    </a>
                    <a href="blog.php" class="text-gray-700 hover:text-blue-600 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'blog.php' || basename($_SERVER['PHP_SELF']) == 'blog-detail.php' ? 'text-blue-600 font-medium' : ''; ?>">
                        Blog
                    </a>
                    <a href="contact.php" class="text-gray-700 hover:text-blue-600 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'text-blue-600 font-medium' : ''; ?>">
                        İletişim
                    </a>
                    <a href=contact.php class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        İletişim
                    </a>
                    <a href="admin" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        Giriş Yap
                    </a>
                </div>
                
                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-gray-700 hover:text-blue-600 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Menu -->
            <div id="mobile-menu" class="md:hidden hidden">
                <div class="px-2 pt-2 pb-3 space-y-1 bg-white border-t">
                    <a href="index.php" class="block px-3 py-2 text-gray-700 hover:text-blue-600 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'text-blue-600 font-medium' : ''; ?>">
                        Ana Sayfa
                    </a>
                    <a href="about.php" class="block px-3 py-2 text-gray-700 hover:text-blue-600 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'text-blue-600 font-medium' : ''; ?>">
                        Hakkımızda
                    </a>
                    <a href="services.php" class="block px-3 py-2 text-gray-700 hover:text-blue-600 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'services.php' ? 'text-blue-600 font-medium' : ''; ?>">
                        Hizmetler
                    </a>
                    <a href="portfolio.php" class="block px-3 py-2 text-gray-700 hover:text-blue-600 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'portfolio.php' ? 'text-blue-600 font-medium' : ''; ?>">
                        Portföy
                    </a>
                    <a href="blog.php" class="block px-3 py-2 text-gray-700 hover:text-blue-600 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'blog.php' || basename($_SERVER['PHP_SELF']) == 'blog-detail.php' ? 'text-blue-600 font-medium' : ''; ?>">
                        Blog
                    </a>
                    <a href="contact.php" class="block px-3 py-2 text-gray-700 hover:text-blue-600 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'text-blue-600 font-medium' : ''; ?>">
                        İletişim
                    </a>
                    <a href="contact.php" class="block mx-3 mt-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-center">
                        Teklif Al
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });
    </script>