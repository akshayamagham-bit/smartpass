<?php
/**
 * Generate a valid bcrypt hash for "password123".
 * Run once: http://localhost/smart-event-passport/includes/generate_hash.php
 * then copy the printed hash into the SQL seed or register a new account.
 */
require_once __DIR__ . '/db.php';
echo '<pre>';
echo "Hash for 'password123':\n";
echo password_hash('password123', PASSWORD_DEFAULT);
echo "\n\nAdmin hash (admin@smartpass.edu):\n";
echo password_hash('admin123', PASSWORD_DEFAULT);
echo '</pre>';
