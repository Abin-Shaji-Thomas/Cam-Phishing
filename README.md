<div align="center">

# 🛡️ CyberSense — Cybersecurity Awareness Workshop Tool

**An educational check-in and quiz platform designed for cybersecurity awareness workshops and seminars.**

[![Developed by Abin Shaji Thomas](https://img.shields.io/badge/Developed%20by-Abin%20Shaji%20Thomas-0a66c2?style=flat-square&logo=linkedin)](https://www.linkedin.com/in/abin-shaji-thomas)
[![Portfolio](https://img.shields.io/badge/Portfolio-abinshajithomas.vercel.app-black?style=flat-square&logo=vercel)](https://abinshajithomas.vercel.app)
[![Instagram](https://img.shields.io/badge/Instagram-@abin__shaji__thomas-E1306C?style=flat-square&logo=instagram)](https://instagram.com/abin_shaji_thomas)
[![Educational Use Only](https://img.shields.io/badge/Purpose-Educational%20Only-blue?style=flat-square)](/) 
[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat-square&logo=php)](https://php.net)
[![Bash](https://img.shields.io/badge/Shell-Bash-4EAA25?style=flat-square&logo=gnu-bash)](/) 
[![Cloudflare Tunnel](https://img.shields.io/badge/Tunnel-Cloudflare-F48120?style=flat-square&logo=cloudflare)](https://developers.cloudflare.com/cloudflare-one/connections/connect-apps/)

</div>

---

## 📖 What Is This?

**CyberSense** is a workshop companion tool built for cybersecurity educators and event organizers. It is designed to be run by a workshop presenter on their own laptop, and shared with an audience via a QR code.

When students scan the QR code, they:
1. Complete a **10-question cybersecurity awareness quiz** (hosted on GitHub Pages / Vercel)
2. Are redirected to a **check-in page** where they enter their name and student ID
3. The presenter's **admin dashboard** shows all check-ins in real time

This tool is built for use in **auditoriums, classrooms, and seminars** where students primarily use mobile phones and tablets.

> **⚠️ Intended Use**: This tool is built exclusively for educational workshops. Misuse of any component is the sole responsibility of the operator.

---

## 🗂️ Project Structure

```
CyberSense/
├── camphish.sh          # Main launcher — starts PHP server + Cloudflare tunnel
├── cleanup.sh           # Wipes all captured data after the session
├── template.php         # Entry page (logs IP, redirects to quiz check-in)
├── ip.php               # Visitor IP logging (included by template.php)
├── post.php             # Handles student check-in form submission
├── admin.php            # Password-protected admin dashboard
├── cybersec_quiz.html   # The check-in page template (has VERCEL_QUIZ_URL placeholder)
└── index.html           # Quiz page (deploy this to GitHub Pages / Vercel separately)
```

**Files created at runtime (NOT committed to git):**

| File / Folder | Created By | Purpose |
|---------------|-----------|---------|
| `index.php` | `camphish.sh` | Generated from `template.php` with live tunnel URL |
| `index2.html` | `camphish.sh` | Generated from `cybersec_quiz.html` with live tunnel URL |
| `photos/` | `post.php` | Student check-in webcam captures |
| `ip_logs/` | `ip.php` | Per-visitor IP log files |
| `responses.jsonl` | `post.php` | All check-in records in JSON format |
| `saved.ip.txt` | `post.php` | Human-readable check-in log |
| `cloudflared` / `cloudflared.exe` | `camphish.sh` | Auto-downloaded Cloudflare binary |
| `qrcode.png` | `camphish.sh` | QR code for the current session |

---

## ⚙️ Prerequisites

### Required on the presenter's laptop:
| Tool | Install |
|------|---------|
| `php` (8.0+) | `sudo apt install php` / [windows.php.net](https://windows.php.net/download/) |
| `wget` | `sudo apt install wget` / included in Git Bash on Windows |
| `bash` | Pre-installed on Linux/macOS. Windows: use [Git Bash](https://git-scm.com/downloads) |
| `qrencode` *(optional)* | `sudo apt install qrencode` — generates a QR code PNG |

`cloudflared` is **auto-downloaded** on first run — no manual install needed.

---

## 🚀 How to Run

### 1. Clone the repository

```bash
git clone https://github.com/YOUR_USERNAME/CyberSense.git
cd CyberSense
```

### 2. Configure admin credentials

Open `admin.php` and set your own credentials at the top:

```php
define('ADMIN_USER', getenv('CYBERSENSE_USER') ?: 'YourUsername');
define('ADMIN_PASS', getenv('CYBERSENSE_PASS') ?: 'YourStrongPassword');
```

Or set environment variables before running:
```bash
export CYBERSENSE_USER="YourUsername"
export CYBERSENSE_PASS="YourStrongPassword"
```

### 3. Deploy the quiz page (one-time setup)

The `index.html` (quiz) is a **static page** that should be hosted separately for reliability:

- Push `index.html` to a **GitHub Pages** repo, or deploy to **Vercel / Netlify**
- Copy your hosted quiz URL (e.g. `https://yourusername.github.io/Quiz/`)

### 4. Launch the tool

```bash
bash camphish.sh
```

You'll be prompted to enter your quiz URL, then the script will:
- Start a local PHP server (8 worker processes)
- Download and launch a Cloudflare tunnel
- Generate a QR code (`qrcode.png`)
- Print both the public URL and the admin panel URL

```
[*] Direct link (share this):    https://xxxx-xxxx.trycloudflare.com
[*] Admin panel (local):          http://localhost:3333/admin.php
[*] Admin panel (any device):     https://xxxx-xxxx.trycloudflare.com/admin.php
[*] QR code saved: qrcode.png
```

### 5. Display the QR code

Open `qrcode.png` and display it on the projector screen. Students scan it with their phones.

### 6. Monitor check-ins

Open the admin panel URL in your browser. Default credentials (change these!):
- **Username:** `admin`
- **Password:** `changeme123`

### 7. Clean up after the session

```bash
bash cleanup.sh
```

This wipes all photos, logs, captured IPs, and generated files.

---

## 🔄 How It Works (Full Flow)

```
Student scans QR code
        │
        ▼
https://xxxx.trycloudflare.com   ← Cloudflare edge
        │
        ▼  (tunneled to your laptop)
template.php / index.php          ← Logs visitor IP
        │
        ▼  (redirects to)
https://your-quiz-url/            ← GitHub Pages quiz (10 questions)
        │
        ▼  (quiz submits form to)
cybersec_quiz.html / index2.html  ← Check-in form (name, student ID, webcam)
        │
        ▼
post.php                          ← Saves response + photo to disk
        │
        ▼
admin.php                         ← Presenter views all entries live
```

---

## 🖥️ Platform Support

| OS | Works? | Notes |
|----|--------|-------|
| Linux (Kali, Ubuntu, Debian) | ✅ | Full support, 8 PHP workers |
| macOS | ✅ | Intel + Apple Silicon auto-detected |
| Windows (Git Bash) | ✅ | Uses single PHP worker; run via Git Bash |

---

## 🔧 Troubleshooting

### Cloudflare tunnel not generating a URL
```bash
# Kill any leftover processes first
killall cloudflared php 2>/dev/null

# Then restart
bash camphish.sh
```

If it still fails, fix DNS:
```bash
echo "nameserver 1.1.1.1" | sudo tee /etc/resolv.conf
```

### Error 1033 on student devices
The tunnel on the presenter's laptop has died. Simply restart `bash camphish.sh` — a new URL is generated. Share the new QR code.

### Keep the session stable (recommended)
Run inside `tmux` so the session survives terminal closes:
```bash
tmux new -s cybersense
bash camphish.sh
# Detach: Ctrl+B then D
# Reattach: tmux attach -t cybersense
```

### Admin panel shows no entries
Make sure students are using the generated Cloudflare URL (not localhost). Check that `responses.jsonl` exists and has content.

---

## 📁 After the Workshop

1. Download `responses.jsonl` and `photos/` for your records if needed
2. Run `bash cleanup.sh` to wipe everything from disk
3. The Cloudflare tunnel URL expires when you stop the script — no further cleanup needed on Cloudflare's end

---

## 👤 About the Developer

This tool was fully designed, developed, and customized by **Abin Shaji Thomas** for educational cybersecurity workshops.

| | |
|---|---|
| 💼 **LinkedIn** | [Abin Shaji Thomas](https://www.linkedin.com/in/abin-shaji-thomas) |
| 📸 **Instagram** | [@abin_shaji_thomas](https://instagram.com/abin_shaji_thomas) |
| 🌐 **Portfolio** | [abinshajithomas.vercel.app](https://abinshajithomas.vercel.app) |
| 🏫 **Institution** | URK College of Engineering & Technology |

---

> *CyberSense is built for educational use only. Designed and maintained by Abin Shaji Thomas.*
