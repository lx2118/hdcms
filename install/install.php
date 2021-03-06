<?php
session_start();
set_time_limit( 0 );
error_reporting( 0 );
header( "Content-type:text/html;charset=utf-8" );
if ( version_compare( PHP_VERSION, '5.4.0', '<' ) ) {
	die( 'HDCMS 需要PHP版本大于php5.4' . PHP_VERSION );
}
$action = isset( $_GET['a'] ) ? $_GET['a'] : 'copyright';
//软件包地址
$cloudHost         = 'http://store.hdcms.com';
$cloudUrl          = $cloudHost."?m=store&action=controller/cloud";
//版权信息
if ( $action == 'copyright' ) {
	$content = isset( $copyright ) ? $copyright : file_get_contents( 'copyright.html' );
	echo $content;
	exit;
}
//环境检测
if ( $action == 'environment' ) {
	//系统信息
	$data['PHP_OS']              = PHP_OS;
	$data['SERVER_SOFTWARE']     = $_SERVER['SERVER_SOFTWARE'];
	$data['PHP_VERSION']         = PHP_VERSION;
	$data['upload_max_filesize'] = get_cfg_var( "upload_max_filesize" ) ? get_cfg_var( "upload_max_filesize" ) : "不允许上传附件";
	$data['max_execution_time']  = get_cfg_var( "max_execution_time" ) . "秒 ";
	$data['memory_limit']        = get_cfg_var( "memory_limit" ) ? get_cfg_var( "memory_limit" ) : "0";
	//运行环境
	$data['h_PHP_VERSION'] = PHP_VERSION;
	$data['h_mysql']       = extension_loaded( 'mysql' ) ? '<i class="fa fa-check-circle fa-1x alert-success"></i>' : '<i class="fa fa-times-circle alert-danger"></i>';
	$data['h_Pdo']         = extension_loaded( 'Pdo' ) ? '<i class="fa fa-check-circle fa-1x alert-success"></i>' : '<i class="fa fa-times-circle alert-danger"></i>';
	$data['h_Gd']          = extension_loaded( 'Gd' ) ? '<i class="fa fa-check-circle fa-1x alert-success"></i>' : '<i class="fa fa-times-circle alert-danger"></i>';
	$data['h_curl']        = extension_loaded( 'curl' ) ? '<i class="fa fa-check-circle fa-1x alert-success"></i>' : '<i class="fa fa-times-circle alert-danger"></i>';
	$data['h_openSSL']     = extension_loaded( 'openSSL' ) ? '<i class="fa fa-check-circle fa-1x alert-success"></i>' : '<i class="fa fa-times-circle alert-danger"></i>';
	//目录状态
	$data['d_root'] = is_writable( '.' ) ? '<i class="fa fa-check-circle fa-1x alert-success"></i>' : '<i class="fa fa-times-circle alert-danger"></i>';
	$content        = isset( $environment ) ? $environment : file_get_contents( 'environment.html' );
	foreach ( $data as $t => $v ) {
		$content = str_replace( "{hd:{$t}}", $v, $content );
	}
	echo $content;
	exit;
}
//执行安装
if ( $action == 'database' ) {
	if ( ! empty( $_POST ) ) {
		//测试数据库连接
		$_SESSION['config'] = $_POST;
		$host               = $_SESSION['config']['host'];
		$username           = $_SESSION['config']['user'];
		$password           = $_SESSION['config']['password'];
		$dbname             = $_SESSION['config']['database'];
		if ( ! mysql_connect( $host, $username, $password ) ) {
			echo json_encode( [ 'valid' => 0, 'message' => '连接失败,请检查帐号与密码' ] );
			exit;
		}
		//数据库
		if ( ! mysql_select_db( $dbname ) ) {
			if ( ! mysql_query( "CREATE DATABASE $dbname CHARSET UTF8" ) ) {
				echo json_encode( [ 'valid' => 0, 'message' => '创建数据库失败' ] );
				exit;
			}
		}
		echo json_encode( [ 'valid' => 1, 'message' => '连接成功' ] );
		exit;
	}
	$content = isset( $database ) ? $database : file_get_contents( 'database.html' );
	echo $content;
	exit;
}
//环境检测
if ( $action == 'download' ) {
	$content = isset( $download ) ? $download : file_get_contents( 'download.html' );
	echo $content;
	exit;
}

//远程下载文件
if ( $action == 'downloadFile' ) {
	if ( is_dir( 'app' ) ) {
		//完整版时
		echo 1;
		exit;
	} else {
		$hdcms             = curl_get( $cloudUrl . '/downloadFullHdcms' );
		$_SESSION['hdcms'] = json_decode( $hdcms, true );
		//更新下载数量
		curl_get( $cloudUrl . '/updateHDownloadNum&build=' . $_SESSION['hdcms']['build'] );
		$d = curl_get( $cloudHost . '/' . $_SESSION['hdcms']['file'] );
		if ( strlen( $d ) < 2787715 ) {
			//下载失败
			exit;
		}
		$zipFile = 'hdcms.zip';
		file_put_contents( $zipFile, $d );
		//解包
		get_zip_originalsize( $zipFile, './' );
		function dcopy( $old, $new ) {
			is_dir( $new ) or mkdir( $new, 0755, true );
			foreach ( glob( $old . '/*' ) as $v ) {
				if ( $v != 'upload/install.php' ) {
					$to = $new . '/' . basename( $v );
					is_file( $v ) ? copy( $v, $to ) : dcopy( $v, $to );
				}
			}

			return true;
		}

		dcopy( 'upload', '.' );
		//删除目录
		function del( $dir ) {
			if ( ! is_dir( $dir ) ) {
				return true;
			}
			foreach ( glob( $dir . "/*" ) as $v ) {
				is_dir( $v ) ? del( $v ) : unlink( $v );
			}

			return rmdir( $dir );
		}

		del( 'upload' );
		echo 1;
		exit;
	}
}

