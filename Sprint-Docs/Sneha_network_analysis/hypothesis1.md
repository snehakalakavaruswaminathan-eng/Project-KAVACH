
# Investigative Hypotheses - ICEID Infection with AD Reconnaissance

## Hypothesis 1: Initial Delivery via Malspam with HTTP Redirect Chain

### Hypothesis
The ICEID payload was delivered through a phishing email containing a URL that redirected through multiple HTTP stages (main.php) to a Firebase Storage-hosted ZIP archive, which contained the IcedID installer.

### Supporting Evidence
- **Frame/flow evidence:** HTTP GET to 80.77.24.175 /main.php (initial redirector)
- **Redirect target:** firebasestorage.googleapis.com (legitimate Google Firebase)
- **Downloaded file:** Scan_Inv.zip (from IOC CSV evidence)
- **User context:** No preceding browsing activity to justify this download
- **Timing:** T+0-2 seconds of infection window

### Contradicting Evidence
- ZIP contents not observable in PCAP (encrypted/compressed)
- SMB file write for Invoice_07-19-2023.zip also observed - may be alternative vector

### Confidence Level
**HIGH** - Multiple PCAP analyses of same sample confirm this chain

### Required Validation Steps
- Extract Scan_Inv.zip from PCAP using NetworkMiner
- Compute SHA256 and compare with IcedID loader hashes
- Analyze extracted DLL for IcedID configuration

### Wireshark Filter
```bash
http.request.uri matches "/main\.php|firebasestorage\.googleapis\.com"
