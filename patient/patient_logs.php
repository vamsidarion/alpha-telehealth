<?php
include '../config.php'; // <-- Path corrected
// error_reporting(E_ALL); // Uncomment these lines if you get a blank white page
// ini_set('display_errors', 1);

// --- Patient Log Demo ---
// Use patient_id from URL, or default to 3
$patient_id = $_GET['id'] ?? 3; 

// 1. Fetch Patient Info
$patient_query = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$patient_query->bind_param("i", $patient_id);
$patient_query->execute();
$patient = $patient_query->get_result()->fetch_assoc();

// 2. Fetch the LATEST log for this patient
$log_query = $conn->prepare("
    SELECT * FROM patient_logs 
    WHERE patient_id = ? 
    ORDER BY log_date DESC 
    LIMIT 1
");
$log_query->bind_param("i", $patient_id);
$log_query->execute();
$log = $log_query->get_result()->fetch_assoc();

// 3. --- MOCK DATA (if no log exists) ---
if (!$log) {
    // If no log is found, create mock data
    $log = [
        'problem_area' => 'Right Shoulder',
        'main_diagnosis' => '[S43.4] Sprain of shoulder joint',
        'description' => 'Patient reports pain and stiffness in right shoulder after a fall.'
    ];
    $treatment_plan = [
        ['time' => '02:00 PM', 'title' => 'X-Ray: Right Shoulder', 'doctor' => 'Dr. Anna Lee'],
        ['time' => '03:30 PM', 'title' => 'Consultation: Physical Therapy', 'doctor' => 'Dr. James Smith']
    ];
} else {
    // TODO: Fetch real treatment plan from appointments table based on the log
    $treatment_plan = [
        ['time' => '02:00 PM', 'title' => 'Follow-up X-Ray', 'doctor' => 'Dr. Anna Lee'],
        ['time' => '03:30 PM', 'title' => 'Physical Therapy Session 2', 'doctor' => 'Dr. James Smith']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Patient Log: <?= htmlspecialchars($patient['name'] ?? 'Patient') ?></title>
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
.header-internal .nav-links a.nav-button:hover {
    background: #eee;
}

/* === CONTENT STYLES === */
.section {
    background: var(--card-bg);
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    margin-bottom: 30px;
}
.section h2, .log-panel h3 {
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

/* === PATIENT LOG LAYOUT === */
.log-layout {
    display: grid;
    /* --- GRID RESIZED --- */
    grid-template-columns: 4fr 3fr 4fr;
    gap: 20px;
    max-width: 1400px;
    margin: 30px auto;
    padding: 0 20px;
}
.body-map-container {
    position: relative;
    text-align: center;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    padding: 20px;
    min-height: 500px; /* Give it a minimum height */
    /* REMOVED flex, justify-content, and align-items 
       to make the image align to the top of the container.
    */
}
.body-map-container img {
    width: 100%;
    max-width: 200px; /* A bit narrower for the new grid */
    height: auto;
    /* Horizontally center the image */
    display: block;
    margin: 0 auto;
}
.problem-dot {
    position: absolute;
    width: 25px;
    height: 25px;
    background-color: rgba(255, 165, 0, 0.7);
    border: 2px solid #fff;
    border-radius: 50%;
    box-shadow: 0 0 10px rgba(255, 165, 0, 1);
    transform: translate(-50%, -50%);
    z-index: 10;
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0% { box-shadow: 0 0 5px rgba(255, 165, 0, 0.7); }
    50% { box-shadow: 0 0 20px rgba(255, 165, 0, 1); }
    100% { box-shadow: 0 0 5px rgba(255, 165, 0, 0.7); }
}

/* --- Dot Positions (Adjusted for top-aligned image) --- */
/* You may need to tweak these % values */
.dot-Right-Knee { top: 62%; left: 45%; } 
.dot-Left-Knee { top: 62%; left: 55%; }
.dot-Right-Shoulder { top: 22%; left: 38%; } /* <-- This is the one from your screenshot */
.dot-Left-Shoulder { top: 22%; left: 62%; } 
.dot-Head { top: 8%; left: 50%; }
.dot-Chest { top: 28%; left: 50%; }
.dot-Abdomen { top: 40%; left: 50%; }

.log-panel {
    background: var(--card-bg);
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    margin-bottom: 20px;
}
.plan-item {
    display: flex;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid var(--bg-light);
}
.plan-item:last-child { border-bottom: none; }
.plan-time {
    font-weight: 600;
    color: var(--primary-color);
    font-family: var(--font-heading);
}
.plan-details p { margin: 0; font-weight: 600; }
.plan-details span { font-size: 0.9em; color: #555; }
    
@media (max-width: 992px) {
    .log-layout {
        grid-template-columns: 1fr;
    }
    .body-map-container {
        grid-row: 1; /* Make map appear first on mobile */
        min-height: 400px;
    }
    .body-map-container img {
        max-width: 200px; /* Keep it consistent */
    }
}
</style>
</head>
<body>

<!-- Standard Internal Header -->
<header class="header-internal">
    <div class="container">
        <!-- Path corrected -->
        <a href="../index.php" class="nav-logo">Project ALPHA</a>
        <div class="nav-links">
            <!-- Path corrected -->
            <a href="dashboard.php?id=<?= $patient_id ?>">Patient Dashboard</a>
            <!-- Path corrected -->
            <a href="../logout.php" class="nav-button">Logout</a>
        </div>
    </div>
</header>

<div class="container">
    <h1 style="font-family: var(--font-heading); color: var(--primary-color); text-align: center; margin-bottom: 20px;">
        Patient Log: <?= htmlspecialchars($patient['name'] ?? 'Patient') ?>
    </h1>
</div>

<div class="log-layout">
    
    <!-- ===== LEFT COLUMN ===== -->
    <div class="log-column-left">
        <div class="log-panel">
            <h3>Diagnostic Results</h3>
            <div class="box">
                <p style="font-size: 0.8em; font-weight: 600; color: #777; margin-bottom: 5px;">CONSULTATION</p>
                <p style="font-weight: 600; margin-top: 0;">Initial examination</p>
            </div>
            <div class="box" style="border-left-color: var(--secondary-color);">
                <p style="font-size: 0.8em; font-weight: 600; color: #777; margin-bottom: 5px;">LAB SCREENING</p>
                <p style="font-weight: 600; margin-top: 0;">Blood screening: CRP, RF and ESR</p>
            </div>
        </div>

        <div class="log-panel">
            <h3>Treatment Plan</h3>
            <?php if (!empty($treatment_plan)): ?>
                <?php foreach ($treatment_plan as $item): ?>
                    <div class="plan-item">
                        <div class="plan-time"><?= $item['time'] ?></div>
                        <div class="plan-details">
                            <p><?= htmlspecialchars($item['title']) ?></p>
                            <span><?= htmlspecialchars($item['doctor']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No treatment plan logged.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===== CENTER COLUMN ===== -->
    <div class="body-map-container">
        <!-- 
            THIS IS THE NEW IMAGE LINK YOU PROVIDED
        -->
        <img src="https://t3.ftcdn.net/jpg/02/24/24/06/360_F_224240638_9cg17hGtJw54J4kLjLNsU6EfYiBoJUcI.jpg" alt="Human Body Diagram" style="max-width: 200px;">

        <?php
        if ($log) {
            $dot_class = '';
            // Replace spaces in the name to create a valid CSS class
            $problem_class = str_replace(' ', '-', $log['problem_area']);
            
            // We'll check against a list of known classes
            $known_dots = [
                'Right-Knee', 'Left-Knee', 'Right-Shoulder', 'Left-Shoulder', 'Head', 'Chest', 'Abdomen'
            ];

            if (in_array($problem_class, $known_dots)) {
                $dot_class = 'dot-' . $problem_class;
                echo "<div class='problem-dot {$dot_class}'></div>";
            }
        }
        ?>
    </div>

    <!-- ===== RIGHT COLUMN ===== -->
    <div classs="log-column-right">
        <div class="log-panel">
            <h3><?= htmlspecialchars($log['problem_area'] ?? 'No problem selected') ?></h3>
            <p><strong>MAIN DIAGNOSIS:</strong></p>
            <p style="font-weight: 600;"><?= htmlspecialchars($log['main_diagnosis'] ?? 'N/A') ?></p>
            
            <p style="margin-top: 15px;"><strong>DOCTOR'S NOTES:</strong></p>
            <p><?= nl2br(htmlspecialchars($log['description'] ?? 'N/A')) ?></p>
        </div>

        <div class="log-panel">
            <h3>Medication</h3>
            <div class="box">
                <p style="font-weight: 600; margin: 0;">Acelofenac</p>
                <span style="font-size: 0.9em; color: #555;">Tablets - 100mg</span>
            </div>
            <div class="box">
                <p style="font-weight: 600; margin: 0;">Diclofenac</p>
                <span style="font-size: 0.9em; color: #555;">Topical Gel - 2%</span>
            </div>
        </div>
    </div>

</div>

<?php
// Path corrected
include '../includes/footer.php';
?>
</body>
</html>

