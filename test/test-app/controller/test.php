<?php
/**
 * Controller for testing
 */
class Controller_Test extends Moy_Controller
{
    static public $set_static;

    private $_exec_list = array();

    static public function __initStatic()
    {
        self::$set_static = 'initalized';
    }

    public function _preExecute(Moy_Request $request)
    {
        $this->_exec_list[] = 'pre';
    }

    public function indexAction(Moy_Request $request)
    {
        $this->_exec_list[] = 'index';
    }

    public function testAction(Moy_Request $request)
    {
        $this->_exec_list[] = 'test';
    }

    public function throw404Action(Moy_Request $request)
    {
        $this->gotoHttp404();
    }

    public function throw403Action(Moy_Request $request)
    {
        $this->gotoHttp403();
    }

    public function redirectAction(Moy_Request $request)
    {
        $this->redirectTo(Moy::getRouter()->url('default:index'));
    }

    public function flashAction(Moy_Request $request)
    {
        $this->flashTo(Moy::getRouter()->url('default:index'), 'test', 'flash to index');
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