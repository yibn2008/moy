<?php

class Component_Sidebar_Generator extends Moy_View_Component
{
    private static $_nav_list = array(
        'frame' => '框架',
        'controller' => '控制器',
        'component' => '视图组件',
        'form' => '表单',
        'control' => '控件',
        'model' => '模型'
    );

    public function execute(array $params)
    {
        if (isset($params['selected'])) {
            $selected = $params['selected'];
        } else {
            $selected = Moy::getRequest()->getAction();
        }

        $this->assign('selected', $selected);
        $this->assign('nav_list', self::$_nav_list);
    }
}