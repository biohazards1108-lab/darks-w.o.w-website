<?php
$config = require __DIR__ . '/config.php';

$secret = $_GET['key'] ?? '';
if ($secret !== $config['admin_secret']) {
    http_response_code(404);
    exit("Not found.");
}

$mysqli = new mysqli(
    $config['db_host'],
    $config['db_user'],
    $config['db_pass'],
    $config['db_name_website']
);
if ($mysqli->connect_error) {
    exit("DB error.");
}

$visits = $mysqli->query("SELECT COUNT(*) AS c FROM visit_stats")->fetch_assoc()['c'];
$attacks = $mysqli->query("SELECT COUNT(*) AS c FROM security_events")->fetch_assoc()['c'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Darks WoW - Hidden Stats</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="theme-icy">
  <div class="bg-overlay"></div>
  <main class="content">
    <section class="hero">
      <div class="hero-text">
        <h2>Frozen Intel</h2>
        <p>Private stats for Darks WoW (you and your dad only).</p>
      </div>
      <div class="feature-grid">
        <article class="feature-card">
          <h4>Total Visitors</h4>
          <p><?php echo (int)$visits; ?></p>
        </article>
        <article class="feature-card">
          <h4>Recorded Attack Attempts</h4>
          <p><?php echo (int)$attacks; ?></p>
        </article>
      </div>
    </section>
  </main>
</body>
</html>
