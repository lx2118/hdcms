<?php
/** .-------------------------------------------------------------------
 * |  Software: [HDCMS framework]
 * |      Site: www.hdcms.com
 * |-------------------------------------------------------------------
 * |    Author: 向军 <2300071698@qq.com>
 * |    WeChat: aihoudun
 * | Copyright (c) 2012-2019, www.houdunwang.com. All Rights Reserved.
 * '-------------------------------------------------------------------*/
namespace module\ucenter\controller;

use system\model\CreditsRecord;
use system\model\Member;

/**
 * 会员积分余额管理
 * Class Credit
 * @package module\ucenter\controller
 */
class Credit extends Auth {
	//积分/余额列表
	public function lists() {
		if ( $timerange = q( 'get.timerange' ) ) {
			//有筛选时间的
			$timerange = explode( '至', $timerange );
			$data      = CreditsRecord::where( 'uid', v( 'member.info.uid' ) )
			                          ->where( 'credittype', q( 'get.type' ) )
			                          ->where( 'createtime', '>=', strtotime( $timerange[0] ) )
			                          ->where( 'createtime', '<=', strtotime( $timerange[1] ) )
			                          ->paginate( 10 );
		} else {
			$data = CreditsRecord::where( 'uid', v( 'member.info.uid' ) )->where( 'credittype', q( 'get.type' ) )->paginate( 8 );
		}
		//收入
		$income = CreditsRecord::where( 'num', '>', 0 )->where( 'uid', v( 'member.info.uid' ) )->where( 'credittype', q( 'get.type' ) )->sum( 'num' );
		//支出
		$expend = CreditsRecord::where( 'num', '<', 0 )->where( 'uid', v( 'member.info.uid' ) )->where( 'credittype', q( 'get.type' ) )->sum( 'num' );
		View::with( [ 'income' => $income, 'expend' => $expend ] );
		View::with( 'data', $data );

		return View::make( $this->template . '/credit_lists.html' );
	}
}