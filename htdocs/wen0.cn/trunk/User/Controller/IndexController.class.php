<?php
namespace User\Controller;
use Common\Controller\CommonController;
class IndexController extends CommonController 
{
    public function index()
    {
    	dump(I('get.'));
    	echo MODULE_NAME;
    }
}