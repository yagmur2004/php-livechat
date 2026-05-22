<?php

/**
 * config.php
 *
 * Kişi 1'in Database.php'si şunu bekliyor:
 *   require_once __DIR__ . '/../config/config.php';
 * Bu dosya o require'ı karşılar.
 *
 * Docker ortamında environment variable varsa onu kullan,
 * yoksa XAMPP defaults'una düş — her iki ortamda da çalışır.
 */

define('DB_HOST',    getenv('DB_HOST')    ?: 'localhost');
define('DB_NAME',    getenv('DB_NAME')    ?: 'php_livechat');
define('DB_USER',    getenv('DB_USER')    ?: 'root');
define('DB_PASS',    getenv('DB_PASS')    ?: '');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'PHP LiveChat');
define('BASE_URL',  getenv('BASE_URL') ?: 'http://localhost');
