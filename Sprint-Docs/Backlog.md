# Sprint 1 Backlog
## To Do
- [ ] Research and understand the project Kaavach requirements and deliverables. Created the three workstreams: Workstream - A — (Network Forensics), Workstream B — Web Application Security and Workstream C — Synthesis. Conducted comprehensive research across the three workstreams. 
    -  Lukesh Singh - 6-7 hours [21 May 2026] 
- [ ] Gather the requirements for the Workstream - A — (Network Forensics) training and shared the resources for Wireshark PCAP requirements to the team and overview of Malware traffic analysis website and Wireshark setup. [[21 May 2026]
- [ ] Begin Workstream C (Synthesis) by correlating findings from Network Forensics and Web Application Security assessments into a unified security assessment report.
Sneha – Estimated 4 hours [Week 2]
 Prepare final attack flow diagrams and architecture visualizations for Project Kavach deliverables.
Sneha – Estimated 3 hours [Week 2]

## In Progress
- [ ] Learning and gathering more information on the PCAP zip files discussed and checking the other requirements for the deliverables.
     - Lukesh Singh - 2 hours - Started: [22 May 2026]   
- [ ] Research on Wireshark tutorials for different type of configurations for analysing PCAP files by adding custom columns on the wireshark and using different filters for checking malicious web traffic is in progress. Collecting information for malicious signals in the PCAP files, currently aggregating data from different scenarios to identify malicious host and HTTP traffic using best practices. It's in progress.
     - Lukesh Singh - 2hours [22 May 2026]
- [ ] Performing detailed analysis of the selected IcedID malware PCAP dataset to identify Active Directory enumeration, LDAP authentication, Kerberos ticketing, SMB session establishment, DCE/RPC communications, and potential DCSync preparation activities.
     - Sneha – 4 hours – Started: [24 May 2026]
- [ ] Researching and validating Wireshark filters for identifying malware beaconing, credential abuse, Active Directory reconnaissance, lateral movement indicators, and command-and-control communications.
      - Sneha – 3 hours – Started: [24 May 2026]
- [ ] Developing forensic investigation templates including IOC extraction methodology, timeline reconstruction, MITRE ATT&CK mapping, and evidence correlation procedures for malware traffic analysis.
      - Sneha – 2 hours – Started: [25 May 2026]
     
## Done
- [x] Research is done for the Wireshark PCAP files and some PCAP files are chosen for our Project. 
    - Lukesh Singh - 2 hours - [22 May 2026]
- [x] Started to commit the changes in the repository. Using different AI tools for any issues related to kali linux. Using kali linux environment to analyze the PCAP files, since these files contains actual malware in them.
    - Lukesh Singh - 2 hours - Completed: [22 May 2026]
- [x] Connected with the team for any feedback on the pcap files chosen and any issues realted to wireshark, discussed our next steps for the Project.
    - Lukesh Singh - Completed [23 May 2026]
- [x] Researched the Project Kavach Workstream B requirements and established a web application security assessment methodology using OWASP Juice Shop and DVWA as target applications.
    - Sneha – 5 hours – Completed: [24 May 2026]
- [x] Installed and configured Docker-based lab environments for OWASP Juice Shop and DVWA in Kali Linux to support vulnerability assessment activities.
    - Sneha – 3 hours – Completed: [25 May 2026]
- [x] Performed OWASP ZAP vulnerability scanning against OWASP Juice Shop and documented identified issues including CSP Header Missing, Missing Anti-Clickjacking Header, Missing HSTS Header, X-Content-Type-Options Missing, and Information Disclosure findings.
    - Sneha – 4 hours – Completed: [26 May 2026]
- [x] Conducted vulnerability analysis of OWASP Juice Shop findings and mapped observations to OWASP Top 10 categories, CWE references, security impacts, and remediation recommendations.
    - Sneha – 4 hours – Completed: [26 May 2026]
- [x] Performed OWASP ZAP assessment against DVWA and analyzed vulnerabilities including SQL Injection, Reflected XSS, Stored XSS, Command Injection, CSRF, File Inclusion, and Security Misconfiguration weaknesses.
    - Sneha – 5 hours – Completed: [27 May 2026]
- [x] Created detailed vulnerability reports containing attack descriptions, affected endpoints, request/response evidence, vulnerable headers, payload examples, root cause analysis, severity ratings, and remediation guidance.
    - Sneha – 4 hours – Completed: [27 May 2026]
- [x] Conducted forensic analysis of LDAP, Kerberos, SMB2, NBNS, and DCE/RPC traffic from the IcedID malware PCAP dataset to identify authentication workflows, Active Directory discovery activities, and attacker reconnaissance patterns.
    - Sneha – 6 hours – Completed: [28 May 2026]
- [x] Developed IOC tables and evidence matrices containing IP addresses, domains, URLs, hostnames, protocols, and Wireshark filters used during malware investigation activities.
    - Sneha – 3 hours – Completed: [28 May 2026]
- [x] Created GitHub-compatible Markdown, Mermaid architecture diagrams, CSV IOC datasets, and technical documentation templates for Project Kavach deliverables.
Sneha – 3 hours – Completed: [29 May 2026]
- [x] Evaluated the use of Semgrep for Static Application Security Testing (SAST) and researched secure code review approaches for OWASP Juice Shop and DVWA source code analysis.
    - Sneha – 2 hours – Completed: [29 May 2026]
- [x] Collaborated with team members to review PCAP analysis findings, validate vulnerability assessment outputs, refine reporting formats, and align deliverables across Workstreams A, B, and C.
- Sneha – 2 hours – Completed: [30 May 2026]
