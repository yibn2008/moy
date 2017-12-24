<?php
/**
 * 标签组合框
 *
 * 根据控件命令规则, 此控件的名称为: test.taggroup
 */
class Control_Test_Taggroup extends Moy_Form_Control
{
    public function __construct()
    {
        $this->_value = array();
    }

    public function setValue($value)
    {
        if (!is_array($value)) {
            $value = array($value);
        }

        $this->_value = $value;
    }

    public function setOptions(array $options) {}

    public function render()
    {
        $prop_strs = array();
        foreach ($this->_props as $prop => $value) {
            $prop_strs[] = "$prop=\"$value\"";
        }

        echo '<div ' . implode(' ', $prop_strs) . ">\n" . implode("\n", $this->getRenderArray()) . "\n</div>";
    }

    public function getRenderArray()
    {
        $inputs = array();
        foreach ($this->_value as $value) {
            $inputs[] = parent::textTag($this->_name . '[]', $value);
        }

        return $inputs;
    }
}