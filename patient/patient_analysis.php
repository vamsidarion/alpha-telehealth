<?php
include '../config.php';
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// --- Patient Analysis ---
// Use patient_id from URL, or default to 3
$patient_id = $_GET['id'] ?? 3; 

// 1. Fetch Patient Info
$patient_query = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$patient_query->bind_param("i", $patient_id);
$patient_query->execute();
$patient = $patient_query->get_result()->fetch_assoc();

// 2. Fetch ALL logs for this patient
$logs_query = $conn->prepare("
    SELECT * FROM patient_logs 
    WHERE patient_id = ? 
    ORDER BY log_date DESC
");
$logs_query->bind_param("i", $patient_id);
$logs_query->execute();
$all_logs_result = $logs_query->get_result();

$all_logs = [];
$problem_counts = [];
while ($row = $all_logs_result->fetch_assoc()) {
    $all_logs[] = $row;
    $area = $row['problem_area'];
    if (!isset($problem_counts[$area])) {
        $problem_counts[$area] = 0;
    }
    $problem_counts[$area]++;
}

// 3. Prepare data for the JavaScript chart
$chart_labels = json_encode(array_keys($problem_counts));
$chart_data = json_encode(array_values($problem_counts));
$all_logs_json = json_encode($all_logs);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Patient Analysis: <?= htmlspecialchars($patient['name'] ?? 'Patient') ?></title>
<!-- 1. Load the Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* Import Google Fonts at the top */
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700&family=Open+Sans:wght@400;600&display=swap');

:root {
    --primary-color: #3a5a40;
    --primary-hover: #303d2b;
    --secondary-color: #588157;
    --accent-color: #a3b18a;
    --bg-light: #f8f9fa;
    --card-bg: #ffffff;
    --text-light: #ffffff;
    --text-dark: #333333;
    --font-heading: 'Montserrat', sans-serif;
    --font-body: 'Open Sans', sans-serif;
}
body { 
    font-family: var(--font-body); 
    margin:0; 
    background: var(--bg-light); 
    color: var(--text-dark);
}
.container { 
    max-width:1200px; 
    margin: 30px auto; 
    padding:0 20px; 
}
footer { 
    background: var(--primary-color); 
    color: rgba(255, 255, 255, 0.8); 
    padding:40px 20px; 
    text-align:center; 
    margin-top: 60px;
}
footer a { color: var(--text-light); margin:0 10px; text-decoration:none; font-weight: 600; }
footer a:hover { text-decoration:underline; }

/* === INTERNAL PAGE HEADER === */
.header-internal {
    background: var(--primary-color);
    color: var(--text-light);
    padding: 15px 0;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.header-internal .container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 0 auto;
    padding: 0 20px;
}
.header-internal .nav-logo {
    font-size: 1.5em;
    font-weight: 700;
    font-family: var(--font-heading);
    color: var(--text-light);
    text-decoration: none;
}
.header-internal .nav-links a {
    color: var(--text-light);
    text-decoration: none;
    margin-left: 20px;
    font-weight: 600;
    font-size: 0.9em;
}
.header-internal .nav-links a.nav-button {
    background: var(--text-light);
    color: var(--primary-color);
    padding: 8px 15px;
    border-radius: 5px;
}

/* === ANALYSIS LAYOUT === */
.analysis-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
.log-panel {
    background: var(--card-bg);
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    margin-bottom: 20px;
}
.log-panel h3 {
    font-family: var(--font-heading);
    color: var(--primary-color);
    margin-top: 0;
    margin-bottom: 20px;
    border-bottom: 2px solid var(--bg-light);
    padding-bottom: 10px;
}
.box {
    background: var(--bg-light);
    padding: 15px;
    border-radius: 5px;
    margin-top: 10px;
    border-left: 4px solid var(--accent-color);
    font-size: 0.95em;
}

/* === SVG BODY MAP === */
.body-map-svg {
    width: 100%;
    max-width: 300px;
    height: auto;
    margin: 0 auto;
    display: block;
}
.body-map-svg path {
    fill: #cce5cc; /* Light green fill */
    stroke: var(--primary-color);
    stroke-width: 1.5;
    transition: fill 0.3s ease;
}
.body-map-svg path.clickable:hover {
    fill: var(--accent-color); /* Highlight color */
    cursor: pointer;
}
.body-map-svg path.selected {
    fill: #ffA500; /* Orange for selected */
}

/* === LOG DISPLAY AREA === */
#log-details {
    margin-top: 20px;
}
#log-details h4 {
    font-family: var(--font-heading);
    color: var(--primary-color);
    border-bottom: 1px solid #eee;
    padding-bottom: 5px;
}
#log-details .log-entry {
    background: var(--bg-light);
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 10px;
}
#log-details .log-entry p {
    margin: 5px 0;
}
#log-details .log-entry strong {
    color: var(--primary-color);
}
#log-details .log-entry small {
    color: #777;
    font-weight: 600;
}
#log-details .no-logs {
    font-style: italic;
    color: #777;
}

@media (max-width: 992px) {
    .analysis-layout {
        grid-template-columns: 1fr;
    }
}
</style>
</head>
<body>

