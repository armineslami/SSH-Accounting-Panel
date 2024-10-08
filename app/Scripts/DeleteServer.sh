#!/bin/bash

echo "<span class='text-terminal-info'>Cleaning up the server</span>"

cd ~/ssh-accounting-panel 2>&1 || exit;

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

echo "<span class='text-terminal-info'>Removing udp service</span>"

sudo systemctl disable ssh-accounting-panel-udp 2>&1
sudo systemctl stop ssh-accounting-panel-udp 2>&1

echo "<span class='text-terminal-info'>Removing app files</span>"

sudo rm -rf ssh-accounting-panel 2>&1
sudo rm -rf /usr/local/bin/badvpn-udpgw 2>&1
sudo rm -rf /etc/systemd/system/ssh-accounting-panel-udp.service 2>&1

sudo systemctl daemon-reload

sudo deluser ssh-accounting-panel-udp 2>&1

docker rm -f shadowbox watchtower 2>&1
yes | docker system prune -a 2>&1
sudo rm -rf "/root/shadowbox" 2>&1
sudo rm -rf "/opt/outline" 2>&1
echo ""

echo "<span class='text-terminal-success'>Server is deleted successfully</span>"
