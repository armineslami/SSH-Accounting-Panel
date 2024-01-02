#!/bin/bash

createResponse() {
    json_string="{ \"code\": \"$1\", \"message\": \"$2\" }";
    echo "$json_string"
}

udp_port=$1

if [ -z "$udp_port" ]; then
    createResponse "0" "Failed to get udp port"
    exit;
fi

mv /etc/security/limits.conf /etc/security/limits.conf.backup >/dev/null 2>&1
touch /etc/security/limits.conf >/dev/null 2>&1

##############################
#####  Install Packages  #####
##############################

if [ -x "$(command -v yum)" ]; then
    # CentOS/RHEL
    sudo yum -y update
    sudo yum -y -q install nethogs golang bc coreutils cmake git >/dev/null 2>&1

elif [ -x "$(command -v apt-get)" ]; then
    # Debian/Ubuntu
    sudo DEBIAN_FRONTEND=noninteractive apt-get -y update
    sudo apt-get -y install nethogs golang bc coreutils cmake git >/dev/null 2>&1
    sudo DEBIAN_FRONTEND=interactive >/dev/null 2>&1
fi

#############################
#####  Nethogs Cron Job #####
#############################

cd /root/ssh-accounting-panel > /dev/null 2>&1 || exit

sudo chmod +x nethogs.sh

#wget -O hogs.go https://raw.githubusercontent.com/boopathi/nethogs-parser/master/hogs.go >/dev/null 2>&1

sudo go build -o hogs hogs.go

#sudo rm -f hogs.go

cron_job="*/5 * * * * sh $(pwd)/nethogs.sh"

if ! crontab -l 2>/dev/null | grep -Fq "$cron_job"; then
    (crontab -l ; echo "$cron_job") | crontab
fi

sudo sh "$(pwd)/nethogs.sh"

########################################
#####  Kill Extra Session Cron Job #####
########################################

sudo chmod +x killExtraSession.sh

cron_job="*/1 * * * * sh $(pwd)/killExtraSession.sh"

if ! crontab -l 2>/dev/null | grep -Fq "$cron_job"; then
    (crontab -l ; echo "$cron_job") | crontab
fi

sudo sh "$(pwd)/killExtraSession.sh"

###########################
#####  BadVPN For UDP #####
###########################

git clone https://github.com/ambrop72/badvpn.git /root/badvpn >/dev/null 2>&1

mkdir /root/badvpn/badvpn-build >/dev/null 2>&1

if [ ! -d "/root/badvpn/badvpn-build" ]; then
    createResponse "0" "Failed to create required directory for badvpn"
    exit;
fi

cd  /root/badvpn/badvpn-build > /dev/null 2>&1 || exit

temp_output=$(mktemp)

cmake .. -DBUILD_NOTHING_BY_DEFAULT=1 -DBUILD_UDPGW=1 > "$temp_output" 2>&1 &

cmake_pid=$!

wait $cmake_pid

cmake_result=$(<"$temp_output")

rm "$temp_output"

if ! grep -q "Configuring done" <<< "$cmake_result"; then
    createResponse "0" "Failed to run cmake"
    exit;
fi

make >/dev/null 2>&1 &

wait

cp udpgw/badvpn-udpgw /usr/local/bin >/dev/null 2>&1

cat >  /etc/systemd/system/ssh-accounting-panel-udp.service << ENDOFFILE
[Unit]
Description=UDP forwarding for badvpn-tun2socks
After=nss-lookup.target

[Service]
ExecStart=/usr/local/bin/badvpn-udpgw --loglevel none --listen-addr 127.0.0.1:$udp_port --max-clients 999
User=ssh-accounting-panel-udp

[Install]
WantedBy=multi-user.target
ENDOFFILE

useradd -m ssh-accounting-panel-udp >/dev/null 2>&1

systemctl enable ssh-accounting-panel-udp >/dev/null 2>&1

systemctl start ssh-accounting-panel-udp >/dev/null 2>&1

createResponse "1" "Server is ready."
