#!/bin/bash

#################
### Variables ###
#################

DEV=0
FRESH_COMPOSER=0

# Panel info
project_display_name="SSH Accounting Panel"
project_version="3.2.0"
project_name="sap"
project_source_link="https://github.com/armineslami/SSH-Accounting-Panel.git"
project_latest_commit_link="https://api.github.com/repos/armineslami/SSH-Accounting-Panel/commits/master"
cli_command="sap"
is_none_tls="no"

# Colors
RED="\033[0;31m"
BLUE='\033[0;34m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC="\033[0m" # No Color

# Required packages
packages="php8.2 php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php-curl openssl cron apache2 libapache2-mod-php certbot python3-certbot-apache mariadb-server sshpass openssh-client openssh-server unzip jq curl net-tools supervisor"
node_packages="nodejs npm"

#################
### Functions ###
#################

# Checks if the script is running with root privileges
isRoot() {
    uid=$(id -u)
    if [ "$uid" -eq 0 ]; then
        echo "true"
    else
        echo "false"
    fi
}

# Checks for OS package manager
get_package_manager_name() {
    if [ -x "$(command -v apt-get)" ]; then
        echo "apt-get"
    else
        echo "Unsupported"
    fi
}

check_for_none_lts() {
    description=$(lsb_release -d)

    if [[ ! $description == *"LTS"* ]]; then
        printf "${YELLOW}\nYour server os distribution is not LTS and installation may fail. Continue? y/n [default: y]: ${NC}"
        read answer
        continue=${answer:="y"}

        if [ "$continue" != "y" ]; then
            exit 1;
        fi

        is_none_tls="yes"
    fi
}

add_support_for_ondrej_repo_in_none_tls_dist() {
        # Get the current distribution codename
        codename=$(lsb_release -c -s)

        source_list=""

        if [ "$codename" == "lunar" ]; then
            source_list=/etc/apt/sources.list.d/ondrej-ubuntu-php-lunar.list
            # Replace 'lunar' with 'jammy' in the file
            sudo sed -i 's/lunar/jammy/g' "$source_list" > /dev/null 2>&1
        elif [ "$codename" == "mantic" ]; then
            source_list=/etc/apt/sources.list.d/ondrej-ubuntu-php-mantic.list
            # Replace 'mantic' with 'jammy' in the file
            sudo sed -i 's/mantic/jammy/g' "$source_list" > /dev/null 2>&1
        fi

        sudo touch /etc/apt/preferences.d/ondrejphp
        sudo tee /etc/apt/preferences.d/ondrejphp >/dev/null <<EOF
Package: libgd3
Pin: release n=lunar
Pin-Priority: 900
EOF
}

check_mysql_connection() {
    if [ -z "$1" ]; then
        result=$(mysql -u root -e "SELECT 1" 2>&1)
    else
        result=$(mysql -u root -p"$1" -e "SELECT 1" 2>&1)
    fi
    echo "$result"
}

is_installed() {
    apache_config_file="/etc/apache2/sites-available/$project_name.conf"
    if [ -f "$apache_config_file" ]; then
        return 0 # installed
    else
        printf "${RED}\nYou must first install the panel\n${NC}\n"
        before_show_menu
        return 1 # no installed
    fi
}

is_uninstalled() {
    apache_config_file="/etc/apache2/sites-available/$project_name.conf"
    if [ ! -f "$apache_config_file" ]; then
        return 0 # not installed
    else
        printf "${RED}\nThe panel is already installed\n${NC}\n"
        before_show_menu
        return 1 # installed
    fi
}

get_ip_address() {
#    ip=$(ifconfig | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1')
    ip=$(curl -s ipv4.icanhazip.com)
    echo "$ip"
}

