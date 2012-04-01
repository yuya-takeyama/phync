<?php
class Phync_Event_Event
{
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
}
