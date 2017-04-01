<?php

namespace LiquidSoft\Render;

use LiquidSoft\Render\Support\Singleton;

class ArgumentService
{
    use Singleton;

    /**
     * @var RenderService
     */
    protected $render;

    public function __construct()
    {
        $this->render = RenderService::getInstance();
    }

    /**
     * Fetch an argument from the current scope
     *
     * @param string $query
     * @param mixed $default
     * @return mixed
     */
    public function get(string $query, $default = null)
    {
        $currentView = $this->render->getCurrentView();
        $parentViews = $this->render->getParentViews();

        if (!isset($currentView)) {
            return $default;
        }

        // Parse view hierarchy and look for query
        $hierarchy = array_reverse(array_merge($parentViews, [$currentView]));

        foreach ($hierarchy as $view) {
            if ($view->hasArgument($query)) {
                return $view->getArgument($query);
            }
        }

        return $default;
    }

    /**
     * Check if an argument exists in the current scope
     *
     * @param string $query
     * @return bool
     */
    public function has(string $query)
    {
        $currentView = $this->render->getCurrentView();
        $parentViews = $this->render->getParentViews();

        if (!isset($currentView)) {
            return false;
        }

        // Parse view hierarchy and look for query
        $hierarchy = array_reverse(array_merge($parentViews, [$currentView]));

        foreach ($hierarchy as $view) {
            if ($view->hasArgument($query)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set an argument in the current view
     *
     * @param string $query
     * @param $value
     */
    public function set(string $query, $value)
    {
        $currentView = $this->render->getCurrentView();

        if (!isset($currentView)) {
            return;
        }

        return $currentView->setArgument($query, $value);
    }

}