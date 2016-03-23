<?php
namespace Common\Controller;
use Think\Controller;
class CommonController extends Controller {

	protected $TPL      = array();   //全局参数
	protected $domain;
	protected $wx_browser  = false; //是否微信浏览器环境
	protected $isHtmlCache = false;        //是否生成HTML缓存


    protected function _initialize(){
    	$this->domain = $_SERVER['HTTP_HOST'];
    	$this->website_name = (is_ssl()?'https://':'http://').$this->domain.'/';
    }

    /**
     * 输出提示
     * 
     * @param type $status 状态
     * @param type $info   提示语
     * @param type $data   数据，type为page时是跳转URL
     * @param type $type   类型
     * 
     * @return void
     */
    protected function tips($status = 0, $info = '操作提示', $data = array(), $type="jsonp")
    {
        $type = strtolower($type);
        if ($type === "page") {
            if ($status) {
                $this->success($info, $data ? : null);
                exit;
            } else {
                $this->error($info, $data ? : null);
            }
        } else {
            $rs = array();
            $rs['status'] = $status;
            $rs['info']   = $info;
            if ($data) {
                $rs['data'] = $data;
            }
            $this->ajaxReturn($rs, $type);
        }
    }


    /**
     * 渲染模板
     * 
     * @param string $tpl        模板
     * @param string $htmlfile   生成的静态文件名称
     * @param int    $website_id 站点ID
     * 
     * @return void
     */
    protected function _display($tpl = "", $htmlfile = '', $website_id = 0)
    {
        $this->assign("TPL", $this->TPL);
        if ($this->isHtmlCache && $htmlfile) {
            $htmlCachePath = \Tools\Path::getHtmlCachePath($website_id);
            echo $this->buildHtml($htmlfile, $htmlCachePath, $tpl);
        } else {
            $this->display($tpl);
        }
    }


     /**
     * 显示模板分页
     * 
     * @param int    $total 总数
     * @param int    $limit 单页显示数量
     * @param string $url   URL设置
     * @param string $name  分页对象名称，注意加上量词
     * 
     * @return void
     */
    protected function _page($total = 1, $limit = 10, $url='', $name="条记录")
    {
        $pageStr = '';
        if ($total > $limit) { //大于一页才显示分页条
            if (function_exists("showTplPage")) {
                $pageStr = \showTplPage($total, $limit, $url);
            } else {
                $page = new \Think\Page($total, $limit);
                $page->setUrl($url);
                $page->rollPage = 4;
                $page->setConfig('header', '<li class="rows">共 %TOTAL_ROW% '.$name.'</li>');
                $page->setConfig('last', '... %TOTAL_PAGE%');
                $page->setConfig("theme", "<ul class=\"wen0-page\">%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %END% %DOWN_PAGE%</ul>");
                $pageStr = $page->show();
            }
        }
        $this->assign('pageStr', $pageStr);
    }
    
    /**
     * Html静态缓存
     * 
     * @param int    $website_id 站点ID
     * @param string $page       文件
     * @param bool   $clear      是否清除，是删除缓存，否则输出内容，并终止执行
     * 
     * @return void
     */
    protected function htmlCache($website_id = 0, $page = "index.html", $clear = false)
    {
        $ip = get_client_ip(0,  true);
        $ips = explode(',', $this->MK['config']['ip_nocache']);
        if (!in_array($ip, $ips)) { //在客服配置的IP段内，不生成页面缓存
            $htmlCachePath = \Tools\Path::getHtmlCachePath($website_id);
            $file = $htmlCachePath.$page;
            if (is_file($file)) {
                if ($clear) {
                    @unlink($file);
                } else {
                    $filetime = filemtime($file);
                    if ($filetime > time() - 3600) {
                        exit(file_get_contents($file));
                    }
                }
            }
        }
    }





}