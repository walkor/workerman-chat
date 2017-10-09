<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>聊天室</title>
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">

    <script type="text/javascript" src="/js/swfobject.js"></script>
    <script type="text/javascript" src="/js/web_socket.js"></script>
    <script type="text/javascript" src="/js/jquery.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,700' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="lib/css/bootstrap.css"/>
    <link rel="stylesheet" href="lib/css/jquery.mCustomScrollbar.min.css"/>
    <link rel="stylesheet" href="dist/css/jquery.emoji.css"/>
    <link rel="stylesheet" href="lib/css/railscasts.css"/>
    <link rel="stylesheet" href="dist/css/index.css"/>
    <script src="lib/script/jquery.min.js"></script>
    <script src="lib/script/highlight.pack.js"></script>
    <script src="lib/script/jquery.mousewheel-3.0.6.min.js"></script>
    <script src="lib/script/jquery.mCustomScrollbar.min.js"></script>
    <script src="dist/js/jquery.emoji.min.js"></script>

    <script type="text/javascript">
        if (typeof console == "undefined") {
            this.console = {
                log: function (msg) {
                }
            };
        }
        // 如果浏览器不支持websocket，会使用这个flash自动模拟websocket协议，此过程对开发者透明
        WEB_SOCKET_SWF_LOCATION = "/swf/WebSocketMain.swf";
        // 开启flash的websocket debug
        WEB_SOCKET_DEBUG = true;
        var ws, name, client_list = {};

        // 连接服务端
        function connect() {
            console.log(document.domain);
            // 创建websocket
            ws = new WebSocket("ws://" + document.domain + ":7272");
            // 当socket连接打开时，输入用户名
            ws.onopen = onopen;
            // 当有消息时根据消息类型显示不同信息
            ws.onmessage = onmessage;
            ws.onclose = function () {
                console.log("连接关闭，定时重连");
                connect();
            };
            ws.onerror = function () {
                console.log("出现错误");
            };
        }

        // 连接建立时发送登录信息
        function onopen() {
            if (!name) {
                show_prompt();
            }
            // 登录
            var login_data = '{"type":"login","client_name":"' + name.replace(/"/g, '\\"') + '","room_id":"<?php echo isset($_GET['room_id']) ? $_GET['room_id'] : 1?>"}';
            console.log("websocket握手成功，发送登录数据:" + login_data);
            ws.send(login_data);
        }

        // 服务端发来消息时
        function onmessage(e) {
            console.log(e.data);
            var data = eval("(" + e.data + ")");
            switch (data['type']) {
                // 服务端ping客户端
                case 'ping':
                    ws.send('{"type":"pong"}');
                    break;
                    ;
                // 登录 更新用户列表
                case 'login':
                    //{"type":"login","client_id":xxx,"client_name":"xxx","client_list":"[...]","time":"xxx"}
                    say(data['client_id'], data['client_name'], data['client_name'] + ' 加入了聊天室', data['time']);
                    if (data['client_list']) {
                        client_list = data['client_list'];
                    }
                    else {
                        client_list[data['client_id']] = data['client_name'];
                    }
                    flush_client_list();
                    console.log(data['client_name'] + "登录成功");
                    break;
                // 发言
                case 'say':
                    //{"type":"say","from_client_id":xxx,"to_client_id":"all/client_id","content":"xxx","time":"xxx"}
                    say(data['from_client_id'], data['from_client_name'], data['content'], data['time']);
                    break;
                // 用户退出 更新用户列表
                case 'logout':
                    //{"type":"logout","client_id":xxx,"time":"xxx"}
                    say(data['from_client_id'], data['from_client_name'], data['from_client_name'] + ' 退出了', data['time']);
                    delete client_list[data['from_client_id']];
                    flush_client_list();
            }
        }

        // 输入姓名
        function show_prompt() {
            name = prompt('输入你的名字：', '');
            if (!name || name == 'null') {
                name = '游客';
            }
        }

        // 提交对话
        function onSubmit() {
            var input = document.getElementById("textarea");
            var to_client_id = $("#client_list option:selected").attr("value");
            var to_client_name = $("#client_list option:selected").text();
            ws.send('{"type":"say","to_client_id":"' + to_client_id + '","to_client_name":"' + to_client_name + '","content":"' + input.value.replace(/"/g, '\\"').replace(/\n/g, '\\n').replace(/\r/g, '\\r') + '"}');
            input.value = "";
            input.focus();
        }

        // 刷新用户列表框
        function flush_client_list() {
            var userlist_window = $("#userlist");
            var client_list_slelect = $("#client_list");
            userlist_window.empty();
            client_list_slelect.empty();
            userlist_window.append('<h4>在线用户</h4><ul>');
            client_list_slelect.append('<option value="all" id="cli_all">所有人</option>');
            for (var p in client_list) {
                userlist_window.append('<li id="' + p + '">' + client_list[p] + '</li>');
                client_list_slelect.append('<option value="' + p + '">' + client_list[p] + '</option>');
            }
            $("#client_list").val(select_client_id);
            userlist_window.append('</ul>');
        }

        //放到全局变量 避免重复定义
        var alias = {
            1: "hehe",
            2: "haha",
            3: "tushe",
            4: "a",
            5: "ku",
            6: "lu",
            7: "kaixin",
            8: "han",
            9: "lei",
            10: "heixian",
            11: "bishi",
            12: "bugaoxing",
            13: "zhenbang",
            14: "qian",
            15: "yiwen",
            16: "yinxian",
            17: "tu",
            18: "yi",
            19: "weiqu",
            20: "huaxin",
            21: "hu",
            22: "xiaonian",
            23: "neng",
            24: "taikaixin",
            25: "huaji",
            26: "mianqiang",
            27: "kuanghan",
            28: "guai",
            29: "shuijiao",
            30: "jinku",
            31: "shengqi",
            32: "jinya",
            33: "pen",
            34: "aixin",
            35: "xinsui",
            36: "meigui",
            37: "liwu",
            38: "caihong",
            39: "xxyl",
            40: "taiyang",
            41: "qianbi",
            42: "dnegpao",
            43: "chabei",
            44: "dangao",
            45: "yinyue",
            46: "haha2",
            47: "shenli",
            48: "damuzhi",
            49: "ruo",
            50: "OK"
        };
        var arr = [];
        for (var i in alias) {
            if (alias.hasOwnProperty(i)) { //filter,只输出man的私有属性
                arr[alias[i]] = i;
            }
            ;
        }


        var reg = new RegExp(/\:(\w+)\s*\:/);
        var reg2 = new RegExp(/\#qq_(\d+)\s*\#/);

        function parseContent(content) {
            if (reg.test(content)) {
                var content1 = content.replace(/\:(\w+)\s*\:/, function (wd, s1) {
                    return "<img style='width: 30px;height: 30px;' src='dist/img/tieba/" + arr[s1] + ".jpg'>";
                });


                return parseContent(content1);
            }
            else if (reg2.test(content)) {
                var content1 = content.replace(/\#qq_(\d+)\s*\#/, function (wd, s1) {
                    console.log(wd + "+" + s1);
                    return "<img style='width: 30px;height: 30px;' src='dist/img/qq/" + s1 + ".gif'>";
                });


                return parseContent(content1);
            }
            else {
                return content;
            }
        }

        // 发言
        function say(from_client_id, from_client_name, content, time) {


            //格式化
            //console.log(content);
            content = parseContent(content);

            $("#dialog").append('<div class="speech_item"><img src="http://lorempixel.com/38/38/?' + from_client_id + '" class="user_icon" /> ' + from_client_name + ' <br> ' + time + '<div style="clear:both;"></div><p class="triangle-isosceles top">' + content + '</p> </div>');
        }

        $(function () {
            select_client_id = 'all';
            $("#client_list").change(function () {
                select_client_id = $("#client_list option:selected").attr("value");
            });
        });
    </script>
</head>
<body onload="connect();">
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
                <!--                <div id="textarea" contenteditable="true"></div>-->

                <div class="col-md-6">

                </div>
                <textarea class="textarea thumbnail" id="textarea"></textarea>
                <!--                <button id="btn" class="btn btn-sm btn-default" style="width: 30px;height: 30px; background-image: url('src/img/qq/1.gif')"></button>-->
                <div class="say-btn"><input type="submit" class="btn btn-default" value="发表"/>
                    <input id="btnParse" type="button" class="btn btn-default" value="格式化"/>
                </div>
            </form>
            <div>
                &nbsp;&nbsp;&nbsp;&nbsp;<b>房间列表:</b>（当前在&nbsp;房间<?php echo isset($_GET['room_id']) && intval($_GET['room_id']) > 0 ? intval($_GET['room_id']) : 1; ?>
                ）<br>
                &nbsp;&nbsp;&nbsp;&nbsp;<a href="/?room_id=1">房间1</a>&nbsp;&nbsp;&nbsp;&nbsp;<a
                        href="/?room_id=2">房间2</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="/?room_id=3">房间3</a>&nbsp;&nbsp;&nbsp;&nbsp;<a
                        href="/?room_id=4">房间4</a>
                <br><br>
            </div>
        </div>
        <div class="col-md-3 column">
            <div class="thumbnail">
                <div class="caption" id="userlist"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $().ready(function () {

        $("#textarea").emoji({
            showTab: true,
            animation: 'fade',
            icons: [{
                name: "贴吧表情",
                path: "dist/img/tieba/",
                maxNum: 50,
                file: ".jpg",
                placeholder: ":{alias}:",
                alias: {
                    1: "hehe",
                    2: "haha",
                    3: "tushe",
                    4: "a",
                    5: "ku",
                    6: "lu",
                    7: "kaixin",
                    8: "han",
                    9: "lei",
                    10: "heixian",
                    11: "bishi",
                    12: "bugaoxing",
                    13: "zhenbang",
                    14: "qian",
                    15: "yiwen",
                    16: "yinxian",
                    17: "tu",
                    18: "yi",
                    19: "weiqu",
                    20: "huaxin",
                    21: "hu",
                    22: "xiaonian",
                    23: "neng",
                    24: "taikaixin",
                    25: "huaji",
                    26: "mianqiang",
                    27: "kuanghan",
                    28: "guai",
                    29: "shuijiao",
                    30: "jinku",
                    31: "shengqi",
                    32: "jinya",
                    33: "pen",
                    34: "aixin",
                    35: "xinsui",
                    36: "meigui",
                    37: "liwu",
                    38: "caihong",
                    39: "xxyl",
                    40: "taiyang",
                    41: "qianbi",
                    42: "dnegpao",
                    43: "chabei",
                    44: "dangao",
                    45: "yinyue",
                    46: "haha2",
                    47: "shenli",
                    48: "damuzhi",
                    49: "ruo",
                    50: "OK"
                },
                title: {
                    1: "呵呵",
                    2: "哈哈",
                    3: "吐舌",
                    4: "啊",
                    5: "酷",
                    6: "怒",
                    7: "开心",
                    8: "汗",
                    9: "泪",
                    10: "黑线",
                    11: "鄙视",
                    12: "不高兴",
                    13: "真棒",
                    14: "钱",
                    15: "疑问",
                    16: "阴脸",
                    17: "吐",
                    18: "咦",
                    19: "委屈",
                    20: "花心",
                    21: "呼~",
                    22: "笑脸",
                    23: "冷",
                    24: "太开心",
                    25: "滑稽",
                    26: "勉强",
                    27: "狂汗",
                    28: "乖",
                    29: "睡觉",
                    30: "惊哭",
                    31: "生气",
                    32: "惊讶",
                    33: "喷",
                    34: "爱心",
                    35: "心碎",
                    36: "玫瑰",
                    37: "礼物",
                    38: "彩虹",
                    39: "星星月亮",
                    40: "太阳",
                    41: "钱币",
                    42: "灯泡",
                    43: "茶杯",
                    44: "蛋糕",
                    45: "音乐",
                    46: "haha",
                    47: "胜利",
                    48: "大拇指",
                    49: "弱",
                    50: "OK"
                }
            }, {
                path: "dist/img/qq/",
                name: "qq表情",
                maxNum: 91,
                excludeNums: [41, 45, 54],
                file: ".gif",
                placeholder: "#qq_{alias}#"
            }]
        });


//        $("#textarea").emoji({
//            button: "#btn",
//            showTab: false,
//            animation: 'slide',
//            icons: [{
//                name: "QQ表情",
//                path: "dist/img/qq/",
//                maxNum: 91,
//                excludeNums: [41, 45, 54],
//                file: ".gif"
//            }]
//        });
    })

    $("#btnParse").click(function () {

        $("#sourceText").emojiParse({
            icons: [{
                path: "dist/img/tieba/",
                file: ".jpg",
                placeholder: ":{alias}:",
                alias: {
                    1: "hehe",
                    2: "haha",
                    3: "tushe",
                    4: "a",
                    5: "ku",
                    6: "lu",
                    7: "kaixin",
                    8: "han",
                    9: "lei",
                    10: "heixian",
                    11: "bishi",
                    12: "bugaoxing",
                    13: "zhenbang",
                    14: "qian",
                    15: "yiwen",
                    16: "yinxian",
                    17: "tu",
                    18: "yi",
                    19: "weiqu",
                    20: "huaxin",
                    21: "hu",
                    22: "xiaonian",
                    23: "neng",
                    24: "taikaixin",
                    25: "huaji",
                    26: "mianqiang",
                    27: "kuanghan",
                    28: "guai",
                    29: "shuijiao",
                    30: "jinku",
                    31: "shengqi",
                    32: "jinya",
                    33: "pen",
                    34: "aixin",
                    35: "xinsui",
                    36: "meigui",
                    37: "liwu",
                    38: "caihong",
                    39: "xxyl",
                    40: "taiyang",
                    41: "qianbi",
                    42: "dnegpao",
                    43: "chabei",
                    44: "dangao",
                    45: "yinyue",
                    46: "haha2",
                    47: "shenli",
                    48: "damuzhi",
                    49: "ruo",
                    50: "OK"
                }
            }, {
                path: "dist/img/qq/",
                file: ".gif",
                placeholder: "#qq_{alias}#"
            }]
        });
    });

</script>
<script type="text/javascript">var _bdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");
    document.write(unescape("%3Cscript src='" + _bdhmProtocol + "hm.baidu.com/h.js%3F7b1919221e89d2aa5711e4deb935debd' type='text/javascript'%3E%3C/script%3E"));</script>

</body>
</html>
