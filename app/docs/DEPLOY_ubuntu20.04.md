# Ubuntu 20.04 最短構築

## 初期Linux設定
```
$ sudo su
# apt -y update && apt -y upgrade && shutdown -r now

$ sudo su
# ufw allow 22
# ufw allow 80
# ufw allow 443
# ufw enable
# ufw status
# timedatectl set-timezone Asia/Tokyo
# apt -y install apache2 php-mysql php-mbstring php-gd php-curl php-intl php-zip mysql-server php7.4 unzip
# apt -y install language-pack-ja-base language-pack-ja
# locale -a
(ja_JP.UTF-8の確認)
```

## mysql
```
$ sudo mysql_secure_installation
$ sudo mysql -u root
> create user 'fc2blog'@'localhost' identified by 'topsecretpasswordforyoursafe';
> grant all on fc2blog.* to 'fc2blog'@'localhost' with grant option;
> flush privileges;
> exit
```

## deploy
```
$ cd
$ wget http://github.com/..../fc2blog-dist-XXX.zip (例
$ unzip fc2blog-dist-XXX.zip
$ sudo su
# cp -a fc2blog-dist-XXX/* /var/www
# cp fc2blog-dist-XXX/app/resource/apache/100-fc2blog.conf /etc/apache2/sites-available/
# vi /etc/apache2/sites-available/100-fc2blog.conf
# a2dissite 000-default.conf
# a2ensite 100-fc2blog.conf
# a2enmod rewrite
# systemctl restart apache2

# cp /var/www/app/config.sample.php /var/www/app/config.php
# vi  /var/www/app/config.php
```

`http://{ホスト}/install.php` を開く
