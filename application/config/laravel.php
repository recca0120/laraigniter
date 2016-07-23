<?php

use Illuminate\Container\Container;
use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Pagination\PaginationServiceProvider;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Fluent;
use Illuminate\View\ViewServiceProvider;
use Recca0120\LaravelTracy\Tracy;

$app = new Container();
$app['request'] = Request::capture();

$app['events'] = new Dispatcher();
$app['config'] = new Fluent();
$app['files'] = new Filesystem();
$app['config']['view.paths'] = [__DIR__.'/../views/'];
$app['config']['view.compiled'] = __DIR__.'/../cache/compiled/';

$viewServiceProvider = new ViewServiceProvider($app);
$viewServiceProvider->register();
$viewServiceProvider->boot();
Facade::setFacadeApplication($app);
class_alias(View::class, 'View');

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

$app['config']['database.fetch'] = PDO::FETCH_CLASS;
$app['config']['database.default'] = $active_group;
$app['config']['database.connections'] = $connections;

$databaseServiceProvider = new DatabaseServiceProvider($app);
$databaseServiceProvider->register();
$databaseServiceProvider->boot();

$tracy = Tracy::instance();
$databasePanel = $tracy->getPanel('database');
$app['events']->listen(QueryExecuted::class, function ($event) use ($databasePanel) {
    $sql = $event->sql;
    $bindings = $event->bindings;
    $time = $event->time;
    $name = $event->connectionName;
    $pdo = $event->connection->getPdo();

    $databasePanel->logQuery($sql, $bindings, $time, $name, $pdo);
});

$paginationServiceProvider = new PaginationServiceProvider($app);
$paginationServiceProvider->register();
$paginationServiceProvider->boot();
