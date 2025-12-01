<?php
// api/login.php
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/MoveByMood',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success'=>false, 'message'=>'Invalid request']);
    exit;
}

$identifier = trim($input['identifier'] ?? '');
$password   = $input['password'] ?? '';

if ($identifier === '' || $password === '') {
    echo json_encode(['success'=>false, 'message'=>'Missing credentials']);
    exit;
}

$mysqli = get_db();

$stmt = $mysqli->prepare("
    SELECT 
        UserID,
        Email,
        Username,
        PasswordHash,
        IsVerified,
        ActiveStatus,
        Role
    FROM Users
    WHERE Email = ? OR Username = ?
    LIMIT 1
");
$stmt->bind_param('ss', $identifier, $identifier);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success'=>false, 'message'=>'Invalid credentials']);
    exit;
}

$user = $result->fetch_assoc();

// Password check
if (!password_verify($password, $user['PasswordHash'])) {
    echo json_encode(['success'=>false, 'message'=>'Invalid credentials']);
    exit;
}

// Must verify email first
if ((int)$user['IsVerified'] !== 1) {
    echo json_encode(['success'=>false, 'message'=>'Please verify your email.']);
    exit;
}

// Must have active status
if ((int)$user['ActiveStatus'] !== 1) {
    echo json_encode(['success'=>false, 'message'=>'Account is deactivated by admin.']);
    exit;
}

session_regenerate_id(true);

$_SESSION['user_id'] = (int)$user['UserID'];
$_SESSION['username'] = $user['Username'];
$_SESSION['email'] = $user['Email'];
$_SESSION['role'] = $user['Role'];

echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'role' => strtolower($user['Role'])
]);