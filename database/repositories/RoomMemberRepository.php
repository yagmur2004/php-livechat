<?php

require_once __DIR__ . '/../Database.php';

class RoomMemberRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Odaya katıl
    public function joinRoom(int $roomId, int $userId): void {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO room_members (room_id, user_id) 
            VALUES (:room_id, :user_id)
        ");
        $stmt->execute([':room_id' => $roomId, ':user_id' => $userId]);
    }

    // Odadan ayrıl
    public function leaveRoom(int $roomId, int $userId): void {
        $stmt = $this->db->prepare("
            DELETE FROM room_members 
            WHERE room_id = :room_id AND user_id = :user_id
        ");
        $stmt->execute([':room_id' => $roomId, ':user_id' => $userId]);
    }

    // Odadaki tüm üyeleri getir
    public function getRoomMembers(int $roomId): array {
        $stmt = $this->db->prepare("
            SELECT users.id, users.username, users.is_online
            FROM room_members
            JOIN users ON room_members.user_id = users.id
            WHERE room_members.room_id = :room_id
        ");
        $stmt->execute([':room_id' => $roomId]);
        return $stmt->fetchAll();
    }

    // Kullanıcı bu odada mı?
    public function isMember(int $roomId, int $userId): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM room_members 
            WHERE room_id = :room_id AND user_id = :user_id
        ");
        $stmt->execute([':room_id' => $roomId, ':user_id' => $userId]);
        return (bool) $stmt->fetchColumn();
    }
}