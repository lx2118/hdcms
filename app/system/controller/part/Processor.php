<?php namespace app\system\controller\part;

/**
 * 模块的微信消息代码片段
 * Class Processor
 * @package app\system\controller\part
 * @author 向军 <2300071698@qq.com>
 * @site www.houdunwang.com
 */
class Processor {
	public static function make( $data ) {
		foreach ( (array) $data['processors'] as $s ) {
			//定阅了任何一个类型的消息时都创建脚本处理程序
			if ( ! empty( $s ) ) {
				return self::php( $data );
			}
		}
	}

	protected static function php( $data ) {
		$tpl = <<<php
<?php namespace addons\\{$data['name']};

/**
 * 测试模块消息处理器
 *
 * @author {$data['author']}
 * @url http://open.hdcms.com
 */
use module\hdProcessor;
class Processor extends hdProcessor{
	/**
	 * 微信消息处理
	 * \$rid 为包含消息内容处理规则的编号
	 * 根据\$rid 从当前模块的数据表中获取回复内容,然后进行回复处理
	 * 如果没有回复系统将回复默认消息内容
	 * 微信消息类型很多, 系统已经内置了"后盾网微信接口SDK"
	 * 要更全面的使用本功能请查看 SDK文档
	 * @param int \$rid
	 */
	public function handle(\$rid){
		p(\$this->message());
	}
}
php;
		file_put_contents( "addons/{$data['name']}/system/Processor.php", $tpl );
	}
}