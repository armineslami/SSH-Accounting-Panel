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
root_path=$(openssl rand -base64 4 | cut -c1-5)
cli_command="spa"

# Colors
RED="\033[0;31m"
BLUE='\033[0;34m'
GREEN='\033[0;32m'
NC="\033[0m" # No Color

# Required packages
packages="php php-cli php-mysql php-mbstring php-xml php-curl openssl php-zip cron apache2 mariadb-server nodejs npm sshpass openssh-client openssh-server unzip jq curl"

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

is_installed() {
    apache_config_file="/etc/apache2/sites-enabled/$project_name.conf"
    if [ -f "$apache_config_file" ]; then
        return 0 # installed
    else
        printf "${RED}\nYou must first install the panel\n${NC}\n"
        before_show_menu
        return 1 # no installed
    fi
}

is_uninstalled() {
    apache_config_file="/etc/apache2/sites-enabled/$project_name.conf"
    if [ ! -f "$apache_config_file" ]; then
        return 0 # not installed
    else
        printf "${RED}\nThe panel is already installed\n${NC}\n"
        before_show_menu
        return 1 # installed
    fi
}

install() {
    local package_manager
    package_manager=$(get_package_manager_name)

    ############################
    ### Package Installation ###
    ############################

    printf "${BLUE}\nInstalling required packages ...\n${NC}\n"

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
        printf "${RED}\nError: Unsupported distribution or package manager!.\n${NC}\n"
        exit 1
    fi

    printf "${BLUE}\nInstalling packages is done.\n${NC}\n"

    # Remove old source file if it exists
    sudo rm -f "$project_name.zip" > /dev/null 2>&1

    # If the project already exits, remove everything inside it's folder
    sudo rm -rf "$project_name/*" > /dev/null 2>&1

    printf "${BLUE}\nDownloading the project from the github ...\n${NC}\n"

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

    # Delete the unzipped file
    sudo rm -rf "$project_name-$project_branch_name/*"

    # Delete the zipped file
    sudo rm -f "$project_name.zip"

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

    printf "${BLUE}\nSetting up the database ...\n${NC}\n"

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

    printf "${BLUE}\nSetting up the framework ...\n${NC}\n"

    # Go the project directory
    cd "/var/www/$project_name" || exit

    # Create a .env file using the sample file
    cp .env.example .env

    # Set the DB_PASSWORD inside the .env
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=${db_password}/" .env

    # Get the database name from the .env
    laravel_db_name=$(awk -F "=" '/^DB_DATABASE=/ {print $2}' .env)

    # Drop old database if it exists
    sudo mysql -u root -p"$db_password" -e "DROP DATABASE \`$laravel_db_name\`;" > /dev/null 2>&1

    # Create a database for the panel
    sudo mysql -u root -p"$db_password" -e "CREATE DATABASE \`$laravel_db_name\`;" > /dev/null 2>&1

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

    printf "${BLUE}\nSetting up the apache ...\n${NC}\n"

    apache_project_path="/var/www/$project_name"
    domain="your_domain.com"
    config_file="/etc/apache2/sites-available/$project_name.conf"

    # Get domain name
    printf "${BLUE}Enter a domain for the panel if you've got one or leave it empty: ${NC}"
    read domain

    # Get port number
    printf "${BLUE}Enter a port number for the panel [default: 3010]: ${NC}"
    read port_num
    port=${port_num:=!3010?}

    # Remove www. from the beginning of domain if it exists
    domain=$(echo "$domain" | sed 's/^www\.//')

    # Set domain alias
    domainAlias=""
    if [[ -n $domain ]]; then
        domainAlias="www.$domain"
    fi

    # Create Apache configuration file
    cat >  "$config_file" << ENDOFFILE
<VirtualHost *:$port>
    ServerName $domain
    ServerAlias $domainAlias

    DocumentRoot $apache_project_path/public

    <Directory $apache_project_path/public>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Require all granted
    </Directory>

    <Location "/$root_path">
        Require all granted
    </Location>

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

    #######################################
    ### Create an alias for bash script ###
    #######################################

    mv ~/main.sh /usr/local/bin/

    chmod +x /usr/local/bin/main.sh

    # The alias command
    alias_command="alias $cli_command=\"usr/local/bin/main.sh\""

    # Add the alias to the bash configuration file
    echo "$alias_command" >> ~/.bashrc

    # Apply the changes
    . ~/.bashrc > /dev/null 2>&1

    # Done
    printf "${GREEN}\nInstallation is completed.\n${NC}"
    printf "${BLUE}\nPanel address: ${GREEN}${domain}:${port}/${root_path}.\n${NC}"
    printf "${BLUE}\nPanel credentials:\n\nusername: ${GREEN}admin${BLUE}\npassword: ${GREEN}admin\n${NC}"
    printf "${BLUE}\nFrom now on you can access the menu using 'spa' command in your terminal\n${NC}"
}

uninstall() {
    printf "\n${YELLOW}Uninstall functionality is not completed yet ...${NC}\n"
}

update() {
    printf "\n${YELLOW}Update functionality is not completed yet ...${NC}\n"
}

show_config() {
    apache_conf="/etc/apache2/sites-enabled/$project_name.conf"
    apache_domain=$(grep -E "^ *ServerName" "$apache_conf" | awk '{print $2}')
    apache_port=$(grep -Po '(?<=<VirtualHost \*:)\d+' "$apache_conf")
    apache_root_path=$(grep -Po '<Location "\K[^"]+' "$apache_conf")

    printf "
${GREEN}$project_display_name${NC}

Version: ${BLUE}$project_version${NC}
domain: ${BLUE}$apache_domain${NC}
port: ${BLUE}$apache_port${NC}
root path: ${BLUE}$apache_root_path${NC}

"

before_show_menu
}

set_port() {
    printf "${BLUE}Enter a port number for the panel: ${NC}"
    read port

    apache_conf="/etc/apache2/sites-available/$project_name.conf"
    sed -i "s/<VirtualHost \*:.*>/<VirtualHost *:$port>/" "$apache_conf"

    sudo a2ensite "$project_name".conf
    sudo systemctl restart apache2

    printf "${GREEN}\nPanel Port changed to $port.\n${NC}"

    before_show_menu
}

before_show_menu() {
    echo && echo -n -e "${YELLOW}Enter to return to the SAP menu: ${NC}" && read temp
    clear
    show_menu
}

show_menu() {
    echo -e "
${GREEN}SAP menu${NC}

  ${GREEN}0.${NC} exit
————————————————
  ${GREEN}1.${NC} install
  ${GREEN}2.${NC} update
  ${GREEN}3.${NC} uninstall
————————————————
  ${GREEN}4.${NC} show config
  ${GREEN}5.${NC} change port
"

    echo && read -p "please input a legal number[0-5]: " num

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
        4 )
            is_installed && show_config
            ;;
        5)
            is_installed && set_port
            ;;
        *)
            printf "${RED}Error: Please input a legal number[0-5].${NC}\n"
            ;;
    esac
}

main() {
    clear

    # Let the user know that installing is started
    printf "${GREEN}\n###########################\n\n${project_display_name} v${project_version}\n\n###########################\n${NC}\n"

    # Check if user has root access
    if [ "$(isRoot)" != "true" ]; then
    	printf "${RED}Error: You must run this script as root!.${NC}\n"
    	exit 1
    fi

    show_menu
}

main



