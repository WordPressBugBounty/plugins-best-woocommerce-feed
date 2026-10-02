<?php

namespace LukeSnowden\GoogleShoppingFeed;

class Node
{
    /**
     * [$name description]
     * @var string
     */
    protected $name = null;

    /**
     * [$namespace description]
     * @var string
     */
    protected $_namespace = null;

    /**
     * [$value description]
     * @var string
     */
    protected $value = '';

    /**
     * [$cdata description]
     * @var boolean
     */
    protected $cdata = false;

    /**
     * Node constructor.
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Sets Node namespace
     * @param string $value
     * @return $this
     */
    public function _namespace($value)
    {
        $this->_namespace = $value;
        return $this;
    }

    /**
     * @return $this
     */
    public function addCdata()
    {
        $this->cdata = true;
        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function value($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->{$key};
    }

    /**
     * Attaches actual node to a parent node
     * @param \SimpleXMLElement $parent
     */
    public function attachNodeTo(\SimpleXMLElement $parent)
    {
        if( 'sale_price' === $this->name && !$this->value ) {
            return;
        }
        if( is_array( $this->value ) ) {
            return;
        }

        if ( is_string( $this->value ) && 0 === strpos( $this->value, 'CDATA' ) && ! preg_match( '#^CDATA\[#', $this->value ) ) {
            // "CDATA" prefix is the marker added by the "CDATA without space" output filter.
            $this->value = '<![CDATA[' . substr( $this->value, 5 ) . ']]>';
        } elseif ($this->cdata && ! preg_match("#^<!\[CDATA#i", $this->value)) {
            $this->value = "<![CDATA[ {$this->value} ]]>";
        }
        $parent->addChild($this->name, htmlspecialchars($this->value ?? ''), $this->_namespace);
    }
}
