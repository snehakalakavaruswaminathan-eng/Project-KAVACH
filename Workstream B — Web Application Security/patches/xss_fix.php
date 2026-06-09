<?php
/*
 * KAVACH WORKSTREAM B — XSS Patch
 * Vulnerability: A03 XSS Reflected
 * File: vulnerabilities/xss_r/source/low.php
 */

// ❌ VULNERABLE CODE (original)
// echo '<pre>Hello ' . $_GET['name'] . '</pre>';

// ✅ FIXED CODE (output encoding)
if(isset($_GET['name'])) {
    // htmlspecialchars encodes <, >, &, ", ' 
    // ENT_QUOTES handles both single and double quotes
    $name = htmlspecialchars($_GET['name'], ENT_QUOTES, 'UTF-8');
    echo "<pre>Hello {$name}</pre>";
}
?>
