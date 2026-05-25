
# Executive Summary

The provided packet evidence demonstrates authenticated interaction between workstation 10.7.10.47 and Active Directory infrastructure hosted on 10.7.10.9. Observed traffic includes LDAP authentication, Kerberos negotiation, SMB2 session setup, and NBNS workstation registration. The traffic pattern is consistent with post-compromise enterprise authentication activity associated with malware such as ICEID.

# Environment Overview
|--------------------|------------------------------|
| Component 		 | Value 						|
|--------------------|------------------------------|
| Workstation 		 | 10.7.10.47 					|
| Hostname 			 | DESKTOP-9PEA63H 				|
| Domain Controller  | 10.7.10.9 					|
| DC Hostname 		 | WIN-S3WT6LGQFVX 				|	
| Domain 			 | COOLWEATHERCOAT.COM 			|
| Protocols Observed | LDAP, Kerberos, SMB2, NBNS 	|
|--------------------|------------------------------|


# Chronological Timeline

## Frame 18 — LDAP Netlogon Discovery

### Evidence
- LDAP Netlogon response observed.
- Domain Controller capabilities exposed:
  - LDAP server
  - KDC

### Wireshark Filter
ldap contains "Netlogon"

### Analyst Interpretation
The workstation obtained Active Directory infrastructure details from the Domain Controller.

---

## Frame 19 — NBNS Registration

### Evidence
- NBNS Registration for DESKTOP-9PEA63H<00>
- Broadcast destination: 10.7.10.255

### Wireshark Filter
nbns

### Analyst Interpretation
The workstation announced itself on the local Windows network using NetBIOS Name Service.

---

## Frame 189 — LDAP SASL Authentication

### Evidence
- LDAP bindRequest observed
- SASL authentication
- GSS-SPNEGO negotiation
- Embedded Kerberos negotiation token

### Wireshark Filter
ldap contains "GSS-SPNEGO"

### Matching Values
- mechanism: GSS-SPNEGO
- krb5_blob present
- Domain: COOLWEATHERCOAT.COM

### Analyst Interpretation
The workstation authenticated to Active Directory using integrated Windows authentication.

---

## Frame 194 — SMB2 Session Setup

### Evidence
- SMB2 Session Setup Request
- TCP/445 communication
- Signing required
- SPNEGO security blob present

### Wireshark Filters
smb2
smb2.cmd == 1

### Matching Values
- Command: Session Setup (1)
- Security mode: Signing required
- Blob Length: 3710

### Analyst Interpretation
The workstation attempted authenticated SMB session establishment with the Domain Controller.

### Revalidation Notes
- No ADMIN$ access identified.
- No SMB file-transfer evidence identified.
- No remote service creation identified.

---

## Frame 195 — Kerberos PREAUTH_REQUIRED

### Evidence
- Kerberos response from Domain Controller
- Error Code: PREAUTH_REQUIRED (25)

### Wireshark Filters
kerberos
kerberos.error_code == 25

### Matching Values
- realm: COOLWEATHERCOAT.COM
- sname: krbtgt/COOLWEATHERCOAT.COM
- pA-ETYPE-INFO2 present

### Analyst Interpretation
The Domain Controller requested Kerberos pre-authentication before issuing authentication tickets.

# Deep Packet Analysis

## LDAP Authentication
The LDAP bindRequest contains SASL/GSS-SPNEGO authentication negotiation with embedded Kerberos data. This demonstrates authenticated enterprise identity interaction rather than anonymous LDAP access.

## SMB Authentication
The SMB2 Session Setup Request confirms SMB authentication negotiation toward the Domain Controller. However, no evidence of ADMIN$, IPC$, or file-write operations was identified in the supplied evidence.

## Kerberos Negotiation
The PREAUTH_REQUIRED response demonstrates normal Kerberos challenge behavior. The evidence confirms Kerberos-backed authentication workflows but does not independently prove credential theft.

# Negative Findings
|-------------------------------|-------------------------------|-----------------------|
| 		Observation Tested 		| 			Filter				|		Result	    	|	  
|-------------------------------|-------------------------------|-----------------------|
| HTTP POST exfiltration 		| http.request.method == "POST" | No packets found 		|
| ADMIN$ access 		 		| smb2.tree contains "ADMIN$" 	| No packets found 		|
| SMB file writes 		 		| smb2.cmd == 5 				| No packets found 		|
| PsExec-style service creation | dcerpc 						| No confirmed evidence |
|-------------------------------|-------------------------------|-----------------------|

# IOC Table
|----------|---------------------|---------------------------|
| IOC Type | Value 				 | Evidence 				 |
|----------|---------------------|---------------------------|
| IP 	   | 10.7.10.47 		 | LDAP/SMB/Kerberos traffic |
| IP 	   | 10.7.10.9 			 | Domain Controller 		 |
| Domain   | COOLWEATHERCOAT.COM | Kerberos realm            |
| Hostname | DESKTOP-9PEA63H     | NBNS registration         |
| Protocol | SMB2 Session Setup  | Frame 194                 |
| Protocol | LDAP SASL Bind      | Frame 189                 |
|--------------------------------|---------------------------|

# Detection Engineering

|----------------------------|-----------------------------|--------------------------|
|       Detection Goal 		 |       Wireshark Filter      | Expected Result 		  |
|----------------------------|-----------------------------|--------------------------|
| LDAP authentication 		 | ldap contains "GSS-SPNEGO"  | SASL bind requests       |
| Kerberos negotiation  	 | kerberos.error_code == 25   | PREAUTH_REQUIRED         |
| SMB authentication 		 | smb2.cmd == 1               | Session Setup            |
| NBNS workstation discovery | nbns 					   | Registration traffic     |
| LDAP Netlogon discovery 	 | ldap contains "Netlogon"    | DC capability disclosure |
|----------------------------|-----------------------------|--------------------------|

# MITRE ATT&CK Mapping
|-------------------|------------------------------------|---------------------------------------|
| Tactic 			| Technique 						 |	 Evidence 							 |
|-------------------|------------------------------------|---------------------------------------|
| Discovery 		| T1018 Remote System Discovery      | NBNS registration 					 |
| Discovery 		| T1087 Account Discovery 		     | LDAP interaction 					 |
| Credential Access | T1558 Kerberos Tickets 			 | Kerberos negotiation 				 |
| Lateral Movement  | T1021.002 SMB/Windows Admin Shares | SMB Session Setup authentication only |
|-------------------|------------------------------------|---------------------------------------|

# Final Assessment

The supplied packet evidence demonstrates authenticated Active Directory interaction involving LDAP, Kerberos, and SMB protocols. The workstation authenticated against enterprise identity infrastructure and initiated SMB session negotiation with the Domain Controller.

The evidence supports post-compromise identity interaction and possible reconnaissance preparation; however:
- no HTTP POST exfiltration was observed,
- no ADMIN$ access was observed,
- no confirmed lateral movement execution was identified,
- no confirmed payload transfer was identified.

The conclusions above are restricted to observable packet evidence only.
