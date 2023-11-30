[![Latest Stable Version](https://poser.pugx.org/miladrahimi/phpcontainer/v)](//packagist.org/packages/miladrahimi/phpcontainer)
[![Total Downloads](https://poser.pugx.org/miladrahimi/phpcontainer/downloads)](//packagist.org/packages/miladrahimi/phpcontainer)
[![Build](https://github.com/miladrahimi/phpcontainer/actions/workflows/ci.yml/badge.svg)](https://github.com/miladrahimi/phpcontainer/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/miladrahimi/phpcontainer/graph/badge.svg?token=LFW0H0GSMQ)](https://codecov.io/gh/miladrahimi/phpcontainer)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/miladrahimi/phpcontainer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/miladrahimi/phpcontainer/?branch=master)
[![License](https://poser.pugx.org/miladrahimi/phpcontainer/license)](//packagist.org/packages/miladrahimi/phpcontainer)

# PhpContainer

A dependency injection (Inversion of Control) container written in PHP programming language, compliant with PSR-11 standards.

Features:
* Singleton, transient, and closure bindings
* Explicit and implicit bindings
* Typed and named bindings
* Automatic injection

## Overview

[Dependency Inversion](https://en.wikipedia.org/wiki/Dependency_inversion_principle) is a fundamental concept in Object-oriented design.

It leads to important ideas like [Dependency Injection](https://en.wikipedia.org/wiki/Dependency_injection), [Inversion of Control](https://en.wikipedia.org/wiki/Inversion_of_control), and the creation of an [IoC Container](http://www.codeproject.com/Articles/542752/Dependency-Inversion-Principle-IoC-Container-Depen).

For PHP projects, there's the PhpContainer, a handy tool that provides a dependency injection container (IoC Container) conforming to [PSR-11 standards](https://www.php-fig.org/psr/psr-11).

## Installation

To integrate PhpContainer into your project, use the following Composer command:

```bash
composer require miladrahimi/phpcontainer:5.*
```

## Documentation

### Explicit Binding

Explicit binding involves directly linking an abstraction to a concrete implementation.
This binding can be achieved by using the `singleton()`, `transient()`, and `closure()` methods.

```php
use MiladRahimi\PhpContainer\Container;

$container = new Container();

$container->singleton(DatabaseInterface::class, MySQL::class);
$container->transient(MailerInterface::class, MailTrap::class);
$container->closure('sum', function($a, $b) { return $a + $b; });

$database = $container->get(DatabaseInterface::class); // An instance of MySQL
$mailer = $container->get(MailerInterface::class); // An instance of MailTrap
$sum = $container->get('sum'); // A closure: $sum(6, 7) => 13
```

#### Binding Methods

* Singleton binding: The container creates the concrete only once and returns it whenever needed.
* Transient binding: The container clones or creates brand-new concrete each time you need it.
* Closure binding: Only for closures. It prevents the container from calling the closure (the default behavior).

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

When the container needs a class without a specific binding, it tries to create an instance.
In the example below, it instantiates the `MySQL` class in the provided code.
But if it encounters an abstract class or an interface that can't be instantiated directly, an error occurs.

```php
use MiladRahimi\PhpContainer\Container;

$container = new Container();

// No (explicit) binding here!

$database = $container->get(MySQL::class);
```

### Binding to Objects

You can connect abstracts to specific objects.
Using singleton binding gives you the original object when required, while transient binding offers a fresh copy of the object every time you ask for it.

```php
use MiladRahimi\PhpContainer\Container;

$user = new User();
$user->name = 'Milad';

$container = new Container();

$container->singleton('user', $user);
// OR
$container->transient('user', $user);
```

### Constructor Auto-injection

Concrete classes might contain constructor parameters that either possess default values or can be resolved by the container.

```php
use MiladRahimi\PhpContainer\Container;

class Notifier implements NotifierInterface
{
    public MailInterface $mail;
    public Vonage $vonage;
    public string $sender;

    public function __constructor(MailInterface $mail, Vonage $vonage, $sender = 'PhpContainer')
    {
        $this->mail = $mail;
        $this->vonage = $vonage;
        $this->sender = $sender;
    }
}

$container = new Container();
$container->transient(MailInterface::class, MailTrap::class);
$container->transient(NotifierInterface::class, Notifier::class);

$notifier = $container->get(NotifierInterface::class);
print_r($notifier->mail);   // $mail would be an instnace of MailTrap (explicit binding)
print_r($notifier->vonage); // $vonage would be an instnace of Vonage (implicit binding)
print_r($notifier->sender); // $sender would be "PhpContainer" (default value)
```

### Binding Using Closure

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

In singleton binding, the container executes the Closure once and retrieves the result whenever needed.
Conversely, in transient binding, the container invokes the Closure each time it's required.
If you intend to bind an abstraction to a Closure without immediate invocation by the container, you can use the `closure()` method instead.

### Resolving Using Closure

You have the option to use the `call` method, allowing the container to execute the provided function or closure and resolve its arguments.

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

### Type-based and name-based binding

PhpContainer supports typed-based and name-based bindings.
The following example demonstrates these types of bindings.

```php
use MiladRahimi\PhpContainer\Container;

$container = new Container();

// Type-based binding
$container->singleton(Database::class, MySQL::class);
$container->call(function(Database $database) {
    $database->ping();
});

// Name-based binding
$container->singleton('$number', 666);
$container->call(function($number) {
    echo $number; // 666
});
```

### Error handling

The `ContainerException` might be raised by the container for several reasons.
It can arise when a `ReflectionException` occurs, indicating a missing concrete implementation for a provided abstraction.
Additionally, this exception occures when the container cannot inject parameter values into concrete constructors or closures.

## License

PhpContainer is created by [Milad Rahimi](https://miladrahimi.com) and released under the [MIT License](http://opensource.org/licenses/mit-license.php).
