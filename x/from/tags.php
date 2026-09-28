<?php

require __DIR__ . '/../../from.php';

// <https://symfony.com/doc/7.3/reference/formats/yaml.html#symfony-specific-features>
$tags = [
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

echo '<pre>';
echo htmlspecialchars(var_export(x\y_a_m_l\from(file_get_contents(__DIR__ . '/tags.yaml'), ['lot' => $tags]), true));
echo '</pre>';