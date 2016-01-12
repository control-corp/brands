<?php

namespace Micro\Form\Element;

use Micro\Application\Utils;
use Micro\Form\Element;
use Micro\Session\SessionNamespace;

class Csrf extends Element
{
    protected $csrf;

    public function __construct($name, array $options)
    {
        parent::__construct($name, $options);

        $session = new SessionNamespace($name . '_csrf');

        $this->setRequired(true);

        $this->addValidator('Identical', array(
            'value' => (isset($session->value) ? $session->value : null),
            'error' => 'Формата е невалидна'
        ));

        $this->csrf = $session->value = md5(Utils::randomSentence(10) . time());
    }

    public function render()
    {
        $tmp = '';

        $name = $this->getFullyName();

        $tmp .= '<input type="hidden" name="' . $name . '" value="' . $this->csrf . '" />';

        $tmp .= $this->renderErrors();

        return $tmp;
    }
}