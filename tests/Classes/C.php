<?php

namespace MiladRahimi\PhpContainer\Tests\Classes;

class C
{
    public A $a;

    public B $b;

    public function __construct(A $a, B $b)
    {
        $this->a = $a;
        $this->b = $b;
    }
}
