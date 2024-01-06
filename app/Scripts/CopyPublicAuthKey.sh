#!/bin/bash

# Create a json string
createResponse() {
    json_string="{ \"code\": \"$1\", \"message\": \"$2\" }";
    echo "$json_string"
}

if [[ -z $1 ]]; then
    # Copy the key to the authorized_keys file of the local machine
    if [ ! -f /root/.ssh/authorized_keys ]; then
        mkdir -p /root/.shh
        touch /root/.ssh/authorized_keys
        chmod 700 ~/.ssh
        chmod 600 ~/.ssh/authorized_keys
    fi
    cat /root/.ssh/ssh_accounting_panel.pub >> /root/.ssh/authorized_keys

    createResponse "1" "successful"
    exit 1
else
    # Copy the key to the server
    sshpass -p "$2" ssh-copy-id -p "$4" -i /root/.ssh/ssh_accounting_panel.pub "$1"@"$3" > /dev/null 2>&1

    # Test if SSH connection works with the key
    ssh -q -o BatchMode=yes -o ConnectTimeout=5 -i /root/.ssh/ssh_accounting_panel -p "$4" "$1"@"$3" 'echo Connected' >/dev/null 2>&1 && (createResponse "1" "Successful") || (createResponse "0" "Failed to connect to the server")
fi
