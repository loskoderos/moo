# Moo

Question is not _what_ is Moo, but _why_ is Moo?

There is time that evety PHP developer _has to_ create its own framework. Most of them are crap, this one is no exception. 

So, Moo, the framework, is a spawn of PHP evil created for making life and debugging miserable. It took a couple of hours to create, but can take days to find out what and why works or does not.

Like most microframeworks, Moo is quite close to HTTP world, made of bunch of pure PHP classes with no external dependencies. Just router, request, response, boom done.

Of course, it would be easier, faster and wiser to just use any other off shelf framework, but there it is, Moo!

## Why _Moo_?
Why not.

## Why use _Moo_?
Seriously, for 99% of time you should not use this framework. Don't do it for sake of your and other developers wellbeing and mental health. 

The only exception to that I can think of, is when you consider using raw .php scripts somewhere on a server. If that is the case, Moo can actually be useful.

Looking for some decent PHP framework? Go learn Symfony, Laravel or anything that actually has any community around. This one does not have any.

Actually, you can try writing _your own_ sacred micro framework, just like Moo to learn and validate your PHP skills.

## Installation
If you really have to, here you go, just use composer.
~~~
composer config minimum-stability dev
composer require loskoderos/gpx-php:dev-master
~~~

## Usage
This is the Moo hello world app.
~~~php
<?php
$moo = new Moo\Moo();

$moo->get('\', function () {
    echo "Hello, this is Moo!";
});

$moo();
~~~

## Documentation
In progress...

## Testing & Development
Moo is unittested, just run `make run`.

## Contributing
Contributions are welcome, please submit a pull request.

## License
MIT
