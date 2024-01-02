#!/bin/bash

project_path="/var/html/ssh-accounting-panel"

(crontab -l ; echo "* * * * * cd $project_path && php artisan schedule:run >> /dev/null 2>&1") | crontab -
