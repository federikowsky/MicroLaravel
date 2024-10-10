<?php

define('APP_URL', 'http://localhost:3001');
define('SENDER_EMAIL',  $_ENV['SENDER_EMAIL']);
define('SENDER_PASSWORD', str_replace('_', ' ',$_ENV['SENDER_PASSWORD']));
define('SECRET_KEY', $_ENV['SECRET_KEY']);
define('APP_KEY', $_ENV['APP_KEY']);