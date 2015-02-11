<?php 
use \Workerman\WebServer;
use \GatewayWorker\Gateway;
use \GatewayWorker\BusinessWorker;

// gateway
$gateway = new Gateway("Websocket://0.0.0.0:7272");

$gateway->name = 'ChatGateway';

$gateway->count = 4;

$gateway->lanIp = '127.0.0.1';

$gateway->startPort = 3000;

$gateway->pingInterval = 10;

$gateway->pingData = '{"type":"ping"}';


// bussinessWorker
$worker = new BusinessWorker();

$worker->name = 'ChatBusinessWorker';

$worker->count = 4;


// WebServer
$web = new WebServer("http://0.0.0.0:55151");

$web->count = 2;

$web->addRoot('www.workerman.net', __DIR__.'/Web');
