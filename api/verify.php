<?php
// api/verify.php
require_once __DIR__ . '/../db.php';

$token = $_GET['token'] ?? '';
if (!$token) {
    echo "Invalid verification link.";
    exit;
}

$mysqli = get_db();

$stmt = $mysqli->prepare("SELECT UserID, TokenExpires, IsVerified FROM Users WHERE VerificationToken = ? LIMIT 1");
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false || $result->num_rows === 0) {
    echo "Invalid or expired token.";
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

if ((int)$user['IsVerified'] === 1) {
    echo "Your account is already verified. You may now log in.";
    exit;
}

$now = new DateTime();
$expires = new DateTime($user['TokenExpires']);
if ($now > $expires) {
    echo "Verification link has expired. Please sign up again or request a new verification email.";
    exit;
}

// Mark verified and clear token
$upd = $mysqli->prepare("UPDATE Users SET IsVerified = 1, VerificationToken = NULL, TokenExpires = NULL WHERE UserID = ?");
$upd->bind_param('i', $user['UserID']);
$upd->execute();

echo "Email verified! You can now <a href='" . (htmlspecialchars('/MoveByMood/login.php')) . "'>log in</a>.";