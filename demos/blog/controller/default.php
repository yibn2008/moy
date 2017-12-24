<?php

class Controller_Default extends Moy_Controller
{
    public function indexAction()
    {
        $this->assign('message', 'Hello Moy!');
    }

    public function phpinfoAction()
    {
        $this->setLayout(false);
    }
}