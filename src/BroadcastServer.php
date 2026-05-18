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
        echo "Broadcast message from {$from->resourceId}: $msg\n";
        foreach ($this->clients as $client) {
            // Broadcasting to all connected clients
            $client->send($msg);
            
            // If you want to broadcast to everyone EXCEPT the sender, use this instead:
            // if ($from !== $client) {
            //     $client->send($msg);
            // }
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
