# Active Directory Network Forensic Evidence Report
# Executive Summary

The provided packet evidence demonstrates authenticated interaction between workstation 10.7.10.47 and Active Directory infrastructure hosted on 10.7.10.9. Observed traffic includes LDAP authentication, Kerberos negotiation, SMB2 session setup, and NBNS workstation registration. The traffic pattern is consistent with post-compromise enterprise authentication activity associated with malware such as ICEID.

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

# Negative Findings
<!--|-------------------------------|-------------------------------|-----------------------|-->
| 		Observation Tested 		| 			Filter				|		Result	    	|	  
|-------------------------------|-------------------------------|-----------------------|
| HTTP POST exfiltration 		| http.request.method == "POST" | No packets found 		|
| ADMIN$ access 		 		| smb2.tree contains "ADMIN$" 	| No packets found 		|
| SMB file writes 		 		| smb2.cmd == 5 				| No packets found 		|
| PsExec-style service creation | dcerpc 						| No confirmed evidence |
<!--|-------------------------------|-------------------------------|-----------------------|-->

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
