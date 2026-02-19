<?php
/**
 * Scan History API
 * Endpoints: list, save, delete, stats
 */

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        if ($method !== 'GET') sendJSON(['error' => 'Method not allowed'], 405);
        listHistory();
        break;
    case 'save':
        if ($method !== 'POST') sendJSON(['error' => 'Method not allowed'], 405);
        saveHistory();
        break;
    case 'delete':
        if ($method !== 'DELETE') sendJSON(['error' => 'Method not allowed'], 405);
        deleteHistory();
        break;
    case 'stats':
        if ($method !== 'GET') sendJSON(['error' => 'Method not allowed'], 405);
        getStats();
        break;
    default:
        sendJSON(['error' => 'Invalid action'], 400);
}

function listHistory() {
    $user = requireAuth();
    $db = getDB();
    
    $limit = min((int)($_GET['limit'] ?? 50), 200);
    $offset = (int)($_GET['offset'] ?? 0);
    $filter = $_GET['filter'] ?? '';
    
    $sql = "SELECT * FROM scan_history WHERE user_id = ?";
    $params = [$user['id']];
    
    if (in_array($filter, ['SAFE', 'SUSPICIOUS', 'DANGEROUS'])) {
        $sql .= " AND risk_level = ?";
        $params[] = $filter;
    }
    
    $sql .= " ORDER BY scan_date DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Decode JSON fields
    foreach ($history as &$item) {
        $item['threats'] = json_decode($item['threats'] ?? '[]');
        $item['warnings'] = json_decode($item['warnings'] ?? '[]');
        $item['safe_indicators'] = json_decode($item['safe_indicators'] ?? '[]');
        $item['api_results'] = json_decode($item['api_results'] ?? '{}');
    }
    
    sendJSON(['history' => $history]);
}

function saveHistory() {
    $user = requireAuth();
    $data = getInput();
    
    if (empty($data['url'])) {
        sendJSON(['error' => 'URL wajib diisi'], 400);
    }
    
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO scan_history 
        (user_id, url, domain, risk_level, risk_score, threats, warnings, safe_indicators, api_results) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $user['id'],
        $data['url'],
        $data['domain'] ?? parse_url($data['url'], PHP_URL_HOST),
        $data['risk_level'] ?? 'UNKNOWN',
        $data['risk_score'] ?? 0,
        json_encode($data['threats'] ?? []),
        json_encode($data['warnings'] ?? []),
        json_encode($data['safe_indicators'] ?? []),
        json_encode($data['api_results'] ?? [])
    ]);
    
    // Update daily stats
    updateDailyStats($data['risk_level'] ?? 'UNKNOWN');
    
    sendJSON([
        'success' => true,
        'id' => $db->lastInsertId()
    ]);
}

function deleteHistory() {
    $user = requireAuth();
    $id = $_GET['id'] ?? null;
    
    $db = getDB();
    
    if ($id === 'all') {
        $stmt = $db->prepare("DELETE FROM scan_history WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        sendJSON(['success' => true, 'message' => 'Semua riwayat dihapus']);
    } else if ($id) {
        $stmt = $db->prepare("DELETE FROM scan_history WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user['id']]);
        sendJSON(['success' => true]);
    } else {
        sendJSON(['error' => 'ID not specified'], 400);
    }
}

function getStats() {
    $user = requireAuth();
    $db = getDB();
    
    // User stats
    $stmt = $db->prepare("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN risk_level = 'SAFE' THEN 1 ELSE 0 END) as safe,
        SUM(CASE WHEN risk_level = 'SUSPICIOUS' THEN 1 ELSE 0 END) as suspicious,
        SUM(CASE WHEN risk_level = 'DANGEROUS' THEN 1 ELSE 0 END) as dangerous
        FROM scan_history WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Recent 7 days
    $stmt = $db->prepare("SELECT DATE(scan_date) as date, COUNT(*) as count 
        FROM scan_history WHERE user_id = ? AND scan_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(scan_date) ORDER BY date");
    $stmt->execute([$user['id']]);
    $daily = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendJSON([
        'stats' => $stats,
        'daily' => $daily
    ]);
}

function updateDailyStats($riskLevel) {
    $db = getDB();
    $today = date('Y-m-d');
    
    $stmt = $db->prepare("INSERT INTO statistics (date, total_scans, safe_count, suspicious_count, dangerous_count) 
        VALUES (?, 1, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            total_scans = total_scans + 1,
            safe_count = safe_count + ?,
            suspicious_count = suspicious_count + ?,
            dangerous_count = dangerous_count + ?");
    
    $safe = $riskLevel === 'SAFE' ? 1 : 0;
    $suspicious = $riskLevel === 'SUSPICIOUS' ? 1 : 0;
    $dangerous = $riskLevel === 'DANGEROUS' ? 1 : 0;
    
    $stmt->execute([$today, $safe, $suspicious, $dangerous, $safe, $suspicious, $dangerous]);
}
