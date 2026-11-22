# CyberSense — cybersecurity awareness workshop tool

CyberSense is a PHP and Bash workshop companion for supervised cybersecurity-awareness sessions. The repository contains a quiz/check-in page, server-side form handlers, an admin view, and launcher/cleanup scripts.

## Repository files

| File | Role |
|---|---|
| `cybersec_quiz.html` | Quiz/check-in page template |
| `template.php` | PHP entry template |
| `ip.php` | Visitor IP logging helper |
| `post.php` | Check-in submission handler |
| `admin.php` | Password-protected check-in view |
| `camphish.sh` | Workshop launcher script |
| `cleanup.sh` | Session cleanup script |

The launcher may create temporary runtime files and session data. These are not committed source files. Review the scripts and handlers before use to understand exactly what information is collected and where it is stored.

## Requirements

- PHP 8 or later
- Bash
- `wget`
- `qrencode` is optional
- A Cloudflare Tunnel binary may be downloaded by the launcher

## Responsible use and data handling

Use only in a workshop you administer, with clear advance notice and informed, affirmative consent from every participant. Do not impersonate a service, collect webcam images, credentials, or other sensitive data, or expose the session to people who have not consented. This repository includes an IP logging helper; disclose that collection before the session and keep any retained data access-controlled and short-lived. Obtain any required institutional approval, and delete session data when it is no longer needed.

Before deploying, inspect the current scripts, set appropriate access controls, and confirm the cleanup behavior against the files created during a session. Do not deploy this as a public production service.

## Project status

This is an educational project. The README describes the tracked files; it does not certify the application as secure or suitable for production.
