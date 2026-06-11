# Joint Threat Model (Workstream C.1) — Meridian FinServe

## 1. System Scope & Context
This threat model establishes a single, integrated architectural view of Meridian FinServe, mapping how perimeter vulnerabilities cross-pollinate into internal domain infrastructure risks. The system assessment unifies:

* **Customer Web Applications (External Surface):** OWASP Juice Shop, Damn Vulnerable Web Application (DVWA), Customer Portal APIs, and associated web frontend/backend authentication layers.
* **Internal Enterprise Network (Internal Surface):** Windows Active Directory environment, including Domain Controllers, corporate user workstations, and underlying administrative transport protocols (LDAP, Kerberos, SMB, DCE/RPC).

### System Architecture & Trust Boundary Flow
```mermaid
graph TD
    User([Internet Users / Malicious Actors]) -->|Public HTTP/HTTPS| Web[Web Applications: Juice Shop / DVWA]
    
    subgraph DMZ_Trust_Boundary [External Web Layer]
        Web -->|Backend Database Queries| DB[(Application Database)]
    end
    
    subgraph Corporate_Network_Boundary [Internal Active Directory Infrastructure]
        Workstation[User Workstations: DESKTOP-9PEA63H] -->|LDAP SASL Bind / NBNS / SMB2| DC[Domain Controller: WIN-S3WT6LGQFVX]
        Workstation -->|Kerberos Exchange| Krb[Kerberos TGT Provider]
        Workstation -->|DRSUAPI RPC Bind| DC
    end


##**Cross surface threat chains
###Chain 1: Perimeter Web Compromise to Internal Domain Takeover
**graph TD
    Step1[Step 1: Attacker executes SQLi injection against DVWA] --> Step2[Step 2: Backend application leaks cleartext or hashed domain credentials]
    Step2 --> Step3[Step 3: Stolen credentials reused to gain an internal workstation foothold]
    Step3 --> Step4[Step 4: Host executes authenticated LDAP SASL Bind discovery]
    Step4 --> Step5[Step 5: Host issues a Kerberos TGT request validating pre-auth setup]
    Step5 --> Step6[Step 6: High-privilege SMB2 Session Setup established with Domain Controller]
    Step6 --> Step7[Step 7: Unauthorized DRSUAPI interface discovery bind executed]
    Step7 --> Outcome1([Critical Outcome: DCSync Domain Compromise / NTDS.dit Hash Extraction])

    classDef web fill:#1f77b4,stroke:#114b75,stroke-width:1px,color:#fff;
    classDef network fill:#ff7f0e,stroke:#b85a06,stroke-width:1px,color:#fff;
    classDef target fill:#d62728,stroke:#911113,stroke-width:2px,color:#fff;
    
    class Step1,Step2 web;
    class Step3,Step4,Step5,Step6,Step7 network;
    class Outcome1 target;

###Chain 2: Client-Side Flaw to Endpoint Compromise & Beaconing
graph TD
    StepB1[Step 1: Missing Content Security Policy CSP header on Web App] --> StepB2[Step 2: Attacker injects malicious persistent JavaScript into application]
    StepB2 --> StepB3[Step 3: Script executes automated client download of Scan_Inv.zip payload]
    StepB3 --> Step4B[Step 4: Employee opens archive, dropping malware on local workstation]
    Step4B --> Step5B[Step 5: Malware initializes out-of-band C2 beaconing over ports 80/443/12432]
    Step5B --> Step6B[Step 6: Workstation triggers local Active Directory network enumeration]
    Step6B --> Outcome2([Critical Outcome: Local Host Subverted / Network Profiling Complete])

    classDef web fill:#1f77b4,stroke:#114b75,stroke-width:1px,color:#fff;
    classDef network fill:#ff7f0e,stroke:#b85a06,stroke-width:1px,color:#fff;
    classDef target fill:#d62728,stroke:#911113,stroke-width:2px,color:#fff;
    
    class StepB1,StepB2,StepB3 web;
    class Step4B,Step5B,Step6B network;
    class Outcome2 target;


## 2. Comprehensive STRIDE Threat Matrix

The matrix below provides a systematic evaluation of security defects identified across the integrated Meridian FinServe environment using the STRIDE methodology.

| STRIDE Category | ID | Threat Scenario | Impacted Component(s) | Observed Forensic / Technical Evidence | Risk Rating |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **S** — Spoofing | **S1** | Actor abuses input validation vulnerabilities on public-facing application endpoints to bypass login boundaries and hijack privileged database/domain context. | • DVWA Login Interface<br>• Backend Database Engine | • Unsanitized SQL injection vectors discovered within web portal query paths.<br>• Lack of input parameterization or prepared statement execution. | 🔴 **Critical** |
| **S** — Spoofing | **S2** | Actor executes unauthorized local network sweeps using host impersonation techniques to intercept internal business traffic. | • Local Workstation Subnets<br>• Network Broadcast Domain | • Active NetBIOS Name Service (NBNS) and LLMNR broadcast query strings identified in PCAP data.<br>• Missing secure DNS verification or network host-isolation. | 🟡 **High** |
| **T** — Tampering | **T1** | Actor injects arbitrary SQL syntax to manipulate internal database structural parameters, modify ledger tables, or forge financial balances. | • Customer FinServe Database<br>• Core Transaction Ledgers | • Active exploitation of unvalidated parameter vectors confirmed in Web Workstream B triage. | 🔴 **Critical** |
| **T** — Tampering | **T2** | Actor drops or switches client-side execution binaries on employee endpoints via drive-by downloads or unvalidated scripts. | • Enduser Filesystem<br>• Workstation Workspace | • Automated distribution of malicious archive files (`Scan_Inv.zip` / `Invoice_07-19-2023.zip`) hosted via external redirectors. | 🔴 **Critical** |
| **R** — Repudiation | **R1** | Actor conducts system-wide configuration modifications or lateral movement sessions without triggering centralized telemetry logs. | • Active Directory Audit Engine<br>• Web Service Event Pipes | • Complete absence of continuous host-activity monitoring, append-only security logs, or centralized SIEM ingestion rules. | 🟠 **Medium** |
| **I** — Info Disclosure | **I1** | Sensitive runtime parameters, access tokens, or session identifiers are exposed to client-side injection due to loose header defenses. | • OWASP Juice Shop Frontend<br>• Browser DOM Context | • Complete absence of basic security response headers: `Content-Security-Policy` (CSP), `Strict-Transport-Security` (HSTS). | 🟡 **High** |
| **I** — Info Disclosure | **I2** | Internal domain composition data, naming schemas, and domain controller platform attributes are leaked via unauthenticated queries. | • Internal Network Subnet<br>• LDAP Directory Catalog | • Netlogon responses and directory metadata details transmitted in cleartext format across the internal subnet. | 🟡 **High** |
| **D** — Denial of Service | **D1** | Resource exhaustion loops target application ingestion APIs or infrastructure authentication handles, driving service downtime. | • Web Portal API Gateway<br>• Internal Domain Services | • Unthrottled request limits and missing connection rate-limiting boundaries across public service gateways. | 🟠 **Medium** |
| **E** — Elevation of Privilege | **E1** | Actor leverages stolen corporate account credentials to query domain resources, check pre-auth criteria, and acquire domain control. | • Active Directory Identity Vault<br>• `Internal_Domain_Controller` | • Authenticated `LDAP SASL Bind` activities, Kerberos pre-auth queries, and `SMB2 Session Setup` captured on non-admin hosts. | 🔴 **Critical** |
| **E** — Elevation of Privilege | **E2** | Actor invokes administrative system replication parameters over remote RPC pipelines to extract the complete user credential archive. | • DRSUAPI RPC Interface<br>• Active Directory NTDS DB | • Diagnostic packet logs capturing a highly anomalous `DRSUAPI Interface Bind` executed from workstation host `DESKTOP-9PEA63H`. | 🔴 **Critical** |


LIKELIHOOD 
     ▲
High │ [I1] Session Hijack      [S1] SQL Injection
     │ [I2] Domain Leak         [T2] Malware Drop / [E1] Credential Abuse
     │
 Med │ [D1] Service DoS         [T1] Ledger Tampering
     │                          [R1] Missing Audit Logging
     │
 Low │                          [E2] DRSUAPI Abuse / DCSync
     │
     └────────────────────────────────────────────────────────►
                 Medium            High             Critical
                                 IMPACT
# Vulnerability & Threat Prioritization Registry — Project KAVACH

This registry serves as the authoritative risk prioritization log for the **Meridian FinServe** security assessment (**Workstream C.3**). Threats are categorized and ranked using a combined assessment of exploitation feasibility, detection complexity, and environmental blast radius across both external web application and internal Active Directory surfaces.

---
