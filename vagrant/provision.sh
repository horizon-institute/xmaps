export DEBIAN_FRONTEND=noninteractive
apt-add-repository multiverse
apt-get update
debconf-set-selections <<< 'mysql-server mysql-server/root_password password password'
debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password password'
apt-get install -y mysql-server 
#sed -i "s/^bind-address/#bind-address/" /etc/mysql/my.cnf
mysql -u root -proot -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY 'root' WITH GRANT OPTION; FLUSH PRIVILEGES;"
service mysql restart
apt-get install -y apache2 php5-cli libapache2-mod-fastcgi php5-fpm php5-mysql php-pear git
a2enmod actions fastcgi rewrite alias
cp /vagrant/vagrant/php5-fpm.conf /etc/apache2/conf-available
cp /vagrant/vagrant/000-default.conf /etc/apache2/sites-available
a2enconf php5-fpm
service apache2 reload
if [ ! -e "/usr/local/bin/phpunit" ]; then
  curl -sSL -O https://phar.phpunit.de/phpunit.phar
  chmod a+x phpunit.phar
  mv phpunit.phar /usr/local/bin/phpunit
fi
pear install PHP_CodeSniffer
if [ ! -e "/usr/share/wpcs" ]; then
  git clone -b master \
      https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards.git \
      /usr/share/wpcs
  phpcs --config-set installed_paths /usr/share/wpcs
fi   
if [ ! -e "/usr/local/bin/wp" ]; then
  curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
  chmod +x wp-cli.phar
  mv wp-cli.phar /usr/local/bin/wp
fi
if [ ! -e "/var/www/html/wp-config.php" ]; then
  mkdir -p /var/www/html
  cp /vagrant/vagrant/.htaccess /var/www/html
  rm -f /var/www/html/index.html
  curl -o wordpress.tar.gz -sSL https://wordpress.org/wordpress-4.3.tar.gz
  tar --strip-components=1 -C /var/www/html/ -xzf wordpress.tar.gz wordpress/
  rm wordpress.tar.gz
  WP_CLI="/usr/local/bin/wp --allow-root --path=/var/www/html"
  $WP_CLI core config \
    --dbname=wordpress \
    --dbuser=root \
    --dbpass=password \
    --dbhost=localhost \
    --dbprefix=wp_ \
    --skip-check
  $WP_CLI db create	
  $WP_CLI core install \
    --url="$1" \
    --title="xMaps" \
    --admin_user="admin" \
    --admin_password="password" \
    --admin_email="nouser@example.com"
  chown -R www-data:www-data /var/www/html
  ln -s /vagrant/src /var/www/html/wp-content/plugins/xmaps
  $WP_CLI plugin activate xmaps
  $WP_CLI plugin install \
      https://downloads.wordpress.org/plugin/disable-wordpress-updates.zip --activate
  $WP_CLI plugin delete $($WP_CLI plugin list --status=inactive --field=name)
fi