install_packages() {
    local package_manager
    package_manager=$(get_package_manager_name)

     # Install required packages based on OS
    if [ "$package_manager" = "apt-get"  ]; then
        # Debian/Ubuntu
        sudo DEBIAN_FRONTEND=noninteractive "$package_manager" -y update
        sudo "$package_manager" install -y software-properties-common
        if ! grep -q "^deb.*ondrej/php" /etc/apt/sources.list /etc/apt/sources.list.d/*; then
            # Repository not found, add it
            sudo add-apt-repository ppa:ondrej/php -y
        fi
        if [ "$is_none_tls" == "yes" ]; then
            add_support_for_ondrej_repo_in_none_tls_dist
        fi
        sudo "$package_manager" update -y
        sudo "$package_manager" -y install php8.2 php8.2-cli php8.2-mysql php8.2-xml php8.2-dom php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php-curl openssl cron apache2 libapache2-mod-php certbot python3-certbot-apache mariadb-server sshpass openssh-client openssh-server unzip jq curl net-tools supervisor
#        curl -fsSL https://deb.nodesource.com/setup_21.x | sudo -E bash - &&\
#        sudo "$package_manager" -y install nodejs npm
        sudo snap install node --classic --channel=21
        sudo DEBIAN_FRONTEND=interactive
    # couldn't find package manger of the OS
    else
        printf "${RED}\nError: Unsupported distribution or package manager!.\n${NC}\n"
        exit 1
    fi
}

install_worker() {
    log="/var/www/$project_name/storage/logs/worker.log"
    config="/etc/supervisor/conf.d/$project_name-worker.conf"

    if test -f "$config"; then
        rm -r $config
    fi

    if [ ! -f "$log" ]; then
        touch $log;
    fi
    chown -R www-data:www-data $log
    chmod -R 644 $log

    touch $config

    cat > "$config" <<ENDOFFILE
[program:${project_name}-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/${project_name}/artisan queue:work database --sleep=3 --tries=1 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/var/www/${project_name}/storage/logs/worker.log
stopwaitsecs=3600
ENDOFFILE

    sudo supervisorctl reread > /dev/null 2>&1
    sudo supervisorctl update > /dev/null 2>&1
    sudo supervisorctl start "$project_name-worker:*" > /dev/null 2>&1
}

install() {
    current_directory=$(pwd)

    cd /root || exit

    check_for_none_lts

    ############################
    ### Package Installation ###
    ############################

    printf "${GREEN}\nInstalling required packages ...\n${NC}\n"

    install_packages

    #############################
    ### Composer Installation ###
    #############################

    if [ ! -e /usr/local/bin/composer ]; then
        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        #php -r "if (hash_file('sha384', 'composer-setup.php') === 'e21205b207c3ff031906575712edab6f13eb0b361f2085f1f1237b7126d785e826a450292b6cfd1d64d92e6563bbde02') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
        php composer-setup.php
        php -r "unlink('composer-setup.php');"
        sudo mv composer.phar /usr/local/bin/composer
        FRESH_COMPOSER=1
    fi

    #####################
    ### Project Clone ###
    #####################

    printf "${GREEN}\nDownloading the project from the github ...\n${NC}\n"

    # Remove old project in /root if it exists
    sudo rm -rf "$project_name" > /dev/null 2>&1

    # Create a directory for the project
    mkdir -p "$project_name" > /dev/null 2>&1

    # Go to the directory
    cd sap || exit;

    # Clone the project
    git init
    git config --global --add safe.directory /root/sap
    git remote add origin "$project_source_link"

    if [ $DEV == 1 ]; then
        printf "${YELLOW}\n###########################\n\nEntering To Dev Mode\n\n###########################\n${NC}\n"
        git pull origin dev
    else
        git pull origin master
    fi

    # Create a file that holds the sha of latest commit
    curl -s "$project_latest_commit_link" | jq -r .sha > version.info

    cd ..

    if [ ! -d /var/www ]; then
        sudo mkdir /var/www
    fi

    # Remove old folder inside the apache if it exists
    rm -rf "/var/www/$project_name"

    # Move the project into apache directory
    mv -i "/root/$project_name"  /var/www/

    # Create a directory to copy nethogs and cron jobs into it
    mkdir /var/www/ssh-accounting-panel

    # Make a directory for go lang
    mkdir /var/www/.cache

    # Create a file for badvpn service in case the current server be used for as a inbound server
    touch /etc/systemd/system/ssh-accounting-panel-udp.service

    # Create a file to store inbounds limits
    touch /var/www/ssh-accounting-panel/limits.conf

    ###################
    ### Permissions ###
    ###################

    chown -R www-data:www-data "/var/www/$project_name"
    chmod -R 700  "/var/www/$project_name/app/Scripts"

    chown -R www-data:www-data /var/www/ssh-accounting-panel
    chmod -R 700  /var/www/ssh-accounting-panel

    chown -R www-data:www-data /var/www/.cache
    chmod -R 700  /var/www/.cache

    chown -R www-data:www-data /var/www/ssh-accounting-panel/limits.conf
    chmod -R 700 /var/www/ssh-accounting-panel/limits.conf

    chown root:www-data /etc/systemd/system/ssh-accounting-panel-udp.service
    chmod 770 /etc/systemd/system/ssh-accounting-panel-udp.service

    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/sbin/adduser' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/sbin/useradd' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/sbin/deluser' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/sed' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/passwd' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/sbin/chpasswd' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/curl' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/kill' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/killall' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/pkill' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/rm' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/mv' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/cp' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/touch' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/grep' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/chmod' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/chown' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/crontab' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/sbin/nethogs' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/nethogs' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/local/sbin/nethogs' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/sbin/service' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/bin/systemctl restart apache2' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/bin/systemctl start ssh-accounting-panel-udp' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/bin/systemctl stop ssh-accounting-panel-udp' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/bin/systemctl enable ssh-accounting-panel-udp' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/bin/systemctl disable ssh-accounting-panel-udp' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/bin/systemctl daemon-reload' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/systemctl restart apache2' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/systemctl start ssh-accounting-panel-udp' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/systemctl stop ssh-accounting-panel-udp' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/systemctl enable ssh-accounting-panel-udp' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/systemctl disable ssh-accounting-panel-udp' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/systemctl daemon-reload' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/zip' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/sbin/usermod' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/ssh' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/sshpass' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/bash' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/apt-get' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/mkdir' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/cmake' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/make' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/cat' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/scp' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/echo' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/ssh-keygen' | sudo EDITOR='tee -a' visudo &
    wait
    echo 'www-data ALL=(ALL:ALL) NOPASSWD:/usr/bin/nohup' | sudo EDITOR='tee -a' visudo &
    wait
#    sudo sed -i '/%sudo/s/^/#/' /etc/sudoers &
#    wait

    ######################
    ### Database Setup ###
    ######################

    printf "${GREEN}\nSetting up the database ...\n${NC}\n"

    # Try to login into mysql without a password
    result=$(check_mysql_connection)

    if [[ $result == *"Access denied"* ]]; then
        # root user has a password
        message="Enter the password of the 'root' user of the mysql service: "
        while true; do
            printf "${BLUE}${message}${NC}"
            read db_password

            result=$(check_mysql_connection "$db_password")

            if [[ -n $db_password && $result != *"Access denied"* ]]; then
                break
            else
                message="\nThe password is wrong, enter again: "
            fi
        done
    else
        printf "${BLUE}Set a password for the 'root' user of the mysql service [default: !12345678?]: ${NC}"
        read password
        db_password=${password:=!12345678?}

        # Execute the SQL query
        sudo mysql -u root -p"$db_password" -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '${db_password}'; FLUSH PRIVILEGES;"
    fi

    #####################
    ### Laravel Setup ###
    #####################

    printf "${GREEN}\nSetting up the framework ...${NC}\n"

    domain=""

    # Get domain name
    printf "${BLUE}\nEnter a domain for the panel or leave it empty: ${NC}"
    read domain

    # Get port number
    while true; do
        printf "${BLUE}\nEnter a port number for the panel [default: 3010]: ${NC}"
        read port_num
        port=${port_num:=3010}

        if netstat -tuln | grep -q ":$port\b"; then
            printf "${YELLOW}\nPort ${port} is in use. Choose another port.\n${NC}\n"
        else
            break;
        fi
    done

    install_worker

    # Go the project directory
    cd "/var/www/$project_name" || exit

    # Create a .env file using the sample file
    cp .env.example .env

    # Set the APP_URL in the .env
    if [ -z "$domain" ]; then
        domain=$(get_ip_address)
    fi
    app_url="$domain:$port"
    sed -i "s/APP_URL=.*/APP_URL=${app_url}/" .env

    # Set the DB_PASSWORD in the .env
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=${db_password}/" .env

    # Get the database name from the .env
    laravel_db_name=$(awk -F "=" '/^DB_DATABASE=/ {print $2}' .env)

    # Drop old database if it exists
    sudo mysql -u root -p"$db_password" -e "DROP DATABASE \`$laravel_db_name\`;" > /dev/null 2>&1

    # Create a database for the panel
    sudo mysql -u root -p"$db_password" -e "CREATE DATABASE \`$laravel_db_name\`;" > /dev/null 2>&1

    # Define the cron job command
    cron_job="* * * * * cd /var/www/sap && php artisan schedule:run >> /dev/null 2>&1"

    # Check if the cron job already exists in the user's crontab
    if ! crontab -u www-data -l 2>/dev/null | grep -Fq "$cron_job"; then
        # If the cron job doesn't exist, append it to the user's crontab
        (crontab -u www-data -l 2>/dev/null; echo "$cron_job") | crontab -u www-data -
    fi


    # Prepare laravel
    if [ $FRESH_COMPOSER == 1 ]; then
        COMPOSER_ALLOW_SUPERUSER=1 composer install
    fi
    COMPOSER_ALLOW_SUPERUSER=1 composer clear-cache
    COMPOSER_ALLOW_SUPERUSER=1 composer self-update
    COMPOSER_ALLOW_SUPERUSER=1 composer update --optimize-autoloader --ignore-platform-reqs

    npm install
    npm run build
    php artisan key:generate
    php artisan config:cache
    php artisan event:cache
    php artisan route:cache
    php artisan view:cache
    php artisan optimize
    php artisan migrate --force
    php artisan db:seed --force

    ####################
    ### Apache Setup ###
    ####################

    printf "${GREEN}\nSetting up the apache ...\n${NC}"

    apache_project_path="/var/www/$project_name"
    config_file="/etc/apache2/sites-available/$project_name.conf"

    # Create Apache configuration file
    cat >  "$config_file" << ENDOFFILE
<VirtualHost *:$port>
ENDOFFILE

    if [ -n "$domain" ]; then
        # Remove www. from the beginning of domain if it exists
        domain=$(echo "$domain" | sed 's/^www\.//')

        # Set domain alias
        domain_alias="www.$domain"

        echo "    ServerName $domain" >> "$config_file"
        echo "    ServerAlias $domain_alias" >> "$config_file"
    fi

    cat >> "$config_file" << ENDOFFILE

    DocumentRoot $apache_project_path/public

    <Directory $apache_project_path/public>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/sap_error.log
    CustomLog \${APACHE_LOG_DIR}/sap_access.log combined
</VirtualHost>
ENDOFFILE

    # Add the config to listen for the port only if it's not already set
    grep -wq "Listen $port" /etc/apache2/ports.conf || sudo bash -c "echo 'Listen $port' >> /etc/apache2/ports.conf"

    # Disable the default config
    sudo a2dissite 000-default.conf > /dev/null 2>&1

    # Remove the default config
    rm /etc/apache2/sites-available/000-default.conf > /dev/null 2>&1

    # Enable the site and restart Apache
    sudo a2ensite "$project_name".conf > /dev/null 2>&1

    # Enable mod_rewrite for Laravel routing
    sudo a2enmod rewrite > /dev/null 2>&1

    # Restart Apache
    sudo systemctl restart apache2

    ###############
    ### SSH Key ###
    ###############

    printf "${GREEN}\nCreating ssh keys ...\n${NC}"

    mkdir -p /var/www/.ssh > /dev/null 2>&1

    touch /var/www/.ssh/known_hosts > /dev/null 2>&1

    ssh-keygen -q -t rsa -b 4096 -N "" -C "$project_name" -f "/var/www/$project_name/storage/keys/ssh_accounting_panel" > /dev/null 2>&1

    chown -R www-data:www-data "/var/www/$project_name/storage/keys"
    chmod 700 "/var/www/$project_name/storage/keys"

    chown -R www-data:www-data "/var/www/$project_name/storage/keys/ssh_accounting_panel"
    chown -R www-data:www-data "/var/www/$project_name/storage/keys/ssh_accounting_panel.pub"

    chmod 700 "/var/www/$project_name/storage/keys/ssh_accounting_panel"
    chmod 700 "/var/www/$project_name/storage/keys/ssh_accounting_panel.pub"

    chown -R www-data:www-data /var/www/.ssh
    chmod -R 700  /var/www/.ssh

    chown -R www-data:www-data /var/www/.ssh/known_hosts
    chmod -R 700  /var/www/.ssh/known_hosts

    #########################
    ### Bash Script Alias ###
    #########################

    cd "$current_directory" || exit;

    mv -f sap.sh /usr/local/bin/ > /dev/null 2>&1

    chmod +x /usr/local/bin/sap.sh

    # The alias command
    alias_command="alias $cli_command=\"/usr/local/bin/sap.sh\""

    # Add the alias to the bash configuration file
    grep -wq "alias $cli_command" /root/.bashrc || echo "$alias_command" >> /root/.bashrc

    # Get the public ip address of the server if no domain is given
    if [ -z "$domain" ]; then
        domain=$(get_ip_address)
    fi

    # Done
    printf "${GREEN}\nInstallation is completed.\n${NC}"
    printf "${BLUE}\nThe panel address: ${GREEN}http://${domain}:${port}\n${NC}"
    printf "${BLUE}\nThe panel credentials:\n\nusername: ${GREEN}admin${BLUE}\npassword: ${GREEN}admin\n${NC}"
    printf "${BLUE}\nFrom now on you can access the menu using ${GREEN}${cli_command}${BLUE} command in your terminal\n${NC}\n"

    # Remove the script
    rm sap.sh > /dev/null 2>&1

    # Apply the changes
    #see: https://askubuntu.com/a/1041348/1710858
    exec bash
}

