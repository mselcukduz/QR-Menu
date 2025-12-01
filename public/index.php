<?php
require __DIR__ . '/../src/bootstrap.php';

$lang = getActiveLanguage($pdo, $config['app']['default_language']);
$languages = getAvailableLanguages($pdo);
$menu = fetchMenu($pdo);
$settings = array_merge(['currency_symbol' => $config['app']['currency_symbol']], getSettings($pdo));
$appUrl = sprintf('%s://%s%s', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http', $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);
$qrData = urlencode($appUrl);
$restaurantName = $settings['restaurant_name'] ?: $config['app']['name'];
$primary = $settings['brand_primary'] ?: '#222831';
$accent = $settings['brand_accent'] ?: '#00adb5';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($restaurantName) ?></title>
    <style>
        :root {
            --primary: <?= htmlspecialchars($primary) ?>;
            --accent: <?= htmlspecialchars($accent) ?>;
            --bg: #f8f9fb;
        }
        body { font-family: Arial, sans-serif; margin: 0; background: var(--bg); color: #222; }
        header { background: var(--primary); color: white; padding: 16px; display: flex; justify-content: space-between; align-items: center; }
        header h1 { margin: 0; font-size: 20px; }
        .language-switcher a { color: #fff; margin-left: 8px; text-decoration: none; padding: 6px 10px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); }
        .language-switcher a.active { background: var(--accent); border-color: var(--accent); }
        .container { max-width: 1180px; margin: 0 auto; padding: 24px; display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
        .card { background: white; padding: 16px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); }
        .category { margin-bottom: 16px; border-left: 4px solid transparent; padding-left: 8px; }
        .category.featured { border-color: var(--accent); }
        .category h2 { margin: 0 0 6px; color: var(--primary); display:flex; align-items:center; gap:8px; }
        .category p { margin: 0 0 12px; color: #555; }
        .item { display: grid; grid-template-columns: 1fr auto; column-gap: 16px; padding: 12px 0; border-bottom: 1px solid #eee; }
        .item:last-child { border-bottom: none; }
        .item .name { font-weight: bold; }
        .tag { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 12px; background: #e8f7f8; color: var(--accent); margin-left: 8px; }
        .item .meta { color: #666; font-size: 13px; margin-top: 4px; }
        .qr-box { text-align: center; }
        .qr-box img { border: 6px solid white; border-radius: 12px; box-shadow: 0 6px 20px rgba(0,0,0,0.08); }
        .qr-box p { margin-top: 8px; color: #555; }
        .item figure { margin: 0; }
        .thumb { width: 96px; height: 72px; object-fit: cover; border-radius: 10px; border: 1px solid #eee; box-shadow: inset 0 1px 2px rgba(0,0,0,0.04); }
        .item-main { display: grid; grid-template-columns: auto 1fr; column-gap: 12px; align-items: start; }
        @media (max-width: 900px) { .container { grid-template-columns: 1fr; } .item-main { grid-template-columns: 72px 1fr; } }
        @media (max-width: 600px) { .item { grid-template-columns: 1fr; row-gap: 6px; } .item-main { grid-template-columns: 1fr; } .thumb { width: 100%; height: 160px; } }
    </style>
</head>
<body>
<header>
    <div style="display:flex; align-items:center; gap:12px;">
        <?php if (!empty($settings['logo_url'])): ?>
            <img src="<?= htmlspecialchars($settings['logo_url']) ?>" alt="Logo" style="height:42px; border-radius:8px; background:#fff; padding:6px;">
        <?php endif; ?>
        <div>
            <h1 style="margin-bottom:4px;"><?= htmlspecialchars($restaurantName) ?></h1>
            <?php if (!empty($settings['hero_note'])): ?>
                <div style="color: rgba(255,255,255,0.75); font-size: 14px;"><?= htmlspecialchars($settings['hero_note']) ?></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="language-switcher">
        <?php foreach ($languages as $language): ?>
            <a href="?lang=<?= htmlspecialchars($language['code']) ?>" class="<?= $language['code'] === $lang ? 'active' : '' ?>">
                <?= htmlspecialchars($language['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</header>

<div class="container">
    <div class="card">
        <?php if (empty($menu)): ?>
            <p>Henüz menü eklenmedi. Admin panelinden yeni ürünler ekleyin.</p>
        <?php else: ?>
            <?php foreach ($menu as $category): ?>
                <?php $categoryText = translateCategory($pdo, (int)$category['id'], $lang); ?>
                <div class="category <?= $category['featured'] ? 'featured' : '' ?>">
                    <h2>
                        <?= htmlspecialchars($categoryText['name'] ?? $category['slug']) ?>
                        <?php if ($category['featured']): ?><span class="tag">⭐ Öne Çıkan</span><?php endif; ?>
                    </h2>
                    <?php if (!empty($categoryText['description'])): ?>
                        <p><?= nl2br(htmlspecialchars($categoryText['description'])) ?></p>
                    <?php endif; ?>
                    <?php foreach ($category['items'] as $item): ?>
                        <?php $itemText = translateItem($pdo, (int)$item['id'], $lang); ?>
                        <div class="item">
                            <div class="item-main">
                                <?php if (!empty($item['image_url'])): ?>
                                    <figure><img src="<?= htmlspecialchars($item['image_url']) ?>" class="thumb" alt="<?= htmlspecialchars($itemText['name'] ?? 'Ürün') ?>"></figure>
                                <?php endif; ?>
                                <div>
                                    <span class="name"><?= htmlspecialchars($itemText['name'] ?? 'Ürün') ?></span>
                                    <?php if (!$item['available']): ?>
                                        <span class="tag">Tükendi</span>
                                    <?php endif; ?>
                                    <?php if ($item['is_special']): ?><span class="tag" title="Şefin Önerisi">👨‍🍳 Şefin Önerisi</span><?php endif; ?>
                                    <?php if ($item['spicy']): ?><span class="tag" title="Acı">🌶️ Acı</span><?php endif; ?>
                                    <?php if ($item['vegan']): ?><span class="tag" title="Vegan">🌱 Vegan</span><?php endif; ?>
                                    <?php if ($item['gluten_free']): ?><span class="tag" title="Glutensiz">🚫🌾 Glutensiz</span><?php endif; ?>
                                    <?php if (!empty($itemText['description'])): ?>
                                        <div class="description"><?= nl2br(htmlspecialchars($itemText['description'])) ?></div>
                                    <?php endif; ?>
                                    <div class="meta">
                                        <?php if (!empty($item['calories'])): ?>
                                            <span><?= (int)$item['calories'] ?> kcal</span>
                                            <span style="margin:0 6px;">•</span>
                                        <?php endif; ?>
                                        <span><?= htmlspecialchars($settings['currency_symbol'] ?? '₺') ?><?= number_format((float)$item['price'], 2) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="card qr-box">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=<?= $qrData ?>" alt="QR Code">
        <p>Bu kodu masalara yerleştirerek dijital menünüzü paylaşabilirsiniz.</p>
        <?php if (!empty($settings['wifi_name'])): ?>
            <p><strong>Wi-Fi:</strong> <?= htmlspecialchars($settings['wifi_name']) ?><?php if (!empty($settings['wifi_password'])): ?> — <?= htmlspecialchars($settings['wifi_password']) ?><?php endif; ?></p>
        <?php endif; ?>
        <?php if (!empty($settings['contact_phone'])): ?>
            <p><strong>Rezervasyon:</strong> <?= htmlspecialchars($settings['contact_phone']) ?></p>
        <?php endif; ?>
        <?php if (!empty($settings['address'])): ?>
            <p><strong>Adres:</strong> <?= nl2br(htmlspecialchars($settings['address'])) ?></p>
        <?php endif; ?>
        <p><a href="/admin.php">Admin Paneline Git</a></p>
    </div>
</div>
</body>
</html>
