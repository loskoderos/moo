# Moo

Question is not _what_ is Moo, but _why_ is Moo?

There is time that every PHP developer _has to_ create its own framework. Most of them are crap, this one is no exception. 

So, Moo, the framework, is a spawn of PHP evil created for making life and debugging miserable. It took a couple of hours to create, but can take days to find out what and why works or does not. Like most microframeworks, Moo is quite close to HTTP world, made of bunch of pure PHP classes with no external dependencies. Just router, request, response, boom done.

Of course, it would be easier, faster and wiser to just use any other off shelf framework, but there it is, Moo!

## Why _Moo_?
Why not.

## Why use _Moo_?
Seriously, for 99% of time you should not use this framework. Don't do it for sake of your and other developers wellbeing and mental health. 

The only exception to that I can think of, is when you consider using raw .php scripts somewhere on a server. If that is the case, Moo can actually be useful.

Looking for some decent PHP framework? Go learn Symfony, Laravel or anything that actually has any community around. This one does not have any. Actually, you can try writing _your own_ sacred micro framework, just like Moo to learn and validate your PHP skills.

## Installation
If you really have to, here you go, just use composer.
~~~
composer config minimum-stability dev
composer require loskoderos/gpx-php:dev-master
~~~

## Usage
This is the sample Moo app.
~~~php
<?php
$moo = new Moo\Moo();

$moo->get('\', function () {
    echo "Hello, this is Moo!";
});

$moo();
~~~

## Examples
There are some examples in the `examples` directory.
To run them you can use builtin PHP server.
~~~
php -S 0.0.0.0:8080 examples/hello-world/index.php
~~~

## Documentation
The goal of Moo is simplicity, flexibility and ease of use.

### Concepts
Moo is written in PHP and is closure based. From design perspective it is a front controller, the `Moo\Moo` class exposes a set of standard HTTP methods to bind routing handlers as closures. Additionally, Moo acts as a state container and can be extended with plugins.

All Moo components reside in the PSR-4 `Moo` namespace. The main component is the `Moo\Moo` class. There are three models: `Moo\Request`, `Moo\Response`, `Moo\Route` and the `Moo\Router` that works as dispatcher.

Moo does output buffering, so you can simply output with echo or return a serializable value in the closure.

### Lifecycle
Here is what happends when you call `$moo(...)`:
```mermaid
flowchart TD
    init["Pre request hook, $moo->init(...)"]
    dispatch["Match route, $moo->get(...), $moo->post(...), etc..."]
    error["Run error hook if no route matched request, $moo->error(...)"]
    finish["Post request hook, $moo->finish(...)"]
    flush["Flush output, $moo->flush(...)]
    init --> dispatch
    dispatch --> error
    error --> finish
    finish --> flush
```

### HTTP Methods
You can bind handlers to standard HTTP methods:
- GET: `$moo->get(...)`
- HEAD: `$moo->head(...)`
- POST: `$moo->post(...)`
- PUT:  `$moo->put(...)`
- DELETE: `$moo->delete(...)`
- CONNECT: `$moo->connect(...)`
- OPTIONS: `$moo->options(...)`
- TRACE: `$moo->trace(...)`
- PATCH: `$moo->patch(...)`
You can match multiple methods using `$moo->route(...)`.

### Routing
### Request
### Response
### Plugins

## Testing
Moo is unittested, just run `make run`.

## Contributing
Contributions are welcome, please submit a pull request.

## License
MIT
