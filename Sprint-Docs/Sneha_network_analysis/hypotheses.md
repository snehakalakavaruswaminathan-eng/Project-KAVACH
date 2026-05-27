
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
