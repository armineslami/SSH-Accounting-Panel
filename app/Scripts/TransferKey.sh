#!/bin/bash

if [[ -n $1 ]]; then
    # Remove any old entry for this host in know_hosts file
    sudo ssh-keygen -f ~/.ssh/known_hosts -R "[$3]:$4" > /dev/null 2>&1

    cd ../storage/keys

    echo "<span class='text-terminal-info'>Copying app public key</span>"

    # Copy the key to the server
    # -o StrictHostKeyChecking=no
    sudo cat ssh_accounting_panel.pub | sudo sshpass -p "$2" ssh -o StrictHostKeyChecking=accept-new -p "$4" "$1"@"$3" "mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys" 2>&1

    echo "<span class='text-terminal-info'>Testing the connection using the key</span>"

    # Test if SSH connection works with the key
    # -o StrictHostKeyChecking=no
    sudo ssh -q -o BatchMode=yes -o ConnectTimeout=5 -i ssh_accounting_panel -p "$4" "$1"@"$3" 'echo Connected' >/dev/null 2>&1 && (echo "<span class='text-terminal-success'>Connection test succeeded</span>") || (echo "<span class='text-terminal-error'>Connection test failed</span>")
else
    echo "Server address is required"
fi
