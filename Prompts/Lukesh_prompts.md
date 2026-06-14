# Prompt - 1 (Workstream - A)
## Date - 24 May 2026

## ROLE
You are a senior threat analyst and digital forensics expert with deep expertise in network forensics, intrusion analysis, and malware behavior. You operate under the rigor of NIST SP 800-86 and follow PICERL (Preparation, Identification, Containment, Eradication, Recovery, Lessons Learned) as your incident response framework.

---

## CONTEXT
Project: Network Forensics | Workstream A
Artifact under investigation: A potentially malicious PCAP file
Tool: Wireshark (latest stable)
Goal: Systematically triage the PCAP to uncover adversarial activity across the full attack lifecycle — from initial reconnaissance through exfiltration.

---

## TASK
Provide a structured, step-by-step Wireshark investigation methodology for analyzing a malicious PCAP file. Your analysis must cover detection of the following threat categories:

  1. C2 Beaconing
  2. Lateral Movement
  3. Data Exfiltration
  4. Network Scanning / Reconnaissance
  5. Credential Abuse

For each threat category, provide:
  - Specific Wireshark display filters (exact syntax)
  - Key IOCs (Indicators of Compromise) to look for
  - Behavioral patterns that confirm maliciousness
  - Example packet-level evidence to validate findings

---

## CONSTRAINTS
- Prioritize triage steps a forensic analyst should run FIRST on an unknown PCAP
- Include both quick-win filters and deeper analysis techniques
- Assume Wireshark is the primary tool (no CLI-only tools like tshark unless supplementary)
- Flag any steps that require GeoIP, TLS decryption, or additional plugins

---

## OUTPUT FORMAT
Structure your response as follows:

### Phase 0 — Initial Triage (run before any category-specific analysis)
### Phase 1 — C2 Beaconing Detection
### Phase 2 — Lateral Movement Detection
### Phase 3 — Data Exfiltration Detection
### Phase 4 — Scanning & Reconnaissance Detection
### Phase 5 — Credential Abuse Detection
### Phase 6 — IOC Summary Table

Each phase must include:
  ✦ Objective
  ✦ Wireshark filters (code-formatted)
  ✦ What to look for (behavioral indicators)
  ✦ Severity signal (Low / Medium / High / Critical)

End with a prioritized analyst action checklist.


# Prompt - 2 (Workstream - A)
## Date - 26 May 2026

## ROLE
You are a senior network forensics analyst and threat hunter with deep expertise in Wireshark, packet-level traffic analysis, and malicious traffic pattern recognition across HTTP, DNS, TLS, SMTP, FTP, and proprietary C2 protocols.

---

## CONTEXT
- Tool       : Wireshark (GUI) — display filters and coloring rules
- Artifact   : Unknown / potentially malicious PCAP file
- Purpose    : Threat hunting and incident discovery for research
- Reuse goal : Filters must be saveable as a reusable Wireshark filter library
- Skill level: Intermediate security analyst

---

## TASK
Produce a curated, reusable Wireshark filter and command reference covering:

  1. Initial triage filters  — quick overview of all traffic categories
  2. HTTP analysis           — suspicious requests, unusual user-agents, POST exfiltration
  3. DNS analysis            — tunneling, DGA domains, high-volume queries, TXT abuse
  4. TLS/HTTPS analysis      — self-signed certs, unusual SNI, JA3 fingerprinting hints
  5. SMTP / FTP / SMB        — credential abuse, lateral movement, file staging
  6. C2 beaconing patterns   — periodic intervals, long connections, unusual port usage
  7. Data exfiltration       — large outbound transfers, encoding patterns, DNS/ICMP abuse
  8. IOC pivot filters       — filter by IP, domain, port, MAC once an IOC is found

For each category provide:
  - Display filter (exact Wireshark syntax, copy-paste ready)
  - What it detects (one line, no filler)
  - Save-as name for the Wireshark filter toolbar

---

## CONSTRAINTS
- Wireshark display filter syntax only (no tshark CLI unless explicitly marked as bonus)
- Every filter must be tested-syntax safe — no pseudo-code or placeholders
- Where a value must be substituted (e.g. IP address), mark it clearly as 
- Keep descriptions to one line — purpose, not explanation
- Group filters by threat category, not alphabetically

---

## OUTPUT FORMAT
Structure output as follows:

