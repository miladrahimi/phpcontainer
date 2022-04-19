<?php

namespace MiladRahimi\PhpContainer;

use Closure;
use MiladRahimi\PhpContainer\Exceptions\ContainerException;
use MiladRahimi\PhpContainer\Types\Closure as TClosure;
use MiladRahimi\PhpContainer\Types\Transient;
use MiladRahimi\PhpContainer\Types\Singleton;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;

class Container implements ContainerInterface
{
    /**
     * Binding repository
     *
     * @var Transient[]|Singleton[]
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
     * Bind a transient dependency
     */
    public function transient(string $id, $concrete)
    {
        $this->repository[$id] = new Transient($concrete);
    }

    /**
     * Bind a singleton dependency
     */
    public function singleton(string $id, $concrete)
    {
        $this->repository[$id] = new Singleton($concrete);
    }

    /**
     * Bind a closure dependency
     */
    public function closure($id, Closure $closure)
    {
        $this->repository[$id] = new TClosure($closure);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     */
    public function has(string $id): bool
    {
        return isset($this->repository[$id]);
    }

    /**
     * Check if the given class is abstract or not
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
            throw new ContainerException('Reflection failed for ' . $class, 0, $e);
        }

        return $reflection->isAbstract();
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @throws ContainerException
     */
    public function get(string $id)
    {
        if (!isset($this->repository[$id])) {
            if (class_exists($id) && !$this->isAbstract($id)) {
                return $this->instantiate($id);
            }

            throw new ContainerException("Cannot find $id in the container.");
        }

        $binding = $this->repository[$id];

        if ($binding instanceof Singleton && $instance = $binding->getInstance()) {
            return $instance;
        }

        $concrete = $binding->getConcrete();

        if (is_string($concrete) && class_exists($concrete)) {
            $concrete = $this->instantiate($concrete);
        } elseif (is_callable($concrete) && !($binding instanceof TClosure)) {
            $concrete = $this->call($concrete);
        } elseif (is_object($concrete) && $binding instanceof Transient) {
            return clone $concrete;
        }

        if ($binding instanceof Singleton) {
            $this->repository[$id]->setInstance($concrete);
        }

        return $concrete;
    }

    /**
     * Catch an entry of the container by its identifier without resolving nested dependencies.
     *
     * @throws ContainerException
     */
    public function catch(string $id)
    {
        if (!isset($this->repository[$id])) {
            throw new ContainerException("Cannot find $id in the container.");
        }

        return $this->repository[$id]->getConcrete();
    }

    /**
     * Get rid of the given binding
     */
    public function delete($id): void
    {
        unset($this->repository[$id]);
    }

    /**
     * Instantiate the given class
     *
     * @throws ContainerException
     */
    public function instantiate(string $class)
    {
        try {
            $reflection = new ReflectionClass($class);

            $parameters = [];
            if ($reflection->hasMethod('__construct')) {
                $method = $reflection->getMethod('__construct');
                $parameters = $this->resolveParameters($method->getParameters());
            }

            return count($parameters) == 0 ? new $class : $reflection->newInstanceArgs($parameters);
        } catch (ReflectionException $e) {
            throw new ContainerException('Reflection failed for ' . $class, 0, $e);
        }
    }

    /**
     * Call the concrete callable
     *
     * @throws ContainerException
     */
    public function call($callable)
    {
        try {
            $reflection = is_array($callable)
                ? new ReflectionMethod($callable[0], $callable[1])
                : new ReflectionFunction($callable);

            return call_user_func_array($callable, $this->resolveParameters($reflection->getParameters()));
        } catch (ReflectionException $e) {
            throw new ContainerException('Reflection failed.', 0, $e);
        }
    }

    /**
     * Resolve dependencies of the given function parameters
     *
     * @throws ContainerException
     */
    private function resolveParameters(array $reflectedParameters = []): array
    {
        $parameters = [];

        foreach ($reflectedParameters as $parameter) {
            if (isset($this->repository['$' . $parameter->getName()])) {
                $parameters[] = $this->catch('$' . $parameter->getName());
            } elseif (
                $parameter->getType() &&
                (
                    class_exists($parameter->getType()->getName()) ||
                    interface_exists($parameter->getType()->getName())
                )
            ) {
                $parameters[] = $this->get($parameter->getType()->getName());
            } else {
                $defaultValue = $parameter->getDefaultValue();
                $parameters[] = $defaultValue;
            }
        }

        return $parameters;
    }
}
