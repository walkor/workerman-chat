<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
use \Workerman\Worker;
use \GatewayWorker\BusinessWorker;
use \Workerman\Autoloader;

// bussinessWorker 进程
$worker = new BusinessWorker();
// worker名称
$worker->name = 'ChatBusinessWorker';
// bussinessWorker进程数量
$worker->count = 4;
// 服务注册地址
$worker->registerAddress = '127.0.0.1:1236';

$worker->onWorkerStart = function($worker)
{
	//加载env文件
	$dotenv = new Dotenv\Dotenv(dirname(dirname(__DIR__)));
	$dotenv->load();

	// 将db实例存储在全局变量中(也可以存储在某类的静态成员中)
	global $db;
	$db = new Workerman\MySQL\Connection(getenv('DB_HOST'), getenv('DB_PORT'), getenv("DB_USERNAME"), getenv("DB_PASSWORD"), getenv("DB_DATABASE"));
};

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}