### Category N — [Threat Category Name]
| Filter name (save-as label) | Display filter | Detects |
|---|---|---|

After all categories:

### Wireshark save instructions
Step-by-step: how to save each filter to the Wireshark filter toolbar and export the full set as a filters file for reuse across sessions.

### Quick-reference cheat sheet
One compact table: Category → Top filter → Primary IOC signal.

End with a one-paragraph analyst note on triage order — which category to run first on an unknown PCAP and why.




# Prompt - 3 (Workstream - B)
## Date - 29 May 2026

## ROLE
You are a senior ethical hacking instructor and DevSecOps engineer with expertise in vulnerable-by-design lab environments and Docker-based deployments on macOS.

---

## CONTEXT
- Platform : macOS (Apple Silicon or Intel — note any architecture differences)
- Tool     : Docker Desktop (latest stable)
- Labs     : DVWA (Damn Vulnerable Web Application) + OWASP Juice Shop
- Purpose  : Local ethical hacking practice environment — isolated, not internet-exposed
- User     : Intermediate security practitioner, first-time setup of these labs

---

## TASK
Provide a complete, beginner-safe setup guide covering:

  1. Prerequisites — Docker Desktop install/verify on macOS
  2. DVWA setup via Docker
     - Pull image, run container, port mapping, first-login credentials
     - Set security level to Low; enable all vulnerability modules
  3. Juice Shop setup via Docker
     - Pull image, run container, port mapping, access URL
     - First-time walkthrough: dashboard, challenge tracker, score board
  4. Post-setup hygiene
     - How to stop/start/remove containers safely
     - Warning: never expose these on a public/shared network

---

## CONSTRAINTS
- macOS-specific commands only (no Linux/Windows variants)
- All docker commands must be copy-paste ready (exact flags, ports, image tags)
- Flag any Apple Silicon (M1/M2/M3) specific gotchas (platform flag, Rosetta)
- Keep explanations concise — one purpose per step, no filler prose

---

## OUTPUT FORMAT
Use this structure exactly:

### 0 — Prerequisites checklist
### 1 — DVWA: install & first run
### 2 — Juice Shop: install & first run
### 3 — First-time usage guide (both labs)
### 4 — Container management quick-reference
### 5 — Security reminder

Each section must include:
  ✦ Goal (one line)
  ✦ Commands (code-formatted, copy-paste ready)
  ✦ Expected output or verification step
  ✦ Common errors + fix (if applicable)

End with a side-by-side comparison table: DVWA vs Juice Shop (focus area, difficulty, best-for).



# Prompt - 4 (Workstream - B)
## Date - 30 May 2026

## ROLE
You are a senior application security engineer and DevSecOps practitioner specializing in SAST tooling, secure code review, and vulnerability discovery in intentionally vulnerable applications. You have hands-on expertise with Semgrep, OWASP rulesets, and Python/PHP/JavaScript static analysis.

---

## CONTEXT
- Tool         : Semgrep (OSS CLI — semgrep.dev)
- Platform     : macOS (Apple Silicon or Intel — flag any chip-specific steps)
- Target apps  : DVWA (PHP) + OWASP Juice Shop (Node.js / TypeScript)
- Purpose      : First-time SAST setup and scan for security research and lab practice
- Skill level  : Intermediate security analyst, new to Semgrep
- Scope        : Local source code scan only — not CI/CD pipeline integration

---

## TASK
Provide a complete, step-by-step Semgrep SAST guide covering:

  1. Prerequisites
     - Python / pip or Homebrew install path on macOS
     - Semgrep OSS install and version verification

  2. Clone source code (both targets)
     - DVWA   : GitHub repo URL, clone command, directory note
     - Juice Shop : GitHub repo URL, clone command, directory note

  3. Semgrep ruleset selection
     - Recommended rulesets for PHP (DVWA) — e.g. p/php, p/owasp-top-ten
     - Recommended rulesets for Node.js/JS (Juice Shop) — e.g. p/nodejs, p/javascript
     - How to browse and add community rulesets from semgrep.dev/r

  4. Running the first scan (per target)
     - Full scan command with flags (--config, --output, --json/--sarif)
     - How to scope scan to a subdirectory or single file
     - Expected output structure explanation

  5. Reading and triaging findings
     - Anatomy of a Semgrep finding (rule ID, severity, file, line, message)
     - How to filter by severity (ERROR / WARNING / INFO)
     - Top 5 finding types to expect in DVWA (SQLi, XSS, file inclusion, command injection, hardcoded creds)
     - Top 5 finding types to expect in Juice Shop (NoSQL injection, XSS, JWT abuse, path traversal, prototype pollution)

  6. Saving and reusing scan configurations
     - How to write a .semgrepignore file
     - How to save a custom scan command as a shell alias or Makefile target for reuse

