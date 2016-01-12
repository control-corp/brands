<?php

namespace Micro\Form\Element;

use Micro\Form\Element;

class Checkbox extends Element
{
    protected $checkedValue = '1';
    protected $uncheckedValue = '0';

    public function __construct($name, array $options)
    {
        parent::__construct($name, $options);

        if ($this->isRequired()) {
            $this->addValidator('NotIdentical', array(
                'value' => $this->uncheckedValue,
                'error' => 'Полето е задължително'
            ));
        }
    }

    public function render()
    {
        $tmp = '';

        $checked = '';

        if ($this->getValue() == $this->checkedValue) {
            $checked = ' checked="checked"';
        }

        $name = $this->getFullyName();

        $tmp .= '<input type="hidden" name="' . $name . '" value="' . $this->view->escape($this->uncheckedValue) . '" />';
        $tmp .= '<input type="checkbox" name="' . $name . '" value="' . $this->view->escape($this->checkedValue) . '"' . $checked . $this->htmlAttributes() . ' />';

        $tmp .= $this->renderErrors();

        return $tmp;
    }

    public function setCheckedValue($value)
    {
        $this->checkedValue = $value;

        return $this;
    }

    public function setUnCheckedValue($value)
    {
        $this->uncheckedValue = $value;

        return $this;
    }
}