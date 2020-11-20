<?php

namespace MiladRahimi\PhpContainer\Types;

/**
 * Class Singleton
 *
 * @package MiladRahimi\PhpContainer\Types
 */
class Singleton
{
    /**
     * @var mixed
     */
    private $concrete;

    /**
     * Created instance for singleton resolution
     *
     * @var mixed
     */
    private $instance;

    /**
     * Prototype constructor.
     *
     * @param mixed $concrete
     */
    public function __construct($concrete)
    {
        $this->concrete = $concrete;
    }

    /**
     * @return mixed
     */
    public function getConcrete()
    {
        return $this->concrete;
    }

    /**
     * @return mixed
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * @param mixed $instance
     */
    public function setInstance($instance): void
    {
        $this->instance = $instance;
    }
}
