<?php
// Test the admin account page
session_start();
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id'] = 1;

// Change to the account directory to run the test
chdir('account');

// Include the account page to test if it works
ob_start();
include 'index.php';
$content = ob_get_clean();

// Check for errors
echo "Page loaded successfully!\n";
echo "Content length: " . strlen($content) . " characters\n";

// Check if account status is displayed
echo "\nChecking for account status display:\n";
if (strpos($content, 'Aktif') !== false) {
    echo "✓ Account status 'Aktif' found in page\n";
} else {
    echo "✗ Account status 'Aktif' NOT found in page\n";
}

if (strpos($content, 'Pasif') !== false) {
    echo "✓ Account status 'Pasif' found in page\n";
} else {
    echo "✗ Account status 'Pasif' NOT found in page\n";
}

// Check for other admin details
$details_to_check = ['Kullanıcı ID', 'Kullanıcı Adı', 'E-posta', 'Hesap Durumu'];
foreach ($details_to_check as $detail) {
    if (strpos($content, $detail) !== false) {
        echo "✓ '$detail' section found in page\n";
    } else {
        echo "✗ '$detail' section NOT found in page\n";
    }
}