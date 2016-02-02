<?php

namespace Micro\Router;

class UserRoute extends Route
{
    /**
     * @param string $requestUri
     * @return boolean
     */
    public function match($requestUri)
    {
        return \false;
    }

    /**
     * Compile route pattern to regex
     * @return string
     */
    public function compile()
    {
        if ($this->compiled === \null) {
            $this->compiled = $this->pattern;
        }

        return $this->compiled;
    }

    /**
     * @param array $data
     * @throws \Exception
     * @return string
     */
    public function assemble(array &$data = [])
    {
        return $this->pattern;
    }
}