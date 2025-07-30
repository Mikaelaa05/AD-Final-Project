<?php
define('BASE_PATH', realpath(__DIR__));
define('UTILS_PATH', realpath(BASE_PATH . '/utils'));
define('DUMMIES_PATH', realpath(BASE_PATH . '/staticData/dummies'));
define('COMPONENTS_PATH', realpath(BASE_PATH . '/components'));
define('PAGES_PATH', realpath(BASE_PATH . '/pages'));
define('LAYOUTS_PATH', realpath(BASE_PATH . '/layouts'));
define('HANDLERS_PATH', realpath(BASE_PATH . '/handlers'));
define('ERRORS_PATH', realpath(BASE_PATH . '/errors'));
define('ASSETS_PATH', realpath(BASE_PATH . '/assets'));
define('DATABASE_PATH', realpath(BASE_PATH . '/database'));
define('STATIC_DATA_PATH', realpath(BASE_PATH . '/staticData'));
chdir(BASE_PATH) ;

