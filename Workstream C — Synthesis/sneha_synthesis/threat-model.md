# Threat Model – Meridian FinServe

## Executive Summary

This threat model evaluates Meridian FinServe as an integrated environment consisting of internet-facing web applications, internal authentication infrastructure, enterprise workstations, and backend databases.

The analysis combines findings from:

- Workstream A: Network Forensics Investigation (IcedID Activity Analysis)
- Workstream B: Web Application Security Assessment (DVWA and OWASP Juice Shop)

The assessment identified that the highest organizational risk arises from attack chains that begin with web application vulnerabilities and progress into internal Active Directory infrastructure. While individual vulnerabilities present significant risks, their combined exploitation could result in enterprise-wide compromise.

---

## Scope

The threat model covers:

- Customer-facing web applications
- Employee web applications
- Backend databases
- Active Directory infrastructure
- Authentication services
- File-sharing services
- Enterprise workstations
- Security monitoring systems

---

## Critical Assets

| Asset | Criticality | Business Impact |
|---------|-------------|-----------------|
| Customer Data | High | Privacy breach, regulatory penalties |
| Employee Credentials | High | Unauthorized access |
| Active Directory | Critical | Enterprise compromise |
| Financial Records | Critical | Financial fraud, compliance issues |
| Internal Databases | High | Data manipulation and disclosure |
| Authentication Services | Critical | Identity compromise |

---

## Threat Actors

| Threat Actor | Motivation | Capability |
|--------------|------------|-------------|
| External Attackers | Financial gain | High |
| Cybercriminal Groups | Credential theft | High |
| Malware Operators | Persistence and exfiltration | Medium |
| Insider Threats | Misuse of privileges | Medium |
| Advanced Persistent Threats (APT) | Long-term access | High |

---

## Trust Boundaries

### TB-1: Internet → Web Applications

Users access:

- Customer Portal
- Employee Portal
- Public APIs

#### Threats

- SQL Injection
- Cross-Site Scripting (XSS)
- Session Hijacking
- File Upload Abuse
- Credential Stuffing

---

### TB-2: Web Applications → Database

Applications communicate with backend databases.

#### Threats

- Unauthorized Queries
- Data Manipulation
- Credential Exposure
- Database Enumeration

---

### TB-3: Internal Network → Active Directory

Workstations communicate using:

- LDAP
- Kerberos
- SMB
- DCE/RPC

#### Threats

- Credential Abuse
- Domain Enumeration
- Privilege Escalation
- DCSync Preparation

---

## STRIDE Analysis

| Category | Threat | Impact |
|-----------|---------|---------|
| Spoofing | Credential theft through web compromise | Unauthorized access |
| Tampering | SQL Injection modifying backend data | Data integrity loss |
| Repudiation | Inadequate audit logging | Reduced accountability |
| Information Disclosure | Missing security headers and AD enumeration | Data exposure |
| Denial of Service | Application resource exhaustion | Service disruption |
| Elevation of Privilege | LDAP/Kerberos abuse leading to domain compromise | Full enterprise compromise |

---

## PASTA Summary

### Stage 1 – Business Objectives

Protect:

- Customer financial information
- Authentication systems
- Internal enterprise resources

### Stage 2 – Technical Scope

Included systems:

- Web applications
- Databases
- Active Directory
- Workstations
- Network infrastructure

### Stage 3 – Application Decomposition

#### Key Components

- Web Frontend
- Application Layer
- Database Layer
- Identity Services
- Internal File Services

### Stage 4 – Threat Analysis

#### Identified Threats

- SQL Injection
- Web Shell Deployment
- Credential Theft
- Active Directory Enumeration
- DCSync Activity

### Stage 5 – Vulnerability Analysis

#### Observed Weaknesses

- SQL Injection
- Missing Content Security Policy (CSP)
- Missing HTTP Strict Transport Security (HSTS)
- Weak Authentication Controls
- Excessive Internal Service Exposure

### Stage 6 – Attack Modeling

#### Primary Attack Paths

1. SQL Injection → Credential Exposure → Active Directory Compromise
2. File Upload Abuse → Web Shell → Internal Reconnaissance

### Stage 7 – Risk Analysis

Highest-risk scenarios involve compromise of Active Directory through web application exploitation.

---

## Cross-Surface Threat Scenario 1

### SQL Injection → Active Directory Compromise

#### Attack Flow

