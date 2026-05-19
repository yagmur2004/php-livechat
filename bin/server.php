<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\BroadcastServer;

$port = 8080;

// Explicitly binding to 0.0.0.0 so Docker exposes it correctly
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new BroadcastServer()
        )
    ),
    $port,
    '0.0.0.0'
);

echo "Starting WebSocket broadcast server on port {$port}...\n";
$server->run();