---

## CONSTRAINTS
- macOS CLI commands only — no Linux/Windows variants unless marked [cross-platform]
- All commands must be copy-paste ready — no pseudo-code, no  left undefined
- One objective per step — no combined multi-action steps
- Flag any Semgrep Cloud / login-gated features clearly as [Semgrep Cloud only]
- Keep descriptions to one line of purpose — no padding prose

---

## OUTPUT FORMAT
Structure output as follows:

### Step 0 — Prerequisites checklist
### Step 1 — Install Semgrep on macOS
### Step 2 — Clone DVWA source code
### Step 3 — Clone Juice Shop source code
### Step 4 — Select rulesets (DVWA + Juice Shop)
### Step 5 — Run first scan: DVWA
### Step 6 — Run first scan: Juice Shop
### Step 7 — Triage findings: what to look for
### Step 8 — Save and reuse scan config

Each step must include:
  ✦ Objective (one line)
  ✦ Command(s) — code-formatted, copy-paste ready
  ✦ Expected output or verification signal
  ✦ Common error + fix (where applicable)

End with a side-by-side comparison table:
DVWA vs Juice Shop — language, primary vuln classes, recommended ruleset, expected finding count range.



# Prompt -5 (Git and Github)
## Date - 02 June 2026

## ROLE
You are a senior software engineer and open-source contributor with 15+ years of Git experience. You teach developers from zero — using plain language, mental models before commands, and real-world contributor workflows rather than textbook theory.

---

## CONTEXT
- Tool       : Git (CLI) + GitHub (web + remote)
- Platform   : macOS / Linux (flag Windows differences only where they matter)
- User       : Complete beginner — first time using Git and GitHub
- Goal       : Three outcomes in one guide —
    (1) Push files from local to GitHub for the first time
    (2) Contribute to an existing GitHub project as a first-time contributor
    (3) Build a reusable command reference for active daily Git use
- Tone       : Simple, direct — no jargon without immediate plain-English definition

---

## TASK
Produce a beginner-complete Git + GitHub guide structured across three tracks:

  TRACK A — First push: local → GitHub
    1. Install Git and verify (macOS / Linux)
    2. Configure identity (git config — name, email, default branch)
    3. Create a local repo (git init, first file, .gitignore)
    4. Stage and commit (git add, git commit — explain the staging area in one sentence)
    5. Create a GitHub repo (UI steps — public vs private, README toggle)
    6. Connect and push (git remote add origin, git push -u origin main)
    7. Verify on GitHub

  TRACK B — First contribution to an existing project
    1. Fork the repo (GitHub UI)
    2. Clone your fork locally (git clone)
    3. Create a feature branch (git checkout -b)
    4. Make changes, stage, and commit
    5. Push branch to your fork (git push origin )
    6. Open a Pull Request (GitHub UI — title, description, base branch)
    7. Keep fork in sync with upstream (git remote add upstream, git fetch, git merge)

  TRACK C — Essential command reference (daily use)
    Group commands by workflow moment:
    - Status and inspection  : git status, git log --oneline, git diff
    - Branching              : git branch, git checkout, git switch, git merge
    - Remote sync            : git fetch, git pull, git push
    - Undo and recover       : git restore, git revert, git stash
    - Cleanup                : git branch -d, git remote prune origin

---

## CONSTRAINTS
- Explain the mental model BEFORE the command — one sentence max per concept
- All commands copy-paste ready — no pseudo-code, no  left undefined
- Flag GitHub UI steps clearly as [GitHub UI] vs CLI commands
- No Git GUIs (GitHub Desktop, GitKraken) — CLI only
- Beginner-safe: never introduce rebase, force-push, or cherry-pick without an explicit danger warning
- One action per step — no multi-command steps bundled together

---

## OUTPUT FORMAT

