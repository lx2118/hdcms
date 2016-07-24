<?php namespace hdphp\cloud;
/**
 * 云接口
 * Class Cloud
 * @package hdphp\cloud
 * @author 向军
 */
class Cloud {
	private $app;
	const URL = 'http://dev.hdcms.com/index.php';

	public function __construct( $app ) {
		$this->app = $app;
	}


	/**
	 * 发送短信
	 *
	 * @param $mobile 手机号
	 * @param $content 内容
	 *
	 * @return bool 返回true发送成功,否则返回错误消息内容
	 */
	public function mobile( $mobile, $content ) {
		$data = [ 'mobile' => $mobile, 'content' => $content ];
		$data = http_build_query( $data );
		$res  = Curl::post( self::URL . '?a=cloud/mobile_message&t=web&siteid=1&m=store', $data );

		return Xml::toSimpleArray( $res );
	}
}