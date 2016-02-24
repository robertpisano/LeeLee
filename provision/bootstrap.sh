#!/usr/bin/env bash

echo "--------------------------------------"
echo "Provisioning virual machine..."
echo "--------------------------------------"

# Use single quotes instead of double quotes to make it work with special-character passwords
PASSWORD='MattLeeRobMike123'

echo "--------------------------------------"
echo "Update & Upgrade apt-get..."
echo "--------------------------------------"

# update / upgrade
sudo apt-get update
sudo apt-get -y upgrade

echo "--------------------------------------"
echo "Installing Apache & PHP"
echo "--------------------------------------"

# install apache 2.5 and php 5.5
sudo apt-get install -y apache2
if ! [ -L /var/www ]; then
  rm -rf /var/www
  ln -fs /vagrant /var/www
fi
sudo apt-get install -y php5
sudo apt-get install -y php5-curl

echo "--------------------------------------"
echo "Installing MySQL"
echo "--------------------------------------"

# install mysql and give password to installer
sudo debconf-set-selections <<< "mysql-server mysql-server/root_password password $PASSWORD"
sudo debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $PASSWORD"
sudo apt-get -y install mysql-server
sudo apt-get install php5-mysql

echo "--------------------------------------"
echo "Installing phpmyadmin"
echo "--------------------------------------"

# install phpmyadmin and give password(s) to installer
# for simplicity I'm using the same password for mysql and phpmyadmin
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/dbconfig-install boolean true"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/app-password-confirm password $PASSWORD"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/admin-pass password $PASSWORD"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/app-pass password $PASSWORD"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2"
sudo apt-get -y install phpmyadmin

echo "--------------------------------------"
echo "Setting up DB"
echo "--------------------------------------"

# variables
dbName='Cibo_NY'

# look to see if the database is installed yet
result=`mysqlshow --user=root -pMattLeeRobMike123 $dbName | grep -v Wildcard | grep -o $dbName`

if [ "$result" == $dbName ]
  # if it's already installed, just indicate such
  then
    echo 'Database already installed.'

  # if it's not installed, install it
  else
    echo "$result - $dbName"
    echo "Database $dbName not yet installed... creating using mysql"
    mysql -u root -pMattLeeRobMike123 -e "CREATE DATABASE IF NOT EXISTS $dbName;"

    echo "Inserting SQL dump"
    mysql -u root -pMattLeeRobMike123 Cibo_NY < "/home/vagrant/Cibo/API/app_media.sql"

    echo "Database $dbName should be installed, drop then run this script again to reinstall."
fi

echo "--------------------------------------"
echo "Setting up VirtualHosts"
echo "--------------------------------------"

# setup hosts file
VHOST=$(cat <<EOF
ServerName localhost
<VirtualHost *:80>
    DocumentRoot "/home/vagrant/Cibo/API/web"
    <Directory "/home/vagrant/Cibo/API/web">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
EOF
)
echo "${VHOST}" > /etc/apache2/sites-available/000-default.conf

# enable mod_rewrite
sudo a2enmod rewrite

echo "--------------------------------------"
echo "Install Git"
echo "--------------------------------------"

# install git
sudo apt-get -y install git

echo "--------------------------------------"
echo "Install Composer"
echo "--------------------------------------"

# install Composer
curl -s https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

echo "--------------------------------------"
echo "Setting up log files"
echo "--------------------------------------"

# make log file
if [ ! -d "/home/vagrant/Cibo/API/App/log/" ]
  then
    echo "Log folder doesn't exist, creating..."
    mkdir /home/vagrant/Cibo/API/app/log
  else
    echo "Log folder exists, skipping."
fi

if [ ! -f "/home/vagrant/Cibo/API/app/log/app.log" ]
  then
    echo "Log file doesn't exist, creating..."
    touch /home/vagrant/Cibo/API/app/log/app.log
  else
    echo "Log file exists, skipping."
fi

echo "--------------------------------------"
echo "Restarting Apache"
echo "--------------------------------------"

# restart apache
sudo service apache2 restart