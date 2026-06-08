
# ICEID Rapid Triage Notes

**Incident ID:** KAVACH-2024-ICEID-002
**Analyst:** Data Defenders Team
**PCAP Source:** Malware-Traffic-Analysis.net / Unit 42 IcedID Quiz
**Date/Time Range:** 2023-07-19 14:12:33 - 14:25:47 UTC
**Severity:** CRITICAL

## Executive Summary

ICEID (BokBot) banking trojan infection with confirmed post-compromise Active Directory reconnaissance, Kerberos authentication abuse, and DRSUAPI interface discovery consistent with DCSync preparation. Initial access via malicious email attachment (Invoice_07-19-2023.zip) containing ISO/LNK sideloading chain. Cobalt Strike beacon C2 patterns NOT confirmed in this capture; analysis focuses on ICEID native C2 and AD abuse.

- Suspected malware family: ICEID / BokBot
- Primary infected host: 10.7.10.47 (DESKTOP-9PEA63H)
- Domain Controller: 10.7.10.9 (WIN-S3WT6LGQFVX.coolweathercoat.com)
- Source MAC: 80 86 5b ab 1e c4
- Dest MAC: FF:FF:FF:FF:FF:FF
- Active Directory domain: COOLWEATHERCOAT.COM
- Observed protocols: LDAP, Kerberos, SMB2, NBNS, DNS, HTTP
    (DHCP, TCP,UDP, ARP,DNS, HTTP, TLS1.2, LDAP,CLDAP, DCERPC, DRSUAPI, EPM, IGMPv3,KRB5, LSARPC,MDNS,NBNS,NTP,RPC_NETLOGON, SMB,SMB2) 
- Infection severity: Critical
- Potential attacker objectives:
  - Active Directory reconnaissance
  - Kerberos-authenticated resource access
  - SMB-based lateral movement
  - Command-and-control beaconing

## Findings
- LDAP bind requests using SASL/GSS-SPNEGO observed from 10.7.10.47 to Domain Controller.
- Kerberos pre-authentication negotiation observed via KRB-ERROR PREAUTH_REQUIRED.
- SMB2 Session Setup Requests toward Domain Controller over TCP/445.
- NBNS workstation registration broadcasts from DESKTOP-9PEA63H.
- Internal AD discovery activity strongly suggests post-compromise reconnaissance.

## Potential Lateral Movement Indicators
- LDAP authenticated queries
- NetBIOS workstation advertisement
- SMB2 Session Setup to Domain Controller
- Kerberos service negotiation - TGS Requests (Kerberoasting)
  kerberos.msg_type == 12 -  12 packets
- AS-REQ Without Preauthentication (AS-REP Roasting)
  kerberos.msg_type == 10 - 14 Packets

## Potential C2 Characteristics
- Repeated outbound communications were validated using:
  - dns
  - tls
- Periodic Connections (BEACONING) was observed with multiple TCP connections
  - !(ip.dst == 10.0.0.0/8) && !(ip.dst == 172.16.0.0/12) && !(ip.dst == 192.168.0.0/16)
  - tcp.flags.syn == 1 && tcp.flags.ack == 0
- DNS query Frequency:
  - dns.qry.name
- Large outbound payloads
  - tcp.len > 1000 
- Replication Interface Enumeration :
-     DRSUAPI
 
## Key Packet References
- Frame 18: LDAP Netlogon response
- Frame 19: Workstation discovery - NBNS workstation registration
- Frame 89 - RPC endpoint negotiation
- Frame 189: Authenticated AD interaction - LDAP SASL bind request
- Frame 194: SMB2 Session Setup
- Frame 195: Domain authentication negotiation - Kerberos PREAUTH_REQUIRED response 

## CONFIRMED INFECTION INDICATORS

| Indicator | Value | Confidence |
|-----------|-------|------------|
| Victim IP | 10.7.10.47 | HIGH |
| Victim Hostname | DESKTOP-9PEA63H | HIGH |
| Domain Controller | 10.7.10.9 (WIN-S3WT6LGQFVX) | HIGH |
| AD Domain | COOLWEATHERCOAT.COM | HIGH |

## HIGH-CONFIDENCE MALICIOUS IOCs