<!-- Standard Internal Header -->
<header class="header-internal">
    <div class="container">
        <a href="../index.php" class="nav-logo">Project ALPHA</a>
        <div class="nav-links">
            <a href="dashboard.php?id=<?= $patient_id ?>">Patient Dashboard</a>
            <a href="../logout.php" class="nav-button">Logout</a>
        </div>
    </div>
</header>

<div class="container">
    <h1 style="font-family: var(--font-heading); color: var(--primary-color); text-align: center; margin-bottom: 20px;">
        Patient Analysis: <?= htmlspecialchars($patient['name'] ?? 'Patient') ?>
    </h1>

    <div class="analysis-layout">
        
        <!-- ===== LEFT COLUMN (CHART) ===== -->
        <div class="log-panel">
            <h3>Problem Area Frequency</h3>
            <!-- 2. This is the canvas for the chart -->
            <canvas id="problemChart"></canvas>
        </div>

        <!-- ===== RIGHT COLUMN (SVG MAP) ===== -->
        <div class="log-panel">
            <h3>Click a body part to view logs</h3>
            
            <!-- 3. This is the interactive SVG body map -->
            <!-- SVG paths are simplified for this demo -->
            <svg 
                class="body-map-svg" 
                viewBox="0 0 200 450" 
                xmlns="http://www.w3.org/2000/svg"
            >
                <!-- Clickable parts have data-area attribute -->
                <path class="clickable" data-area="Head" d="M80 60 a30 30 0 1 1 40 0 a30 30 0 1 1 -40 0"/>
                <path d="M100 90 v40"/> <!-- Neck -->
                <path class="clickable" data-area="Chest" d="M70 130 h60 v50 h-60 z"/>
                <path class="clickable" data-area="Abdomen" d="M70 180 h60 v50 h-60 z"/>
                <!-- Arms -->
                <path class="clickable" data-area="Left Shoulder" d="M70 130 l-30 30 v60 l30 -20 z"/>
                <path class="clickable" data-area="Right Shoulder" d="M130 130 l30 30 v60 l-30 -20 z"/>
                <!-- Legs -->
                <path class="clickable" data-area="Left Knee" d="M70 230 v80 l-10 90 h20 l-10 -90 z"/>
                <path class="clickable" data-area="Right Knee" d="M110 230 v80 l-10 90 h20 l-10 -90 z"/>
            </svg>

            <!-- 4. This is where the log details will appear -->
            <div id="log-details">
                <h4>Please select a body part</h4>
                <div class="no-logs">No area selected.</div>
            </div>

        </div>

    </div>
</div>

<?php
include '../includes/footer.php';
?>

<!-- 5. All the JavaScript goes here -->
<script>
    // --- 1. Pass PHP data to JavaScript ---
    const allLogs = <?php echo $all_logs_json; ?>;
    const chartLabels = <?php echo $chart_labels; ?>;
    const chartData = <?php echo $chart_data; ?>;

    // --- 2. Draw the Chart ---
    const ctx = document.getElementById('problemChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar', // You can change this to 'doughnut' or 'pie'
            data: {
                labels: chartLabels,
                datasets: [{
                    label: '# of Logs',
                    data: chartData,
                    backgroundColor: [
                        'rgba(58, 90, 64, 0.7)',
                        'rgba(88, 129, 87, 0.7)',
                        'rgba(163, 177, 138, 0.7)',
                    ],
                    borderColor: [
                        'rgba(58, 90, 64, 1)',
                        'rgba(88, 129, 87, 1)',
                        'rgba(163, 177, 138, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                responsive: true,
                plugins: {
                    legend: {
                        display: false // Hide legend for a cleaner look
                    }
                }
            }
        });
    }

    // --- 3. Make the SVG Body Map Interactive ---
    const bodyParts = document.querySelectorAll('.body-map-svg path.clickable');
    const logDetailsContainer = document.getElementById('log-details');
    let selectedPart = null;

    bodyParts.forEach(part => {
        part.addEventListener('click', () => {
            const area = part.getAttribute('data-area');
            
            // Remove 'selected' class from old part
            if (selectedPart) {
                selectedPart.classList.remove('selected');
            }
            // Add 'selected' class to new part
            part.classList.add('selected');
            selectedPart = part;

            // Update the log details
            updateLogDetails(area);
        });
    });

    function updateLogDetails(area) {
        // Filter the logs for the selected area
        const logsForArea = allLogs.filter(log => log.problem_area === area);

        // Clear the container
        logDetailsContainer.innerHTML = '';

        // Create a title
        const title = document.createElement('h4');
        title.textContent = `Logs for: ${area}`;
        logDetailsContainer.appendChild(title);

        if (logsForArea.length > 0) {
            logsForArea.forEach(log => {
                const entry = document.createElement('div');
                entry.className = 'log-entry';
                entry.innerHTML = `
                    <small>${log.log_date}</small>
                    <p><strong>Diagnosis:</strong> ${log.main_diagnosis}</p>
                    <p><strong>Notes:</strong> ${log.description}</p>
                `;
                logDetailsContainer.appendChild(entry);
            });
        } else {
            const noLogs = document.createElement('div');
            noLogs.className = 'no-logs';
            noLogs.textContent = `No logs found for ${area}.`;
            logDetailsContainer.appendChild(noLogs);
        }
    }
</script>

</body>
</html>
