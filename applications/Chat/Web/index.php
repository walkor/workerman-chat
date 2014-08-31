<html><head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>workerman-chat PHP聊天室 Websocket(HTLM5/Flash)+PHP多进程socket实时推送技术</title>
  <script type="text/javascript">
  //WebSocket = null;
  </script>
  <link href="/css/bootstrap.min.css" rel="stylesheet">
  <link href="/css/style.css" rel="stylesheet">
  <!-- Include these three JS files: -->
  <script type="text/javascript" src="/js/swfobject.js"></script>
  <script type="text/javascript" src="/js/web_socket.js"></script>
  <script type="text/javascript" src="/js/json.js"></script>
  <script type="text/javascript" src="/js/jquery.min.js"></script>

  <script type="text/javascript">
    if (typeof console == "undefined") {    this.console = { log: function (msg) {  } };}
    WEB_SOCKET_SWF_LOCATION = "/swf/WebSocketMain.swf";
    WEB_SOCKET_DEBUG = true;
    var ws, name, client_list={},timeid, reconnect=false;
    function init() {
       // 创建websocket
    	ws = new WebSocket("ws://"+document.domain+":7272");
      // 当socket连接打开时，输入用户名
      ws.onopen = function() {
    	  timeid && window.clearInterval(timeid);
    	  if(!name)
    	  {
  		    show_prompt();
    	  }
    	  if(!name) {
    		  return ws.close();
   		  }
    	  if(reconnect == false)
    	  {
        	  // 登录
    		  var login_data = JSON.stringify({"type":"login","client_name":name,"room_id":<?php echo isset($_GET['room_id']) ? $_GET['room_id'] : 1?>});
    		  console.log("websocket握手成功，发送登录数据:"+login_data);
  		      ws.send(login_data);
    		  reconnect = true;
    	  }
    	  else
    	  {
        	  // 断线重连
        	  var relogin_data = JSON.stringify({"type":"re_login","client_name":name,"room_id":<?php echo isset($_GET['room_id']) ? $_GET['room_id'] : 1?>});
    		  console.log("websocket握手成功，发送重连数据:"+relogin_data);
    		  ws.send(relogin_data);
    	  }
      };
      // 当有消息时根据消息类型显示不同信息
      ws.onmessage = function(e) {
    	console.log(e.data);
        var data = JSON.parse(e.data);
        switch(data['type']){
              // 服务端ping客户端
              case 'ping':
            	ws.send(JSON.stringify({"type":"pong"}));
                break;;
              // 登录 更新用户列表
              case 'login':
                  //{"type":"login","client_id":xxx,"client_name":"xxx","client_list":"[...]","time":"xxx"}
                  say(data['client_id'], data['client_name'],  data['client_name']+' 加入了聊天室', data['time']);
                  flush_client_list(data['client_list']);
                  console.log(data['client_name']+"登录成功");
                  break;
              // 断线重连，只更新用户列表
              case 're_login':
              	  //{"type":"re_login","client_id":xxx,"client_name":"xxx","client_list":"[...]","time":"xxx"}
            	  flush_client_list(data['client_list']);
            	  console.log(data['client_name']+"重连成功");
                  break;
              // 发言
              case 'say':
            	  //{"type":"say","from_client_id":xxx,"to_client_id":"all/client_id","content":"xxx","time":"xxx"}
            	  say(data['from_client_id'], data['from_client_name'], data['content'], data['time']);
            	  break;
             // 用户退出 更新用户列表
              case 'logout':
            	  //{"type":"logout","client_id":xxx,"time":"xxx"}
            	  say(data['from_client_id'], data['from_client_name'], data['from_client_name']+' 退出了', data['time']);
          		 flush_client_list(data['client_list']);
        }
      };
      ws.onclose = function() {
    	  console.log("连接关闭，定时重连");
    	  // 定时重连
    	  window.clearInterval(timeid);
    	  timeid = window.setInterval(init, 3000);
      };
      ws.onerror = function() {
    	  console.log("出现错误");
      };
    }

    // 输入姓名
    function show_prompt(){  
        name = prompt('输入你的名字：', '');
        if(!name){  
            alert('姓名输入为空，请重新输入！');  
            show_prompt();
        }
    }  

    // 提交对话
    function onSubmit() {
      var input = document.getElementById("textarea");
      var to_client_id = $("#client_list option:selected").attr("value");
      var to_client_name = $("#client_list option:selected").text();
      ws.send(JSON.stringify({"type":"say","to_client_id":to_client_id,"to_client_name":to_client_name,"content":input.value}));
      input.value = "";
      input.focus();
    }

    // 刷新用户列表框
    function flush_client_list(client_list){
    	var userlist_window = $("#userlist");
    	var client_list_slelect = $("#client_list");
    	userlist_window.empty();
    	client_list_slelect.empty();
    	userlist_window.append('<h4>在线用户</h4><ul>');
    	client_list_slelect.append('<option value="all" id="cli_all">所有人</option>');
    	for(var p in client_list){
    		userlist_window.append('<li id="'+client_list[p]['client_id']+'">'+client_list[p]['client_name']+'</li>');
    		client_list_slelect.append('<option value="'+client_list[p]['client_id']+'">'+client_list[p]['client_name']+'</option>');
        }
    	$("#client_list").val(select_client_id);
    	userlist_window.append('</ul>');
    }

    // 发言
    function say(from_client_id, from_client_name, content, time){
    	$("#dialog").append('<div class="speech_item"><img src="http://lorempixel.com/38/38/?'+from_client_id+'" class="user_icon" /> '+from_client_name+' <br> '+time+'<div style="clear:both;"></div><p class="triangle-isosceles top">'+content+'</p> </div>');
    }

    $(function(){
    	select_client_id = 'all';
	    $("#client_list").change(function(){
	         select_client_id = $("#client_list option:selected").attr("value");
	    });
    });
  </script>
