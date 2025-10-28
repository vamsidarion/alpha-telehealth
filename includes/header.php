<?php
// You can add session checks or other logic here later
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Project ALPHA</title>

<!-- 
    Internal CSS for all "content" pages.
    This contains all styles for forms, tables, buttons, etc.
    It does *not* contain the ".hero-main" styles.
-->
<style>
/* Import Google Fonts at the top */
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700&family=Open+Sans:wght@400;600&display=swap');

:root {
    /* New green color palette */
    --primary-color: #3a5a40; /* Deeper, more natural green */
    --primary-hover: #303d2b; /* Darker shade */
    --secondary-color: #588157;
    --accent-color: #a3b18a; /* Muted green for placeholders */
    --bg-light: #f8f9fa; /* A very light, clean background */
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

/* === NEW INTERNAL PAGE HEADER === */
.header-internal {
    background: var(--primary-color);
    padding: 15px 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.header-internal nav {
    width: 90%;
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
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

/* Container */
.container { 
    max-width:1200px; 
    margin: 40px auto; /* Added margin for space below header */
    padding:0 20px; 
}

/* Footer */
footer { 
    background: var(--primary-color); 
    color: rgba(255, 255, 255, 0.8); 
    padding:40px 20px; 
    text-align:center; 
    margin-top: 60px; /* Added margin */
}
footer a { color: var(--text-light); margin:0 10px; text-decoration:none; font-weight: 600; }
footer a:hover { text-decoration:underline; }


/* === INTERNAL CONTENT STYLES === */
.section {
    background: var(--card-bg);
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    margin-bottom: 30px;
}

.section h2 {
    font-family: var(--font-heading);
    color: var(--primary-color);
    margin-top: 0;
    margin-bottom: 20px;
    border-bottom: 2px solid var(--bg-light);
    padding-bottom: 10px;
}

/* Table Styles */
.table-wrapper {
    overflow-x: auto; /* Makes tables responsive */
}
table {
    width: 100%;
    border-collapse: separate; /* Use separate for clean rounded corners */
    border-spacing: 0;
    margin-top: 15px;
    font-size: 0.95em;
    border: 1px solid #e0e0e0;
    border-radius: 8px; /* Rounded corners for table */
    overflow: hidden; /* Clips content to rounded corners */
}
table, th, td {
    border-bottom: 1px solid #e0e0e0;
}
th, td {
    padding: 12px 15px;
    text-align: left;
    line-height: 1.6;
}
th {
    background: var(--primary-color);
    color: var(--text-light);
    font-family: var(--font-heading);
    font-weight: 600;
    border-bottom: none;
}
tr:nth-child(even) {
    background: var(--bg-light);
}
tr:hover {
    background: #f0f0f0;
}
tr:last-child td {
    border-bottom: none; /* No bottom border on last row */
}

/* Form Styles */
form {
    display: grid;
    gap: 15px;
}
label {
    font-weight: 600;
    font-family: var(--font-heading);
    color: var(--primary-color);
    font-size: 0.9em;
    margin-bottom: -5px; /* Pulls label closer to input */
}
textarea,
input[type="text"],
input[type="email"],
input[type="password"],
input[type="datetime-local"],
select {
    width: 100%;
    padding: 12px;
    font-family: var(--font-body);
    font-size: 1em;
    color: var(--text-dark);
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box; /* Important! */
    background: #fff;
}
select {
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%233a5a40' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px;
}
input[type="submit"] {
    background: var(--secondary-color);
    color: var(--text-light);
    padding: 12px 22px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 600;
    font-size: 1em;
    font-family: var(--font-heading);
    transition: background-color 0.3s ease;
    width: auto; /* Don't force full width */
    justify-self: start; /* Align button to the left */
}
input[type="submit"]:hover {
    background: var(--primary-color);
}

/* Button Styles */
.btn {
    text-decoration: none;
    color: var(--text-light);
    background: var(--secondary-color);
    padding: 8px 15px;
    border-radius: 5px;
    font-weight: 600;
    font-size: 0.9em;
    display: inline-block;
    transition: background-color 0.3s ease;
    border: none;
    cursor: pointer;
    font-family: var(--font-body);
}
.btn:hover {
    background: var(--primary-color);
    color: var(--text-light);
}

/* Box for saved descriptions */
.box {
    background: var(--bg-light);
    padding: 15px;
    border-radius: 5px;
    margin-top: 10px;
    border-left: 4px solid var(--accent-color);
    font-size: 0.95em;
}
.box p {
    margin: 8px 0;
}
.box small {
    color: #777;
    display: block;
    margin-top: 5px;
}

/* === STATUS BADGES === */
.status-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.85em;
    text-transform: capitalize;
}
.status-badge.status-pending {
    background-color: #fff8e1; /* Light yellow */
    color: #f57f17; /* Dark yellow/orange */
}
.status-badge.status-confirmed {
    background-color: #e3f2fd; /* Light blue */
    color: #0d47a1; /* Dark blue */
}
.status-badge.status-completed {
    background-color: #e8f5e9; /* Light green */
    color: #1b5e20; /* Dark green */
}
.status-badge.status-cancelled {
    background-color: #ffebee; /* Light red */
    color: #b71c1c; /* Dark red */
}

/* === RESPONSIVE STYLES === */
@media (max-width: 768px) {
    .header-internal nav {
        flex-direction: column;
        gap: 15px;
    }
    input[type="submit"] {
        width: 100%; /* Full width button on mobile */
    }
}
</style>
</head>
<body>

<!-- This is the new, solid-color header for internal pages -->
<header class="header-internal">
    <nav>
        <a href="../index.php" class="nav-logo">Project ALPHA</a>
        <div class="nav-links">
            <a href="../patient/dashboard.php">Dashboard</a>
            <a href="../patient/profile.php">Profile</a>
            <a href="../patient/documents.php">Documents</a>
            <!-- You can add more links here, or use PHP to show different links based on user role -->
            <a href="../logout.php" class="nav-button">Logout</a>
        </div>
    </nav>
</header>

