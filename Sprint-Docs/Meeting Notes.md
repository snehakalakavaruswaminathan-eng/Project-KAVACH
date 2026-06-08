# Sprint Planning & Team Coordination

## Project: Kavach

**Duration:** 22 May 2026 – 08 June 2026

### Team Members & Roles

| Member          | Role                     | Responsibilities                                                                                    |
| --------------- | ------------------------ | --------------------------------------------------------------------------------------------------- |
| Lukesh Singh    | Scrum Master             | Sprint planning, task tracking, team coordination, backlog management                               |
| Bharath Vinayak | Research Analyst         | Threat research, malware intelligence gathering, technical validation               |
| Sneha           | Research Analyst         | Pcap selection, Network forensics, PCAP analysis, web application security assessment, vulnerability research       |
| Sumit Patil     | Documentation Specialist | Report preparation, evidence documentation, GitHub repository management, deliverable consolidation |

---

# Sprint Planning Meeting

### Day 1 – 24 May 2026

**Attendees:** Bharath, Lukesh, Sneha, Sumit
**Duration:** 3 Hours

### Objectives

* Understand Project Kavach requirements and expected deliverables.
* Review project statement and evaluation criteria.
* Discuss workstreams and assign responsibilities.
* Define analysis methodology and reporting structure.

### Decisions Made

#### Workstream Allocation

**Workstream A – Network Forensics**

* Malware traffic analysis
* PCAP investigation
* IOC extraction
* Attack chain reconstruction
* Wireshark-based analysis

**Assigned:** Lukesh, Sneha, Bharath

---

**Workstream B – Web Application Security**

* OWASP Juice Shop assessment
* DVWA assessment
* OWASP ZAP analysis
* Vulnerability validation
* Evidence collection

**Assigned:** Sumit, Sneha, Lukesh

---

**Workstream C – Synthesis & Reporting**

* Consolidation of findings
* Final report preparation
* Architecture diagrams
* GitHub documentation

**Assigned:** Sumit, Bharath

---

#### Technical Decisions

* Use Kali Linux as the primary analysis environment.
* Use Wireshark for PCAP investigation.
* Use Malware-Traffic-Analysis.net datasets for malware analysis.
* Use OWASP Juice Shop and DVWA for web application security assessment.
* Use OWASP ZAP for dynamic vulnerability scanning.
* Use Docker containers to host vulnerable applications.
* Maintain all findings in GitHub repository using Markdown format.

### Action Items

| Task                             | Owner   |
| -------------------------------- | ------- |
| Research malware PCAP datasets   | Bharath |
| Configure Wireshark environment and do analysis  | Sneha   |
| Setup DVWA and Juice Shop        | Sneha   |
| Create repository structure      | Lukesh   |
| Sprint tracking and coordination | Lukesh  |

---

# Sprint Review Meeting 1

### Day 2 – 30 May 2026

**Attendees:** Bharath, Lukesh, Sneha, Sumit
**Duration:** 4 Hours

### Agenda

* Review progress across workstreams.
* Discuss technical challenges.
* Validate preliminary findings.
* Refine reporting templates.

### Workstream Updates

#### Workstream A – Network Forensics

**Completed**

* Selection of IcedID malware PCAP dataset.
* Initial packet analysis.
* Identification of:

  * LDAP traffic
  * Kerberos authentication
  * SMB session setup
  * NBNS registrations
  * DCE/RPC communications

**In Progress**

* IOC extraction.
* Timeline reconstruction.
* MITRE ATT&CK mapping.

---

#### Workstream B – Web Application Security

**Completed**

* OWASP Juice Shop deployment using Docker.
* DVWA deployment using Docker.
* OWASP ZAP scanning completed.

**Key Findings**

* Missing CSP Header
* Missing HSTS Header
* Missing Anti-Clickjacking Header
* Missing X-Content-Type-Options Header
* Information Disclosure
* Security Misconfiguration

**Challenges Discussed**

* False positives from automated scans.
* Identifying supporting request/response evidence.
* Mapping findings to OWASP Top 10 categories.

---

#### Workstream C – Documentation

**Completed**

* Initial report templates.
* Markdown formatting guidelines.
* GitHub repository structure.

---

### Decisions Made

* Include packet-level evidence for all malware observations.
* Include request/response evidence for all web vulnerabilities.
* Create IOC tables with corresponding Wireshark filters.
* Prepare architecture diagrams for before/after attack scenarios.
* Standardize severity ratings across all findings.

### Action Items

| Task                                   | Owner   |
| -------------------------------------- | ------- |
| Complete forensic investigation report | Sneha   |
| Validate malware findings              | Bharath |
| Prepare architecture diagrams          | Sumit   |
| Review backlog and sprint progress     | Lukesh  |

---

# Final Review & Documentation Meeting

### Day 3 – 03 June 2026

**Attendees:** Bharath, Lukesh, Sneha, Sumit
**Duration:** 3 Hours

### Agenda

* Final review of technical findings.
* Verify deliverables.
* Consolidate documentation.
* Prepare repository submission.

### Workstream A Summary

#### Completed Activities

* LDAP authentication analysis.
* Kerberos ticketing analysis.
* SMB session establishment analysis.
* DCE/RPC DRSUAPI investigation.
* Active Directory reconnaissance identification.
* IOC generation.
* Wireshark filter validation.
* Attack timeline reconstruction.

---

### Workstream B Summary

#### Completed Activities

* OWASP Juice Shop security assessment.
* DVWA vulnerability assessment.
* OWASP Top 10 mapping.
* Request/response evidence collection.
* Root cause analysis.
* Remediation recommendations.
* Detailed technical reporting.

---

### Workstream C Summary

#### Completed Activities

* Report consolidation.
* GitHub Markdown conversion.
* IOC documentation.
* Mermaid diagrams.
* Sprint documentation.
* Repository organization.

---

### Scrum Master Review

#### Sprint Achievements

* Successfully completed all planned workstreams.
* Completed malware traffic investigation.
* Completed vulnerable web application assessment.
* Produced consolidated project deliverables.
* Maintained collaboration and sprint tracking throughout the project lifecycle.

### Lessons Learned

* Importance of validating automated security findings.
* Importance of packet-level evidence in malware investigations.
* Benefits of combining network forensics with application security assessments.
* Importance of documentation and reproducibility in cybersecurity projects.

### Sprint Closure Status

| Workstream                              | Status    |
| --------------------------------------- | --------- |
| Workstream A – Network Forensics        | Completed |
| Workstream B – Web Application Security | Completed |
| Workstream C – Synthesis & Reporting    | On going |

### Project Status

**Overall Status:**  Completed

**Project Duration:** 22 May 2026 – 08 June 2026
