<?php

namespace MiladRahimi\PhpContainer\Types;

use Closure as PClosure;

class Closure
{
    private PClosure $concrete;

    public function __construct(PClosure $concrete)
    {
        $this->concrete = $concrete;
    }

    public function getConcrete(): PClosure
    {
        return $this->concrete;
    }
}
