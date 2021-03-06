<?php namespace system\service\cloud;

use system\model\Cloud as CloudModel;
use system\model\Modules;

/**
 * 远程云请求
 * Class Cloud
 * @package system\service\cloud
 * @author 向军 <2300071698@qq.com>
 * @site www.houdunwang.com
 */
class Cloud {
	//云主机
	protected $host = 'http://store.hdcms.com';
	protected $url;
	//云帐号
	protected $accounts;

	public function __construct() {
		$this->accounts = Db::table( 'cloud' )->first();
		$this->url      = "http://store.hdcms.com?&secret={$this->accounts['secret']}&uid={$this->accounts['uid']}&m=store&action=controller";
	}

	//验证云帐号状态
	public function checkAccount() {
		if ( $this->accounts['status'] == 0 ) {
			message( '请先绑定云帐号后执行操作', u( 'cloud.account' ), 'warning' );
		}
	}

	/**
	 * 绑定云帐号
	 *
	 * @param $data 帐号、密码、密钥数据
	 *
	 * @return mixed
	 */
	public function connect( $data ) {
		$res   = Curl::post( $this->url . '/cloud/connect', $data );
		$res   = json_decode( $res, true );
		$model = CloudModel::find( 1 );
		if ( $res['valid'] == 1 ) {
			//连接成功
			$model['uid']      = $res['uid'];
			$model['username'] = $data['username'];
			$model['secret']   = $data['secret'];
			$model['webname']  = $data['webname'];
			$model['status']   = 1;
			$model->save();
		} else {
			$model['status'] = 0;
			$model->save();
		}

		return $res;
	}

	/**
	 * 检测HDCMS有没有新版本可用来更新
	 * @return array
	 */
	public function getUpgradeVersion() {
		$data = CloudModel::find( 1 )->toArray();
		$res = Curl::post( $this->url . '/cloud/getUpgradeVersion', $data );

		return json_decode( $res, true );
	}

	/**
	 * 更新HDCMS安装数量
	 * @return mixed
	 */
	public function updateHDownloadNum() {
		$res = Curl::get( $this->url . '/cloud/updateHDownloadNum&build='
		                   . Db::table( 'cloud' )->where( 'id', 1 )->pluck( 'build' ) );

		return json_decode( $res, true );
	}

	/**
	 * 下载HDCMS更新包
	 * @return array
	 */
	public function downloadUpgradeVersion() {
		$soft    = $this->getUpgradeVersion();
		$content = \Curl::get( $this->host . '/' . $soft['hdcms']['file'] );
		\Dir::create( 'upgrade/hdcms' );
		file_put_contents( 'upgrade/hdcms.zip', $content );
		chdir( 'upgrade' );
		Zip::PclZip( 'hdcms.zip' );//设置压缩文件名
		Zip::extract();//解压缩到当前目录
		chdir( '..' );

		//将旧版本文件进行备份
		$files   = file_get_contents( 'upgrade/hdcms/upgrade_files.php' );
		$files   = preg_split( '/\n/', $files );
		$current = Db::table( 'cloud' )->find( 1 );
		foreach ( $files as $f ) {
			$info = preg_split( '/\s+/', $f );
			\Dir::copyFile( $info[1], "upgrade/{$current['version']}/{$info[1]}" );
		}
		//直接文件替换操作
		$formats = [ ];
		foreach ( $files as $f ) {
			$info      = preg_split( '/\s+/', $f );
			$formats[] = [ 'type' => $info[0], 'file' => $info[1] ];
		}
		//验证结果
		$valid = 1;
		foreach ( $formats as $k => $info ) {
			switch ( $info['type'] ) {
				case 'D':
					//删除文件
					if ( \Dir::delFile( $info[1] ) === false ) {
						$valid                   = 0;
						$formats[ $k ]['status'] = false;
					} else {
						$formats[ $k ]['status'] = true;
					}
					break;
				default:
					if ( \Dir::copyFile( "upgrade/hdcms/" . $info['file'], $info['file'] ) === false ) {
						$formats[ $k ]['status'] = false;
					} else {
						$formats[ $k ]['status'] = true;
					}
			}
		}

		return [ 'files' => $formats, 'valid' => $valid ];
	}

	/**
	 * 获取商城中的模块或模板列表
	 *
	 * @param $type 类型 module/template
	 * @param $page 页数
	 *
	 * @return mixed
	 */
	public function apps( $type, $page ) {
		$this->checkAccount();
		if ( empty( $type ) || empty( $page ) ) {
			message( '参数错误无法获取应用列表', '', 'warning' );
		}
		$content = \Curl::get( $this->url . "/cloud/apps&type={$type}&page={$page}" );
		$apps    = json_decode( $content, true );
		if ( $apps['valid'] == 1 ) {
			//已经安装的所模块
			$modules = Modules::lists( 'name' );
			foreach ( $apps['apps'] as $k => $v ) {
				//是否安装
				$apps['apps'][ $k ]['is_install'] = false;
				if ( in_array( $v['name'], $modules ) ) {
					$apps['apps'][ $k ]['is_install'] = true;
				}
			}

			return $apps;
		}
		message( $apps['message'], '', 'error' );
	}

