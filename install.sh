#!/bin/bash

#################
### Variables ###
#################

# Panel info
project_display_name="SSH Accounting Panel"
project_version="1.0.0"
project_name="ssh-accounting-panel"
project_branch_name="master"
project_source_link="https://github.com/armineslami/SSH-Accounting-Panel/archive/refs/heads/master.zip"

# Colors
RED="\033[0;31m"
BLUE='\033[0;34m'
GREEN='\033[0;32m'
NC="\033[0m" # No Color

# Required packages
packages="php php-cli php-mysql php-mbstring php-xml php-curl php-zip cron apache2 mariadb-server nodejs npm sshpass openssh-client openssh-server unzip jq curl"

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
    if [ -x "$(command -v yum)" ]; then
        echo "yum"
    elif [ -x "$(command -v apt-get)" ]; then
        echo "apt-get"
    else
        echo "Unsupported"
    fi
}

# Installs required packages
install_packages() {
    #Get the package manager name from the input
    local package_manager=$1

    #Install required packages
    sudo "$package_manager" -y install $packages
}

check_mysql_connection() {
    if [ -z "$1" ]; then
        result=$(mysql -u root -e "SELECT 1" 2>&1)
    else
        result=$(mysql -u root -p"$1" -e "SELECT 1" 2>&1)
    fi
    echo "$result"
}

