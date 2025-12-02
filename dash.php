<?php
//dash.php
// CLASS: SessionManager
class SessionManager {
    public static function checkLogin() {
        session_start();
        if (empty($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }
    }
}

// ✅ Check login before rendering dashboard
SessionManager::checkLogin();

// ✅ NEW: Check if account is active
require_once __DIR__ . '/db.php';
$mysqli = get_db();

$stmt = $mysqli->prepare("SELECT ActiveStatus FROM Users WHERE UserID = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($active);
$stmt->fetch();
$stmt->close();

if ((int)$active !== 1) {
    header("Location: deactivated.php");
    exit;
}

$statsQuery = $mysqli->prepare("
    SELECT 
        TotalWorkouts, TotalMinutes, CurrentStreak, WeeklyCompleted,
        CardioCount, StrengthCount, FlexibilityCount, OtherCount,
        AverageDuration
    FROM workout_stats
    WHERE UserID = ?
    LIMIT 1
");
$statsQuery->bind_param('i', $_SESSION['user_id']);
$statsQuery->execute();
$stats = $statsQuery->get_result()->fetch_assoc();
$statsQuery->close();

// If no stats exist yet, make empty defaults
if (!$stats) {
    $stats = [
        'TotalWorkouts' => 0,
        'TotalMinutes' => 0,
        'CurrentStreak' => 0,
        'WeeklyCompleted' => 0,
        'CardioCount' => 0,
        'StrengthCount' => 0,
        'FlexibilityCount' => 0,
        'OtherCount' => 0,
        'AverageDuration' => 0
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoveByMood</title>
    <link rel="icon" type="image/png" href="images/real-logo.png">

    <!--Inter Font-->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!--Font Awesome-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- External CSS -->
    <link rel="stylesheet" href="dash.css">
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

        <ul class="sidebar-list">
            <li><button onclick="location.href='dash.php'"><i class="fa-solid fa-house"></i> Home</button></li>
            <li><button onclick="location.href='messages.php'"><i class="fa-solid fa-envelope"></i> Messages</button></li>
            <li class="settings"><button onclick="location.href='settings.php'"><i class="fa-solid fa-gear"></i> Settings</button></li>
        </ul>
    </aside>
    <!--end of sidebar-->

    <!--main content-->
    <main class="main-container">
        <button onclick="location.href='exercise.php'" class="btn-workout">
            <i class="fa-solid fa-play"></i> Start Your Workout
        </button>

        <div class="stats-cards">
            <div class="card">
                <span class="card-label">Total Workouts</span>
                <span class="card-value"><?= $stats['TotalWorkouts'] ?></span>
            </div>
            <div class="card">
                <span class="card-label">Total Minutes</span>
                <span class="card-value"><?= $stats['TotalMinutes'] ?></span>
            </div>
            <div class="card">
                <span class="card-label">Current Streak</span>
                <span class="card-value"><?= $stats['CurrentStreak'] ?> Days</span>
            </div>
            <div class="card">
                <span class="card-label">Weekly Completed</span>
                <span class="card-value"><?= $stats['WeeklyCompleted'] ?></span>
            </div>
        </div>

        <section class="charts">
            <div class="chart-container">
                <h2>User Activity (Weekly)</h2>
                <canvas id="activityChart"></canvas>
            </div>

            <div class="chart-container">
                <h2>Workout Breakdown</h2>
                <canvas id="workoutChart"></canvas>
            </div>
        </section>
    </main>

    <script>
    const workoutStats = <?= json_encode($stats) ?>;
    
    class ChartData {
        constructor() {
            this.activityData = {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Active Minutes',
                    data: Array(7).fill(workoutStats.TotalMinutes / 7),
                    backgroundColor: 'rgba(67, 160, 71, 0.8)',
                    borderColor: 'rgba(67, 160, 71, 1)',
                    borderWidth: 2,
                    borderRadius: 8,
                    barThickness: 40
                }]
            };

            this.workoutData = {
                labels: ['Cardio', 'Strength', 'Flexibility', 'Other'],
                datasets: [{
                    label: 'Workouts',
                    data: [
                        workoutStats.CardioCount,
                        workoutStats.StrengthCount,
                        workoutStats.FlexibilityCount,
                        workoutStats.OtherCount
                    ],
                    backgroundColor: [
                        '#43A047',
                        '#2196F3',
                        '#FF9800',
                        '#7C4DFF'
                    ],
                    borderWidth: 0
                }]
            };
        }

        getActivityData() {
            return this.activityData;
        }

        getWorkoutData() {
            return this.workoutData;
        }
    }

    class ChartRenderer {
        constructor(chartData) {
            this.chartData = chartData;
        }

        renderActivityChart() {
            const ctx = document.getElementById('activityChart');
            if (!ctx) return;

            new Chart(ctx, {
                type: 'bar',
                data: this.chartData.getActivityData(),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Minutes',
                                font: { size: 12, weight: '600' }
                            },
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Day',
                                font: { size: 12, weight: '600' }
                            },
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

        renderWorkoutChart() {
            const ctx = document.getElementById('workoutChart');
            if (!ctx) return;

            new Chart(ctx, {
                type: 'doughnut',
                data: this.chartData.getWorkoutData(),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: { size: 12 },
                                usePointStyle: true
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }

        initCharts() {
            this.renderActivityChart();
            this.renderWorkoutChart();
        }
    }

    class DashboardUI {
        constructor() {
            this.chartRenderer = new ChartRenderer(new ChartData());
        }

        init() {
            document.addEventListener('DOMContentLoaded', () => {
                this.chartRenderer.initCharts();
            });
        }
    }

    const dashboard = new DashboardUI();
    dashboard.init();
    </script>
</body>
</html>