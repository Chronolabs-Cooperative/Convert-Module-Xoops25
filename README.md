# Chronolabs Cooperative
# Web Font Converter + TCPDF  - http://fonts4web.org.uk -

# BASIC INSTALLATION MANUAL
## by. Simon Antony Roberts (Sydney)
## simon@snails.email

# Foreword

In this manual we will take you through all the conditions which you will encounter in general Ubuntu or Debian environment setting up this API. It will include cronjobs as well as any basis of general configuration you will encounter with your API in configuring and definition operations parameters in most types of places you find Ubuntu or Debian.


# Setting up the environment
You will first have to set up the environment running the following script at root on Ubuntu or Debian Server it will install all the system environment variables you require to do an installation:-

    
    $ sudo apt-get install zip unzip fontforge cpulimit
    

Now you will have to execute 'tasksel' in root with the 'sudo' precursor for this to install the LAMP environment; run the following command and install the LAMP environment.

    
    $ sudo tasksel install lamp-server

    
You will need to create these fonts folders now with 'mkdir' and then copy the contents of the XOOPS 2.5.x distribution to the path then with the module from this repository to the allocated path resolution.

    
    $ sudo mkdir /var/www/fonts-converter
    $ sudo chown -Rfv web:www-data /var/www/fonts-converter
    $ sudo chmod -Rfv 0777 /var/www/fonts-converter
    

We are going to assume for the fonting api runtime PHP files you are going to store them in /var/www/fonts-converter and this will be the path you have to unpack the downloaded archive from Chronolabs APIs on sourceforge.net into with the contants.php listed in the root of this folder.

## Setting Up Apache 2 (httpd)
We are going to assume your domain your setting it up on is a sub-domain of mysite.com so the following example in installing and setting up Apache 2 examples will place this on the sub-domain of fonts.mysite.com.

You will have to make the file /etc/apache2/sites-available/fonts.mysite.com.conf which you can with the following command:-
$ sudo nano /etc/apache2/sites-available/fonts.mysite.com.conf
You need to put the following configuration in to run a standard site, there is more to add for SSL which is not included in this example but you can find many examples on what to add to this file for port 443 for SSL which is duplicated code for port 443 not 80 with the SSL Certificates included, use the following code as your measure of basis of what to configure for apache 2 (httpd):-

    
    <VirtualHost *:80>
           ServerName fonts.mysite.com
           ServerAdmin webmaster@mysite.com
           DocumentRoot /var/www/fonts-converter
           ErrorLog /var/log/apache2/fonts.mysite.com-error.log
           CustomLog /var/log/apache2/fonts.mysite.com-access.log common
           <Directory /var/www/fonts-converter>
                   Options Indexes FollowSymLinks MultiViews
                   AllowOverride All
                   Require all granted
           </Directory>
    </VirtualHost>
    

You need to now enable this website in apache the following command will do this from root:-

    
    $ sudo a2ensite fonts.mysite.com
    $ sudo service apache2 reload
    

This is all that is involved in configuring apache 2 httpd on Debian/Ubuntu, the next step is the database.

## Apache2 Shorten URL (Mod Rewrite)

The following .htaccess goes in /var/www/font-converter

    RewriteEngine On
    RewriteRule ^convert/preview/(.*?).png			./modules/convert/preview.php?id=$1		[L,NC,QSA]
    RewriteRule ^convert/naming/(.*?).png			./modules/convert/naming.php?id=$1		[L,NC,QSA]
    RewriteRule ^convert/glyph/(.*?)-([0-9]+).png		./modules/convert/glyph.php?id=$1&char=$2	[L,NC,QSA]
    RewriteRule ^convert/font/(.*?)/(.*?).html			./modules/convert/font.php?id=$2		[L,NC,QSA]
    RewriteRule ^convert/([0-9]+)/([0-9]+)/history.html$	./modules/convert/history.php?start=$1&limit=$2	[L,NC,QSA]
    RewriteRule ^convert/css/font/(.*?).([a-z0-9]+)$		./modules/convert/css-font.php?id=$1&format=$2	[L,NC,QSA]
    RewriteRule ^convert/css/(.*?).css$				./modules/convert/css.php?id=$1			[L,NC,QSA]
    RewriteRule ^convert/index.html$				./modules/convert/index.php			[L,NC,QSA]
    RewriteRule ^convert/upload.html$				./modules/convert/upload.php			[L,NC,QSA]

# Configuring MySQL
You will need to use with either MySQL Workbench or PHPMyAdmin create a MySQL Database for the fonting repository services API. You will find in the path of /sql the sql dump files for the database for the API.

You will need to restore these with either import with MySQL Workbench or within the database on PHPMyAdmin uploading each SQL to create the tables required.

You may also depending on your memory limits edit the settings in /etc/mysql/mysql.conf.d/mysqld.cnf and then reload and restart the mysql service, this is so that mysql not only uses less CPU it also means it will be running properly with little scape for error or crashing.

## Configuring CPU throttling (CPULimit)

You now need to cpu load balance with cpulimit sometimes fontforge can really chew MIPS, run the following on the shell to edit the file that will intialise CPU Throttling on boot, as fontforge as mention can really chew and chew and chew your CPU usage:

    $ sudo nano /etc/rc.local
    
and put the following lines in it before the exit() command:

    pkill cpulimit
    /usr/bin/cpulimit -e mysql -b -q -l 67
    /usr/bin/cpulimit -e fontforge -b -q -l 36
    /usr/bin/cpulimit -e apache2 -b -q -l 35
    /usr/bin/cpulimit -e php -b -q -l 35
    /usr/bin/cpulimit -e cron -b -q -l 25
    /usr/bin/cpulimit -e wget -b -q -l 15

You may have to play around with the cpu throttling if your site is down ever to have the levels picture perfect, these are just estimates on the adverage service.


