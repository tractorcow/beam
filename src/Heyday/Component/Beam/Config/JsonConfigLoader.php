<?php

namespace Heyday\Component\Beam\Config;

use Symfony\Component\Config\Loader\FileLoader;

class JsonConfigLoader extends FileLoader
{
    /**
     * @var array
     */
    protected $locateCache = array();
    /**
     * Loads a resource.
     *
     * @param  mixed  $resource The resource
     * @param  string $type     The resource type
     * @return array
     */
    public function load($resource, $type = null)
    {
        return json_decode(
            file_get_contents(
                $this->locate(
                    $resource
                )
            ),
            true
        );
    }
    /**
     * @param $resource
     * @return array|string
     */
    public function locate($resource)
    {
        if (!isset($this->locateCache[$resource])) {
            $this->locateCache[$resource] = $this->locator->locate(
                $resource
            );
        }

        return $this->locateCache[$resource];
    }
    /**
     * Returns true if this class supports the given resource.
     *
     * @param mixed  $resource A resource
     * @param string $type     The resource type
     *
     * @return Boolean true if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'json' === pathinfo(
            $resource,
            PATHINFO_EXTENSION
        );
    }
}