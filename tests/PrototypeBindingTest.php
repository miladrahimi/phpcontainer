<?php
/**
 * Created by PhpStorm.
 * User: Milad Rahimi <info@miladrahimi.com>
 * Date: 6/22/2017
 * Time: 2:03 PM
 */

namespace MiladRahimi\PhpContainer\Tests;

require_once "bootstrap.php";

use DateTime;
use MiladRahimi\PhpContainer\Container;
use PHPUnit\Framework\TestCase;

class PrototypeBindingTest extends TestCase
{
    public function test_binding_a_closure()
    {
        $container = new Container();

        $container->prototype('blackNumber', function () {
            return 666;
        });

        $result = $container->make('blackNumber');

        $this->assertSame($result, 666);
    }

    public function test_binding_a_class()
    {
        $container = new Container();
        $container->prototype('datetime', DateTime::class);

        $e = $container->make('datetime');
        $this->assertEquals(new DateTime(), $e);
    }

    public function test_binding_an_object()
    {
        $container = new Container();
        $container->prototype('datetime', new DateTime());

        $e = $container->make('datetime');
        $this->assertEquals(new DateTime(), $e);
    }

    public function test_difference_of_given_instances()
    {
        $container = new Container();

        $container->prototype('dateTime', DateTime::class);

        $instance1 = $container->make('dateTime');
        $instance2 = $container->make('dateTime');

        $this->assertNotSame($instance1, $instance2);
    }
}
