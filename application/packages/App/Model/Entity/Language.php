<?php

namespace App\Model\Entity;

use Micro\Translator\Language\LanguageInterface;

class Language implements LanguageInterface
{
    protected $id;
    protected $code;

    public function __construct($id = \null, $code = \null)
    {
        $this->id = $id;
        $this->code = $code;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCode()
    {
        return $this->code;
    }
}