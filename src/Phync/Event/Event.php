<?php
class Phync_Event_Event
{
    private $name;

    private $params;

    public function __construct($params = array())
    {
        $this->params = $params;
    }

    public function __get($prop)
    {
        if (array_key_exists($prop, $this->params)) {
            return $this->params[$prop];
        } else {
            throw new RuntimeException("Undefined property: " . get_class($this) . "::\${$prop}");
        }
    }

    public function __set($prop, $value)
    {
        throw new RuntimeException(get_class($this) . ' is immutable.');
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