1. SQL Injection exploited in employee portal.
2. Database credentials exposed.
3. Credentials reused internally.
4. LDAP authentication performed.
5. Kerberos tickets requested.
6. SMB sessions established.
7. DRSUAPI discovered.
8. DCSync attack attempted.

#### Evidence

##### Workstream B

- SQL Injection vulnerability
- Weak authentication controls

##### Workstream A

- LDAP SASL Bind
- Kerberos PREAUTH_REQUIRED
- SMB Session Setup
- DRSUAPI Discovery

#### Business Impact

- Active Directory compromise
- Credential theft
- Enterprise-wide access
- Potential regulatory violations

---

## Cross-Surface Threat Scenario 2

### Web Shell → Malware Infection → Internal Reconnaissance

#### Attack Flow

1. Vulnerable file upload exploited.
2. Web shell deployed.
3. PowerShell downloader executed.
4. Malware payload installed.
5. Command-and-control communication established.
6. Active Directory reconnaissance initiated.
7. Credential theft preparation observed.

#### Evidence

##### Workstream B

- File upload weaknesses
- Missing security controls

##### Workstream A

- LDAP Netlogon Discovery
- Kerberos Negotiation
- SMB Authentication
- DCE/RPC DRSUAPI Activity

#### Business Impact

- Persistent attacker access
- Internal reconnaissance
- Potential domain compromise
- Increased incident recovery costs

---

## MITRE ATT&CK Mapping

| Technique | ATT&CK ID |
|------------|------------|
| Exploit Public-Facing Application | T1190 |
| Valid Accounts | T1078 |
| Account Discovery | T1087 |
| Domain Trust Discovery | T1482 |
| Remote Services (SMB) | T1021 |
| Credential Dumping | T1003 |
| DCSync | T1003.006 |
| Command and Scripting Interpreter | T1059 |
| Web Shell | T1505.003 |

---

## Abuse Cases

### Abuse Case 1

An attacker exploits SQL Injection to access sensitive customer records without authorization.

### Abuse Case 2

An attacker uploads a malicious web shell and uses it to conduct internal reconnaissance.

### Abuse Case 3

An attacker obtains administrative credentials and performs DCSync operations against Active Directory.

---

## Risk Matrix

| Threat | Likelihood | Impact | Risk Rating |
|----------|------------|---------|-------------|
| SQL Injection → AD Compromise | High | Critical | Critical |
| Web Shell → Malware Infection | High | Critical | Critical |
| Credential Theft | High | High | High |
| Session Hijacking | Medium | High | High |
| Information Disclosure | High | Medium | Medium |

---

## Recommended Controls

| Threat | Recommended Control |
|----------|---------------------|
| SQL Injection | Parameterized Queries and Input Validation |
| Web Shell Deployment | Secure File Upload Validation |
| Credential Theft | Multi-Factor Authentication (MFA) |
| AD Enumeration | Network Segmentation |
| DCSync Activity | Active Directory Monitoring and Alerting |
| Information Disclosure | CSP, HSTS, and Security Headers |

---

## Residual Risk Assessment

| Risk | Residual Rating After Controls |
|---------|-----------------------------|
| SQL Injection | Medium |
| Credential Theft | Medium |
| Web Shell Deployment | Medium |
| DCSync Attack | Low-Medium |
| Information Disclosure | Low |

---

## Threat Prioritization

1. SQL Injection leading to Active Directory compromise
2. Web Shell deployment and malware execution
3. Credential theft and privilege escalation
4. Active Directory reconnaissance
5. Information disclosure vulnerabilities

---

## Assumptions and Limitations

### Assumptions

- Active Directory is the primary identity provider.
- Internal administrative credentials may be reused across systems.
- Web applications have access to backend databases containing sensitive information.
- Network monitoring coverage is incomplete.

### Limitations

- Assessment findings are based on available forensic evidence and application testing results.
- No source code review was conducted.
- Risk ratings are qualitative and based on observed evidence.

---

## Conclusion

The assessment demonstrates that the greatest threat to Meridian FinServe is the convergence of web application vulnerabilities and internal Active Directory exposure. Attackers are unlikely to stop at exploiting a single vulnerability; instead, they will leverage weaknesses across multiple trust boundaries to achieve persistence, privilege escalation, and enterprise-wide compromise.

Reducing this risk requires coordinated improvements across application security, identity protection, network segmentation, and security monitoring. Implementing the recommended controls will significantly decrease the likelihood and impact of a successful attack while improving the organization's overall security posture.
