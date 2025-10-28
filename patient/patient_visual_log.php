<?php
include '../config.php';
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// --- Patient Analysis ---
$patient_id = $_GET['id'] ?? 3; // Default to patient 3 for demo

// 1. Fetch Patient Info
$patient_query = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$patient_query->bind_param("i", $patient_id);
$patient_query->execute();
$patient = $patient_query->get_result()->fetch_assoc();

// 2. Fetch ALL logs for this patient (for SVG map)
$logs_query = $conn->prepare("
    SELECT log_id, problem_area, main_diagnosis, description, log_date, medication 
    FROM patient_logs 
    WHERE patient_id = ? 
    ORDER BY log_date DESC
"); // <-- Added 'medication'
$logs_query->bind_param("i", $patient_id);
$logs_query->execute();
$all_logs_result = $logs_query->get_result();

$all_logs = [];
$problem_areas_with_logs = [];
while ($row = $all_logs_result->fetch_assoc()) {
    $all_logs[] = $row;
    // We need to convert "Right Shoulder" to "right_shoulder" for the SVG id
    $area_id = strtolower(str_replace(' ', '_', $row['problem_area']));
    if (!in_array($area_id, $problem_areas_with_logs)) {
        $problem_areas_with_logs[] = $area_id;
    }
}

// 3. Fetch ALL Appointments for this patient (for Treatment Plan panel)
$appointments_query = $conn->prepare("
    SELECT a.date_time, a.status, d.name as doctor_name
    FROM appointments a
    LEFT JOIN users d ON a.doctor_id = d.user_id
    WHERE a.patient_id = ?
    ORDER BY a.date_time DESC
");
$appointments_query->bind_param("i", $patient_id);
$appointments_query->execute();
$appointments = $appointments_query->get_result();


// 4. Prepare data for JavaScript
$all_logs_json = json_encode($all_logs);
$problem_areas_json = json_encode($problem_areas_with_logs);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Visual Log: <?= htmlspecialchars($patient['name'] ?? 'Patient') ?></title>
<style>
/* Import Google Fonts at the top */
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700&family=Open+Sans:wght@400;600&display=swap');

:root {
    --primary-color: #3a5a40;
    --primary-hover: #303d2b;
    --secondary-color: #588157;
    --accent-color: #a3b18a;
    --highlight-color: #ffa500; /* Orange highlight */
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

/* === NEW HERO SECTION === */
.hero-main {
    position: relative;
    padding: 80px 20px;
    padding-top: 150px; 
    text-align: center;
    color: var(--text-light);
    background: url('https://images.pexels.com/photos/3985163/pexels-photo-3985163.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1') no-repeat center center;
    background-size: cover;
    min-height: 40vh; /* Shorter hero */
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}
.hero-main::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.5); 
    z-index: 1;
}
.hero-main > * { 
    position: relative;
    z-index: 2;
}
.hero-main nav {
    position: absolute;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    width: 90%;
    max-width: 1200px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(5px);
    border-radius: 8px;
    box-sizing: border-box;
}
.hero-main .nav-logo {
    font-size: 1.5em;
    font-weight: 700;
    font-family: var(--font-heading);
    color: var(--text-light);
    text-decoration: none;
}
.hero-main .nav-links a {
    color: var(--text-light);
    text-decoration: none;
    margin-left: 20px;
    font-weight: 600;
    font-size: 0.9em;
}
.hero-main .nav-links a.nav-button {
    background: var(--text-light);
    color: var(--primary-color);
    padding: 8px 15px;
    border-radius: 5px;
}
.hero-content {
    max-width: 600px;
    margin: 40px auto 0 auto;
}
.hero-content h2 {
    font-family: var(--font-heading);
    font-size: 3em;
    margin: 0 0 15px 0;
}
.hero-content p {
    font-size: 1.2em;
    margin-bottom: 30px;
    opacity: 0.9;
}


/* === PROFESSIONAL LOG LAYOUT === */
.log-layout {
    display: grid;
    grid-template-columns: 4fr 3fr 4fr;
    gap: 20px;
    max-width: 1400px;
    margin: 30px auto;
    padding: 0 20px;
}
.log-panel {
    background: var(--card-bg);
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    margin-bottom: 20px;
    min-height: 500px; 
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
.box p { margin: 5px 0; }
.box strong { color: var(--primary-color); }

/* === INTERACTIVE SVG BODY MAP === */
.body-map-container {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    padding: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
}
#body-svg {
    width: 100%;
    max-width: 250px;
    height: auto;
}
/* Style for all body parts */
#body-svg path {
    fill: #e0e0e0; /* Neutral gray */
    stroke: #555;
    stroke-width: 1px;
    transition: fill 0.2s ease-in-out;
}
/* Style for body parts WITH LOGS */
#body-svg path.has-log {
    fill: var(--accent-color); /* Green to show data is present */
    cursor: pointer;
}
/* Style for HOVERING over a part with logs */
#body-svg path.has-log:hover {
    fill: var(--secondary-color); /* Darker green */
}
/* Style for the CLICKED/SELECTED part */
#body-svg path.selected {
    fill: var(--highlight-color); /* Bright orange */
    stroke: var(--primary-hover);
    stroke-width: 2px;
}

