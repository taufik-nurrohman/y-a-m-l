<?php

namespace x\y_a_m_l {
    function from(?string $value, $array = false, array &$lot = []) {
        if ("" === ($value = \trim($raw = $value ?? ""))) {
            return null;
        }
        // Normalize line break(s)
        $value = \strtr($value, [
            "\r\n" => "\n",
            "\r" => "\n"
        ]);
        // Remove comment(s)
        if ("" === ($value = \trim(from\c($value), "\n"))) {
            return null;
        }
        if (\array_key_exists($value, $var = [
            "''" => "",
            '""' => "",
            '!!binary' => \base64_decode(""),
            '!!bool' => false,
            '!!float' => 0.0,
            '!!int' => 0,
            '!!map' => (object) [],
            '!!null' => null,
            '!!seq' => [],
            '!!str' => "",
            '!!timestamp' => new \DateTime,
            '+.INF' => \INF,
            '+.Inf' => \INF,
            '+.NAN' => \NAN,
            '+.Nan' => \NAN,
            '+.inf' => \INF,
            '+.nan' => \NAN,
            '-.INF' => -\INF,
            '-.Inf' => -\INF,
            '-.NAN' => -\NAN,
            '-.Nan' => -\NAN,
            '-.inf' => -\INF,
            '-.nan' => -\NAN,
            '.INF' => \INF,
            '.Inf' => \INF,
            '.NAN' => \NAN,
            '.Nan' => \NAN,
            '.inf' => \INF,
            '.nan' => \NAN,
            'FALSE' => false,
            'False' => false,
            'NULL' => null,
            'Null' => null,
            'TRUE' => true,
            'True' => true,
            '[]' => [],
            'false' => false,
            'null' => null,
            'true' => true,
            '{}' => $array ? [] : (object) [],
            '~' => null
        ])) {
            return $var[$value];
        }
        $str = '\'(?>\'\'|[^\'])*\'|"(?>[^"\\\\]|\\\\.)*"';
        if ("'" === $value[0] && "'" === \substr($value, -1)) {
            return from\f(\strtr(\substr($value, 1, -1), [
                "''" => "'"
            ]), false);
        }
        if ('"' === $value[0] && '"' === \substr($value, -1)) {
            try {
                $value = \json_decode($value, false, 1, \JSON_THROW_ON_ERROR);
            } catch (\Throwable $e) {
                $value = \strtr(\substr($value, 1, -1), [
                    '\"' => '"'
                ]);
            }
            return from\f($value, false);
        }
        // Fold-style or literal-style value
        if (false !== \strpos('>|', $value[0])) {
            [$rule, $content] = \array_replace(["", ""], \explode("\n", from\c($raw), 2));
            $dent = \strspn(\trim($content, "\n"), ' ');
            $content = \substr(\strtr("\n" . $content, [
                "\n" . \str_repeat(' ', $dent) => "\n"
            ]), 1);
            if (isset($rule[1])) {
                $cut = \substr($rule, -1);
                // `>4`
                if (\is_numeric($cut)) {
                    $cut = "";
                    $dent = (int) \substr($rule, 1);
                // `>4+`
                } else {
                    $dent = (int) \substr($rule, 1, -1);
                }
            // `>`
            } else {
                $cut = "";
                $dent = 0;
            }
            if ("" !== $cut && false === \strpos('+-', $cut)) {
                return null; // :(
            }
            if ('+' !== $cut) {
                $content = \rtrim($content) . ("" === $cut ? "\n" : "");
            }
            if ('>' === $rule[0]) {
                $content = from\f($content);
            }
            if ($dent > 0) {
                $d = \str_repeat(' ', $dent);
                $content = \substr(\strtr(\strtr("\n" . $content, [
                    "\n" => "\n" . $d
                ]), [
                    "\n" . $d . "\n" => "\n\n"
                ]), 1);
            }
            return $content;
        }
        if ('[' === $value[0] && ']' === \substr($value, -1) || '{' === $value[0] && '}' === \substr($value, -1)) {
            $out = "";
            // Validate to JSON
            foreach (\preg_split('/\s*(#[^\n]+|' . $str . '|[\[\]\{\}:,])\s*/', $value, -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY) as $v) {
                if ('#' === $v[0]) {
                    continue;
                }
                if ('~' === $v) {
                    $out .= 'null';
                    continue;
                }
                if ('FALSE' === $v || 'NULL' === $v || 'TRUE' === $v || 'False' === $v || 'Null' === $v || 'True' === $v || 'false' === $v || 'null' === $v || 'true' === $v) {
                    $out .= ':' === \substr($out, -1) ? \strtolower($v) : '"' . $v . '"';
                    continue;
                }
                if (\is_numeric($v)) {
                    $out .= false !== \strpos(',:[', \substr($out, -1)) ? $v : '"' . $v . '"';
                    continue;
                }
                if (false !== \strpos(',:[]{}', $v)) {
                    $out .= $v;
                    continue;
                }
                $out .= false !== \strpos(',:[]{}', $v) ? $v : \json_encode(from($v, $array, $lot), false, 1);
            }
            // `{1:a,2:b,3:c}`
            $out = \preg_replace('/([+-]?\d*[.]?\d+):/', '"$1":', $out);
            return \json_decode(\strtr(\strtr($out, [
                // `[1,2,3,]`
                ',]' => ']',
                // `{a:1,b:2,c:3,}`
                ',}' => '}',
            ]), [
                // `{a:,b:0}`
                ':,' => ':null,',
                // `{a:0,b:}`
                ':}' => ':null}'
            ])) ?? $value;
        }
        // A tag
        if ('!' === $value[0]) {
            [$tag, $content] = \preg_split('/\s+/', $value, 2);
            $value = from($content, $array, $lot);
            if ('!!str' === $tag && $value instanceof \DateTime) {
                return $content;
            }
            return from\t($value, $tag);
        }
        // <https://yaml.org/spec/1.2.2#692-node-anchors>
        if (false !== \strpos('&*', $value[0]) && \preg_match('/^([&*])([^\s,\[\]{}]+)(\s+|$)/', $value, $m)) {
            $key = $m[2];
            if ('&' === $m[1]) {
                $value = from(\substr($value, \strlen($m[0])), $array, $lot);
                if (!isset($lot[0][$key])) {
                    $lot[0][$key] = &$value;
                }
                return $value;
            }
            return $lot[0][$key] ?? null;
        }
        // List-style value
        if ('-' === $value[0] && \strlen($value) > 2 && false !== \strpos(" \n\t", $value[1])) {
            $out = [];
            foreach (\preg_split('/\n-[ \n\t]/', \substr($value, 2)) as $v) {
                $out[] = from($v, $array, $lot);
            }
            return $out;
        }
        if (\strlen($value) > 2 && '0' === $value[0]) {
            // Hex
            if (\preg_match('/^0x[a-f\d]+$/i', $value)) {
                return \hexdec($value);
            }
            // Octal
            if (\preg_match('/^0o?[0-7]+$/i', $value)) {
                if (false !== \strpos('Oo', $value[1])) {
                    // PHP < 8.1
                    $value = \substr($value, 2);
                }
                return \octdec($value);
            }
        }
        // Exponent
        if (\preg_match('/^[+-]?\d*[.]?\d+e[+-]?\d+$/i', $value)) {
            return (float) $value;
        }
        if (\is_numeric($value)) {
            return false !== \strpos($value, '.') ? (float) $value : (int) $value;
        }
        // <https://yaml.org/type/timestamp.html>
        if (\is_numeric($value[0]) && \preg_match('/^[1-9]\d{3,}-(0\d|1[0-2])-(0\d|[1-2]\d|3[0-1])((t|[ \t]+)([0-1]\d|2[0-4]):([0-5]\d|60)(:([0-5]\d|60)([.]\d+)?)?([ \t]*[+-]([0-1]\d|2[0-4]):([0-5]\d|60)(:([0-5]\d|60)([.]\d+)?)?|z)?)?$/i', $value)) {
            return new \DateTime($value);
        }
        if (false === ($n = \strpos($value, ':')) || false === \strpos(" \t", \substr($value, $n + 1, 1))) {
            return from\f($value, false);
        }
        $block = -1;
        $blocks = [];
        $rows = \explode("\n", $value);
        foreach ($rows as $row) {
            $dent = \strspn($row, ' ');
            $current = $dent > 0 ? \substr($row, $dent) : $row;
            if ($prev = $blocks[$block] ?? 0) {
                // A blank line
                if ("" === $current) {
                    $blocks[$block] .= "\n";
                    continue;
                }
                // A comment
                if ('#' === $current[0]) {
                    continue;
                }
                // A list
                if ('-' === \trim(\strstr($current, '#', true) ?: $current)) {
                    $blocks[$block] .= "\n- ";
                    continue;
                }
                if ('-' === $current[0] && false !== \strpos(" \t", $current[1])) {
                    $blocks[$block] .= "\n- " . \substr($current, 2);
                    continue;
                }
                if ($dent > 0) {
                    if ("\n- " === \substr($prev, -3)) {
                        $blocks[$block] .= $current;
                        continue;
                    }
                    $blocks[$block] .= "\n" . $current;
                    continue;
                }
            }
            $blocks[++$block] = $current;
        }
        $out = [];
        foreach ($blocks as $block) {
            if (false !== \strpos('\'"', $block[0]) && \preg_match('/^(' . $str . '):\s+/', $block, $m)) {
                $out[from($m[1])] = from(\substr($block, \strlen($m[0])), $array, $lot);
                continue;
            }
            [$k, $s, $v] = \array_replace(["", "", ""], \preg_split('/[ \t]*:([ \n\t]\s*|$)/', $block, 2, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY));
            if ("" === $v) {
                $out[$k] = null;
                continue;
            }
            // Fix case for invalid key-value pair(s) such as `asdf: asdf: asdf` as it should be `asdf:\n asdf: asdf`
            if ($s && "\n" !== $s[0] && false === \strpos("\n!#&*:>[{|", $v[0]) && \strpos($v, ':') > 0) {
                $out[$k] = $v;
                continue;
            }
            $out[$k] = from($v, $array, $lot);
        }
        return $array ? $out : (object) $out;
    }
}

