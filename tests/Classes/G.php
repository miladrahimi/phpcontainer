<?php

namespace MiladRahimi\PhpContainer\Tests\Classes;

class G
{
    /**
     * @var mixed
     */
    public $number;

    /**
     * @param mixed $number
     * @return void
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }
}
