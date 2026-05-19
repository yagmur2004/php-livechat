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
