PHP YAML Parser
===============

Motivation
----------

People don’t seem to be looking for content management systems anymore. There are so many blogging services already that
allow them to just write. Personalization and monetization don’t seem to be the main concerns anymore in this era of big
data. Or, if monetization is their only concern, they will tend to leave it up to the services they use, limiting their
ability to choose a web design that fits their personality.

This project is actually an internal feature of my content management system [Mecha](https://github.com/mecha-cms), but
I decided to make it a stand-alone project so that other people can use it too. People also still seem to have a
tendency to look for PHP YAML parsers, far more than their tendency to look for content management systems that fit
their needs. So, this project is also my attempt to drive people who need a PHP YAML parser to my content management
system project that I’m proud of (which is apparently not very popular since people seem to be more interested in static
site generators these days).

Features
--------

_TODO_

Usage
-----

### Using Composer

From the command line interface, navigate to your project folder then run this command:

~~~ sh
composer require taufik-nurrohman/y-a-m-l
~~~

Require the generated auto-loader file in your application:

~~~ php
<?php

use function x\y_a_m_l\from as from_yaml;
use function x\y_a_m_l\to as to_yaml;

require 'vendor/autoload.php';

echo from_yaml('asdf: asdf'); // Returns `(object) ['asdf' => 'asdf']`
~~~

### Using File

Require the `from.php` and `to.php` files in your application:

~~~ php
<?php

use function x\y_a_m_l\from as from_yaml;
use function x\y_a_m_l\to as to_yaml;

require 'from.php';
require 'to.php';

echo from_yaml('asdf: asdf'); // Returns `(object) ['asdf' => 'asdf']`
~~~

Options
-------

~~~ php
/**
 * Convert YAML string to PHP data.
 *
 * @param null|string $value Your YAML string.
 * @param bool $array If this option is set to `true`, PHP object will becomes associative array.
 * @return mixed
 */
from(?string $value, bool $array = false): mixed;
~~~

~~~ php
/**
 * Convert PHP data to YAML string.
 *
 * @param mixed $value Your PHP data.
 * @param bool|int|string $dent Specify the indent size or character(s).
 * @return null|string
 */
to(mixed $value, bool|int|string $dent = true): ?string;
~~~