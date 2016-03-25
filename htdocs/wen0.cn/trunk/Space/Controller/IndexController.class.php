<?php
namespace Space\Controller;
use Common\Controller\CommonController;
class IndexController extends CommonController 
{
    public function index()
    {
        dump(S(md5('wen0')));
    }

    public function test()
    {
       echo 'test';
    }

}