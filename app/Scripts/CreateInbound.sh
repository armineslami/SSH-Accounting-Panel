#!/bin/bash

#####################
###  Create User  ###
#####################

echo "<span class='text-terminal-info'>Creating the inbound</span>"
    sudo adduser --shell /usr/sbin/nologin --no-create-home --disabled-password --gecos "" "$USERNAME" 2>&1

#####################
###  Set Password ###
#####################

echo "$USERNAME":"$PASSWORD" | sudo chpasswd 2>&1

#####################
###  Expire Date  ###
#####################

usermodResult=""

if [ "$IS_ACTIVE" -eq 1 ]; then
    if [ -n "$ACTIVE_DAYS" ]; then
        expire_date=$(date -d "+$ACTIVE_DAYS days" +"%Y-%m-%d")
        echo "<span class='text-terminal-info'>Setting expire date to: <b class='text-terminal-warn'>$expire_date</b></span>"
    else
        expire_date=""
        echo "<span class='text-terminal-info'>Setting expire date to: <b class='text-terminal-warn'>Never</b></span>"
    fi
    usermodResult=$(sudo usermod --expiredate "$expire_date" "$USERNAME" 2>&1)
else
    echo "<span class='text-terminal-info'>Deactivating the user</span>"
    # Set expire date to January 1, 1970
    usermodResult=$(sudo usermod --expiredate 1 "$USERNAME" 2>&1)
fi

if [ -n "$usermodResult" ]; then
    echo "$usermodResult";
fi

###################
###  Max Login  ###
###################

echo "<span class='text-terminal-info'>Setting max login to: <b class='text-terminal-warn'>$MAX_LOGIN</b></span>"
# Limit the active ssh logins. If user is already limited, delete the rule line and add a new rule.
file_name=~/ssh-accounting-panel/limits.conf
line_number=$(grep -nw "$USERNAME" "$file_name" | cut -d':' -f1)
if [ -n "$line_number" ]; then
    # Remove the rule line for this user
    sed -i "${line_number}d" "$file_name" 2>&1
fi

#Add limitation rule
echo "$USERNAME maxlogins $MAX_LOGIN" >> $file_name

echo "<span class='text-terminal-success'>Inbound is created successfully</span>"
