<?php
/**
 * @author: chenjia404
 * @Date  : 2017-11-04
 * @Time  : 16:48
 */

namespace App\Service;


class ClientName
{
	protected $client_name_list_file = 'client_name_list.json';

	private $client_name_list;

	/**
	 * 获取项目根目录
	 * @return string
	 */
	public function getRootDir()
	{
		return dirname(dirname(dirname(__DIR__)));
	}

	/**
	 * 获取记录文件位置
	 * @return string
	 */
	public function getFilename()
	{
		return $this->getRootDir() . DIRECTORY_SEPARATOR . $this->client_name_list_file;
	}


	public function __construct()
	{
		//昵称数据保存在一个json文件里面
		if(!file_exists($this->getFilename()))
		{
			file_put_contents($this->getFilename(),json_encode([]));
		}

		$this->client_name_list = json_decode(file_get_contents($this->getFilename()),true);
	}

	/**
	 * 检查昵称是否重复
	 * @param int    $room_id 房间id
	 * @param string $name    客户端昵称
	 * @return bool
	 */
	public function exists(int $room_id,$name):bool
	{
		return isset($this->client_name_list[ $room_id ][ $name ]);
	}

	/**
	 * 记录房间昵称
	 */
	public function add(int $room_id,$name)
	{
		$this->client_name_list[ $room_id ][ $name ] = true;
		file_put_contents($this->getFilename(),json_encode($this->client_name_list));
	}


	/**
	 * 移除昵称记录
	 * @param int $room_id
	 * @param     $name
	 */
	public function remove(int $room_id,$name)
	{
		unset($this->client_name_list[ $room_id ][ $name ]);
		file_put_contents($this->getFilename(),json_encode($this->client_name_list));
	}

	/**
	 * 清空昵称记录，最好的开启聊天室的时候操作
	 */
	public function removeAll()
	{
		file_put_contents($this->getFilename(),json_encode([]));
	}
}