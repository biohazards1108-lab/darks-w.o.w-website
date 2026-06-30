<?php
$config = require __DIR__ . '/config.php';

function db_connect_auth($config) {
    $mysqli = new mysqli(
        $config['db_host'],
        $config['db_user'],
        $config['db_pass'],
        $config['db_name_auth']
    );
    if ($mysqli->connect_error) {
        error_log("Auth DB connection failed: " . $mysqli->connect_error);
        http_response_code(500);
        exit("Internal error.");
    }
    $mysqli->set_charset("utf8mb4");
    return $mysqli;
}

function db_connect_site($config) {
    $mysqli = new mysqli(
        $config['db_host'],
        $config['db_user'],
        $config['db_pass'],
        $config['db_name_website']
    );
    if ($mysqli->connect_error) {
        error_log("Site DB connection failed: " . $mysqli->connect_error);
        http_response_code(500);
        exit("Internal error.");
    }
    $mysqli->set_charset("utf8mb4");
    return $mysqli;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Method not allowed.");
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';
$realm = $_POST['realm'] ?? 'icebound';
$fingerprint = $_POST['client_fingerprint'] ?? '';

if ($password !== $confirm) {
    exit("Passwords do not match.");
}

if (strlen($username) < 3 || strlen($username) > 16) {
    exit("Invalid username length.");
}

// TODO: add more validation (allowed chars, etc.)

$authDb = db_connect_auth($config);
$siteDb = db_connect_site($config);

// Example for TrinityCore-style SRP6 is more complex;
// for now, store a hashed password in website DB and
// later adapt to your server’s auth system.
$hash = password_hash($password, PASSWORD_DEFAULT);

// Insert into website stats/users table
$stmt = $siteDb->prepare("
    INSERT INTO website_accounts (username, email, realm, created_at, fingerprint)
    VALUES (?, ?, ?, NOW(), ?)
");
$stmt->bind_param("ssss", $username, $email, $realm, $fingerprint);
$stmt->execute();
$stmt->close();

// Insert into WoW auth DB (simplified, adjust to your core’s schema)
$stmt2 = $authDb->prepare("
    INSERT INTO account (username, sha_pass_hash, email, joindate)
    VALUES (?, ?, ?, NOW())
");
$stmt2->bind_param("sss", $username, $hash, $email);
$stmt2->execute();
$stmt2->close();

echo "Account created successfully. You can now log in to the game.";
