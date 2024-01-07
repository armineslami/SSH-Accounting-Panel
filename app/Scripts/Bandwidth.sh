#!/bin/bash

createResponse() {
    json_string="{ \"code\": \"$1\", \"message\": \"$2\" }";
    echo "$json_string"
}

get_users() {
    awk -F: '$3 >= 1000 && $1 != "nobody" { print $1 }' /etc/passwd
}

user_stats() {
    cd ~/ssh-accounting-panel || exit

    json='{ "code": "1", "message": "success", "users": {'

#    ./hogs -type=csv logs/* > hogs.csv

    # Read all log files except the last one
    ls -1 logs/* | head -n -1 | xargs ./hogs -type=csv > hogs.csv

    local i=1

    if [ -n "$1" ]; then
        users="$1"
    else
        users=$(get_users)
    fi

for user in $users; do
    user_upload=0
    user_download=0
    rm -f temp.csv
    cat hogs.csv | grep ",$user," > temp.csv
    while IFS=, read -r tmp upload download username path machine; do
        # date=$(echo "$path" | awk -F/ '{print $NF}' | awk -F. '{print $1}' | cut -d "-" -f "1-3")
        if [ -n "$upload" ]; then
            user_upload=$(echo "$user_upload + ($upload / 1024)" | bc)
        fi
        if [ -n "$download" ]; then
            user_download=$(echo "$user_download + ($download / 1024)" | bc)
        fi
        done < temp.csv

        text="$user"

        user_upload_formatted=$(echo $user_upload | numfmt --grouping)
        user_download_formatted=$(echo $user_download | numfmt --grouping)

        json+=" \"$text\": { \"download\": $user_download_formatted, \"upload\": $user_upload_formatted },"

        i=$((i + 1))
    done

    rm -f temp.csv hogs.csv

    # Remove all log files except the last one
    ls -1 logs/* | head -n -1 | xargs rm -f

    # Remove the trailing comma after the last user
    json="${json%,}"

    # Closing the JSON structure
    json+=' } }'

    echo "$json"
}

# shellcheck disable=SC2119
echo "$(user_stats)"

