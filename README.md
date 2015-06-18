workerman-chat
=======
基于workerman的GatewayWorker框架开发的一款高性能支持分布式部署的聊天室系统。

GatewayWorker框架文档：http://gatewayworker-doc.workerman.net/

 特性
======
 * 使用websocket协议
 * 多浏览器支持（浏览器支持html5或者flash任意一种即可）
 * 多房间支持
 * 私聊支持
 * 掉线自动重连
 * 支持多服务器部署
 * 业务逻辑全部在一个文件中，快速入门可以参考这个文件[Applications/Chat/Event.php](https://github.com/walkor/workerman-chat/blob/master/Applications/Chat/Event.php)   
  
启动停止(Linux系统)
=====
以debug方式启动  
```php start.php start  ```

以daemon方式启动  
```php start.php start -d ```

启动(windows系统)
======
（windows系统仅作为开发测试环境）  
首先windows系统需要先下载windows版本workerman，替换Workerman目录。

步骤：  
1、下载代码到本地,从源码中找到Workerman目录并删除   
2、下载或者windows版本workerman，zip地址 https://github.com/walkor/workerman-for-win/archive/master.zip  
3、解压到原Worekrman目录所在位置，同时目录workerman-for-win-master重命名为Workerman(注意第一个字母W为大写)  
4、双击start_for_win.bat启动（系统已经装好php，并设置好环境变量，要求版本php>=5.3.3）  

注意：  
windows系统下无法使用 stop reload status 等命令  
如果无法打开页面请尝试关闭服务器防火墙  

测试
=======
浏览器访问 http://服务器ip或域:55151,例如http://127.0.0.1:55151

 [更多请访问www.workerman.net](http://www.workerman.net/workerman-chat)
