<?php
namespace App;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../database/repositories/MessageRepository.php';
require_once __DIR__ . '/../database/repositories/RoomMemberRepository.php';

class BroadcastServer implements MessageComponentInterface {
    protected $clients;
    protected $msgRepo;
    protected $memberRepo;

    public function __construct() {
        $this->clients   = new \SplObjectStorage;
        $this->msgRepo   = new \MessageRepository();
        $this->memberRepo = new \RoomMemberRepository();
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId}) from {$conn->remoteAddress}\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        if (!$data || !isset($data['type'])) {
            $from->send(json_encode(['error' => 'Geçersiz mesaj formatı']));
            return;
        }

        echo "Broadcast message from {$from->resourceId}: $msg\n";

        switch ($data['type']) {

            case 'message':
                // Mesajı DB'ye kaydet
                if (isset($data['room_id'], $data['user_id'], $data['text'])) {
                    $this->msgRepo->saveMessage(
                        (int) $data['room_id'],
                        (int) $data['user_id'],
                        $data['text']
                    );
                }
                break;

            case 'join':
                // Odaya katılımı DB'ye kaydet
                if (isset($data['room_id'], $data['user_id'])) {
                    $this->memberRepo->joinRoom(
                        (int) $data['room_id'],
                        (int) $data['user_id']
                    );
                }
                break;

            case 'leave':
                // Odadan ayrılmayı DB'ye kaydet
                if (isset($data['room_id'], $data['user_id'])) {
                    $this->memberRepo->leaveRoom(
                        (int) $data['room_id'],
                        (int) $data['user_id']
                    );
                }
                break;
        }

        // Herkese ilet
        foreach ($this->clients as $client) {
            $client->send($msg);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}