<?php

namespace RexTheme\RexShoppingFeed\Containers;

use RexTheme\RexShoppingFeed\Feed;

class RexShopping
{
    /**
     * Feed container
     * @var Feed
     */
    public static $container = null;

    /**
     * Feed namespace
     * @var Feed
     */
    public static $namespace = null;

    /**
     * Feed rss version
     * @var Feed
     */
    public static $version = '';

    /**
     * Feed products wrapper
     * @var Feed
     */
    public static $wrapper = false;

    /**
     * Feed product item wrapper
     * @var Feed
     */
    public static $itemName = 'item';


    public static $rss = 'rss';

    public static $stand_alone = false;

    public static $wrapperel = '';

    public static $namespace_prefix = '';

    /**
     * Return feed container
     * @return Feed
     */
    public static function container()
    {
        if (is_null(static::$container)) {
            static::$container = new Feed( static::$wrapper, static::$itemName, static::$namespace, static::$version , static::$rss, static::$stand_alone, static::$wrapperel, static::$namespace_prefix );
        }

        return static::$container;
    }

    /**
     * Init Feed Configuration
     * @return Feed
     */
    public static function init( $wrapper = false, $itemName = 'item', $namespace = null, $version = '' , $rss = 'rss', $stand_alone = false, $wrapperel = '', $namespace_prefix='')
    {
        static::$namespace = $namespace;
        static::$version   = $version;
        static::$wrapper   = $wrapper;
        static::$itemName  = $itemName;
        static::$rss       = $rss;
        static::$stand_alone = $stand_alone;
        static::$wrapperel = $wrapperel;
        static::$namespace_prefix = $namespace_prefix;

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
