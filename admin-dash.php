<?php
//admin-dash.php
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
    $_SESSION['role'] = 'admin';
}

// Ensure username exists with fallback
if (empty($_SESSION['username'])) {
    $_SESSION['username'] = 'Administrator';
}

// Connect to DB
$mysqli = new mysqli("localhost", "root", "", "movebymood");

// Fetch user activity
$userQuery = $mysqli->query("
    SELECT UserID, Username, Email, Role, CreatedAt
    FROM users
    WHERE Role != 'admin'
    ORDER BY CreatedAt DESC
");

// Check if reports table exists before querying
$tableCheck = $mysqli->query("SHOW TABLES LIKE 'reports'");
if ($tableCheck && $tableCheck->num_rows > 0) {
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
        ORDER BY r.ReportID DESC
    ");
} else {
    $reportQuery = null;
}

// Get statistics
$totalUsers = $mysqli->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$totalReports = $reportQuery ? $reportQuery->num_rows : 0;
$pendingReports = 0;
if ($reportQuery) {
    $reportQuery->data_seek(0); // Reset pointer
    while ($r = $reportQuery->fetch_assoc()) {
        if ($r['Validation_Status'] == 'Pending') $pendingReports++;
    }
    $reportQuery->data_seek(0); // Reset for table display
}

// Handle deactivation (disabled since is_active column doesn't exist)
if (isset($_GET['deactivate'])) {
    $uid = intval($_GET['deactivate']);
    echo "<script>alert('Deactivation feature requires is_active column in database');</script>";
}

// Handle activation (disabled since is_active column doesn't exist)
if (isset($_GET['activate'])) {
    $uid = intval($_GET['activate']);
    echo "<script>alert('Activation feature requires is_active column in database');</script>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MoveByMood</title>
    <link rel="icon" type="image/png" href="images/real-logo.png">

    <!--Inter Font-->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!--Font Awesome-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- External CSS -->
    <link rel="stylesheet" href="admin-dash.css?v=2.0">
</head>

<body>
    <!--header-->
    <header class="header">
        <img src="images/real-logo.png" class="logo" alt="Logo">
        <img src="images/words-logo.png" class="words-logo" alt="MoveByMood">
    </header>
    <!--end of header-->

    <!--sidebar-->
    <aside id="sidebar">
        <img src="images/temp-profile.png" class="profile-pic" alt="Profile">
        <h2 class="username"><?= htmlspecialchars($_SESSION['username']) ?></h2>
        <span class="user-role">Administrator</span>

        <ul class="sidebar-list">
            <li><button onclick="location.href='admin-dash.php'"><i class="fa-solid fa-chart-column"></i> Dashboard</button></li>
            <li class="settings"><button onclick="location.href='logout.php'"><i class="fa-solid fa-right-from-bracket"></i> Logout</button></li>
        </ul>
    </aside>
    <!--end of sidebar-->

    <!--main content-->
    <main class="main-container">
        
        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="card">
                <span class="card-label">Total Users</span>
                <span class="card-value"><?= $totalUsers ?></span>
            </div>
            <div class="card">
                <span class="card-label">Total Reports</span>
                <span class="card-value"><?= $totalReports ?></span>
            </div>
            <div class="card">
                <span class="card-label">Pending Reports</span>
                <span class="card-value"><?= $pendingReports ?></span>
            </div>
            <div class="card">
                <span class="card-label">Active Sessions</span>
                <span class="card-value">N/A</span>
            </div>
        </div>

        <!-- User Activity Section -->
        <section class="data-section">
            <h2 class="section-title"><i class="fa-solid fa-users"></i> User Activity</h2>
            <div class="table-wrapper">
                <table class="data-table">
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
                            <td><strong><?= htmlspecialchars($row['Username']) ?></strong></td>
                            <td><?= htmlspecialchars($row['Email']) ?></td>
                            <td>
                                <span class="role-badge <?= strtolower($row['Role']) ?>">
                                    <?= htmlspecialchars($row['Role']) ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y', strtotime($row['CreatedAt'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- User Reports Section -->
        <section class="data-section">
            <h2 class="section-title"><i class="fa-solid fa-flag"></i> User Reports</h2>
            <div class="table-wrapper">
            <?php if ($reportQuery && $reportQuery->num_rows > 0): ?>
                <table class="data-table">
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
                            <td><strong><?= htmlspecialchars($r['SenderName'] ?? 'Unknown') ?></strong></td>
                            <td><strong><?= htmlspecialchars($r['TargetName'] ?? 'Unknown') ?></strong></td>
                            <td>
                                <span class="type-badge">
                                    <?= htmlspecialchars($r['ReportType'] ?? 'Unknown') ?>
                                </span>
                            </td>
                            <td class="report-text"><?= htmlspecialchars($r['Report']) ?></td>
                            <td>
                                <span class="status-badge <?= strtolower($r['Validation_Status']) ?>">
                                    <?= htmlspecialchars($r['Validation_Status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <i class="fa-solid fa-inbox"></i>
                    <p>No reports found.</p>
                </div>
            <?php endif; ?>
            </div>
        </section>

    </main>

</body>
</html>