uninstall() {
    printf "${YELLOW}\nAre you sure that you want to uninstall the panel? [y/n] [default: n]: ${NC}"
    read answer
    is_user_sure=${answer:="n"}

    if [ "$is_user_sure" != "y" ]; then
        before_show_menu
        return 1
    fi

    printf "${BLUE}\nUninstalling the panel ...\n${NC}\n"

    # Define the cron job command to remove
    cron_job="* * * * * cd /var/www/sap && php artisan schedule:run >> /dev/null 2>&1"

    # Check if the cron job exists in the user's crontab
    if crontab -u www-data -l 2>/dev/null | grep -Fq "$cron_job"; then
        # If the cron job exists, remove it from the user's crontab
        crontab -u www-data -l 2>/dev/null | grep -Fv "$cron_job" | crontab -u www-data -
    fi

    cron_job="*/5 * * * * sh /var/www/ssh-accounting-panel/nethogs.sh"

    # Check if the cron job exists in the user's crontab
    if crontab -u www-data -l 2>/dev/null | grep -Fq "$cron_job"; then
        # If the cron job exists, remove it from the user's crontab
        crontab -u www-data -l 2>/dev/null | grep -Fv "$cron_job" | crontab -u www-data -
    fi

    cron_job="*/1 * * * * sh /var/www/ssh-accounting-panel/killExtraSession.sh"

    # Check if the cron job exists in the user's crontab
    if crontab -u www-data -l 2>/dev/null | grep -Fq "$cron_job"; then
        # If the cron job exists, remove it from the user's crontab
        crontab -u www-data -l 2>/dev/null | grep -Fv "$cron_job" | crontab -u www-data -
    fi

    apache_conf="/etc/apache2/sites-enabled/$project_name.conf"
    apache_port=$(grep -Po '(?<=<VirtualHost \*:)\d+' "$apache_conf")
    sed -i "/Listen $apache_port/d" /etc/apache2/ports.conf

    a2dissite "$project_name".conf > /dev/null 2>&1

    rm -rf "/var/www/$project_name" > /dev/null 2>&1
    rm -f "/etc/apache2/sites-available/$project_name.conf" > /dev/null 2>&1
    rm -f "/etc/apache2/sites-enabled/$project_name.conf" > /dev/null 2>&1
    rm -f "/etc/apache2/sites-available/$project_name-http.conf" > /dev/null 2>&1
    rm -f "/etc/apache2/sites-enabled/$project_name-http.conf" > /dev/null 2>&1

    sudo systemctl restart apache2

    rm -f /usr/local/bin/sap.sh > /dev/null 2>&1

    sed -i "/alias $cli_command/d" /root/.bashrc
    source /root/.bashrc > /dev/null 2>&1

    rm -rf /var/www/ssh-accounting-panel > /dev/null 2>&1
    rm -rf /var/www/.cache > /dev/null 2>&1
    rm -rf /var/www/.ssh/known_hosts > /dev/null 2>&1
    rm /etc/systemd/system/ssh-accounting-panel-udp.service > /dev/null 2>&1

    deluser ssh-accounting-panel-udp >/dev/null 2>&1

    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/sbin\/adduser/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/sbin\/useradd/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/sbin\/deluser/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/sed/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/passwd/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/sbin\/chpasswd/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/curl/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/kill/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/killall/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/pkill/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/rm/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/mv/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/cp/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/touch/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/grep/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/chmod/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/chown/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/crontab/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/sbin\/nethogs/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/nethogs/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/local\/sbin\/nethogs/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/sbin\/service/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/bin\/systemctl restart apache2/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/bin\/systemctl start ssh-accounting-panel-udp/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/bin\/systemctl stop ssh-accounting-panel-udp/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/bin\/systemctl enable ssh-accounting-panel-udp/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/bin\/systemctl disable ssh-accounting-panel-udp/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/bin\/systemctl daemon-reload/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/systemctl restart apache2/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/systemctl start ssh-accounting-panel-udp/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/systemctl stop ssh-accounting-panel-udp/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/systemctl enable ssh-accounting-panel-udp/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/systemctl disable ssh-accounting-panel-udp/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/systemctl daemon-reload/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/zip/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/sbin\/usermod/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/ssh/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/sshpass/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/bash/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/apt-get/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/mkdir/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/cmake/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/make/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/cat/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/scp/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/echo/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/ssh-keygen/d' /etc/sudoers &
    wait
    sudo sed -i '/www-data ALL=(ALL:ALL) NOPASSWD:\/usr\/bin\/nohup/d' /etc/sudoers &
    wait

    rm -r "/etc/supervisor/conf.d/$project_name-worker.conf" > /dev/null 2>&1
    sudo supervisorctl reread > /dev/null 2>&1
    sudo supervisorctl update > /dev/null 2>&1
    sudo supervisorctl restart all > /dev/null 2>&1

    printf "${GREEN}\nUninstallation is completed.\n${NC}\n"
}

