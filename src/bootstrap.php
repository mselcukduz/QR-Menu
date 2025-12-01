<?php
session_start();

$config = require __DIR__ . '/config.php';

$db = $config['database'];
$pdo = new PDO($db['dsn'], $db['user'], $db['password']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

initializeDatabase($pdo, $config['app']['default_language']);

function initializeDatabase(PDO $pdo, string $defaultLang): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS languages (
        code VARCHAR(8) PRIMARY KEY,
        name VARCHAR(50) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    $pdo->exec('CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(100) NOT NULL UNIQUE,
        position INT DEFAULT 0,
        featured TINYINT(1) DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    $pdo->exec('CREATE TABLE IF NOT EXISTS category_translations (
        category_id INT NOT NULL,
        language_code VARCHAR(8) NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        PRIMARY KEY (category_id, language_code),
        CONSTRAINT fk_category_translations_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
        CONSTRAINT fk_category_translations_language FOREIGN KEY (language_code) REFERENCES languages(code) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    $pdo->exec('CREATE TABLE IF NOT EXISTS items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        available TINYINT(1) DEFAULT 1,
        spicy TINYINT(1) DEFAULT 0,
        vegan TINYINT(1) DEFAULT 0,
        gluten_free TINYINT(1) DEFAULT 0,
        is_special TINYINT(1) DEFAULT 0,
        calories INT DEFAULT NULL,
        image_url VARCHAR(500) DEFAULT NULL,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    $pdo->exec('CREATE TABLE IF NOT EXISTS item_translations (
        item_id INT NOT NULL,
        language_code VARCHAR(8) NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        PRIMARY KEY (item_id, language_code),
        CONSTRAINT fk_item_translations_item FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
        CONSTRAINT fk_item_translations_language FOREIGN KEY (language_code) REFERENCES languages(code) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    $pdo->exec('CREATE TABLE IF NOT EXISTS settings (
        `key` VARCHAR(100) PRIMARY KEY,
        value TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    ensureColumn($pdo, 'categories', 'featured', 'TINYINT(1) DEFAULT 0');
    ensureColumn($pdo, 'items', 'spicy', 'TINYINT(1) DEFAULT 0');
    ensureColumn($pdo, 'items', 'vegan', 'TINYINT(1) DEFAULT 0');
    ensureColumn($pdo, 'items', 'gluten_free', 'TINYINT(1) DEFAULT 0');
    ensureColumn($pdo, 'items', 'is_special', 'TINYINT(1) DEFAULT 0');
    ensureColumn($pdo, 'items', 'calories', 'INT DEFAULT NULL');
    ensureColumn($pdo, 'items', 'image_url', 'VARCHAR(500) DEFAULT NULL');

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM languages WHERE code = :code');
    $stmt->execute([':code' => $defaultLang]);
    if ((int)$stmt->fetchColumn() === 0) {
        $pdo->prepare('INSERT INTO languages(code, name) VALUES (:code, :name)')
            ->execute([':code' => $defaultLang, ':name' => strtoupper($defaultLang)]);
    }

    $defaults = [
        'restaurant_name' => 'QR Menü',
        'wifi_name' => '',
        'wifi_password' => '',
        'contact_phone' => '',
        'address' => '',
        'currency_symbol' => '₺',
        'hero_note' => 'QR ile menünüze hızlıca ulaşın.',
        'brand_primary' => '#222831',
        'brand_accent' => '#00adb5',
        'logo_url' => ''
    ];
    foreach ($defaults as $key => $value) {
        $stmt = $pdo->prepare('INSERT INTO settings(`key`, value) VALUES (:key, :value) ON DUPLICATE KEY UPDATE value = value');
        $stmt->execute([':key' => $key, ':value' => $value]);
    }
}

function ensureColumn(PDO $pdo, string $table, string $column, string $definition): void
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column');
    $stmt->execute([':table' => $table, ':column' => $column]);
    if ((int)$stmt->fetchColumn() === 0) {
        $pdo->exec(sprintf('ALTER TABLE %s ADD COLUMN %s %s', $table, $column, $definition));
    }
}

function isAdminLoggedIn(): bool
{
    return isset($_SESSION['admin']);
}

function requireAdmin(): void
{
    if (!isAdminLoggedIn()) {
        header('Location: /admin.php');
        exit;
    }
}

function getAvailableLanguages(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT code, name FROM languages ORDER BY name');
    return $stmt->fetchAll();
}

function getActiveLanguage(PDO $pdo, string $default): string
{
    $languages = array_column(getAvailableLanguages($pdo), 'code');
    $requested = $_GET['lang'] ?? $_SESSION['lang'] ?? $default;
    if (!in_array($requested, $languages, true)) {
        $requested = $default;
    }
    $_SESSION['lang'] = $requested;
    return $requested;
}

function getSettings(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT `key`, value FROM settings');
    $rows = $stmt->fetchAll();
    $settings = [];
    foreach ($rows as $row) {
        $settings[$row['key']] = $row['value'];
    }
    return $settings;
}

function saveSettings(PDO $pdo, array $data): void
{
    $stmt = $pdo->prepare('INSERT INTO settings(`key`, value) VALUES (:key, :value)
        ON DUPLICATE KEY UPDATE value = VALUES(value)');
    foreach ($data as $key => $value) {
        $stmt->execute([':key' => $key, ':value' => $value]);
    }
}

function translateCategory(PDO $pdo, int $categoryId, string $lang): ?array
{
    $stmt = $pdo->prepare('SELECT name, description FROM category_translations WHERE category_id = :id AND language_code = :lang');
    $stmt->execute([':id' => $categoryId, ':lang' => $lang]);
    $translation = $stmt->fetch();
    if (!$translation) {
        $stmt = $pdo->prepare('SELECT name, description FROM category_translations WHERE category_id = :id LIMIT 1');
        $stmt->execute([':id' => $categoryId]);
        return $stmt->fetch() ?: null;
    }
    return $translation;
}

function translateItem(PDO $pdo, int $itemId, string $lang): ?array
{
    $stmt = $pdo->prepare('SELECT name, description FROM item_translations WHERE item_id = :id AND language_code = :lang');
    $stmt->execute([':id' => $itemId, ':lang' => $lang]);
    $translation = $stmt->fetch();
    if (!$translation) {
        $stmt = $pdo->prepare('SELECT name, description FROM item_translations WHERE item_id = :id LIMIT 1');
        $stmt->execute([':id' => $itemId]);
        return $stmt->fetch() ?: null;
    }
    return $translation;
}

function fetchMenu(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT id, slug, featured FROM categories ORDER BY position, slug');
    $categories = $stmt->fetchAll();
    foreach ($categories as &$category) {
        $itemStmt = $pdo->prepare('SELECT id, price, available, spicy, vegan, gluten_free, is_special, calories, image_url FROM items WHERE category_id = :id ORDER BY id');
        $itemStmt->execute([':id' => $category['id']]);
        $category['items'] = $itemStmt->fetchAll();
    }
    return $categories;
}

function handleLogin(PDO $pdo, array $config): void
{
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === $config['admin']['username'] && password_verify($password, $config['admin']['password_hash'])) {
        $_SESSION['admin'] = $username;
        header('Location: /admin.php');
        exit;
    }
    $error = urlencode('Geçersiz kullanıcı adı veya şifre');
    header('Location: /admin.php?error=' . $error);
    exit;
}

function redirectWithMessage(string $message): void
{
    header('Location: /admin.php?notice=' . urlencode($message));
    exit;
}