### Track A — Your first push (local → GitHub)
### Track B — Your first contribution (fork → PR)
### Track C — Command reference (grouped by workflow moment)

Each step in Tracks A and B must include:
  ✦ What this does (one line — plain English)
  ✦ Command or action — code-formatted
  ✦ Expected output or confirmation signal
  ✦ Beginner trap to avoid (where applicable)

Track C must be formatted as a compact table:
  | Command | What it does | When to use it |

End with a one-paragraph mental model summary:
"Git in three sentences" — the simplest possible explanation of how local, staging, and remote relate to each other.


# Prompt - 6 (Workstream - B)
## Date - 05 June 2026

# DVWA Semgrep Remediation Playbook Prompt

## Role
Senior Application Security Engineer

## Context
The user is running Damn Vulnerable Web Application (DVWA) inside a Docker container on macOS and has executed a Semgrep SAST (Static Application Security Testing) scan. You will act as an expert guide to help remediate the detected security flaws.

## Task
For each Semgrep finding provided by the user, deliver a comprehensive remediation analysis covering the following 8 components:

1. **Vulnerability Explanation:** Clear, beginner-friendly breakdown of the flaw and its impact.
2. **Vulnerable Code Snippet:** Display the specific lines of insecure code.
3. **Secure Patched Code Snippet:** Provide the securely refactored, patched code.
4. **Fix Rationale:** Explain exactly why the fix addresses the root cause.
5. **Security Mapping:** Classify the issue using the OWASP Top 10 framework and specific CWE identifiers.
6. **File Target:** Specify the exact file location/path within DVWA to update.
7. **Containerized Validation:** Provide the precise Docker commands required to apply the patch to the container and manually test it.
8. **SAST Verification:** Demonstrate the exact Semgrep command to rerun and verify that the vulnerability is resolved.

## Focus Areas
Optimize analysis for these core vulnerability categories:
* SQL Injection (SQLi)
* Command Injection
* Cross-Site Scripting (XSS)
* File Inclusion (LFI/RFI)
* Path Traversal
* Arbitrary File Upload Issues
* Cross-Site Request Forgery (CSRF)
* Authentication Weaknesses

## Output Format
Structure your response strictly following this sequential flow for each finding:

`Finding` → `Risk` → `Vulnerable Code` → `Secure Fix` → `Validation Steps` → `Semgrep Verification` → `Best Practices`

## Constraints & Style Instructions
* **Tone:** Professional, practical, authoritative, yet accessible.
* **Clarity:** Keep technical explanations concise and highly actionable. Avoid fluff.
* **Environment:** Ensure all Docker and file commands are fully compatible with macOS workflows.


# Prompt - 7 (Workstream - C)
## Date - 08 June 2026

## Role
Senior Cybersecurity Architect & Threat Modeling Specialist (20+ Years Experience)

## Context
You are creating a master-class educational and research guide on organizational threat modeling. The target audience needs a clear, practical execution framework rather than high-level theory.

## Task
Provide a definitive, step-by-step guide to executing threat modeling within an enterprise environment. Your guide must cover the following four core modules:

### 1. Foundations of Enterprise Threat Modeling
* A streamlined lifecycle for rolling out threat modeling across an organization.
* Core stakeholder alignment (Security, Dev, Ops) and minimal essential assets to track.

### 2. Open-Source Tooling: CAIRIS Framework
* Practical steps to utilize the CAIRIS (Computer Aided Integration of Requirements and Information Security) platform for threat analysis.
* How to define assets, personas, and architectural representations within the tool.

### 3. Threat Identification & Mapping Framework (STRIDE + MITRE ATT&CK)
* Methodology for applying the STRIDE mnemonic to identify vulnerabilities.
* A concrete example mapping a STRIDE-identified threat (e.g., Spoofing or Tampering) directly to a specific MITRE ATT&CK technique/tactics matrix.

### 4. Defensive Engineering & Critical Controls
* How to translate identified threats into actionable security controls.
* Concrete prevention strategies and remediation workflows to secure the organization long-term.

## Constraints & Style Instructions
* **Tone:** Professional, authoritative, and highly technical yet accessible.
* **Format:** Use markdown tables for the STRIDE-to-MITRE mapping, distinct headers, and bulleted lists for workflows.
* **Essentialism:** Avoid fluff, lengthy introductions, or meta-commentary. Move straight to the actionable guide.


  
