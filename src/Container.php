<?php

namespace MiladRahimi\PhpContainer;

use Closure;
use MiladRahimi\PhpContainer\Bindings\Closure as BClosure;
use MiladRahimi\PhpContainer\Bindings\Transient;
use MiladRahimi\PhpContainer\Bindings\Singleton;
use MiladRahimi\PhpContainer\Exceptions\ContainerException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;

class Container implements ContainerInterface
{
    /**
     * Binding repository
     *
     * @var Transient[]|Singleton[]|BClosure[]
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
     *
     * @param $id
     * @param $concrete
     */
    public function transient($id, $concrete)
    {
        $this->repository[$id] = new Transient($concrete);
    }

    /**
     * Bind a singleton dependency
     *
     * @param $id
     * @param $concrete
     */
    public function singleton($id, $concrete)
    {
        $this->repository[$id] = new Singleton($concrete);
    }

    /**
     * Bind a closure dependency
     *
     * @param $id
     * @param Closure $closure
     */
    public function closure($id, Closure $closure)
    {
        $this->repository[$id] = new BClosure($closure);
    }

    /**
     * Check if the given abstract exist in the container or not
     *
     * @param $id
     * @return bool
     */
    public function has($id): bool
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
            throw new ContainerException('Reflection failed for ' . get_class($class), 0, $e);
        }

        return $reflection->isAbstract();
    }

    /**
     * Get the right concrete for the given abstract
     *
     * @param $id
     * @return mixed
     * @throws ContainerException
     */
    public function get($id)
    {
        if (isset($this->repository[$id]) == false) {
            if (class_exists($id) && $this->isAbstract($id) == false) {
                return $this->instantiate($id);
            }

            throw new ContainerException($id . ' is not bound.');
        }

        $binding = $this->repository[$id];

        if ($binding instanceof Singleton && $instance = $binding->getInstance()) {
            return $instance;
        }

        $concrete = $binding->getConcrete();

        if (is_string($concrete) && class_exists($concrete)) {
            $concrete = $this->instantiate($concrete);
        } elseif (is_callable($concrete) && !($binding instanceof BClosure)) {
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
     * Get ride of the given binding
     *
     * @param $id
     */
    public function delete($id)
    {
        unset($this->repository[$id]);
    }

    /**
     * Instantiate the concrete class
     *
     * @param string $class
     * @return object
     * @throws ContainerException
     */
    public function instantiate(string $class)
    {
        try {
            $reflection = new ReflectionClass($class);

            $parameters = [];
            if ($reflection->hasMethod('__construct')) {
                $method = $reflection->getMethod('__construct');
                $parameters = $this->arrangeParameters($method->getParameters());
            }

            if (count($parameters) == 0) {
                return new $class;
            } else {
                return $reflection->newInstanceArgs($parameters);
            }
        } catch (ReflectionException $e) {
            throw new ContainerException('Reflection failed for ' . $class, 0, $e);
        }
    }

    /**
     * Call the concrete callable
     *
     * @param callable|array $callable
     * @return mixed
     * @throws ContainerException
     */
    public function call($callable)
    {
        try {
            $reflection = is_array($callable)
                ? new ReflectionMethod($callable[0], $callable[1])
                : new ReflectionFunction($callable);

            return call_user_func_array($callable, $this->arrangeParameters($reflection->getParameters()));
        } catch (ReflectionException $e) {
            throw new ContainerException('Reflection failed.', 0, $e);
        }
    }

    /**
     * Retrieve and arrange method/function parameters (dependencies)
     *
     * @param ReflectionParameter[] $reflectedParameters
     * @return array
     * @throws ContainerException
     * @throws ReflectionException
     */
    private function arrangeParameters(array $reflectedParameters = []): array
    {
        $parameters = [];

        foreach ($reflectedParameters as $parameter) {
            if (isset($this->repository['$' . $parameter->getName()])) {
                $parameters[] = $this->get('$' . $parameter->getName());
            } elseif ($parameter->getClass()) {
                $parameters[] = $this->get($parameter->getClass()->getName());
            } else {
                $defaultValue = $parameter->getDefaultValue();
                $parameters[] = $defaultValue;
            }
        }

        return $parameters;
    }
}
