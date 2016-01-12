<?php

namespace Micro\Form\Element;

use Micro\Form\Element;

class Hidden extends Element
{
    public function render()
    {
        $tmp = '';

        $name = $this->getFullyName();

        $tmp .= '<input type="hidden" name="' . $name . '" value="' . $this->view->escape($this->getValue()) . '"' . $this->htmlAttributes() . ' />';

        $tmp .= $this->renderErrors();

        return $tmp;
    }
}