	/**
	 * 获取模块更新列表
	 */
	public function getModuleUpgradeLists() {
		$this->checkAccount();
		$post = Modules::where( 'locality', 0 )->lists( 'name,build' );
		if ( empty( $post ) ) {
			ajax( [ 'valid' => 1, 'message' => '系统没有安装任何模块, 去应用商店转转吧。', 'apps' => [ ] ] );
		}
		$content = \Curl::post( $this->url . "/cloud/getModuleUpgradeLists", $post );
		$apps    = json_decode( $content, true );
		if ( $apps['valid'] == 1 ) {
			ajax( $apps );
		}
		ajax( [ 'valid' => 0, 'message' => $apps['message'] ] );
	}

	/**
	 * 更新指定模块
	 *
	 * @param $name 模块标识
	 */
	public function upgradeModuleByName( $name ) {
		$this->checkAccount();
		$module = Db::table( 'modules' )->where( 'name', $name )->first();
		$res    = \Curl::post( $this->url . "/cloud/getModuleUpgrade", $module );
		$app    = json_decode( $res, true );

		if ( $app['valid'] == 1 ) {
			//下载压缩文件
			$content = \Curl::get( $this->host . '/' . $app['zip']['file'] );
			\Dir::create( 'upgrade/module' );
			$file = "addons/{$name}.zip";
			file_put_contents( $file, $content );
			Zip::PclZip( $file );//设置压缩文件名
			Zip::extract( 'addons' );
			file_put_contents( "addons/{$name}/cloud.app", '<?php return ' . var_export( $app, true ) . ';?>' );
			//删除下载压缩包
			\Dir::delFile( $file );
			//执行模块更新表语句
			$class = 'addons\\' . $app['name'] . '\system\Setup';
			call_user_func_array( [ new $class, 'upgrade' ], [ ] );
			//更新数据表模块编译版本
			$data = [ 'version' => $app['zip']['version'], 'build' => $app['zip']['build'] ];
			Modules::where( 'name', $name )->update( $data );
			//更新模块安装数量
			\Curl::post( $this->url . "/cloud/updateModuleInstallNum", $app );
			ajax( [
				'message' => '模块更新完毕',
				'config'  => $app,
				'valid'   => 1
			] );
		} else {
			ajax( $app );
		}
	}

	/**
	 * 下载应用
	 *
	 * @param $type
	 * @param $id
	 */
	public function downloadApp( $type, $id ) {
		$this->checkAccount();
		if ( empty( $type ) || empty( $id ) ) {
			message( '参数错误无法执行安装', '', 'warning' );
		}
		//获取模块信息
		$app = \Curl::get( $this->url . "/cloud/getLastAppById&type={$type}&id={$id}" );
		$app = json_decode( $app, true );

		if ( $app['valid'] == 0 ) {
			ajax( $app );
		}
		//安装前检测
		switch ( $type ) {
			case 'module':
				if ( Modules::where( 'name', $app['name'] )->get() ) {
					message( '应用已经安装,不允许重复安装!', '', 'error' );
				}
				if ( is_dir( "addons/{$app['name']}" ) && ! is_file( "addons/{$app['name']}/cloud.app" ) ) {
					message( '已经存在本地开发的同名模块', '', 'error' );
				}
				break;
			case 'template':
				if ( is_dir( "theme/{$app['name']}" ) && ! is_file( "addons/{$app['name']}/cloud.app" ) ) {
					message( '同名模板已经存在', '', 'error' );
				}
				break;
		}

		//文件保存
		switch ( $type ) {
			case 'module':
				//下载文件
				$content = \Curl::get( $this->host . "/{$app['zip']['file']}" );
				$file    = "addons/{$app['name']}.zip";
				file_put_contents( $file, $content );
				Zip::PclZip( $file );//设置压缩文件名
				Zip::extract( 'addons' );
				file_put_contents( "addons/{$app['name']}/cloud.app", '<?php return ' . var_export( $app, true ) . ';?>' );
				//删除下载压缩包
				\Dir::delFile( $file );
				ajax( [
					'message' => '模块下载完成,准备开始安装',
					'config'  => $app,
					'url'     => u( 'module.install', [ 'module' => $app['name'] ] ),
					'valid'   => 1
				] );
				break;
			case 'template':
				//下载文件
				$content = \Curl::get( $this->host . "/{$app['file']}" );
				$file    = "theme/{$app['name']}.zip";
				file_put_contents( $file, $content );
				Zip::PclZip( $file );//设置压缩文件名
				Zip::extract( 'theme' );
				file_put_contents( "addons/{$app['name']}/cloud.app", '来自云应用' );
				//删除下载压缩包
				\Dir::delFile( $file );
				ajax( [
					'message' => '模板下载完成,准备开始安装',
					'config'  => $app,
					'url'     => u( 'template.install', [ 'name' => $app['name'] ] ),
					'valid'   => 1
				] );
				break;
		}


	}
}







