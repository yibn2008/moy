<?php
// Test Component
class Component_Test extends Moy_View_Component
{
    /**
     * (non-PHPdoc)
     * @see Moy_View_Component::execute()
     */
    public function execute(array $params)
    {
        $this->assignArray($params);
    }
}