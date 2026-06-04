# Workstream A — Hypothesis-Driven Deep Dive
**Project KAVACH | Network Forensics — A.3**

---

> **Brief requirement:** Form at least three competing hypotheses about what the traffic represents, then test each against the data. Each hypothesis carries: what would confirm it, what would refute it, and the verdict. The deliverable is the reasoning, not the answer.

---

## Hypothesis 1 — Routine IT Onboarding / New Domain Join

**Statement:** The anomalous traffic observed is simply a workstation being freshly joined to the domain. The LDAP, Kerberos, and SMB2 traffic is entirely expected during provisioning, and the outbound connections are standard Windows telemetry.

### What Would Confirm It
- All external connections resolve to known Microsoft/CDN infrastructure
- No connections to non-standard ports
- No PowerShell-based HTTP requests to unknown domains
- No DRSUAPI/DCSync calls from a non-DC host
- Traffic volume normalises after initial burst

### What Would Refute It
- Presence of HTTP requests with PowerShell user-agent to unknown domains
- Connection to IPs with no legitimate business justification on non-standard ports
- DRSUAPI (DCSync) traffic originating from the workstation rather than the DC
- DNS queries to newly-registered or low-reputation domains

### Evidence Tested

| Evidence | Supports / Refutes |
|---|---|
| LDAP, Kerberos, SMB2 to `10.7.10.9` | Supports — normal domain join |
| Microsoft/Azure TLS connections (Bing, MSN, Azure CDN) | Supports — normal Windows |
| HTTP GET `623start.site/?status=start&av=Windows%20Defender` via PowerShell UA (frame 1357) | **Refutes** — no legitimate onboarding tool uses this pattern |
| HTTP GET `623start.site/?status=install` (frame 1360) | **Refutes** — install status reporting to unknown server |
| DNS query `623start.site` → `195.161.114.3` (frame 1352) | **Refutes** — domain not associated with any Microsoft service |
| DRSUAPI opcodes from `10.7.10.47` (workstation) to DC (frames 252–263) | **Refutes** — DCSync should only originate from Domain Controllers |
| TCP connection to `194.26.135.119:12432` (592 KB, non-standard port) | **Refutes** — no legitimate Windows process uses port 12432 |

### Verdict: **REJECTED**

The hypothesis fails on multiple strong refutations. A legitimate onboarding process does not beacon to unknown domains via PowerShell, does not perform DCSync from a workstation, and does not establish 592 KB data transfers to unrecognised IPs on non-standard ports.

---

## Hypothesis 2 — PowerShell-Delivered Malware with C2 Beaconing (No Credential Theft)

**Statement:** A PowerShell script was delivered and executed on the workstation, which established C2 communication. The scope is limited to remote access — the attacker has not yet moved laterally or stolen credentials.

### What Would Confirm It
- PowerShell HTTP check-ins to a C2 domain
- Encrypted C2 traffic to an external IP
- No DRSUAPI / DCSync activity
- No credential-related LDAP queries beyond normal logon
- No evidence of second-stage payload deployment

### What Would Refute It
- Presence of DRSUAPI (DCSync) calls — these indicate credential harvesting, not just remote access
- Second-stage payload download (a file disguised as an image, for example)
- Large encrypted data transfer suggesting exfiltration or module download

### Evidence Tested

| Evidence | Supports / Refutes |
|---|---|
| PowerShell UA HTTP GET `/?status=start&av=Windows%20Defender` (frame 1357) | Supports — consistent with PowerShell dropper |
| PowerShell UA HTTP GET `/?status=install` (frame 1360) | Supports — install confirmation callback |
| TLS connection to `194.26.135.119:12432` (787 frames, 592 KB) | Supports — encrypted C2 channel |
| DRSUAPI opcodes 0, 1, 12, 13 from workstation (frames 252–315, 604–720) | **Refutes** — credential dumping is occurring |
| HTTP GET `guiatelefonos.com/data/czx.jpg` then TLS on same IP (frames 1369–1383) | **Refutes** — second stage payload download present |
| 371 KB transferred from `92.118.151.9:443` | **Refutes** — this volume is consistent with a payload, not just metadata |

