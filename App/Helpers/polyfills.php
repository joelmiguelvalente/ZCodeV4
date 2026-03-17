<?php

/**
 * @name polyfills.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

/**
 * safe_count
 */

if (!function_exists('safe_count')) {
    function safe_count($data, $mode = COUNT_NORMAL)
    {
        return is_countable($data) ? count($data, $mode) : 0;
    }
}

/**
 * safe_unserialize
 */
if (!function_exists('safe_unserialize')) {
    function safe_unserialize($data)
    {
        if (!is_string($data) || trim($data) === '') {
            return [];
        }
        $result = @unserialize($data, ['allowed_classes' => false]);
        if ($result === false && $data !== 'b:0;') {
            return [];
        }
        return $result;
    }
}
