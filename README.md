PHP YAML Parser
===============

Motivation
----------

People don’t seem to be looking for content management systems anymore. There are so many blogging services already that
allow them to just write. Personalization and monetization don’t seem to be the main concerns anymore in this era of big
data. Or, if monetization is their only concern, they will tend to leave it up to the services they use, limiting their
ability to choose a web design that fits their personality.

This project is actually an internal feature of my content management system, [Mecha](https://github.com/mecha-cms), but
I decided to make it a stand-alone project now so that other people can use it too. People seem to have a tendency to
look for PHP YAML parsers, far more than their tendency to look for content management systems that fit their needs. So,
this project is also my attempt to drive people who need a PHP YAML parser to my content management system project that
I’m proud of (which is apparently not very popular since people seem to be more interested in static site generators
these days).

Features
--------

### Array

#### List

~~~ yaml
- asdf
- asdf
- asdf
~~~

#### Flow

~~~ yaml
[ asdf, asdf, asdf ]
~~~

### Object

#### Block

~~~ yaml
a: asdf
b: asdf
c: asdf
~~~

#### Flow

~~~ yaml
{ a: asdf, b: asdf, c: asdf }
~~~

### Comment

~~~ yaml
# This is a comment.
~~~

### Scalar

#### Boolean

~~~ yaml
FALSE
~~~

~~~ yaml
False
~~~

~~~ yaml
false
~~~

~~~ yaml
TRUE
~~~

~~~ yaml
True
~~~

~~~ yaml
true
~~~

#### Constant

~~~ yaml
.INF
~~~

~~~ yaml
.Inf
~~~

~~~ yaml
.inf
~~~

~~~ yaml
.NAN
~~~

~~~ yaml
.Nan
~~~

~~~ yaml
.nan
~~~

#### Date

~~~ yaml
2023-09-25
~~~

~~~ yaml
2023-09-25 20:22:42
~~~

~~~ yaml
2023-09-25T20:22:42.025Z
~~~

~~~ yaml
2023-09-25T20:22:42+07:00
~~~

#### Number

##### Float

~~~ yaml
0.5
~~~

~~~ yaml
.5
~~~

##### Float as Exponential Number

~~~ yaml
# Case insensitive
1.2e+34
~~~

##### Integer

~~~ yaml
12
~~~

##### Integer as Hexadecimal

~~~ yaml
# Case insensitive
0xC
~~~

##### Integer as Octal

~~~ yaml
# Case insensitive
0o14
~~~

~~~ yaml
014
~~~

#### Null

~~~ yaml
NULL
~~~

~~~ yaml
Null
~~~

~~~ yaml
null
~~~

~~~ yaml
~
~~~

~~~ yaml
~~~

#### String

#### Block

##### Fold-Style

~~~ yaml
>
  asdf asdf asdf asdf
  asdf asdf asdf asdf

  asdf asdf asdf asdf
~~~

##### Literal-Style

~~~ yaml
|
  asdf asdf asdf asdf
  asdf asdf asdf asdf

  asdf asdf asdf asdf
~~~

#### Double Quote

~~~ yaml
"asdf asdf \"asdf\" asdf"
~~~

#### Single Quote

~~~ yaml
'asdf asdf ''asdf'' asdf'
~~~

#### Plain

~~~ yaml
asdf asdf 'asdf' asdf
~~~

### Tag

Supported built-in tags:

 - `!!binary`
 - `!!bool`
 - `!!float`
 - `!!int`
 - `!!map`
 - `!!null`
 - `!!seq`
 - `!!str`
 - `!!timestamp`

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

Tests
-----

Clone this repository into the root of your web server that supports PHP and then you can open the `test/from.php` and
`test/to.php` file with your browser to see the result and the performance of this converter in various cases.

Tweaks
------

_TODO_

License
-------

This library is licensed under the [MIT License](LICENSE). Please consider
[donating 💰](https://github.com/sponsors/taufik-nurrohman) if you benefit financially from this library.