<?php

namespace Micro\Form\Element;

use Micro\Form\Element;

class Submit extends Element
{
    public function render()
    {
        $tmp = '';

        $tmp .= '<input type="submit" name="' . $this->getFullyName() . '" value="' . escape($this->value) . '"' . $this->htmlAttributes() . ' />';

        if ($this->showErrors == \true) {
            $tmp .= $this->renderErrors();
        }

        return $tmp;
    }
}