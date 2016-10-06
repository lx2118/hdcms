<?php
/** .-------------------------------------------------------------------
 * |  Software: [HDCMS framework]
 * |      Site: www.hdcms.com
 * |-------------------------------------------------------------------
 * |    Author: 向军 <2300071698@qq.com>
 * |    WeChat: aihoudun
 * | Copyright (c) 2012-2019, www.houdunwang.com. All Rights Reserved.
 * '-------------------------------------------------------------------*/
namespace system\model;

use hdphp\model\Model;

/**
 * 卡券使用会员组
 * Class TicketGroups
 * @package system\model
 * @author 向军
 */
class TicketGroups extends Model {
	protected $table = 'ticket_groups';
	protected $auto
	                 = [
			[ 'siteid', 'autoSiteid', 'method', self::MUST_AUTO, self::MODEL_BOTH ],
		];

	protected function autoSiteid() {
		return v( 'site.siteid' );
	}
}