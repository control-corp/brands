<?php

namespace Micro\Http;

class Response
{
    protected $body;

    protected $code;

    protected $version;

    protected $headers = [];

    protected $statuses = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Reserved for WebDAV advanced collections expired proposal',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    public function __construct($body = '', $code = 200)
    {
        $this->setBody($body);

        $this->setCode($code);

        $this->setVersion('1.0');
    }

    public function send()
    {
        $this->sendHeaders();
        $this->sendBody();

        if ('cli' !== PHP_SAPI) {
            static::closeOutputBuffers(0, true);
        }

        return $this;
    }

    public function sendHeaders()
    {
        if (headers_sent()) {
            return $this;
        }

        foreach ($this->headers as $name => $values) {
            foreach ($values as $value) {
                header($name . ': ' . $value, \false, $this->code);
            }
        }

        header(sprintf('HTTP/%s %s %s', $this->version, $this->code, $this->getStatus()), true, $this->code);

        return $this;
    }

    public function sendBody()
    {
        echo $this->body;

        return $this;
    }

    public function setBody($value)
    {
        $this->body = $value;

        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setCode($value)
    {
        $this->code = $value;

        return $this;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setVersion($value)
    {
        $this->version = $value;

        return $this;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getStatus()
    {
        if (isset($this->statuses[$this->code])) {
            return $this->statuses[$this->code];
        }

        return 'Unknown';
    }

    public function addHeader($key, $value)
    {
        if (!isset($this->headers[$key])) {
            $this->headers[$key] = [];
        }

        $this->headers[$key][] = $value;

        return $this;
    }

    public function hasHeader($key)
    {
        return isset($this->headers[$key]);
    }

    public function removeHeader($key)
    {
        if ($this->hasHeader($key)) {
            unset($this->headers[$key]);
        }

        return $this;
    }

    public static function closeOutputBuffers($targetLevel, $flush)
    {
        $status = ob_get_status(true);
        $level = count($status);

        // PHP_OUTPUT_HANDLER_* are not defined on HHVM 3.3
        $flags = defined('PHP_OUTPUT_HANDLER_REMOVABLE') ? PHP_OUTPUT_HANDLER_REMOVABLE | ($flush ? PHP_OUTPUT_HANDLER_FLUSHABLE : PHP_OUTPUT_HANDLER_CLEANABLE) : -1;

        while ($level-- > $targetLevel && ($s = $status[$level]) && (!isset($s['del']) ? !isset($s['flags']) || $flags === ($s['flags'] & $flags) : $s['del'])) {
            if ($flush) {
                ob_end_flush();
            } else {
                ob_end_clean();
            }
        }
    }
}