<?php

use LiquidSoft\Render\RenderService;

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
     * @param string $query
     * @param mixed $default
     * @return mixed
     */
    function argument(string $query, $default = null)
    {
        return RenderService::getInstance()->argument($query, $default);
    }
}