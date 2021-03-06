<?php namespace addons\test\system;

/**
 * 回复规则列表
 * 回复规则列表用于设置多个微信回复关键词使用
 * 本类通过系统内部自动调用处理
 * @author 模块作者
 * @url http://www.hdcms.com
 */
use module\HdRule;

class Rule extends HdRule {
	/**
	 * 显示自定义的回复界面
	 * 返回true表示验证通过
	 * $rid 为规则编号,新增时为0,编辑时才有具体编号
	 * @param int $rid
	 */
	public function fieldsDisplay( $rid = 0 ) {
	}
	
	/**
	 * 验证用户输入内容的合法性
	 * 返回true表示验证通过
	 * $rid 为规则编号,新增时为0,编辑时才有具体编号
	 * @param int $rid
	 * @return bool
	 */
	public function fieldsValidate( $rid = 0 ) {
		return true;
	}
	
	/**
	 * 保存回复内容到模块表中
	 * 这里应该进行自定义字段的保存
	 * $rid 为规则编号,新增时为0,编辑时才有具体编号
	 * @param int $rid
	 */
	public function fieldsSubmit( $rid ) {
	}
	
	/**
	 * 删除规则时调用
	 * $rid 为规则编号,新增时为0,编辑时才有具体编号
	 * @param int $rid
	 */
	public function ruleDeleted( $rid ) {
	}
}