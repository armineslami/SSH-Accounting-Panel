#!/bin/sh

# Get CPU usage
cpuUsage=$(top -b -n2 -p 1 | fgrep "Cpu(s)" | tail -1 | awk -F'id,' -v prefix="$prefix" '{ split($1, vs, ","); v=vs[length(vs)]; sub("%", "", v); printf "%s%.1f\n", prefix, 100 - v }')

# Get memory and swap usage
free_output=$(free -m)
memory=$(echo "$free_output" | awk '/^Mem:/ {print $2}')
memoryUsage=$(echo "$free_output" | awk '/^Mem:/ {print $3}')
swap=$(echo "$free_output" | awk '/^Swap:/ {print $2}')
swapUsage=$(echo "$free_output" | awk '/^Swap:/ {print $3}')

#Convert values greater than 1000 to gigabytes
# convert_to_gb() {
#     val=$1
#     if [ "$val" -gt 1000 ]; then
#         val=$(awk "BEGIN { printf \"%.2f\", $val/1024 }")
#         echo "$val G"
#     else
#         echo "$val M"
#     fi
# }

# memory=$(convert_to_gb "$mem_total")
# memoryUsage=$(convert_to_gb "$mem_used")
# swap=$(convert_to_gb "$swap_total")
# swapUsage=$(convert_to_gb "$swap_used")

# Get disk usage
df_output=$(df -h .)
disk=$(echo "$df_output" | awk 'NR==2 {print $2}')
diskUsage=$(echo "$df_output" | awk 'NR==2 {print $3}')

# Add space between the numeric value and the unit (G or M)
disk=$(echo "$disk" | sed 's/\([0-9]\)\([GM]\)/\1 \2/')
diskUsage=$(echo "$diskUsage" | sed 's/\([0-9]\)\([GM]\)/\1 \2/')

# Get Uptime
uptime -p >/dev/null 2>&1
if [ "$?" -eq 0 ]; then
  # Supports most Linux distro
  # when the machine is up for less than '0' minutes then
  # 'uptime -p' returns ONLY 'up', so we need to set a default value
  UP_SET_OR_EMPTY=$(uptime -p | awk -F 'up ' '{print $2}')
  uptime=${UP_SET_OR_EMPTY:-'less than a minute'}
else
  # Supports Mac OS X, Debian 7, etc
  uptime=$(uptime | sed -E 's/^[^,]*up *//; s/mins/minutes/; s/hrs?/hours/;
  s/([[:digit:]]+):0?([[:digit:]]+)/\1 hours, \2 minutes/;
  s/^1 hours/1 hour/; s/ 1 hours/ 1 hour/;
  s/min,/minutes,/; s/ 0 minutes,/ less than a minute,/; s/ 1 minutes/ 1 minute/;
  s/  / /; s/, *[[:digit:]]* users?.*//')
fi

json_string="{ \"upTime\": \"$uptime\", \"cpuUsage\": \"$cpuUsage\", \"diskUsage\": \"$diskUsage\", \"disk\": \"$disk\", \"memoryUsage\": \"$memoryUsage\", \"memory\": \"$memory\", \"swapUsage\": \"$swapUsage\", \"swap\": \"$swap\" }"
echo "$json_string"
