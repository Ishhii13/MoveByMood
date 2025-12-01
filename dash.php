<?php
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoveByMood</title>
    <link rel="icon" type="image/png" href="images/real-logo.png">

    <!--Lilita One-->
    <link href="https://fonts.googleapis.com/css2?family=Lilita+One&display=swap" rel="stylesheet">

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
        <img src="images/real-logo.png" class="logo">
        <img src="images/words-logo.png" class="words-logo">
    </header>
    <!--end of header-->

    <!--sidebar-->
    <aside id="sidebar">
        <img src="images/temp-profile.png" class="profile-pic">
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
        <button onclick="location.href='exercise.php'" class="btn-workout">Start Your Workout</button>

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
    <!--end of main content-->

    <script>
    // ------------------------------
    // CLASS: ChartData
    // ------------------------------
    class ChartData {
        constructor() {
            this.activityData = {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Active Minutes',
                    data: [30, 45, 25, 50, 60, 40, 35],
                    backgroundColor: 'rgba(74, 144, 226, 1)',
                    borderRadius: 6
                }]
            };

            this.workoutData = {
                labels: ['Cardio', 'Strength', 'Yoga', 'Stretching', 'HIIT'],
                datasets: [{
                    label: 'Workouts',
                    data: [5, 3, 2, 1, 4],
                    backgroundColor: [
                        '#4a90e2',
                        '#50e3c2',
                        '#f5a623',
                        '#9013fe',
                        '#e94e77'
                    ]
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

    // ------------------------------
    // CLASS: ChartRenderer
    // ------------------------------
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
                    scales: {
                        y: { beginAtZero: true, title: { display: true, text: 'Minutes' } },
                        x: { title: { display: true, text: 'Day' } }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        }

        renderWorkoutChart() {
            const ctx = document.getElementById('workoutChart');
            if (!ctx) return;

            new Chart(ctx, {
                type: 'pie',
                data: this.chartData.getWorkoutData(),
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        initCharts() {
            this.renderActivityChart();
            this.renderWorkoutChart();
        }
    }

    // ------------------------------
    // CLASS: DashboardUI
    // ------------------------------
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

    // Initialize dashboard
    const dashboard = new DashboardUI();
    dashboard.init();
    </script>
</body>
</html>