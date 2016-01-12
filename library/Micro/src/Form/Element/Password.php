<?php

namespace Micro\Form\Element;

use Micro\Form\Element;

class Password extends Element
{
    public function render()
    {
        $tmp = '';

        $name = $this->getFullyName();

        $tmp .= '<input type="password" name="' . $name . '"' . $this->htmlAttributes() . ' />';

        $tmp .= $this->renderErrors();

        return $tmp;
    }
}