#!/bin/bash

project_path="/var/www/sap"

(crontab -l ; echo "* * * * * cd $project_path && php artisan schedule:run >> /dev/null 2>&1") | crontab -
