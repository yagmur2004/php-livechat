<?php

/**
 * chat.php
 * Ana chat sayfası — korumalı.
 *
 * Kişi 1'in BroadcastServer.php'si şu mesaj tiplerini bekliyor:
 *   { "type": "message", "room_id": 1, "user_id": 5, "text": "..." }
 *   { "type": "join",    "room_id": 1, "user_id": 5 }
 *   { "type": "leave",   "room_id": 1, "user_id": 5 }
 *
 * Bu sayfa tam olarak bu formatı gönderir.
 * WebSocket URL'si: ws://localhost:8080  (Kişi 1'in server.php port 8080'de açıyor)
 */

require_once __DIR__ . '/../auth/auth_check.php';  // $currentUser set eder, yoksa login'e atar
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../database/repositories/RoomRepository.php';
require_once __DIR__ . '/../database/repositories/MessageRepository.php';

// Tüm odaları listele (sol panel için)
$roomRepo = new RoomRepository();
$rooms    = $roomRepo->getAllRooms();

// Aktif oda: query string'den al, yoksa ilk odayı seç
$activeRoomId = isset($_GET['room']) ? (int) $_GET['room'] : ($rooms[0]['id'] ?? null);
$activeRoom   = null;
$history      = [];

if ($activeRoomId) {
    $activeRoom = $roomRepo->getRoomById($activeRoomId);
    if ($activeRoom) {
        $msgRepo = new MessageRepository();
        $history = $msgRepo->getRoomHistory($activeRoomId, 50);
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $activeRoom ? htmlspecialchars($activeRoom['name']) . ' — ' : '' ?><?= APP_NAME ?></title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #1a1a2e; color: #e0e0e0; height: 100vh; display: flex; flex-direction: column; overflow: hidden; }

        /* ── Header ── */
        header { background: #16213e; padding: .75rem 1.25rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #0f3460; flex-shrink: 0; }
        header h1 { font-size: 1rem; color: #e94560; }
        .user-info { display: flex; align-items: center; gap: .75rem; font-size: .85rem; }
        .user-info strong { color: #e94560; }
        .ws-badge { font-size: .75rem; padding: .2rem .55rem; border-radius: 20px; background: #555; color: #fff; }
        .ws-badge.connected    { background: #2d6a4f; }
        .ws-badge.disconnected { background: #7b2d2d; }
        .logout-link { color: #aaa; text-decoration: none; font-size: .8rem; }
        .logout-link:hover { color: #e94560; }

        /* ── Layout ── */
        .chat-layout { display: flex; flex: 1; overflow: hidden; }

        /* ── Sidebar ── */
        .sidebar { width: 220px; background: #0f3460; border-right: 1px solid #1a4a7a; display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar-title { padding: .75rem 1rem; font-size: .75rem; text-transform: uppercase; letter-spacing: .08em; color: #7a9cc4; border-bottom: 1px solid #1a4a7a; }
        .room-list { flex: 1; overflow-y: auto; }
        .room-item { display: block; padding: .6rem 1rem; color: #aac; text-decoration: none; font-size: .9rem; border-left: 3px solid transparent; }
        .room-item:hover { background: #1a4a7a; color: #fff; }
        .room-item.active { background: #1a4a7a; border-left-color: #e94560; color: #fff; }
        .room-item::before { content: '# '; color: #e94560; font-weight: 700; }

        /* ── Main chat ── */
        .chat-main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .chat-header { padding: .65rem 1.25rem; background: #16213e; border-bottom: 1px solid #0f3460; font-size: .9rem; color: #aac; flex-shrink: 0; }
        .chat-header strong { color: #e0e0e0; }

        /* ── Messages ── */
        .messages { flex: 1; overflow-y: auto; padding: 1rem 1.25rem; display: flex; flex-direction: column; gap: .65rem; }
        .message { display: flex; flex-direction: column; }
        .message .meta { font-size: .75rem; color: #7a9cc4; margin-bottom: .2rem; }
        .message .meta strong { color: #e94560; margin-right: .4rem; }
        .message .bubble { background: #16213e; border: 1px solid #1e3a5f; border-radius: 0 8px 8px 8px; padding: .5rem .85rem; font-size: .9rem; line-height: 1.5; max-width: 75%; }
        .message.own .bubble { background: #0f3460; border-color: #1e5a9f; border-radius: 8px 0 8px 8px; align-self: flex-end; }
        .message.own .meta { align-self: flex-end; }
        .system-msg { font-size: .8rem; color: #556; text-align: center; padding: .25rem; }

        /* ── Input ── */
        .chat-input-wrap { padding: .75rem 1.25rem; background: #16213e; border-top: 1px solid #0f3460; display: flex; gap: .6rem; flex-shrink: 0; }
        .chat-input-wrap input { flex: 1; background: #0f3460; border: 1px solid #1e4d80; border-radius: 8px; color: #eee; padding: .55rem .9rem; font-size: .9rem; outline: none; }
        .chat-input-wrap input:focus { border-color: #e94560; }
        .chat-input-wrap input:disabled { opacity: .45; }
        .send-btn { background: #e94560; color: #fff; border: none; border-radius: 8px; padding: .55rem 1.1rem; font-size: .9rem; cursor: pointer; }
        .send-btn:disabled { opacity: .45; cursor: default; }

        /* ── No room ── */
        .no-room { flex: 1; display: flex; align-items: center; justify-content: center; color: #556; font-size: .95rem; }
    </style>
</head>
<body>

<header>
    <h1>💬 <?= APP_NAME ?></h1>
    <div class="user-info">
        <span class="ws-badge disconnected" id="ws-badge">● Bağlanıyor...</span>
        Hoş geldin, <strong><?= htmlspecialchars($currentUser['username']) ?></strong>
        <a href="/public/logout.php" class="logout-link">Çıkış</a>
    </div>
</header>

<div class="chat-layout">

    <!-- Oda listesi -->
    <aside class="sidebar">
        <div class="sidebar-title">Odalar</div>
        <nav class="room-list">
            <?php foreach ($rooms as $room): ?>
                <a href="/public/chat.php?room=<?= $room['id'] ?>"
                   class="room-item <?= $room['id'] === $activeRoomId ? 'active' : '' ?>">
                    <?= htmlspecialchars($room['name']) ?>
                </a>
            <?php endforeach; ?>
            <?php if (empty($rooms)): ?>
                <p style="padding:.75rem 1rem;color:#556;font-size:.8rem;">Henüz oda yok.</p>
            <?php endif; ?>
        </nav>
    </aside>

    <!-- Chat alanı -->
    <main class="chat-main">

        <?php if ($activeRoom): ?>

            <div class="chat-header">
                <strong># <?= htmlspecialchars($activeRoom['name']) ?></strong>
            </div>

            <div class="messages" id="messages">
                <?php foreach ($history as $msg): ?>
                    <div class="message <?= $msg['username'] === $currentUser['username'] ? 'own' : '' ?>">
                        <div class="meta">
                            <strong><?= htmlspecialchars($msg['username']) ?></strong>
                            <?= htmlspecialchars(date('H:i', strtotime($msg['sent_at']))) ?>
                        </div>
                        <div class="bubble"><?= htmlspecialchars($msg['content']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="chat-input-wrap">
                <input type="text" id="msg-input" placeholder="Mesajınızı yazın..." disabled>
                <button class="send-btn" id="send-btn" disabled>Gönder</button>
            </div>

        <?php else: ?>
            <div class="no-room">Sol taraftan bir oda seçin.</div>
        <?php endif; ?>

    </main>
</div>

<?php if ($activeRoom): ?>
<script>
// ── Kişi 1'in BroadcastServer.php'siyle tam uyumlu mesaj yapısı ──────────
const USER_ID   = <?= (int) $currentUser['id'] ?>;
const USERNAME  = <?= json_encode($currentUser['username']) ?>;
const ROOM_ID   = <?= (int) $activeRoomId ?>;
const WS_URL    = 'ws://localhost:8080';

const messagesEl = document.getElementById('messages');
const msgInput   = document.getElementById('msg-input');
const sendBtn    = document.getElementById('send-btn');
const wsBadge    = document.getElementById('ws-badge');

let ws = null;

function scrollBottom() {
    messagesEl.scrollTop = messagesEl.scrollHeight;
}
scrollBottom();

function appendMessage(username, text, time) {
    const isOwn = username === USERNAME;
    const div   = document.createElement('div');
    div.className = 'message' + (isOwn ? ' own' : '');
    div.innerHTML = `
        <div class="meta"><strong>${escHtml(username)}</strong> ${time}</div>
        <div class="bubble">${escHtml(text)}</div>`;
    messagesEl.appendChild(div);
    scrollBottom();
}

function appendSystem(text) {
    const div = document.createElement('div');
    div.className = 'system-msg';
    div.textContent = text;
    messagesEl.appendChild(div);
    scrollBottom();
}

function escHtml(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
}

function setBadge(state) {
    wsBadge.className  = 'ws-badge ' + state;
    wsBadge.textContent = state === 'connected' ? '● Bağlı' : '● Bağlantı kesildi';
}

// ── WebSocket bağlantısı ──────────────────────────────────────────────────
function connect() {
    ws = new WebSocket(WS_URL);

    ws.onopen = () => {
        setBadge('connected');
        msgInput.disabled = false;
        sendBtn.disabled  = false;
        msgInput.focus();

        // Odaya katılımı BroadcastServer'a bildir
        // BroadcastServer::onMessage → case 'join' → memberRepo->joinRoom()
        ws.send(JSON.stringify({ type: 'join', room_id: ROOM_ID, user_id: USER_ID }));
    };

    ws.onmessage = (event) => {
        let data;
        try { data = JSON.parse(event.data); } catch { return; }

        if (data.type === 'message' && data.room_id === ROOM_ID) {
            const time = new Date().toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' });
            appendMessage(data.username ?? '?', data.text, time);
        }
        if (data.type === 'join' && data.room_id === ROOM_ID && data.user_id !== USER_ID) {
            appendSystem((data.username ?? 'Biri') + ' odaya katıldı.');
        }
        if (data.type === 'leave' && data.room_id === ROOM_ID) {
            appendSystem((data.username ?? 'Biri') + ' odadan ayrıldı.');
        }
    };

    ws.onclose = () => {
        setBadge('disconnected');
        msgInput.disabled = true;
        sendBtn.disabled  = true;
        appendSystem('Bağlantı kesildi. Yeniden bağlanılıyor...');
        setTimeout(connect, 3000);
    };

    ws.onerror = () => {
        appendSystem('Bağlantı hatası oluştu.');
    };
}

// ── Mesaj gönder ──────────────────────────────────────────────────────────
function sendMessage() {
    const text = msgInput.value.trim();
    if (!text || !ws || ws.readyState !== WebSocket.OPEN) return;

    // BroadcastServer::onMessage → case 'message' → msgRepo->saveMessage()
    ws.send(JSON.stringify({
        type:     'message',
        room_id:  ROOM_ID,
        user_id:  USER_ID,
        username: USERNAME,
        text:     text
    }));

    msgInput.value = '';
    msgInput.focus();
}

sendBtn.onclick = sendMessage;
msgInput.addEventListener('keypress', e => { if (e.key === 'Enter') sendMessage(); });

// Sayfa kapanırken odadan ayrıl
window.addEventListener('beforeunload', () => {
    if (ws && ws.readyState === WebSocket.OPEN) {
        ws.send(JSON.stringify({ type: 'leave', room_id: ROOM_ID, user_id: USER_ID, username: USERNAME }));
    }
});

connect();
</script>
<?php endif; ?>

</body>
</html>
