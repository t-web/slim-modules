<?php

class IndexController extends \BenGee\Slim\Modules\Controller
{
    public function indexAction()
    {
        $this->display('index.twig');
    }
}
