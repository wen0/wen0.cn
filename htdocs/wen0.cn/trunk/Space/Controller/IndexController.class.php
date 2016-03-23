<?php
namespace Space\Controller;
use Common\Controller\CommonController;
class IndexController extends CommonController 
{
    public function index()
    {
        dump(S(md5('wen0')));
    	dump(I('get.'));
    	echo MODULE_NAME;
    }

    public function test()
    {
       
    }

}