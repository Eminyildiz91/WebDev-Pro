-- İletişim bilgileri tablosu
CREATE TABLE IF NOT EXISTS contact_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(50) NOT NULL,
    info TEXT NOT NULL,
    is_visible BOOLEAN DEFAULT TRUE
);

-- Çalışma saatleri tablosu
CREATE TABLE IF NOT EXISTS working_hours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    day_type VARCHAR(20) NOT NULL,
    hours VARCHAR(50) NOT NULL
);

-- Varsayılan iletişim bilgilerini ekle
INSERT INTO contact_info (title, info, is_visible) VALUES
('E-posta', 'info@webdevpro.com', TRUE),
('Telefon', '+90 (555) 123 -4567', TRUE),
('WhatsApp', '+90 (555) 123 -4567', TRUE),
('Adres', 'Levent Mahallesi\nBüyükdere Caddesi No:123\nŞişli, İstanbul', TRUE)
ON DUPLICATE KEY UPDATE info = VALUES(info), is_visible = VALUES(is_visible);

-- Varsayılan çalışma saatlerini ekle
INSERT INTO working_hours (day_type, hours) VALUES
('weekday', '09:00 - 18:00'),
('saturday', '10:00 - 16:00'),
('sunday', 'Kapalı')
ON DUPLICATE KEY UPDATE hours = VALUES(hours);

-- İletişim formları tablosu
CREATE TABLE IF NOT EXISTS contact_forms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);