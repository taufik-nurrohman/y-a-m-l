<?php

namespace x\y_a_m_l {
    function from(?string $value, $array = false, array &$lot = []) {
        $value = \strtr(\trim($value ?? "", "\n"), [
            "\r\n" => "\n",
            "\r" => "\n"
        ]);
        // <https://yaml.org/spec/1.2.2#68-directives>
        if (\strspn($value, '#%') && false !== \strpos($value, "\n---")) {
            $max = \strlen($value);
            $n = 0;
            while ($n < $max) {
                if (false === ($x = \strpos($value, "\n", $n))) {
                    $next = $max;
                    $v = \substr($value, $n);
                } else {
                    $next = $x + 1;
                    $v = \substr($value, $n, $x - $n);
                }
                if ("" === $v || '#' === $v[0] || '%' === $v[0] && \strlen($v) > 1 && \strcspn($v, " \t", 1)) {
                    $n = $next;
                    continue;
                }
                break;
            }
            if (0 !== $n) {
                $value = \substr($value, $n);
            }
        }
        // <https://yaml.org/spec/1.2.2#912-document-markers>
        if (0 === \strncmp($value, '---', 3) && (3 === \strlen($value) || \strspn($value, " \n\t", 3))) {
            $r = [];
            $s = null;
            $start = true;
            foreach (\explode("\n", $value) as $v) {
                if ((0 === \strncmp($v, '---', 3) || 0 === \strncmp($v, '...', 3)) && (3 === \strlen($v) || \strspn($v, " \t", 3))) {
                    if (null !== $s) {
                        $r[] = from\v($s, $array, $lot);
                    }
                    $s = '-' === $v[0] ? \ltrim(\substr($v, 3)) . "\n" : null;
                    $start = true;
                    continue;
                }
                if (null !== $s) {
                    // <https://yaml.org/spec/1.2.2#68-directives>
                    if ($start && \strlen($v) > 1 && '%' === $v[0] && \strcspn($v, " \t", 1)) {
                        continue;
                    }
                    $s .= $v . "\n";
                    $start = false;
                } else {
                    if ($v && 0 !== \strcspn($v, '#%')) {
                        break;
                    }
                }
            }
            if (null !== $s) {
                $r[] = from\v($s, $array, $lot);
            }
            return $r;
        }
        return from\v($value, $array, $lot);
    }
}

