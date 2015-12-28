<?php

namespace Micro\Http\Response;

use Micro\Http\Response;

class HtmlResponse extends Response
{
    public function send()
    {
        if (\false === $this->hasHeader('Content-Type')) {
            $this->addHeader('Content-Type', 'text/html; charset=utf8');
        }

        return parent::send();
    }
}