update() {
    cd "/var/www/$project_name" || exist;

    current_sha=$(cat version.info)
    latest_sha=$(curl -s "$project_latest_commit_link" | jq -r .sha)

    if [ "$current_sha" = "$latest_sha" ]; then
        printf "\n${GREEN}The panel is up to date\n${NC}\n"
        before_show_menu
        return 1
    fi

    cd /root || exit;
    wget -O sap.sh https://raw.githubusercontent.com/armineslami/SSH-Accounting-Panel/master/sap.sh && sudo bash sap.sh update
}

update_panel() {
    printf "${BLUE}\nUpdating the panel ...\n${NC}\n"

    install_packages

    install_worker

    cd "/var/www/$project_name" || exist;

    # Check if 'origin' remote exists
    if ! git remote get-url origin | grep -q "$project_source_link"; then
        git remote add origin "$project_source_link"
    fi

    git config --global --add safe.directory "/var/www/$project_name" > /dev/null 2>&1

    # Discard local changes
    git reset --hard

    # Pull from git
    git pull origin master

    # Update laravel framework
    COMPOSER_ALLOW_SUPERUSER=1 composer update --optimize-autoloader

    npm install
    npm run build

    # Update the APP_VERSION in .env
    sed -i "s/^\(APP_VERSION=\).*/\1$project_version/" .env

    php artisan config:clear
    php artisan event:clear
    php artisan route:clear
    php artisan view:clear

    php artisan config:cache
    php artisan event:cache
    php artisan route:cache
    php artisan view:cache
    php artisan optimize
    php artisan migrate --force

    # Create a file that holds the sha of latest commit
    rm -f version.info > /dev/null 2>&1
    curl -s "$project_latest_commit_link" | jq -r .sha > version.info

    cd /root || exit;
    mv -f sap.sh /usr/local/bin/ > /dev/null 2>&1
    chmod +x /usr/local/bin/sap.sh

    printf "\n${GREEN}The panel updated to v$project_version\n${NC}\n"
}

