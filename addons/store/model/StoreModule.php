<?php namespace addons\store\model;

use houdunwang\model\Model;

/**
 * 模块
 * Class StoreModule
 * @package addons\store\model
 */
class StoreModule extends Model {
	protected $table = 'store_module';
	protected $allowFill = [ '*' ];
	protected $validate = [
		[ 'title', 'required', '标题不能为空', self::EMPTY_VALIDATE, self::MODEL_BOTH ],
		[ 'name', 'required', '标识不能为空', self::EMPTY_VALIDATE, self::MODEL_BOTH ],
		[ 'name', 'unique', '应用已经存在了', self::MUST_VALIDATE, self::MODEL_BOTH ],
		[ 'thumb', 'required', '缩略图不能为空', self::EMPTY_VALIDATE, self::MODEL_INSERT ],
	];
	protected $auto = [
		[ 'uid', 'getUid', 'method', self::EMPTY_AUTO, self::MODEL_INSERT ],
		[ 'price', 0, 'string', self::EMPTY_AUTO, self::MODEL_INSERT ],
		[ 'racking', 1, 'string', self::EMPTY_AUTO, self::MODEL_INSERT ],
		[ 'package', 'json_encode', 'function', self::EMPTY_AUTO, self::MODEL_INSERT ],
		[ 'createtime', 'time', 'function', self::EMPTY_AUTO, self::MODEL_INSERT ],
	];

	protected function getUid() {
		return v( 'member.info.uid' );
	}
}