/* === LOG DISPLAY AREA (RIGHT COLUMN) === */
#log-display-area {
    max-height: 250px; /* Adjusted height */
    overflow-y: auto;
    padding-right: 10px; /* For scrollbar */
}
#medication-display-area {
    max-height: 150px; /* Height for medication */
    overflow-y: auto;
    padding-right: 10px; /* For scrollbar */
}

.log-entry {
    background: var(--bg-light);
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 15px;
    border-left: 4px solid var(--secondary-color);
}
.log-entry small {
    font-weight: 600;
    color: #555;
    font-family: var(--font-heading);
}
.log-entry p {
    margin: 8px 0 0 0;
}
.log-entry strong {
    color: var(--primary-color);
}
#log-display-area h4 {
    font-family: var(--font-heading);
    color: var(--primary-color);
}
#log-display-area .no-logs,
#medication-display-area .no-logs {
    font-style: italic;
    color: #777;
    padding: 20px;
    text-align: center;
}

/* === TREATMENT PLAN (LEFT COLUMN) === */
#treatment-plan-area {
    max-height: 400px;
    overflow-y: auto;
    padding-right: 10px; /* For scrollbar */
}
.appointment-item {
    background: var(--bg-light);
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 10px;
    border-left: 4px solid var(--accent-color);
}
.appointment-item p {
    margin: 5px 0;
    font-weight: 600;
}
.appointment-item small {
    font-family: var(--font-heading);
    color: var(--primary-color);
    font-weight: 600;
}
/* Style for status badges */
.status-badge {
    padding: 3px 8px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.8em;
    text-transform: capitalize;
    float: right;
}
.status-pending {
    background-color: #fff8e1; color: #f57f17;
}
.status-confirmed {
    background-color: #e3f2fd; color: #0d47a1;
}
.status-completed {
    background-color: #e8f5e9; color: #1b5e20;
}
.status-cancelled {
    background-color: #ffebee; color: #b71c1c;
}


@media (max-width: 992px) {
    .log-layout {
        grid-template-columns: 1fr;
    }
    .body-map-container {
        grid-row: 1; 
    }
}
</style>
</head>
<body>

<!-- NEW Hero Header Section -->
<section class="hero-main">
    <nav>
        <a href="../index.php" class="nav-logo">Project ALPHA</a>
        <div class="nav-links">
            <a href="dashboard.php?id=<?= $patient_id ?>">Patient Dashboard</a>
            <a href="../logout.php" class="nav-button">Logout</a>
        </div>
    </nav>
    <div class="hero-content">
        <h2>Visual Health Log</h2>
        <p><?= htmlspecialchars($patient['name'] ?? 'Patient') ?></p>
    </div>
</section>


<div class="log-layout">
    
    <!-- ===== LEFT COLUMN (Dynamic Treatment Plan) ===== -->
    <div class="log-panel">
        <h3>Treatment Plan</h3>
        <div id="treatment-plan-area">
            <?php if ($appointments && $appointments->num_rows > 0): ?>
                <?php while ($appt = $appointments->fetch_assoc()): ?>
                    <div class="appointment-item">
                        <span class="status-badge status-<?= htmlspecialchars($appt['status']) ?>">
                            <?= htmlspecialchars($appt['status']) ?>
                        </span>
                        <small><?= date('F j, Y, g:i a', strtotime($appt['date_time'])) ?></small>
                        <p>Consultation with <?= htmlspecialchars($appt['doctor_name'] ?? 'N/A') ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-logs"><p>No appointments found.</p></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===== CENTER COLUMN (Interactive SVG) ===== -->
    <div class="body-map-container">
        
        <!-- This is a simplified SVG. A real one would have more complex paths. -->
        <!-- Each clickable <path> has an id that matches the database 
             (e.g., id="right_shoulder" matches "Right Shoulder" in DB) -->
        <svg id="body-svg" viewBox="0 0 200 450" xmlns="http://www.w3.org/2000/svg">
            <path id="head" data-name="Head" d="M80 60 a30 30 0 1 1 40 0 a30 30 0 1 1 -40 0"/>
            <path id="neck" d="M90 90 h20 v20 h-20 z"/>
            <path id="chest" data-name="Chest" d="M70 110 h60 v50 h-60 z"/>
            <path id="abdomen" data-name="Abdomen" d="M70 160 h60 v50 h-60 z"/>
            
            <path id="left_shoulder" data-name="Left Shoulder" d="M70 110 l-30 20 v40 l30 -20 z"/>
            <path id="left_arm" d="M40 130 l-10 70 h20 l-10 -70 z"/>
            
            <path id="right_shoulder" data-name="Right Shoulder" d="M130 110 l30 20 v40 l-30 -20 z"/>
            <path id="right_arm" d="M160 130 l10 70 h-20 l10 -70 z"/>
            
            <path id="left_knee" data-name="Left Knee" d="M70 210 v80 l-10 90 h20 l-10 -90 z"/>
            <path id="right_knee" data-name="Right Knee" d="M110 210 v80 l-10 90 h20 l-10 -90 z"/>
        </svg>

    </div>

    <!-- ===== RIGHT COLUMN (Dynamic Log Display) ===== -->
    <div class="log-panel">
        
        <!-- New Patient Details Box -->
        <h3>Patient Details</h3>
        <div class="box">
            <p><strong>Name:</strong> <?= htmlspecialchars($patient['name'] ?? 'N/A') ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($patient['email'] ?? 'N/A') ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($patient['phone'] ?? 'N/A') ?></p>
        </div>

        <!-- Diagnostic Logs (populates on click) -->
        <h3 style="margin-top: 20px;">Diagnostic Logs</h3>
        <div id="log-display-area">
            <div class="no-logs">
                <p>Click a highlighted body part on the diagram to see related logs.</p>
                <p style="font-size: 0.9em; margin-top: 10px;">(Highlighted parts like <span style="color: var(--accent-color); font-weight: 600;">this</span> have logs.)</p>
            </div>
        </div>

        <!-- NEW Medication Panel (populates on click) -->
        <h3 style="margin-top: 20px;">Medication</h3>
        <div id="medication-display-area">
            <div class="no-logs">
                <p>Click a body part to see prescribed medication.</p>
            </div>
        </div>

    </div>