show_config() {
    apache_conf="/etc/apache2/sites-available/$project_name.conf"
    apache_domain=$(grep -E "^ *ServerName" "$apache_conf" | awk '{print $2}')
    apache_port=$(grep -Po '(?<=<VirtualHost \*:)\d+' "$apache_conf")
    is_running="${GREEN}YES${NC}"

    if [ ! -f "/etc/apache2/sites-enabled/$project_name.conf" ]; then
        is_running="${RED}NO${NC}"
    fi

    if [ -z "$apache_domain" ]; then
        apache_domain=$(get_ip_address)
    fi

    printf "
${GREEN}$project_display_name${NC}

Version: ${GREEN}$project_version${NC}
domain: ${GREEN}$apache_domain${NC}
port: ${GREEN}$apache_port${NC}
running: $is_running

"

before_show_menu
}

set_port() {
    while true; do
        printf "${BLUE}\nEnter a port number for the panel: ${NC}"
        read new_port

        if [ -z "$new_port" ]; then
            continue;
        elif netstat -tuln | grep -q ":$new_port\b"; then
            printf "${YELLOW}\nPort ${new_port} is in use. Choose another port.\n${NC}"
        else
            break;
        fi
    done

    # Config file
    apache_conf="/etc/apache2/sites-available/$project_name.conf"

    # Get current port
    current_port=$(grep -Po '(?<=<VirtualHost \*:)\d+' "$apache_conf")

    # Update the port in the config file
    sed -i "s/<VirtualHost \*:.*>/<VirtualHost *:$new_port>/" "$apache_conf"

    # Remove current port listen
    sed -i "/Listen $current_port/d" /etc/apache2/ports.conf

    # Add the new port to the ports.conf only if it's not already there
    grep -wq "Listen $new_port" /etc/apache2/ports.conf || sudo bash -c "echo 'Listen $new_port' >> /etc/apache2/ports.conf"

    sudo a2ensite "$project_name".conf > /dev/null 2>&1
    sudo systemctl restart apache2

    cd "/var/www/$project_name" || exist

    app_url="$domain:$new_port"
    sed -i "s/\(APP_URL=.*:\)[0-9]\+\(.*\)/\1$new_port\2/" .env

    php artisan config:cache

    printf "${GREEN}\nThe panel port changed to $new_port\n${NC}"

    before_show_menu
}

