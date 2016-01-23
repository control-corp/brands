<?php

namespace Micro\Form\Element;

use Micro\Application\Utils;
use Micro\Form\Element;
use Micro\Session\SessionNamespace;
use Micro\Validate;

class Csrf extends Element
{
    protected $csrf;

    public function __construct($name, array $options)
    {
        parent::__construct($name, $options);

        $session = new SessionNamespace($name . '_form_csrf');

        $this->setRequired(\true);

        $this->addValidator(new Validate\Identical([
            'value' => (isset($session->value) ? $session->value : \null),
            'error' => 'Формата е невалидна'
        ]));

        $this->csrf = $session->value = md5(Utils::randomSentence(10) . time());
    }

    public function render()
    {
        $tmp = '';

        $name = $this->getFullyName();

        $tmp .= '<input type="hidden" name="' . $name . '" value="' . $this->csrf . '" />';

        if ($this->showErrors === \true) {
            $tmp .= $this->renderErrors();
        }

        return $tmp;
    }
}