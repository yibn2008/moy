<?php
/**
 * class Controller_Default for testing
 */
class Controller_Default extends Moy_Controller
{
    private $_exec_list = array();

    public function _preExecute(Moy_Request $request)
    {
        $this->_exec_list[] = 'pre';
    }

    public function indexAction(Moy_Request $request)
    {
        $this->_exec_list[] = 'index';
        $this->setLayout(false);
    }

    public function flashAction(Moy_Request $request)
    {
        $flash_msg = Moy::getSession()->flushFlashMsg($name);
    }

    public function testAction(Moy_Request $request)
    {
        $this->_exec_list[] = 'test';
    }

    public function forwardAction(Moy_Request $request)
    {
        $this->_exec_list[] = 'forward';
        $this->forward('test', 'default');
    }

    public function forwardOtherAction(Moy_Request $request)
    {
        $this->_exec_list[] = 'forwardOther';
        $this->forward('test', 'test');
    }

    public function getExecList()
    {
        return implode(',', $this->_exec_list);
    }

    public function cleanExecList()
    {
        $this->_exec_list = array();
    }
}