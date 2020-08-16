<?php

namespace MiladRahimi\PhpContainer\Collections;

/**
 * Class Singleton
 *
 * @package MiladRahimi\PhpContainer\Collections
 */
class Singleton
{
    /**
     * @var mixed
     */
    public $concrete;

    /**
     * Created instance for singleton resolution
     *
     * @var mixed
     */
    public $instance;

    /**
     * Prototype constructor.
     *
     * @param mixed $concrete
     */
    public function __construct($concrete)
    {
        $this->concrete = $concrete;
    }
}
