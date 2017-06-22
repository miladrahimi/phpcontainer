# PhpContainer

Free PHP dependency injection container (IoC) for neat and powerful projects!

## Overview

[Dependency Inversion](https://en.wikipedia.org/wiki/Dependency_inversion_principle)
is one of the most important Object-oriented design principles.

[Dependency Injection](https://en.wikipedia.org/wiki/Dependency_injection),
[Inversion of control](https://en.wikipedia.org/wiki/Inversion_of_control) and
[IoC Container](http://www.codeproject.com/Articles/542752/Dependency-Inversion-Principle-IoC-Container-Depen)
are this principle outcomes.

PhpContainer provides a dependency injection container (aka IoC Container) for your project.

Due to PHP flexibility, this package is incredibly simple and flexible.

## Installation

You can add PhpContainer to your project via Composer with following command:

```
composer require miladrahimi/phpcontainer:2.*
```

## Getting Started

Following example demonstrates how to create a container and bind a service:

```
use MiladRahimi\PhpContainer\Container;

$container = new Container();
$container->prototype('user', function() {
    $user = new User();
    $mailer = new AppropriateMailer();
    $user->setMailer($mailer);
    return $user;
});
```

Once you have created a service, you can make it using `make()` method as the following example shows:

```
$user = $container->make('user');
$user->setEmail('info@miladrahimi.com');
$user->sendWelcomeEmail();
```

As you can see in code above `Mailer` instance is injected to `$user` already in the container
so it is just ready to use.

## Binding Methods

The mentioned example shows how to bind a service but here we introduce all the ways to bind a service.

```
// Bind by Class Name
$container->singleton('db', 'App\Core\Database');

// Bind by Class Name (Supported by PHP 5.5 and later)
$container->singleton('db', App\Core\Database::class);

// Bind by Object
$container->singleton('db', $database);

// Bind by Closure
$container->singleton('db', function() {
    $db = new Database();
    $db->hostname = 'localhost';
    $db->username = 'root';
    $db->password = 'secret';
    $db->database = 'shop';
    return $db;
});
```

## Singleton Services

Singleton services are bound with `singleton()` method.

Once you call `make()` method, the made service will be stored to be used for next requests.

Singleton services will be unique whole the project lifecycle.
If you change a singleton instance all other instances will be affected.

## Prototype Services

Prototype services are bound with `prototype()` method.

Anytime you call the `make()` method, PhpContainer will make a new instance of the bound service.

```
$user = new User();
$mailer = new Mailer($config);
$user->setMailer($mailer);
$container->prototype("user", $user);
```

## Comparison of Binding Types

The example below shows what difference singleton and prototype have.

```
use MiladRahimi\PhpContainer\ContainerBuilder;

class Test
{
    public $value;
}

$container = new Container();
$container->singleton("testSingleton", Test::class);
$container->prototype("testPrototype", Test::class});

$singleton1 = $container->make("testSingleton");
$singleton1->value = "singleton-1";
$singleton2 = $container->get("testSingleton");
$singleton2->value = "singleton-2";

$prototype1 = $container->make("testPrototype");
$prototype1->value = "prototype-1";
$prototype2 = $container->make("testPrototype");
$prototype2->value = "prototype-2";

print_r($singleton1);
print_r($singleton2);
print_r($prototype1);
print_r($prototype2);
```

And the output is:

```
Test Object
(
    [val] => singleton-2
)
Test Object
(
    [val] => singleton-2
)
Test Object
(
    [val] => prototype-1
)
Test Object
(
    [val] => prototype-2
)
```

## License
PHPConfig is created by [Milad Rahimi](http://miladrahimi.com)
and released under the [MIT License](http://opensource.org/licenses/mit-license.php).
