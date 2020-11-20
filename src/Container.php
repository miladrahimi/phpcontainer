<?php

namespace MiladRahimi\PhpContainer;

use Closure;
use MiladRahimi\PhpContainer\Types\Transient;
use MiladRahimi\PhpContainer\Types\Singleton;
use MiladRahimi\PhpContainer\Exceptions\NotFoundException;
use MiladRahimi\PhpContainer\Exceptions\ContainerException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;

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
     * Bind in transient mode
     *
     * @param $id
     * @param $concrete
     */
    public function transient($id, $concrete)
    {
        $this->repository[$id] = new Transient($concrete);
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
     * Bind to a closure
     *
     * @param $id
     * @param Closure $closure
     */
    public function closure($id, Closure $closure)
    {
        $this->repository[$id] = new Types\Closure($closure);
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
            throw new ContainerException('Reflection failed for ' . get_class($class));
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

        if ($binding instanceof Singleton && $binding->getInstance()) {
            return $binding->getInstance();
        }

        $concrete = $binding->getConcrete();

        if (is_string($concrete) && class_exists($concrete)) {
            $concrete = $this->instantiate($concrete);
        } elseif (is_callable($concrete) && !($binding instanceof Types\Closure)) {
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
     * @throws NotFoundException
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
     * @throws NotFoundException
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
