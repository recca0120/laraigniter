<?php

use Recca0120\LaravelBridge\Laravel;

require __DIR__.'/../../vendor/autoload.php';
require __DIR__.'/database.php';

$connections = [];
foreach ($db as $key => $options) {
    $connections[$key] = [
        'driver'    => array_get($db, 'default.dbdriver'),
        'host'      => array_get($db, 'default.hostname'),
        'port'      => array_get($db, 'default.port', 3306),
        'database'  => array_get($db, 'default.database'),
        'username'  => array_get($db, 'default.username'),
        'password'  => array_get($db, 'default.password'),
        'charset'   => array_get($db, 'default.char_set'),
        'collation' => array_get($db, 'default.dbcollat'),
        'prefix'    => array_get($db, 'default.swap_pre'),
        'strict'    => array_get($db, 'default.stricton'),
        'engine'    => null,
    ];
}

Laravel::instance()
    ->setupView(__DIR__.'/../views/', __DIR__.'/../cache/compiled/')
    ->setupDatabase($connections, $active_group)
    ->setupPagination()
    ->setupTracy([
        'showBar' => true,
    ]);
