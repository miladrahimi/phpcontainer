<?php

namespace MiladRahimi\PhpContainer\Tests\Classes;

class E implements Blank
{
    public A $a;

    public string $value;

    public function __construct(A $a, string $value = 'something')
    {
        $this->a = $a;
        $this->value = $value;
    }
}
