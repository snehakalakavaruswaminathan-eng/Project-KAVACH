# Static Application Security Testing (SAST) Report
**Tool:** Semgrep Community Edition  
**Rule Configuration:** `auto` (Semgrep Registry)  
**Targets:** OWASP Juice Shop & Damn Vulnerable Web Application (DVWA)

---

## 1. Executive Summary

| Target Application | Primary Language | Total Findings | Critical/High Risks |
| :--- | :--- | :--- | :--- |
| **OWASP Juice Shop** | TypeScript / JavaScript | ~42 | Injection, Insecure Crypto, Broken Auth |
| **DVWA** | PHP | ~35 | SQLi, Command Injection, XSS, CSRF |

---

## 2. OWASP Juice Shop Scan Findings (Node.js / Express / TypeScript)

### 🚨 [HIGH] Broken Access Control & Hardcoded Secrets
*   **Rule ID:** `javascript.express.security.audit.xss` / `generic.secrets`
*   **File:** `juice-shop/routes/login.ts`
*   **Description:** Found hardcoded cryptographic keys and client-side secrets utilized in JWT verification routines. 
*   **Impact:** Attackers can potentially forge json web tokens (JWT) to elevate privileges or bypass the authentication barrier entirely.

### 🛑 [CRITICAL] SQL Injection (SQLi) via Dynamic Queries
*   **Rule ID:** `javascript.sequelize.security.audit.sequelize-injection`
*   **File:** `juice-shop/routes/search.ts`
*   **Description:** Raw user input is concatenated straight into Sequelize database query strings without proper parameterization.
*   **Remediation:** Utilize bind variables or abstraction queries native to Sequelize (`where: { string }`) instead of raw text interpolation.

### ⚠️ [WARNING] Insecure Cross-Origin Resource Sharing (CORS)
*   **Rule ID:** `javascript.express.security.audit.cors-allow-all`
*   **File:** `juice-shop/server.ts`
*   **Description:** CORS headers are configured to allow wildcard requests (`Access-Control-Allow-Origin: *`).
*   **Remediation:** Explicitly define white-listed origin domains rather than exposing backend endpoints globally.

---

## 3. DVWA Scan Findings (PHP)

### 🛑 [CRITICAL] OS Command Injection
*   **Rule ID:** `php.lang.security.exec-use`
*   **File:** `DVWA/vulnerabilities/exec/source/low.php`
*   **Description:** User input delivered via the IP address text field is directly passed to the `shell_exec()` or `passthru()` system utilities without sanitation.
*   **Impact:** Complete remote code execution (RCE) on the underlying host operating system.

### 🛑 [CRITICAL] SQL Injection inside PHP MySQL Queries
*   **Rule ID:** `php.lang.security.injection.mysql-injection`
*   **File:** `DVWA/vulnerabilities/sqli/source/low.php`
*   **Description:** The variable `$id` is integrated unfiltered into the SQL command (`SELECT first_name FROM users WHERE user_id = '$id'`).
*   **Remediation:** Refactor code to use PHP Data Objects (PDO) with strictly enforced prepared statements.

### 🚨 [HIGH] Cross-Site Scripting (XSS) - Reflected & Stored
*   **Rule ID:** `php.lang.security.audit.xss.echo-user-input`
*   **File:** `DVWA/vulnerabilities/xss_r/source/low.php`
*   **Description:** User-controlled HTTP context is mirrored straight back out to the document stream using an unsafe `echo` or `print` statement.
*   **Remediation:** Enwrap output elements with `htmlspecialchars()` to sanitize active HTML characters before printing to screen.

---

## 4. Scan Summary Stats
*   **Scan status:** Completed successfully
*   **Language Parsers Triggered:** `javascript`, `typescript`, `php`
*   **Remediation rate recommendation:** High priority target remediations should prioritize fixing the dynamic query parameters inside the `low` and `medium` directories of DVWA first to mitigate complete RCE exposures.

EOF
