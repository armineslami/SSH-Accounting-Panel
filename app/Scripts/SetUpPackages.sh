#!/bin/bash

udp_port=$1
export HOME=~

if [ -z "$udp_port" ]; then
    echo "<span class='text-terminal-error'>Failed to get udp port</span>"
    exit;
fi

##############################
#####  Install Packages  #####
##############################

echo "<span class='text-terminal-info'>Installing/Updating packages</span>"

if [ -x "$(command -v apt-get)" ]; then
    # Debian/Ubuntu
    DEBIAN_FRONTEND=noninteractive apt-get -y update 2>&1
    sudo apt-get -y install nethogs golang bc coreutils cmake git 2>&1
    DEBIAN_FRONTEND=interactive >/dev/null 2>&1
else
    echo "<span class='text-terminal-error'>Unsupported OS</span>"
fi

#############################
#####  Nethogs Cron Job #####
#############################

echo "<span class='text-terminal-info'>Adding cron jobs</span>"

cd ~/ssh-accounting-panel 2>&1 || exit

sudo chmod +x nethogs.sh 2>&1

#wget -O hogs.go https://raw.githubusercontent.com/boopathi/nethogs-parser/master/hogs.go >/dev/null 2>&1

go build -o hogs hogs.go 2>&1

#sudo rm -f hogs.go

cron_job="*/5 * * * * sh $(pwd)/nethogs.sh"

if ! crontab -l 2>/dev/null | grep -Fq "$cron_job"; then
    (crontab -l 2>/dev/null; echo "$cron_job") | crontab
fi

sh "$(pwd)/nethogs.sh"

########################################
#####  Kill Extra Session Cron Job #####
########################################

sudo chmod +x killExtraSession.sh

cron_job="*/1 * * * * sh $(pwd)/killExtraSession.sh"

if ! crontab -l 2>/dev/null | grep -Fq "$cron_job"; then
    (crontab -l 2>/dev/null; echo "$cron_job") | crontab
fi

sh "$(pwd)/killExtraSession.sh"

###########################
#####  BadVPN For UDP #####
###########################

echo "<span class='text-terminal-info'>Setting up udp port</span>"

git clone https://github.com/ambrop72/badvpn.git ~/ssh-accounting-panel/badvpn 2>&1

sudo mkdir -p ~/ssh-accounting-panel/badvpn/badvpn-build 2>&1

if [ ! -d ~/ssh-accounting-panel/badvpn/badvpn-build ]; then
    echo "<span class='text-terminal-error'>Failed to create required directory for badvpn</span>"
    exit;
fi

cd  ~/ssh-accounting-panel/badvpn/badvpn-build 2>&1 || exit

temp_output=$(mktemp)

sudo cmake .. -DBUILD_NOTHING_BY_DEFAULT=1 -DBUILD_UDPGW=1 > "$temp_output" 2>&1 &

cmake_pid=$!

wait $cmake_pid

cmake_result=$(<"$temp_output")

rm "$temp_output"

if ! grep -q "Configuring done" <<< "$cmake_result"; then
    echo "<span class='text-terminal-error'>Failed to run cmake</span>"
    exit;
fi

sudo make 2>&1 &

wait

sudo cp udpgw/badvpn-udpgw /usr/local/bin 2>&1

sudo bash -c "cat >  /etc/systemd/system/ssh-accounting-panel-udp.service << ENDOFFILE
[Unit]
Description=UDP forwarding for badvpn-tun2socks
After=nss-lookup.target

[Service]
ExecStart=/usr/local/bin/badvpn-udpgw --loglevel none --listen-addr 127.0.0.1:$udp_port --max-clients 999
User=ssh-accounting-panel-udp

[Install]
WantedBy=multi-user.target
ENDOFFILE"

sudo useradd -m ssh-accounting-panel-udp 2>&1

sudo systemctl enable ssh-accounting-panel-udp 2>&1

sudo systemctl start ssh-accounting-panel-udp 2>&1

sudo systemctl daemon-reload

###########################
#####     Outline     #####
###########################

if ! (docker ps -a --filter "name=shadowbox" | grep -q shadowbox) && ! (docker ps -a --filter "name=watchtower" | grep -q watchtower); then
    yes | sudo bash -c "$(wget -qO- https://raw.githubusercontent.com/Jigsaw-Code/outline-apps/master/server_manager/install_scripts/install_server.sh)"
fi

echo "<span class='text-terminal-info'>Outline API URL</span>"
apiUrl=$(grep "apiUrl" /opt/outline/access.txt | cut -d: -f 2-)
echo "API URL: $apiUrl"
echo ""

echo "<span class='text-terminal-success'>Server is set and ready to use</span>"
