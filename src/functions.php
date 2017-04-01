<?php

use LiquidSoft\Render\RenderService;
use LiquidSoft\Render\ArgumentService;

if (!function_exists('render')) {
    /**
     * @param string|null $fileQuery
     * @param array $arguments
     * @return RenderService|void
     */
    function render(string $fileQuery = null, array $arguments = [])
    {
        if (count(func_get_args()) === 0) {
            return RenderService::getInstance();
        }

        return RenderService::getInstance()->render($fileQuery, $arguments);
    }
}

if (!function_exists('argument')) {
    /**
     * @param string|null $query
     * @param null $default
     * @return ArgumentService|mixed
     */
    function argument(string $query = null, $default = null)
    {
        if (count(func_get_args()) === 0) {
            return ArgumentService::getInstance();
        }

        return ArgumentService::getInstance()->get($query, $default);
    }
}