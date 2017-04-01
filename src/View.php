<?php

namespace LiquidSoft\Render;

use \Closure;

class View
{

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * View constructor.
     * @param string $filename
     * @param array $arguments
     */
    public function __construct(string $filename, array $arguments = [])
    {
        $this->filename = $filename;
        $this->arguments = $arguments;
    }

    /**
     * @param array $arguments
     */
    public function with(array $arguments)
    {
        $this->arguments = array_merge($this->arguments, $arguments);
    }

    /**
     * Before render hook
     */
    protected function beforeRender()
    {

    }

    /**
     * After render hook
     */
    protected function afterRender()
    {

    }

    /**
     * @throws RenderException
     */
    public function render()
    {
        // beforeRender hook
        $this->beforeRender();

        // Unpack arguments
        extract($this->arguments);

        // Validate view file
        if (!file_exists($this->filename)) {
            throw new RenderException(sprintf('View file `%s` does not exist!', $this->filename));
        }

        include $this->filename;

        // afterRender hook
        $this->afterRender();
    }

    /**
     * Query an argument in the collection
     *
     * @param string $query
     * @param mixed $default
     * @return mixed
     */
    public function getArgument(string $query, $default = null)
    {
        $current = $this->arguments;
        $keys = explode('.', $query);

        do {
            $currentKey = array_shift($keys);

            if (is_array($current) && array_key_exists($current, $currentKey)) {
                $current = $current[$currentKey];
                continue;
            }

            if (is_object($current) && property_exists($current, $currentKey)) {
                $current = $current->$currentKey;
                continue;
            }

            return $default;

        } while (count($keys) > 0);

        return $current;
    }

    /**
     * @param string $query
     * @return bool
     */
    public function hasArgument(string $query)
    {
        $current = $this->arguments;
        $keys = explode('.', $query);

        do {
            $currentKey = array_shift($keys);

            if (is_array($current) && array_key_exists($current, $currentKey)) {
                $current = $current[$currentKey];
                continue;
            }

            if (is_object($current) && property_exists($current, $currentKey)) {
                $current = $current->$currentKey;
                continue;
            }

            return false;

        } while (count($keys) > 0);

        return true;
    }

    /**
     * Set an argument
     *
     * @param string $query
     * @param mixed $value
     */
    public function setArgument(string $query, $value)
    {
        $current = &$this->arguments;
        $keys = explode('.', $query);
        $key = array_pop($keys);

        // Create path
        while (count($keys) > 0) {
            $currentKey = array_shift($keys);

            if (is_array($current)) {
                if (!isset($current[$currentKey]) ||
                    (!is_array($current[$currentKey]) && !is_object($current[$currentKey]))
                ) {
                    $current[$currentKey] = [];
                }

                $current = &$current[$currentKey];
                continue;
            }

            if (is_object($current)) {
                if (!isset($current->$currentKey) ||
                    (!is_array($current->$currentKey) && !is_object($current->$currentKey))
                ) {
                    $current->$currentKey = [];
                }

                $current = &$current->$currentKey;
            }
        }

        // Set value
        if (is_array($current)) {
            $current[$key] = $value;
        } else if (is_object($current)) {
            $current->$currentKey = $value;
        }
    }

}