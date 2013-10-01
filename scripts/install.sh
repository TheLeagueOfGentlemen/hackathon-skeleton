if [ ! -f app/config/params.php ]; then
    echo "Please copy app/config/params.php.dist to app/config/params.php and update the settings before running this script."
    echo "cp app/config/params.php.dist app/config/params.php && vi app/config/params.php"
    exit
fi
composer install
mkdir -p cache/twig && mkdir cache/profiler
chmod -R 777 cache
touch logs/silex_dev.log && chmod 777 $_
php scripts/create_schema.php
php scripts/dummy_data.php
