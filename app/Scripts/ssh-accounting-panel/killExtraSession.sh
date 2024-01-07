#!/bin/bash

# Unlock user after a delay
unlock_user() {
    sleep "$1"
    sudo usermod -U "$2"
}

# Fetch the list of all users with UIDs greater than or equal to 1000
user_list=$(awk -F: '$3 >= 1000 && $1 != "nobody" { print $1 }' /etc/passwd)

# Loop through each user
for username in $user_list; do
    # Get the PIDs of SSH sessions for the user
    session_pids=$(pgrep -u "$username" sshd)

    # Count the number of sessions
    num_sessions=$(echo "$session_pids" | wc -w)

    # Get the session limit for the user
    session_limit=$(grep "$username.*maxlogins" ~/ssh-accounting-panel/limits.conf | awk '{print $NF}')

    if [ -z "$session_limit" ] || [ -z "$num_sessions" ]; then
        continue
    fi

    if [ "$num_sessions" -gt "$session_limit" ]; then
        first=1  # Flag to identify the first PID

        # Keep first session and kill all extra ones
        for session_pid in $session_pids; do
            if [ "$first" -eq 1 ]; then
                first=0  # Set the flag to 0 after skipping the first PID
                continue  # Skip terminating the first PID
            fi
            sudo kill "$session_pid"
        done

        # Lock the user
        sudo usermod -L "$username";

        # Unlock the user after 50 seconds (run it in the background)
        # !! 50 seconds because the cron job is running this file every 60 seconds !!
        # !! See SetUpPackages.sh !! #
        unlock_user 50 "$username" &
    fi
done
