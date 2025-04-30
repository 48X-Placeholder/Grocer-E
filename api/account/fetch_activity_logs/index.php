<?php
require_once dirname(__FILE__) . '../../../../config.php'; // Ensure database connection
require_once dirname(__FILE__) . '../../../../functions/load.php';
header('Content-Type: application/json');

// Ensure user is authenticated
if (!is_user_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

$userId = cached_userid_info();

// Create database connection to grocery_db (for scan logs)
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection to grocery_db failed"]);
    exit;
}

// Fetch scan logs from grocery_db
$sqlScanLogs = "
    SELECT LogId, UPC, ActionType, AmountChanged, Timestamp AS ActionTimestamp, 'scan' AS LogSource
    FROM scan_logs
    WHERE UserId = ?
";

$stmtScanLogs = $conn->prepare($sqlScanLogs);
$stmtScanLogs->bind_param('i', $userId);
$stmtScanLogs->execute();
$resultScanLogs = $stmtScanLogs->get_result();
$scanLogs = [];

while ($row = $resultScanLogs->fetch_assoc()) {
    $scanLogs[] = $row;
}
$stmtScanLogs->close();
$conn->close(); // Close the grocery_db connection

// Create a separate connection for user activity logs (stored in user_db)
$conn_user_db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME_ACCOUNTS); // Connect to user_db
if ($conn_user_db->connect_error) {
    echo json_encode(["error" => "Database connection to user_db failed"]);
    exit;
}

// Fetch user activity logs from user_db
$sqlActivityLogs = "
    SELECT LogId, Action, ActionTimestamp, 'activity' AS LogSource
    FROM user_activity_logs
    WHERE UserId = ?
";

$stmtActivityLogs = $conn_user_db->prepare($sqlActivityLogs);
$stmtActivityLogs->bind_param('i', $userId);
$stmtActivityLogs->execute();
$resultActivityLogs = $stmtActivityLogs->get_result();
$activityLogs = [];

while ($row = $resultActivityLogs->fetch_assoc()) {
    $activityLogs[] = $row;
}
$stmtActivityLogs->close();
$conn_user_db->close(); // Close the user_db connection

// Merge both log sources
$combinedLogs = array_merge($scanLogs, $activityLogs);

// Sort logs by timestamp (newest first)
usort($combinedLogs, function ($a, $b) {
    return strtotime($b['ActionTimestamp']) - strtotime($a['ActionTimestamp']);
});

echo json_encode(['success' => true, 'logs' => $combinedLogs]);
?>
