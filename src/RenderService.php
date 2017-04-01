<?php

namespace LiquidSoft\Render;

use LiquidSoft\Render\Support\Singleton;

class RenderService
{
    use Singleton;

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
        $this->viewMap = [];
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

    public function getParentViews()
    {
        return $this->parentViews;
    }

    /**
     * @return array
     */
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
     View mapping
     -------------------------------
     */

    /**
     * @var array
     */
    protected $viewMap;

    /**
     * @param string|array $query
     * @param null $viewClass
     * @return $this
     */
    public function map($query, $viewClass = null)
    {
        if (is_array($query)) {
            foreach ($query as $q => $class) {
                $this->map($q, $class);
            }

            return $this;
        }

        $this->viewMap[$query] = $viewClass;
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

        // Determine class
        $className = View::class;

        if (isset($this->viewMap[$fileQuery])) {
            if (!class_exists($this->viewMap[$fileQuery])) {
                throw new RenderException(sprintf('Class `%s% cannot be found!', $this->viewMap[$fileQuery]));
            }

            $className = $this->viewMap[$fileQuery];
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

}