<?php
return [
	/*
	|--------------------------------------------------------------------------
	| 错误提示页面
	|--------------------------------------------------------------------------
	| 发生错误并且闭关DEBUG时,用于显示错误内容的页面
	| 可以根据网站特点自定义页面用于显示一个友好的错误提示
	*/
	'bug'         => ROOT_PATH . '/resource/view/404.php',

	/*
	|--------------------------------------------------------------------------
	| 提示错误处理
	|--------------------------------------------------------------------------
	| 是否显示NOTICE提示错误信息
	| 当为FALSE时所有NOTICE错误系统将忽略显示
	| 建议设置为FALSE
	*/
	'show_notice' => false,

	/*
	|--------------------------------------------------------------------------
	| 开启Trace
	|--------------------------------------------------------------------------
	| Trace调试面板是用于显示系统运行状态的分析面板
	| 当关闭DEBUG时自动关闭Trace
	*/
	'trace'       => true,

	/*
	|--------------------------------------------------------------------------
	| Trace选项卡
	|--------------------------------------------------------------------------
	| 选项卡中文标题
	*/
	'level'       => [
		'view'  => '视图',
		'sql'   => 'sql语句',
		'file'  => '加载文件',
		'error' => '错误',
		'debug' => '调试',
	],
];