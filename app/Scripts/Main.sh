#!/bin/bash

# Arguments
action=""
username=""
password=""
is_active=""
server_ip=""
server_port=""
server_udp_port=""
server_username=""
server_password=""
max_login="1"
active_days=""
traffic_limit=""

# Create a json string
createResponse() {
    json_string="{ \"code\": \"$1\", \"message\": \"$2\" }";
    echo "$json_string"
}

# Process arguments
while [[ $# -gt 0 ]]; do
    case "$1" in
        -action)
            action="$2"
            shift 2
            ;;
        -username)
            username="$2"
            shift 2
            ;;
        -password)
            password="$2"
            shift 2
            ;;
        -is_active)
            is_active="$2"
            shift 2
            ;;
        -server_ip)
            server_ip="$2"
            shift 2
            ;;
        -server_port)
            server_port="$2"
            shift 2
            ;;
        -server_udp_port)
            server_udp_port="$2"
            shift 2
            ;;
        -server_username)
            server_username="$2"
            shift 2
            ;;
        -server_password)
            server_password="$2"
            shift 2
            ;;
        -max_login)
            max_login="$2"
            shift 2
            ;;
        -active_days)
            active_days="$2"
            shift 2
            ;;
        -traffic_limit)
            traffic_limit="$2"
            shift 2
            ;;
        *)
            createResponse "0" "Unknown argument"
            exit 1
            ;;
    esac
done

if [ -z "$action" ]; then
    createResponse "0" "Unknown action"
    exit 1
fi

if [ "$action" = "CreateUser" ] || [ "$action" = "UpdateUser" ]; then
    # If one of the arguments is missing, Exit
    if [ -z "$username" ] || [ -z "$password" ] || [ -z "$is_active" ] || [ -z "$server_ip" ] || [ -z "$server_port" ] || [ -z "$server_username" ]; then
        createResponse "0" "Missing arguments"
        exit 1
    fi
elif [ "$action" = "DeleteUser" ]; then
    if [ -z "$username" ] || [ -z "$server_ip" ] || [ -z "$server_port" ] || [ -z "$server_username" ]; then
        createResponse "0" "Missing arguments"
        exit 1
    fi
elif [ "$action" = "CopyPublicAuthKey" ]; then
    if [ -z "$server_ip" ] || [ -z "$server_port" ] || [ -z "$server_username" ] || [ -z "$server_password" ]; then
        createResponse "0" "Missing arguments"
        exit 1
    fi
elif [ "$action" = "SetUpServer" ]; then
    if [ -z "$server_ip" ] || [ -z "$server_port" ] || [ -z "$server_username" ] || [ -z "$server_udp_port" ]; then
        createResponse "0" "Missing arguments"
        exit 1
    fi
elif [ "$action" = "RemoveServer" ] || [ "$action" = "Bandwidth" ]; then
    if [ -z "$server_ip" ] || [ -z "$server_port" ] || [ -z "$server_username" ]; then
        createResponse "0" "Missing arguments"
        exit 1
    fi
else
    createResponse "0" "Unknown action"
    exit 1
fi

# Get public ip address
public_ip=$(curl -s ipv4.icanhazip.com)

# Get script directory
script_dir=$(cd "$(dirname "$0")" && pwd)

# Get script absolute path
script="$script_dir/$action.sh"

# Get path to ssh key
root_dir=$(cd "$(dirname "$script_dir")/.." && pwd)
ssh_accounting_panel_key_dir="$root_dir/storage/keys/ssh_accounting_panel"

# Check if given server ip is the same of current server that this script is running on
if [ "$server_ip" != "$public_ip" ] || [[ -z "$public_ip" ]]; then
    # Run the script on the remote server
    if [ "$action" = "CopyPublicAuthKey" ]; then
        # Copy auth key of app server to the remote server
        result=$(bash -s < "$script" "$server_username" "$server_password" "$server_ip" "$server_port" 2>&1)
    elif [ "$action" = "SetUpServer" ]; then
        result=$(bash -s < "$script" "$server_udp_port" "$script_dir" "$server_username" "$server_ip" "$server_port" 2>&1)
    elif [ "$action" = "RemoveServer" ]; then
            result=$(sudo ssh -i "$ssh_accounting_panel_key_dir" -p "$server_port" "$server_username@$server_ip" "bash -s" < "$script" 2>&1)
    elif [ "$action" = "Bandwidth" ]; then
        result=$(sudo ssh -i "$ssh_accounting_panel_key_dir" -p "$server_port" "$server_username@$server_ip" "bash -s" < "$script" 2>&1)
    else
        result=$(sudo ssh -i "$ssh_accounting_panel_key_dir" -p "$server_port" "$server_username@$server_ip" "export USERNAME='$username'; export PASSWORD='$password'; export IS_ACTIVE='$is_active'; export MAX_LOGIN='$max_login'; export ACTIVE_DAYS='$active_days'; export TRAFFIC_LIMIT='$traffic_limit'; bash -s" < "$script" 2>&1)
    fi
else
    # Server is local
    if [ "$action" = "CopyPublicAuthKey" ]; then
         createResponse "1" "Server is local so no need to copy ssh key"
    elif [ "$action" = "Bandwidth" ] || [ "$action" = "RemoveServer" ]; then
        result=$(bash -s < "$script" 2>&1)
    elif [ "$action" = "SetUpServer" ]; then
        result=$(bash -s < "$script" "$server_udp_port" "$script_dir" 2>&1)
    else
        result=$(USERNAME="$username" PASSWORD="$password" IS_ACTIVE="$is_active" MAX_LOGIN="$max_login" ACTIVE_DAYS="$active_days" TRAFFIC_LIMIT="$traffic_limit" . "$script")
    fi
fi

if [[ $result == *"Operation timed out"* ]]; then
    createResponse "0" "Connection timed out"
    exit 1
elif [[ $result == *"Permission denied"* ]]; then
    createResponse "0" "Incorrect server password"
    exit 1
fi

echo "$result"
