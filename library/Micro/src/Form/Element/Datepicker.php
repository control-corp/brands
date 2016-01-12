<?php

namespace Micro\Form\Element;

use Micro\Form\Element;

class Datepicker extends Element
{
    protected $format = 'Y-m-d H:i:s';

    public function render()
    {
        $tmp = '';

        $name = $this->getFullyName();

        $value = $this->getValue();

        if ($value instanceof \DateTime) {
            $value = $value->format($this->format);
        } else if (is_string($value)) {
            $date  = new \DateTime($value);
            $value = $date->format($this->format);
        } else {
            $value = '';
        }

        $tmp .= '<input type="text" name="' . $name . '" value="' . $this->view->escape($value) . '"' . $this->htmlAttributes() . ' />';

        $tmp .= $this->renderErrors();

        return $tmp;
    }

    public function setFormat($value)
    {
        $this->format = $value;
    }
}