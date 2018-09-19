<?php

namespace MiladRahimi\PhpContainer\Collections;

class Prototype
{
    /**
     * @var mixed
     */
    public $concrete;

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