</head>
<body onload="init();">
    <div class="container">
	    <div class="row clearfix">
	        <div class="col-md-1 column">
	        </div>
	        <div class="col-md-6 column">
	           <div class="thumbnail">
	               <div class="caption" id="dialog"></div>
	           </div>
	           <form onsubmit="onSubmit(); return false;">
	                <select style="margin-bottom:8px" id="client_list">
                        <option value="all">所有人</option>
                    </select>
                    <textarea class="textarea thumbnail" id="textarea"></textarea>
                    <div class="say-btn"><input type="submit" class="btn btn-default" value="发表" /></div>
               </form>
               <div>
               &nbsp;&nbsp;&nbsp;&nbsp;<b>房间列表:</b>（当前在&nbsp;房间<?php echo isset($_GET['room_id'])&&intval($_GET['room_id'])>0 ? intval($_GET['room_id']):1; ?>）<br>
               &nbsp;&nbsp;&nbsp;&nbsp;<a href="/?room_id=1">房间1</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="/?room_id=2">房间2</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="/?room_id=3">房间3</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="/?room_id=4">房间4</a>
               <br><br>
               </div>
               <p class="cp">PHP多进程+Websocket(HTML5/Flash)+PHP Socket实时推送技术&nbsp;&nbsp;&nbsp;&nbsp;Powered by <a href="http://www.workerman.net/workerman-chat" target="_blank">workerman-chat</a></p>
	        </div>
	        <div class="col-md-3 column">
	           <div class="thumbnail">
                   <div class="caption" id="userlist"></div>
               </div>
               <a href="http://workerman.net:8383" target="_blank"><img style="width:252px;margin-left:5px;" src="/img/workerman-todpole.png"></a>
	        </div>
	    </div>
    </div>
    <script type="text/javascript">var _bdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");document.write(unescape("%3Cscript src='" + _bdhmProtocol + "hm.baidu.com/h.js%3F7b1919221e89d2aa5711e4deb935debd' type='text/javascript'%3E%3C/script%3E"));</script>
</body>
</html>
