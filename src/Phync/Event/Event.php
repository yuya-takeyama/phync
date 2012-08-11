<?php
/**
 * This file is part of Phync.
 *
 * (c) Yuya Takeyama
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
