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

/**
 * 聊天主逻辑
 * 主要是处理 onMessage onClose 
 */
use \GatewayWorker\Lib\Gateway;
use \GatewayWorker\Lib\Store;

class Event
{
   
   /**
    * 有消息时
    * @param int $client_id
    * @param string $message
    */
   public static function onMessage($client_id, $message)
   {
        // debug
        echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id session:".json_encode($_SESSION)." onMessage:".$message."\n";
        
        // 客户端传递的是json数据
        $message_data = json_decode($message, true);
        if(!$message_data)
        {
            return ;
        }
        
        // 根据类型执行不同的业务
        switch($message_data['type'])
        {
            // 客户端回应服务端的心跳
            case 'pong':
                return;
            // 客户端登录 message格式: {type:login, name:xx, room_id:1} ，添加到客户端，广播给所有客户端xx进入聊天室
            case 'login':
            case 're_login':
                // 判断是否有房间号
                if(!isset($message_data['room_id']))
                {
                    throw new \Exception("\$message_data['room_id'] not set. client_ip:{$_SERVER['REMOTE_ADDR']} \$message:$message");
                }
                
                // 把房间号昵称放到session中
                $room_id = $message_data['room_id'];
                $client_name = htmlspecialchars($message_data['client_name']);
                $_SESSION['room_id'] = $room_id;
                $_SESSION['client_name'] = $client_name;
                
                // 存储到当前房间的客户端列表
                $all_clients = self::addClientToRoom($room_id, $client_id, $client_name);
                
                // 整理客户端列表以便显示
                $client_list = self::formatClientsData($all_clients);
                
                // 转播给当前房间的所有客户端，xx进入聊天室 message {type:login, client_id:xx, name:xx} 
                $new_message = array('type'=>$message_data['type'], 'client_id'=>$client_id, 'client_name'=>htmlspecialchars($client_name), 'client_list'=>$client_list, 'time'=>date('Y-m-d H:i:s'));
                $client_id_array = array_keys($all_clients);
                Gateway::sendToAll(json_encode($new_message), $client_id_array);
                return;
                
            // 客户端发言 message: {type:say, to_client_id:xx, content:xx}
            case 'say':
                // 非法请求
                if(!isset($_SESSION['room_id']))
                {
                    throw new \Exception("\$_SESSION['room_id'] not set. client_ip:{$_SERVER['REMOTE_ADDR']}");
                }
                $room_id = $_SESSION['room_id'];
                $client_name = $_SESSION['client_name'];
                
                // 私聊
                if($message_data['to_client_id'] != 'all')
                {
                    $new_message = array(
                        'type'=>'say',
                        'from_client_id'=>$client_id, 
                        'from_client_name' =>$client_name,
                        'to_client_id'=>$message_data['to_client_id'],
                        'content'=>"<b>对你说: </b>".nl2br(htmlspecialchars($message_data['content'])),
                        'time'=>date('Y-m-d H:i:s'),
                    );
                    Gateway::sendToClient($message_data['to_client_id'], json_encode($new_message));
                    $new_message['content'] = "<b>你对".htmlspecialchars($message_data['to_client_name'])."说: </b>".nl2br(htmlspecialchars($message_data['content']));
                    return Gateway::sendToCurrentClient(json_encode($new_message));
                }
                
                // 向大家说
                $all_clients = self::getClientListFromRoom($_SESSION['room_id']);
                $client_id_array = array_keys($all_clients);
                $new_message = array(
                    'type'=>'say', 
                    'from_client_id'=>$client_id,
                    'from_client_name' =>$client_name,
                    'to_client_id'=>'all',
                    'content'=>nl2br(htmlspecialchars($message_data['content'])),
                    'time'=>date('Y-m-d H:i:s'),
                );
                return Gateway::sendToAll(json_encode($new_message), $client_id_array);
        }
   }
   
   /**
    * 当客户端断开连接时
    * @param integer $client_id 客户端id
    */
   public static function onClose($client_id)
   {
       // debug
       echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id onClose:''\n";
       
       // 从房间的客户端列表中删除
       if(isset($_SESSION['room_id']))
       {
           $room_id = $_SESSION['room_id'];
           self::delClientFromRoom($room_id, $client_id);
           // 广播 xxx 退出了
           if($all_clients = self::getClientListFromRoom($room_id))
           {
               $client_list = self::formatClientsData($all_clients);
               $new_message = array('type'=>'logout', 'from_client_id'=>$client_id, 'from_client_name'=>$_SESSION['client_name'], 'client_list'=>$client_list, 'time'=>date('Y-m-d H:i:s'));
               $client_id_array = array_keys($all_clients);
               Gateway::sendToAll(json_encode($new_message), $client_id_array);
           }
       }
   }
   
  
   /**
    * 格式化客户端列表数据
    * @param array $all_clients
    */
   public static function formatClientsData($all_clients)
   {
       $client_list = array();
       if($all_clients)
       {
           foreach($all_clients as $tmp_client_id=>$tmp_name)
           {
               $client_list[] = array('client_id'=>$tmp_client_id, 'client_name'=>$tmp_name);
           }
       }
       return $client_list;
   }
   