</div>

<?php
include '../includes/footer.php';
?>

<script>
    // --- 1. Get Data from PHP ---
    const allLogs = <?php echo $all_logs_json; ?>;
    const problemAreas = <?php echo $problem_areas_json; ?>;
    let selectedSVGPart = null;

    // --- 2. Get DOM Elements ---
    const svgMap = document.getElementById('body-svg');
    const logDisplay = document.getElementById('log-display-area');
    const medicationDisplay = document.getElementById('medication-display-area'); // <-- New display area

    // --- 3. Highlight Body Parts that Have Logs ---
    problemAreas.forEach(areaId => {
        const part = document.getElementById(areaId);
        if (part) {
            part.classList.add('has-log');
        }
    });

    // --- 4. Add Click Listeners to the SVG ---
    svgMap.addEventListener('click', (e) => {
        const clickedPart = e.target;
        
        // Check if the clicked part is a body part with logs
        if (clickedPart.classList.contains('has-log')) {
            const areaName = clickedPart.getAttribute('data-name');
            const areaId = clickedPart.id;

            // Update visual selection
            if (selectedSVGPart) {
                selectedSVGPart.classList.remove('selected');
            }
            clickedPart.classList.add('selected');
            selectedSVGPart = clickedPart;

            // Update the log and medication displays
            updateLogDisplay(areaName);
        }
    });

    // --- 5. Function to Update the Log and Medication Display Panels ---
    function updateLogDisplay(areaName) {
        // Filter logs for the selected area
        const logsForArea = allLogs.filter(log => log.problem_area === areaName);

        // Clear the displays
        logDisplay.innerHTML = '';
        medicationDisplay.innerHTML = '';
        
        if (logsForArea.length > 0) {
            // --- A. Populate Diagnostic Logs ---
            const logTitle = document.createElement('h4');
            logTitle.textContent = `Logs for: ${areaName}`;
            logDisplay.appendChild(logTitle);

            let hasMedication = false;

            logsForArea.forEach(log => {
                // Add log entry
                const entry = document.createElement('div');
                entry.className = 'log-entry';
                const logDate = new Date(log.log_date).toLocaleDateString('en-US', {
                    year: 'numeric', month: 'long', day: 'numeric'
                });

                entry.innerHTML = `
                    <small>${logDate}</small>
                    <p><strong>Diagnosis:</strong> ${log.main_diagnosis}</p>
                    <p><strong>Notes:</strong> ${nl2br(log.description)}</p>
                `;
                logDisplay.appendChild(entry);

                // --- B. Populate Medication ---
                if (log.medication && log.medication.trim() !== '') {
                    hasMedication = true;
                    const medBox = document.createElement('div');
                    medBox.className = 'box'; // Use .box style
                    // We use nl2br to respect line breaks from the textarea
                    medBox.innerHTML = `<p>${nl2br(log.medication)}</p>`;
                    medicationDisplay.appendChild(medBox);
                }
            });

            if (!hasMedication) {
                medicationDisplay.innerHTML = `<div class="no-logs"><p>No medication prescribed for this log.</p></div>`;
            }

        } else {
            // Show a "no logs" message (this shouldn't happen if it was highlighted, but good to have)
            logDisplay.innerHTML = `<div class="no-logs"><p>No logs found for ${areaName}.</p></div>`;
            medicationDisplay.innerHTML = `<div class="no-logs"><p>Click a body part to see prescribed medication.</p></div>`;
        }
    }

    // Helper function to mimic PHP's nl2br
    function nl2br(str) {
        if (typeof str === 'undefined' || str === null) {
            return '';
        }
        return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br>$2');
    }

</script>

</body>
</html>

