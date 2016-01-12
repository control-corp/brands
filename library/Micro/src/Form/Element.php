<?php

namespace Micro\Form;

use Micro\Validate\ValidateInterface;
use Micro\Application\View;

class Element
{
    protected $name;
    protected $value;
    protected $label;
    protected $class;

    protected $view;

    protected $required = false;
    protected $validators = array();
    protected $errors = array();
    protected $attributes = array();
    protected $isArray = false;
    protected $translate = false;

    protected $belongsTo;

    /**
     * @param string $name
     * @param array $options
     */
    public function __construct($name, array $options = null)
    {
        if ($options === null) {
            $options = array();
        }

        $this->name = $name;

        if (!empty($options)) {
            $this->setOptions($options);
        }

        $this->view = new View($name);
    }

    public function setOptions(array $options)
    {
        foreach ($options as $k => $v) {
            $method = 'set' . ucfirst($k);
            if (method_exists($this, $method)) {
                $this->$method($v);
            }
        }

        return $this;
    }

    public function setIsArray($flag = true)
    {
        $this->isArray = (bool) $flag;

        return $this;
    }

    public function setClass($value)
    {
        $this->attributes['class'] = $value;

        return $this;
    }

    public function setName($value)
    {
        $this->name = $value;

        return $this;
    }

    public function setTranslate($value)
    {
        $this->translate = $value;

        return $this;
    }

    public function setBelongsTo($value)
    {
        $this->belongsTo = $value;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFullyName()
    {
        return ($this->belongsTo ? $this->belongsTo . '[' . $this->name . ']' : $this->name);
    }

    public function setLabel($value)
    {
        $this->label = $value;

        return $this;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setRequired($flag = true)
    {
        $this->required = (bool) $flag;

        return $this;
    }

    public function isRequired()
    {
        return $this->required;
    }

    public function setAttributes(array $attributes)
    {
        $this->addAttributes($attributes);

        return $this;
    }

    public function clearAttributes()
    {
        $this->attributes = array();

        return $this;
    }

    public function addAttributes(array $attributes)
    {
        foreach ($attributes as $k => $v) {
            $this->setAttribute($k, $v);
        }

        return $this;
    }

    public function setAttribute($k, $v)
    {
        $this->attributes[$k] = $v;

        return $this;
    }

    public function getAttribute($k)
    {
        return isset($this->attributes[$k]) ? $this->attributes[$k] : null;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function setValidators(array $validators)
    {
        $this->clearValidators();

        $this->addValidators($validators);

        return $this;
    }

    public function clearValidators()
    {
        $this->validators = array();

        return $this;
    }

    public function addValidators(array $validators)
    {
        foreach ($validators as $validatorInfo) {
            if (is_string($validatorInfo)) {
                $this->addValidator($validatorInfo);
            } elseif ($validatorInfo instanceof ValidateInterface) {
                $this->addValidator($validatorInfo);
            } elseif (is_array($validatorInfo)) {
                $options = array();
                if (isset($validatorInfo['validator'])) {
                    $validator = $validatorInfo['validator'];
                    if (isset($validatorInfo['options'])) {
                        $options = $validatorInfo['options'];
                    }
                    $this->addValidator($validator, $options);
                } else {
                    throw new \Exception('Invalid validator passed to addValidators()');
                }
            }
        }

        return $this;
    }

    public function addValidator($validator, array $options = null)
    {
        if ($options === null) {
            $options = array();
        }

        if ($validator instanceof ValidateInterface) {
            $name = get_class($validator);
        } else if (is_string($validator)) {
            $name = $validator;
            $validator = array(
                'validator' => $validator,
                'options'   => $options,
            );
        } else {
            throw new \Exception('Invalid validator provided to addValidator; must be string or Micro\Validate\ValidateInterface');
        }

        $this->validators[$name] = $validator;

        return $this;
    }

    public function getValidators()
    {
        $validators = array();

        foreach ($this->validators as $key => $value) {
            if ($value instanceof ValidateInterface) {
                $validators[$key] = $value;
                continue;
            }
            $validator = $this->loadValidator($value);
            $validators[get_class($validator)] = $validator;
        }

        return $validators;
    }

    protected function loadValidator($validator)
    {
        $origName = $validator['validator'];

        if (class_exists($origName)) {
            $name = $origName;
        } else {
            $name = 'Micro\Validate\\' . ucfirst($origName);
            if (!class_exists($name)) {
                throw new \Exception('Class "' . $name . '" does not exists');
            }
        }

        $instance = new $name($validator['options']);

        if ($origName != $name) {
            $validatorNames     = array_keys($this->validators);
            $order              = array_flip($validatorNames);
            $order[$name]       = $order[$origName];
            $validatorsExchange = array();
            unset($order[$origName]);
            asort($order);
            foreach ($order as $key => $index) {
                if ($key == $name) {
                    $validatorsExchange[$key] = $instance;
                    continue;
                }
                $validatorsExchange[$key] = $this->validators[$key];
            }
            $this->validators = $validatorsExchange;
        } else {
            $this->validators[$name] = $instance;
        }

        return $instance;
    }

    public function isValid($value, array $context = null)
    {
        $this->setValue($value);

        $value = $this->getValue();

        if ((('' === $value) || (null === $value)) && !$this->isRequired()) {
            return true;
        }

        if ($this->isRequired()) {
            $validators = $this->getValidators();
            array_unshift($validators, 'NotEmpty');
            $this->setValidators($validators);
        }

        foreach ($this->getValidators() as $key => $validator) {
            if (!$validator->isValid($value, $context)) {
                $this->errors = array_merge($this->errors, $validator->getMessages());
                return false;
            }
        }

        if (!empty($this->errors)) {
            return false;
        }

        return true;
    }

    public function addError($message)
    {
        $this->errors[] = $message;

        return $this;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return !empty($this->errors);
    }

    public function render()
    {
        return '';
    }

    public function __toString()
    {
        try {
            return $this->render();
        } catch (\Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            return '';
        }
    }

    protected function htmlAttributes()
    {
        $xhtml = '';

        foreach ($this->attributes as $key => $val) {

            if (is_array($val)) {
                $val = implode(' ', $val);
            }

            if (is_numeric($key)) {
                $xhtml .= " $val";
                continue;
            }

            if (is_array($val)) {
                $val = implode(' ', $val);
            }

            if (strpos($val, '"') !== false) {
                $xhtml .= " $key='$val'";
            } else {
                $xhtml .= " $key=\"$val\"";
            }

        }

        return $xhtml;
    }

    public function renderLabel()
    {
        if ($this->getLabel()) {
            return '<label class="control-label' . ($this->isRequired() ? ' required' : '') . '">' . $this->getLabel() . ($this->isRequired() ? ' <span class="asterisk">*</span>' : '') . '</label>';
        }

        return '';
    }

    public function renderErrors()
    {
        $tmp = '';

        foreach ($this->errors as $error) {
            $tmp .= '<span class="errors">' . $error . '</span>';
        }

        return $tmp;
    }

    public function translateData($data)
    {
        if ($this->translate === false) {
            return $data;
        }

        /**
         * @todo Translate
         */
        return $data;
    }
}