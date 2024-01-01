#!/bin/bash

# Create a json string
createResponse() {
    json_string="{ \"code\": \"$1\", \"message\": \"$2\" }";
    echo "$json_string"
}

# Check if user exists
if ! grep -q "^$USERNAME:" /etc/passwd; then
    createResponse "1" "Successful"
    exit
fi

# Force logout
sudo pkill -KILL -u "$USERNAME" > /dev/null 2>&1

# Delete the user
deluser "$USERNAME" > /dev/null 2>&1

# Remove max login rule
file_name="/etc/security/limits.conf"
line_number=$(grep -nw "$USERNAME" "$file_name" | cut -d':' -f1)

if [ -n "$line_number" ]; then
    # Remove the rule line for this user
    sed -i "${line_number}d" "$file_name" > /dev/null 2>&1
fi

createResponse "1" "Successful"
