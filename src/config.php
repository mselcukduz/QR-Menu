<?php
return [
    'database' => [
        'dsn' => 'mysql:host=localhost;dbname=qr_menu;charset=utf8mb4',
        'user' => 'root',
        'password' => 'root',
    ],
    'admin' => [
        'username' => 'admin',
        // Password: changeit
        'password_hash' => password_hash('changeit', PASSWORD_DEFAULT),
    ],
    'app' => [
        'name' => 'QR Menü',
        'default_language' => 'tr',
        'currency_symbol' => '₺',
    ],
];
