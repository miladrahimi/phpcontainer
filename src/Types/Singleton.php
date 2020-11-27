<?php

namespace MiladRahimi\PhpContainer\Types;

class Singleton
{
    /**
     * @var mixed
     */
    private $concrete;

    /**
     * Created instance of the concrete
     *
     * @var mixed
     */
    private $instance;

    /**
     * Singleton constructor.
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
