# Drops all tables in the database.
basedir=$(dirname $0);
mysql -u baryllium -pbaryllium < "$basedir"/060-droptables.sql;
