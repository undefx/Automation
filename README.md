Automation
==========

A simple, robust solution for automating tasks.


Basic Setup
==========

(Originally written on CentOS 6.2, also tested on Fedora 20.)

Stuff to install:
```
sudo yum install mysql-server mysql httpd php php-common php-mysql
```

Looks like mysql has been replaced by mariadb in fedora 20. You can still connect to it by using the "mysql" command though. Here's how to start it:
```
sudo service mariadb start
```

MySQL security: only allow connections from localhost.
```
netstat -anl | grep "3306"

tcp        0      0 0.0.0.0:3306            0.0.0.0:*               LISTEN
```
It's bound to 0.0.0.0 - need to change that to 127.0.0.1

```
sudo nano /etc/my.cnf
```

Add this line after [mysql]:
```
bind-address = 127.0.0.1
```

```
sudo service mariadb restart
netstat -anl | grep "3306"

tcp        0      0 127.0.0.1:3306          0.0.0.0:*               LISTEN
```

Excellent. Now to change (set) the root password. Also, need to delete anonymous accounts.
```
mysql -u root

SELECT User, Host, Password FROM mysql.user;
DROP USER ''@'localhost.localdomain';
DROP USER ''@'localhost';
UPDATE mysql.user SET Password = PASSWORD('PASSWORD') WHERE User = 'root';
FLUSH PRIVILEGES;
```

Now to secure httpd:
```
sudo service httpd start
sudo nano /etc/httpd/conf/httpd.conf
```

change "Listen 80" to "Listen 127.0.0.1:80"

```
sudo service httpd restart
```

Sanity check:
```
netstat -anl | grep ":80" | grep "LISTEN"

tcp        0      0 127.0.0.1:80            0.0.0.0:*               LISTEN
```

Now everything is ready to go. (If virtual hosting or something else fancy, there would be more stuff to setup in apache.)

Get the code:
```
git clone https://github.com/undefx/Automation.git
```

Make a database and a user for it:
```
mysql -u root -p

CREATE DATABASE automation;
CREATE USER 'auto'@'localhost' IDENTIFIED BY 'autopass';
GRANT ALL ON automation.* TO 'auto'@'localhost';
FLUSH PRIVILEGES;
```

Setup the database:
```
mysql -u auto -p automation < Automation/database/setup.sql
```

Install the web interface (this would be different if using virtual hosts):
```
sudo cp -rv Automation/interface/* /var/www/html/
sudo chown -R apache.apache /var/www/html/
sudo chmod -R 754 /var/www/html/
```

Install the automation script somewhere (I'm making a subdir in my home directory for it):
```ch
mkdir ~/Automation
cp Automation/driver/automation.pl ~/Automation/
```

Fill in database connection info and credentials:
```
sudo nano /var/www/html/settings.php
nano ~/Automation/automation.pl
```

Then just run automation (in screen!):
```
screen -S auto
cd ~/Automation/
perl automation.pl
```
