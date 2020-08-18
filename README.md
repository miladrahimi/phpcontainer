[![Latest Stable Version](https://poser.pugx.org/miladrahimi/phpcontainer/v)](//packagist.org/packages/miladrahimi/phpcontainer)
[![Total Downloads](https://poser.pugx.org/miladrahimi/phpcontainer/downloads)](//packagist.org/packages/miladrahimi/phpcontainer)
[![Build Status](https://travis-ci.org/miladrahimi/phpcontainer.svg?branch=master)](https://travis-ci.org/miladrahimi/phpcontainer)
[![Coverage Status](https://coveralls.io/repos/github/miladrahimi/phpcontainer/badge.svg?branch=master)](https://coveralls.io/github/miladrahimi/phpcontainer?branch=master)
[![License](https://poser.pugx.org/miladrahimi/phpcontainer/license)](//packagist.org/packages/miladrahimi/phpcontainer)

# PhpContainer

PSR-11 compliant dependency injection container (IoC) for PHP projects.

## Overview
[Dependency Inversion](https://en.wikipedia.org/wiki/Dependency_inversion_principle) is one of the most important Object-oriented design principles.

[Dependency Injection](https://en.wikipedia.org/wiki/Dependency_injection), [Inversion of control](https://en.wikipedia.org/wiki/Inversion_of_control) and [IoC Container](http://www.codeproject.com/Articles/542752/Dependency-Inversion-Principle-IoC-Container-Depen) are the outcomes for this principle.

PhpContainer provides a [PSR-11 compliant](https://www.php-fig.org/psr/psr-11) dependency injection container (aka IoC Container) for your PHP projects.

## Installation

You can add PhpContainer to your project via Composer with the following command:

```bash
composer require miladrahimi/phpcontainer:4.*
```

## Documentation

### Explicit Binding

Explicit binding means explicitly bind an abstraction to a concrete (implementation).
You can bind via `singleton()` and `prototype()` methods.

```php
use MiladRahimi\PhpContainer\Container;

$container = new Container();

$container->singleton(DatabaseInterface::class, MySQL::class);
$container->prototype(MailerInterface::class, MailTrap::class);

$database = $container->get(DatabaseInterface::class);
$mailer = $container->get(MailerInterface::class);
```

The container instantiates implementation classes only once and returns them whenever you call the `get` method if you bind them via the `singleton` method.
On the other hand, it instantiates implementation classes on any instantiation request, if you bind them via the `prototype` method.

The following example demonstrates the differences between singleton and prototype binding.

```php
use MiladRahimi\PhpContainer\Container;

$container = new Container();

$container->prototype(InterfaceA::class, ClassA::class);
$container->singleton(InterfaceB::class, ClassB::class);

$a1 = $container->get(InterfaceA::class);
$a1->name = 'Something';

$a2 = $container->get(InterfaceA::class);
echo $a2->name; // NULL

$b1 = $container->get(InterfaceB::class);
$b1->name = 'Something';

$b2 = $container->get(InterfaceB::class);
echo $b2->name; // 'Something'

```

### Implicit Binding

You can retrieve implementation classes from the container instead of using the new keyword to instantiate them.
It could help mock orphan implementation classes.

```php
use MiladRahimi\PhpContainer\Container;

$container = new Container();

// No binding here!

$database = $container->get(MySQL::class);
```

### Constructor Auto-injection

Implementation classes can have constructor parameters that have default values or resolvable by the container.

```php
use MiladRahimi\PhpContainer\Container;

class Notifier implements NotifierInterface {
    public function __constructor(MailInterface $mail, Sms $sms, $sender = 'Awesome') {
        // ...
    }
}

$container = new Container();

$container->prototype(MailInterface::class, MailTrap::class);
$container->prototype(NotifierInterface::class, Notifier::class);

$notifier = $container->get(NotifierInterface::class);
```

Well, let's check what will the container do!
The container is supposed to create an instance of Notifier.
The Notifier constructor has some arguments, it's ok!
The first argument is MailInterface and it is already bound to MailTrap so the container will inject an instance of MailTrap.
The second argument is SMS class, it's not bound to any implementation but it's insatiable so the container instantiates and passes an instance of it.
The last argument is a primitive variable and has a default value so the container passes the same default value.

Constructor auto-injection is also available for implicit bindings.

### Closure as implementation

Following example illustrates how to use closure as implementation.

```php
use MiladRahimi\PhpContainer\Container;

$container = new Container();

$container->prototype('time-prototype', function () {
    return microtime(true);
});

$container->singleton('time-singleton', function () {
    return microtime(true);
});

$tp1 = $container->get('time-prototype');
$tp2 = $container->get('time-prototype');
echo $tp1 == $tp2; // FALSE

$ts1 = $container->get('time-singleton');
$ts2 = $container->get('time-singleton');
echo $ts1 == $ts2; // TRUE
```

Just like class constructors, closures are also able to have arguments.
The container will try to inject/pass appropriate implementations/values to closures.

```php
use MiladRahimi\PhpContainer\Container;

$container = new Container();

$container->prototype(MailerInterface::class, MailTrap::class);
$container->prototype('notifier', function (MailerInterface $mailer) {
    $notifier = new Notifier();
    $notifier->setMailer($mailer);
    
    return $notifier;
});
```

### Object binding

You can also bind an abstraction to an object.
In this case, singleton binding is used to release the original object on resolve, and prototype binding is also used to release a clone of the object each time.

```php
use MiladRahimi\PhpContainer\Container;

$user = new User();
$user->name = 'Milad';

$container = new Container();

$container->singleton('user', $user);
```

### Resolving function parameters

The example below demonstrates how to resolve a function parameters.

```php
use MiladRahimi\PhpContainer\Container;

$container = new Container();

$container->singleton(MailInterface::class, MailTrap::class);

$response = $container->call(function(MailerInterface $mailer) {
    // $mailer will be an instance of MailerInterface
    return $mailer->send('info@example.com', 'Hello...');
});

```

### Error handling

The container might raise the following exceptions:

`NotFoundException` raises when you try to make an abstraction while you haven't bound it to any concrete yet.

`ContainerException` raises when `ReflectionException` raises, or the container cannot inject parameter values to the concrete constructor or closures.

## License

PhpContainer is created by [Milad Rahimi](https://miladrahimi.com) and released under the [MIT License](http://opensource.org/licenses/mit-license.php).
