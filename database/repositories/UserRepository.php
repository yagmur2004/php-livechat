<?php

/**
 * UserRepository.php
 *
 * Kişi 3'ün repository'leriyle birebir aynı yapı:
 *   - require_once Database.php
 *   - __construct: Database::getInstance()->getConnection()
 *   - Global namespace (namespace yok), sınıf adı doğrudan kullanılır
 *
 * BroadcastServer.php şu anda UserRepository'yi çağırmıyor;
 * ancak Hafta 2 entegrasyonunda onOpen içinden çağrılabilmesi için
 * setOnlineStatus() metodu hazır bırakıldı.
 */

require_once __DIR__ . '/../Database.php';

class UserRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Yeni kullanıcı oluşturur.
     * Şifre dışarıdan hash'lenmiş olarak gelir (password_hash ile).
     */
    public function create(string $username, string $email, string $hashedPassword): int {
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password)
            VALUES (:username, :email, :password)
        ");
        $stmt->execute([
            ':username' => $username,
            ':email'    => $email,
            ':password' => $hashedPassword,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * E-posta adresine göre kullanıcı bulur.
     * login.php içinde kullanılır.
     */
    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("
            SELECT id, username, email, password, is_online, created_at
            FROM users
            WHERE email = :email
            LIMIT 1
        ");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Kullanıcı adına göre kullanıcı bulur.
     * register.php içinde benzersizlik kontrolü için kullanılır.
     */
    public function findByUsername(string $username): ?array {
        $stmt = $this->db->prepare("
            SELECT id, username, email, is_online, created_at
            FROM users
            WHERE username = :username
            LIMIT 1
        ");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * ID'ye göre kullanıcı bulur.
     * auth_check.php içinde session doğrulaması için kullanılır.
     */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT id, username, email, is_online, created_at
            FROM users
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Çevrimiçi durumu günceller.
     * BroadcastServer::onOpen / onClose tarafından çağrılabilir (Hafta 2).
     */
    public function setOnlineStatus(int $userId, bool $isOnline): void {
        $stmt = $this->db->prepare("
            UPDATE users SET is_online = :status WHERE id = :id
        ");
        $stmt->execute([
            ':status' => (int) $isOnline,
            ':id'     => $userId,
        ]);
    }
}
