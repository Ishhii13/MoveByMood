<?php
session_start();
require_once __DIR__ . '/../db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
die('Unauthorized');
}
$mysqli = get_db();
$userID = intval($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';
if ($userID <= 0) die('Invalid ID');
$newStatus = ($action === 'deactivate') ? 0 : 1;
$stmt = $mysqli->prepare("UPDATE Users SET ActiveStatus = ? WHERE UserID = ?");
$stmt->bind_param('ii', $newStatus, $userID);
$stmt->execute();
header('Location: /MoveByMood/admin-dash.php');
exit;
?>