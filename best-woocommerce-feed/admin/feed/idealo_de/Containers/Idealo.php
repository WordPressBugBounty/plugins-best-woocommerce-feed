<?php

namespace RexTheme\RexShoppingFeedCustom\Idealo_de\Containers;

use RexTheme\RexShoppingFeedCustom\Idealo_de\Feed;

class Idealo_de
{

	/**
	 * Feed container
	 *
	 * @var Feed
	 */
	public static $container = null;

    /**
     * Return feed container
     * @return \RexTheme\RexShoppingFeed\Feed
     */
	public static function container() {
		if ( empty( static::$container ) ) {
			static::$container = new Feed();
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
