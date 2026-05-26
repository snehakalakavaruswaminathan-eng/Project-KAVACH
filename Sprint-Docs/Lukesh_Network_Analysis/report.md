# Workstream A — Network Forensics Report
**Project KAVACH | Meridian FinServe Security Assessment**

---

## Summary

Analysis of the IcedID banking trojan PCAP (Unit 42 Wireshark Quiz, July 2023) confirms a multi-stage infection consistent with the anomalous traffic described in Meridian FinServe's Trigger A. A single workstation (`10.7.10.47`, DESKTOP-9PEA63H) was compromised via a PowerShell dropper, which downloaded the IcedID banking trojan payload, established an encrypted C2 channel on non-standard port 12432, and performed a DCSync attack against the Domain Controller — dumping all domain credentials. The infection chain is fully reconstructed and documented below.

**Confidence level: HIGH** — All findings are supported by direct packet evidence with frame number references.

---

## Infection Chain

| Step | Time Offset | Event | Frame(s) | Confidence |
|---|---|---|---|---|
| 1 | T+0s | Host joins domain; LDAP/Kerberos/SMB2 begin | 13–46 | Baseline |
| 2 | T+11s | AD enumeration; DRSUAPI DCSync starts | 252–315 | High |
| 3 | T+24s | PowerShell dropper checks in; reports AV product | 1357 | High |
| 4 | T+25s | Dropper confirms install | 1360 | High |
| 5 | T+26s | IcedID payload download (disguised as JPEG) | 1369 | High |
| 6 | T+26.5s | Encrypted payload delivery via TLS | 1372–1383 | High |
| 7 | T+27s | IcedID C2 channel established (port 12432) | 1697–1787 | High |
| 8 | T+27s+ | Continued DCSync credential harvesting | 604–720 | High |

---

## Key Findings

### Finding 1 — PowerShell Dropper with AV Fingerprinting (CRITICAL)
- **Frame 1357:** `GET /?status=start&av=Windows%20Defender HTTP/1.1`
- **Host:** `623start.site` (195.161.114.3)
- **User-Agent:** `Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.3031`
- **Significance:** The dropper reports the installed AV product to the C2, enabling the operator to select an AV-evasive second-stage payload. This is a documented IcedID behaviour.

### Finding 2 — IcedID Payload Disguised as JPEG (HIGH)
- **Frame 1369:** `GET /data/czx.jpg HTTP/1.1` → Host: `guiatelefonos.com` (92.118.151.9:80)
- **Response:** HTTP 301 redirect to HTTPS; 371 KB binary payload delivered via TLS on same IP
- **Significance:** IcedID routinely disguises its DLL payload as an image file. The `.jpg` extension is a social engineering artefact — the file is a Windows DLL.

### Finding 3 — IcedID BackConnect C2 on Non-Standard Port (CRITICAL)
- **Connection:** `10.7.10.47:49744` ↔ `194.26.135.119:12432`
- **Volume:** 787 frames, 592 KB total (380 frames inbound, 407 frames outbound)
- **Duration:** 8.2 seconds of active transfer
- **Port 12432:** Non-standard; no legitimate service uses this port; consistent with IcedID BackConnect C2 protocol
- **Significance:** This is the primary C2 channel. The operator has full remote access to the infected host.

### Finding 4 — DCSync Credential Dumping (CRITICAL)
- **Frames 252–263, 308–315, 604–720:** DRSUAPI opcodes 0 (bind), 1 (DsBind), 12 (DsGetNCChanges), 13 (DsReplicaSync) from `10.7.10.47` (workstation) to `10.7.10.9` (DC)
- **Significance:** DCSync is an attack where a non-DC host impersonates a Domain Controller and requests credential replication from the real DC. DRSUAPI opcode 12 (`DsGetNCChanges`) is the specific call that retrieves password hashes. All domain account credentials — including administrator accounts — are likely compromised.
- **MITRE ATT&CK:** T1003.006 — OS Credential Dumping: DCSync

### Finding 5 — Active Directory Enumeration (HIGH)
- 94 LDAP frames to `10.7.10.9:389` across the capture duration
- Kerberos AS-REQ/TGS-REQ patterns consistent with credential testing
- LSARPC calls (frames involving lsarpc) — Local Security Authority queried for privilege information

---

## Reproducing This Analysis

All findings can be reproduced using the following tshark commands against `2023-07-Unit42-Wireshark-quiz.pcap`:

```bash
# Protocol hierarchy
tshark -r 2023-07-Unit42-Wireshark-quiz.pcap -q -z io,phs

# Top talkers
tshark -r 2023-07-Unit42-Wireshark-quiz.pcap -q -z conv,tcp

# All DNS queries
tshark -r 2023-07-Unit42-Wireshark-quiz.pcap \
  -Y "dns.flags.response == 0" \
  -T fields -e frame.number -e ip.src -e dns.qry.name

# HTTP requests with user agents
tshark -r 2023-07-Unit42-Wireshark-quiz.pcap \
  -Y "http.request" \
  -T fields -e frame.number -e ip.dst -e http.host \
  -e http.request.uri -e http.user_agent

# DRSUAPI (DCSync) traffic
tshark -r 2023-07-Unit42-Wireshark-quiz.pcap \
  -Y "drsuapi" \
  -T fields -e frame.number -e ip.src -e ip.dst -e drsuapi.opnum

# IcedID C2 traffic
tshark -r 2023-07-Unit42-Wireshark-quiz.pcap \
  -Y "ip.addr == 194.26.135.119" \
  -T fields -e frame.number -e frame.time_relative \
  -e ip.src -e ip.dst -e tcp.dstport -e tcp.len

# TLS Server Name Indicators
tshark -r 2023-07-Unit42-Wireshark-quiz.pcap \
  -Y "tls.handshake.type == 1" \
  -T fields -e frame.number -e ip.dst \
  -e tls.handshake.extensions_server_name
```

---

## Architecture Diff Summary

See `architecture/before.mmd` and `architecture/after.mmd` for full diagrams.

| Gap (Current) | Control (Proposed) | Priority |
|---|---|---|
| No egress filtering — workstation can reach any external IP on any port | NGFW with deny-by-default egress; approved domain allowlist | Critical |
| No DNS firewall — C2 domains resolve freely | DNS RPZ/firewall blocking known-malicious domains | Critical |
| DRSUAPI/DCSync allowed from workstations | Restrict DCSync to DC-to-DC traffic only via firewall rules | Critical |
| No PowerShell execution monitoring | PowerShell Script Block Logging + SIEM alerting | High |
| Flat internal network — workstations reach DC directly | East-west microsegmentation; jump host for privileged access | High |
| No EDR on workstations | EDR deployment with process and network telemetry | High |
| No IDS/NSM | Zeek + Suricata on network tap; baseline alerting | Medium |

---

## References
- Unit 42 IcedID Wireshark Quiz: https://unit42.paloaltonetworks.com/wireshark-quiz-icedid/
- MITRE ATT&CK T1003.006 — DCSync: https://attack.mitre.org/techniques/T1003/006/
- IcedID BackConnect C2 analysis (NETRESEC): https://www.netresec.com/?page=Blog&month=2021-04&post=Analysing-a-malware-PCAP-with-IcedID-and-Cobalt-Strike-traff
- NETRESEC — IcedID beaconing interval analysis: https://www.netresec.com/?page=Blog&year=2023
