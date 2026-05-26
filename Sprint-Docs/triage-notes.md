# Workstream A — Triage Notes
**Project KAVACH | Network Forensics**

---

## A.1 — Source PCAP Justification

| Field | Detail |
|---|---|
| **Source** | Unit 42 / Palo Alto Networks Wireshark Quiz — July 2023 |
| **Reference** | https://unit42.paloaltonetworks.com/wireshark-quiz-icedid/ |
| **Repository** | https://github.com/pan-unit42/Wireshark-quizzes/ |
| **Malware Family** | IcedID (a.k.a. Bokbot) — banking trojan |
| **File** | `2023-07-Unit42-Wireshark-quiz.pcap` |

### Analogue Justification

This capture is a defensible analogue of the Meridian FinServe incident for the following reasons:

1. **Sector match:** IcedID is a banking trojan specifically designed to target financial institutions, steal banking credentials, and enable fraud. Meridian FinServe is an NBFC — precisely the class of organisation IcedID operators target.

2. **Behavioural match:** The capture shows a host that performs normal-looking domain-joined Windows activity (Kerberos, LDAP, SMB) before exhibiting anomalous outbound traffic — exactly the "server segment that historically generates predictable, low-variance flows" described in Trigger A of the brief.

3. **Attack surface match:** The infection chain includes PowerShell-based dropper activity, C2 beaconing, credential dumping via DCSync (DRSUAPI), and lateral movement indicators — covering the east-west and outbound anomalies Meridian's SOC observed.

4. **Time window:** The capture spans approximately 36 seconds of intense activity (22:39:22 – 22:39:58 UTC, July 10 2023), representing a compressed view of an infection that in a real environment would unfold over hours or days — consistent with the 72-hour window described in the brief.

---

## A.2 — Triage Pass

### Capture File Properties

| Property | Value |
|---|---|
| **Start time** | Jul 10, 2023 22:39:22.849 UTC |
| **End time** | Jul 10, 2023 22:39:58.364 UTC |
| **Duration** | ~35.5 seconds |
| **Total frames** | 2,497 |
| **Total bytes** | ~1.38 MB |

### Internal Hosts Identified

| IP Address | Role |
|---|---|
| `10.7.10.47` | **Victim workstation** — DESKTOP-9PEA63H, domain-joined to coolweathercoat.com |
| `10.7.10.9` | **Domain Controller** — WIN-S3WT6LGQFVX.coolweathercoat.com |

### Protocol Hierarchy

| Protocol | Frames | Notes |
|---|---|---|
| TCP | 2,369 | Dominant — carries LDAP, SMB2, Kerberos, TLS, HTTP |
| TLS | 211 | Encrypted C2 and HTTPS traffic |
| LDAP | 94 | Active Directory enumeration |
| DCERPC | 94 | Remote procedure calls — includes DRSUAPI |
| SMB2 | 135 | File sharing and Group Policy fetch |
| Kerberos | 52 | Authentication ticket requests |
| DNS | 68 | Domain lookups — includes two highly suspicious domains |
| HTTP | 6 | Unencrypted — PowerShell dropper check-ins |
| DRSUAPI | 36 | **Critical** — Directory Replication Service (DCSync) |
| UDP | 115 | DHCP, mDNS, NBNS, NTP |

### Top Talkers (TCP Conversations by Volume)

| Conversation | Frames | Bytes | Significance |
|---|---|---|---|
| `10.7.10.47` ↔ `194.26.135.119:12432` | 787 | 592 KB | **CRITICAL — IcedID C2 on non-standard port** |
| `10.7.10.47` ↔ `92.118.151.9:443` | 324 | 371 KB | **HIGH — IcedID payload delivery (guiatelefonos.com)** |
| `10.7.10.47` ↔ `10.7.10.9:445` | 151 | 43 KB | SMB — normal domain traffic |
| `10.7.10.47` ↔ `204.79.197.200:443` | 48 | 19 KB | Microsoft (Bing/MSN) — legitimate |

### Anomalous Bursts (IO Graph Observations)

| Time Offset | Event |
|---|---|
| T+0 to T+11s | Domain join activity — LDAP, Kerberos, SMB2, DRSUAPI (AD enumeration begins immediately) |
| T+24s | PowerShell dropper checks in to `623start.site` with `?status=start&av=Windows%20Defender` |
| T+25s | Second PowerShell check-in: `?status=install` |
| T+26s | HTTP GET to `guiatelefonos.com/data/czx.jpg` — IcedID payload (disguised as image) |
| T+26.5s | TLS connection to `92.118.151.9:443` — encrypted payload download begins |
| T+27s | **IcedID C2 beacon begins** — connection to `194.26.135.119:12432` (592 KB exchanged) |

### Baseline Characterisation (Normal Traffic in This Capture)

The following traffic is consistent with a newly domain-joined Windows workstation performing normal AD operations:

- Kerberos AS-REQ / TGS-REQ to Domain Controller `10.7.10.9`
- LDAP bind and search queries to `10.7.10.9:389`
- SMB2 Group Policy fetch (`coolweathercoat.com\Policies\...gpt.ini`)
- DNS lookups for `_ldap._tcp.dc._msdcs.coolweathercoat.com`
- Microsoft telemetry and MSN/Bing connections (normal Windows browser activity)

**Anomaly baseline:** Any connection to IPs outside Microsoft/Azure infrastructure, any HTTP traffic using a PowerShell user-agent, and any connection to non-standard ports (12432) are deviations from this baseline.

---

*tshark commands used to produce this analysis are documented in `report.md`*
