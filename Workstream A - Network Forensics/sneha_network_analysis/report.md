# Active Directory Network Forensic Evidence Report
# Executive Summary

The provided packet evidence demonstrates authenticated interaction between workstation 10.7.10.47 and Active Directory infrastructure hosted on 10.7.10.9. Observed traffic includes LDAP authentication, Kerberos negotiation, SMB2 session setup, and NBNS workstation registration. The traffic pattern is consistent with post-compromise enterprise authentication activity associated with malware such as ICEID.

**Case Number:** KAVACH-2024-ICEID-002
**Investigation Period:** 2023-07-19 14:12:33 - 14:25:47 UTC
**Analyst:** Senior DFIR Team
**PCAP Source:** Malware-Traffic-Analysis.net / Unit 42 IcedID Quiz
**Classification:** CONFIDENTIAL - Security Incident Report

# Environment Overview

| Component                  | Value                               |
| -------------------------- | ----------------------------------- |
| Workstation IP             | 10.7.10.47                          |
| Workstation Hostname       | DESKTOP-9PEA63H                     |
| Domain Controller IP       | 10.7.10.9                           |
| Domain Controller Hostname | WIN-S3WT6LGQFVX                     |
| Domain                     | COOLWEATHERCOAT.COM                 |
| Site                       | Default-First-Site-Name             |
| Protocols Observed         | LDAP, Kerberos, SMB2, DCE/RPC, NBNS |

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

# Evidence 1 – LDAP Netlogon Discovery

## Packet Reference

Frame 18

## Wireshark Filter

```wireshark
ldap contains "Netlogon"
```

## Observed Values

| Field          | Value                               |
| -------------- | ----------------------------------- |
| Domain         | COOLWEATHERCOAT.COM                 |
| Forest         | COOLWEATHERCOAT.COM                 |
| Hostname       | WIN-S3WT6LGQFVX.coolweathercoat.com |
| NetBIOS Domain | COOLWEATHERCOAT                     |
| LDAP Server    | TRUE                                |
| KDC            | TRUE                                |
| Global Catalog | TRUE                                |
| PDC            | TRUE                                |
| Writable DC    | TRUE                                |

## Evidence

LDAP Netlogon response disclosed:

* Active Directory domain information
* Domain controller capabilities
* Kerberos service availability
* LDAP service availability
* Global Catalog availability

## Analyst Interpretation

The workstation successfully queried Active Directory infrastructure and obtained domain controller information.

This activity is consistent with:

* Domain discovery
* Enterprise authentication preparation
* Active Directory reconnaissance

## MITRE ATT&CK

T1018 – Remote System Discovery

---

# Evidence 2 – NBNS Host Registration

## Packet Reference

Frame 19

## Wireshark Filter

```wireshark
nbns
```

## Observed Values

| Field             | Value             |
| ----------------- | ----------------- |
| Source IP         | 10.7.10.47        |
| Destination       | 10.7.10.255       |
| Hostname          | DESKTOP-9PEA63H   |
| Registration Type | NBNS Registration |

## Evidence

The workstation broadcasted a NetBIOS registration request.

## Analyst Interpretation

The system announced its NetBIOS identity to the local network.

This confirms:

* Host identity
* Windows workstation presence
* Participation in Microsoft networking services

## MITRE ATT&CK

T1018 – Remote System Discovery

---

# Evidence 3 – DCE/RPC Endpoint Mapper Discovery

## Packet Reference

Frame 89

## Wireshark Filter

```wireshark
tcp.port == 135
```

## Observed Values

| Field            | Value        |
| ---------------- | ------------ |
| Protocol         | DCE/RPC Bind |
| Destination Port | 135          |
| Interface        | EPMv4        |
| Context Items    | 3            |

## Evidence

The workstation initiated DCE/RPC communication with the Domain Controller Endpoint Mapper.

## Analyst Interpretation

The Endpoint Mapper is used to discover RPC services available on a host.

This activity frequently precedes:

* Service discovery
* Administrative operations
* Active Directory RPC operations

---

# Evidence 4 – DRSUAPI Discovery

## Packet Reference

Frame 91

## Wireshark Filter

```wireshark
dcerpc
```

## Observed Values

| Field     | Value                                |
| --------- | ------------------------------------ |
| Operation | Endpoint Mapper Map                  |
| UUID      | e3514235-4b06-11d1-ab04-00c04fc2dcd2 |
| Service   | DRSUAPI                              |
| Version   | 4.00                                 |

