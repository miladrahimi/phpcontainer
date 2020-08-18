<?php

namespace MiladRahimi\PhpContainer\Tests;

use MiladRahimi\PhpContainer\Container;
use MiladRahimi\PhpContainer\Exceptions\NotFoundException;
use MiladRahimi\PhpContainer\Exceptions\ContainerException;
use MiladRahimi\PhpContainer\Tests\Classes\A;
use MiladRahimi\PhpContainer\Tests\Classes\B;
use MiladRahimi\PhpContainer\Tests\Classes\Blank;
use MiladRahimi\PhpContainer\Tests\Classes\C;
use MiladRahimi\PhpContainer\Tests\Classes\D;
use MiladRahimi\PhpContainer\Tests\Classes\E;
use MiladRahimi\PhpContainer\Tests\Classes\F;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @inheritDoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->container = new Container();
    }

    public function test_has_method()
    {
        $this->container->prototype(A::class, A::class);
        $this->container->singleton(B::class, C::class);

        $this->assertTrue($this->container->has(A::class));
        $this->assertTrue($this->container->has(B::class));
        $this->assertFalse($this->container->has(C::class));
    }

    public function test_empty_method()
    {
        $this->container->prototype(Blank::class, A::class);
        $this->assertTrue($this->container->has(Blank::class));

        $this->container->empty();
        $this->assertFalse($this->container->has(Blank::class));
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function test_getting_unbound_abstraction_it_should_fail()
    {
        $this->expectException(NotFoundException::class);

        $this->container->get(D::class);
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function test_getting_implicitly_with_no_constructor_parameter_it_should_resolve()
    {
        $this->assertInstanceOf(A::class, $this->container->get(A::class));
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function test_getting_implicitly_with_constructor_injections_it_should_resolve()
    {
        $this->assertInstanceOf(C::class, $this->container->get(C::class));
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function test_getting_implicitly_with_constructor_parameter_with_default_value_it_should_resolve()
    {
        /** @var E $e */
        $e = $this->container->get(E::class);

        $this->assertInstanceOf(E::class, $e);
        $this->assertEquals('something', $e->value);
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function test_getting_implicitly_with_constructor_parameter_without_default_value_it_should_fail()
    {
        $this->expectException(ContainerException::class);
        $this->container->get(F::class);
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function test_getting_explicitly_with_constructor_auto_injection_it_should_resolve()
    {
        $this->container->prototype(A::class, A::class);
        $this->container->prototype(B::class, B::class);
        $this->container->prototype(C::class, C::class);

        $this->assertInstanceOf(C::class, $this->container->get(C::class));
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
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

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function test_prototype_implicit_binding()
    {
        /** @var E $e1 */
        $e1 = $this->container->get(E::class);
        $e1->value = 'something-else';

        /** @var E $e2 */
        $e2 = $this->container->get(E::class);

        $this->assertEquals('something', $e2->value);
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function test_prototype_explicit_binding()
    {
        $this->container->prototype(Blank::class, E::class);

        /** @var E $e1 */
        $e1 = $this->container->get(Blank::class);
        $e1->value = 'something-else';

        /** @var E $e2 */
        $e2 = $this->container->get(Blank::class);

        $this->assertEquals('something', $e2->value);
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function test_prototype_callable_binding_with_no_parameter()
    {
        $this->container->prototype('time', function () {
            return microtime(true);
        });

        $t1 = $this->container->get('time');

        $t2 = $this->container->get('time');

        $this->assertNotEquals($t1, $t2);
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     * @noinspection PhpUnusedParameterInspection
     */
    public function test_getting_with_invalid_callable_bound_it_should_fail()
    {
        $this->container->prototype('time', function (int $requiredArg) {
            return microtime(true);
        });

        $this->expectException(ContainerException::class);
        $this->container->get('time');
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function test_singleton_callable_binding_with_no_parameter()
    {
        $this->container->singleton('time', function () {
            return microtime(true);
        });

        $t1 = $this->container->get('time');

        $t2 = $this->container->get('time');

        $this->assertSame($t1, $t2);
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function test_callable_binding_with_string_parameter()
    {
        $f = function ($value = 'something') {
            return $value;
        };

        $this->container->prototype('string', $f);

        $x = $this->container->get('string');

        $this->assertEquals('something', $x);
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function test_callable_binding_with_injection()
    {
        $this->container->singleton(Blank::class, E::class);

        $this->container->prototype('element', function (Blank $e, B $b) {
            return $e instanceof E && $b instanceof B;
        });

        $x = $this->container->get('element');

        $this->assertTrue($x);
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
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

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function test_object_prototype_binding()
    {
        $a = new A();
        $a->value = 'something';

        $this->container->prototype(Blank::class, $a);

        $a1 = $this->container->get(Blank::class);
        $a1->value = 'something-else';

        $a2 = $this->container->get(Blank::class);

        $this->assertInstanceOf(A::class, $a);
        $this->assertEquals('something', $a2->value);
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function test_scalar_binding()
    {
        $this->container->prototype('ABC', 'XYZ');

        $value = $this->container->get('ABC');

        $this->assertEquals('XYZ', $value);
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function test_call_with_free_function_it_should_only_call_it()
    {
        $response = $this->container->call(function () {
            return 666;
        });

        $this->assertEquals($response, 666);
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function test_call_with_some_dependencies_it_should_resolve_em()
    {
        $value = mt_rand(0, 1000000);

        $this->container->prototype(Blank::class, new A($value));

        $response = $this->container->call(function (Blank $a) {
            return $a->value;
        });

        $this->assertEquals($response, $value);
    }
}
