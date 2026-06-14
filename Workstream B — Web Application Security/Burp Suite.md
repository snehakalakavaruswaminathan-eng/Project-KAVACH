# Burp Suite

# Application Security Testing & Vulnerability Playbook

This repository contains step-by-step documentation for identifying, exploiting, and verifying common web application vulnerabilities using **Burp Suite** across target environments like **DVWA** and **OWASP Juice Shop**.

---

## Finding F-01 — SQL Injection in DVWA

### Step 1 — Navigate to the Target
1. Open the Burp browser and navigate to: `http://localhost:8080`
2. Login using the credentials: **Username:** `admin` | **Password:** `password`
3. Click **Create / Reset Database**.
4. Log in again, navigate to the **DVWA Security** tab, and set the security level to **Low**.

### Step 2 — Find the Request in HTTP History
1. Navigate to the **Proxy** tab -> **HTTP History** sub-tab in Burp Suite.
2. In the target browser, go to **DVWA** -> **SQL Injection**.
3. Input `1` into the *User ID* input box and click **Submit**.
4. Observe the captured `GET` request appear in your HTTP History log.

### Step 3 — Send to Repeater
1. Right-click the captured SQLi request in the HTTP History list.
2. Select **Send to Repeater** (or press `Ctrl + R` / `Cmd + R`).
3. Click on the **Repeater** tab at the top of the interface.

### Step 4 — Modify and Attack
In the Repeater panel, locate the baseline request parameter string:

```http
GET /vulnerabilities/sqli/?id=1&Submit=Submit HTTP/1.1
```

##### Authentication Bypass / Entry Enumeration Test
Change the parameter `id=1` to the following payload:
```sql
id=1' OR '1'='1
```
Click **Send**. Review the **Response** panel on the right side to verify that account details for all users are returned.

##### Schema Data Extraction via UNION
Modify the `id` parameter again to perform a structural data extraction:
```sql
id=1' UNION SELECT user,password FROM users-- -
```
Click **Send**. Review the output response to view application usernames alongside their respective MD5 password hashes.

---

### Finding F-02 — XSS Stored in DVWA

#### Step 1 — Navigate to the Target
* In the Burp browser, navigate to the **DVWA** sidebar and select **XSS (Stored)**.

#### Step 2 — Baseline Payload Validation
In the *Message* field text box, input the following script payload:
```html
<script>alert('KAVACH-XSS')</script>
```
Click **Sign Guestbook**. Verify that the JavaScript alert dialog fires instantly in the browser environment.

#### Step 3 — Capture Intercept for Documentation
1. Inside Burp Suite, navigate to **Proxy** -> **Intercept** and click **Intercept is OFF** to toggle it to **Intercept is ON**.
2. Re-populate the form fields with the identical XSS script payload and click **Submit**.
3. Analyze the raw state transaction frozen within the Intercept tab view.
4. Click **Forward** to release the request, then switch **Intercept to OFF**.

#### Step 4 — Verify Exploitation Persistence
* Reload or navigate away and return to the **XSS (Stored)** module page. Verify that the JavaScript injection fires execution persistently upon every distinct page load. 
---

### Finding F-03 — XSS Reflected in DVWA

#### Step 1 — Navigate to the Target
* Navigate to the **DVWA** sidebar and select **XSS (Reflected)**.

#### Step 2 — Attack execution via Repeater
1. Enter a placeholder string into the input name box and click **Submit**.
2. Locate the generated transaction inside **Proxy** -> **HTTP History** and send it to **Repeater**.
3. In the Repeater panel, target the `name` parameter value and substitute it with:
   ```html
   name=<script>alert('REFLECTED-XSS')</script>
   ```
4. Click **Send**.
5. Inside the **Response** panel, trigger the search bar (`Ctrl + F` / `Cmd + F`) and search for `<script>`. Verify that the payload is returned dynamically unescaped directly inside the application raw HTML payload code.

---

### Finding F-04 — IDOR in Juice Shop

#### Step 1 — Create Dual Test Accounts
1. In the Burp browser environment, go to: `http://localhost:3000`
2. Complete registration for **Account A:** `userA@test.com` (Password: `Test1234!`)
3. Complete registration for **Account B:** `userB@test.com` (Password: `Test1234!`)

