<?php

namespace MiladRahimi\PhpContainer\Tests\Classes;

class F
{
    public $a;

    public $value;

    public function __construct(A $a, string $value)
    {
        $this->a = $a;
        $this->value = $value;
    }
}
