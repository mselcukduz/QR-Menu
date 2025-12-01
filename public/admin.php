<?php
require __DIR__ . '/../src/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        handleLogin($pdo, $config);
    }
    requireAdmin();
    switch ($_POST['action'] ?? '') {
        case 'add_language':
            $code = strtolower(trim($_POST['code'] ?? ''));
            $name = trim($_POST['name'] ?? '');
            if ($code && $name) {
                $stmt = $pdo->prepare('INSERT IGNORE INTO languages(code, name) VALUES (:code, :name)');
                $stmt->execute([':code' => $code, ':name' => $name]);
                redirectWithMessage('Dil eklendi.');
            }
            redirectWithMessage('Dil eklenemedi.');
            break;
        case 'add_category':
            $slug = trim($_POST['slug'] ?? '');
            $position = (int)($_POST['position'] ?? 0);
            $featured = isset($_POST['featured']) ? 1 : 0;
            if ($slug) {
                $pdo->prepare('INSERT INTO categories(slug, position, featured) VALUES (:slug, :position, :featured)')
                    ->execute([':slug' => $slug, ':position' => $position, ':featured' => $featured]);
                redirectWithMessage('Kategori eklendi.');
            }
            redirectWithMessage('Kategori eklenemedi.');
            break;
        case 'translate_category':
            $categoryId = (int)($_POST['category_id'] ?? 0);
            $language = trim($_POST['language_code'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            if ($categoryId && $language && $name) {
                $pdo->prepare('REPLACE INTO category_translations(category_id, language_code, name, description) VALUES (:id, :lang, :name, :description)')
                    ->execute([
                        ':id' => $categoryId,
                        ':lang' => $language,
                        ':name' => $name,
                        ':description' => $description,
                    ]);
                redirectWithMessage('Kategori çevirisi kaydedildi.');
            }
            redirectWithMessage('Kategori çevirisi eklenemedi.');
            break;
        case 'add_item':
            $categoryId = (int)($_POST['category_id'] ?? 0);
            $price = (float)($_POST['price'] ?? 0);
            $available = isset($_POST['available']) ? 1 : 0;
            $spicy = isset($_POST['spicy']) ? 1 : 0;
            $vegan = isset($_POST['vegan']) ? 1 : 0;
            $glutenFree = isset($_POST['gluten_free']) ? 1 : 0;
            $isSpecial = isset($_POST['is_special']) ? 1 : 0;
            $calories = trim($_POST['calories'] ?? '') === '' ? null : (int)$_POST['calories'];
            $imageUrl = trim($_POST['image_url'] ?? '') ?: null;
            if ($categoryId && $price) {
                $pdo->prepare('INSERT INTO items(category_id, price, available, spicy, vegan, gluten_free, is_special, calories, image_url) VALUES (:category_id, :price, :available, :spicy, :vegan, :gluten_free, :is_special, :calories, :image_url)')
                    ->execute([
                        ':category_id' => $categoryId,
                        ':price' => $price,
                        ':available' => $available,
                        ':spicy' => $spicy,
                        ':vegan' => $vegan,
                        ':gluten_free' => $glutenFree,
                        ':is_special' => $isSpecial,
                        ':calories' => $calories,
                        ':image_url' => $imageUrl,
                    ]);
                redirectWithMessage('Ürün eklendi.');
            }
            redirectWithMessage('Ürün eklenemedi.');
            break;
        case 'update_item_flags':
            $itemId = (int)($_POST['item_id'] ?? 0);
            $available = isset($_POST['available']) ? 1 : 0;
            $spicy = isset($_POST['spicy']) ? 1 : 0;
            $vegan = isset($_POST['vegan']) ? 1 : 0;
            $glutenFree = isset($_POST['gluten_free']) ? 1 : 0;
            $isSpecial = isset($_POST['is_special']) ? 1 : 0;
            if ($itemId) {
                $stmt = $pdo->prepare('UPDATE items SET available = :available, spicy = :spicy, vegan = :vegan, gluten_free = :gluten_free, is_special = :is_special WHERE id = :id');
                $stmt->execute([
                    ':available' => $available,
                    ':spicy' => $spicy,
                    ':vegan' => $vegan,
                    ':gluten_free' => $glutenFree,
                    ':is_special' => $isSpecial,
                    ':id' => $itemId,
                ]);
                redirectWithMessage('Ürün bilgisi güncellendi.');
            }
            redirectWithMessage('Ürün güncellenemedi.');
            break;
        case 'update_item_details':
            $itemId = (int)($_POST['item_id'] ?? 0);
            $price = (float)($_POST['price'] ?? 0);
            $calories = trim($_POST['calories'] ?? '') === '' ? null : (int)$_POST['calories'];
            $imageUrl = trim($_POST['image_url'] ?? '') ?: null;
            if ($itemId && $price) {
                $stmt = $pdo->prepare('UPDATE items SET price = :price, calories = :calories, image_url = :image_url WHERE id = :id');
                $stmt->execute([
                    ':price' => $price,
                    ':calories' => $calories,
                    ':image_url' => $imageUrl,
                    ':id' => $itemId,
                ]);
                redirectWithMessage('Fiyat ve görsel güncellendi.');
            }
            redirectWithMessage('Fiyat/görsel güncellenemedi.');
            break;
        case 'translate_item':
            $itemId = (int)($_POST['item_id'] ?? 0);
            $language = trim($_POST['language_code'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            if ($itemId && $language && $name) {
                $pdo->prepare('REPLACE INTO item_translations(item_id, language_code, name, description) VALUES (:id, :lang, :name, :description)')
                    ->execute([
                        ':id' => $itemId,
                        ':lang' => $language,
                        ':name' => $name,
                        ':description' => $description,
                    ]);
                redirectWithMessage('Ürün çevirisi kaydedildi.');
            }
            redirectWithMessage('Ürün çevirisi eklenemedi.');
            break;
        case 'delete_category':
            $categoryId = (int)($_POST['category_id'] ?? 0);
            if ($categoryId) {
                $pdo->prepare('DELETE FROM categories WHERE id = :id')->execute([':id' => $categoryId]);
                redirectWithMessage('Kategori ve bağlı ürünler silindi.');
            }
            redirectWithMessage('Kategori silinemedi.');
            break;
        case 'delete_item':
            $itemId = (int)($_POST['item_id'] ?? 0);
            if ($itemId) {
                $pdo->prepare('DELETE FROM items WHERE id = :id')->execute([':id' => $itemId]);
                redirectWithMessage('Ürün silindi.');
            }
            redirectWithMessage('Ürün silinemedi.');
            break;
        case 'update_settings':
            $fields = ['restaurant_name', 'wifi_name', 'wifi_password', 'contact_phone', 'address', 'currency_symbol', 'hero_note', 'brand_primary', 'brand_accent', 'logo_url'];
            $data = [];
            foreach ($fields as $field) {
                $data[$field] = trim($_POST[$field] ?? '');
            }
            saveSettings($pdo, $data);
            redirectWithMessage('Genel ayarlar kaydedildi.');
            break;
        case 'update_category_featured':
            $categoryId = (int)($_POST['category_id'] ?? 0);
            $featured = isset($_POST['featured']) ? 1 : 0;
            $position = (int)($_POST['position'] ?? 0);
            if ($categoryId) {
                $pdo->prepare('UPDATE categories SET featured = :featured, position = :position WHERE id = :id')
                    ->execute([':featured' => $featured, ':position' => $position, ':id' => $categoryId]);
                redirectWithMessage('Kategori bilgisi güncellendi.');
            }
            redirectWithMessage('Kategori güncellenemedi.');
            break;
        case 'logout':
            session_destroy();
            header('Location: /admin.php');
            exit;
    }
}

$languages = getAvailableLanguages($pdo);
$categories = $pdo->query('SELECT * FROM categories ORDER BY position, slug')->fetchAll(PDO::FETCH_ASSOC);
$items = $pdo->query('SELECT i.*, c.slug AS category_slug FROM items i JOIN categories c ON c.id = i.category_id ORDER BY i.id')
    ->fetchAll(PDO::FETCH_ASSOC);
$settings = getSettings($pdo);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; }
        .container { max-width: 1100px; margin: 0 auto; padding: 24px; }
        .card { background: white; padding: 18px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.06); margin-bottom: 16px; }
        h1 { margin-top: 0; }
        form { margin-bottom: 12px; }
        label { display: block; margin-bottom: 4px; font-weight: bold; }
        input, textarea, select { width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 8px; }
        button { background: #00adb5; color: white; padding: 8px 14px; border: none; border-radius: 8px; cursor: pointer; }
        .grid { display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); }
        .tag { background: #e8f7f8; color: #00adb5; padding: 2px 8px; border-radius: 8px; font-size: 12px; }
        .logout { float: right; }
        .notice { padding: 10px; background: #e0f7e9; border: 1px solid #9ad3ae; border-radius: 8px; margin-bottom: 12px; }
        .error { padding: 10px; background: #ffe5e5; border: 1px solid #ff9b9b; border-radius: 8px; margin-bottom: 12px; }
        .inline-form { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
        .inline-form input[type="number"], .inline-form input[type="text"] { width: auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Admin Paneli</h1>
            <?php if (isset($_GET['notice'])): ?>
                <div class="notice"><?= htmlspecialchars($_GET['notice']) ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <div class="error"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>
            <?php if (!isAdminLoggedIn()): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="login">
                    <label>Kullanıcı Adı</label>
                    <input type="text" name="username" required>
                    <label>Şifre</label>
                    <input type="password" name="password" required>
                    <button type="submit">Giriş Yap</button>
                </form>
            <?php else: ?>
                <form method="POST" class="logout">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit">Çıkış</button>
                </form>
                <p>Hoş geldin, <?= htmlspecialchars($_SESSION['admin']) ?>! Varsayılan dil: <?= htmlspecialchars($config['app']['default_language']) ?></p>
            <?php endif; ?>
        </div>

        <?php if (isAdminLoggedIn()): ?>
        <div class="grid">
            <div class="card">
                <h2>Genel Ayarlar</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="update_settings">
                    <label>Restoran Adı</label>
                    <input type="text" name="restaurant_name" value="<?= htmlspecialchars($settings['restaurant_name'] ?? '') ?>" placeholder="Menü başlığı">
                    <label>Tanıtım Notu</label>
                    <textarea name="hero_note" rows="2" placeholder="Kısa slogan veya mesaj"><?= htmlspecialchars($settings['hero_note'] ?? '') ?></textarea>
                    <label>Logo URL</label>
                    <input type="text" name="logo_url" value="<?= htmlspecialchars($settings['logo_url'] ?? '') ?>" placeholder="https://...">
                    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
                        <div>
                            <label>Birincil Renk</label>
                            <input type="text" name="brand_primary" value="<?= htmlspecialchars($settings['brand_primary'] ?? '#222831') ?>" placeholder="#222831">
                        </div>
                        <div>
                            <label>Vurgu Rengi</label>
                            <input type="text" name="brand_accent" value="<?= htmlspecialchars($settings['brand_accent'] ?? '#00adb5') ?>" placeholder="#00adb5">
                        </div>
                    </div>
                    <label>Para Birimi Simgesi</label>
                    <input type="text" name="currency_symbol" value="<?= htmlspecialchars($settings['currency_symbol'] ?? $config['app']['currency_symbol']) ?>" placeholder="₺, €, $ ...">
                    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
                        <div>
                            <label>Wi-Fi Adı</label>
                            <input type="text" name="wifi_name" value="<?= htmlspecialchars($settings['wifi_name'] ?? '') ?>">
                        </div>
                        <div>
                            <label>Wi-Fi Şifresi</label>
                            <input type="text" name="wifi_password" value="<?= htmlspecialchars($settings['wifi_password'] ?? '') ?>">
                        </div>
                    </div>
                    <label>Rezervasyon Telefonu</label>
                    <input type="text" name="contact_phone" value="<?= htmlspecialchars($settings['contact_phone'] ?? '') ?>">
                    <label>Adres</label>
                    <textarea name="address" rows="2" placeholder="Açık adres ve açıklama"><?= htmlspecialchars($settings['address'] ?? '') ?></textarea>
                    <button type="submit">Ayarları Kaydet</button>
                </form>
            </div>

            <div class="card">
                <h2>Dil Ekle</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add_language">
                    <label>Dil Kodu (ör. tr, en)</label>
                    <input type="text" name="code" required>
                    <label>Dil Adı</label>
                    <input type="text" name="name" required>
                    <button type="submit">Kaydet</button>
                </form>
                <p>Aktif Diller: <?php foreach ($languages as $language) { echo '<span class="tag">' . htmlspecialchars($language['code']) . '</span> '; } ?></p>
            </div>

            <div class="card">
                <h2>Kategori Oluştur</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add_category">
                    <label>Slug</label>
                    <input type="text" name="slug" required>
                    <label>Gösterim Sırası</label>
                    <input type="number" name="position" value="0">
                    <label><input type="checkbox" name="featured"> Öne çıkar</label>
                    <button type="submit">Kategori Ekle</button>
                </form>
            </div>

            <div class="card">
                <h2>Ürün Oluştur</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add_item">
                    <label>Kategori</label>
                    <select name="category_id" required>
                        <option value="">Seçiniz</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['slug']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Fiyat</label>
                    <input type="number" step="0.01" name="price" required>
                    <label>Kalori (kcal)</label>
                    <input type="number" name="calories" placeholder="Örn: 320">
                    <label>Görsel URL</label>
                    <input type="text" name="image_url" placeholder="https://...">
                    <label>
                        <input type="checkbox" name="available" checked> Stokta
                    </label>
                    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));">
                        <label><input type="checkbox" name="spicy"> 🌶️ Acı</label>
                        <label><input type="checkbox" name="vegan"> 🌱 Vegan</label>
                        <label><input type="checkbox" name="gluten_free"> 🚫🌾 Glutensiz</label>
                        <label><input type="checkbox" name="is_special"> 👨‍🍳 Şefin Önerisi</label>
                    </div>
                    <button type="submit">Ürün Ekle</button>
                </form>
            </div>

            <div class="card">
                <h2>Kategori Çevirisi</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="translate_category">
                    <label>Kategori</label>
                    <select name="category_id" required>
                        <option value="">Seçiniz</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['slug']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Dil</label>
                    <select name="language_code" required>
                        <option value="">Seçiniz</option>
                        <?php foreach ($languages as $language): ?>
                            <option value="<?= htmlspecialchars($language['code']) ?>"><?= htmlspecialchars($language['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Başlık</label>
                    <input type="text" name="name" required>
                    <label>Açıklama</label>
                    <textarea name="description" rows="2"></textarea>
                    <button type="submit">Kaydet</button>
                </form>
            </div>

            <div class="card">
                <h2>Ürün Çevirisi</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="translate_item">
                    <label>Ürün</label>
                    <select name="item_id" required>
                        <option value="">Seçiniz</option>
                        <?php foreach ($items as $item): ?>
                            <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['category_slug'] . ' #' . $item['id']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Dil</label>
                    <select name="language_code" required>
                        <option value="">Seçiniz</option>
                        <?php foreach ($languages as $language): ?>
                            <option value="<?= htmlspecialchars($language['code']) ?>"><?= htmlspecialchars($language['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Başlık</label>
                    <input type="text" name="name" required>
                    <label>Açıklama</label>
                    <textarea name="description" rows="2"></textarea>
                    <button type="submit">Kaydet</button>
                </form>
            </div>
        </div>

        <div class="card">
            <h2>İçerik Özeti</h2>
            <h3>Kategoriler</h3>
            <ul>
                <?php foreach ($categories as $category): ?>
                    <li>
                        <?= htmlspecialchars($category['slug']) ?> <span class="tag">#<?= $category['id'] ?></span>
                        <?= $category['featured'] ? '<span class="tag">Öne Çıkan</span>' : '' ?>
                        <form method="POST" class="inline-form" style="margin-top:6px;">
                            <input type="hidden" name="action" value="update_category_featured">
                            <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                            <label style="font-weight:normal;"><input type="checkbox" name="featured" <?= $category['featured'] ? 'checked' : '' ?>> Öne çıkar</label>
                            <label style="font-weight:normal;">Sıra <input type="number" name="position" value="<?= (int)$category['position'] ?>" style="width:70px;"></label>
                            <button type="submit">Kaydet</button>
                        </form>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Kategoriyi silmek istediğinize emin misiniz?');">
                            <input type="hidden" name="action" value="delete_category">
                            <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                            <button type="submit" style="background:#ff6b6b;">Sil</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
            <h3>Ürünler</h3>
            <ul>
                <?php foreach ($items as $item): ?>
                    <li>
                        <strong><?= htmlspecialchars($item['category_slug']) ?></strong> / #<?= $item['id'] ?> — <?= htmlspecialchars($settings['currency_symbol'] ?? '₺') ?><?= number_format((float)$item['price'], 2) ?>
                        <?= $item['available'] ? '' : '<span class="tag">Tükendi</span>' ?>
                        <?= $item['spicy'] ? '<span class="tag">🌶️</span>' : '' ?>
                        <?= $item['vegan'] ? '<span class="tag">🌱</span>' : '' ?>
                        <?= $item['gluten_free'] ? '<span class="tag">🚫🌾</span>' : '' ?>
                        <?= $item['is_special'] ? '<span class="tag">👨‍🍳</span>' : '' ?>
                        <?php if (!empty($item['calories'])): ?> <span class="tag"><?= (int)$item['calories'] ?> kcal</span><?php endif; ?>
                        <form method="POST" class="inline-form" style="margin-top:6px;">
                            <input type="hidden" name="action" value="update_item_flags">
                            <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                            <label><input type="checkbox" name="available" <?= $item['available'] ? 'checked' : '' ?>> Stokta</label>
                            <label><input type="checkbox" name="spicy" <?= $item['spicy'] ? 'checked' : '' ?>> 🌶️</label>
                            <label><input type="checkbox" name="vegan" <?= $item['vegan'] ? 'checked' : '' ?>> 🌱</label>
                            <label><input type="checkbox" name="gluten_free" <?= $item['gluten_free'] ? 'checked' : '' ?>> 🚫🌾</label>
                            <label><input type="checkbox" name="is_special" <?= $item['is_special'] ? 'checked' : '' ?>> 👨‍🍳</label>
                            <button type="submit">Güncelle</button>
                        </form>
                        <form method="POST" class="inline-form">
                            <input type="hidden" name="action" value="update_item_details">
                            <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                            <label>Fiyat <input type="number" step="0.01" name="price" value="<?= number_format((float)$item['price'], 2, '.', '') ?>" style="width:110px;"></label>
                            <label>Kalori <input type="number" name="calories" value="<?= htmlspecialchars($item['calories']) ?>" style="width:90px;"></label>
                            <label>Görsel URL <input type="text" name="image_url" value="<?= htmlspecialchars($item['image_url']) ?>" style="width:200px;"></label>
                            <button type="submit">Kaydet</button>
                        </form>
                        <form method="POST" onsubmit="return confirm('Ürünü silmek istediğinize emin misiniz?');">
                            <input type="hidden" name="action" value="delete_item">
                            <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                            <button type="submit" style="background:#ff6b6b;">Ürünü Sil</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
