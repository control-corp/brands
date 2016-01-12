<?php

namespace Micro\Form\Element;

use Micro\Form\Element;

class Button extends Element
{
    public function render()
    {
        $tmp = '';

        $tmp .= '<button name="' . $this->getFullyName() . '"' . $this->htmlAttributes() . '>' . $this->view->escape($this->getValue()) . '</button>';

        $tmp .= $this->renderErrors();

        return $tmp;
    }
}