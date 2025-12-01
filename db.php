<?php
// db.php
// Returns a mysqli connection via get_db()

// === CONFIG: adjust these for your environment ===
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'MoveByMood'); // change to your DB name
// ==================================================

function get_db() {
    static $mysqli = null;
    if ($mysqli !== null) return $mysqli;

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $mysqli->set_charset('utf8mb4');
    return $mysqli;
}