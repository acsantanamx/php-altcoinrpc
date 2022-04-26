<?php

declare(strict_types=1);

namespace DOne\Altcoin;

use DOne\Altcoin\Exceptions\BadConfigurationException;
use DOne\Altcoin\Exceptions\Handler as ExceptionHandler;

if (!function_exists('to_altcoin')) {
    /**
     * Converts from satoshi to altcoin.
     *
     * @param int $satoshi
     *
     * @return string
     */
    function to_altcoin(int $satoshi): string
    {
        return bcdiv((string) $satoshi, (string) 1e8, 8);
    }
}

if (!function_exists('to_satoshi')) {
    /**
     * Converts from altcoin to satoshi.
     *
     * @param string|float $altcoin
     *
     * @return string
     */
    function to_satoshi($altcoin): string
    {
        return bcmul(to_fixed($altcoin, 8), (string) 1e8);
    }
}

if (!function_exists('to_ubtc')) {
    /**
     * Converts from altcoin to ubtc/bits.
     *
     * @param string|float $altcoin
     *
     * @return string
     */
    function to_ubtc($altcoin): string
    {
        return bcmul(to_fixed($altcoin, 8), (string) 1e6, 4);
    }
}

if (!function_exists('to_mbtc')) {
    /**
     * Converts from altcoin to mbtc.
     *
     * @param string|float $altcoin
     *
     * @return string
     */
    function to_mbtc($altcoin): string
    {
        return bcmul(to_fixed($altcoin, 8), (string) 1e3, 4);
    }
}

if (!function_exists('to_fixed')) {
    /**
     * Brings number to fixed precision without rounding.
     *
     * @param string $number
     * @param int    $precision
     *
     * @return string
     */
    function to_fixed(string $number, int $precision = 8): string
    {
        $number = bcmul($number, (string) pow(10, $precision));

        return bcdiv($number, (string) pow(10, $precision), $precision);
    }
}

if (!function_exists('split_url')) {
    /**
     * Splits url into parts.
     *
     * @param string $url
     *
     * @return array
     */
    function split_url(string $url): array
    {
        $allowed = ['scheme', 'host', 'port', 'user', 'pass'];

        $parts = (array) parse_url($url);
        $parts = array_intersect_key($parts, array_flip($allowed));

        if (!$parts || empty($parts)) {
            throw new BadConfigurationException(
                ['url' => $url],
                'Invalid url'
            );
        }

        return $parts;
    }
}

if (!function_exists('exception')) {
    /**
     * Gets exception handler instance.
     *
     * @return \DOne\Altcoin\Exceptions\Handler
     */
    function exception(): ExceptionHandler
    {
        return ExceptionHandler::getInstance();
    }
}

set_exception_handler([ExceptionHandler::getInstance(), 'handle']);
