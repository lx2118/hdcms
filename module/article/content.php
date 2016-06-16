<?php
/** .-------------------------------------------------------------------
 * |  Software: [HDCMS framework]
 * |      Site: www.hdcms.com
 * |-------------------------------------------------------------------
 * |    Author: 向军 <2300071698@qq.com>
 * |    WeChat: aihoudun
 * | Copyright (c) 2012-2019, www.houdunwang.com. All Rights Reserved.
 * '-------------------------------------------------------------------*/
namespace module\article;

use module\hdSite;
use web\site;

/**
 * 模板列表
 * Class content
 * @package module\article
 */
class content extends hdSite {
	//添加栏目
	public function doSiteCategory() {
		$data = Db::table( 'web_category' )->where( 'siteid', '=', v( 'site.siteid' ) )->get();
		foreach ( $data as $k => $v ) {
			$data[ $k ]['url'] = __ROOT__ . '/index.php?s=content/home/category&siteid=' . v( 'site.siteid' ) . '&cid=' . $v['cid'];
		}
		$data = Data::tree( $data, 'title', 'cid', 'pid' );
		View::with( 'data', $data )->make( $this->template . '/content/category.php' );
	}

	//栏目修改
	public function doSiteCategoryPost() {
		$cid = q( 'get.cid' );
		if ( $cid && ! Db::table( 'web_category' )->find( $cid ) ) {
			message( '栏目不存在', 'back', 'error' );
		}
		if ( IS_POST ) {
			$data        = json_decode( $_POST['data'], TRUE );
			$data['css'] = serialize( $data['css'] );
			$data['pid'] = empty( $data['pid'] ) ? 0 : $data['pid'];
			$cid         = Db::table( 'web_category' )->replaceGetId( $data );
			//是添加微站首页导航
			if ( $data['isnav'] ) {
				$nav['siteid']       = v( 'site.siteid' );
				$nav['name']         = $data['title'];
				$nav['url']          = $data['linkurl'] ?: '?a=article/entry/category&t=web&siteid=' . v( 'site.siteid' ) . '&cid=' . $data['cid'];
				$nav['css']          = serialize( $data['css'] );
				$nav['status']       = 1;
				$nav['entry']        = 'home';
				$nav['icontype']     = $data['icontype'];
				$nav['category_cid'] = $cid;
				$nav['web_id']       = $data['web_id'];
				$nav['description']  = $data['description'];
				if ( ! api( 'article' )->addNav( $nav ) ) {
					message( '添加微站导航失败', 'back', 'error' );
				}
			} else {
				Db::table( 'web_nav' )->where( 'category_cid', '=', $cid )->delete();
			}
			message( '栏目保存成功', site_url( 'category' ), 'success' );
		}
		//所有栏目
		$category = Db::table( 'web_category' )->where( 'siteid', '=', v( 'site.siteid' ) )->get();
		$category = Data::tree( $category, 'title', 'cid', 'pid' );
		if ( $cid ) {
			//编辑时在栏目选择中不显示自身与子级栏目
			foreach ( $category as $k => $v ) {
				if ( $v['cid'] == $cid || Data::isChild( $category, $v['cid'], $cid ) ) {
					unset( $category[ $k ] );
				}
			}
		}
		//所有站点
		$web = Db::table( 'web' )->where( 'siteid', '=', v( 'site.siteid' ) )->get();
		$web or message( '没有站点不能添加文章类别', 'manage/site', 'error' );
		if ( $cid ) {
			//编辑
			$field        = Db::table( 'web_category' )->find( $cid );
			$field['css'] = unserialize( $field['css'] );
			//栏目模板
			$template = Db::table( 'template' )->find( $field['template_tid'] );
		} else {
			$defaultWeb = api( 'web' )->getDefaultWeb();
			$field      = [
				'isnav'        => 0,
				'ishomepage'   => 0,
				'css'          => [ 'icon' => 'fa fa-external-link', 'image' => '', 'color' => '#333333', 'size' => 35 ],
				'icontype'     => 1,
				'description'  => '',
				'orderby'      => 0,
				'pid'          => 0,
				'template_tid' => 0,
				'status'       => 1,
				'linkurl'      => '',
				'siteid'       => v( 'site.siteid' ),
				'web_id'       => $defaultWeb['id'],
			];
			$template   = [ ];
		}
		View::with( 'web', Arr::string_to_int( $web ) );
		View::with( 'field', Arr::string_to_int( $field ) );
		View::with( 'category', Arr::string_to_int( $category ) );
		View::with( 'template', Arr::string_to_int( $template ) );
		View::make( $this->template . '/content/categoryPost.php' );
	}

