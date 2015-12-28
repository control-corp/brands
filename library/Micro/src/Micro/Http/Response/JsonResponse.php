<?php

namespace Micro\Http\Response;

use Micro\Http\Response;

class JsonResponse extends Response
{
    public function setBody($body)
    {
        $this->body = json_encode($body);

        $this->addHeader('Content-Type', 'application/json');

        return $this;
    }
}