<?php

class Component_Navigator extends Moy_View_Component
{
    private static $_nav_list = array(
        'generator' => '代码生成',
        'config' => '配置管理',
        'api' => 'API参考',
        'help' => '帮助文档',
    );

    public function execute(array $params)
    {
        $controller = Moy::getRequest()->getController();
        $selected = null;
        foreach (array_keys(self::$_nav_list) as $ctrlr) {
            if ($ctrlr == $controller) {
                $selected = $controller;
            }
        }

        $this->assign('selected', $selected);
        $this->assign('nav_list', self::$_nav_list);
    }
}