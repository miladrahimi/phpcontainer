<?php

namespace MiladRahimi\PhpContainer\Types;

class Transient
{
    /**
     * @var mixed
     */
    private $concrete;

    /**
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