set_domain() {
    printf "${BLUE}\nEnter a domain for the panel (can be empty): ${NC}"
    read new_domain

    # Config file
    apache_conf="/etc/apache2/sites-available/$project_name.conf"

    # If domain is empty remove the ServerName
    if [ -z "$new_domain" ]; then
        sudo sed -i "/ServerName/d" "$apache_conf"
    else
        if grep -q "ServerName" "$apache_conf"; then
            # Add a new ServerName because none is set
            sudo sed -i "s/ServerName .*/ServerName $new_domain/" "$apache_conf"
        else
            # Update the ServerName because one is set
            port=$(grep -Po '(?<=<VirtualHost \*:)\d+' "$apache_conf")
            sudo sed -i "/<VirtualHost \*:$port>/a \    ServerName $new_domain" "$apache_conf"
        fi
    fi

    sudo a2ensite "$project_name".conf > /dev/null 2>&1
    sudo systemctl restart apache2

    cd "/var/www/$project_name" || exist

    app_url_domain=$new_domain

    if [ -z "$new_domain" ]; then
        app_url_domain=$(get_ip_address)
    fi

    sed -i "s/APP_URL=[^:]*:/APP_URL=${app_url_domain}:/g" .env

    php artisan config:cache

    if [ -n "$new_domain" ]; then
        printf "${GREEN}\nThe panel domain changed to $new_domain.\n${NC}"
    else
        printf "${GREEN}\nThe panel domain removed.\n${NC}"
    fi

    before_show_menu
}

