<?php namespace addons\salary\system;

/**
 * 模块配置管理
 * 用于管理当前模块的配置项
 * 每个模块配置是独立管理的互不影响
 * @author 后盾团队
 * @url http://www.hdcms.com
 */
use houdunwang\database\build\Blueprint;
use module\HdSetup;

class Setup extends HdSetup {
	/**
	 * 模块安装执行的方法
	 * 可以执行模块数据表安装语句
	 * 建议使用HDPHP中的Schema组件完成数据安装
	 */
	public function install() {
		$sql = <<<sql
CREATE TABLE `hd_salary_student` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '学生姓名',
  `graduation` varchar(100) NOT NULL DEFAULT '' COMMENT '毕业时间',
  `wage` varchar(100) NOT NULL DEFAULT '' COMMENT '就业薪资',
  `present` varchar(100) NOT NULL DEFAULT '' COMMENT '目前薪资',
  `address` varchar(100) NOT NULL DEFAULT '' COMMENT '工作地址',
  `createtime` int(11) unsigned NOT NULL COMMENT '创建时间',
  `orderby` tinyint(3) unsigned NOT NULL COMMENT '排序',
  `company` varchar(100) DEFAULT NULL COMMENT '就业公司',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
sql;
		Db::execute( $sql );
	}
	
	/**
	 * 卸载模块时执行的方法
	 * 可以执行模块数据表删除语句
	 * 建议使用HDPHP中的Schema组件完成数据安装
	 */
	public function uninstall() {
	}
	
	/**
	 * 模块更新时执行的方法
	 * 可以执行模块更新时修改数据表的语句
	 * 建议使用HDPHP中的Schema组件完成数据安装
	 */
	public function upgrade() {
		Schema::table('salary_student', function (Blueprint $table) {
			//添加字段
			$table->string('company', 100)->add();
		});
	}
}