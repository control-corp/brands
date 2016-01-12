<?php

namespace Micro\Form;

class Form
{
    /**
     * @var array
     */
    protected $elements = array();

    /**
     * @var boolean
     */
    protected $errorsExist = false;

    /**
     * Form constructor
     * @param array|string $options
     * @throws \Exception
     */
    public function __construct($options)
    {
        if (is_string($options) && file_exists($options)) {
            $options = include $options;
            $options = is_array($options) ? $options : array();
        }

        if (!is_array($options)) {
            throw new \Exception('Invalid data in ' . __METHOD__ . ' (' . (is_string($options) ? $options : json_encode($options)) . ')');
        }

        $this->setOptions($options);
    }

    /**
     * @param array $options
     * @return \Micro\Form\Form
     */
    public function setOptions(array $options)
    {
        if (isset($options['elements']) && is_array($options['elements'])) {
            $this->setElements($options['elements']);
        }

        return $this;
    }

    /**
     * @param array $elements
     * @return \Micro\Form\Form
     */
    public function setElements(array $elements)
    {
        foreach ($elements as $name => $config) {
            $this->addElement($name, $config);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param array $config
     * @throws CoreException
     */
    public function addElement($name, array $config = null)
    {
        if ($name instanceof Element) {
            $instance = $name;
        } else if (is_array($config) && isset($config['type'])) {
            $options = isset($config['options']) ? $config['options'] : array();
            if (class_exists($config['type'])) {
                $instance = $config['type'];
            } else {
                $instance = 'Micro\Form\Element\\' . ucfirst($config['type']);
                if (!class_exists($instance)) {
                    throw new \Exception('Form element class "' . $instance . '" does not exists');
                }
            }
            $instance = new $instance($name, $options);
        } else {
            throw new \Exception('The element is not instance of Micro\\Form\\Element');
        }

        $this->elements[$instance->getName()] = $instance;
    }

    /**
     * @param $name
     * @return Form\Element|null
     */
    public function getElement($name)
    {
        return isset($this->elements[$name]) ? $this->elements[$name] : null;
    }

    /**
     * @param $name
     * @return \Micro\Form\Form
     */
    public function removeElement($name)
    {
        if (is_array($name)) {
            foreach ($name as $n) {
                $this->removeElement($n);
            }
            return $this;
        }

        if (isset($this->elements[$name])) {
            unset($this->elements[$name]);
        }

        return $this;
    }

    /**
     * @param array $data
     * @return boolean
     */
    public function isValid(array $data)
    {
        $valid = true;

        foreach ($this->elements as $key => $element) {
            if (!$element instanceof Element) {
                continue;
            }
            if (isset($data[$key])) {
                $valid = $element->isValid($data[$key], $data) && $valid;
            } else {
                $valid = $element->isValid(null, $data) && $valid;
            }
        }

        $this->errorsExist = !$valid;

        return $valid;
    }

    /**
     * @param string $name
     * @return array
     */
    public function getErrors($name = null)
    {
        if (null !== $name) {
            if (isset($this->elements[$name])) {
                return $this->getElement($name)->getErrors();
            }
            return array();
        }

        $errors = array();

        foreach ($this->elements as $key => $element) {
            if ($element instanceof Element) {
                $errors[$key] = $element->getErrors();
            }
        }

        return $errors;
    }

    /**
     * @return boolean
     */
    public function hasErrors()
    {
        return $this->errorsExist;
    }

    /**
     * @param array $values
     */
    public function populate(array $values)
    {
        foreach ($this->elements as $name => $element) {
            if (array_key_exists($name, $values)) {
                $element = $this->elements[$name];
                if ($element instanceof Element) {
                    $element->setValue($values[$name]);
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getValues()
    {
        $values = array();

        foreach ($this->elements as $key => $element) {
            if ($element instanceof Element) {
                $values[$key] = $element->getValue();
            }
        }

        return $values;
    }

    /**
     * @param string $key
     * @return \Micro\Form\Element|null
     */
    public function __get($key)
    {
        if (isset($this->elements[$key])) {
            return $this->elements[$key];
        }

        return null;
    }

    /**
     * @return \Micro\Form\Form
     */
    public function markAsError()
    {
        $this->errorsExist = true;

        return $this;
    }
}