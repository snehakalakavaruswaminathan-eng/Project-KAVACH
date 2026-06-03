# ICEID Banking Trojan - Full Forensic Investigation Report

**Case Number:** KAVACH-2024-ICEID-002
**Investigation Period:** 2023-07-19 14:12:33 - 14:25:47 UTC
**Analyst:** Senior DFIR Team
**PCAP Source:** Malware-Traffic-Analysis.net / Unit 42 IcedID Quiz
**Classification:** CONFIDENTIAL - Security Incident Report

---

## Executive Summary

### Malware Identification

| Attribute | Value |
|-----------|-------|
| **Malware Family** | ICEID (a.k.a. BokBot, BokBoat) |
| **Malware Type** | Banking Trojan / Loader with AD Reconnaissance Capabilities |
| **Primary Objective** | Credential theft, Active Directory enumeration, DCSync preparation |
| **ATT&CK Initial Access** | T1566 - Phishing (Malicious Link/Attachment) |
| **ATT&CK Execution** | T1204 - User Execution, T1059.001 - PowerShell |
| **ATT&CK C2** | T1071.001 - Web Protocols (HTTP/HTTPS/TCP) |
| **ATT&CK Discovery** | T1087 - Account Discovery, T1018 - Remote System Discovery |
| **ATT&CK Credential Access** | T1558 - Kerberos Tickets, T1003.006 - DCSync (preparation) |
| **Confidence** | VERY HIGH |

### Initial Access Vector

The infection chain began with a phishing email containing either:
1. A malicious URL redirecting through `80.77.24.175/main.php` to `firebasestorage.googleapis.com` hosting `Scan_Inv.zip` (IcedID installer), OR
2. A malicious attachment (`Invoice_07-19-2023.zip`) containing an ISO with LNK/DLL sideloading chain

**Full Attack Chain:**
1. User clicks phishing link or opens attachment
2. HTTP redirect to Google Firebase Storage for Scan_Inv.zip download
3. IcedID loader execution
4. PowerShell HTTP check-in to 623start.site (reports AV status)
5. IcedID payload download from guiatelefonos.com/data/czx.jpg (disguised as JPEG)
6. IcedID C2 beaconing established over custom TCP (port 12432) and HTTPS
7. Post-compromise AD reconnaissance via LDAP, Kerberos, SMB2
8. DRSUAPI interface discovery (DCSync preparation)

### Attacker Objectives

Based on observed network behavior, the attacker's objectives were:

1. **Establish persistent C2 channel** via multiple protocols (HTTP, HTTPS, custom TCP)
2. **Harvest AD information** through authenticated LDAP enumeration
3. **Prepare for credential theft** via DCSync (DRSUAPI interface discovery)
4. **Exfiltrate data** (~592KB observed over C2 channel)
5. **Potential lateral movement** (SMB authentication validation)

### Risk Assessment

| Risk Category | Assessment | Justification |
|---------------|------------|---------------|
| **Confidentiality** | CRITICAL | AD enumeration exposes entire domain structure; DCSync preparation threatens all password hashes |
| **Integrity** | HIGH | Malware execution enables system modification and credential theft |
| **Availability** | MODERATE | No ransomware observed, but domain compromise could enable destructive actions |
| **Compliance** | CRITICAL | Potential breach of all domain user credentials (regulatory reporting required) |
| **Business Impact** | CRITICAL | Domain compromise would allow attacker full control of enterprise infrastructure |

### Scope of Compromise

| Scope Element | Status |
|---------------|--------|
| **Single Workstation** | Confirmed - 10.7.10.47 (DESKTOP-9PEA63H) |
| **Domain Controller** | Targeted - DRSUAPI interface discovery observed (DCSync risk) |
| **Active Directory** | Enumerated - LDAP discovery and Kerberos authentication observed |
| **Data Exfiltration** | Confirmed - ~592KB transferred over C2 |
| **Lateral Movement** | Potential - SMB authentication validated, no confirmed execution |
| **Persistence** | Likely - IcedID standard behavior (registry Run key) |

---

## Environment Overview

### Victim Systems

| System | IP Address | Hostname | Role | Compromised |
|--------|------------|----------|------|-------------|
| DESKTOP-9PEA63H | 10.7.10.47 | DESKTOP-9PEA63H | User Workstation | **YES (Confirmed)** |
| WIN-S3WT6LGQFVX | 10.7.10.9 | WIN-S3WT6LGQFVX | Domain Controller | Targeted (DRSUAPI) |
| Unknown | 10.7.10.1 | Gateway | Firewall | No |
| Unknown | 10.7.10.20 | Fileserver | File Server | Not directly |

### Internal Addressing

| Network | Range | Purpose |
|---------|-------|---------|
| Corporate LAN | 10.7.10.0/24 | Main enterprise network |
| Domain Controller | 10.7.10.9 | AD DS, DNS, Kerberos, LDAP |
| Workstation Segment | 10.7.10.47 | Compromised endpoint |

### External Infrastructure

| Host | IP Address | Role | Classification | Confidence |
|------|------------|------|----------------|------------|
| (Redirector) | 80.77.24.175 | Initial redirector | Malicious | HIGH |
| firebasestorage.googleapis.com | (multiple) | Payload hosting | Legitimate (abused) | HIGH |
| skigimeetroc.com | 192.153.57.223 | IcedID loader C2 | Malicious | HIGH |
| askamoshopsi.com | 104.168.53.18 | IcedID HTTPS C2 | Malicious | HIGH |
| skansnekssky.com | 217.199.121.56 | IcedID HTTPS C2 | Malicious | HIGH |
| 623start.site | (unknown) | PowerShell check-in | Malicious | HIGH |
| guiatelefonos.com | (unknown) | Payload hosting | Malicious | HIGH |
| 194.26.135.119 | 194.26.135.119 | IcedID C2 (port 12432) | Malicious | HIGH |

