<?php

namespace Micro\Form\Element;

use Micro\Form\Element;

class Textarea extends Element
{
    public function render()
    {
        $tmp = '';

        $name = $this->getFullyName();

        $tmp .= '<textarea name="' . $name . '"' . $this->htmlAttributes() . '>' . $this->view->escape($this->getValue()) . '</textarea>';

        $tmp .= $this->renderErrors();

        return $tmp;
    }
}