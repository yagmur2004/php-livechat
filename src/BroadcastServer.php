<?php
namespace App;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class BroadcastServer implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId}) from {$conn->remoteAddress}\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // JSON parse et
        $data = json_decode($msg, true);

        if (!$data || !isset($data['type'])) {
            $from->send(json_encode(['error' => 'Geçersiz mesaj formatı']));
            return;
        }

        echo "Broadcast message from {$from->resourceId}: $msg\n";

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
