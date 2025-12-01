<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../phpmailer/src/Exception.php';
require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$input = json_decode(file_get_contents('php://input'), true);

$email    = trim($input['email'] ?? '');
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($username) < 3 || strlen($password) < 6) {
    echo json_encode(['success'=>false, 'message'=>'Invalid input.']);
    exit;
}

$mysqli = get_db();

// Check if email OR username exists
$stmt = $mysqli->prepare("SELECT UserID FROM Users WHERE Email = ? OR Username = ?");
$stmt->bind_param('ss', $email, $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['success'=>false, 'message'=>'Email or Username already taken.']);
    exit;
}

$stmt->close();

// Prepare token
$token   = bin2hex(random_bytes(32));
$expires = (new DateTime('+1 day'))->format('Y-m-d H:i:s');
$hash    = password_hash($password, PASSWORD_DEFAULT);
$active  = 0; // Not active until email is verified

// Construct verification URL
$verifyUrl = rtrim(BASE_URL, '/') . "/api/verify.php?token={$token}";

// Send email FIRST — before inserting into DB
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;

    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress($email, $username);

    $mail->isHTML(true);
    $mail->Subject = 'Verify your MoveByMood account';
    $mail->Body    = "
        <p>Hello <strong>{$username}</strong>,</p>
        <p>Please verify your account by clicking the button below:</p>
        <p><a href='{$verifyUrl}' style='padding: 10px 20px; background:#4CAF50; color:white; text-decoration:none; border-radius:5px;'>Verify Account</a></p>
        <p>If the button doesn't work, use this link:</p>
        <p>{$verifyUrl}</p>
    ";

    // Attempt to send email
    $mail->send();

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send verification email. Account not created.',
        'error'   => $e->getMessage()
    ]);
    exit;
}

// Email succeeded → NOW insert into database
$insert = $mysqli->prepare("
    INSERT INTO Users (Email, Username, PasswordHash, VerificationToken, TokenExpires, ActiveStatus)
    VALUES (?, ?, ?, ?, ?, ?)
");

$insert->bind_param('sssssi', $email, $username, $hash, $token, $expires, $active);
$insert->execute();

echo json_encode([
    'success' => true,
    'message' => 'Account created! Please check your email to verify.'
]);