[![Latest Stable Version](https://poser.pugx.org/miladrahimi/phpcontainer/v)](//packagist.org/packages/miladrahimi/phpcontainer)
[![Total Downloads](https://poser.pugx.org/miladrahimi/phpcontainer/downloads)](//packagist.org/packages/miladrahimi/phpcontainer)
[![Build Status](https://travis-ci.org/miladrahimi/phpcontainer.svg?branch=master)](https://travis-ci.org/miladrahimi/phpcontainer)
[![Coverage Status](https://coveralls.io/repos/github/miladrahimi/phpcontainer/badge.svg?branch=master)](https://coveralls.io/github/miladrahimi/phpcontainer?branch=master)
[![License](https://poser.pugx.org/miladrahimi/phpcontainer/license)](//packagist.org/packages/miladrahimi/phpcontainer)

# PhpContainer

PSR-11 compliant dependency injection (Inversion of Control) container for PHP projects.

Features:
* Singleton, transient, and Closure binding
* Explicit and implicit binding
* Typed and named binding
* Constructor and Closure auto-injection for nested resolving
* Smart resolving using explicit and implicit binding and default values
* Binding using Closure
* Binding to objects
* Direct class instantiating and dependency injection
* Direct function, closure, and method calling and dependency injection

## Overview
[Dependency Inversion](https://en.wikipedia.org/wiki/Dependency_inversion_principle) is one of the most important Object-oriented design principles.

[Dependency Injection](https://en.wikipedia.org/wiki/Dependency_injection), [Inversion of control](https://en.wikipedia.org/wiki/Inversion_of_control) and [IoC Container](http://www.codeproject.com/Articles/542752/Dependency-Inversion-Principle-IoC-Container-Depen) are the outcomes for this principle.

PhpContainer provides a [PSR-11 compliant](https://www.php-fig.org/psr/psr-11) dependency injection container (aka IoC Container) for your PHP projects.

## Installation

You can add PhpContainer to your project via Composer with the following command:

```bash
composer require miladrahimi/phpcontainer:5.*
```

## Documentation

### Explicit Binding

Explicit binding means explicitly bind an abstraction to a concrete (implementation).
You can use the `singleton()`, `transient()` and `closure()` methods to bind explicitly.

```php
use MiladRahimi\PhpContainer\Container;

$container = new Container();

$container->singleton(DatabaseInterface::class, MySQL::class);
$container->transient(MailerInterface::class, MailTrap::class);
$container->closure('sum', function($a, $b) {
    return $a + $b;
});

$database = $container->get(DatabaseInterface::class);
$mailer = $container->get(MailerInterface::class);
$sum = $container->get('sum');
```

#### Binding methods

* **Singleton binding**: When you bind using the `singleton` method, the container creates the concrete only once and return it whenever you need it.

* **Transient binding**: When you bind using the `transient` method, the container clones or creates a brand-new concrete each time you need it.

* **Closure binding**: You can only bind closures using the `closure` method. Then the container returns the closure when you need it. It prevents the container call the closure (it is the default behavior).

The following example demonstrates the differences between singleton and transient binding.

```php
use MiladRahimi\PhpContainer\Container;

$container = new Container();

$container->transient(InterfaceA::class, ClassA::class);
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

The container tries to instantiate the needed class when there is no concrete bound.
In the example below, the container instantiates the `MySQL` class and returns the instance.
The container raises an error when it cannot instantiate (for example, it's an interface or abstract class).

```php
use MiladRahimi\PhpContainer\Container;

$container = new Container();

// No (explicit) binding here!

$database = $container->get(MySQL::class);
```

### Binding to objects

You can bind abstractions to objects.
In this case, you can use singleton binding to get the original object when you need it or transient binding to get a clone of the object each time you need it.

```php
use MiladRahimi\PhpContainer\Container;

$user = new User();
$user->name = 'Milad';

$container = new Container();
$container->singleton('user', $user);
```

### Constructor Auto-injection

Concrete classes can have constructor parameters that have default values or resolvable by the container.

```php
use MiladRahimi\PhpContainer\Container;

class Notifier implements NotifierInterface {
    public function __constructor(MailInterface $mail, Sms $sms, $sender = 'Awesome') {
        // ...
    }
}

$container = new Container();

$container->transient(MailInterface::class, MailTrap::class);
$container->transient(NotifierInterface::class, Notifier::class);

$notifier = $container->get(NotifierInterface::class);
```

Well, let's check what the container does!
The container tries to create an instance of Notifier.
The Notifier constructor has some arguments. It's ok!
The first argument is MailInterface, and it's already bound to MailTrap. The container injects an instance of MailTrap.
The second argument is the `Sms` class. It's not bound to any implementation, but it's insatiable, so the container instantiates and passes an instance of it.
The last argument is a primitive variable and has a default value, so the container passes the default value.

Constructor auto-injection is also available for implicit bindings.

### Binding using Closure

The following example illustrates how to bind using Closure.

```php
use MiladRahimi\PhpContainer\Container;

$container = new Container();

$container->singleton(Config::class, function () {
    return new JsonConfig('/path/to/config.json');
});

// $config would be auto-injected
$container->singleton(Database::class, function (Config $config) {
    return new MySQL(
        $config->get('database.host'),
        $config->get('database.port'),
        $config->get('database.name'),
        $config->get('database.username'),
        $config->get('database.password')
    );
});
```

The container calls the Closure once in singleton binding and calls it each time needed in transient binding.
If you want to bind an abstraction to a Closure and don't want the container to call the Closure, you can use the `closure()` binding method instead.

### Direct Closure, function, and method call

```php
use MiladRahimi\PhpContainer\Container;

$container = new Container();
$container->singleton(MailInterface::class, MailTrap::class);

// Direct Closure call
$response = $container->call(function(MailerInterface $mailer) {
    return $mailer->send('info@example.com', 'Hello...');
});

// Direct function call
function sendMail(MailerInterface $mailer) {
    return $mailer->send('info@example.com', 'Hello...');
}
$response = $container->call('sendMail');

// Direct method call
class UserManager {
    function sendMail(MailerInterface $mailer) {
        return $mailer->send('info@example.com', 'Hello...');
    }
}
$response = $container->call([UserManager::class, 'sendMail']);
```

### Direct class instantiating

You can instantiate classes using the container. In this case, the container injects constructor dependencies and returns an instance.

```php
use MiladRahimi\PhpContainer\Container;

$container = new Container();
$controller = $container->instantiate(Controller::class);
```

### Type-based and name-based binding

PhpContainer supports typed-based and name-based binding.
The following example demonstrates these types of binding.

```php
use MiladRahimi\PhpContainer\Container;

$container = new Container();

// Type-based binding
$container->singleton(Database::class, MySQL::class);
$container->call(function(Database $database) {
    // ...
});

// Name-based binding
$container->singleton('$number', 666);
$container->call(function($number) {
    echo $number; // 666
});
```

### Error handling

The container might raise the `ContainerException` exception.
It raises when `ReflectionException` raises, no concrete exist for given abstraction, or the container cannot inject parameter values to the concrete constructor or closures.

## License

PhpContainer is created by [Milad Rahimi](https://miladrahimi.com) and released under the [MIT License](http://opensource.org/licenses/mit-license.php).
