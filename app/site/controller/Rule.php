<?php namespace app\site\controller;

/** .-------------------------------------------------------------------
 * |  Software: [HDCMS framework]
 * |      Site: www.hdcms.com
 * |-------------------------------------------------------------------
 * |    Author: 向军 <2300071698@qq.com>
 * |    WeChat: aihoudun
 * | Copyright (c) 2012-2019, www.houdunwang.com. All Rights Reserved.
 * '-------------------------------------------------------------------*/
use system\model\Rule as Model;

/**
 * 模块回复规则管理
 * Class Rule
 * @package app\site\controller
 */
class Rule {
	/**
	 * 显示规则回复列表
	 * @return mixed
	 */
	public function lists() {
		auth( 'system_rule' );
		$db = Db::table( 'rule' )->where( 'siteid', SITEID )->where( 'module', v( 'module.name' ) );
		if ( $status = Request::get( 'status' ) ) {
			$db->where( 'status', $status == 'close' ? 0 : 1 );
		}
		$rules = $db->get() ?: [ ];
		$data  = [ ];
		//回复关键词
		foreach ( $rules as $k => $v ) {
			$v['keywords'] = Db::table( 'rule_keyword' )->where( 'rid', $v['rid'] )->lists( 'content' ) ?: [ ];
			//按关键词搜索
			if ( $con = Request::post( 'content' ) ) {
				if ( ! in_array( $con, $v['keywords'] ) ) {
					continue;
				}
			}
			$data[ $k ] = $v;
		}

		return view()->with( [
			'module' => v( 'module.name' ),
			'data'   => $data
		] );
	}

	/**
	 * 添加/修改回复关键词
	 * @return mixed
	 */
	public function post() {
		auth( 'system_rule' );
		//模块关于回复关键词的处理类
		$class    = ( v( 'module.is_system' ) ? '\module\\' : 'addons\\' ) .
		            v( 'module.name' ) . '\system\Rule';
		$instance = new $class();
		if ( IS_POST ) {
			$data             = json_decode( Request::post( 'keyword' ), true );
			$data['rid']      = isset( $data['rid'] ) ? $data['rid'] : 0;
			$data['rank']     = $data['istop'] == 1 ? 255 : min( 255, intval( $data['rank'] ) );
			$data['module']   = v( 'module.name' );
			$data['keywords'] = $data['keyword'];
			$rid              = \Wx::rule( $data );
			//调用模块的执行方法进行数据验证
			$msg = $instance->fieldsValidate( $rid );
			if ( $msg !== true ) {
				message( $msg, 'back', 'error' );
			}
			//使模块保存回复内容
			$instance->fieldsSubmit( $rid );
			message( '规则保存成功', u( 'post', [ 'rid' => $rid, 'm' => v( 'module.name' ) ] ) );
		}
		//获取关键词回复
		if ( $rid = Request::get( 'rid' ) ) {
			$data = Model::where( 'rid', $rid )->first()->toArray();
			if ( empty( $data ) ) {
				message( '回复规则不存在', 'lists', 'error' );
			}
			$data['keyword'] = Db::table( 'rule_keyword' )->orderBy( 'id', 'asc' )->where( 'rid', $rid )->get();
			View::with( 'rule', $data );
		}

		$moduleForm = $instance->fieldsDisplay( $rid );

		return view()->with( 'moduleForm', $moduleForm );
	}

	//删除规则
	public function remove() {
		auth( 'system_rule' );
		$rid = Request::get( 'rid' );
		\Wx::removeRule( $rid );
		//执行模块中的删除动作
		$class    = ( v( 'module.is_system' ) ? '\module\\' : 'addons\\' ) .
		            v( 'module.name' ) . '\system\Rule';
		$instance = new $class();
		$instance->ruleDeleted( $rid );
		message( '文字回复删除成功', 'back', 'success' );
	}
}