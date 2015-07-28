<?php namespace MiladRahimi\PHPContainer;

use MiladRahimi\PHPContainer\Exceptions\BadServiceBodyException;
use MiladRahimi\PHPContainer\Exceptions\InvalidArgumentException;
use MiladRahimi\PHPContainer\Exceptions\RebindException;
use MiladRahimi\PHPContainer\Exceptions\ServiceNotFoundException;

/**
 * Class Container
 * Container is an Inversion of Control or Dependency Injecc
 *
 * @package MiladRahimi\PHPContainer
 * @author Milad Rahimi <info@miladrahimi.com>
 */
class Container
{

    /**
     * Services in the container
     *
     * @var array
     */
    private $services = array();

    /**
     * Bind new singleton service
     *
     * @param string $service_name
     * @param \Closure $injection_body
     * @throws RebindException
     */
    public function singleton($service_name, \Closure $injection_body)
    {
        if (!isset($service_name) || !is_scalar($service_name))
            throw new InvalidArgumentException("Service name must be a scalar value");
        if (array_key_exists($service_name, $this->services))
            throw new RebindException($service_name . "is bound already");
        $this->add($service_name, array(
            "type" => "singleton",
            "body" => $injection_body
        ));
    }

    /**
     * Bind new prototype service
     *
     * @param string $service_name
     * @param \Closure $injection_body
     * @throws RebindException
     */
    public function prototype($service_name, \Closure $injection_body)
    {
        if (!isset($service_name) || !is_scalar($service_name))
            throw new InvalidArgumentException("Service name must be a scalar value");
        if (array_key_exists($service_name, $this->services))
            throw new RebindException($service_name . "is bound already");
        $this->add($service_name, array(
            "type" => "prototype",
            "body" => $injection_body
        ));
    }

    /**
     * Bind new normal service
     *
     * @param string $service_name
     * @param \Closure $injection_body
     * @throws RebindException
     */
    public function define($service_name, \Closure $injection_body)
    {
        if (!isset($service_name) || !is_scalar($service_name))
            throw new InvalidArgumentException("Service name must be a scalar value");
        if (array_key_exists($service_name, $this->services))
            throw new RebindException($service_name . "is bound already");
        $this->add($service_name, array(
            "type" => "normal",
            "body" => $injection_body
        ));
    }

    /**
     * Bind new normal service
     *
     * @param string $service_name
     * @param $instance
     * @throws RebindException
     */
    public function instance($service_name, $instance)
    {
        if (!isset($service_name) || !is_scalar($service_name))
            throw new InvalidArgumentException("Service name must be a scalar value");
        if (!isset($instance) || !is_object($instance))
            throw new InvalidArgumentException("Instance must be an object");
        if (array_key_exists($service_name, $this->services))
            throw new RebindException($service_name . "is bound already");
        $this->add($service_name, array(
            "type" => "instance",
            "instance" => $instance
        ));
    }

    /**
     * Add new service
     *
     * @param $service_name
     * @param array $service_details
     */
    private function add($service_name, array $service_details)
    {
        $this->services[$service_name] = $service_details;
    }

    private function run(\Closure $closure)
    {
        $ref = new \ReflectionFunction($closure);
        if ($ref->getNumberOfParameters() > 0)
            throw new BadServiceBodyException("Service body closure cannot have parameters");
        return $ref->invoke();

    }

    /**
     * @param string $service_name
     * @return mixed
     * @throws ServiceNotFoundException
     */
    public function get($service_name)
    {
        if (!isset($service_name) || !is_scalar($service_name))
            throw new InvalidArgumentException("Service name must be a scalar value");
        if (!array_key_exists($service_name, $this->services))
            throw new ServiceNotFoundException($service_name . "is not bound in the container");
        switch ($this->services[$service_name]["type"]) {
            case "singleton":
                if (isset($this->services[$service_name]["instance"])) {
                    return $this->services[$service_name]["instance"];
                } else {
                    return $this->services[$service_name]["instance"] =
                        $this->run($this->services[$service_name]["body"]);
                }
                break;
            case "prototype":
                if (isset($this->services[$service_name]["instance"])) {
                    return clone $this->services[$service_name]["instance"];
                } else {
                    $this->services[$service_name]["instance"] = $this->run($this->services[$service_name]["body"]);
                    return clone $this->services[$service_name]["instance"];
                }
                break;
            case "normal":
                return $this->run($this->services[$service_name]["body"]);
            case "instance":
                return $this->services[$service_name]["instance"];
        }
        return null;
    }

}