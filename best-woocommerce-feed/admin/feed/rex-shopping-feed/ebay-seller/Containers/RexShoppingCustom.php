<?php

namespace RexTheme\RexShoppingFeedCustom\EbaySeller\Containers;

use RexTheme\RexShoppingFeed\Containers\RexShopping;
use RexTheme\RexShoppingFeedCustom\EbaySellerFeed\EbaySellerFeed;

class RexShoppingCustom extends RexShopping
{

    /**
     * Return feed container
     * @return Feed
     */
    public static function container()
    {
        if (is_null(static::$container)) {
            static::$container = new EbaySellerFeed( static::$wrapper, static::$itemName, static::$namespace, static::$version , static::$rss );
        }

        return static::$container;
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
