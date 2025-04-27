<?php
/**
 * API Gateway with MySQL-based key auth and rate limiting.
 *
 * @version    1.0
 * @since      Available since Release 1.2.0
 */

// -----------------
// Database Connection
// -----------------
$dsn = "mysql:host=localhost;port=3306;dbname=gateway";
$dbUser = "root";
$dbPass = "root";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// -----------------
// Read Headers and Request Path
// -----------------
$headers = getallheaders();
$apiKey = $headers["X-API-Key"] ?? null;
$requestedPath = $_GET['request_path'] ?? '';
$status = 200;

// -----------------
// Step 1: API Key Validation
// -----------------
$stmt = $pdo->prepare("SELECT user_name FROM api_keys WHERE api_key = ?");
$stmt->execute([$apiKey]);
$user = $stmt->fetch();

if (!$user) {
    $status = 401;
    logRequest($apiKey, $requestedPath, $status);
    http_response_code($status);
    echo json_encode(["error" => "Invalid or missing API Key"]);
    exit;
}

// -----------------
// Step 2: Rate Limiting
// -----------------
$currentTime = time();
$limitStmt = $pdo->prepare("SELECT last_request_ts, request_count FROM rate_limits WHERE api_key = ?");
$limitStmt->execute([$apiKey]);
$limit = $limitStmt->fetch();

if ($limit) {
    $elapsed = $currentTime - $limit['last_request_ts'];
    if ($elapsed <= 60) {
        if ($limit['request_count'] >= 10) {
            $status = 429;
            logRequest($apiKey, $requestedPath, $status);
            http_response_code($status);
            echo json_encode(["error" => "Rate limit exceeded"]);
            exit;
        } else {
            $update = $pdo->prepare("UPDATE rate_limits SET request_count = request_count + 1 WHERE api_key = ?");
            $update->execute([$apiKey]);
        }
    } else {
        $reset = $pdo->prepare("UPDATE rate_limits SET last_request_ts = ?, request_count = 1 WHERE api_key = ?");
        $reset->execute([$currentTime, $apiKey]);
    }
} else {
    $insert = $pdo->prepare("INSERT INTO rate_limits (api_key, last_request_ts, request_count) VALUES (?, ?, 1)");
    $insert->execute([$apiKey, $currentTime]);
}

// -----------------
// Step 3: Routing
// -----------------
ob_start(); // Capture service output
switch ($requestedPath) {
    case "users":
        $_SERVER['HTTP_X_FORWARDED_BY'] = "MyPHPGateway";
        require __DIR__ . '/services/service_users.php';
        break;
    case "products":
        $_SERVER['HTTP_X_FORWARDED_BY'] = "MyPHPGateway";
        require __DIR__ . '/services/service_products.php';
        break;
    case "dashboard":
        include_once __DIR__ . '/services/service_dashboard.php';
        break;
    default:
        $status = 404;
        header("Content-Type: application/json");
        echo json_encode(["error" => "Service not found"]);
}
$output = ob_get_clean();
http_response_code($status);
echo $output;
logRequest($apiKey, $requestedPath, $status);

// -----------------
// Logging function
// -----------------

/**
 * Logs the API request details into a log file.
 *
 * @param string|null $apiKey  The API key used for the request. Can be null if not provided.
 * @param string      $path    The requested service path (e.g., "users", "products").
 * @param int         $status  The HTTP response status code for the request (e.g., 200, 401, 404, 429).
 *
 * @return void
 */
function logRequest($apiKey, $path, $status) {
    $logLine = "[" . date('Y-m-d H:i:s') . "] - IP: " . $_SERVER['REMOTE_ADDR'] . " - API Key: " . ($apiKey ?: 'None') . " - Path: $path - Status: $status\n";
    file_put_contents(__DIR__ . "/logs/gateway.log", $logLine, FILE_APPEND);
}