#### Step 2 — Basket State Creation
1. Log in securely as **User A**.
2. Select any arbitrary product asset catalog item and add it to the active shopping basket.
3. Click the navigation icon to view the active basket contents.

#### Step 3 — Map Target API Endpoint
1. Open **Proxy** -> **HTTP History** in Burp Suite.
2. Filter or search to locate the targeted REST api transitional vector path:
   ```http
   GET /rest/basket/1 HTTP/1.1
   ```
   *Note: The trailing index integer points explicitly to the primary unique identifier key tracking User A's basket.*

#### Step 4 — Manipulate Parameter ID in Repeater
1. Right-click the identified endpoint item log and select **Send to Repeater**.
2. Inside the active Repeater view panel, manually modify the path parameters from `/rest/basket/1` to target index `/rest/basket/2`.
3. Click **Send**.
4. Observe the data object structural population payload to confirm reading unauthorized active private basket line-items belonging to **User B**.

---

### Finding F-05 — Authentication Failure in DVWA (Brute Force)

#### Step 1 — Capture Login Interface Transaction
1. In the application workspace dashboard, navigate to **DVWA** -> **Brute Force**.
2. Enter fallback test input data inside both the Username and Password fields, then click **Login**.
3. Track the corresponding logged dynamic URI endpoint pattern inside **Proxy** -> **HTTP History**:
   ```http
   GET /vulnerabilities/brute/?username=admin&password=wrongpass&Login=Login
   ```
4. Right-click this item block sequence and choose **Send to Intruder**.

#### Step 2 — Configure Target Intruder Positions
1. Click into the main **Intruder** dashboard tab workspace.
2. Select the **Positions** sub-tab. Observe automated variable assignment wrapping indicated by green marker highlight lines (`§`).
3. Click the **Clear §** command button to flush default positions.
4. Highlight only the value argument string representing your password parameter (`wrongpass`).
5. Click **Add §** to encapsulate this variable block. It should appear as: `§wrongpass§`.

#### Step 3 — Attach Wordlist Payloads
1. Navigate across to the active **Payloads** configuration sub-tab option.
2. Confirm the payload type option configuration is set to **Simple list**.
3. Click the **Load** option file picker and source your local system tracking dictionaries (e.g., `/usr/share/wordlists/rockyou.txt`), or manually construct an array tracking standard fallback entry values:
   * `password`
   * `admin`
   * `123456`
   * `admin123`
   * `letmein`

#### Step 4 — Launch Attack & Analyze Discrepancies
1. Click the top-right **Start Attack** control button block.
2. Monitor progress trends directly within the real-time runtime results window.
3. Track variance anomalies across the tracking **Length** column entries.
4. Note that the authentic password target string entry (`password`) displays a distinctly contrasting response length variance compared to standard tracking invalid attempts.

---

### Finding F-06 — SQL Injection in Juice Shop Login

#### Step 1 — Target Login Endpoint Interface
* Open the Burp browser environment and access the target endpoint interface portal: `http://localhost:3000/#/login`

#### Step 2 — Toggle Intercept Mode Active
* Inside Burp Suite, select **Proxy** -> **Intercept** and ensure **Intercept is ON** is enabled.

#### Step 3 — Inject Login Form Vector
1. Within the UI web application email field box, supply the target evasion payload query:
   ```sql
   ' OR 1=1--
   ```
2. Enter any arbitrary dummy value inside the password text area input box.
3. Click the interactive user **Login** interface element button.

#### Step 4 — Inspect Intercepted JSON Structure
Review the raw network payload layout format captured in the active Intercept window view:

```json
POST /rest/user/login HTTP/1.1
Host: localhost:3000
Content-Type: application/json

{
  "email": "' OR 1=1--",
  "password": "anything"
}
```
Click **Forward** to release the payload string modification to the back-end application parser.

#### Step 5 — Verify Response Evasion State
1. Open the historical structural logs inside **Proxy** -> **HTTP History**.
2. Target the outbound `POST /rest/user/login` tracking element line entry and inspect its respective Response object data payload details.
3. Confirm the successful authorization validation bypass verified by the active instantiation generation of an administrative class **JWT (JSON Web Token)** structural assignment returned from the remote API interface.


