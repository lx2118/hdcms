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

/**
 * 回复关键字
 * Class RuleKeyword
 * @package system\model
 * @author 向军 <2300071698@qq.com>
 * @site www.houdunwang.com
 */
class RuleKeyword extends Common {
	protected $table = 'rule_keyword';
	protected $denyInsertFields = [ 'id' ];
	protected $validate = [
		[ 'rid', 'required', '规则编号不能为空', self::MUST_VALIDATE, self::MODEL_BOTH ],
		[ 'content', 'required', '关键词内容不能为空', self::MUST_VALIDATE, self::MODEL_BOTH ],
		[ 'content', 'unique', '关键词已经被使用', self::MUST_VALIDATE, self::MODEL_BOTH ],
		[ 'type', 'regexp:/^[1-4]$/', '关键词类型只能为1~4的数字', self::EXIST_VALIDATE, self::MODEL_BOTH ],
		[ 'rank', 'regexp:/^[0-255]$/', '排序只能为0~255的数字', self::EXIST_VALIDATE, self::MODEL_BOTH ],
		[ 'status', 'regexp:/^[0-1]$/', '状态只能为1或0', self::EXIST_VALIDATE, self::MODEL_BOTH ],
	];
	protected $auto = [
		[ 'siteid', 'siteid', 'function', self::MUST_AUTO, self::MODEL_BOTH ],
		[ 'rank', 0, 'string', self::NOT_EXIST_AUTO, self::MODEL_INSERT ],
		[ 'type', 1, 'string', self::NOT_EXIST_AUTO, self::MODEL_INSERT ],
		[ 'status', 1, 'string', self::NOT_EXIST_AUTO, self::MODEL_INSERT ],
	];
}