//安装完成,添加数据
if ( $action == 'table' ) {
	//修改配置文件
	file_put_contents( 'data/database.php', '<?php return ' . var_export( $_SESSION['config'], true ) . ';?>' );

	//创建表与初始数据
	curl_get( $_SERVER['HTTP_HOST'] . '/' . dirname( $_SERVER['SCRIPT_NAME'] ) . '/index.php?s=install' );

	//添加数据表
	$dsn      = "mysql:host={$_SESSION['config']['host']};dbname={$_SESSION['config']['database']}";
	$username = $_SESSION['config']['user'];
	$password = $_SESSION['config']['password'];
	$pdo      = new Pdo( $dsn, $username, $password, [ PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'" ] );
	$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

	//更新系统版本号
	$version = include 'data/upgrade.php';
	$build   = $_SESSION['hdcms']['build'];
	$sql     = "REPLACE INTO hd_cloud (id,uid,username,webname,secret,version,build,status)
		VALUES(1,0,'','','','{$_SESSION['hdcms']['version']}',$build,1)";
	try {
		$pdo->exec( $sql );
	} catch ( PDOException $e ) {
		die( 'SQL执于失败:' . $sql . '. ' . $e->getMessage() );
	}
	//设置管理员帐号
	$user     = $pdo->query( "select * from hd_user where uid=1" );
	$row      = $user->fetchAll( PDO::FETCH_ASSOC );
	$username = $_SESSION['config']['username'];
	$password = md5( $_SESSION['config']['upassword'] . $row[0]['security'] );
	$regtime  = time();
	$pdo->exec( "UPDATE hd_user SET username='{$username}',password='{$password}',regtime='{$regtime}' WHERE uid=1" );

	header( 'Location:?a=finish' );
}
if ( $action == 'finish' ) {
	//清除运行数据
	foreach ( glob( 'data/*' ) as $f ) {
		if ( ! in_array( basename( $f ), [ 'database.php', 'upgrade.php' ] ) ) {
			@unlink( $f );
		}
	}
	foreach ( glob( 'install/*' ) as $f ) {
		@unlink( $f );
	}
	is_dir( 'install' ) and rmdir( 'install' );
	is_dir( 'upgrade' ) and rmdir( 'upgrade' );
	//删除下载的压缩包
	@unlink( 'hdcms.zip' );
	@unlink( 'install.php' );
	//显示界面
	$content = isset( $finish ) ? $finish : file_get_contents( 'finish.html' );
	echo $content;
	exit;
}
//编译install.php安装文件,主要是将模板整合进来
if ( $action == 'compile' ) {
	$tpl     = [ 'copyright', 'environment', 'database', 'download', 'finish' ];
	$content = substr( file_get_contents( 'install.php' ), 5 );
	foreach ( $tpl as $t ) {
		$content = '$' . $t . "=<<<str\n" . file_get_contents( $t . '.html' ) . "\nstr;\n" . $content;
	}
	file_put_contents( '../install.php', "<?php \n" . $content );
	echo "<h1>编译成功</h1>";
}

function curl_get( $url ) {
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );

	if ( ! curl_exec( $ch ) ) {
		$data = '';
	} else {
		$data = curl_multi_getcontent( $ch );
	}
	curl_close( $ch );

	return $data;
}

/**
 * 解压zip包
 *
 * @param string $filename 文件名
 * @param string $path 路径 如:./
 *
 * @return bool
 * 例 get_zip_originalsize( '20131101.zip', 'temp/' );
 */
function get_zip_originalsize( $filename, $path ) {
	//先判断待解压的文件是否存在
	if ( ! file_exists( $filename ) ) {
		die( "文件 $filename 不存在！" );
	}
	$starttime = explode( ' ', microtime() ); //解压开始的时间

	//将文件名和路径转成windows系统默认的gb2312编码，否则将会读取不到
	$filename = iconv( "utf-8", "gb2312", $filename );
	$path     = iconv( "utf-8", "gb2312", $path );
	//打开压缩包
	$resource = zip_open( $filename );
	$i        = 1;
	//遍历读取压缩包里面的一个个文件
	while ( $dir_resource = zip_read( $resource ) ) {
		//如果能打开则继续
		if ( zip_entry_open( $resource, $dir_resource ) ) {
			//获取当前项目的名称,即压缩包里面当前对应的文件名
			$file_name = $path . zip_entry_name( $dir_resource );
			//以最后一个“/”分割,再用字符串截取出路径部分
			$file_path = substr( $file_name, 0, strrpos( $file_name, "/" ) );
			//如果路径不存在，则创建一个目录，true表示可以创建多级目录
			if ( ! is_dir( $file_path ) ) {
				mkdir( $file_path, 0777, true );
			}
			//如果不是目录，则写入文件
			if ( ! is_dir( $file_name ) ) {
				//读取这个文件
				$file_size = zip_entry_filesize( $dir_resource );
				//最大读取6M，如果文件过大，跳过解压，继续下一个
				if ( $file_size < ( 1024 * 1024 * 6 ) ) {
					$file_content = zip_entry_read( $dir_resource, $file_size );
					file_put_contents( $file_name, $file_content );
				} else {
					echo "<p> " . $i ++ . " 此文件已被跳过，原因：文件过大， -> " . iconv( "gb2312", "utf-8", $file_name ) . " </p>";
				}
			}
			//关闭当前
			zip_entry_close( $dir_resource );
		}
	}
	//关闭压缩包
	zip_close( $resource );

	return true;
}

