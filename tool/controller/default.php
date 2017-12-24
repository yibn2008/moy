<?php

class Controller_Default extends Moy_Controller
{
    public function indexAction($request)
    {
        $this->assign('message', 'Hello Moy-Tool!');
    }
}