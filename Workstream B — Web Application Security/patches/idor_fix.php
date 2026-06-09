<?php
/*
 * KAVACH WORKSTREAM B — IDOR Patch
 * Vulnerability: A01 Broken Access Control (IDOR)
 */

// ❌ VULNERABLE CODE (original)
// $user_id = $_GET['id'];
// $query = "SELECT * FROM users WHERE user_id = '$user_id'";

// ✅ FIXED CODE (session-based access control)
session_start();

if(!isset($_SESSION['user_id'])) {
    die("Unauthorized: Please login first.");
}

// Force the query to use the logged-in user's ID only
// User cannot supply their own ID via URL parameter
$user_id = $_SESSION['user_id'];

$stmt = $GLOBALS["___mysqli_ston"]->prepare(
    "SELECT * FROM users WHERE user_id = ?"
);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
