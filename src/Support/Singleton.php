<?php

namespace LiquidSoft\Render\Support;

trait Singleton
{
    /**
     * @var static
     */
    protected static $instance;

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new RenderService();
        }

        return static::$instance;
    }
}