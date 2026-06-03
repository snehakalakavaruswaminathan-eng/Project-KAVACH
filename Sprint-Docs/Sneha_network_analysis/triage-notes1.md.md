# ICEID Malware Infection - Triage Notes (Refined)

**Incident ID:** KAVACH-2024-ICEID-002
**Analyst:** Data Defenders Team
**PCAP Source:** Malware-Traffic-Analysis.net / Unit 42 IcedID Quiz
**Date/Time Range:** 2023-07-19 14:12:33 - 14:25:47 UTC
**Severity:** CRITICAL

## EXECUTIVE SUMMARY

ICEID (BokBot) banking trojan infection with confirmed post-compromise Active Directory reconnaissance, Kerberos authentication abuse, and DRSUAPI interface discovery consistent with DCSync preparation. Initial access via malicious email attachment (Invoice_07-19-2023.zip) containing ISO/LNK sideloading chain. Cobalt Strike beacon C2 patterns NOT confirmed in this capture; analysis focuses on ICEID native C2 and AD abuse.

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

## OBSERVED PROTOCOLS & ACTIVITY

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

## POST-COMPROMISE AD RECONNAISSANCE

| Observation | Wireshark Filter | Evidence |
|-------------|------------------|----------|
| LDAP Netlogon Discovery | `ldap contains "Netlogon"` | DC capabilities exposed (LDAP, KDC, GC, PDC) |
| NBNS Registration | `nbns` | DESKTOP-9PEA63H broadcast |
| LDAP SASL/GSS-SPNEGO | `ldap contains "GSS-SPNEGO"` | Authenticated bind with Kerberos token |
| SMB2 Session Setup | `smb2.cmd == 1` | Authentication negotiation with DC |
| Kerberos PREAUTH | `kerberos.error_code == 25` | DC requesting pre-authentication |
| DRSUAPI Binding | `dcerpc && frame contains "DRSUAPI"` | DCSync interface discovery (CRITICAL) |

## IMMEDIATE CONTAINMENT RECOMMENDATIONS

1. **IMMEDIATE:** Isolate 10.7.10.47 from network
2. **BLOCK:** Outbound to 80.77.24.175, 192.153.57.223, 104.168.53.18, 217.199.121.56, 194.26.135.119
3. **BLOCK:** DNS for 623start.site, guiatelefonos.com, skigimeetroc.com, askamoshopsi.com, skansnekssky.com
4. **RESET:** All credentials used on DESKTOP-9PEA63H
5. **AUDIT:** Kerberos TGT requests, LDAP queries, DRSUAPI calls from non-DC hosts
6. **REIMAGE:** Workstation immediately (persistence cannot be guaranteed)
7. **DISABLE:** NBNS/LLMNR if operationally feasible

## KEY WIRESHARK FILTERS (Quick Validation)

```bash
# Victim identification
ip.addr == 10.7.10.47

# Malicious outbound C2
ip.dst == 80.77.24.175 || ip.dst == 192.153.57.223 || ip.dst == 104.168.53.18 || ip.dst == 217.199.121.56

# Custom C2 port
tcp.port == 12432

# AD Reconnaissance
ldap && ip.addr == 10.7.10.47
kerberos && ip.addr == 10.7.10.47
smb2 && ip.addr == 10.7.10.47

# DRSUAPI/DCSync detection
dcerpc && frame contains "DRSUAPI"

# PowerShell HTTP User-Agent
http.user_agent contains "PowerShell"

# DGA pattern DNS
dns.qry.name matches "^[a-z]{5,15}\.(com|org|net)"