| Type | Value | Stage | Confidence |
|------|-------|-------|------------|
| IP | 80.77.24.175 | Initial Payload Delivery | HIGH |
| IP | 192.153.57.223 | IcedID Loader C2 | HIGH |
| IP | 104.168.53.18 | IcedID HTTPS C2 | HIGH |
| IP | 217.199.121.56 | IcedID HTTPS C2 | HIGH |
| IP | 194.26.135.119 | IcedID C2 (port 12432) | HIGH |
| Domain | firebasestorage.googleapis.com | Payload Hosting | HIGH |
| Domain | skigimeetroc.com | Loader C2 | HIGH |
| Domain | askamoshopsi.com | HTTPS C2 | HIGH |
| Domain | skansnekssky.com | HTTPS C2 | HIGH |
| Domain | 623start.site | PowerShell Check-in | HIGH |
| Domain | guiatelefonos.com | Payload Download | HIGH |

## OBSERVED PROTOCOLS AND ACTIVITY

| Protocol | Port | Direction | Observed Activity |
|----------|------|-----------|-------------------|
| HTTP | 80 | Outbound | GET /main.php (redirect to Firebase), GET to 623start.site (PowerShell UA) |
| HTTPS | 443 | Outbound | IcedID C2 beaconing, payload download from Firebase |
| TCP | 12432 | Outbound | Custom IcedID C2 protocol (~592KB transfer) |
| DNS | 53 | Outbound | DGA domains, C2 resolution |
| LDAP | 389 | East-West | Authenticated AD enumeration, Netlogon discovery |
| Kerberos | 88 | East-West | TGT requests, PREAUTH_REQUIRED |
| SMB2 | 445 | East-West | Session Setup, IPC$, DRSUAPI binding |
| NBNS | 137 | Broadcast | Host registration (DESKTOP-9PEA63H) |

## POST-COMPROMISE AND RECONNAISSANCE

| Observation | Wireshark Filter | Evidence |
|-------------|------------------|----------|
| LDAP Netlogon Discovery | `ldap contains "Netlogon"` | DC capabilities exposed (LDAP, KDC, GC, PDC) |
| NBNS Registration | `nbns` | DESKTOP-9PEA63H broadcast |
| LDAP SASL/GSS-SPNEGO | `ldap contains "GSS-SPNEGO"` | Authenticated bind with Kerberos token |
| SMB2 Session Setup | `smb2.cmd == 1` | Authentication negotiation with DC |
| Kerberos PREAUTH | `kerberos.error_code == 25` | DC requesting pre-authentication |
| DRSUAPI Binding | `dcerpc && frame contains "DRSUAPI"` | DCSync interface discovery (CRITICAL) |

## KEY WIRESHARK FILTERS (Quick Validation)

- ldap
- nbns
- smb2
- smb2.cmd == 5
- smb2.tree contains "IPC$"
- kerberos
- kerberos.msg_type == 1
- dcerpc

#### Victim identification
ip.addr == 10.7.10.47

#### Malicious outbound C2
ip.dst == 80.77.24.175 || ip.dst == 192.153.57.223 || ip.dst == 104.168.53.18 || ip.dst == 217.199.121.56

#### Custom C2 port
tcp.port == 12432

#### AD Reconnaissance
ldap && ip.addr == 10.7.10.47
kerberos && ip.addr == 10.7.10.47
smb2 && ip.addr == 10.7.10.47

#### DRSUAPI/DCSync detection
dcerpc && frame contains "DRSUAPI"

#### PowerShell HTTP User-Agent
http.user_agent contains "PowerShell"

#### DGA pattern DNS
dns.qry.name matches "^[a-z]{5,15}\.(com|org|net)"


## IMMEDIATE CONTAINMENT RECOMMENDATIONS

1. **IMMEDIATE:** Isolate 10.7.10.47 from network
2. **BLOCK:** Outbound to 80.77.24.175, 192.153.57.223, 104.168.53.18, 217.199.121.56, 194.26.135.119
3. **BLOCK:** Block suspicious outbound HTTP/HTTPS. DNS for 623start.site, guiatelefonos.com, skigimeetroc.com, askamoshopsi.com, skansnekssky.com
4. **RESET:** All credentials used on DESKTOP-9PEA63H
5. **AUDIT:** Kerberos TGT requests, LDAP queries, DRSUAPI calls from non-DC hosts
6. **REIMAGE:** Workstation immediately (persistence cannot be guaranteed)
7. **DISABLE:** Disable legacy NBNS/LLMNR if operationally feasible
8. **REVIEW:** SMB administrative share access
