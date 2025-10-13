<?php
// Blog fonksiyonları

// Blog verilerini yükle
function loadBlogData() {
    $blog_file = 'data/blog.json';
    if (file_exists($blog_file)) {
        $data = file_get_contents($blog_file);
        return json_decode($data, true) ?: [];
    }
    return [];
}

// Blog yazısını ID'ye göre getir
function getBlogById($id) {
    $blogs = loadBlogData();
    foreach ($blogs as $blog) {
        if ($blog['id'] == $id) {
            return $blog;
        }
    }
    return null;
}

// Yayınlanmış blog yazılarını getir
function getPublishedBlogs() {
    $blogs = loadBlogData();
    return array_filter($blogs, function($blog) {
        return $blog['status'] === 'published';
    });
}

// Blog yazısının özetini oluştur
function createExcerpt($content, $length = 150) {
    $content = strip_tags($content);
    if (strlen($content) <= $length) {
        return $content;
    }
    return substr($content, 0, $length) . '...';
}

// Tarih formatla
function formatDate($date) {
    return date('d.m.Y', strtotime($date));
}

// URL'yi temizle
function cleanUrl($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}

// Sayfalama
function paginate($items, $page = 1, $perPage = 6) {
    $total = count($items);
    $totalPages = ceil($total / $perPage);
    $offset = ($page - 1) * $perPage;
    
    return [
        'items' => array_slice($items, $offset, $perPage),
        'current_page' => $page,
        'total_pages' => $totalPages,
        'total_items' => $total,
        'per_page' => $perPage
    ];
}
?>