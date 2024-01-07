#!/bin/bash

# Create a json string
createResponse() {
    json_string="{ \"code\": \"$1\", \"message\": \"$2\" }";
    echo "$json_string"
}

#####################
###  Create User  ###
#####################

sudo adduser --shell /usr/sbin/nologin --no-create-home --disabled-password --gecos "" "$USERNAME" > /dev/null 2>&1

#####################
###  Set Password ###
#####################

echo "$USERNAME":"$PASSWORD" | sudo chpasswd > /dev/null 2>&1

#####################
###  Expire Date  ###
#####################

if [ "$IS_ACTIVE" -eq 1 ]; then
    if [ -n "$ACTIVE_DAYS" ]; then
        expire_date=$(date -d "+$ACTIVE_DAYS days" +"%Y-%m-%d")
    else
        expire_date=""
    fi
    sudo usermod --expiredate "$expire_date" "$USERNAME" > /dev/null 2>&1
else
    # Set expire date to January 1, 1970
    sudo usermod --expiredate 1 "$USERNAME" > /dev/null 2>&1
fi

###################
###  Max Login  ###
###################

# Limit the active ssh logins. If user is already limited, delete the rule line and add a new rule.
file_name=~/ssh-accounting-panel/limits.conf
line_number=$(grep -nw "$USERNAME" "$file_name" | cut -d':' -f1)
if [ -n "$line_number" ]; then
    # Remove the rule line for this user
    sed -i "${line_number}d" "$file_name" > /dev/null 2>&1
fi

#Add limitation rule
echo "$USERNAME maxlogins $MAX_LOGIN" >> $file_name

# Done
createResponse "1" "Successful"
