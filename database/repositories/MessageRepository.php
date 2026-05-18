<?php

require_once __DIR__ . '/../Database.php';

class MessageRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Mesaj kaydet
    public function saveMessage(int $roomId, int $userId, string $content): int {
        $stmt = $this->db->prepare("
            INSERT INTO messages (room_id, user_id, content) 
            VALUES (:room_id, :user_id, :content)
        ");
        $stmt->execute([
            ':room_id' => $roomId,
            ':user_id' => $userId,
            ':content' => $content
        ]);
        return (int) $this->db->lastInsertId();
    }

    // Odanın mesaj geçmişini getir (son 50 mesaj)
    public function getRoomHistory(int $roomId, int $limit = 50): array {
        $stmt = $this->db->prepare("
            SELECT messages.id, messages.content, messages.sent_at,
                   users.username
            FROM messages
            JOIN users ON messages.user_id = users.id
            WHERE messages.room_id = :room_id
            ORDER BY messages.sent_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':room_id', $roomId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return array_reverse($stmt->fetchAll());
    }

    // Tek mesaj getir
    public function getMessageById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT messages.*, users.username 
            FROM messages
            JOIN users ON messages.user_id = users.id
            WHERE messages.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $message = $stmt->fetch();
        return $message ?: null;
    }
}