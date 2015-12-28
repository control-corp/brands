<?php

namespace Micro\Application;

class View
{
    protected $template;
    protected $parent;
    protected $__currentSection;
    protected $data = [];
    protected $paths = [];
    protected $sections = [];
    protected $cloned = \false;
    protected static $helpers = [];

    public function __construct($template, array $data = \null, $injectPaths = \false)
    {
        $this->template = $template;
        $this->data = $data ?: [];

        if ($injectPaths) {
            $this->injectPaths();
        }
    }

    public function addPath($path)
    {
        if (is_array($path)) {
            foreach ($path as $p) {
                $this->addPath($p);
            }
            return $this;
        }

        $path = rtrim($path, '/\\');

        $this->paths[$path] = $path;

        return $this;
    }

    public function render($template = \null)
    {
        $renderParent = \true;

        if ($template !== \null) {
            $file = $template;
            $renderParent = \false;
        } else {
            if (empty($this->template)) {
                throw new \Exception('Template is empty', 500);
            }
            $file = str_replace(array('@', '::'), '/', $this->template);
        }

        $file .= '.phtml';

        foreach ($this->paths as $path) {
            if (file_exists($path . '/' . $file)) {
                $content = $this->evalFile($path . '/' . $file);
                if ($renderParent === \true && $this->parent !== \null) {
                    $this->parent->setSections(array_merge(
                        $this->getSections(),
                        array('content' => $content)
                    ));
                    $content = $this->parent->render();
                }
                return $content;
            }
        }

        return 'Template "' . $file . '" not found in ' . implode(', ', $this->paths);
    }

    public function evalFile($__path)
    {
        $__obLevel = ob_get_level();

        ob_start();

        extract($this->data);

        try {
            include $__path;
        } catch (\Exception $e) {
            while (ob_get_level() > $__obLevel) {
                ob_end_clean();
            }
            if (env('development')) {
                echo $e->getMessage();
            }
        }

        return ob_get_clean();
    }

    public function __toString()
    {
        try {
            return (string) $this->render();
        } catch (\Exception $e) {
            return '';
        }
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function __get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : \null;
    }

    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function partial($template, array $data = [])
    {
        $view = clone $this;

        if ($template === $view->getTemplate()) {
            throw new \Exception('Recursion detected', 500);
        }

        $view->setData($data);

        $view->setTemplate($template);

        $view->setParent(\null);

        return $view->render();
    }

    public function extend($template, array $data = [])
    {
        $view = clone $this;

        $view->setData($data);

        $view->setTemplate($template);

        $this->setParent($view);

        return $this;
    }

    public function setParent(View $parent = \null)
    {
        $this->parent = $parent;

        return $this;
    }

    public function start($section)
    {
        if ($this->__currentSection !== \null) {
            throw new \Exception('There is current started section', 500);
        }

        $this->__currentSection = $section;

        ob_start();
    }

    public function stop()
    {
        if ($this->__currentSection === \null) {
            throw new \Exception('There is not current started section', 500);
        }

        $section = $this->__currentSection;

        $this->__currentSection = \null;

        return $this->section($section, ob_get_clean());
    }

    public function section($section, $content)
    {
        if (!isset($this->sections[$section])) {
            $this->sections[$section] = [];
        }

        $this->sections[$section][] = $content;

        return $this;
    }

    public function renderSection($section, $default = \null)
    {
        if (!isset($this->sections[$section])) {
            return $default;
        }

        return implode("\n", (array) $this->sections[$section]);
    }

    public function setSections(array $sections)
    {
        $this->sections = $sections;

        return $this;
    }

    public function getSections()
    {
        return $this->sections;
    }

    protected function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    public function __call($method, $params)
    {
        $method = ucfirst($method);

        if (!isset(static::$helpers[$method])) {

            $search   = [];
            $search[] = $helper = 'Micro\\View\\' . $method;

            if (class_exists($helper)) {
                static::$helpers[$method] = $helper = new $helper();
            } else {
                foreach (app()->getPackages() as $package) {
                    $search[] = $helper = $package->getName() . '\\View\\' . ucfirst($method);
                    if (class_exists($helper)) {
                        static::$helpers[$method] = $helper = new $helper();
                        break;
                    }
                }
            }

            if (!isset(static::$helpers[$method])) {
                throw new \Exception('Invalid view helper: [' . implode('], [', $search) . ']', 500);
            }
        }

        return call_user_func_array(static::$helpers[$method], $params);
    }

    public function __clone()
    {
        $this->cloned = \true;
    }

    public function isCloned()
    {
        return $this->cloned;
    }

    public function injectPaths()
    {
        $this->addPath(config('view.paths', []));
    }
}