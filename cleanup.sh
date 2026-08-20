#!/bin/bash
# CyberSense Cleanup Script
# Developed by Abin Shaji Thomas | abinshajithomas.vercel.app
# Clears all captured data, photos, QR codes, logs, and generated files

GREEN='\e[1;92m'
YELLOW='\e[1;93m'
RESET='\e[0m'

echo ""
printf "${GREEN}[*] CyberSense Cleanup${RESET}\n"
echo "────────────────────────────────"

removed=0

remove_files() {
  local pattern="$1"
  local label="$2"
  local count
  count=$(ls $pattern 2>/dev/null | wc -l)
  if [[ $count -gt 0 ]]; then
    rm -f $pattern 2>/dev/null
    printf "${GREEN}[+]${RESET} Removed ${count} ${label}\n"
    removed=$((removed + count))
  fi
}

# Photos folder (captured images)
if [[ -d "photos" ]]; then
  count=$(ls photos/*.png 2>/dev/null | wc -l)
  if [[ $count -gt 0 ]]; then
    rm -f photos/*.png 2>/dev/null
    printf "${GREEN}[+]${RESET} Removed ${count} photo(s) from photos/\n"
    removed=$((removed + count))
  fi
fi

# Root-level stray cam*.png (legacy)
remove_files "cam*.png" "stray captured image(s)"

# QR code
remove_files "qrcode*.png" "QR code(s)"
remove_files "qr*.png" "QR image(s)"

# Response data
if [[ -f "responses.jsonl" ]]; then
  lines=$(wc -l < responses.jsonl)
  rm -f responses.jsonl
  printf "${GREEN}[+]${RESET} Removed responses.jsonl (${lines} records)\n"
  removed=$((removed + 1))
fi

# Quiz answers (legacy)
remove_files "quiz_answers.jsonl" "quiz answers file(s)"

# Generated files (recreated by camphish.sh each run)
remove_files "index.php" "generated index.php"
remove_files "index2.html" "generated index2.html"
remove_files "index3.html" "generated index3.html"

# Logs
remove_files "Log.log" "capture log(s)"
remove_files "*.log" "log file(s)"
remove_files ".cloudflared.log" "cloudflared log"
remove_files "ip.txt" "IP log(s)"

# Concurrency logs directory
if [[ -d "ip_logs" ]]; then
  count=$(ls ip_logs/*.txt 2>/dev/null | wc -l)
  rm -rf ip_logs
  printf "${GREEN}[+]${RESET} Removed ip_logs directory (${count} files)\n"
  removed=$((removed + 1))
fi

# Location files (legacy)
remove_files "location_*.txt" "location file(s)"
remove_files "current_location.txt" "location tracker"
remove_files "current_location.bak" "location backup"
remove_files "saved.ip.txt" "saved IP file"
remove_files "saved.locations.txt" "saved locations file"

# Saved locations directory (legacy)
if [[ -d "saved_locations" ]]; then
  rm -rf saved_locations
  printf "${GREEN}[+]${RESET} Removed saved_locations directory\n"
  removed=$((removed + 1))
fi

echo "────────────────────────────────"
if [[ $removed -eq 0 ]]; then
  printf "${YELLOW}[!] Nothing to clean — already clean.${RESET}\n"
else
  printf "${GREEN}[✓] Done! Removed ${removed} item(s) total.${RESET}\n"
fi
echo ""