<?php

namespace RexTheme\Rozetka;

/**
 * @desc A class to process live currency exchange rate
 * @class ExchangeRate
 */
class ExchangeRate
{
    /**
     * @desc Gets currency exchange rate depending on a base price
     *
     * @param string $base
     * @return mixed|void|null
     */
    public static function get_exchange_rate( $base = 'USD' ) {
        $req_url = 'https://api.exchangerate-api.com/v4/latest/' . $base;
        $response_json = file_get_contents( $req_url );

        if(false !== $response_json) {
            $response = json_decode($response_json);
            return isset( $response->rates ) ? $response->rates : false;
        }
        return false;
    }
}