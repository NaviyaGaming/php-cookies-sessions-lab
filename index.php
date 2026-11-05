<?php
/**
 * PHP Cookies & Sessions — Single-File Local Lab (index.php)
 * ----------------------------------------------------------
 * An educational, self-contained sandbox demonstrating:
 * 1. How Cookies are queued in HTTP Response Headers via setcookie()
 * 2. How the $_COOKIE superglobal populates on subsequent HTTP requests
 * 3. How PHP Sessions work via session_start() and /tmp/sess_<id> storage
 * 4. Safe cookie deletion and full session teardown (session_destroy())
 *
 * How to run:
 *   Option A (PHP Built-in Server):
 *     php -S localhost:8000
 *     Open: http://localhost:8000/index.php
 *
 *   Option B (XAMPP / WAMP / MAMP):
 *     Copy this file into C:\xampp\htdocs\index.php
 *     Open: http://localhost/index.php
 */

// 1. ALWAYS start the session at the very top BEFORE any HTML output!
session_start();

$flashMessage = "";

// 2. Handle Form Actions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    // --- COOKIE ACTIONS ---
    if ($action === "set_cookie") {
        $name  = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST["cookie_name"] ?? "theme");
        $value = $_POST["cookie_value"] ?? "dark";
        $ttl   = (int)($_POST["cookie_ttl"] ?? 60);

        if ($name !== "") {
            // Queue the Set-Cookie HTTP response header (HttpOnly = true for security)
            setcookie($name, $value, time() + $ttl, "/", "", false, true);
            $flashMessage = "Cookie '$name' queued in Set-Cookie header (expires in {$ttl}s). Reload page to see it in \$_COOKIE!";
        }
    } elseif ($action === "delete_cookie") {
        $name = $_POST["cookie_name"] ?? "theme";
        // Expire cookie by setting timestamp in the past
        setcookie($name, "", time() - 3600, "/");
        unset($_COOKIE[$name]);
        $flashMessage = "Cookie '$name' expired (time() - 3600).";
    }

    // --- SESSION ACTIONS ---
    elseif ($action === "set_session") {
        $key = $_POST["session_key"] ?? "username";
        $val = $_POST["session_val"] ?? "alex_dev";
        if ($key !== "") {
            $_SESSION[$key] = $val;
            $flashMessage = "Saved \$_SESSION['$key'] = '$val' on the server!";
        }
    } elseif ($action === "clear_session") {
        session_unset();
        $flashMessage = "Cleared all variables in \$_SESSION with session_unset().";
    } elseif ($action === "destroy_session") {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600, $params["path"]);
        }
        session_destroy();
        $flashMessage = "Session destroyed and PHPSESSID cookie deleted!";
    }
}

