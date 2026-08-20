<?php
session_start();
// Set credentials via environment variables, or edit directly for local use.
// NEVER commit real credentials to version control.
define('ADMIN_USER', getenv('CYBERSENSE_USER') ?: 'admin');
define('ADMIN_PASS', getenv('CYBERSENSE_PASS') ?: 'changeme123');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    if (trim($_POST['username'] ?? '') === ADMIN_USER && trim($_POST['password'] ?? '') === ADMIN_PASS) {
        $_SESSION['admin'] = true;
        header('Location: admin.php'); exit;
    }
    $error = 'Invalid credentials.';
}
if (isset($_GET['logout'])) { session_destroy(); header('Location: admin.php'); exit; }

// Delete a photo
if (isset($_GET['del']) && !empty($_SESSION['admin'])) {
    $f = basename($_GET['del']);
    if (preg_match('/^[A-Za-z0-9_\-]+\.(png|jpg)$/', $f) && file_exists("photos/$f")) {
        unlink("photos/$f");
        // Remove from responses.jsonl
        if (file_exists('responses.jsonl')) {
            $lines = file('responses.jsonl', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $kept  = array_filter($lines, fn($l) => !str_contains($l, "photos/$f"));
            file_put_contents('responses.jsonl', implode("\n", $kept) . "\n");
        }
    }
    header('Location: admin.php'); exit;
}

$loggedIn = !empty($_SESSION['admin']);

// Load metadata
$records = [];
if ($loggedIn && file_exists('responses.jsonl')) {
    foreach (file('responses.jsonl', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $r = json_decode($line, true);
        if ($r) $records[] = $r;
    }
    $records = array_reverse($records); // newest first
}

// Summary stats
$total    = count($records);
$withPhoto = array_filter($records, fn($r) => !empty($r['photo']) && file_exists($r['photo']));
$photoCount = count($withPhoto);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — CyberSense Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;background-color:#f8fafc;color:#0f172a;min-height:100vh}

/* Dark mode overrides */
body.dark {
  background-color: #090d16;
  color: #f8fafc;
}

/* ── Login ── */
.login-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
.login-box{background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:36px 30px;width:100%;max-width:360px;box-shadow: 0 1px 3px rgba(0,0,0,0.05)}
body.dark .login-box {
  background: #121824;
  border-color: #1e293b;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.2);
}
.li{font-size:32px;text-align:center;margin-bottom:12px}
.login-box h1{font-size:19px;font-weight:700;text-align:center;color:#0f172a;margin-bottom:4px}
body.dark .login-box h1 { color: #f8fafc; }
.login-box .ls{font-size:13px;color:#475569;text-align:center;margin-bottom:22px}
body.dark .login-box .ls { color: #94a3b8; }
.fg{margin-bottom:16px}
.fg label{display:block;font-size:12px;font-weight:500;color:#475569;margin-bottom:6px}
body.dark .fg label { color: #94a3b8; }
.fg input{width:100%;padding:10px 12px;background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;color:#0f172a;font-size:14px;outline:none;font-family:inherit;transition:border-color .12s, box-shadow .12s}
body.dark .fg input {
  background: #090d16;
  border-color: #1e293b;
  color: #f8fafc;
}
.fg input:focus{border-color:#0f172a;box-shadow:0 0 0 1px #0f172a}
body.dark .fg input:focus{border-color:#f8fafc;box-shadow:0 0 0 1px #f8fafc}
.btn-login{width:100%;padding:11px;background:#0f172a;color:#ffffff;border:1px solid #0f172a;border-radius:6px;font-size:14px;font-weight:500;cursor:pointer;font-family:inherit;margin-top:4px;transition:background-color .12s}
body.dark .btn-login {
  background: #f8fafc;
  color: #090d16;
  border-color: #f8fafc;
}
.btn-login:hover{background-color:#1e293b;border-color:#1e293b}
body.dark .btn-login:hover{background-color:#e2e8f0;border-color:#e2e8f0}
.err-msg{background:#fef2f2;border:1px solid #fca5a5;color:#991b1b;font-size:13px;padding:10px 12px;border-radius:6px;margin-bottom:16px}
body.dark .err-msg {
  background: #450a0a;
  border-color: #991b1b;
  color: #fca5a5;
}

/* ── Topbar ── */
.topbar{background:#ffffff;border-bottom:1px solid #e2e8f0;padding:14px 24px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:20}
body.dark .topbar {
  background: #121824;
  border-bottom-color: #1e293b;
}
.topbar-l{display:flex;align-items:center;gap:10px}
.tb-icon{font-size:20px}
.tb-title{font-size:15px;font-weight:700;color:#0f172a}
body.dark .tb-title { color: #f8fafc; }
.tb-sub{font-size:12px;color:#475569}
body.dark .tb-sub { color: #94a3b8; }
.logout{background:#ffffff;border:1px solid #cbd5e1;color:#0f172a;padding:8px 14px;border-radius:6px;font-size:12px;font-weight:500;text-decoration:none;transition:background-color .12s}
body.dark .logout {
  background: #121824;
  border-color: #1e293b;
  color: #f8fafc;
}
.logout:hover{background-color:#f1f5f9}
body.dark .logout:hover{background-color:#1e293b}

/* Theme Toggle Button */
.theme-toggle{background:#ffffff;border:1px solid #cbd5e1;color:#0f172a;padding:8px 12px;border-radius:6px;font-size:12px;font-weight:500;cursor:pointer;margin-right:8px;transition:background-color .12s}
body.dark .theme-toggle{background:#121824;border-color:#1e293b;color:#f8fafc}
.theme-toggle:hover{background-color:#f1f5f9}
body.dark .theme-toggle:hover{background-color:#1e293b}

/* ── Content ── */
.content{padding:24px;max-width:1400px;margin:0 auto}

/* Stats */
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:28px}
.stat{background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:16px 18px;box-shadow: 0 1px 2px rgba(0,0,0,0.02)}
body.dark .stat {
  background: #121824;
  border-color: #1e293b;
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}
.stat-v{font-size:26px;font-weight:700;color:#0f172a}
body.dark .stat-v { color: #f8fafc; }
.stat-l{font-size:11px;color:#475569;margin-top:3px}
body.dark .stat-l { color: #94a3b8; }

/* Section */
.sec-title{font-size:12px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid #e2e8f0}
body.dark .sec-title {
  color: #94a3b8;
  border-bottom-color: #1e293b;
}

/* Search */
.search-bar{margin-bottom:18px}
.search-bar input{width:100%;max-width:360px;padding:10px 12px;background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;color:#0f172a;font-size:13px;font-family:inherit;outline:none;transition:border-color .12s, box-shadow .12s}
body.dark .search-bar input {
  background: #090d16;
  border-color: #1e293b;
  color: #f8fafc;
}
.search-bar input:focus{border-color:#0f172a;box-shadow:0 0 0 1px #0f172a}
body.dark .search-bar input:focus{border-color:#f8fafc;box-shadow:0 0 0 1px #f8fafc}
.search-bar input::placeholder{color:#94a3b8}
body.dark .search-bar input::placeholder{color:#475569}

/* Photo grid */
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;margin-bottom:40px}
.pcard{background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;transition:border-color .12s, box-shadow .12s;box-shadow: 0 1px 2px rgba(0,0,0,0.02)}
body.dark .pcard {
  background: #121824;
  border-color: #1e293b;
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}
.pcard:hover{border-color:#cbd5e1}
body.dark .pcard:hover{border-color:#334155}
.pcard img{width:100%;height:160px;object-fit:cover;display:block;background:#f1f5f9}
body.dark .pcard img { background: #090d16; }
.pcard .no-photo{width:100%;height:160px;background:#f8fafc;display:flex;align-items:center;justify-content:center;font-size:32px;color:#94a3b8}
body.dark .pcard .no-photo {
  background: #090d16;
  color: #475569;
}
.pcard .meta{padding:12px 14px}
.meta-name{font-size:13px;font-weight:600;color:#0f172a;margin-bottom:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
body.dark .meta-name { color: #f8fafc; }
.meta-urk{font-size:12px;color:#2563eb;font-weight:600;margin-bottom:4px}
body.dark .meta-urk { color: #60a5fa; }
.meta-dev{font-size:11px;color:#475569;margin-bottom:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
body.dark .meta-dev { color: #94a3b8; }
.meta-ts{font-size:10px;color:#64748b}
.meta-bot{display:flex;justify-content:space-between;align-items:center;margin-top:8px;padding-top:8px;border-top:1px solid #f1f5f9}
body.dark .meta-bot { border-top-color: #1e293b; }
.meta-ip{font-size:10px;color:#64748b}
.del-btn{font-size:11px;color:#dc2626;text-decoration:none;opacity:.7;transition:opacity .12s}
body.dark .del-btn { color: #f87171; }
.del-btn:hover{opacity:1}

/* Empty */
.empty{text-align:center;padding:48px 20px;color:#64748b;font-size:14px}
body.dark .empty { color: #475569; }
.empty .ei{font-size:36px;margin-bottom:10px}

/* No-photo badge */
.no-photo-badge{display:inline-block;background:#fef2f2;border:1px solid #fca5a5;color:#991b1b;font-size:10px;padding:2px 6px;border-radius:4px;margin-left:4px;vertical-align:middle}
body.dark .no-photo-badge {
  background: #450a0a;
  border-color: #991b1b;
  color: #fca5a5;
}

@media(max-width:600px){.topbar{padding:11px 14px}.content{padding:16px}.grid{grid-template-columns:repeat(auto-fill,minmax(160px,1fr))}.pcard img,.pcard .no-photo{height:130px}}
</style>
</head>
<body>

<?php if (!$loggedIn): ?>
<!-- ──────────── LOGIN ──────────── -->
<div class="login-wrap">
  <div class="login-box">
    <div class="li">🔐</div>
    <h1>Admin Access</h1>
    <p class="ls">CyberSense Quiz Dashboard</p>
    <?php if ($error): ?>
      <div class="err-msg">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="admin.php">
      <input type="hidden" name="action" value="login">
      <div class="fg"><label>Username</label><input type="text" name="username" autocomplete="off" required></div>
      <div class="fg"><label>Password</label><input type="password" name="password" required></div>
      <button type="submit" class="btn-login">Sign In →</button>
    </form>
  </div>
</div>

<?php else: ?>
<!-- ──────────── DASHBOARD ──────────── -->
<div class="topbar">
  <div class="topbar-l">
    <span class="tb-icon">🛡️</span>
    <div><div class="tb-title">CyberSense Dashboard</div><div class="tb-sub">Cybersecurity Awareness Quiz · Admin</div></div>
  </div>
  <div style="display:flex;align-items:center">
    <button class="theme-toggle" onclick="toggleTheme()" id="themeBtn">🌓 Theme</button>
    <a href="admin.php?logout=1" class="logout">Sign Out</a>
  </div>
</div>

<div class="content">

  <!-- Stats row -->
  <div class="stats">
    <div class="stat"><div class="stat-v"><?= $total ?></div><div class="stat-l">Total Check-Ins</div></div>
    <div class="stat"><div class="stat-v"><?= $photoCount ?></div><div class="stat-l">Photos Captured</div></div>
    <div class="stat"><div class="stat-v"><?= $total - $photoCount ?></div><div class="stat-l">No Photo (Denied)</div></div>
    <div class="stat"><div class="stat-v"><?= date('H:i') ?></div><div class="stat-l">Current Time</div></div>
  </div>


  <!-- Search -->
  <div class="search-bar">
    <input type="text" id="searchInput" placeholder="🔍  Search by name or Register number..." oninput="filterCards()">
  </div>

  <div class="sec-title">📷 Participant Photos & Details</div>

  <?php if (empty($records)): ?>
    <div class="empty"><div class="ei">📷</div><p>No check-ins yet. Waiting for participants...</p></div>
  <?php else: ?>
    <div class="grid" id="photoGrid">
      <?php foreach ($records as $idx => $r):
        $photoPath = $r['photo'] ?? '';
        $hasPhoto  = !empty($photoPath) && file_exists($photoPath);
        $photoFile = $hasPhoto ? basename($photoPath) : '';
        $devParts  = explode('|', $r['device'] ?? '');
        $devName   = trim($devParts[0] ?? 'Unknown');
        $devScreen = trim($devParts[1] ?? '');
      ?>
      <div class="pcard" data-search="<?= htmlspecialchars(strtolower(($r['name'] ?? '') . ' ' . ($r['urk'] ?? ''))) ?>">
        <?php if ($hasPhoto): ?>
          <img src="<?= htmlspecialchars($photoPath) ?>" alt="<?= htmlspecialchars($r['name'] ?? '') ?>" loading="lazy">
        <?php else: ?>
          <div class="no-photo">📷</div>
        <?php endif; ?>
        <div class="meta">
          <div class="meta-name">
            <?= htmlspecialchars($r['name'] ?? 'Unknown') ?>
            <?php if (!$hasPhoto): ?><span class="no-photo-badge">No photo</span><?php endif; ?>
          </div>
          <div class="meta-urk"><?= htmlspecialchars($r['urk'] ?? '—') ?></div>
          <div class="meta-dev">📱 <?= htmlspecialchars($devName) ?><?= $devScreen ? ' · '.$devScreen : '' ?></div>
          <div class="meta-ts">🕐 <?= htmlspecialchars($r['timestamp'] ?? '') ?></div>
          <div class="meta-bot">
            <span class="meta-ip">IP: <?= htmlspecialchars($r['ip'] ?? '') ?></span>
            <?php if ($hasPhoto): ?>
              <a href="admin.php?del=<?= urlencode($photoFile) ?>" class="del-btn"
                 onclick="return confirm('Delete this record?')">✕</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div><!-- /content -->

<script>
function filterCards() {
  var q = document.getElementById('searchInput').value.toLowerCase().trim();
  document.querySelectorAll('.pcard').forEach(function(card) {
    card.style.display = (!q || card.dataset.search.includes(q)) ? '' : 'none';
  });
}

// Local storage theme logic
(function() {
  var currentTheme = localStorage.getItem('admin_theme') || 'light';
  if (currentTheme === 'dark') {
    document.body.classList.add('dark');
  }
})();

function toggleTheme() {
  var body = document.body;
  if (body.classList.contains('dark')) {
    body.classList.remove('dark');
    localStorage.setItem('admin_theme', 'light');
  } else {
    body.classList.add('dark');
    localStorage.setItem('admin_theme', 'dark');
  }
}

// Auto-refresh every 20 seconds to pick up new check-ins
setTimeout(function(){ window.location.reload(); }, 20000);
</script>

<?php endif; ?>
<!-- Theme initializer for login page as well -->
<script>
(function() {
  if (localStorage.getItem('admin_theme') === 'dark') {
    document.body.classList.add('dark');
  }
})();
</script>
</body>
</html>
