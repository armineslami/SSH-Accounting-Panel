#!/bin/bash

server_udp_port=$1
scripts_directory=$2
server_username=$3
server_ip=$4
server_port=$5
panel_files_address=$scripts_directory/ssh-accounting-panel
script=$scripts_directory/SetUpPackages.sh

if [ -z "$scripts_directory" ]; then
    echo "<span class='text-terminal-error'>Missing arguments</span>"
    exit 1;
fi

if [ -n "$server_username" ] && [ -n "$server_ip" ] && [ -n "$server_port" ]; then
    echo "<span class='text-terminal-info'>Copying app files</span>"

    # Copy files to the server
    sudo scp -r -i ../storage/keys/ssh_accounting_panel -P "$server_port" "$panel_files_address" "$server_username"@"$server_ip":/root/ 2>&1

    # If scp was successful, exit status would be 0
    if [ $? != 0 ]; then
        echo "<span class='text-terminal-error'>Failed to set up the server</span>"
        exit 1
    fi

    # Run the SetUpPackages script on the remote server
    sudo ssh -i ../storage/keys/ssh_accounting_panel -p "$server_port" "$server_username@$server_ip" "bash -s" < "$script" "$server_udp_port" 2>&1
else
    # Copy files to root directory
    sudo cp -r "$panel_files_address" ~

    # Set www-data as owner
    sudo chown -R www-data:www-data ~/ssh-accounting-panel
    sudo chmod -R 700  ~/ssh-accounting-panel

    # Run the SetUpPackages script on the local machine
    bash -s < "$script" "$server_udp_port" 2>&1
fi