toggle_server() {
    if [ -f "/etc/apache2/sites-enabled/$project_name.conf" ]; then
        # Stop the virtual host
        sudo a2dissite "$project_name".conf > /dev/null 2>&1
        printf "${GREEN}\nThe panel has stopped.\n${NC}"
    else
        # Start the virtual host
        sudo a2ensite "$project_name".conf > /dev/null 2>&1
        printf "${GREEN}\nThe panel has started.\n${NC}"
    fi

    sudo systemctl restart apache2

    before_show_menu
}

install_ssl_certificate() {
    printf "${BLUE}\nTo install SSL certificate, port 80 must be open and available. Do you confirm?: [y/n] [default: y]: ${NC}"
    read answer
    continue=${answer:="y"}

    if [ "$continue" != "y" ]; then
        exit 1;
    fi

    panel_conf="/etc/apache2/sites-available/$project_name.conf"

    # Get the current port
    current_port=$(grep -Po '(?<=<VirtualHost \*:)\d+' "$panel_conf")

    domain=$(grep -E "^ *ServerName" "$panel_conf" | awk '{print $2}')

    if [ -z "$domain" ]; then
        printf "${RED}\nThere is no doamin set for the panel. You should first set a domain\n${NC}\n"
        before_show_menu
        return 1
    fi

    # Remove www. from the beginning of domain if it exists
    domain=$(echo "$domain" | sed 's/^www\.//')
    domain_alias="www.$domain"

    printf "${GREEN}\nCreating SSL certificate ...\n${NC}"

    while true; do
        printf "${BLUE}\nEnter a port for ssl: [default: 443]: ${NC}"
        read port
        ssl_port=${port:=443}

        if [ "$ssl_port" == 80 ] || [ "$ssl_port" == 8080 ]; then
            printf "${YELLOW}\nYou can not use port ${ssl_port}\n${NC}"
        elif [ "$current_port" != "$ssl_port" ]; then
            if netstat -tuln | grep -q ":$ssl_port\b"; then
                printf "${YELLOW}\nPort ${ssl_port} is in use. Choose another port${NC}"
            else
                break;
            fi
        else
            # Add ssl for current port
            break;
        fi
    done

    # Add a new config to listen on port 80 so certbot can renew ssl certificate
    sudo cp -f "/etc/apache2/sites-available/$project_name.conf" "/etc/apache2/sites-available/$project_name-http.conf"
    sed -i "s/<VirtualHost \*:.*>/<VirtualHost *:80>/" "/etc/apache2/sites-available/$project_name-http.conf"
    if [ "$current_port" != 80 ]; then
        # If current port is not 80, active an virtual host that listens on port 80 so certbot can verify
        grep -wq "Listen 80" /etc/apache2/ports.conf || sudo bash -c "echo 'Listen 80' >> /etc/apache2/ports.conf"
        a2ensite "$project_name-http".conf > /dev/null 2>&1
    fi

    sudo ufw allow 'Apache Full'
    sudo ufw delete allow 'Apache'

    output=$(sudo certbot certonly --apache --force-renewal -d "$domain" -d "$domain_alias" 2>&1 & wait $!)

    if ! echo "$output" | grep -q -E "Congratulations|Successfully received certificate"; then
        printf "${RED}\nFailed to create SSL certificate\n${NC}\n"
        printf "${RED}\n${output}${NC}\n"
        rm -rf "/etc/apache2/sites-available/$project_name-http.conf"
        a2dissite "$project_name-http".conf > /dev/null 2>&1
        before_show_menu
        return 1
    fi

    printf "${BLUE}\n${output}${NC}\n"

    # Get directory name of created certificate
    directory_name=$(echo "$output" | grep -oP '(?<=/live/)[^/]+(?=/fullchain.pem)')

    cert_path="/etc/letsencrypt/live/$directory_name/fullchain.pem"
    key_path="/etc/letsencrypt/live/$directory_name/privkey.pem"

    printf "${GREEN}\nInstalling SSL certificate ... \n${NC}"

    # Remove </VirtualHost> from the end of the file, append SSL configuration, and add </VirtualHost> back
    sudo sed -i -e '/<\/VirtualHost>$/d' "$panel_conf"
    sudo tee -a "$panel_conf" >/dev/null <<EOF

    # SSL Configuration
    Include /etc/letsencrypt/options-ssl-apache.conf
    SSLCertificateFile $cert_path
    SSLCertificateKeyFile $key_path
</VirtualHost>
EOF

    # Update the port in the config file
    sed -i "s/<VirtualHost \*:.*>/<VirtualHost *:$ssl_port>/" "$panel_conf"

    if [ "$current_port" != 80 ]; then
        o_port="Listen $current_port"
        n_port="Listen $ssl_port"
        sudo sed -i "s/$o_port/$n_port/" /etc/apache2/ports.conf
    else
        # Remove old port
        sed -i "/Listen $current_port/d" /etc/apache2/ports.conf
        # Add new port
        grep -wq "Listen $ssl_port" /etc/apache2/ports.conf || sudo bash -c "echo 'Listen $ssl_port' >> /etc/apache2/ports.conf"
    fi

    a2ensite "$project_name-http".conf > /dev/null 2>&1
    sudo a2enmod ssl > /dev/null 2>&1
    sudo systemctl restart apache2 > /dev/null 2>&1

    printf "${GREEN}\nSSL cretificate is now installed.\n${NC}\n"

    before_show_menu
}

