<?php

namespace Micro\Http\Response;

use Micro\Http\Response;

class RedirectResponse extends Response
{
    public function __construct($url, $code = 302)
    {
        parent::__construct('', $code);

        $this->setUrl($url);
    }

    public function setUrl($url)
    {
        $this->setBody(
            sprintf('<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="1;url=%1$s" />
        <title>Redirecting to %1$s</title>
    </head>
    <body>
        Redirecting to <a href="%1$s">%1$s</a>.
    </body>
</html>', htmlspecialchars($url, ENT_QUOTES, 'UTF-8')));

        $this->addHeader('Location', $url);

        return $this;
    }
}