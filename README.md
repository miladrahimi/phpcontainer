# PhpContainer
Dependency injection container (IoC) for PHP projects.

## Overview
[Dependency Inversion](https://en.wikipedia.org/wiki/Dependency_inversion_principle) is one of the most important Object-oriented design principles.

[Dependency Injection](https://en.wikipedia.org/wiki/Dependency_injection), [Inversion of control](https://en.wikipedia.org/wiki/Inversion_of_control) and [IoC Container](http://www.codeproject.com/Articles/542752/Dependency-Inversion-Principle-IoC-Container-Depen) are the outcomes for this principle.

PhpContainer provides a dependency injection container (aka IoC Container) for your PHP projects.

## Installation

You can add PhpContainer to your project via Composer with following command:

```bash
composer require miladrahimi/phpcontainer:3.*
```

## Documentation

### Explicit Binding

Explicit binding means explicitly bind an abstraction to a concrete (implementation).
You can bind via singleton and prototype methods.

```php
use MiladRahimi\PhpContainer\Container;

Container::singleton(DatabaseInterface::class, MySQL::class);
Container::prototype(MailerInterface::class, MailTrap::class);

$database = Container::make(DatabaseInterface::class);
$mailer = Container::make(MailerInterface::class);
```

The container instantiates implementation classes only once and returns them whenever you call the make method if you bind them via singleton method, otherwise, it instantiates implementation classes on any instantiation request.

Following example demonstrates the differences between singleton and prototype binding.

```php
use MiladRahimi\PhpContainer\Container;

Container::prototype(InterfaceA::class, ClassA::class);
Container::singleton(InterfaceB::class, ClassB::class);

$a1 = Container::make(InterfaceA::class);
$a1->name = 'Something';

$a2 = Container::make(InterfaceA::class);
echo $a2->name; // NULL

$b1 = Container::make(InterfaceB::class);
$b1->name = 'Something';

$b2 = Container::make(InterfaceB::class);
echo $b2->name; // 'Something'

```

### Implicit Binding

You can retrieve implementation classes from the container instead of using the new keyword to instantiate them. It could be helpful for mocking orphan implementation classes.

```php
use MiladRahimi\PhpContainer\Container;

// No binding here!

$database = Container::make(MySQL::class);
```

### Constructor Auto-injection

Implementation classes can have constructor parameters which have default values or resolvable by the container.

```php
use MiladRahimi\PhpContainer\Container;

class Notifier implements NotifierInterface {
    public function __constructor(MailInterface $mail, Sms $sms, $sender = 'Awesome') {
        // ...
    }
}

Container::prototype(MailInterface::class, MailTrap::class);
Container::prototype(NotifierInterface::class, Notifier::class);

$notifier = Container::make(NotifierInterface::class);
```

Well, let's check what will the container do! The container is supposed to create an instance of Notifier, its constructor has some arguments, it's ok! The first argument is MailInterface, it is bound to MailTrap so the container will inject an instance of MailTrap, the second argument is SMS class, it's not bound to any implementation but it's insatiable so the container pass an instance of itself, the last argument is a primitive variable and has a default value so the container passes the same default value.

Constructor auto-injection is also available for implicit bindings.

### Closure as implementation

Following example illustrates how to use closure as implementation.

```php
use MiladRahimi\PhpContainer\Container;

Container::prototype('time-prototype', function () {
    return microtime(true);
});

Container::singleton('time-singleton', function () {
    return microtime(true);
});

$tp1 = Container::make('time-prototype');
$tp2 = Container::make('time-prototype');
echo $tp1 == $tp2; // FALSE

$ts1 = Container::make('time-singleton');
$ts2 = Container::make('time-singleton');
echo $ts1 == $ts2; // TRUE
```

Just like class constructors, closures are also able to have arguments, the container will try to inject/pass appropriate implementations/values.

```php
use MiladRahimi\PhpContainer\Container;

Container::prototype(MailerInterface::class, MailTrap::class);
Container::prototype('notifier', function (MailerInterface $mailer) {
    $notifier = new Notifier();
    $notifier->setMailer($mailer);
    
    return $notifier;
});
```

### Object binding

You can also bind an abstraction to an object. In this case, singleton binding is used to release the original object on resolve and prototype binding is used to release a clone of the object each time.

```php
use MiladRahimi\PhpContainer\Container;

$user = new User();
$user->name = 'Milad';

Container::prototype('user', $user);
```

### Exceptions

The container might raise the following exceptions:

`BindingNotFoundException` raises when you try to make an abstraction while you haven't bound it to any concrete yet.

`ResolutionException` raises when PHP raises `ReflectionException` or the container cannot inject parameter values to the concrete constructor or closure.

## License

PhpContainer is created by [Milad Rahimi](http://miladrahimi.com) and released under the [MIT License](http://opensource.org/licenses/mit-license.php).