before_show_menu() {
    echo && echo -n -e "${YELLOW}Hit enter to return to the menu: ${NC}" && read temp
    clear
    show_menu
}

show_menu() {
    echo -e "
${GREEN}SAP menu${NC}

  ${GREEN}0.${NC} Exit
————————————————
  ${GREEN}1.${NC} Install
  ${GREEN}2.${NC} Update
  ${GREEN}3.${NC} Uninstall
————————————————
  ${GREEN}4.${NC} Show   Config
  ${GREEN}5.${NC} Change Port
  ${GREEN}6.${NC} Change Domain
  ${GREEN}7.${NC} Start / Stop
  ${GREEN}8.${NC} Install SSL Certificate
"

    echo && read -p "Please enter a valid number [0-8]: " num

    case "${num}" in
        0)
            exit 0
            ;;
        1)
            is_uninstalled && install
            ;;
        2)
            is_installed && update
            ;;
        3)
            is_installed && uninstall
            ;;
        4)
            is_installed && show_config
            ;;
        5)
            is_installed && set_port
            ;;
        6)
            is_installed && set_domain
            ;;
        7)
            is_installed && toggle_server
            ;;
        8)
            is_installed && install_ssl_certificate
            ;;
        *)
            printf "${RED}\nError: Please enter a valid number [0-8]: \n${NC}\n"
            show_menu
            ;;
    esac
}

main() {
    # Check if user has root access
    if [ "$(isRoot)" != "true" ]; then
    	printf "${RED}Error: You must run this script as root!${NC}\n"
    	exit 1
    fi

    action="$1"

    if [ "$action" = "" ]; then
        clear
        # Let the user know that installing is started
        printf "${GREEN}\n###########################\n\n${project_display_name} v${project_version}\n\n###########################\n${NC}\n"
        show_menu
    elif [ "$action" = "update" ]; then
        update_panel
    elif [ "$action" = "dev" ]; then
        DEV=1
        install
    else
        printf "${RED}Error: Invalid action${NC}\n"
    fi
}

main "$@"
