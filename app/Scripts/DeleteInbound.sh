#!/bin/bash

# Check if user exists
if ! grep -q "^$USERNAME:" /etc/passwd; then
    echo "<span class='text-terminal-error'>Inbound not found</span>"
    exit
fi

echo "<span class='text-terminal-info'>Logging out the inbound</span>"

# Force logout
sudo pkill -KILL -u "$USERNAME" > /dev/null 2>&1

echo "<span class='text-terminal-info'>Deleting the inbound</span>"

# Delete the user
sudo deluser "$USERNAME" 2>&1

# Remove max login rule
file_name=~/ssh-accounting-panel/limits.conf
line_number=$(grep -nw "$USERNAME" "$file_name" | cut -d':' -f1)

if [ -n "$line_number" ]; then
    # Remove the rule line for this user
    sed -i "${line_number}d" "$file_name" 2>&1
fi

echo "<span class='text-terminal-success'>Inbound is deleted successfully</span>"