namespace x\y_a_m_l\from {
    function b(string $v) {
        $max = \strlen($v);
        $stack = [];
        for ($i = 0; $i < $max; ++$i) {
            $c = $v[$i];
            if ($c === '"' || $c === "'") {
                if ("" !== ($q = q(\substr($v, $i)))[0]) {
                    $i += \strlen($q[0]) - 1;
                }
                continue;
            }
            if ('#' === $c) {
                if (false === ($n = \strpos($v, "\n", $i))) {
                    break;
                }
                $i = $n;
                continue;
            }
            if ('[' === $c || '{' === $c) {
                $stack[] = $c;
                continue;
            }
            if (']' === $c || '}' === $c) {
                $z = \array_pop($stack);
                if (']' === $c && '[' !== $z || '}' === $c && '{' !== $z) {
                    return false;
                }
                if (!$stack) {
                    break;
                }
            }
        }
        return !$stack;
    }
    function c(string $v) {
        if (0 === ($n = \strpos($v, '#'))) {
            return "";
        }
        if (\strspn($v, " \t", $n - 1)) {
            return \substr($v, 0, $n - 1) . \strstr(\substr($v, $n), "\n");
        }
        return $v;
    }
    function d(string $v, $next = false) {
        if ($d = \strspn($v, ' ')) {
            $v = \substr(\strtr($v, [
                "\n" . \str_repeat(' ', $d) => "\n"
            ]), $d);
        } else if ($next && ($d = \strspn($v, ' ', \strpos($v, "\n") + 1))) {
            $v = \strtr($v, [
                "\n" . \str_repeat(' ', $d) => "\n"
            ]);
        }
        return $v;
    }
    function e(string $v, $array = false, array &$lot = []) {
        if ("" === ($v = \rtrim($raw = $v))) {
            return null;
        }
        $k = \substr($v, 0, \strcspn($v, " \n\t"));
        if ('!' === $v[0] && '!' !== $k) {
            $v = v(d($w = \trim(\substr($v, \strlen($k) + 1), "\n")), $array, $lot);
            if ('!!str' === $k && !isset($lot[$k]) && $v instanceof \DateTimeInterface) {
                return $w;
            }
            return t($v, $k, $array, $lot);
        }
        if ('&' === $v[0] && '&' !== $k) {
            $v = \substr($v, \strlen($k) + 1);
            $lot[$k] = $v = v(d($v), $array, $lot);
            return $v;
        }
        if ('*' === $v[0] && '*' !== $k) {
            return $lot['&' . \substr($k, 1)] ?? null;
        }
        if (\array_key_exists($k = \strtolower($v), $a = [
            "''" => "",
            '""' => "",
            '+.inf' => \INF,
            '+.nan' => \NAN,
            '-.inf' => -\INF,
            '-.nan' => -\NAN,
            '.inf' => \INF,
            '.nan' => \NAN,
            '[]' => [],
            'false' => false,
            'null' => null,
            'true' => true,
            '{}' => $array ? [] : (object) [],
            '~' => null
        ])) {
            return $a[$k];
        }
        if ('"' === $v[0] && '"' === \substr($v, -1)) {
            if (false !== \strpos($v, "\\'")) {
                return null; // Broken :(
            }
            $r = "";
            foreach (\explode("\n", \strtr($v, [
                "\\ " => "",
                "\\\n" => "",
                "\\\t" => ""
            ])) as $v) {
                if ("" === $r) {
                    $r .= $v;
                    continue;
                }
                if ("" === $v) {
                    $r .= "\\n";
                    continue;
                }
                $r .= "\\n" === \substr($r, -2) ? $v : ' ' . \ltrim($v);
            }
            return \json_decode($r);
        }
        if ("'" === $v[0] && "'" === \substr($v, -1)) {
            if (false !== \strpos($v, "\\'")) {
                return null; // Broken :(
            }
            $r = "";
            foreach (\explode("\n", \strtr(\substr($v, 1, -1), [
                "''" => "'"
            ])) as $v) {
                if ("" === $r) {
                    $r .= $v;
                    continue;
                }
                if ("" === $v) {
                    $r .= "\n";
                    continue;
                }
                $r .= "\n" === \substr($r, -1) ? $v : ' ' . \ltrim($v);
            }
            return $r;
        }
        if (\strspn($v, '[{')) {
            if (0 === \strpos($v = o($v), "-\0")) {
                $r = [];
                foreach (\explode("\n-\0", \substr($v, 2)) as $v) {
                    $r[] = v(d(\ltrim($v, "\n")), $array, $lot);
                }
                return $r;
            }
            return v($v, $array, $lot);
        }
        if (\strspn($v, '>|')) {
            return f($raw);
        }
        if (\strlen($n = \strtolower($v)) > 2 && '0' === $n[0]) {
            // Octal
            if ('o' === $n[1] && \strspn($n, '01234567', 2) === \strlen($n) - 2) {
                return \octdec(\substr($n, 2));
            }
            // Hex
            if ('x' === $n[1] && \strspn($n, '0123456789abcdef', 2) === \strlen($n) - 2) {
                return \hexdec($n);
            }
            if (\strspn($n, '01234567', 1) === \strlen($n) - 1) {
                return \octdec($n);
            }
        }
        // <https://yaml.org/spec/1.2.2#10214-floating-point>
        if (\strspn($n, '+-.0123456789e') === \strlen($n) && \preg_match('/^-?(?>0|\d+)(?>\.\d*)?(?>[e][-+]?\d+)$/', $n)) {
            return (float) $n;
        }
        // <https://yaml.org/type/timestamp.html>
        if (\strspn($n, '+-.0123456789:tz' . " \t") === \strlen($n) && \preg_match('/^\d{4,}-\d{1,2}-\d{1,2}(?>(?>[t]|\s+)\d{1,2}:\d{1,2}:\d{1,2}(?>\.\d*)?(?>\s*[z]|[-+]\d{1,2}(?>:\d{2})?)?)?$/', $n)) {
            return new \DateTime($n);
        }
        if (\is_numeric($n)) {
            return 0 + $n;
        }
        $r = "";
        foreach (\explode("\n", $v) as $v) {
            if ("" === ($v = \trim($v))) {
                $r .= "\n";
                continue;
            }
            $r .= "" === $r || "\n" === \substr($r, -1) ? $v : ' ' . $v;
        }
        return $r;
    }
    // <https://yaml.org/spec/1.2.2#81-block-scalar-styles>
    function f(string $v) {
        if (false === ($n = \strpos(\rtrim($v), "\n"))) {
            return null; // Broken :(
        }
        $k = \trim(\substr($v, 0, $n));
        $q = $k[0];
        $v = \substr($v, $n + 1);
        if ("" === ($k = \substr($k, 1))) {
            $d = $e = "";
        } else if (\strspn($k, '+-')) {
            $d = \substr($k, 1, \strspn($k, '0123456789', 1));
            $e = $k[0];
        } else {
            $d = \substr($k, 0, $n = \strspn($k, '0123456789'));
            $e = \substr($k, $n);
        }
        $d = (int) ($d ?: 0);
        $dd = 0;
        $v = \explode("\n", $v);
        foreach ($v as $vv) {
            if ("" !== \trim($vv) && ($ddd = \strspn($vv, ' '))) {
                if ($d > 0 && $ddd < $d) {
                    return null; // Broken :(
                }
                // <https://yaml.org/spec/1.2.2#example-invalid-block-scalar-indentation-indicators>
                if (0 !== $dd && $ddd < $dd) {
                    return null; // Broken :(
                }
                if (0 === $dd) {
                    $dd = $ddd;
                }
            }
        }
        if (0 === $d) {
            $d = $dd;
        }
        $r = "";
        if ('>' === $q) {
            foreach ($v as $vv) {
                if ("" === \trim($vv)) {
                    $r .= "\n";
                    continue;
                }
                if (($dd = \strspn($vv, ' ')) >= $d) {
                    $vv = \substr($vv, $d);
                }
                if (' ' === ($vv[0] ?? 0)) {
                    $r .= "\n" . $vv;
                    continue;
                }
                if ("\n" === \substr($r, -1)) {
                    $r .= $vv;
                    continue;
                }
                if (\strspn(\substr(\strrchr($r, "\n"), 1), ' ')) {
                    $r .= "\n" . $vv;
                    continue;
                }
                $r .= ' ' . $vv;
            }
        } else {
            foreach ($v as $vv) {
                if ("" === \trim($vv)) {
                    $r .= "\n";
                    continue;
                }
                if (\strspn($vv, ' ') >= $d) {
                    $vv = \substr($vv, $d);
                }
                $r .= "\n" . $vv;
            }
        }
        $r = \ltrim(\substr($r, 1), "\n");
        return '+' === $e ? $r : ('-' === $e ? \rtrim($r) : ("\n" === \substr($r, -1) ? \rtrim($r) . "\n" : $r));
    }
    function k(string $k, $array = false, array &$lot = []) {
        if (\is_numeric($k)) {
            return $k;
        }
        $k = v($k, $array, $lot);
        if (-\INF === $k || -\NAN === $k || \INF === $k || \NAN === $k || \is_array($k) || \is_object($k) || false === $k || null === $k || true === $k) {
            $k = "\0" . \serialize($k) . "\0";
        }
        return $k;
    }
    function o(string $v) {
        $b = $v[0];
        $d = $r = $stack = "";
        while ("" !== $v) {
            if ($n = \strcspn($v, '"' . "'" . ',:[]{}')) {
                $r .= \ltrim(\substr($v, 0, $n));
                $v = \ltrim(\substr($v, $n));
            }
            if (('"' === ($c = $v[0] ?? 0) || "'" === $c) && "" !== ($q = q($v))[0]) {
                $r .= $q[0];
                $v = c(\substr($v, \strlen($q[0])));
                continue;
            }
            if (',' === $c) {
                if ($c === $v) {
                    return ""; // Broken :(
                }
                if ('#' === ($v[1] ?? 0)) {
                    return ""; // Broken :(
                }
                $v = \ltrim(\substr(c($v), 1));
                if ("" !== ($q = q($w = \trim(\strrchr($r, "\n"), " \n\t")))[0] && (':' === \substr($q[1] = \trim($q[1]), -1) || ': ' === \substr($q[1], 0, 2))) {
                    // …
                } else if (':' === \substr($w, -1) || false !== \strpos($w, ': ')) {
                    // …
                } else {
                    if ("" !== $w && '-' !== $w && '- ' !== \substr($w, 0, 2)) {
                        $r .= ': ~';
                    }
                }
                $r .= "\n" . $d . ('[' === \substr($stack, -1) ? '- ' : "");
                continue;
            }
            if (':' === $c) {
                if ($c === $v) {
                    return ""; // Broken :(
                }
                $r .= $c;
                if (\strcspn($v = \substr(c($v), 1), " \n\t")) {
                    continue;
                }
                $r .= ' ';
                $v = \ltrim($v);
                continue;
            }
            if ('[' === $c) {
                $stack .= $c;
                $d .= ' ';
                if (': ' === \substr($r, -2)) {
                    $r = \rtrim($r);
                }
                $r .= "\n" . $d . '- ';
                $v = \ltrim(\substr(c($v), 1));
                continue;
            }
            if (']' === $c) {
                if ('[' !== \substr($stack, -1)) {
                    return ""; // Broken :(
                }
                $stack = \substr($stack, 0, -1);
                $v = \ltrim(\substr(c($v), 1));
                if ("" !== $v && \strcspn($v, ',]}')) {
                    return ""; // Broken :(
                }
                $d = \substr($d, 0, -1);
                if ("" !== ($q = q($w = \trim(\strrchr($r = \rtrim($r, "\n"), "\n"))))[0] && (':' === \substr($q[1] = \trim($q[1]), -1) || ': ' === \substr($q[1], 0, 2))) {
                    // …
                } else if (':' === \substr($w, -1) || false !== \strpos($w, ': ')) {
                    // …
                } else {
                    if ("" !== $w && '-' !== $w && '- ' !== \substr($w, 0, 2)) {
                        $r .= ': ~';
                    }
                }
                if ('-' === $w) {
                    $r = \rtrim(\substr($r, 0, -2));
                }
                continue;
            }
            if ('{' === $c) {
                $stack .= $c;
                $v = \ltrim(\substr(c($v), 1));
                if (\strspn($v, ',[{')) {
                    return ""; // Broken :(
                }
                $d .= ' ';
                if (': ' === \substr($r, -2)) {
                    $r = \rtrim($r);
                }
                $r .= "\n" . $d;
                continue;
            }
            if ('}' === $c) {
                if ('{' !== \substr($stack, -1)) {
                    return ""; // Broken :(
                }
                $stack = \substr($stack, 0, -1);
                $v = \ltrim(\substr(c($v), 1));
                if ("" !== $v && \strcspn($v, ',]}')) {
                    return ""; // Broken :(
                }
                $d = \substr($d, 0, -1);
                if ("" !== ($q = q($w = \trim(\strrchr($r = \rtrim($r, "\n"), "\n"))))[0] && (':' === \substr($q[1] = \trim($q[1]), -1) || ': ' === \substr($q[1], 0, 2))) {
                    // …
                } else if (':' === \substr($w, -1) || false !== \strpos($w, ': ')) {
                    // …
                } else {
                    if ("" !== $w && '-' !== $w && '- ' !== \substr($w, 0, 2)) {
                        $r .= ': ~';
                    }
                }
                continue;
            }
            $r .= \ltrim(c($v));
            break;
        }
        if ("" !== $stack) {
            return ""; // Broken :(
        }
        if ("" === \trim($r)) {
            return '[' === $b ? '[]' : '{}';
        }
        return d(\rtrim(\trim($r, "\n")));
    }
    function q(string $v) {
        // `""…`
        // `''…`
        if (0 === \strpos($v, '""') || 2 === \strspn($v, "'")) {
            return [$v[0] . $v[1], \substr($v, 2)];
        }
        if ("" === $v || \strcspn($v, '"' . "'")) {
            return ["", $v];
        }
        // `"`
        // `'`
        $r = [$c = $v[0], ""];
        $v = \substr($v, 1);
        // <https://yaml.org/spec/1.2.2#731-double-quoted-style>
        if ('"' === $c) {
            while (false !== ($n = \strpos($v, $c))) {
                // `"asdf"` or `"asdf\"`
                $r[0] .= \substr($v, 0, $n += 1);
                $v = \substr($v, $n);
                if ("\\" !== \substr($r[0], -2, 1)) {
                    // `"asdf"`
                    break;
                }
                // `"asdf\"…`
            }
            $r[1] .= $v;
            return $c === $r[0] ? ["", $c . $r[1]] : $r;
        }
        // <https://yaml.org/spec/1.2.2#732-single-quoted-style>
        if ("'" === $c) {
            while (false !== ($n = \strpos($v, $c))) {
                // `'asdf'`
                $r[0] .= \substr($v, 0, $n += 1);
                $v = \substr($v, $n);
                if ($c === ($v[0] ?? 0)) {
                    $r[0] .= $c;
                    $v = \substr($v, 1);
                    // `'asdf''…`
                    continue;
                }
                // `'asdf'`
                break;
            }
            $r[1] .= $v;
            return $c === $r[0] ? ["", $c . $r[1]] : $r;
        }
    }
    // <https://yaml.org/type>
    function t($v, $k, $array, $lot) {
        if (\is_callable($lot[$k] ?? 0)) {
            return \call_user_func($lot[$k], $v, $array, $lot);
        }
        if (0 === \strpos($k, '!!')) {
            $k = \substr($k, 2);
            if ('binary' === $k) {
                return \base64_decode(\preg_replace('/\s+/', "", \trim($v ?? 'AA==')));
            }
            if ('bool' === $k) {
                return (bool) $v;
            }
            if ('float' === $k) {
                return (float) $v;
            }
            if ('int' === $k) {
                return (int) $v;
            }
            if ('map' === $k) {
                return (object) $v;
            }
            if ('null' === $k) {
                return null;
            }
            if ('seq' === $k) {
                return \array_values((array) $v);
            }
            if ('str' === $k) {
                return (string) $v;
            }
            if ('timestamp' === $k) {
                return new \DateTime((string) $v);
            }
        }
        return $v;
    }
    function v(string $value, $array = false, array &$lot = []) {
        if ("" === ($value = d(\ltrim($value, "\n")))) {
            return null;
        }
        $i = -1;
        $r = [];
        foreach (\explode("\n", $value) as $v) {
            $d = \strspn($v, ' ');
            // Part of a block…
            if ($w = $r[$i] ?? 0) {
                $w = \rtrim(\strstr($w . "\n", "\n", true), " \t");
                if (\strspn(\trim(\rtrim(c($w), '+-0123456789')), '>|')) {
                    if ($d || "" === $v) {
                        $r[$i] .= $v . "\n";
                        continue;
                    }
                    if ('#' === $v[0]) {
                        continue;
                    }
                }
                if ('"' === ($c = $w[0] ?? 0) || "'" === $c) {
                    if ("" === $v || "" === q($r[$i])[0]) {
                        $r[$i] .= $v . "\n";
                        continue;
                    }
                }
                if ("-\0" === \substr($w, 0, 2)) {
                    if ('-' === \trim(c($v))) {
                        $r[$i] .= "-\0\n";
                        continue;
                    }
                    if ('-' === ($v[0] ?? 0) && \strspn($v, " \0\t", 1)) {
                        $r[$i] .= "-\0" . \substr($v, 2) . "\n";
                        continue;
                    }
                    if ($d) {
                        $r[$i] .= $v . "\n";
                    }
                    continue;
                }
                if ("?\0" === \substr($w, 0, 2)) {
                    if (':' === \trim(c($v))) {
                        $r[$i] .= ":\n";
                        continue;
                    }
                    if (':' === ($v[0] ?? 0) && \strspn($v, " \t", 1)) {
                        $r[$i] .= ': ' . \substr($v, 2) . "\n";
                        continue;
                    }
                    if ($d) {
                        $r[$i] .= $v . "\n";
                        continue;
                    }
                    // Start of a block…
                    if ('?' === ($v[0] ?? 0) && \strspn($v, " \0\t", 1)) {
                        $r[++$i] = "?\0" . \substr($v, 2) . "\n";
                        continue;
                    }
                    // Start of a block…
                    $r[++$i] = $v . "\n";
                    continue;
                }
                if ("" !== ($q = q($w))[0] && ':' === (\trim($q[1])[0] ?? 0)) {
                    if ($d) {
                        $r[$i] .= $v . "\n";
                        continue;
                    }
                    if ('?' === ($v[0] ?? 0) && \strspn($v, " \0\t", 1)) {
                        $v = "?\0" . \substr($v, 2);
                    }
                    $r[++$i] = $v . "\n";
                    continue;
                }
                $w = \trim(c($w));
                if ('[' === ($w[0] ?? 0) || (($n = \strpos($w, '[')) > 0 && 1 === \strspn($w, " \t", $n - 1, 1) && false !== \strpos(\substr($w, 0, $n), ':')) && !b($w)) {
                    if (b(\substr($r[$i] .= $v . "\n", \strlen($w) - 1))) {
                        $i += 1;
                    }
                    continue;
                }
                if ('{' === ($w[0] ?? 0) || (($n = \strpos($w, '{')) > 0 && 1 === \strspn($w, " \t", $n - 1, 1) && false !== \strpos(\substr($w, 0, $n), ':')) && !b($w)) {
                    if (b(\substr($r[$i] .= $v . "\n", \strlen($w) - 1))) {
                        $i += 1;
                    }
                    continue;
                }
                if (':' === \substr(\trim(\substr($w, 0, \strcspn($w, '!&*'))), -1)) {
                    if ($d) {
                        $r[$i] .= $v . "\n";
                        continue;
                    }
                    if ('-' === \trim(c($v))) {
                        $r[$i] .= "-\0\n";
                        continue;
                    }
                    if ('-' === ($v[0] ?? 0) && \strspn($v, " \0\t", 1)) {
                        $r[$i] .= "-\0" . \substr($v, 2) . "\n";
                        continue;
                    }
                    if ('?' === ($v[0] ?? 0) && \strspn($v, " \0\t", 1)) {
                        $v = "?\0" . \substr($v, 2);
                    }
                    if ("" === ($v = c($v))) {
                        $r[$i] .= "\n";
                        continue;
                    }
                    $r[++$i] = $v . "\n";
                    continue;
                }
                if (false !== ($n = \strpos($w, ":\t") ?: \strpos($w, ': '))) {
                    if ($d) {
                        $r[$i] .= $v . "\n";
                        continue;
                    }
                    if (('"' === ($c = \trim(\substr($w, $n + 2))[0] ?? 0) || "'" === $c) && "" === q(\trim(\substr($r[$i], $n + 2)))[0]) {
                        $r[$i] .= $v . "\n";
                        continue;
                    }
                    if (\strspn(\trim(\rtrim(\substr($w, $n + 2), '+-0123456789')), '>|')) {
                        if ($d || "" === $v) {
                            $r[$i] .= $v . "\n";
                            continue;
                        }
                        if ('#' === $v[0]) {
                            continue;
                        }
                    }
                    if ('?' === ($v[0] ?? 0) && \strspn($v, " \0\t", 1)) {
                        $v = "?\0" . \substr($v, 2);
                    }
                    if ("" === ($v = c($v))) {
                        $r[$i] .= "\n";
                        continue;
                    }
                    $r[++$i] = $v . "\n";
                    continue;
                }
                $r[$i] .= c($v) . "\n";
                continue;
            }
            // Start of a block…
            if ("" === c($v)) {
                continue;
            }
            if ("" !== ($q = q($v))[0]) {
                // <https://yaml.org/spec/1.2.2#66-comments>
                if ('#' === ($q[1][0] ?? 0)) {
                    $r[++$i] = "~\n";
                    continue;
                }
                if (':' === (($q[1] = \ltrim($q[1]))[0] ?? 0)) {
                    if (\strspn($q[1], " \n\t", 1)) {
                        $qq = q(\ltrim(\substr($q[1], 1)));
                        $r[++$i] = $q[0] . $q[1][0] . $q[1][1] . $qq[0] . c($qq[1]) . "\n";
                        continue;
                    }
                    $r[++$i] = $q[0] . c($q[1]) . "\n";
                    continue;
                }
                $r[++$i] = $q[0] . c($q[1]) . "\n";
                continue;
            }
            if ('-' === $v || '?' === $v) {
                $r[++$i] = $v . "\0\n";
                continue;
            }
            if (\strspn($v, '-?') && \strspn($v, " \0\t", 1)) {
                $r[++$i] = $v[0] . "\0" . \substr($v, 2) . "\n";
                continue;
            }
            // <https://yaml.org/spec/1.2.2#741-flow-sequences>
            if ('[' === ($v[0] ?? 0) || (($n = \strpos($v, '[')) > 0 && 1 === \strspn($v, " \t", $n - 1, 1) && false !== \strpos(\substr($v, 0, $n), ':'))) {
                $r[++$i] = $v . "\n";
                continue;
            }
            // <https://yaml.org/spec/1.2.2#742-flow-mappings>
            if ('{' === ($v[0] ?? 0) || (($n = \strpos($v, '{')) > 0 && 1 === \strspn($v, " \t", $n - 1, 1) && false !== \strpos(\substr($v, 0, $n), ':'))) {
                $r[++$i] = $v . "\n";
                continue;
            }
            $r[++$i] = c($v) . "\n";
        }
        $to = [];
        foreach ($r as $v) {
            // `!asdf asdf`
            // `&asdf asdf`
            // `*asdf asdf`
            if (0 !== ($c = $v[0] ?? 0) && \strspn($c, '!&*') && $c !== \substr($v, 0, \strcspn($v, " \n\t"))) {
                return e($v, $array, $lot);
            }
            // `>\n asdf…`
            // `[asdf…`
            // `{asdf…`
            // `|\n asdf…`
            if (0 !== $c && \strspn($c, '>[{|')) {
                if (false !== ($n = \strpos($w = \strstr($v, "\n", true), '#')) && \strcspn($w, " \t", $n - 1)) {
                    return null; // Broken :(
                }
                return e($v, $array, $lot);
            }
            // `"asdf asdf \"asdf\" asdf"`
            // `'asdf asdf ''asdf'' asdf'`
            if ('"' === $c || "'" === $c) {
                // `"asdf`
                // `'asdf`
                if ("" === ($q = q($v))[0]) {
                    return null; // Broken :(
                }
                // `"asdf"#`
                // `'asdf'#`
                if ('#' === ($q[1][0] ?? 0)) {
                    return null; // Broken :(
                }
                // `"asdf"…`
                // `'asdf'…`
                if ("" !== \trim(c($q[1]))) {
                    // `"asdf": `
                    // `'asdf': `
                    if (':' === (($q[1] = \ltrim($q[1]))[0] ?? 0) && \strspn($q[1], " \n\t", 1)) {
                        // <https://github.com/nodeca/js-yaml/issues/189>
                        if (false !== \strpos($q[0], "\n")) {
                            $object = true;
                            continue; // Broken :(
                        }
                        $k = k($q[0], $array, $lot);
                        $v = \substr($q[1], 1);
                        if ("\n" === ($v[0] ?? 0)) {
                            $to[$k] = v(d(\substr($v, 1)), $array, $lot);
                            continue;
                        }
                        // `"asdf": "asdf"`
                        // `'asdf': 'asdf'`
                        if ("" !== ($q = q($v = d(\substr($v, 1))))[0]) {
                            // `"asdf": "asdf"…`
                            // `'asdf': 'asdf'…`
                            if ('#' === ($q[1][0] ?? 0) || "" !== \trim(c($q[1]))) {
                                $to[$k] = null; // Broken :(
                                continue;
                            }
                            $to[$k] = e($q[0], $array, $lot);
                            continue;
                        }
                        // `asdf: asdf: asdf`
                        if ("" !== $v && \strcspn($v, '!&>[{|') && false !== ($n = \strpos($v, ':')) && \strspn($v, " \t", $n + 1)) {
                            $to[$k] = null; // Broken :(
                            continue;
                        }
                        $to[$k] = v($v, $array, $lot);
                        continue;
                    }
                    return null; // Broken :(
                }
                return e($v, $array, $lot);
            }
            // `- asdf…`
            if ("-\0" === \substr($v, 0, 2)) {
                $r = [];
                foreach (\explode("\n-\0", \substr($v, 2)) as $vv) {
                    // `- "asdf"`
                    // `- 'asdf'`
                    if ("" !== ($q = q($vv = d(\ltrim($vv, "\n"), 1)))[0]) {
                        // `- "asdf"…`
                        // `- 'asdf'…`
                        if ('#' === ($q[1][0] ?? 0) || "" !== \trim(c($q[1]))) {
                            $r[] = null; // Broken :(
                            continue;
                        }
                        $r[] = e($q[0], $array, $lot);
                        continue;
                    }
                    $r[] = v($vv, $array, $lot);
                }
                return $r;
            }
            // `? asdf…`
            if ("?\0" === \substr($v, 0, 2)) {
                if (false !== ($n = \strpos($v, "\n:")) && \strspn($v, " \n\t", $n + 2)) {
                    $k = k(d(\substr($v, 2, $n - 2), 1), $array, $lot);
                    $v = v(d(\substr($v, $n + 3), 1), $array, $lot);
                } else if (false !== ($n = \strpos($v, ':')) && \strspn($v, " \n\t", $n + 1)) {
                    $k = k(\substr($v, 2, $n - 2), $array, $lot);
                    $v = v(\substr($v, $n + 2), $array, $lot);
                } else {
                    $k = k(\substr($v, 2), $array, $lot);
                    $v = null;
                }
                $to[$k] = $v;
                continue;
            }
            // `asdf:`
            if (':' === \substr($v, -1)) {
                if (false !== \strpos($k = \trim(\substr($v, 0, -1)), "\n")) {
                    $object = true;
                    continue; // Broken :(
                }
                $to[k($k, $array, $lot)] = null;
                continue;
            }
            // `asdf: …`
            if (false !== ($n = \strpos($w = \strstr($v . "\n", "\n", true) . "\n", ":\n") ?: \strpos($w, ":\t") ?: \strpos($w, ': '))) {
                // <https://github.com/nodeca/js-yaml/issues/189>
                if (false !== \strpos($k = \trim(\substr($v, 0, $n)), "\n")) {
                    $object = true;
                    continue; // Broken :(
                }
                $k = k($k, $array, $lot);
                $v = \substr($v, $n + 1);
                if ("\n" === ($v[0] ?? 0)) {
                    $to[$k] = v(d(\substr($v, 1)), $array, $lot);
                    continue;
                }
                // `asdf: "asdf"`
                // `asdf: 'asdf'`
                if ("" !== ($q = q($v = d(\substr($v, 1))))[0]) {
                    // `asdf: "asdf"…`
                    // `asdf: 'asdf'…`
                    if ('#' === ($q[1][0] ?? 0) || "" !== \trim(c($q[1]))) {
                        $to[$k] = null; // Broken :(
                        continue;
                    }
                    $to[$k] = e($q[0], $array, $lot);
                    continue;
                }
                // `asdf: asdf: asdf`
                if ("" !== $v && \strcspn($v, '!&>[{|') && false !== ($n = \strpos($v, ':')) && \strspn($v, " \t", $n + 1)) {
                    $to[$k] = null; // Broken :(
                    continue;
                }
                $to[$k] = v($v, $array, $lot);
                continue;
            }
            // <https://github.com/nodeca/js-yaml/issues/189>
            if (false !== (\strpos($v, ":\n") ?: \strpos($v, ":\t") ?: \strpos($v, ': '))) {
                continue; // Broken :(
            }
            return e(d($v), $array, $lot);
        }
        return $to ? ($array ? $to : (object) $to) : null;
    }
}