#!/bin/bash

# Create a json string
createResponse() {
    json_string="{ \"code\": \"$1\", \"message\": \"$2\" }";
    echo "$json_string"
}

if [[ -n $1 ]]; then
    # Remove any old entry for this host in know_hosts file
    sudo ssh-keygen -f ~/.ssh/known_hosts -R "[$3]:$4"  > /dev/null 2>&1

    cd ../storage/keys

    # Copy the key to the server
    # -o StrictHostKeyChecking=no
    sudo cat ssh_accounting_panel.pub | sudo sshpass -p "$2" ssh -p "$4" "$1"@"$3" "mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys" > /dev/null 2>&1

    # Test if SSH connection works with the key
    # -o StrictHostKeyChecking=no
    sudo ssh -q -o BatchMode=yes -o ConnectTimeout=5 -i ssh_accounting_panel -p "$4" "$1"@"$3" 'echo Connected' >/dev/null 2>&1 && (createResponse "1" "Successful") || (createResponse "0" "Failed to connect to the server")
else
    createResponse "0" "Server address is required"
fi
