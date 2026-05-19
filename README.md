# PHP-LiveChat

Gerçek zamanlı çok odalı chat uygulaması — PHP, Ratchet WebSocket, MySQL.

## Kurulum

1. Repoyu klonla: `git clone https://github.com/yagmur2004/php-livechat.git`
2. XAMPP'ta Apache ve MySQL'i başlat
3. `config/config.php` dosyasını oluştur:

```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'php_livechat');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
```

4. `database/schema.sql` dosyasını phpMyAdmin'de çalıştır

## DB Katmanını Kullanmak (Kişi 2 ve Kişi 1 için)

```php
require_once 'database/Database.php';
require_once 'database/repositories/RoomRepository.php';
require_once 'database/repositories/MessageRepository.php';
require_once 'database/repositories/RoomMemberRepository.php';

// Oda oluştur
$roomRepo = new RoomRepository();
$roomRepo->createRoom('genel', $userId);

// Mesaj kaydet
$msgRepo = new MessageRepository();
$msgRepo->saveMessage($roomId, $userId, 'Merhaba!');

// Odaya katıl
$memberRepo = new RoomMemberRepository();
$memberRepo->joinRoom($roomId, $userId);
```

## Klasör Yapısı