install() {
    local package_manager
    package_manager=$(get_package_manager_name)

    ############################
    ### Package Installation ###
    ############################

    printf "${BLUE}Installing required packages ...${NC}\n"

     # Install required packages based on OS
    if [ "$package_manager" = "yum" ]; then
        # CentOS/RHEL
        sudo "$package_manager" -y update
        install_packages "$package_manager"
    elif [ "$package_manager" = "apt-get"  ]; then
        # Debian/Ubuntu
        sudo DEBIAN_FRONTEND=noninteractive "$package_manager" -y update
        install_packages "$package_manager"
        sudo DEBIAN_FRONTEND=interactive
    # couldn't find package manger of the OS
    else
        printf "${RED}Error: Unsupported distribution or package manager!.${NC}\n"
        exit 1
    fi

    printf "${BLUE}Installing packages is done.${NC}\n"

    # Remove old source file if it exists
    sudo rm -f "$project_name.zip" > /dev/null 2>&1

    # If the project already exits, remove everything inside it's folder
    sudo rm -rf "$project_name/*" > /dev/null 2>&1

    printf "${BLUE}Downloading the project from the github ...${NC}\n"

    #############################
    ### Composer Installation ###
    #############################

    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php -r "if (hash_file('sha384', 'composer-setup.php') === 'e21205b207c3ff031906575712edab6f13eb0b361f2085f1f1237b7126d785e826a450292b6cfd1d64d92e6563bbde02') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
    php composer-setup.php
    php -r "unlink('composer-setup.php');"
    sudo mv composer.phar /usr/local/bin/composer

    #####################
    ### Project Clone ###
    #####################

    # Download the source files
    wget -O "$project_name.zip" "$project_source_link"

    # Unzip the downloaded file
    unzip "$project_name.zip"

    if [ ! -d "$project_name" ]; then
        sudo mkdir "$project_name"
    fi

    # Rename project folder
    sudo mv "$project_name-$project_branch_name/*" "$project_name"/

    # Delete unzipped file
    sudo rm -rf "$project_name-$project_branch_name/*"

    # Delete downloaded file
    sudo rm -f "$project_name.zip"

    printf "${BLUE}Moving project to apache directory ...${NC}\n"

    # Go to apache directory
    cd /var/www || exit

    # Move the project into apache directory
    mv "$project_name"  /var/www/

    # Set the right permissions
    chown -R www-data:www-data "/var/www/$project_name"
    chmod +x  "/var/www/$project_name/app/Scripts"

    ######################
    ### Database Setup ###
    ######################

    # Try to login into mysql without a password
    result=$(check_mysql_connection)

    if [[ $result == *"Access denied"* ]]; then
        # root user has a password
        message="Enter the password of the 'root' user of mysql service: "
        while true; do
            printf "${BLUE}${message}${NC}"
                read db_password

            result=$(check_mysql_connection "$db_password")
            if [[ -n $db_password && $result != *"Access denied"* ]]; then
                break
            else
                message="The password is wrong, enter again: "
            fi
        done
    else
        printf "${BLUE}Set a password for the 'root' user of mysql service [default: !12345678?]: ${NC}"
        read password
        db_password=${password:=!12345678?}

        # Execute the SQL query
        sudo mysql -u root -p"$db_password" -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '${db_password}'; FLUSH PRIVILEGES;"
    fi

    #####################
    ### Laravel Setup ###
    #####################

    # Go the project directory
    cd "/var/www/$project_name" || exit

    # Create a .env file using the sample file
    cp .env.example .env

    # Set the DB_PASSWORD inside the .env
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=${db_password}/" .env

    # Get the database name from the .env
    laravel_db_name=$(awk -F "=" '/^DB_DATABASE=/ {print $2}' .env)

    # Create a database for the panel
    sudo mysql -u root -p"$db_password" -e "CREATE DATABASE \`$laravel_db_name\`;"

    # Prepare laravel
    composer install --optimize-autoloader --no-dev
    php artisan config:cache
    php artisan event:cache
    php artisan route:cache
    php artisan view:cache
    php artisan optimize
    php artisan key:generate
    npm run build
    sh app/Scripts/ServerCronJob.sh
    php artisan migrate:refresh --seed

    ####################
    ### Apache Setup ###
    ####################

    laravel_project_path="/var/www/$project_name"
    domain="your_domain.com"
    config_file="/etc/apache2/sites-available/$project_name.conf"

    # Get domain or ip address
    while true; do
        printf "${BLUE}Enter a domain or IP address for the panel: ${NC}"
            read domain

        if [[ -n $domain ]]; then
            break
        fi
    done

    while true; do
        printf "${BLUE}Enter a port number for the panel: ${NC}"
        read port

        if [[ -n $port ]]; then
            break
        fi
    done

    random_text=$(openssl rand -base64 4 | cut -c1-5)

    # Create Apache configuration file
    cat >  "$config_file" << ENDOFFILE
<VirtualHost *:$port>
    ServerName $domain
    ServerAlias www.$domain

    DocumentRoot $laravel_project_path/public

    <Directory $laravel_project_path/public>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Require all granted
    </Directory>

    AliasMatch "^/(?!$random_text)" "/nonexistent_path"

    ErrorLog \${APACHE_LOG_DIR}/ssh-accounting-panel_error.log
    CustomLog \${APACHE_LOG_DIR}/ssh-accounting-panel_access.log combined
</VirtualHost>
ENDOFFILE

    # Disable the default config
    sudo a2dissite 000-default.conf

    # Enable the site and restart Apache
    sudo a2ensite "$project_name".conf

    # Enable mod_rewrite for Laravel routing
    sudo a2enmod rewrite

    # Restart Apache
    sudo systemctl restart apache2

    # Done
    printf "${BLUE}\nPanel address: ${GREEN}${domain}:${port}/${random_text}.\n${NC}\n"
    printf "${BLUE}\nPanel credentials:\n\nusername: ${GREEN}admin${BLUE}\npassword: ${GREEN}admin\n${NC}\n"
    printf "${GREEN}\nInstallation is completed.\n${NC}\n"
}

#######################
### Install Process ###
#######################

main() {
    # Let the user know that installing is started
    printf "${BLUE}${project_display_name} v${project_version}${NC}\n"

    # Check if user has root access
    if [ "$(isRoot)" != "true" ]; then
    	printf "${RED}Error: You must run this script as root!.${NC}\n"
    	exit 1
    fi

    install
}

main



