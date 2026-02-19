<?php
/**
 * Quishing Defender - Database Configuration
 * Setup: Import quishing_db.sql ke phpMyAdmin
 */

// Database config
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'quishing_defender');

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Database connection
function getDB() {
    static $conn = null;
    if ($conn === null) {
        try {
            $conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed']));
        }
    }
    return $conn;
}

// Helper functions
function sendJSON($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function getInput() {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}

function requireAuth() {
    $headers = getallheaders();
    $token = $headers['Authorization'] ?? '';
    
    if (empty($token)) {
        sendJSON(['error' => 'Unauthorized'], 401);
    }
    
    $token = str_replace('Bearer ', '', $token);
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE token = ? AND token_expires > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        sendJSON(['error' => 'Invalid token'], 401);
    }
    
    return $user;
}
