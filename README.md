<<<<<<< HEAD
# PHP WebSocket Broadcast Server

This project is a simple, high-performance WebSocket broadcast server built with PHP, the Ratchet library, and Docker. It is designed to receive a message from any connected client and broadcast it to all other connected clients.

A lightweight HTML/JavaScript test client is included for easy local testing.

## Prerequisites

Before you begin, ensure you have the following installed on your system:
- **Docker**: [Get Docker](https://docs.docker.com/get-docker/)
- **Docker Compose**: [Install Docker Compose](https://docs.docker.com/compose/install/) (Included with Docker Desktop for Windows and Mac).

## How to Run the Server (Local Development)

Follow these steps to get the server up and running on your local machine.

### 1. Start the Services

Navigate to the project's root directory in your terminal and run the following command:

```sh
docker-compose up -d
```

This command will:
- Build the PHP Docker image with all required extensions.
- Install the Composer dependencies (like Ratchet).
- Start two services in the background (`-d`):
  - `websocket-server`: The PHP WebSocket server, listening on **port 8080**.
  - `test-ui`: A lightweight Nginx server to host the testing client, accessible on **port 8081**.

### 2. How to Test the Connection

Once the containers are running, you can easily test the broadcast functionality.

1.  **Open the Test Client**: Open your web browser and navigate to:
    ```
    http://localhost:8081
    ```

2.  **Connect to the Server**: The page will load the "WebSocket Broadcast Tester". The server URL should already be set to `ws://localhost:8080`. Click the **Connect** button.
    - You should see a "Successfully connected to the server!" message in the log.

3.  **Test the Broadcast**:
    - Type any message into the "Enter message to broadcast..." input box and click **Send Message**.
    - The message will appear in the log twice: once as "Sent" and once as "Received", because the server broadcasts the message back to all clients, including the sender.
    - To see the real power of broadcasting, open `http://localhost:8081` in another browser tab or window, connect, and send a message from one window. It will instantly appear in the other.

## Stopping the Server

To stop all running services and remove the containers, run the following command in your project directory:

```sh
docker-compose down
```

## Project Structure

A brief overview of the key files in this project:

- `docker-compose.yml`: Defines and configures the Docker services (`websocket-server`, `test-ui`).
- `Dockerfile`: The recipe for building the custom PHP container image.
- `composer.json`: Declares the PHP project dependencies (i.e., `cboden/ratchet`).
- `src/BroadcastServer.php`: The core application logic. This PHP class handles WebSocket events like `onOpen`, `onMessage`, and `onClose`.
- `bin/server.php`: The entry point script that starts the Ratchet `IoServer`.
- `test-client.html`: A self-contained HTML file for connecting to and testing the WebSocket server.
=======
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




// LATEST UPDATE
# PHP-LiveChat & WebSocket Broadcast Server

A real-time, multi-room chat application built with PHP, Ratchet WebSocket, MySQL, and Docker.

---

## 💻 1. Setup and Run with Docker (Recommended Method)

The entire project infrastructure—including dependencies, database drivers, web servers, and the WebSocket layer—has been containerized and fully integrated by **Eda (DevOps & Integration)**. You do not need to perform any manual PHP or MySQL configurations on your local machine.

### Prerequisites
* Ensure that **Docker Desktop** is installed and running on your system.

### Steps to Run
Open your terminal or PowerShell, navigate to the project's root directory, and run the following single command:
```sh
docker compose up -d --build
