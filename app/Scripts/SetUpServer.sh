#!/bin/bash

server_udp_port=$1
scripts_directory=$2
server_username=$3
server_ip=$4
server_port=$5
panel_files_address=$scripts_directory/ssh-accounting-panel
script=$scripts_directory/SetUpPackages.sh

createResponse() {
    json_string="{ \"code\": \"$1\", \"message\": \"$2\" }";
    echo "$json_string"
}

if [ -z "$scripts_directory" ]; then
    createResponse "0" "Missing arguments"
    exit 1;
fi

result=""

if [ -n "$server_username" ] && [ -n "$server_ip" ] && [ -n "$server_port" ]; then
    # Copy files to the server
    sudo scp -r -i ../storage/keys/ssh_accounting_panel -P "$server_port" "$panel_files_address" "$server_username"@"$server_ip":/root/ > /dev/null 2>&1

    # If scp was successful, exit status would be 0
    if [ $? != 0 ]; then
        createResponse "0" "Failed to set up the server"
        exit 1
    fi

    # Run the SetUpPackages script on the remote server
    result=$(sudo ssh -i ../storage/keys/ssh_accounting_panel -p "$server_port" "$server_username@$server_ip" "bash -s" < "$script" "$server_udp_port" 2>&1)
else
    # Copy files to root directory
    cp -r "$panel_files_address" ~

    # Run the SetUpPackages script on the local machine
    result=$(bash -s < "$script" "$server_udp_port" 2>&1)
fi

echo "$result";
