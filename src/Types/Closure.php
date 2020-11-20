<?php

namespace MiladRahimi\PhpContainer\Types;

/**
 * Class Closure
 *
 * @package MiladRahimi\PhpContainer\Types
 */
class Closure
{
    private $concrete;

    public function __construct(\Closure $concrete)
    {
        $this->concrete = $concrete;
    }

    /**
     * @return \Closure
     */
    public function getConcrete(): \Closure
    {
        return $this->concrete;
    }
}
