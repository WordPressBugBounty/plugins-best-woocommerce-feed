<?php

namespace RexTheme\Hotline\Containers;

use RexTheme\Hotline\Feed;

class Hotline
{
    /**
     * Feed container
     * @var Feed
     */
    public static $container = null;

    /**
     * Feed elements
     *
     * @var array
     * @since 7.3.2
     */
    public static $elements;


    /**
     * Return feed container
     * @return Feed
     */
    public static function container()
    {
        if (is_null(static::$container)) {
            static::$container = new Feed( static::$elements );
        }

        return static::$container;
    }

    /**
     * Init Feed Configuration
     */
    public static function init( $elements )
    {
        static::$elements = $elements;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array(array(static::container(), $name), $arguments);
    }
}
