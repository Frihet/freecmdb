apt-get install graphviz apache2 php5-pgsql libapache2-mod-php5 postgresql php-pear
pear install Image_GraphViz

cd /var/www
git clone git://github.com/Frihet/freecmdb.git
cd freecmdb
git clone git://github.com/Frihet/fc-framework.git common
chown -R www-data /var/www/freecmdb

su - postgresql
createdb freemcdb
createuser freecmdb
psql
>> grant all on database freecmdb to freecmdb;
>> alter role freecmdb with password 'saltgurka';
Open the directory where you cloned freecmdb in a webbrowser
