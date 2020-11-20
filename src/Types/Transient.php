<?php

namespace MiladRahimi\PhpContainer\Types;

/**
 * Class Transient
 *
 * @package MiladRahimi\PhpContainer\Types
 */
class Transient
{
    /**
     * @var mixed
     */
    private $concrete;

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
}
