
# Investigative Hypotheses

### Hypothesis
ICEID performed authenticated Active Directory reconnaissance.

### Supporting Evidence
- LDAP bindRequest using SASL/GSS-SPNEGO in Frame 189
- Netlogon discovery information exposed in Frame 18
- Kerberos authentication negotiation observed

### Contradicting Evidence
- Traffic may partially overlap with legitimate Windows authentication workflows

### Confidence Level
High

### Required Validation Steps
- Inspect LDAP search requests for user/group enumeration
- Identify LDAP search filters and attributes

### Relevant Packets
- Frame 18
- Frame 189

### Analyst Notes
Evidence supports authenticated interaction with AD infrastructure.

---

### Hypothesis
SMB authentication was used for potential lateral movement preparation.

### Supporting Evidence
- SMB2 Session Setup Request toward Domain Controller
- Kerberos-backed SMB negotiation

### Contradicting Evidence
- No ADMIN$ access observed
- No SMB file writes observed
- No service-creation traffic identified

### Confidence Level
Medium

### Required Validation Steps
- Review SMB Tree Connect requests
- Search for IPC$, SYSVOL, and NETLOGON access

### Relevant Packets
- Frame 194

### Analyst Notes
Evidence supports SMB authentication activity but not confirmed lateral movement.

---

### Hypothesis
Kerberos trust relationships were abused after compromise.

### Supporting Evidence
- Kerberos PREAUTH_REQUIRED negotiation
- SPNEGO authentication workflow
- LDAP + SMB authentication correlation

### Contradicting Evidence
- Kerberos negotiation itself is expected in enterprise AD environments

### Confidence Level
Medium-High

### Required Validation Steps
- Review TGS requests
- Identify service-ticket enumeration patterns

### Relevant Packets
- Frame 195

### Analyst Notes
Observed traffic aligns with identity-centric attack progression.

---

### Hypothesis
Initial Delivery via Malspam with HTTP Redirect Chain. The ICEID payload was delivered through a phishing email containing a URL that redirected through multiple HTTP stages (main.php) to a Firebase Storage-hosted ZIP archive, which contained the IcedID installer.

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