	//删除栏目
	public function doSiteRemoveCat() {
		$cid = q( 'get.cid' );
		if ( ! Db::table( 'web_category' )->find( $cid ) ) {
			ajax( [ 'valid' => 0, 'message' => '栏目不存在' ] );
		}
		if ( Db::table( 'web_category' )->where( 'pid', $cid )->get() ) {
			ajax( [ 'valid' => 0, 'message' => '请先删除子栏目' ] );
		}
		Db::table( 'web_category' )->where( 'cid', '=', $cid )->delete();
		Db::table( 'web_nav' )->where( 'category_cid', '=', $cid )->delete();
		ajax( [ 'valid' => 1, 'message' => '栏目删除成功' ] );
	}

	//文章管理
	public function doSiteArticle() {
		$count = Db::table( 'web_article' )->where( 'siteid', '=', v( 'site.siteid' ) )->count();
		$page  = Page::make( $count );
		$data  = Db::table( 'web_article' )->where( 'siteid', '=', v( 'site.siteid' ) )->limit( Page::limit() )->orderBy( 'orderby', 'desc' )->get();
		View::with( 'data', $data );
		View::with( 'page', $page );
		View::make( $this->template . '/content/article.php' );
	}

	//文章编辑&发表
	public function doSiteArticlePost() {
		$aid = q( 'get.aid' );
		if ( $aid && ! Db::table( 'web_article' )->find( $aid ) ) {
			message( '文章不存在', 'back', 'error' );
		}
		if ( IS_POST ) {
			$data = json_decode( $_POST['data'], TRUE );
			//保存文章
			$aid = api( 'article' )->saveArticle( $data );
			//回复规则与回复关键词设置
			if ( isset( $data['rid'] ) ) {
				$rule['rid'] = $data['rid'];
			}
			$rule['name']    = "article:{$aid}";
			$rule['module']  = 'article';
			$rule['keyword'] = [
				[
					'content' => $data['keyword'],
					'type'    => 1
				]
			];
			$rid             = api( 'rule' )->save( $rule );
			//修改文章回复规则编号
			Db::table( 'web_article' )->update( [ 'aid' => $aid, 'rid' => $rid ] );
			message( '保存文章成功', site_url( 'article', [ 'cid' => q( 'get.cid' ) ] ), 'success' );
		}
		//栏目列表
		$category = Db::table( 'web_category' )->where( 'siteid', '=', v( 'site.siteid' ) )->get();
		$category = Data::tree( $category, 'title', 'cid', 'pid' );
		if ( empty( $category ) ) {
			message( '请先添加文章类别', 'category', 'error' );
		}
		//所有模板数据
		$template = api( 'template' )->get();
		//获取文章数据
		if ( $aid ) {
			$field = Db::table( 'web_article' )->find( $aid );
			//关键词
			$field['keyword'] = Db::table( 'rule_keyword' )->where( 'rid', $field['rid'] )->limit( 1 )->pluck( 'content' );
		} else {
			$field = [ 'keyword' => '' ];
		}
		View::with( 'field', Arr::string_to_int( $field ) );
		View::with( 'template', $template );
		View::with( 'category', Arr::string_to_int( $category ) );
		View::make( $this->template . '/content/articlePost.php' );
	}

	//删除文章
	public function doSiteArticleDel() {
		$aid  = q( 'get.aid', 0, 'intval' );
		$news = Db::table( 'web_article' )->where( 'aid', '=', $aid )->first();
		Db::table( 'rule' )->where( 'rid', '=', $news['rid'] )->delete();
		Db::table( 'rule_keyword' )->where( 'rid', '=', $news['rid'] )->delete();
		Db::table( 'web_article' )->where( 'aid', '=', $aid )->delete();
		message( '删除 [' . $news['title'] . '] 文章成功', 'back', 'success' );
	}

	//文章排序
	public function doSiteArticleOrder() {
		$data = json_decode( $_POST['data'], TRUE );
		foreach ( $data as $a ) {
			$orderby = min( 255, intval( $a['orderby'] ) );
			Db::table( 'web_article' )->where( 'aid', '=', $a['aid'] )->update( [ 'orderby' => $orderby ] );
		}
		message( '修改文章排序成功', 'back', 'success' );
	}

}