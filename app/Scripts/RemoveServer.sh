#!/bin/bash

createResponse() {
    json_string="{ \"code\": \"$1\", \"message\": \"$2\" }";
    echo "$json_string"
}

cd ~/ssh-accounting-panel > /dev/null 2>&1 || exit;

cron_job="*/5 * * * * sh $(pwd)/nethogs.sh"

if crontab -l 2>/dev/null | grep -Fq "$cron_job"; then
    current_crontab=$(crontab -l 2>/dev/null)
    new_crontab=$(echo "$current_crontab" | grep -Fv "$cron_job")
    echo "$new_crontab" | crontab
fi

cron_job="*/1 * * * * sh $(pwd)/killExtraSession.sh"

if crontab -l 2>/dev/null | grep -Fq "$cron_job"; then
    current_crontab=$(crontab -l 2>/dev/null)
    new_crontab=$(echo "$current_crontab" | grep -Fv "$cron_job")
    echo "$new_crontab" | crontab
fi

cd ~ || exit

rm -rf ssh-accounting-panel/* > /dev/null 2>&1
rm -rf /usr/local/bin/badvpn-udpgw >/dev/null 2>&1
rm -rf /etc/systemd/system/ssh-accounting-panel-udp.service >/dev/null 2>&1
deluser ssh-accounting-panel-udp >/dev/null 2>&1
systemctl disable ssh-accounting-panel-udp >/dev/null 2>&1
systemctl stop ssh-accounting-panel-udp >/dev/null 2>&1

rm -rf /etc/security/limits.conf >/dev/null 2>&1
touch /etc/security/limits.conf >/dev/null 2>&1

createResponse "1" "Server is removed."
