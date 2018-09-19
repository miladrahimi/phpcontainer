<?php

namespace MiladRahimi\PhpContainer\Tests;

use MiladRahimi\PhpContainer\Container;
use MiladRahimi\PhpContainer\Exceptions\BindingNotFoundException;
use MiladRahimi\PhpContainer\Exceptions\ResolutionException;
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
    public function setUp()
    {
        parent::setUp();

        Container::reset();
    }

    public function test_is_bound_method()
    {
        Container::prototype(A::class, A::class);
        Container::singleton(B::class, C::class);

        $this->assertTrue(Container::isBound(A::class));
        $this->assertTrue(Container::isBound(B::class));
        $this->assertFalse(Container::isBound(C::class));
    }

    /**
     * @throws ResolutionException
     */
    public function test_is_resolvable_method()
    {
        Container::prototype(A::class, A::class);
        Container::singleton(B::class, C::class);

        $this->assertTrue(Container::isResolvable(A::class));
        $this->assertTrue(Container::isResolvable(B::class));
        $this->assertTrue(Container::isResolvable(C::class));
        $this->assertFalse(Container::isResolvable(D::class));
    }

    /**
     * @throws BindingNotFoundException
     * @throws ResolutionException
     */
    public function test_making_unbound_abstraction()
    {
        $this->expectException(BindingNotFoundException::class);

        Container::make(D::class);
    }

    /**
     * @throws BindingNotFoundException
     * @throws ResolutionException
     */
    public function test_making_implicitly_with_no_constructor_parameter()
    {
        $a = Container::make(A::class);

        $this->assertInstanceOf(A::class, $a);
    }

    /**
     * @throws BindingNotFoundException
     * @throws ResolutionException
     */
    public function test_making_implicitly_with_constructor_injections()
    {
        $c = Container::make(C::class);

        $this->assertInstanceOf(C::class, $c);
    }

    /**
     * @throws BindingNotFoundException
     * @throws ResolutionException
     */
    public function test_making_implicitly_with_constructor_parameter_with_default_value()
    {
        /** @var E $e */
        $e = Container::make(E::class);

        $this->assertInstanceOf(E::class, $e);
        $this->assertEquals('something', $e->value);
    }

    /**
     * @throws ResolutionException
     * @throws BindingNotFoundException
     */
    public function test_making_implicitly_with_constructor_parameter_without_default_value()
    {
        $this->expectException(ResolutionException::class);
        Container::make(F::class);
    }

    /**
     * @throws BindingNotFoundException
     * @throws ResolutionException
     */
    public function test_making_explicitly_with_constructor_auto_injection()
    {
        Container::prototype(A::class, A::class);
        Container::prototype(B::class, B::class);
        Container::prototype(C::class, C::class);

        $c = Container::make(C::class);

        $this->assertInstanceOf(C::class, $c);
    }

    /**
     * @throws BindingNotFoundException
     * @throws ResolutionException
     */
    public function test_prototype_implicit_binding()
    {
        /** @var E $e1 */
        $e1 = Container::make(E::class);
        $e1->value = 'something-else';

        /** @var E $e2 */
        $e2 = Container::make(E::class);

        $this->assertEquals('something', $e2->value);
    }

    /**
     * @throws BindingNotFoundException
     * @throws ResolutionException
     */
    public function test_prototype_explicit_binding()
    {
        Container::prototype(Blank::class, E::class);

        /** @var E $e1 */
        $e1 = Container::make(Blank::class);
        $e1->value = 'something-else';

        /** @var E $e2 */
        $e2 = Container::make(Blank::class);

        $this->assertEquals('something', $e2->value);
    }

    /**
     * @throws BindingNotFoundException
     * @throws ResolutionException
     */
    public function test_singleton_explicit_binding()
    {
        Container::singleton(Blank::class, E::class);

        /** @var E $e1 */
        $e1 = Container::make(Blank::class);
        $e1->value = 'something-else';

        /** @var E $e2 */
        $e2 = Container::make(Blank::class);

        $this->assertEquals('something-else', $e2->value);
    }

    /**
     * @throws BindingNotFoundException
     * @throws ResolutionException
     */
    public function test_prototype_callable_binding_with_no_parameter()
    {
        Container::prototype('time', function () {
            return microtime(true);
        });

        $t1 = Container::make('time');

        $t2 = Container::make('time');

        $this->assertNotEquals($t1, $t2);
    }

    /**
     * @throws BindingNotFoundException
     * @throws ResolutionException
     */
    public function test_singleton_callable_binding_with_no_parameter()
    {
        Container::singleton('time', function () {
            return microtime(true);
        });

        $t1 = Container::make('time');

        $t2 = Container::make('time');

        $this->assertSame($t1, $t2);
    }

    /**
     * @throws BindingNotFoundException
     * @throws ResolutionException
     */
    public function test_callable_binding_with_string_parameter()
    {
        $f = function ($value = 'something') {
            return $value;
        };

        Container::prototype('string', $f);

        $x = Container::make('string');

        $this->assertEquals('something', $x);
    }

    /**
     * @throws BindingNotFoundException
     * @throws ResolutionException
     */
    public function test_callable_binding_with_injection()
    {
        Container::singleton(Blank::class, E::class);

        Container::prototype('element', function (Blank $e, B $b) {
            return $e instanceof E && $b instanceof B;
        });

        $x = Container::make('element');

        $this->assertTrue($x);
    }

    /**
     * @throws BindingNotFoundException
     * @throws ResolutionException
     */
    public function test_object_singleton_binding()
    {
        $a = new A();
        $a->value = 'something';

        Container::singleton(Blank::class, $a);

        $a1 = Container::make(Blank::class);
        $a1->value = 'something-else';

        $a2 = Container::make(Blank::class);

        $this->assertInstanceOf(A::class, $a);
        $this->assertEquals('something-else', $a2->value);
    }

    /**
     * @throws BindingNotFoundException
     * @throws ResolutionException
     */
    public function test_object_prototype_binding()
    {
        $a = new A();
        $a->value = 'something';

        Container::prototype(Blank::class, $a);

        $a1 = Container::make(Blank::class);
        $a1->value = 'something-else';

        $a2 = Container::make(Blank::class);

        $this->assertInstanceOf(A::class, $a);
        $this->assertEquals('something', $a2->value);
    }

    /**
     * @throws BindingNotFoundException
     * @throws ResolutionException
     */
    public function test_scalar_binding()
    {
        Container::prototype('ABC', 'XYZ');

        $value = Container::make('ABC');

        $this->assertEquals('XYZ', $value);
    }
}