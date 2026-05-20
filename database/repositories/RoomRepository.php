<?php

require_once __DIR__ . '/../Database.php';

class RoomRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Yeni oda oluştur
    public function createRoom(string $name, int $createdBy): int {
        $stmt = $this->db->prepare("INSERT INTO rooms (name, created_by) VALUES (:name, :created_by)");
        $stmt->execute([':name' => $name, ':created_by' => $createdBy]);
        return (int) $this->db->lastInsertId();
    }

    // Tüm odaları listele
    public function getAllRooms(): array {
        $stmt = $this->db->query("SELECT * FROM rooms ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    // Tek oda getir
    public function getRoomById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM rooms WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $room = $stmt->fetch();
        return $room ?: null;
    }

    // Oda sil
    public function deleteRoom(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM rooms WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
}