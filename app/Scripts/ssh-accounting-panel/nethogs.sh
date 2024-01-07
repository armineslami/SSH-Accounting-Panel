#!/bin/bash

timestamp=`date +%Y-%m-%d-%H-%M`
output=$HOME/ssh-accounting-panel/logs/$timestamp.log

nethogs_pids=$(pgrep nethogs)

#if [ -n "$nethogs_pid" ]; then
#    kill "$nethogs_pid" >/dev/null 2>&1
#fi

if [ -n "$nethogs_pids" ]; then
    for pid in $nethogs_pids
    do
        kill "$pid" >/dev/null 2>&1
    done
fi

nohup /usr/sbin/nethogs -t -a 2>&1 | grep 'sshd:' > "$output" 2>&1 &
