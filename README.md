# PHPContainer
Free PHP Dependency Injection Container for neat and powerful projects!

## Overview
[Dependency Inversion](https://en.wikipedia.org/wiki/Dependency_inversion_principle)
is one of the most important Object Oriented design principles.

[Dependency Injection](https://en.wikipedia.org/wiki/Dependency_injection),
[Inversion of control](https://en.wikipedia.org/wiki/Inversion_of_control) and
[IoC Container](http://www.codeproject.com/Articles/542752/Dependency-Inversion-Principle-IoC-Container-Depen)
are this principle outcomes.

PHPContainer provides a Dependency Injection Container (aka IoC Container) for your project.

Due to PHP flexibility, this package is incredibly simple and flexible.

### Installation
#### Using Composer (Recommended)
Read
[How to use composer in php projects](http://miladrahimi.com/blog/2015/04/12/how-to-use-composer-in-php-projects)
article if you are not familiar with [Composer](http://getcomposer.org).

Run following command in your project root directory:

```
composer require miladrahimi/phpcontainer
```

#### Manually
You may use your own autoloader as long as it follows [PSR-0](http://www.php-fig.org/psr/psr-0) or
[PSR-4](http://www.php-fig.org/psr/psr-4) standards.
Just put `src` directory contents in your vendor directory.

### Getting Started
PHPContainer offers a completely free environment to injections or whatever is needed.

Following Example shows how to create a container and define a service into it:

```
use MiladRahimi\PHPContainer\ContainerBuilder;

$container = ContainerBuilder::build();
$container->define("user", function() {
    $user = new User();
    $mailer = new Mailer();
    $user->setMailer($mailer);
    return $user;
});
```

Once you create a service, you can get it using `get()` method as the following example shows:

```
$user = $container->make("user");
$user->setEmail("info@miladrahimi.com");
$user->sendWelcomeEmail();
```
Now a `Mailer` instance is injected to `$user` already in the container and it is just ready to use.

### Normal Services
Normal services will be defined via `define()` method.

Whenever you call `get()` method, the injection body (closure) will be invoked to return new service.

### Singleton Services
Singleton services will be defined via `singleton()` method.

Once you call `get()` method, the returned service will be stored to be used for next requests.

Singleton services will be unique in the project.
If you change it all service instance in the project will be affected.

### Prototype Services
Prototype service will be defined via `prototype()` method.

Once you call `get()` method, the returned service will be stored to be cloned for next requests.

Prototype services constructor and injections (initialization) will be run once.
Whenever service is requested a copy of initialized instance will be returned.

### Instance Services
You may inject and prepare instance already.
You can use `instance()` method to register prepared instance as a service.

```
$user = new User();
$mailer = new Mailer();
$user->setMailer($mailer);
$container->instance("user", $user);
```

### Comparison Of Methods
To understand the differences between normal, singleton and prototype you may see following test.

```
use MiladRahimi\PHPContainer\ContainerBuilder;

class Test
{
    public $rnd;
    public $val;

    public function __construct()
    {
        $this->rnd = mt_rand(0, 999);
    }
}

$container = ContainerBuilder::build();
$container->define("tn", function () {
    return new Test();
});
$container->singleton("ts", function () {
    return new Test();
});
$container->prototype("tp", function () {
    return new Test();
});

$normal1 = $container->get("tn");
$normal1->val = "normal-1";
$normal2 = $container->get("tn");
$normal2->val = "normal-2";

$singleton1 = $container->get("ts");
$singleton1->val = "singleton-1";
$singleton2 = $container->get("ts");
$singleton2->val = "singleton-2";

$prototype1 = $container->get("tp");
$prototype1->val = "prototype-1";
$prototype2 = $container->get("tp");
$prototype2->val = "prototype-2";

print_r($normal1);
print_r($normal2);
print_r($singleton1);
print_r($singleton2);
print_r($prototype1);
print_r($prototype2);
```

And the output is:

```

Test Object
(
    [rnd] => 127
    [val] => normal-1
)
Test Object
(
    [rnd] => 507
    [val] => normal-2
)
Test Object
(
    [rnd] => 759
    [val] => singleton-2
)
Test Object
(
    [rnd] => 759
    [val] => singleton-2
)
Test Object
(
    [rnd] => 234
    [val] => prototype-1
)
Test Object
(
    [rnd] => 234
    [val] => prototype-2
)
```

As the example illustrates singleton service will be unique.
Wherever you change it, it will be modified in all other places.

Prototype service injection body (closure) will be invoked once.
But the service in not unique.

## License
PHPConfig is created by [Milad Rahimi](http://miladrahimi.com)
and released under the [MIT License](http://opensource.org/licenses/mit-license.php).
