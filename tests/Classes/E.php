<?php

namespace MiladRahimi\PhpContainer\Tests\Classes;

class E implements Blank
{
    public $a;

    public $value;

    public function __construct(A $a, string $value = 'something')
    {
        $this->a = $a;
        $this->value = $value;
    }
}