$theme = $_COOKIE["theme"] ?? "dark";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Cookies & Sessions Local Sandbox</title>
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: <?= $theme === 'light' ? '#f8fafc' : '#0f172a' ?>;
            color: <?= $theme === 'light' ? '#0f172a' : '#f8fafc' ?>;
            max-width: 960px;
            margin: 2rem auto;
            padding: 0 1rem;
            transition: background 0.3s ease, color 0.3s ease;
        }
        .header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid <?= $theme === 'light' ? '#e2e8f0' : '#334155' ?>;
        }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; }
        .card {
            background: <?= $theme === 'light' ? '#ffffff' : '#1e293b' ?>;
            border: 1px solid <?= $theme === 'light' ? '#e2e8f0' : '#334155' ?>;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem; }
        input[type="text"], input[type="number"] {
            width: 100%;
            box-sizing: border-box;
            padding: 0.55rem 0.75rem;
            border-radius: 6px;
            border: 1px solid #64748b;
            font-size: 0.9rem;
            background: <?= $theme === 'light' ? '#f8fafc' : '#0f172a' ?>;
            color: inherit;
            margin-bottom: 0.85rem;
        }
        button {
            padding: 0.6rem 1rem;
            border-radius: 6px;
            border: none;
            background: #6366f1;
            color: white;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            transition: opacity 0.2s;
        }
        button:hover { opacity: 0.9; }
        button.danger { background: #e11d48; }
        pre {
            background: #020617;
            color: #38bdf8;
            padding: 1rem;
            border-radius: 8px;
            overflow-x: auto;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.85rem;
            line-height: 1.4;
        }
        .alert {
            background: #312e81;
            color: #e0e7ff;
            padding: 0.85rem 1.2rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border: 1px solid #4338ca;
            font-size: 0.9rem;
        }
        .btn-group { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.5rem; }
        .meta-pill {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 4px;
            background: rgba(99, 102, 241, 0.15);
            color: #818cf8;
            font-family: monospace;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🐘 PHP Cookies &amp; Sessions Live Sandbox</h1>
        <p>
            Current Session ID: 
            <span class="meta-pill"><?= htmlspecialchars(session_id() ?: 'Destroyed (Reload to start)') ?></span>
            &nbsp;|&nbsp;
            <a href="index.php" style="color:#818cf8; text-decoration:none; font-weight:600;">🔄 Reload Page (Send HTTP GET)</a>
        </p>
    </div>

    <?php if ($flashMessage): ?>
        <div class="alert">💡 <?= htmlspecialchars($flashMessage) ?></div>
    <?php endif; ?>

    <div class="grid">
        <!-- 1. Cookie Form -->
        <div class="card">
            <h2>🍪 1. Manage Cookies (Client Browser)</h2>
            <p style="font-size:0.85rem; opacity:0.8;">Data stored in your browser; sent to PHP via <code>Cookie:</code> HTTP request header.</p>
            
            <form method="POST">
                <input type="hidden" name="action" value="set_cookie">
                <label>Cookie Name ($name):</label>
                <input type="text" name="cookie_name" value="theme" required>
                
                <label>Cookie Value ($value):</label>
                <input type="text" name="cookie_value" value="light" required>
                
                <label>TTL in Seconds (time() + $ttl):</label>
                <input type="number" name="cookie_ttl" value="120" min="5" max="86400">
                
                <button type="submit">Set Cookie via setcookie()</button>
            </form>

            <form method="POST" style="margin-top: 1rem; border-top: 1px solid #334155; padding-top: 1rem;">
                <input type="hidden" name="action" value="delete_cookie">
                <input type="hidden" name="cookie_name" value="theme">
                <button type="submit" class="danger">Delete 'theme' Cookie (time() - 3600)</button>
            </form>

            <h3 style="margin-top: 1.5rem;">$_COOKIE Superglobal (Current Request):</h3>
            <pre><?php print_r($_COOKIE); ?></pre>
        </div>

        <!-- 2. Session Form -->
        <div class="card">
            <h2>🔒 2. Manage Session (PHP Server)</h2>
            <p style="font-size:0.85rem; opacity:0.8;">Data stored securely on the server; linked via client <code>PHPSESSID</code> cookie.</p>
            
            <form method="POST">
                <input type="hidden" name="action" value="set_session">
                <label>Session Key ($_SESSION[$key]):</label>
                <input type="text" name="session_key" value="username" required>
                
                <label>Session Value ($value):</label>
                <input type="text" name="session_val" value="kasun_dev" required>
                
                <button type="submit">Save to $_SESSION</button>
            </form>

            <form method="POST" style="margin-top: 1rem; border-top: 1px solid #334155; padding-top: 1rem;">
                <div class="btn-group">
                    <button type="submit" name="action" value="clear_session" class="danger">session_unset()</button>
                    <button type="submit" name="action" value="destroy_session" class="danger">session_destroy()</button>
                </div>
            </form>

            <h3 style="margin-top: 1.5rem;">$_SESSION Superglobal:</h3>
            <pre><?php print_r($_SESSION ?? []); ?></pre>
            <p style="font-size:0.8rem; opacity:0.75; margin-top: 0.5rem;">
                <strong>Server Storage File:</strong><br>
                <code><?= htmlspecialchars(session_save_path() ?: sys_get_temp_dir()) ?>/sess_<?= htmlspecialchars(session_id()) ?></code>
            </p>
        </div>
    </div>
</body>
</html>
