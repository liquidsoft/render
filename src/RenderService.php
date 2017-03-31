<?php

namespace LiquidSoft\Render;

class RenderService
{

    /**
     * @var array
     */
    protected $options;

    /**
     * @var View
     */
    protected $currentView;

    /**
     * @var array
     */
    protected $parentViews;

    /**
     * ViewService constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
        $this->parentViews = [];
    }

    /*
     -------------------------------
     Accessors
     -------------------------------
     */


    /**
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        // Call option accessor if it exists
        $getter = 'get' . ucfirst($key);
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }

        // Return option
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }
    }

    /**
     * @return View
     */
    public function getCurrentView()
    {
        return $this->currentView;
    }

    public function getOptions()
    {
        return $this->options;
    }

    /*
     -------------------------------
     Mutators
     -------------------------------
     */

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function __set($key, $value)
    {
        // Call option mutator if it exists
        $setter = 'set' . ucfirst($key);
        if (method_exists($this, $setter)) {
            return $this->$setter($value);
        }

        // Set option
        $this->options[$key] = $value;
    }

    public function setOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }

    /*
     -------------------------------
     Accessor/Mutators support helper
     -------------------------------
     */

    public function __call($name, $arguments)
    {
        if (count($arguments) === 0) {
            return $this->$name;
        }

        $this->$name = $arguments[0];
        return $this;
    }

    /*
     -------------------------------
     Methods
     -------------------------------
     */


    /**
     * @param string $fileQuery
     * @param array $arguments
     * @return View
     * @throws RenderException
     */
    protected function view(string $fileQuery, array $arguments = [])
    {
        // Validate source
        $source = $this->source;
        if (!is_string($source)) {
            throw new RenderException('View source is not set!');
        }

        // Determine path
        $filename = implode(DIRECTORY_SEPARATOR, explode('.', $fileQuery)) . '.php';
        $path = realpath($source . DIRECTORY_SEPARATOR . $filename);

        // Validate path
        if (strpos($path, $source) !== 0) {
            throw new RenderException('View filename cannot be outside the source folder!');
        }

        // Validate namespace
        $namespace = $this->namespace;

        if (!is_string($namespace)) {
            throw new RenderException('View namespace is not set!');
        }

        // Get view class
        $filename = substr($path, strlen($source));
        $className = $namespace . '\\' . implode('\\', explode(DIRECTORY_SEPARATOR, basename($filename, '.php'))) . 'View';

        if (!class_exists($className)) {
            $className = View::class;
        }

        // Create view
        return new $className($path, $arguments);
    }

    /**
     * @param string $fileQuery
     * @param array $arguments
     */
    public function render(string $fileQuery, array $arguments = [])
    {
        // Create view
        $view = $this->view($fileQuery, $arguments);

        // Push current views into parents
        if (isset($this->currentView)) {
            $this->parentViews[] = $this->currentView;
        }

        // Set current view
        $this->currentView = $view;

        // Render
        $this->currentView->render();

        // Revert to parent view
        $this->currentView = count($this->parentViews) > 0 ? array_pop($this->parentViews) : null;
    }

    /**
     * Fetch an argument from the view scope
     *
     * @param string $query
     * @param mixed $default
     * @return mixed
     */
    public function argument(string $query, $default = null)
    {
        if (!isset($this->currentView)) {
            return $default;
        }

        // Parse view hierarchy and look for query
        $hierarchy = array_reverse(array_merge($this->parentViews, [$this->currentView]));

        foreach ($hierarchy as $view) {
            if ($view->hasArgument($query)) {
                return $view->getArgument($query);
            }
        }

        return $default;
    }

    /*
     -------------------------------
     Singleton
     -------------------------------
     */

    /**
     * @var RenderService
     */
    protected static $instance;

    /**
     * @return RenderService
     */
    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new RenderService();
        }

        return static::$instance;
    }

}