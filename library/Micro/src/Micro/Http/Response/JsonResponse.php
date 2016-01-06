<?php

namespace Micro\Http\Response;

use Micro\Http\Response;

class JsonResponse extends Response
{
    public function send()
    {
        $this->removeHeader('Content-Type');

        $this->addHeader('Content-Type', 'application/json');

        $this->setBody(json_encode($this->body));

        return parent::send();
    }
}