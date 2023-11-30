<?php

namespace MiladRahimi\PhpContainer\Tests;

use MiladRahimi\PhpContainer\Container;
use MiladRahimi\PhpContainer\Exceptions\ContainerException;
use MiladRahimi\PhpContainer\Tests\Classes\A;
use MiladRahimi\PhpContainer\Tests\Classes\B;
use MiladRahimi\PhpContainer\Tests\Classes\Blank;
use MiladRahimi\PhpContainer\Tests\Classes\C;
use MiladRahimi\PhpContainer\Tests\Classes\D;
use MiladRahimi\PhpContainer\Tests\Classes\E;
use MiladRahimi\PhpContainer\Tests\Classes\F;
use MiladRahimi\PhpContainer\Tests\Classes\G;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    private Container $container;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
    }

    public function test_has_method()
    {
        $this->container->transient(A::class, A::class);
        $this->container->singleton(B::class, C::class);

        $this->assertTrue($this->container->has(A::class));
        $this->assertTrue($this->container->has(B::class));
        $this->assertFalse($this->container->has(C::class));
    }

    public function test_empty_method()
    {
        $this->container->transient(Blank::class, A::class);
        $this->assertTrue($this->container->has(Blank::class));

        $this->container->empty();
        $this->assertFalse($this->container->has(Blank::class));
    }

    public function test_getting_unbound_abstraction_it_should_fail()
    {
        $this->expectException(ContainerException::class);

        $this->container->get(D::class);
    }

    public function test_getting_implicitly_with_no_constructor_parameter_it_should_resolve()
    {
        $this->assertInstanceOf(A::class, $this->container->get(A::class));
    }

    public function test_getting_implicitly_with_constructor_injections_it_should_resolve()
    {
        $this->assertInstanceOf(C::class, $this->container->get(C::class));
    }

    public function test_getting_implicitly_with_constructor_parameter_with_default_value_it_should_resolve()
    {
        /** @var E $e */
        $e = $this->container->get(E::class);

        $this->assertInstanceOf(E::class, $e);
        $this->assertEquals('something', $e->value);
    }

    public function test_getting_implicitly_with_constructor_parameter_without_default_value_it_should_fail()
    {
        $this->expectException(ContainerException::class);
        $this->container->get(F::class);
    }

    public function test_getting_explicitly_with_constructor_auto_injection_it_should_resolve()
    {
        $this->container->transient(A::class, A::class);
        $this->container->transient(B::class, B::class);
        $this->container->transient(C::class, C::class);

        $this->assertInstanceOf(C::class, $this->container->get(C::class));
    }

    public function test_singleton_explicit_binding()
    {
        $this->container->singleton(Blank::class, E::class);

        /** @var E $e1 */
        $e1 = $this->container->get(Blank::class);
        $e1->value = 'something-else';

        /** @var E $e2 */
        $e2 = $this->container->get(Blank::class);

        $this->assertEquals('something-else', $e2->value);
    }

    public function test_prototype_implicit_binding()
    {
        /** @var E $e1 */
        $e1 = $this->container->get(E::class);
        $e1->value = 'something-else';

        /** @var E $e2 */
        $e2 = $this->container->get(E::class);

        $this->assertEquals('something', $e2->value);
    }

    public function test_prototype_explicit_binding()
    {
        $this->container->transient(Blank::class, E::class);

        /** @var E $e1 */
        $e1 = $this->container->get(Blank::class);
        $e1->value = 'something-else';

        /** @var E $e2 */
        $e2 = $this->container->get(Blank::class);

        $this->assertEquals('something', $e2->value);
    }

    public function test_prototype_callable_binding_with_no_parameter()
    {
        $this->container->transient('time', function () {
            return microtime(true);
        });

        $t1 = $this->container->get('time');

        for ($i = 0; $i < 1000; $i++) sleep(0);

        $t2 = $this->container->get('time');

        $this->assertNotEquals($t1, $t2);
    }

    /**
     * @noinspection PhpUnusedParameterInspection
     */
    public function test_getting_with_invalid_callable_bound_it_should_fail()
    {
        $this->container->transient('time', function (int $requiredArg) {
            return microtime(true);
        });

        $this->expectException(ContainerException::class);
        $this->container->get('time');
    }

    public function test_singleton_callable_binding_with_no_parameter()
    {
        $this->container->singleton('time', function () {
            return microtime(true);
        });

        $t1 = $this->container->get('time');

        $t2 = $this->container->get('time');

        $this->assertSame($t1, $t2);
    }

    public function test_callable_binding_with_string_parameter()
    {
        $f = function ($value = 'something') {
            return $value;
        };

        $this->container->transient('string', $f);

        $x = $this->container->get('string');

        $this->assertEquals('something', $x);
    }

    public function test_callable_binding_with_injection()
    {
        $this->container->singleton(Blank::class, E::class);

        $this->container->transient('element', function (Blank $e) {
            return $e instanceof E;
        });

        $x = $this->container->get('element');

        $this->assertTrue($x);
    }

    public function test_object_singleton_binding()
    {
        $a = new A();
        $a->value = 'something';

        $this->container->singleton(Blank::class, $a);

        $a1 = $this->container->get(Blank::class);
        $a1->value = 'something-else';

        $a2 = $this->container->get(Blank::class);

        $this->assertInstanceOf(A::class, $a);
        $this->assertEquals('something-else', $a2->value);
    }

    public function test_object_prototype_binding()
    {
        $a = new A();
        $a->value = 'something';

        $this->container->transient(Blank::class, $a);

        $a1 = $this->container->get(Blank::class);
        $a1->value = 'something-else';

        $a2 = $this->container->get(Blank::class);

        $this->assertInstanceOf(A::class, $a);
        $this->assertEquals('something', $a2->value);
    }

    public function test_scalar_binding()
    {
        $this->container->transient('ABC', 'XYZ');

        $value = $this->container->get('ABC');

        $this->assertEquals('XYZ', $value);
    }

    public function test_call_with_free_function_it_should_only_call_it()
    {
        $response = $this->container->call(function () {
            return 666;
        });

        $this->assertEquals(666, $response);
    }

    public function test_call_with_some_dependencies_it_should_resolve_em()
    {
        $value = mt_rand(0, 1000000);

        $this->container->transient(Blank::class, new A($value));

        $response = $this->container->call(function (Blank $a) {
            return $a->value;
        });

        $this->assertEquals($value, $response);
    }

    public function test_call_with_singleton_named_binding_it_should_resolve()
    {
        $number = mt_rand(0, 1000000);

        $this->container->singleton('$number', $number);

        $response = $this->container->call(function ($number) {
            return $number;
        });

        $this->assertEquals($number, $response);
    }

    public function test_call_with_value_that_match_a_php_function_name()
    {
        $value = 'count';

        $this->container->transient('$key', $value);

        $response = $this->container->call(function ($key) {
            return $key;
        });

        $this->assertEquals($value, $response);
    }

    public function test_call_with_prototype_named_binding_it_should_resolve()
    {
        $number = mt_rand(0, 1000000);

        $this->container->transient('$number', $number);

        $response = $this->container->call(function ($number) {
            return $number;
        });

        $this->assertEquals($number, $response);
    }

    public function test_calling_method_it_should_resolve()
    {
        $object = new G();

        $this->container->singleton('$number', 666);
        $this->container->call([$object, 'setNumber']);

        $this->assertEquals(666, $object->number);
    }

    public function test_binding_to_a_closure_with_name()
    {
        $sum = function ($a, $b) {
            return $a + $b;
        };

        $this->container->closure('$sum', $sum);

        $r = $this->container->call(function ($sum) {
            return $sum(7, 6);
        });

        $this->assertEquals(13, $r);
    }

    public function test_deleting_a_binding()
    {
        $this->container->transient('temp', 'Bye!');
        $this->container->delete('temp');

        $this->expectException(ContainerException::class);
        $this->container->get('temp');
    }

    public function test_instantiating()
    {
        /** @var E $e */
        $e = $this->container->instantiate(E::class);

        $this->assertInstanceOf(A::class, $e->a);
        $this->assertEquals('something', $e->value);
    }

    public function test_calling_a_method()
    {
        $g = new G();

        $this->container->singleton('$number', 13);
        $this->container->call([$g, 'setNumber']);

        $this->assertEquals(13, $g->number);
    }
}
