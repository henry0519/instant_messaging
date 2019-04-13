<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-01
 * Time: 20:06
 */

return [
    'SERVER_NAME' => "EasySwoole",
    'MAIN_SERVER' => [
        'LISTEN_ADDRESS' => '0.0.0.0',
        'PORT' => 9506,
        'SERVER_TYPE' => EASYSWOOLE_WEB_SOCKET_SERVER, //可选为 EASYSWOOLE_SERVER  EASYSWOOLE_WEB_SERVER EASYSWOOLE_WEB_SOCKET_SERVER
        'SOCK_TYPE' => SWOOLE_TCP,
        'RUN_MODEL' => SWOOLE_PROCESS,
        'SETTING' => [
            'worker_num' => 2,
            'task_worker_num' => 2,
            'reload_async' => true,
            'task_enable_coroutine' => true
        ],
    ],
    'TEMP_DIR' => null,
    'LOG_DIR' => null,
    'CONSOLE' => [
        'ENABLE' => true,
        'LISTEN_ADDRESS' => '127.0.0.1',
        'HOST' => '127.0.0.1',
        'PORT' => 9500,
        'USER' => 'root',
        'PASSWORD' =>'123456'
    ],
    'FAST_CACHE' => [
        'PROCESS_NUM' => 0,
        'BACKLOG' => 256,
    ],
    'DISPLAY_ERROR' => true,
    'PHAR' => [
        'EXCLUDE' => ['.idea', 'Log', 'Temp', 'easyswoole', 'easyswoole.install']
    ],
    'REDIS' => [
        'host'          => '000.000.000.000',//Redis服务器IP
        'port'          => '6379',//Redis端口号
        'auth'          => 'password',//Redis密码
        'select'        => '1',
        'POOL_MAX_NUM'  => '10',
        'POOL_MIN_NUM'  => '5',
        'POOL_TIME_OUT' => '0.1',
    ],
    'MYSQL' => [
        'host'          => '000.000.000.000',//Mysql数据库IP
        'port'          => '3306',
        'user'          => 'user',//Mysql数据库用户
        'timeout'       => '5',
        'charset'       => 'utf8mb4',
        'password'      => 'password',//Mysql数据库密码
        'database'      => 'datebase',//Mysql数据库
        'POOL_MAX_NUM'  => '10',
        'POOL_TIME_OUT' => '0.1',
    ],
];
