# Workstream C – Defense in Depth Proposal

## Meridian FinServe

This proposal maps directly to findings identified during **Workstream A (Network Forensics Investigation)** and **Workstream B (Web Application Security Assessment)**. The recommended controls follow a Defense-in-Depth strategy, ensuring that failures at one security layer do not result in complete compromise of enterprise systems.

---

# Layer 1 – Identity Security

## Finding
LDAP authentication and Kerberos activity were observed across the environment, highlighting the importance of protecting Active Directory and privileged identities.

### Control: Multi-Factor Authentication (MFA) for Privileged Accounts

| Attribute | Details |
|------------|------------|
| Effort | Medium (M) |
| Trade-off | Additional login friction for administrators |

**Recommendation:**  
Require MFA for all privileged and administrative accounts to reduce the risk of credential theft, password spraying, and unauthorized administrative access.

### Control: Tiered Administrative Accounts

| Attribute | Details |
|------------|------------|
| Effort | Medium (M) |
| Trade-off | Increased account management overhead |

**Recommendation:**  
Separate standard user accounts from administrative accounts to limit privilege escalation opportunities and reduce exposure of highly privileged credentials.

---

# Layer 2 – Perimeter Security

## Finding
The web application was found to be missing the HTTP Strict Transport Security (HSTS) header.

### Control: Enforce HTTPS with HSTS

| Attribute | Details |
|------------|------------|
| Effort | Small (S) |
| Trade-off | Legacy HTTP clients may fail |

**Recommendation:**  
Implement HSTS to force secure HTTPS communications and mitigate protocol downgrade attacks.

### Control: Web Application Firewall (WAF)

| Attribute | Details |
|------------|------------|
| Effort | Medium (M) |
| Trade-off | Potential false positives |

**Recommendation:**  
Deploy a WAF to detect and block common web attacks, including SQL injection, cross-site scripting (XSS), and malicious request patterns.

---

# Layer 3 – Network Segmentation

## Finding
SMB and LDAP services were reachable from workstation systems, increasing lateral movement opportunities.

### Control: Restrict SMB Access to Approved Hosts

| Attribute | Details |
|------------|------------|
| Effort | Medium (M) |
| Trade-off | Additional firewall administration |

**Recommendation:**  
Limit SMB communications to approved hosts and administrative systems through network access controls and firewall policies.

### Control: Segment Domain Controllers

| Attribute | Details |
|------------|------------|
| Effort | Large (L) |
| Trade-off | Network redesign effort |

**Recommendation:**  
Place Domain Controllers in dedicated management networks protected by strict access control policies.

---

# Layer 4 – Application Security

## Finding
A SQL Injection vulnerability was identified within the DVWA environment.

### Control: Parameterized Queries

| Attribute | Details |
|------------|------------|
| Effort | Medium (M) |
| Trade-off | Developer remediation effort |

**Recommendation:**  
Implement parameterized queries and secure coding practices to eliminate SQL injection attack vectors.

## Finding
The application was missing a Content Security Policy (CSP) header.

### Control: Content Security Policy (CSP)

| Attribute | Details |
|------------|------------|
| Effort | Small (S) |
| Trade-off | May require frontend compatibility testing |

**Recommendation:**  
Deploy a CSP header to reduce exposure to cross-site scripting attacks and unauthorized script execution.

## Finding
Anti-clickjacking protections were absent.

### Control: X-Frame-Options DENY

| Attribute | Details |
|------------|------------|
| Effort | Small (S) |
| Trade-off | Restricts embedding functionality |

**Recommendation:**  
Implement the `X-Frame-Options: DENY` header to prevent clickjacking attacks.

---

# Layer 5 – Data Protection

## Finding
Potential credential exposure was identified during the assessment.

### Control: Encrypt Sensitive Data

| Attribute | Details |
|------------|------------|
| Effort | Medium (M) |
| Trade-off | Key management overhead |

**Recommendation:**  
Encrypt sensitive information both at rest and in transit using industry-standard cryptographic controls.

### Control: Password Vaulting

| Attribute | Details |
|------------|------------|
| Effort | Medium (M) |
| Trade-off | User adoption and training requirements |

**Recommendation:**  
Store privileged credentials in a centralized password vault to improve access control and auditing.

---

# Layer 6 – Observability and Monitoring

## Finding
Cross-protocol activity required significant manual investigation during forensic analysis.

### Control: Centralized Security Information and Event Management (SIEM)

| Attribute | Details |
|------------|------------|
| Effort | Large (L) |
| Trade-off | Licensing and operational costs |

**Recommendation:**  
Implement centralized log collection and correlation to improve threat detection and investigation efficiency.

### Control: Network Detection Rules

| Attribute | Details |
|------------|------------|
| Effort | Medium (M) |
| Trade-off | Ongoing maintenance requirements |

**Recommendation:**  
Develop custom detection rules for SMB abuse, LDAP enumeration, Kerberos anomalies, and web application attack indicators.

---

# Layer 7 – Incident Response

## Finding
Indicators consistent with potential DCSync preparation activity were identified.

### Control: DCSync Alerting

| Attribute | Details |
|------------|------------|
| Effort | Small (S) |
| Trade-off | Alert tuning may be required |

**Recommendation:**  
Deploy monitoring and alerting for Directory Replication Service (DRS) requests associated with DCSync techniques.

### Control: Malware Containment Playbooks

| Attribute | Details |
|------------|------------|
| Effort | Medium (M) |
| Trade-off | Staff training and maintenance effort |

**Recommendation:**  
Develop and regularly test incident response playbooks covering malware infections, credential compromise, and Active Directory attacks.

---

# Prioritized Recommendations

| Priority | Control | Effort |
|-----------|-----------|-----------|
| 1 | SQL Injection Remediation | M |
| 2 | Content Security Policy (CSP) Implementation | S |
| 3 | HSTS Deployment | S |
| 4 | Multi-Factor Authentication for Administrators | M |
| 5 | SMB Network Segmentation | M |
| 6 | Centralized SIEM Monitoring | L |

---

# Conclusion

The assessment identified weaknesses across both the web application and enterprise network environments. The most significant risk arises from the potential for attackers to exploit application-layer vulnerabilities and subsequently pivot into the internal network environment.

Implementing the recommended Defense-in-Depth controls will reduce the likelihood of compromise, improve detection capabilities, and strengthen organizational resilience against both external and internal threats. The highest-value improvements are achieved by combining **web application remediation**, **identity protection**, and **Active Directory monitoring**, thereby preventing attacks from propagating across application and network security boundaries.
