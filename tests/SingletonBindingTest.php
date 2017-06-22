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

class SingletonBindingTest extends TestCase
{
    public function test_binding_a_closure()
    {
        $container = new Container();

        $container->singleton('blackNumber', function () {
            return 666;
        });

        $result = $container->make('blackNumber');

        $this->assertSame($result, 666);
    }

    public function test_binding_a_class()
    {
        $container = new Container();
        $container->singleton('datetime', DateTime::class);

        $e = $container->make('datetime');
        $this->assertEquals(new DateTime(), $e);
    }

    public function test_binding_an_object()
    {
        $container = new Container();
        $container->singleton('datetime', new DateTime());

        $e = $container->make('datetime');
        $this->assertEquals(new DateTime(), $e);
    }

    public function test_equality_of_given_instances()
    {
        $container = new Container();

        $container->singleton('dateTime', DateTime::class);

        $instance1 = $container->make('dateTime');
        $instance2 = $container->make('dateTime');

        $this->assertSame($instance1, $instance2);
    }

    /**
     * @expectedException \MiladRahimi\PhpContainer\Exceptions\ServiceNotFoundException
     */
    public function test_attempting_to_make_a_unbound_class()
    {
        $container = new Container();
        $container->make('unknown');
    }
}
