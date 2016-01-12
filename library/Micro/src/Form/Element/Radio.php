<?php

namespace Micro\Form\Element;

class Radio extends Select
{
    public function __construct($name, array $options)
    {
        parent::__construct($name, $options);

        if ($this->isRequired()) {
            $this->addValidator('NotIdentical', array(
                'value' => "",
                'error' => 'Полето е задължително'
            ));
        }
    }

    public function render()
    {
        $tmp = '';

        $name = $this->getFullyName();

        $tmp .= '<input type="hidden" name="' . $name . '" value="" />';

        foreach ($this->multiOptions as $k => $v) {

            $tmp .= '<div class="radio">';

            $checked = '';

            if ($this->getValue() == $k) {
                $checked = ' checked="checked"';
            }

            $tmp .= '<label>';
            $tmp .= '<input type="radio" name="' . $name . '" value="' . $this->view->escape($k) . '"' . $checked . $this->htmlAttributes() . ' />';
            $tmp .= '<span class="label-body">' . $this->view->escape($v) . '</span>';
            $tmp .= '</label>';

            $tmp .=  '</div>';
        }

        $tmp .= $this->renderErrors();

        return $tmp;
    }
}