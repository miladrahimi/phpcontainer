<?php

namespace MiladRahimi\PhpContainer\Tests\Classes;

class A implements Blank
{
    public $value;

    public function __construct($value = null)
    {
        $this->value = $value;
    }
}
