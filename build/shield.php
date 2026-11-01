<?php
// build/shield.php - Basic Request & Session Shield
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple rate limiting per session (prevents spam clicking/flooding)
$now = microtime(true);
if (!isset($_SESSION['shield_last_req'])) {
    $_SESSION['shield_last_req'] =$now;
    $_SESSION['shield_burst'] = 0; } else {$elapsed = $now -$_SESSION['shield_last_req'];
    if ($elapsed < 0.08) {$_SESSION['shield_burst']++;
        if ($_SESSION['shield_burst'] > 25) {
            http_response_code(429);
            die(json_encode(['ok' => false, 'error' => 'Too many requests. Slow down!']));
        }
    } else {
        $_SESSION['shield_burst'] = max(0,$_SESSION['shield_burst'] - 1);
    }
    $_SESSION['shield_last_req'] =$now;
}