### Verdict: **PARTIALLY CONFIRMED, BUT INCOMPLETE**

This hypothesis correctly identifies the initial access mechanism (PowerShell dropper + C2). However it underestimates the attack scope. The DRSUAPI traffic definitively shows credential dumping activity has already occurred, and the `guiatelefonos.com` connection confirms a second-stage payload was downloaded. The attacker has progressed beyond simple remote access.

---

## Hypothesis 3 — IcedID Banking Trojan: Multi-Stage Infection with DCSync Credential Harvesting

**Statement:** The traffic represents a full IcedID banking trojan infection chain. A PowerShell dropper performs initial check-in, downloads a second-stage payload disguised as a JPEG, establishes encrypted C2 communication, performs Active Directory enumeration via LDAP and Kerberos, and dumps domain credentials via DCSync (DRSUAPI) — consistent with IcedID's known post-exploitation behaviour.

### What Would Confirm It
- PowerShell HTTP check-in with `?status=start&av=` parameter (AV reporting — IcedID signature)
- Second-stage download from a domain with a `.jpg` URI disguising a binary payload
- Encrypted C2 on a non-standard port with high data volume
- DRSUAPI opcodes from a non-DC workstation (DCSync attack)
- LDAP enumeration of Active Directory objects
- Kerberos ticket requests consistent with credential reuse

### What Would Refute It
- Absence of DRSUAPI from the workstation
- Second-stage download resolving to a known-legitimate CDN
- C2 IP resolving to a known-clean service
- No PowerShell user-agent in HTTP traffic

### Evidence Tested

| Evidence | Supports / Refutes |
|---|---|
| Frame 1357: `GET /?status=start&av=Windows%20Defender` via PowerShell | **Strongly Supports** — IcedID dropper signature |
| Frame 1360: `GET /?status=install` | **Supports** — install confirmation |
| Frame 1352: DNS `623start.site` → `195.161.114.3` | **Supports** — dropper C2 domain |
| Frame 1369: `GET /data/czx.jpg` from `guiatelefonos.com` | **Supports** — IcedID disguises payloads as image files |
| Frames 1372–1383: TLS to `92.118.151.9:443` (371 KB inbound) | **Supports** — encrypted payload delivery |
| Frames 1697–1787: TCP to `194.26.135.119:12432` (592 KB, non-std port) | **Strongly Supports** — IcedID BackConnect C2 protocol |
| Frames 252–263: DRSUAPI from `10.7.10.47` (workstation) to DC | **Strongly Supports** — DCSync credential dumping |
| Repeated LDAP queries to `10.7.10.9:389` (frames 26–720) | **Supports** — AD enumeration |
| Kerberos AS-REQ/TGS-REQ exchanges (52 frames) | **Supports** — credential-based lateral movement preparation |

### Verdict: **CONFIRMED — HIGH CONFIDENCE**

All observable evidence is consistent with IcedID infection. The attack chain is: PowerShell dropper → AV discovery → C2 check-in → second-stage IcedID payload download → AD enumeration → DCSync credential dump → encrypted C2 channel established. This matches IcedID's documented post-exploitation behaviour exactly.

---

## Attack Chain Summary

```
T+0s     Host joins domain — LDAP/Kerberos/SMB2 (normal)
T+11s    AD enumeration begins — LDAP queries, DRSUAPI DCSync starts
T+24s    PowerShell dropper checks in: 623start.site/?status=start&av=Windows Defender
T+25s    Install confirmation: 623start.site/?status=install
T+26s    IcedID payload download: guiatelefonos.com/data/czx.jpg (disguised binary)
T+26.5s  Encrypted payload delivery via TLS: 92.118.151.9:443 (371 KB)
T+27s    IcedID C2 channel established: 194.26.135.119:12432 (592 KB exchanged)
T+27s+   Continued AD enumeration and DCSync operations
```

---

*Packet references are frame numbers from `2023-07-Unit42-Wireshark-quiz.pcap`*
