<?php
/**
 * @author: chenjia404
 * @Date  : 2017-11-04
 * @Time  : 21:46
 */

namespace App\Service;


class ChatLog
{
	private $log_type = 'file';

	public function __construct($log_type)
	{
		$this->log_type = $log_type;
	}

	public function add($ip,$name,$content)
	{
		switch($this->log_type)
		{
			case "mysql":
				$this->addToMysql($ip,$name,$content);
				break;
			case "file":
				$this->addToFile($ip,$name,$content);
				break;
		}
	}

	protected function addToFile($ip,$name,$content)
	{
		//记录聊天日志文件
		$log = ['ip'=>$ip,
		        'name'=>$name,
		        'content'=>$content,
		        'time'=>date('Y-m-d H:i:s')
		];

		$log_dir = getenv("CHAT_LOG_DIR");
		if(!file_exists($log_dir))
		{
			if(mkdir($log_dir,777))
				echo "成功创建聊天记录保存目录{$log_dir}\n";
			else
				echo "聊天记录保存目录{$log_dir} 创建失败，请手动创建\n";
		}

		$log_file = $log_dir . "chat" . date('Y-m-d') . ".log";
		file_put_contents($log_file,json_encode($log,JSON_UNESCAPED_UNICODE) . "\n",FILE_APPEND);
	}

	protected function addToMysql($ip,$name,$content)
	{
		global $db;
		// 插入
		$db->insert('chat_logs')->cols([
			'ip'      => $ip,
			'name'=>$name,
			'content'=>$content,
			'time'=>date('Y-m-d H:i:s')
		])->query();
	}
}