<?php

namespace MiladRahimi\PhpContainer;

use MiladRahimi\PhpContainer\Collections\Prototype;
use MiladRahimi\PhpContainer\Collections\Singleton;
use MiladRahimi\PhpContainer\Exceptions\NotFoundException;
use MiladRahimi\PhpContainer\Exceptions\ContainerException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;

/**
 * Class Container
 *
 * @package MiladRahimi\PhpContainer
 */
class Container implements ContainerInterface
{
    /**
     * Binding repository
     *
     * @var Prototype[]|Singleton[]
     */
    private $repository = [];

    /**
     * Empty the container
     */
    public function empty()
    {
        $this->repository = [];
    }

    /**
     * Bind in prototype mode
     *
     * @param $id
     * @param $concrete
     */
    public function prototype($id, $concrete)
    {
        $this->repository[$id] = new Prototype($concrete);
    }

    /**
     * Bind in singleton mode
     *
     * @param $id
     * @param $concrete
     */
    public function singleton($id, $concrete)
    {
        $this->repository[$id] = new Singleton($concrete);
    }

    /**
     * Check if given abstract is bound or not
     *
     * @param $id
     * @return bool
     */
    public function has($id): bool
    {
        return isset($this->repository[$id]);
    }

    /**
     * Check if class is abstract or not
     *
     * @param string $class
     * @return bool
     * @throws ContainerException
     */
    protected function isAbstract(string $class): bool
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new ContainerException('Cannot create reflection for the class' . $class);
        }

        return $reflection->isAbstract();
    }

    /**
     * Get the right concrete of the abstract
     *
     * @param $id
     * @return mixed
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function get($id)
    {
        if (isset($this->repository[$id]) == false) {
            if (class_exists($id) && $this->isAbstract($id) == false) {
                return $this->instantiate($id);
            }

            throw new NotFoundException($id . ' is not bound.');
        }

        $binding = $this->repository[$id];

        if ($binding instanceof Singleton && $binding->instance) {
            return $binding->instance;
        }

        $concrete = $binding->concrete;

        if (is_string($binding->concrete) && class_exists($binding->concrete)) {
            $concrete = $this->instantiate($binding->concrete);
        } elseif (is_callable($binding->concrete)) {
            $concrete = $this->call($binding->concrete);
        } elseif (is_object($concrete) && $binding instanceof Prototype) {
            return clone $binding->concrete;
        }

        if ($binding instanceof Singleton) {
            $this->repository[$id]->instance = $concrete;
        }

        return $concrete;
    }

    /**
     * Instantiate the concrete class
     *
     * @param string $class
     * @return object
     * @throws ContainerException
     * @throws NotFoundException
     */
    protected function instantiate(string $class)
    {
        try {
            $reflection = new ReflectionClass($class);

            $parameters = [];

            if ($reflection->hasMethod('__construct')) {
                $method = $reflection->getMethod('__construct');

                foreach ($method->getParameters() as $parameter) {
                    if ($parameter->getClass()) {
                        $parameters[] = $this->get($parameter->getClass()->getName());
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
            throw new ContainerException('Reflection error.', 0, $e);
        }
    }

    /**
     * Call the concrete callable
     *
     * @param callable $callable
     * @return object
     * @throws ContainerException
     * @throws NotFoundException
     */
    protected function call(callable $callable)
    {
        try {
            $reflection = new ReflectionFunction($callable);

            $parameters = [];

            foreach ($reflection->getParameters() as $parameter) {
                if ($parameter->getClass()) {
                    $parameters[] = $this->get($parameter->getClass()->getName());
                } else {
                    $defaultValue = $parameter->getDefaultValue();
                    $parameters[] = $defaultValue;
                }
            }

            return call_user_func_array($callable, $parameters);
        } catch (ReflectionException $e) {
            throw new ContainerException('Reflection error.', 0, $e);
        }
    }
}
