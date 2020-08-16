<?php

namespace MiladRahimi\PhpContainer\Tests\Classes;

class C
{
    public $a;

    public $b;

    public function __construct(A $a, B $b)
    {
        $this->a = $a;
        $this->b = $b;
    }
}