## Evidence

The workstation requested endpoint information for the Directory Replication Service (DRSUAPI).

## Why This Matters

DRSUAPI is used by:

* Domain Controllers
* Active Directory replication
* Administrative tools
* DCSync operations

## Analyst Interpretation

The host demonstrated awareness of Active Directory replication services.

### Important Limitation

This packet alone does NOT prove:

* DCSync execution
* Password hash extraction
* Credential theft

It only proves discovery of the replication interface.

## Confidence

Medium

## MITRE ATT&CK

T1482 – Domain Trust Discovery

---

# Evidence 5 – LDAP SASL Authentication

## Packet Reference

Frame 189

## Wireshark Filter

```wireshark
ldap contains "GSS-SPNEGO"
```

## Observed Values

| Field                 | Value               |
| --------------------- | ------------------- |
| Authentication Type   | SASL                |
| Mechanism             | GSS-SPNEGO          |
| LDAP Version          | 3                   |
| Kerberos Blob Present | Yes                 |
| Domain                | COOLWEATHERCOAT.COM |

## Evidence

LDAP authentication used:

* SASL
* SPNEGO
* Kerberos

## Analyst Interpretation

The workstation authenticated using integrated Windows authentication.

This confirms:

* Domain membership
* Authenticated LDAP interaction
* Kerberos-backed identity validation

## MITRE ATT&CK

T1078 – Valid Accounts

---

# Evidence 6 – SMB2 Session Setup

## Packet Reference

Frame 194

## Wireshark Filters

```wireshark
smb2
```

or

```wireshark
smb2.cmd == 1
```

## Observed Values

| Field          | Value            |
| -------------- | ---------------- |
| Command        | Session Setup    |
| Security Mode  | Signing Required |
| Blob Length    | 3710             |
| Authentication | SPNEGO           |
| Protocol       | SMB2             |

## Evidence

An SMB2 Session Setup Request was sent to the Domain Controller.

## Analyst Interpretation

The workstation attempted authenticated SMB access.

This confirms:

* SMB authentication negotiation
* Windows file service interaction

### Not Observed

No evidence was found for:

* ADMIN$ access
* C$ access
* File upload
* File download
* Remote service creation

## MITRE ATT&CK

T1021.002 – SMB/Windows Admin Shares

Confidence: Low

Authentication only observed.

---

# Evidence 7 – Kerberos Authentication Negotiation

## Packet Reference

Frame 195

## Wireshark Filters

```wireshark
kerberos
```

or

```wireshark
kerberos.error_code == 25
```

## Observed Values

| Field            | Value               |
| ---------------- | ------------------- |
| Realm            | COOLWEATHERCOAT.COM |
| Service          | krbtgt              |
| Error Code       | PREAUTH_REQUIRED    |
| Kerberos Version | 5                   |

## Evidence

The Domain Controller returned:

ERR-PREAUTH-REQUIRED (25)

## Analyst Interpretation

The workstation requested Kerberos authentication.

The Domain Controller required pre-authentication before issuing tickets.

This behavior is normal for Kerberos authentication.

### Important Limitation

The packet does NOT indicate:

* Password compromise
* Ticket theft
* Kerberoasting
* AS-REP roasting

## MITRE ATT&CK

T1558 – Steal or Forge Kerberos Tickets

Confidence: Low

Only authentication negotiation observed.

---

# Negative Validation Results

## HTTP POST Exfiltration

Filter

```wireshark
http.request.method == "POST"
```

Result

No packets found.

Assessment

No evidence of HTTP-based data exfiltration.

---

## ADMIN$ Access

Filter

```wireshark
smb2.tree contains "ADMIN$"
```

Result

No packets found.

Assessment

No evidence of administrative share access.

---

## SMB File Write Activity

Filter

```wireshark
smb2.cmd == 5
```

Result

No packets found.

Assessment

No evidence of SMB file uploads.

---

# Consolidated Assessment

The supplied packet evidence demonstrates:

* Active Directory discovery
* Netlogon discovery
* LDAP authentication
* Kerberos authentication negotiation
* SMB authentication negotiation
* DCE/RPC replication-service discovery

The activity is consistent with authenticated interaction against enterprise Active Directory infrastructure and may represent post-compromise reconnaissance behavior.

