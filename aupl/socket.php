<?php
// 设置一些基本的变量
$host = '192.168.16.58';
$port = 1234;
// 设置超时时间
set_time_limit(0);
// 创建一个Socket
$socket = socket_create(AF_INET, SOCK_STREAM, 0) or die('Could not create socket\n');
//绑定Socket到端口
$result = socket_bind($socket, $host, $port) or die('Could not bind to socket\n');
// 开始监听链接
$result = socket_listen($socket, 3);
var_dump($result);