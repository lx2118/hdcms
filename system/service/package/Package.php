<?php namespace system\service\package;

use system\model\UserGroup;
use system\service\Common;
use system\model\Package as PackageModel;

/**
 * Class Package
 * @package system\service\Package
 * @author 向军 <2300071698@qq.com>
 * @site www.houdunwang.com
 */
class Package extends Common {
	/**
	 * 获取站点拥有的套餐数据
	 * 包括站长默认套餐+为站点独立设置的扩展套餐
	 *
	 * @param int $siteId 站点编号
	 *
	 * @return array
	 */
	public function getSiteAllPackageData( $siteId = 0 ) {
		$siteId = $siteId ?: SITEID;
		static $cache = [ ];
		if ( isset( $cache[ $siteId ] ) ) {
			return $cache[ $siteId ];
		}
		$ids      = $this->getSiteAllPackageIds( $siteId );
		$packages = [ ];
		if ( ! empty( $ids ) ) {
			if ( in_array( - 1, $ids ) ) {
				//拥有所有套餐权限
				$packages = Db::table( 'package' )->get() ?: [ ];
			} else {
				$packages = Db::table( 'package' )->whereIn( 'id', $ids )->get() ?: [ ];
			}
		}
		foreach ( $packages as $k => $v ) {
			$packages[ $k ]['modules']  = unserialize( $v['modules'] );
			$packages[ $k ]['template'] = unserialize( $v['template'] );
		}
		//含有[所有服务]套餐时
		if ( in_array( - 1, $ids ) ) {
			array_unshift( $packages, [ 'id' => - 1, 'name' => '所有服务', 'modules' => [ ], 'template' => [ ] ] );
		}

		return $cache[ $siteId ] = $packages;
	}

	/**
	 * 站点所有套餐列表编号
	 * 包括站点默认套餐+为站点独立设置的扩展套餐
	 *
	 * @param int $siteId 站点编号
	 *
	 * @return array 数组中包含-1套餐表示可以使用所有模块
	 */
	public function getSiteAllPackageIds( $siteId ) {
		static $cache = [ ];
		if ( isset( $cache[ $siteId ] ) ) {
			return $cache[ $siteId ];
		}
		//获取站长拥有的套餐
		$default = $this->getSiteDefaultPackageIds( $siteId );
		//站点自定义扩展的套餐
		$ext = $this->getSiteExtPackageIds( $siteId );

		return $cache[ $siteId ] = array_merge( $default, $ext );
	}

	/**
	 * 根据站长用户组获取站点套餐
	 * 不含为站点自定义的扩展套餐
	 * 套餐由用户组套餐+站点扩展套餐构成
	 *
	 * @param int $siteId 站点编号
	 *
	 * @return mixed
	 */
	public function getSiteDefaultPackageIds( $siteId = 0 ) {
		$siteId = $siteId ?: SITEID;
		static $cache = [ ];
		if ( isset( $cache[ $siteId ] ) ) {
			return $cache[ $siteId ];
		}
		//获取站长拥有的套餐
		$sql = "SELECT ug.package FROM " .
		       tablename( 'user' ) . " u JOIN " .
		       tablename( 'user_group' ) . " ug ON u.groupid=ug.id JOIN " .
		       tablename( 'site_user' ) . " su ON u.uid=su.uid " .
		       "WHERE su.siteid={$siteId} AND su.role='owner'";
		if ( $res = Db::query( $sql ) ) {
			$cache[ $siteId ] = json_decode( $res[0]['package'], true ) ?: [ ];
		} else {
			//没有站长时即为系统管理员添加的站点,默认有所有权限
			$cache[ $siteId ] = [ - 1 ];
		}

		return $cache[ $siteId ];
	}

	/**
	 * 获取为站点自定义扩展套餐编号
	 * 站点使用的套餐由用户组套餐+站点扩展套餐构成
	 *
	 * @param int $siteId 站点编号
	 *
	 * @return array
	 */
	public function getSiteExtPackageIds( $siteId = 0 ) {
		$siteId = $siteId ?: SITEID;

		return Db::table( 'site_package' )->where( 'siteid', $siteId )->lists( 'package_id' ) ?: [ ];
	}

	/**
	 * 获取系统所有自定义套餐
	 * 不含基础套餐与所有套餐
	 * @return array
	 */
	public function getSystemAllPackageData() {
		static $cache = [ ];
		if ( ! empty( $cache ) ) {
			return $cache;
		}
		$packages = Db::table( 'package' )->get() ?: [ ];
		foreach ( $packages as $k => $v ) {
			$packages[ $k ]['modules'] = $packages[ $k ]['template'] = [ ];
			if ( $modules = unserialize( $v['modules'] ) ) {
				$packages[ $k ]['modules'] = Db::table( 'modules' )->whereIn( 'name', $modules )->get();
			}

			if ( $template = unserialize( $v['template'] ) ) {
				$packages[ $k ]['template'] = Db::table( 'template' )->whereIn( 'name', $template )->get();
			}
		}
		array_unshift( $packages, [ 'id' => - 1, 'name' => '所有服务', 'modules' => [ ], 'template' => [ ] ] );
		array_unshift( $packages, [ 'id' => 0, 'name' => '基础服务', 'modules' => [ ], 'template' => [ ] ] );

		return $cache = $packages;
	}

	/**
	 * 根据会员组编号获取该会员组的所有套餐信息
	 *
	 * @param int $groupId 会员组编号
	 *
	 * @return array
	 */
	public function getUserGroupPackageLists( $groupId ) {
		$group = Db::table( 'user_group' )->find( $groupId );
		//用户套餐
		$packageIds = unserialize( $group['package'] ) ?: [ ];
		//套餐数据
		$packages = $packageIds ? Db::table( 'package' )->whereIn( 'id', $packageIds )->get() : [ ];
		foreach ( $packages as $k => $p ) {
			$packages[ $k ]['modules'] = $packages[ $k ]['template'] = [ ];
			if ( $names = unserialize( $p['modules'] ) ) {
				$packages[ $k ]['modules'] = Db::table( 'modules' )->whereIn( 'name', $names )->get() ?: [ ];
			}
			if ( $names = unserialize( $p['template'] ) ) {
				$packages[ $k ]['template'] = Db::table( 'template' )->whereIn( 'name', $names )->get() ?: [ ];
			}
		}
		//当套餐编号有-1为拥有所有套餐
		if ( in_array( - 1, $packageIds ) ) {
			array_unshift( $packages, [ 'id' => - 1, 'name' => '所有服务', 'modules' => '', 'template' => '' ] );
		}

		return $packages;
	}

	/**
	 * @param $id
	 */
	public function remove( $id ) {
		//从数组中移除要删除的套餐编号
		$groups = UserGroup::get();
		foreach ( $groups as $group ) {
			$package          = \Arr::del( unserialize( $group['package'] ), [ $id ] );
			$group['package'] = $package;
			$group->save();
		}

		return PackageModel::delete( $id );
	}

	/**
	 * 从套餐中移除模块
	 *
	 * @param string $module 模块名
	 *
	 * @return bool
	 */
	public function removeModule( $module ) {
		//更新套餐数据
		$package = PackageModel::get();
		if ( $package ) {
			foreach ( $package as $p ) {
				$modules = json_decode( $p['modules'], true ) ?: [ ];
				if ( ( $k = array_search( $module, $modules ) ) !== false ) {
					unset( $modules[ $k ] );
				}
				$p['modules'] = $modules;

				return $p->save();
			}
		}

		return true;
	}
}