<?php
/**
 * Authentication API
 * Endpoints: register, login, logout, profile
 */

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        if ($method !== 'POST') sendJSON(['error' => 'Method not allowed'], 405);
        register();
        break;
    case 'login':
        if ($method !== 'POST') sendJSON(['error' => 'Method not allowed'], 405);
        login();
        break;
    case 'logout':
        logout();
        break;
    case 'profile':
        profile();
        break;
    default:
        sendJSON(['error' => 'Invalid action'], 400);
}

function register() {
    $data = getInput();
    
    if (empty($data['email']) || empty($data['password'])) {
        sendJSON(['error' => 'Email dan password wajib diisi'], 400);
    }
    
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        sendJSON(['error' => 'Format email tidak valid'], 400);
    }
    
    if (strlen($data['password']) < 6) {
        sendJSON(['error' => 'Password minimal 6 karakter'], 400);
    }
    
    $db = getDB();
    
    // Check if email exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        sendJSON(['error' => 'Email sudah terdaftar'], 400);
    }
    
    // Create user
    $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
    $token = bin2hex(random_bytes(32));
    $tokenExpires = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    $stmt = $db->prepare("INSERT INTO users (email, password_hash, name, token, token_expires) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['email'],
        $passwordHash,
        $data['name'] ?? explode('@', $data['email'])[0],
        $token,
        $tokenExpires
    ]);
    
    sendJSON([
        'success' => true,
        'message' => 'Registrasi berhasil',
        'token' => $token,
        'user' => [
            'id' => $db->lastInsertId(),
            'email' => $data['email'],
            'name' => $data['name'] ?? explode('@', $data['email'])[0]
        ]
    ]);
}

function login() {
    $data = getInput();
    
    if (empty($data['email']) || empty($data['password'])) {
        sendJSON(['error' => 'Email dan password wajib diisi'], 400);
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($data['password'], $user['password_hash'])) {
        sendJSON(['error' => 'Email atau password salah'], 401);
    }
    
    // Generate new token
    $token = bin2hex(random_bytes(32));
    $tokenExpires = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    $stmt = $db->prepare("UPDATE users SET token = ?, token_expires = ? WHERE id = ?");
    $stmt->execute([$token, $tokenExpires, $user['id']]);
    
    sendJSON([
        'success' => true,
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['name'],
            'role' => $user['role']
        ]
    ]);
}

function logout() {
    $user = requireAuth();
    
    $db = getDB();
    $stmt = $db->prepare("UPDATE users SET token = NULL, token_expires = NULL WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    sendJSON(['success' => true, 'message' => 'Logout berhasil']);
}

function profile() {
    $user = requireAuth();
    
    sendJSON([
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['name'],
            'role' => $user['role'],
            'created_at' => $user['created_at']
        ]
    ]);
}
