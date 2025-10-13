<?php
// Veritabanı bağlantısını dahil et
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

// Ziyaretçi sayacını dahil et ve ziyareti kaydet
require_once 'includes/visitor_counter.php';
$visitor_counter = new VisitorCounter($db);
$visitor_counter->recordVisit('homepage');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebDev Pro - Modern Web Çözümleri</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e3a8a',
                        secondary: '#3b82f6',
                        accent: '#60a5fa'
                    }
                }
            }
        }
    </script>
    <style>
        .smooth-scroll {
            scroll-behavior: smooth;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
        }
    </style>
</head>
<body class="smooth-scroll relative">
    <?php include 'components/header.php'; ?>
  
    <?php include 'components/hero.php'; ?>
    <?php include 'components/services.php'; ?>
    <?php include 'components/portfolio.php'; ?>
    <?php include 'components/blog-portfolio.php'; ?>
    <?php include 'components/about.php'; ?>
    <?php include 'components/contact.php'; ?>
    <?php include 'components/footer.php'; ?>



    <script src="js/main.js"></script>
</body>
</html>