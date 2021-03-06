<?php namespace app\system\controller\part;

/**
 * 支付管理
 * Class Pay
 * @package app\system\controller\part
 */
class Pay {
	public static function make( $data ) {
		if ( $data['pay'] ) {
			return self::php( $data );
		}
	}

	protected static function php( $data ) {
		$tpl = <<<php
<?php namespace addons\\{$data['name']}\\system;
use module\HdPay;
/**
 * 微信支付通知页面
 * Class Pay
 * @package module\ucenter\system
 */
class Pay extends HdPay {
	/**
	 * 微信同步通知
	 *
	 * @param bool \$code 支付状态 true为支付成功
	 * @param array \$order 定单信息
	 */
	public function sync( \$code, \$order ) {
		if ( \$code ) {
			//此处书写模块业务处理
			echo '支付成功';
		} else {
			echo '支付失败';
		}
	}

	/**
	 * 微信异步通知
	 *
	 * @param bool \$code 支付状态 true为支付成功
	 * @param array \$order 定单信息
	 */
	public function async( \$code, \$order ) {
		if ( \$code ) {
			//此处书写模块业务处理
		}
	}
}
php;
		file_put_contents( "addons/{$data['name']}/system/Pay.php", $tpl );
	}
}