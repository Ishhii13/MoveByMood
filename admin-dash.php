<?php

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/MoveByMood',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// for testing
if (empty($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 999;
    $_SESSION['username'] = 'TempAdmin';
    $_SESSION['email'] = 'temp@admin.com';
    $_SESSION['role'] = 'admin';
}

// Ensure username exists with fallback
if (empty($_SESSION['username'])) {
    $_SESSION['username'] = 'Guest Admin';
}

// Connect to DB
$mysqli = new mysqli("localhost", "root", "", "movebymood");

// Fetch user activity (removed last_active and is_active since they don't exist)
$userQuery = $mysqli->query("
    SELECT UserID, Username, Email, Role, CreatedAt
    FROM users
");

// Check if reports table exists before querying
// Check if reports table exists before querying
$tableCheck = $mysqli->query("SHOW TABLES LIKE 'reports'");
if ($tableCheck && $tableCheck->num_rows > 0) {
    // Updated to match actual database columns
    $reportQuery = $mysqli->query("
        SELECT 
            r.ReportID,
            r.SentByUserID,
            sender.Username AS SenderName,
            r.OnUserID,
            target.Username AS TargetName,
            rt.Type AS ReportType,
            r.Report,
            r.Validation_Status
        FROM reports r
        LEFT JOIN users sender ON r.SentByUserID = sender.UserID
        LEFT JOIN users target ON r.OnUserID = target.UserID
        LEFT JOIN reporttype rt ON r.ReportTypeID = rt.ReportTypeID
    ");
} else {
    $reportQuery = null;
}

// Handle deactivation (disabled since is_active column doesn't exist)
if (isset($_GET['deactivate'])) {
    $uid = intval($_GET['deactivate']);
    // $mysqli->query("UPDATE users SET is_active = 0 WHERE id = $uid");
    echo "<script>alert('Deactivation feature requires is_active column in database');</script>";
    // header("Location: admin-dash.php");
    // exit;
}

// Handle activation (disabled since is_active column doesn't exist)
if (isset($_GET['activate'])) {
    $uid = intval($_GET['activate']);
    // $mysqli->query("UPDATE users SET is_active = 1 WHERE id = $uid");
    echo "<script>alert('Activation feature requires is_active column in database');</script>";
    // header("Location: admin-dash.php");
    // exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard - MoveByMood</title>
    <link rel="icon" type="image/png" href="images/real-logo.png">

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Lilita+One&display=swap" rel="stylesheet">
    <link rel="stylesheet"
     href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="admin-dash.css">
</head>

<body>

<!-- HEADER -->
<header class="header">
    <img src="images/real-logo.png" class="logo">
    <img src="images/words-logo.png" class="words-logo">
</header>

<!-- SIDEBAR -->
<aside id="sidebar">
    <img src="images/temp-profile.png" class="profile-pic">
    <h2 class="username"><?= htmlspecialchars($_SESSION['username']) ?></h2>

    <ul class="sidebar-list">
        <li><button onclick="location.href='admin-dash.php'">
            <i class="fa-solid fa-chart-column"></i> Admin Dashboard</button></li>

        <li><button onclick="location.href='logout.php'">
            <i class="fa-solid fa-right-from-bracket"></i> Logout</button></li>
    </ul>
</aside>

<!-- MAIN -->
<main class="main-container">

    <h1 class="section-title">User Activity</h1>

    <section class="table-section">
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created</th>
                </tr>
            </thead>

            <tbody>
            <?php while ($row = $userQuery->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['UserID'] ?></td>
                    <td><?= htmlspecialchars($row['Username']) ?></td>
                    <td><?= htmlspecialchars($row['Email']) ?></td>
                    <td><?= htmlspecialchars($row['Role']) ?></td>
                    <td><?= $row['CreatedAt'] ?: "N/A" ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </section>

    <h1 class="section-title">User Reports</h1>

<section class="table-section">
<?php if ($reportQuery && $reportQuery->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Report ID</th>
                <th>Sent By</th>
                <th>On User</th>
                <th>Type</th>
                <th>Report</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>
        <?php while ($r = $reportQuery->fetch_assoc()): ?>
            <tr>
                <td><?= $r['ReportID'] ?></td>
                <td><?= htmlspecialchars($r['SenderName'] ?? 'Unknown') ?></td>
                <td><?= htmlspecialchars($r['TargetName'] ?? 'Unknown') ?></td>
                <td><?= htmlspecialchars($r['ReportType'] ?? 'Unknown') ?></td>
                <td><?= htmlspecialchars($r['Report']) ?></td>
                <td><?= htmlspecialchars($r['Validation_Status']) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p style="text-align:center; padding:20px; color:#666;">
        No reports found.
    </p>
<?php endif; ?>
</section>


</main>

</body>
</html>