The evidence supports:

* Domain discovery
* Service discovery
* Authentication validation

The evidence does not independently confirm:

* Credential theft
* DCSync execution
* Data exfiltration
* Malware download
* Lateral movement execution
* Command-and-control communications


# Deep Packet Analysis

## LDAP Authentication
The LDAP bindRequest contains SASL/GSS-SPNEGO authentication negotiation with embedded Kerberos data. This demonstrates authenticated enterprise identity interaction rather than anonymous LDAP access.

## SMB Authentication
The SMB2 Session Setup Request confirms SMB authentication negotiation toward the Domain Controller. However, no evidence of ADMIN$, IPC$, or file-write operations was identified in the supplied evidence.

## Kerberos Negotiation
The PREAUTH_REQUIRED response demonstrates normal Kerberos challenge behavior. The evidence confirms Kerberos-backed authentication workflows but does not independently prove credential theft.

## HTTP Redirect Chain Analysis

**Initial Request (Packet reference):**

GET /main.php HTTP/1.1
Host: 80.77.24.175
User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0)

## Malware Identification

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

# IOC Table
<!--|----------|---------------------|---------------------------|-->
| IOC Type | Value 				 | Evidence 				 |
|----------|---------------------|---------------------------|
| IP 	   | 10.7.10.47 		 | LDAP/SMB/Kerberos traffic |
| IP 	   | 10.7.10.9 			 | Domain Controller 		 |
| Domain   | COOLWEATHERCOAT.COM | Kerberos realm            |
| Hostname | DESKTOP-9PEA63H     | NBNS registration         |
| Protocol | SMB2 Session Setup  | Frame 194                 |
| Protocol | LDAP SASL Bind      | Frame 189                 |
<!--|--------------------------------|---------------------------|-->

# Negative Findings
<!--|-------------------------------|-------------------------------|-----------------------|-->
| 		Observation Tested 		| 			Filter				|		Result	    	|	  
|-------------------------------|-------------------------------|-----------------------|
| HTTP POST exfiltration 		| http.request.method == "POST" | No packets found 		|
| ADMIN$ access 		 		| smb2.tree contains "ADMIN$" 	| No packets found 		|
| SMB file writes 		 		| smb2.cmd == 5 				| No packets found 		|
| PsExec-style service creation | dcerpc 						| No confirmed evidence |
<!--|-------------------------------|-------------------------------|-----------------------|-->

# Detection Engineering

<!--|----------------------------|-----------------------------|--------------------------|-->
|       Detection Goal 		 |       Wireshark Filter      | Expected Result 		  |
|----------------------------|-----------------------------|--------------------------|
| LDAP authentication 		 | ldap contains "GSS-SPNEGO"  | SASL bind requests       |
| Kerberos negotiation  	 | kerberos.error_code == 25   | PREAUTH_REQUIRED         |
| SMB authentication 		 | smb2.cmd == 1               | Session Setup            |
| NBNS workstation discovery | nbns 					   | Registration traffic     |
| LDAP Netlogon discovery 	 | ldap contains "Netlogon"    | DC capability disclosure |
<!--|----------------------------|-----------------------------|--------------------------|-->

# MITRE ATT&CK Mapping
<!--|-------------------|------------------------------------|---------------------------------------|-->
| Tactic 			| Technique 						 |	 Evidence 							 |
|-------------------|------------------------------------|---------------------------------------|
| Discovery 		| T1018 Remote System Discovery      | NBNS registration 					 |
| Discovery 		| T1087 Account Discovery 		     | LDAP interaction 					 |
| Credential Access | T1558 Kerberos Tickets 			 | Kerberos negotiation 				 |
| Lateral Movement  | T1021.002 SMB/Windows Admin Shares | SMB Session Setup authentication only |
<!--|-------------------|------------------------------------|---------------------------------------|-->

# Final Assessment

The supplied packet evidence demonstrates authenticated Active Directory interaction involving LDAP, Kerberos, and SMB protocols. The workstation authenticated against enterprise identity infrastructure and initiated SMB session negotiation with the Domain Controller.

The evidence supports post-compromise identity interaction and possible reconnaissance preparation; however:
- no HTTP POST exfiltration was observed,
- no ADMIN$ access was observed,
- no confirmed lateral movement execution was identified,
- no confirmed payload transfer was identified.

The conclusions above are restricted to observable packet evidence only.