namespace x\y_a_m_l\from {
    function c(string $value): string {
        if ('[' === $value[0] && \preg_match('/\[(?>(?R)|#[^\n]*|[^][])*\]/', $value, $m, \PREG_OFFSET_CAPTURE)) {
            if (0 === $m[0][1]) {
                if (0 === \strpos(\trim(\substr($value, \strlen($m[0][0]))), '#')) {
                    return $m[0][0];
                }
                return $value;
            }
        }
        if ('{' === $value[0] && \preg_match('/\{(?>(?R)|#[^\n]*|[^{}])*\}/', $value, $m, \PREG_OFFSET_CAPTURE)) {
            if (0 === $m[0][1]) {
                if (0 === \strpos(\trim(\substr($value, \strlen($m[0][0]))), '#')) {
                    return $m[0][0];
                }
                return $value;
            }
        }
        if (false !== \strpos('>|', $value[0])) {
            [$a, $b] = \array_replace(["", ""], \explode("\n", $value, 2));
            return \trim(\strstr($a, '#', true) ?: $a) . "\n" . \preg_replace('/^#.*$/m', "", $b);
        }
        if (false !== \strpos($value, "\n")) {
            $out = [];
            foreach (\explode("\n", $value) as $v) {
                if ("" === $v) {
                    $out[] = "";
                    continue;
                }
                $out[] = c($v);
            }
            return \implode("\n", $out);
        }
        if ('#' === $value[0]) {
            return "";
        }
        if (false !== \strpos('\'"', $value[0])) {
            $value = \trim($value);
            if ("'" === $value[0] && "'" === \substr($value, -1)) {
                return $value;
            }
            if ('"' === $value[0] && '"' === \substr($value, -1)) {
                return $value;
            }
            if (\preg_match('/^(\'(?>\'\'|[^\'])*\'|"(?>[^"\\\\]|\\\\.)*")\s*#[^\n]*$/', $value, $m)) {
                return $m[1];
            }
        }
        return \rtrim(\strstr($value, '#', true) ?: $value);
    }
    // <https://yaml-multiline.info>
    function f(string $value, $dent = true): string {
        $content = "";
        $test = 0;
        foreach (\explode("\n", $value) as &$v) {
            if ("" === $v) {
                $content .= "\n";
                continue;
            }
            if ($dent && $test !== ($t = \strspn($v, " \t"))) {
                $test = $t;
                $content .= "\n" . $v;
                continue;
            }
            $content .= ("\n" !== \substr($content, -1) ? ' ' : "") . \ltrim($v);
        }
        return \ltrim($content);
    }
    // <https://yaml.org/type>
    function t($value, string $tag) {
        if (0 === \strpos($tag, '!!')) {
            $tag = \substr($tag, 2);
            if ('binary' === $tag) {
                return \base64_decode($value);
            }
            if ('bool' === $tag) {
                return (bool) $value;
            }
            if ('float' === $tag) {
                return (float) $value;
            }
            if ('int' === $tag) {
                return (int) $value;
            }
            if ('map' === $tag) {
                return (object) $value;
            }
            if ('null' === $tag) {
                return null;
            }
            if ('seq' === $tag) {
                return \array_values((array) $value);
            }
            if ('str' === $tag) {
                return (string) $value;
            }
            if ('timestamp' === $tag) {
                return new \DateTime((string) $value);
            }
            return $value;
        }
        // Ignore local tag
        return $value;
    }
}