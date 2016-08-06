<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/

use Recca0120\LaravelBridge\Laravel;
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/database.php';
$connections = [];
foreach ($db as $key => $options) {
	$dbdriver = array_get($options, 'dbdriver');
	$dbdriver = ($dbdriver === 'mysqli') ? 'mysql' : $dbdriver;
    $connections[$key] = [
        'driver'    => $dbdriver,
        'host'      => array_get($options, 'hostname'),
        'port'      => array_get($options, 'port', 3306),
        'database'  => array_get($options, 'database'),
        'username'  => array_get($options, 'username'),
        'password'  => array_get($options, 'password'),
        'charset'   => array_get($options, 'char_set'),
        'collation' => array_get($options, 'dbcollat'),
        'prefix'    => array_get($options, 'swap_pre'),
        'strict'    => array_get($options, 'stricton'),
        'engine'    => null,
    ];
}
$viewPath = __DIR__.'/../views/';
$viewCompiledPath = __DIR__.'/../cache/compiled/';
if (is_dir($viewCompiledPath) === false) {
	mkdir($viewCompiledPath, 0777);
}
Laravel::instance()
    ->setupView($viewPath, $viewCompiledPath)
    ->setupDatabase($connections, $active_group)
    ->setupPagination()
    ->setupTracy([
        'showBar' => true,
        'strictMode' => false,
    ]);

/* End of file hooks.php */
/* Location: ./application/config/hooks.php */