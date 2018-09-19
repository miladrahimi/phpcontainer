<?php

namespace MiladRahimi\PhpContainer;

use MiladRahimi\PhpContainer\Collections\Prototype;
use MiladRahimi\PhpContainer\Collections\Singleton;
use MiladRahimi\PhpContainer\Exceptions\BindingNotFoundException;
use MiladRahimi\PhpContainer\Exceptions\ResolutionException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;

class Container
{
    /**
     * Service repository
     *
     * @var Prototype[]|Singleton[]
     */
    public static $repository = [];

    /**
     * Reset the repository
     */
    public static function reset()
    {
        static::$repository = [];
    }

    /**
     * Bind in prototype mode
     *
     * @param string $abstract
     * @param $concrete
     */
    public static function prototype(string $abstract, $concrete)
    {
        static::$repository[$abstract] = new Prototype($concrete);
    }

    /**
     * Bind in singleton mode
     *
     * @param string $abstract
     * @param $concrete
     */
    public static function singleton(string $abstract, $concrete)
    {
        static::$repository[$abstract] = new Singleton($concrete);
    }

    /**
     * Check if given abstract is bound or not
     *
     * @param string $abstract
     * @return bool
     */
    public static function isBound(string $abstract): bool
    {
        return isset(static::$repository[$abstract]);
    }

    /**
     * Check if given abstract or concrete is resolvable or not
     *
     * @param string $abstract
     * @return bool
     * @throws ResolutionException
     */
    public static function isResolvable(string $abstract): bool
    {
        return static::isBound($abstract) || (class_exists($abstract) && static::isAbstract($abstract) == false);
    }

    /**
     * Check if class is abstract or not
     *
     * @param string $class
     * @return bool
     * @throws ResolutionException
     */
    protected static function isAbstract(string $class): bool
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new ResolutionException('Cannot create reflection for ' . $class);
        }

        return $reflection->isAbstract();
    }

    /**
     * Make right concrete of the abstract
     *
     * @param string $abstract
     * @return mixed
     * @throws BindingNotFoundException
     * @throws ResolutionException
     */
    public static function make(string $abstract)
    {
        if (isset(static::$repository[$abstract]) == false) {
            if (class_exists($abstract) && static::isAbstract($abstract) == false) {
                return static::instantiate($abstract);
            }

            throw new BindingNotFoundException($abstract . ' is not bound.');
        }

        $binding = static::$repository[$abstract];

        if ($binding instanceof Singleton && $binding->instance) {
            return $binding->instance;
        }

        $concrete = $binding->concrete;

        if (is_string($binding->concrete) && class_exists($binding->concrete)) {
            $concrete = static::instantiate($binding->concrete);
        } elseif (is_callable($binding->concrete)) {
            $concrete = static::call($binding->concrete);
        } elseif (is_object($concrete) && $binding instanceof Prototype) {
            return clone $binding->concrete;
        }

        if ($binding instanceof Singleton) {
            static::$repository[$abstract]->instance = $concrete;
        }

        return $concrete;
    }

    /**
     * Instantiate the concrete class
     *
     * @param string $class
     * @return object
     * @throws ResolutionException
     * @throws BindingNotFoundException
     */
    protected static function instantiate(string $class)
    {
        try {
            $reflection = new ReflectionClass($class);

            $parameters = [];

            if ($reflection->hasMethod('__construct')) {
                $method = $reflection->getMethod('__construct');

                foreach ($method->getParameters() as $parameter) {
                    if ($parameter->getClass()) {
                        $parameters[] = static::make($parameter->getClass()->getName());
                    } else {
                        $defaultValue = $parameter->getDefaultValue();
                        $parameters[] = $defaultValue;
                    }
                }
            }

            if (count($parameters) == 0) {
                return new $class;
            } else {
                return $reflection->newInstanceArgs($parameters);
            }
        } catch (ReflectionException $e) {
            throw new ResolutionException('Reflection error.', 0, $e);
        }
    }

    /**
     * Call the concrete callable
     *
     * @param callable $callable
     * @return object
     * @throws ResolutionException
     * @throws BindingNotFoundException
     */
    protected static function call(callable $callable)
    {
        try {
            $reflection = new ReflectionFunction($callable);

            $parameters = [];

            foreach ($reflection->getParameters() as $parameter) {
                if ($parameter->getClass()) {
                    $parameters[] = static::make($parameter->getClass()->getName());
                } else {
                    $defaultValue = $parameter->getDefaultValue();
                    $parameters[] = $defaultValue;
                }
            }

            return call_user_func_array($callable, $parameters);
        } catch (ReflectionException $e) {
            throw new ResolutionException('Reflection error.', 0, $e);
        }
    }
}
