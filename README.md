PHP YAML Parser
===============

![from.php] ![to.php]

[from.php]: https://img.shields.io/github/size/taufik-nurrohman/y-a-m-l/from.php?branch=main&color=%234f5d95&label=from.php&labelColor=%231f2328&style=flat-square
[to.php]: https://img.shields.io/github/size/taufik-nurrohman/y-a-m-l/to.php?branch=main&color=%234f5d95&label=to.php&labelColor=%231f2328&style=flat-square

Motivation
----------

People don’t seem to be looking for content management systems anymore. There are so many blogging services already that
allow them to just write. Personalization and monetization don’t seem to be the main concerns anymore in this era of big
data. Or, if monetization is their only concern, they will tend to leave it up to the services they use, sacrificing
their freedom to pick a web design that fits their personality.

This project is actually an internal feature of my content management system, [Mecha](https://github.com/mecha-cms), but
I decided to make it a stand-alone project now so that other people can use it too. People seem to have a tendency to
look for PHP YAML parsers, far more than their tendency to look for content management systems. So, this project is also
my attempt to drive people who need a PHP YAML parser to my content management system project that I’m proud of (which
is apparently not very popular since people seem to be more interested in static site generators these days).

<picture>
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/taufik-nurrohman/y-a-m-l/assets/1669261/42fd0ad8-2421-4e37-83cc-c1ec456631ec">
  <source media="(prefers-color-scheme: light)" srcset="https://github.com/taufik-nurrohman/y-a-m-l/assets/1669261/e8708ad9-1e41-4b1f-94ea-4d8aabb0ded7">
  <img alt="Why?" src="https://github.com/taufik-nurrohman/y-a-m-l/assets/1669261/e8708ad9-1e41-4b1f-94ea-4d8aabb0ded7">
</picture>

Why should you choose my YAML parser over any other similar YAML parser out there?

 - [mustangostang/spyc](https://github.com/mustangostang/spyc) consists of one PHP file which is 35.1 KB in size and
   contains a total of 1186 lines of code [since the time of writing][1]. It is out of date (only supports YAML 1.0 and
   is buggy [in][2] [various][3] [cases][4]) and is still comparatively bigger than my YAML parser.
 - [symfony/yaml](https://github.com/symfony/yaml) prioritizes reliability and stability for use in large-scale
   applications. This library contains a lot of dependencies that will make your application overly bloated if your main
   goal is simply to convert YAML syntax to PHP data.
 - [yaml](https://www.php.net/book.yaml) requires that your server allows you to install the PHP extension. In terms
   of conversion speed, it should be faster because it uses [C](https://pyyaml.org/wiki/LibYAML), but it’s not
   guaranteed to be available on all PHP servers in the world that you can rent, considering that this PHP extension is
   not bundled with PHP by default.

 [1]: https://github.com/mustangostang/spyc/blob/b066393167c8701d1b11a3828dd08a550b3d9fa1/Spyc.php
 [2]: https://github.com/mustangostang/spyc/issues/30
 [3]: https://github.com/mustangostang/spyc/issues/53
 [4]: https://github.com/mustangostang/spyc/issues/54

Features
--------

### Anchor

~~~ yaml
asdf-1: &asdf 1
asdf-2: *asdf
asdf-3: *asdf
asdf-4: *asdf
~~~

~~~ yaml
asdf-1: &asdf
  a: asdf
  s: asdf
  d: asdf
  f: asdf
asdf-2: *asdf
asdf-3: *asdf
asdf-4: *asdf
~~~

> [!NOTE]
>
> This anchor feature only duplicates the values and does not perform proper memory management by linking the anchored
> values to their aliases as [references](https://www.php.net/manual/en/language.references.return.php), for simplicity
> [^1].

[^1]: See issue: [Anchors as “Real” References](https://github.com/taufik-nurrohman/y-a-m-l/issues/1)

### Array

#### List

~~~ yaml
- asdf
- asdf
- asdf
- asdf
~~~

#### Flow

~~~ yaml
[ asdf, asdf, asdf, asdf ]
~~~

### Map

~~~ yaml
? asdf
  asdf
: asdf
~~~

> [!NOTE]
>
> This is an experimental feature and I don’t plan to make it official. PHP array does not support storing complex data
> as its key. Even the [`SplObjectStorage`](https://www.php.net/class.splobjectstorage) and
> [`WeakMap`](https://www.php.net/class.weakmap) classes do only support object as their key, so this feature will be
> impossible to achieve. Current behavior is to convert this complex data into a serialized data. To mark it as a
> complex key, a `null` character is prepended and appended to it. This does not apply to float, integer, and string
> data types:
>
> ##### Input
>
> ~~~ yaml
> ? 12.3
> : asdf
> ? 1234
> : asdf
> ? asdf
> : asdf
> ? asdf:
>   - asdf
>   - asdf
>   - asdf
> : asdf
> ? false
> : asdf
> ? null
> : asdf
> ? true
> : asdf
> ~~~
>
> ##### Output
>
> ~~~ php
> return (object) array(
>     '12.3' => 'asdf',
>     '1234' => 'asdf',
>     'asdf' => 'asdf',
>     "\0" . 'O:8:"stdClass":1:{s:4:"asdf";a:3:{i:0;s:4:"asdf";i:1;s:4:"asdf";i:2;s:4:"asdf";}}' . "\0" => 'asdf',
>     "\0" . 'b:0;' . "\0" => 'asdf',
>     "\0" . 'N;' . "\0" => 'asdf',
>     "\0" . 'b:1;' . "\0" => 'asdf'
> );
> ~~~
>
> I wouldn’t recommend you to have this kind of syntax in your YAML document, even though this parser is
> able to read some of it. By the time you get there, you may already be using a better YAML parser due to various bugs.

### Object

#### Block

~~~ yaml
a: asdf
s: asdf
d: asdf
f: asdf
~~~

#### Flow

~~~ yaml
{ a: asdf, s: asdf, d: asdf, f: asdf }
~~~

### Comment

~~~ yaml
# asdf
~~~

### Scalar

#### Boolean [^2]

~~~ yaml
false
~~~

~~~ yaml
true
~~~

#### Constant [^2]

~~~ yaml
.INF
~~~

~~~ yaml
.NAN
~~~

#### Date [^2]

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

##### Float as Exponential Number [^2]

~~~ yaml
1.2e+34
~~~

##### Integer

~~~ yaml
12
~~~

##### Integer as Hexadecimal [^2]

~~~ yaml
0xC
~~~

##### Integer as Octal [^2]

~~~ yaml
0o14
~~~

~~~ yaml
014
~~~

#### Null [^2]

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

##### Fold

~~~ yaml
> # Clip (default)
  asdf asdf asdf asdf
  asdf asdf asdf asdf

  asdf asdf asdf asdf
~~~

~~~ yaml
>4 # Clip and indent with 2 space(s)
      asdf asdf asdf asdf
      asdf asdf asdf asdf

      asdf asdf asdf asdf
~~~

~~~ yaml
>+ # Keep
  asdf asdf asdf asdf
  asdf asdf asdf asdf

  asdf asdf asdf asdf
~~~

~~~ yaml
>+4 # Keep and indent with 2 space(s)
      asdf asdf asdf asdf
      asdf asdf asdf asdf

      asdf asdf asdf asdf
~~~

~~~ yaml
>- # Strip
  asdf asdf asdf asdf
  asdf asdf asdf asdf

  asdf asdf asdf asdf
~~~

~~~ yaml
>-4 # Strip and indent with 2 space(s)
      asdf asdf asdf asdf
      asdf asdf asdf asdf

      asdf asdf asdf asdf
~~~

##### Literal

~~~ yaml
| # Clip (default)
  asdf asdf asdf asdf
  asdf asdf asdf asdf

  asdf asdf asdf asdf
~~~

~~~ yaml
|4 # Clip and indent with 2 space(s)
      asdf asdf asdf asdf
      asdf asdf asdf asdf

      asdf asdf asdf asdf
~~~

~~~ yaml
|+ # Keep
  asdf asdf asdf asdf
  asdf asdf asdf asdf

  asdf asdf asdf asdf
~~~

~~~ yaml
|+4 # Keep and indent with 2 space(s)
      asdf asdf asdf asdf
      asdf asdf asdf asdf

      asdf asdf asdf asdf
~~~

~~~ yaml
|- # Strip
  asdf asdf asdf asdf
  asdf asdf asdf asdf

  asdf asdf asdf asdf
~~~

~~~ yaml
|-4 # Strip and indent with 2 space(s)
      asdf asdf asdf asdf
      asdf asdf asdf asdf

      asdf asdf asdf asdf
~~~

#### Flow

##### Double Quote

~~~ yaml
"asdf asdf \"asdf\" asdf"
~~~

##### Single Quote

~~~ yaml
'asdf asdf ''asdf'' asdf'
~~~

##### Plain

~~~ yaml
asdf asdf 'asdf' asdf
~~~

### Tag

These [built-in tags](https://yaml.org/type) are supported:

 - `!!binary`
 - `!!bool`
 - `!!float`
 - `!!int`
 - `!!map`
 - `!!null`
 - `!!seq`
 - `!!str`
 - `!!timestamp`

Users who want to add their own custom tags can define them in the `$lot` parameter of the `from()` function as a
closure. Note that this parameter is provided as a live reference, so you cannot put an array of tag definitions
directly into it. Instead, you must put it into a temporary variable:

~~~ php
// <https://symfony.com/doc/7.0/reference/formats/yaml.html#symfony-specific-features>
$lot = [
    '!php/const' => static function ($value) {
        if (is_string($value) && defined($value)) {
            return constant($value);
        }
        return null;
    },
    '!php/enum' => static function ($value) {
        if (!is_string($value)) {
            return null;
        }
        [$a, $b] = explode('::', $value, 2);
        if ('->value' === substr($b, -7)) {
            return (new ReflectionEnumBackedCase($a, substr($b, 0, -7)))->getBackingValue();
        }
        return (new ReflectionEnumBackedCase($a, $b))->getValue();
    },
    '!php/object' => static function ($value) {
        return is_string($value) ? unserialize($value) : null;
    }
];

$value = from_yaml($value, false, $lot);

// Here, the `$lot` variable will probably contain anchors as well. Anchor data will have a key started with ‘&’.

var_dump($lot, $value);
~~~

[^2]: To simplify the parsing process, the parser does not care about case sensitivity.

Usage
-----

This converter can be installed using [Composer](https://packagist.org/packages/taufik-nurrohman/y-a-m-l), but it
doesn’t need any other dependencies and just uses Composer’s ability to automatically include files. Those of you who
don’t use Composer should be able to include the `from.php` and `to.php` files directly into your application without
any problems.

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

var_export(from_yaml('asdf: asdf')); // Returns `(object) ['asdf' => 'asdf']`
~~~

### Using File

Require the `from.php` and `to.php` files in your application:

~~~ php
<?php

use function x\y_a_m_l\from as from_yaml;
use function x\y_a_m_l\to as to_yaml;

require 'from.php';
require 'to.php';

var_export(from_yaml('asdf: asdf')); // Returns `(object) ['asdf' => 'asdf']`
~~~

Options
-------

~~~ php
/**
 * Convert YAML string to PHP data.
 *
 * @param null|string $value Your YAML string.
 * @param bool $array If this option is set to `true`, PHP object will becomes associative array.
 * @param array $lot Currently used to store anchor(s) and custom tag(s)
 * @return mixed
 */
from(?string $value, bool $array = false, array &$lot = []): mixed;
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

Your YAML content is represented as variable `$value`. If you modify the content before the function `from_yaml()` is
called, it means that you modify the YAML content before it is converted. If you modify the content after the function
`from_yaml()` is called, it means that you modify the results of the YAML conversion.

### Globally Reusable Functions

To make `from_yaml()` and `to_yaml()` functions reusable globally, use this method:

~~~ php
<?php

require 'from.php';
require 'to.php';

// Or, if you are using Composer…
// require 'vendor/autoload.php';

function from_yaml(...$v) {
    return x\y_a_m_l\from(...$v);
}

function to_yaml(...$v) {
    return x\y_a_m_l\to(...$v);
}
~~~

### Document

This converter does not support multiple document feature in one YAML file, but can be supported with a little effort:

~~~ php
// Ensure line break after `---` and `...`
$value = preg_replace('/^(-{3}|[.]{3})\s+/m', '$1' . "\n", $value);

// Remove `---\n` prefix if any
if (0 === strpos($value, "---\n")) {
    $value = substr($value, 4);
}

$values = [];
foreach (explode("\n---\n", $value . "\n") as $v) {
    // Remove everything after `...`
    $v = explode("\n...\n", $v . "\n", 2)[0];
    $values[] = from_yaml($v);
}

var_dump($values);
~~~

### Variable

There are several ways to declare variables in YAML, and all of them are not standard. The most common are variables
with a format like `{{ var }}`. To add a variable feature, you need to convert the variable to a YAML value before
parsing the data:

~~~ php
$variables = [
    'var_1' => 'asdf',
    'var_2' => true,
    'var_3' => 1,
    'var_4' => 1.5
];

if (false !== strpos($value, '{{')) {
    $value = preg_replace_callback('/"\{\{\s*[a-z]\w*\s*\}\}"|\'\{\{\s*[a-z]\w*\s*\}\}\'|\{\{\s*[a-z]\w*\s*\}\}/', static function ($m) use ($variables) {
        $variable = $m[0];
        // `"{{ var }}"`
        if ('"' === $variable[0] && '"' === substr($variable, -1)) {
            $variable = substr($variable, 1, -1);
        }
        // `'{{ var }}'`
        if ("'" === $variable[0] && "'" === substr($variable, -1)) {
            $variable = substr($variable, 1, -1);
        }
        // Trim variable from `{{` and `}}`
        $variable = trim(substr($variable, 2, -2));
        // Get the variable value if available, default to `null`
        $variable = $variables[$variable] ?? null;
        // Return the variable value as YAML string
        return to_yaml($variable);
    }, $value);
}

$value = from_yaml($value);

var_dump($value);
~~~

License
-------

This library is licensed under the [MIT License](LICENSE). Please consider
[donating 💰](https://github.com/sponsors/taufik-nurrohman) if you benefit financially from this library.