   /**
    * 获得客户端列表
    * @todo 保存有限个
    */
   public static function getClientListFromRoom($room_id)
   {
       $key = "ROOM_CLIENT_LIST-$room_id";
       $store = Store::instance('room');
       $ret = $store->get($key);
       if(false === $ret)
       {
           if(get_class($store) == 'Memcached')
           {
               if($store->getResultCode() == \Memcached::RES_NOTFOUND)
               {
                   return array();
               }
               else 
               {
                   throw new \Exception("getClientListFromRoom($room_id)->Store::instance('room')->get($key) fail " . $store->getResultMessage());
               }
           }
           return array();
       }
       return $ret;
   }
   
   /**
    * 从客户端列表中删除一个客户端
    * @param int $client_id
    */
   public static function delClientFromRoom($room_id, $client_id)
   {
       $key = "ROOM_CLIENT_LIST-$room_id";
       $store = Store::instance('room');
       // 存储驱动是memcached
       if(get_class($store) == 'Memcached')
       {
           $cas = 0;
           $try_count = 3;
           while($try_count--)
           {
               $client_list = $store->get($key, null, $cas);
               if(false === $client_list)
               {
                   if($store->getResultCode() == \Memcached::RES_NOTFOUND)
                   {
                       return array();
                   }
                   else
                   {
                        throw new \Exception("Memcached->get($key) return false and memcache errcode:" .$store->getResultCode(). " errmsg:" . $store->getResultMessage());
                   }
               }
               if(isset($client_list[$client_id]))
               {
                   unset($client_list[$client_id]);
                   if($store->cas($cas, $key, $client_list))
                   {
                       return $client_list;
                   }
               }
               else 
               {
                   return true;
               }
           }
           throw new \Exception("delClientFromRoom($room_id, $client_id)->Store::instance('room')->cas($cas, $key, \$client_list) fail" . $store->getResultMessage());
       }
       // 存储驱动是memcache或者file
       else
       {
           $handler = fopen(__FILE__, 'r');
           flock($handler,  LOCK_EX);
           $client_list = $store->get($key);
           if(isset($client_list[$client_id]))
           {
               unset($client_list[$client_id]);
               $ret = $store->set($key, $client_list);
               flock($handler, LOCK_UN);
               return $client_list;
           }
           flock($handler, LOCK_UN);
       }
       return $client_list;
   }
   
   /**
    * 添加到客户端列表中
    * @param int $client_id
    * @param string $client_name
    */
   public static function addClientToRoom($room_id, $client_id, $client_name)
   {
       $key = "ROOM_CLIENT_LIST-$room_id";
       $store = Store::instance('room');
       // 获取所有所有房间的实际在线客户端列表，以便将存储中不在线用户删除
       $all_online_client_id = Gateway::getOnlineStatus();
       // 存储驱动是memcached
       if(get_class($store) == 'Memcached')
       {
           $cas = 0;
           $try_count = 3;
           while($try_count--)
           {
               $client_list = $store->get($key, null, $cas);
               if(false === $client_list)
               {
                   if($store->getResultCode() == \Memcached::RES_NOTFOUND)
                   {
                       $client_list = array();
                   }
                   else
                   {
                       throw new \Exception("Memcached->get($key) return false and memcache errcode:" .$store->getResultCode(). " errmsg:" . $store->getResultMessage());
                   }
               }
               if(!isset($client_list[$client_id]))
               {
                   // 将存储中不在线用户删除
                   if($all_online_client_id && $client_list)
                   {
                       $all_online_client_id = array_flip($all_online_client_id);
                       $client_list = array_intersect_key($client_list, $all_online_client_id);
                   }
                   // 添加在线客户端
                   $client_list[$client_id] = $client_name;
                   // 原子添加
                   if($store->getResultCode() == \Memcached::RES_NOTFOUND)
                   {
                       $store->add($key, $client_list);
                   }
                   // 置换
                   else
                   {
                       $store->cas($cas, $key, $client_list);
                   }
                   if($store->getResultCode() == \Memcached::RES_SUCCESS)
                   {
                       return $client_list;
                   }
               }
               else 
               {
                   return $client_list;
               }
           }
           throw new \Exception("addClientToRoom($room_id, $client_id, $client_name)->cas($cas, $key, \$client_list) fail .".$store->getResultMessage());
       }
       // 存储驱动是memcache或者file
       else
       {
           $handler = fopen(__FILE__, 'r');
           flock($handler,  LOCK_EX);
           $client_list = $store->get($key);
           if(!isset($client_list[$client_id]))
           {
               // 将存储中不在线用户删除
               if($all_online_client_id && $client_list)
               {
                   $all_online_client_id = array_flip($all_online_client_id);
                   $client_list = array_intersect_key($client_list, $all_online_client_id);
               }
               // 添加在线客户端
               $client_list[$client_id] = $client_name;
               $ret = $store->set($key, $client_list);
               flock($handler, LOCK_UN);
               return $client_list;
           }
           flock($handler, LOCK_UN);
       }
       return $client_list;
   }
}
