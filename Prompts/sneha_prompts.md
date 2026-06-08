# 1. Network Forensics 
You are an Active Directory forensic investigator. Given a PCAP containing enterprise Windows traffic, provide Wireshark filters that can be used to investigate following vulnerabilities:
LDAP enumeration
Kerberos ticket activity
NTLM authentication
SMB authentication
SMB file transfer
DCSync attempts
Netlogon discovery
Domain controller communication
Password spraying indicators
Privilege escalation activity
Lateral movement indicators

For each filter provide:

Purpose
Expected packet contents
Suspicious values to look for
Example analyst observations
Relevance to malware investigations such as ICEID

# 2. Web app assessment. 
You are a Senior Web Application Penetration Tester, Application Security Engineer, Threat Researcher, and Security Assessment Mentor. 
I will use OWASP ZAP, Wireshark, and browser developer tools to perform vulnerability assessment and evidence collection from OWASP Juice Shop and DVWA locally using Docker applications.

Your task is to guide me step-by-step from beginner to analyst level while ensuring I understand:

1. The cybersecurity concepts behind each vulnerability.
2. How attackers discover and exploit the vulnerability.
3. What evidence should be collected.
4. How to validate findings manually and using tools.
5. How to document findings professionally.

# 3. Provide wireshark filters to identify:
* Login requests
* Session cookies
* Authentication tokens
* SQL injection attempts
* XSS payloads
* File uploads
* Command injection payloads
* Sensitive data exposure
* Missing security headers

For each filter explain:

* Why it is useful
* What suspicious values look like
* Expected observations

# 4. Help me generate following report with the attached vulnerailty assessment reports and evidence screenshots:

* Executive Summary
* Vulnerability Assessment Report
* Evidence Log
* IOC Table
* Risk Matrix
* OWASP Top 10 Mapping
* CVSS Scoring
* Remediation Plan

# 5. Report Formatting
You are a cybersecurity technical writer. Convert the investigation findings into a professional project structure:
network/
├── triage-notes.md
├── hypotheses.md
├── iocs.csv
├── architecture/
│   ├── before.mmd
│   └── after.mmd
└── report.md

Requirements:

* GitHub-compatible Markdown
* Mermaid architecture diagrams
* IOC tables
* Wireshark filter references
* MITRE ATT&CK mapping
* Detection engineering recommendations
* Executive summary


# 6. SAST report:
You are a Senior Application Security Engineer and Semgrep expert.

I am using Kali Linux and working on a Project. Guide me step-by-step to install, configure, and use Semgrep for Static Application Security Testing (SAST) on OWASP Juice Shop and DVWA.
Include:
1. Installation commands and dependency setup on Kali Linux.
2. How to clone and analyze the source code of Juice Shop and DVWA.
3. Semgrep commands for OWASP Top 10, JavaScript, Node.js, and PHP security scans.
4. How to generate HTML, JSON, and SARIF reports.
5. How to interpret findings with CWE and OWASP mappings.
6. How to identify SQL Injection, XSS, Command Injection, Authentication flaws, Sensitive Data Exposure, and Security Misconfigurations.
7. How to create custom Semgrep rules.
8. Common issues and troubleshooting.
9. How to document findings as evidence for a security assessment report.

Explain every command, output, vulnerability, and remediation in a beginner-friendly but technically detailed manner.
* Evidence references
* Chronological attack timeline
Ensure all conclusions are traceable to observed evidence and suitable for academic project submission.
