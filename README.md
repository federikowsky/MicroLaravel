# myphplib

docker-compose up -d
docker exec -it mysql-container mysql -u root -p

docker inspect mysql-container 

docker logs mysql-container   

## install composer dependencies
### App
composer require vlucas/phpdotenv
composer require league/oauth2-google
composer require phpmailer/phpmailer
### Dev 
composer require --dev phpunit/phpunit
composer require symfony/http-foundation

## Test
vendor/bin/phpunit --bootstrap vendor/autoload.php tests/MyClassTest.php
vendor/bin/phpunit --coverage-html coverage