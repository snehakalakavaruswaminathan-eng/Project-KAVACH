<?php
/*
 * KAVACH WORKSTREAM B — SQL Injection Patch
 * Vulnerability: A03 SQL Injection
 * File: vulnerabilities/sqli/source/low.php
 */

// ❌ VULNERABLE CODE (original)
// $query = "SELECT first_name, last_name FROM users WHERE user_id = '$id';";
// $result = mysqli_query($GLOBALS["___mysqli_ston"], $query);

// ✅ FIXED CODE (parameterized query)
if(isset($_REQUEST['id'])) {
    $id = $_REQUEST['id'];

    // Use prepared statement — no direct string concatenation
    $stmt = $GLOBALS["___mysqli_ston"]->prepare(
        "SELECT first_name, last_name FROM users WHERE user_id = ?"
    );
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()) {
        $first = $row["first_name"];
        $last  = $row["last_name"];
        echo "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
    }
}
?>
