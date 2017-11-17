<?php

namespace Axn\ModelsGenerator\Traits;

trait HasStub
{
    /**
     * Cache des contenus des stubs.
     *
     * @var array[string]
     */
    protected static $stubs = [];

    /**
     * Retourne le contenu d'un stub.
     *
     * @param  string $name
     * @return string
     */
    protected function getStubContent($name)
    {
        if (!isset(static::$stubs[$name])) {
            if (!is_file($path = base_path("resources/stubs/vendor/models-generator/$name.stub"))) {
                $path = __DIR__."/../../resources/stubs/$name.stub";
            }

            static::$stubs[$name] = file_get_contents($path);
        }

        return static::$stubs[$name];
    }
}
