
# ICEID Rapid Triage Notes

## Executive Summary
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

## High Confidence Findings
- LDAP bind requests using SASL/GSS-SPNEGO observed from 10.7.10.47 to Domain Controller.
- Kerberos pre-authentication negotiation observed via KRB-ERROR PREAUTH_REQUIRED.
- SMB2 Session Setup Requests toward Domain Controller over TCP/445.
- NBNS workstation registration broadcasts from DESKTOP-9PEA63H.
- Internal AD discovery activity strongly suggests post-compromise reconnaissance.

## Potential Lateral Movement Indicators
- SMB2 Session Setup to Domain Controller
- LDAP authenticated queries
- Kerberos service negotiation
- NetBIOS workstation advertisement

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
## Immediate Containment Recommendations
- Isolate host 10.7.10.47
- Reset credentials associated with authenticated sessions
- Audit Kerberos TGT/TGS activity
- Block suspicious outbound HTTP/HTTPS
- Review SMB administrative share access
- Disable legacy NBNS/LLMNR where possible

## Key Packet References
- Frame 18: LDAP Netlogon response
- Frame 19: NBNS workstation registration
- Frame 189: LDAP SASL bind request
- Frame 194: SMB2 Session Setup
- Frame 195: Kerberos PREAUTH_REQUIRED response

## Quick Validation Filters
- ldap
- nbns
- smb2
- smb2.cmd == 5
- smb2.tree contains "IPC$"
- kerberos
- kerberos.msg_type == 1
- dcerpc
