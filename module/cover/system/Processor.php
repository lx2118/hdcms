<?php namespace module\cover\system;
use module\HdProcessor;


/**
 * 封面消息处理
 * Class processor
 * @package module\basic
 */
class processor extends HdProcessor {
	//规则编号
	public function handle( $rid ) {
		$res = Db::table( 'reply_cover' )->where( 'rid', $rid )->where( 'siteid', SITEID )->first();
		if ( $res ) {
			$data[] = [
				'title'       => $res['title'],
				'discription' => $res['description'],
				'picurl'      => __ROOT__ . '/' . $res['thumb'],
				'url'         => preg_match( '/^http/i', $res['url'] ) ? $res['url'] : __ROOT__ . '/' . $res['url'],
			];
			$this->news( $data );
		}
	}
}
