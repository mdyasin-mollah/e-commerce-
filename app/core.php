<?php
declare(strict_types=1);
define('ROOT', dirname(__DIR__));
$envPath = ROOT . '/.env';
if (is_file($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim(trim($value), " \"'");
    }
}
date_default_timezone_set('Asia/Dhaka');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('shop_session');
    session_set_cookie_params(['httponly' => true, 'secure' => !empty($_SERVER['HTTPS']), 'samesite' => 'Lax']);
    session_start();
}
function db(): PDO {
    static $pdo = null;
    if (!$pdo) {
        $dsn = 'mysql:host=' . ($_ENV['DB_HOST'] ?? '127.0.0.1') . ';port=' . ($_ENV['DB_PORT'] ?? '3306') . ';dbname=' . ($_ENV['DB_DATABASE'] ?? 'ecommerce') . ';charset=utf8mb4';
        $pdo = new PDO($dsn, $_ENV['DB_USERNAME'] ?? 'root', $_ENV['DB_PASSWORD'] ?? '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    }
    return $pdo;
}
function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function money($value): string { return '৳' . number_format((float)$value, 2); }
function csrf(): string { return $_SESSION['_csrf'] ??= bin2hex(random_bytes(32)); }
function csrf_input(): string { return '<input type="hidden" name="_csrf" value="' . e(csrf()) . '">'; }
function check_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['_csrf'] ?? '', $_POST['_csrf'] ?? '')) {
        http_response_code(419); exit('Invalid session token');
    }
}
function go(string $path = ''): never { header('Location: ' . rtrim($_ENV['APP_URL'] ?? '', '/') . '/' . $path); exit; }
function msg(?string $value = null): ?string {
    if ($value !== null) { $_SESSION['_msg'] = $value; return null; }
    $message = $_SESSION['_msg'] ?? null; unset($_SESSION['_msg']); return $message;
}
function user(): ?array { return $_SESSION['user'] ?? null; }
function login_required(): void { if (!user()) go('login'); }
function admin_required(): void {
    login_required();
    if ((user()['role'] ?? '') !== 'admin') { http_response_code(403); exit('Forbidden'); }
}
function products(string $query = ''): array {
    $sql = 'SELECT p.*, c.name category FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.is_active=1';
    $params = [];
    if ($query !== '') { $sql .= ' AND p.name LIKE ?'; $params[] = '%' . $query . '%'; }
    $stmt = db()->prepare($sql . ' ORDER BY p.id DESC LIMIT 80');
    $stmt->execute($params);
    return $stmt->fetchAll();
}
