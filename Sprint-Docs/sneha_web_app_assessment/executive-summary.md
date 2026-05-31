# Executive Summary

Assessment Target: OWASP Juice Shop
Tool: OWASP ZAP 2.17.0
Assessment Type: Dynamic Application Security Testing (DAST)

The assessment identified one High severity vulnerability (SQL Injection), multiple Medium severity security misconfigurations, and several Low severity information disclosure issues.

The most critical risk is SQL Injection, which may allow attackers to bypass authentication, extract database records, manipulate application data, and potentially compromise customer information.

Additional weaknesses involving CSP, CORS, session handling, clickjacking protection, and security headers increase the attack surface and facilitate exploitation.
