<?php
namespace Weixin\Controller;
class IndexController extends WeixinController
{
    public function index()
    {
        echo 123;
		$this->display();
    }
}