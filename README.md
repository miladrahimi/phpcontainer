# PhpContainer
Dependency injection container (IoC) for PHP projects.

## Overview
[Dependency Inversion](https://en.wikipedia.org/wiki/Dependency_inversion_principle) is one of the most important Object-oriented design principles.

[Dependency Injection](https://en.wikipedia.org/wiki/Dependency_injection), [Inversion of control](https://en.wikipedia.org/wiki/Inversion_of_control) and [IoC Container](http://www.codeproject.com/Articles/542752/Dependency-Inversion-Principle-IoC-Container-Depen) are this principle outcomes.

PhpContainer provides a dependency injection container (aka IoC Container) for your PHP projects.

## Installation

You can add PhpContainer to your project via Composer with following command:

```bash
composer require miladrahimi/phpcontainer:3.*
```

## Documentation

### Explicit Binding

Explicit binding means you explicitly bind an abstraction to a concrete (implementation). You can bind them in prototype or singleton modes.

```php
use MiladRahimi\PhpContainer\Container;

Container::singleton(DatabaseInterface::class, MySQL::class);
Container::prototype(MailerInterface::class, MailerService::class);

$database = Container::make(DatabaseInterface::class);
$mailer = Container::make(MailerInterface::class);
```

The container instantiates the implementation only once and returns it whenever you call the make method if you bind it via singleton method, otherwise, it instantiates the implementation on any instantiation request.

Following example demonstrates the differences between singleton binding and prototype binding.

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

You may get implementations from the container even if there is no abstraction for them and you haven't bound them already.

```php
use MiladRahimi\PhpContainer\Container;

// No binding here!

$service = Container::make(Service::class);
```

So you don't need to define abstractions if you only need mocking and (constructor) auto injections. Notice that Service is instantiable and it will raise an error if you request for an abstraction without explicit binding.

 ### Constructor Auto-injection

Implementations can have constructor parameters which have default values or resolvable by the container.

```php
use MiladRahimi\PhpContainer\Container;

class Service implements ServiceInterface {
    public function __constructor(MailInterface $mail, Sms $sms, $name = 'Jack') {
        // ...
    }
}

Container::prototype(MailInterface::class, Mail::class);
Container::prototype(ServiceInterface::class, Service::class);

$service = Container::make(ServiceInterface::class);
```

Well, let's check what will happen! The container wants to create an instance of service, but its constructor needs some parameters, it's ok! The first parameter is the mail interface since it is bound container inject an instance of mail, the second parameter is SMS class, it's not bound but it's insatiable so the container pass an instance of it, the last parameter is a primitive variable and has a default value so the container passes the same default value.

Notice that all the constructor parameters must be bound, insatiable or has a default value otherwise the container cannot create an instance of the concrete.

Constructor auto-injection is also available for implicit bindings.

### Closure as implementation

Following example illustrates how to use closure as concrete.

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

Like constructors in classes, closures can take arguments, the container tries to pass appropriate values.

```php
use MiladRahimi\PhpContainer\Container;

Container::prototype(MailerInterface::class, Mailer::class);
Container::prototype('notifier', function (MailerInterface $mailer) {
    $notifier = new Notifier();
    $notifier->setMailer($mailer);
    
    return $notifier;
});
```

### Object binding

You may bind an abstraction to an object. In this case, singleton binding provides the original object to the user and prototype binding provides a clone of the object every time.

```php
use MiladRahimi\PhpContainer\Container;

$user = new User();
$user->name = 'Milad';

Container::prototype('user', $user);
```

### Exceptions

The container raises exceptions in some cases.

`BindingNotFoundException` raises when you try to make an instance of abstraction while you haven't bound it to any concrete yet.

`ResolutionException` raises when PHP raises `ReflectionException` or the container cannot inject parameters to the concrete constructor.

## License

PhpContainer is created by [Milad Rahimi](http://miladrahimi.com) and released under the [MIT License](http://opensource.org/licenses/mit-license.php).