### Protocol Inventory

| Protocol | Port | Observed Usage | Encrypted | Direction |
|----------|------|----------------|-----------|-----------|
| HTTP | 80 | Initial redirect, PowerShell C2, Loader C2 | No | Outbound |
| HTTPS | 443 | Firebase download, IcedID C2 | Yes | Outbound |
| TCP (custom) | 12432 | IcedID C2 beaconing | Binary protocol | Outbound |
| DNS | 53 | Domain resolution | No | Outbound |
| LDAP | 389 | AD enumeration, Netlogon discovery | No | East-West |
| Kerberos | 88 | Authentication, TGT requests | Yes (encrypted) | East-West |
| SMB2 | 445 | IPC$, DRSUAPI binding | No | East-West |
| NBNS | 137 | Host registration | No | Broadcast |

### JA3/JA3S Fingerprints (IcedID TLS)

| Hash | Type | Source | Destination | Notes |
|------|------|--------|-------------|-------|
| a0e9f5d64349fb13191bc781f81f42e1 | JA3 | 10.7.10.47 | IcedID C2 | Client TLS fingerprint |
| 3b5074b1b5d032e5620f69f9f700ff0e | JA3 | 10.7.10.47 | IcedID C2 | Alternative client fingerprint |
| 452e969c51882628dac65e38aff0f8e5ebee6e6b | X.509 | IcedID C2 | 10.7.10.47 | Self-signed cert hash |

### DNS Behavior

| Observation | Details |
|-------------|---------|
| Legitimate Queries | _ldap._tcp.dc._msdcs.coolweathercoat.com (AD SRV record) |
| Malicious Domains | 623start.site, guiatelefonos.com, skigimeetroc.com, askamoshopsi.com, skansnekssky.com |
| DGA Pattern | Not prominent in this capture (unlike other IcedID samples) |

---

## Chronological Attack Timeline

### Phase 1: Initial Delivery (T+0:00)

| Time | Event | Protocol | Analyst Interpretation |
|------|-------|----------|------------------------|
| 14:12:33 | SMB write of Invoice_07-19-2023.zip | SMB (445) | Email attachment delivered (alternative vector) |
| 14:12:35 | HTTP GET to 80.77.24.175 /main.php | HTTP | Initial redirector access (primary vector) |
| 14:12:36 | HTTP redirect to Firebase URL | HTTP | 302 redirect to legitimate Google infrastructure |
| 14:12:37 | HTTPS GET from firebasestorage.googleapis.com | HTTPS | Download of Scan_Inv.zip (IcedID loader) |

**Attacker Objective:** Deliver IcedID loader to victim workstation while evading URL filters by using legitimate Google Firebase.

### Phase 2: PowerShell Staging (T+0:02)

| Time | Event | Protocol | Analyst Interpretation |
|------|-------|----------|------------------------|
| 14:14:01 | GET /?status=start&av=Windows%20Defender | HTTP (PowerShell UA) | Malware reports AV status to C2 |
| 14:14:02 | GET /?status=install | HTTP (PowerShell UA) | Malware confirms installation |
| 14:14:03 | GET /data/czx.jpg | HTTP | IcedID payload download (disguised as JPEG) |

**Attacker Objective:** Download main IcedID binary using PowerShell to blend in with administrative scripts.

**Key Suspicious Elements:**
- `User-Agent: PowerShell` (not a browser)
- `av=Windows%20Defender` parameter (system fingerprinting)
- `.jpg` extension serving binary executable

### Phase 3: IcedID C2 Beaconing (T+0:03 - T+0:13)

| Time | Event | Protocol | Size | Analyst Interpretation |
|------|-------|----------|------|------------------------|
| 14:14:15 | TCP connection to 194.26.135.119:12432 | TCP | - | C2 channel establishment |
| 14:14:16-14:25:47 | Sustained C2 conversation | TCP | ~592KB | Exfiltration or module download |
| Throughout | Periodic HTTPS to askamoshopsi.com | HTTPS | Varies | Secondary C2 channel |

**Attacker Objective:** Maintain persistent C2 for command delivery and data exfiltration.

### Phase 4: Post-Compromise AD Reconnaissance (Throughout)

| Time | Frame | Event | Protocol | Analyst Interpretation |
|------|-------|-------|----------|------------------------|
| Early | 18 | LDAP Netlogon response | LDAP | DC capabilities discovered |
| Early | 19 | NBNS registration | NBNS | Host announces presence |
| Mid | 189 | LDAP SASL/GSS-SPNEGO bind | LDAP | Authenticated AD bind |
| Mid | 194 | SMB2 Session Setup | SMB2 | SMB authentication to DC |
| Mid | 195 | Kerberos PREAUTH_REQUIRED | Kerberos | TGT request |
| Mid | 91+ | DRSUAPI interface bind | DCE/RPC | **CRITICAL: DCSync preparation** |

**Attacker Objective:** Enumerate Active Directory structure, validate credentials, prepare for DCSync credential theft.

**Weakness Exploited:** Authenticated domain users can enumerate AD by default. Over-privileged workstation account may have replication rights.

---

## Deep Packet Analysis

### HTTP Redirect Chain Analysis

**Initial Request (Packet reference):**
```http
GET /main.php HTTP/1.1
Host: 80.77.24.175
User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0)