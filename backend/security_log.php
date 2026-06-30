<?php
$config = require __DIR__ . '/config.php';

function db_connect_site($config) {
    $mysqli = new mysqli(
        $config['db_host'],
        $config['db_user'],
        $config['db_pass'],
        $config['db_name_website']
    );
    if ($mysqli->connect_error) {
        error_log("Site DB connection failed: " . $mysqli->connect_error);
        return null;
    }
    $mysqli->set_charset("utf8mb4");
    return $mysqli;
}

function log_security_event($type, $details = '') {
    global $config;
    $db = db_connect_site($config);
    if (!$db) return;

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $stmt = $db->prepare("
        INSERT INTO security_events (ip, event_type, details)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("sss", $ip, $type, $details);
    $stmt->execute();
    $stmt->close();
}
