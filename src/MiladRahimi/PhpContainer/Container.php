<?php
/**
 * Created by PhpStorm.
 * User: Milad Rahimi <info@miladrahimi.com>
 * Date: 6/22/2017
 * Time: 1:44 PM
 */

namespace MiladRahimi\PhpContainer;

use Closure;
use InvalidArgumentException;
use MiladRahimi\PhpContainer\Enums\ServiceTypes;
use MiladRahimi\PhpContainer\Exceptions\BadServiceImplementationException;
use MiladRahimi\PhpContainer\Exceptions\RebindException;
use MiladRahimi\PhpContainer\Exceptions\ServiceNotFoundException;
use ReflectionFunction;

class Container
{
    /**
     * Bound Services
     *
     * @var array
     */
    private $services = [];

    /**
     * Singleton instances
     *
     * @var array
     */
    private $singletonInstances = [];

    /**
     * Bind new singleton service
     *
     * @param string $serviceName
     * @param string|Closure|object $serviceImplementation
     * @throws RebindException
     */
    public function singleton($serviceName, $serviceImplementation)
    {
        $this->bind($serviceName, ServiceTypes::Singleton, $serviceImplementation);
    }

    /**
     * Bind new prototype service
     *
     * @param string $serviceName
     * @param string|Closure|object $serviceImplementation
     * @throws RebindException
     */
    public function prototype($serviceName, $serviceImplementation)
    {
        $this->bind($serviceName, ServiceTypes::Prototype, $serviceImplementation);
    }

    /**
     * Bind new service
     *
     * @param string $serviceName
     * @param int $serviceType
     * @param string|Closure|object $serviceImplementation
     * @throws RebindException
     */
    private function bind($serviceName, $serviceType, $serviceImplementation)
    {
        if (is_string($serviceName) == false) {
            throw new InvalidArgumentException("Service name must be string");
        }

        if (array_key_exists($serviceName, $this->services)) {
            throw new RebindException($serviceName . "is bound already");
        }

        $this->services[$serviceName] = [
            'type' => $serviceType,
            'body' => $serviceImplementation,
        ];
    }

    /**
     * Run service body closure
     *
     * @param Closure $closure
     * @return mixed
     */
    private function run(Closure $closure)
    {
        $reflectionFunction = new ReflectionFunction($closure);

        return $reflectionFunction->invoke();

    }

    /**
     * Make the service (singleton or prototype)
     *
     * @param string $serviceName
     * @return mixed
     * @throws ServiceNotFoundException
     * @throws BadServiceImplementationException
     */
    public function make($serviceName)
    {
        if (array_key_exists($serviceName, $this->services) == false) {
            throw new ServiceNotFoundException();
        }

        switch ($this->services[$serviceName]["type"]) {
            case ServiceTypes::Singleton:
                return $this->makeSingleton($serviceName, $this->services[$serviceName]["body"]);
            case ServiceTypes::Prototype:
                return $this->makePrototype($this->services[$serviceName]["body"]);
        }

        return null;
    }

    /**
     * Make singleton instance of service
     *
     * @param string $serviceName
     * @param string|Closure|object $serviceImplementation
     * @return mixed
     * @throws BadServiceImplementationException
     */
    private function makeSingleton($serviceName, $serviceImplementation)
    {
        if (key_exists($serviceName, $this->singletonInstances)) {
            return $this->singletonInstances[$serviceName];
        }

        if ($serviceImplementation instanceof Closure) {
            return $this->singletonInstances[$serviceName] = $this->run($serviceImplementation);
        }

        if (is_object($serviceImplementation) || class_exists($serviceImplementation)) {
            return $this->singletonInstances[$serviceName] = new $serviceImplementation;
        }

        throw new BadServiceImplementationException('Bound service must be either closure, class or object');
    }

    /**
     * Make prototype instance of service
     *
     * @param string|Closure|object $serviceImplementation
     * @return mixed
     * @throws BadServiceImplementationException
     */
    private function makePrototype($serviceImplementation)
    {
        if ($serviceImplementation instanceof Closure) {
            return $this->run($serviceImplementation);
        }

        if (is_object($serviceImplementation) || class_exists($serviceImplementation)) {
            return new $serviceImplementation;
        }

        throw new BadServiceImplementationException('Bound service must be either closure, class or object');
    }
}