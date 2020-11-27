<?php

namespace MiladRahimi\PhpContainer\Types;

use Closure as PClosure;

class Closure
{
    /**
     * @var PClosure
     */
    private $concrete;

    /**
     * Closure constructor.
     *
     * @param PClosure $concrete
     */
    public function __construct(PClosure $concrete)
    {
        $this->concrete = $concrete;
    }

    /**
     * @return PClosure
     */
    public function getConcrete(): PClosure
    {
        return $this->concrete;
    }
}
