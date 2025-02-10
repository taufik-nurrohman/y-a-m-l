<?php

namespace x\y_a_m_l {
    function from(?string $value, $array = false, array &$lot = []) {
        return from\v($value, $array, $lot);
    }
}

namespace x\y_a_m_l\from {
    function b(string $v) {
        $r = ["", ""];
        $stack = [];
        while ("" !== (string) $v) {
            if ($n = \strcspn($v, '"' . "'" . '#[]{}')) {
                $r[0] .= \substr($v, 0, $n);
                $v = \substr($v, $n);
            }
            if (('"' === ($c = $v[0] ?? 0) || "'" === $c) && "" !== ($q = q($v))[0]) {
                $r[0] .= $q[0];
                $v = \substr($v, \strlen($q[0]));
                continue;
            }
            if ('#' === $c) {
                if (false === ($n = \strpos($v, "\n"))) {
                    break;
                }
                $v = \substr($v, $n + 1);
                continue;
            }
            if ('[' === $c) {
                $stack[] = $c;
                $r[0] .= $c;
                $v = \substr($v, 1);
                continue;
            }
            if (']' === $c) {
                \array_pop($stack);
                $r[0] .= $c;
                $v = \substr($v, 1);
                if (!$stack) {
                    break;
                }
            }
            if ('{' === $c) {
                $stack[] = $c;
                $r[0] .= $c;
                $v = \substr($v, 1);
                continue;
            }
            if ('}' === $c) {
                \array_pop($stack);
                $r[0] .= $c;
                $v = \substr($v, 1);
                if (!$stack) {
                    break;
                }
            }
        }
        if ($stack) {
            return ["", $r[0] . $v]; // Broken :(
        }
        $r[1] = $v;
        return $r;
    }
    function c(string $v) {
        if (0 === ($n = \strpos($v, '#'))) {
            return "";
        }
        if (false !== \strpos(" \t", \substr($v, $n -= 1, 1))) {
            return \substr($v, 0, $n);
        }
        return $v;
    }
    function d(string $v) {
        if ($d = \strspn($v, ' ')) {
            $v = \substr(\strtr($v, [
                "\n" . \str_repeat(' ', $d) => "\n"
            ]), $d);
        }
        return $v;
    }
    function e(string $v, $array = false, array &$lot = []) {
        if ("" === $v) {
            return null;
        }
        if ('!' === $v[0] && '!' !== ($k = \strtok($v, " \n\t"))) {
            $v = v(d($w = \trim(\substr($v, \strlen($k) + 1), "\n")), $array, $lot);
            if ('!!str' === $k && !isset($lot[$k]) && $v instanceof \DateTimeInterface) {
                return $w;
            }
            return t($v, $k, $array, $lot);
        }
        if ('&' === $v[0] && '&' !== ($k = \strtok($v, " \n\t"))) {
            $v = \substr($v, \strlen($k) + 1);
            $lot[$k] = $v = v(d($v), $array, $lot);
            return $v;
        }
        if ('*' === $v[0] && '*' !== ($k = \strtok($v, " \n\t"))) {
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
        if (false !== \strpos('[{', $v[0])) {
            if (0 === \strpos($v = o($v), "-\0")) {
                $r = [];
                foreach (\explode("\n-\0", \substr($v, 2)) as $v) {
                    $r[] = v(d(\ltrim($v, "\n")), $array, $lot);
                }
                return $r;
            }
            return v($v, $array, $lot);
        }
        if (false !== \strpos('>|', $v[0])) {
            return f($v);
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
        if (false !== ($n = \strpos($v, ":\n"))) {
            $k = e(\trim(\substr($v, 0, $n)), $array, $lot);
            $v = v(d(\substr($v, $n + 2)), $array, $lot);
            $r = [$k => $v];
            return $array ? $r : (object) $r;
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
        if (false === ($n = \strpos($v, "\n"))) {
            return $v;
        }
        $k = \trim(\substr($v, 0, $n));
        $q = $k[0];
        $v = \substr($v, $n + 1);
        if ("" === ($k = \substr($k, 1))) {
            $d = $e = "";
        } else if (false !== \strpos('+-', $k[0])) {
            $d = \substr($k, 1, \strspn($k, '0123456789', 1));
            $e = $k[0];
        } else {
            $d = \substr($k, 0, $n = \strspn($k, '0123456789'));
            $e = \substr($k, $n);
        }
        $d = (int) $d;
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
                if (($dd = \strspn($vv, ' ')) >= $d) {
                    $vv = \substr($vv, $d);
                }
                $r .= "\n" . $vv;
            }
        }
        $r = \substr($r, 1);
        return '+' === $e ? $r : ('-' === $e ? \rtrim($r) : ("\n" === \substr($r, -1) ? \rtrim($r) . "\n" : $r));
    }
    function o(string $v) {
        $b = $v[0];
        $d = $r = "";
        $stack = [];
        while ("" !== (string) $v) {
            if ($n = \strcspn($v, '"' . "'" . '#,:[]{}')) {
                $r .= \ltrim(\substr($v, 0, $n));
                $v = \ltrim(\substr($v, $n));
            }
            if (('"' === ($c = $v[0] ?? 0) || "'" === $c) && "" !== ($q = q($v))[0]) {
                $r .= $q[0];
                $v = \substr($v, \strlen($q[0]));
                continue;
            }
            if ('#' === $c) {
                if ("\n" . $d . "-\0" === \substr("\n" . $r, $n = -(1 + \strlen($d) + 1 + 1))) {
                    $r = \substr($r, 0, $n + 1);
                }
                if (false === \strpos(" \n\t", \substr($r, -1))) {
                    if ($n = \strcspn($v, ',:[]{}')) {
                        $r .= \substr($v, 0, $n);
                        $v = \ltrim(\substr($v, $n));
                        continue;
                    }
                    $r .= $c;
                    $v = \substr($v, 1);
                    continue;
                }
                if (false === ($n = \strpos($v, "\n"))) {
                    break;
                }
                $v = \ltrim(\substr($v, $n + 1));
                continue;
            }
            if (',' === $c) {
                if ($c === $v) {
                    return ""; // Broken :(
                }
                $v = \ltrim(\substr($v, 1));
                if ("" !== ($q = q($w = \ltrim(\strrchr($r, "\n"), " \n\t")))[0] && (':' === \substr($q[1] = \trim($q[1]), -1) || ': ' === \substr($q[1], 0, 2))) {
                    // …
                } else if (':' === \substr($w, -1) || false !== \strpos($w, ': ')) {
                    // …
                } else {
                    if ("" !== $w && "-\0" !== \substr($w, 0, 2)) {
                        $r .= ': ~';
                    }
                }
                $r .= "\n" . $d . ('[' === \end($stack) ? "-\0" : "");
                continue;
            }
            if (':' === $c) {
                if ($c === $v) {
                    return ""; // Broken :(
                }
                $r .= $c;
                $v = \substr($v, 1);
                if (false === \strpos(" \n\t", $v[0])) {
                    continue;
                }
                $r .= ' ';
                $v = \ltrim($v);
                continue;
            }
            if ('[' === $c) {
                $stack[] = $c;
                $d .= '  ';
                if (': ' === \substr($r, -2)) {
                    $r = \rtrim($r);
                }
                $r .= "\n" . $d . "-\0";
                $v = \ltrim(\substr($v, 1));
                continue;
            }
            if (']' === $c) {
                if ('[' !== \end($stack)) {
                    return ""; // Broken :(
                }
                \array_pop($stack);
                $v = \ltrim(\substr($v, 1));
                if ("" !== $v && false === \strpos(',]}', $v[0])) {
                    return ""; // Broken :(
                }
                $d = \substr($d, 0, -2);
                if ("" !== ($q = q($w = \trim(\strrchr($r = \rtrim($r, "\n"), "\n"), " \n\t")))[0] && (':' === \substr($q[1] = \trim($q[1]), -1) || ': ' === \substr($q[1], 0, 2))) {
                    // …
                } else if (':' === \substr($w, -1) || false !== \strpos($w, ': ')) {
                    // …
                } else {
                    if ("" !== $w && "-\0" !== \substr($w, 0, 2)) {
                        $r .= ': ~';
                    }
                }
                if ("-\0" === $w) {
                    $r = \rtrim(\substr($r, 0, -2));
                }
                continue;
            }
            if ('{' === $c) {
                $stack[] = $c;
                $v = \ltrim(\substr($v, 1));
                if ("" !== $v && false !== \strpos(',[{', $v[0])) {
                    return ""; // Broken :(
                }
                $d .= '  ';
                if (': ' === \substr($r, -2)) {
                    $r = \rtrim($r);
                }
                $r .= "\n" . $d;
                continue;
            }
            if ('}' === $c) {
                if ('{' !== \end($stack)) {
                    return ""; // Broken :(
                }
                \array_pop($stack);
                $v = \ltrim(\substr($v, 1));
                if ("" !== $v && false === \strpos(',]}', $v[0])) {
                    return ""; // Broken :(
                }
                $d = \substr($d, 0, -2);
                if ("" !== ($q = q($w = \trim(\strrchr($r = \rtrim($r, "\n"), "\n"), " \n\t")))[0] && (':' === \substr($q[1] = \trim($q[1]), -1) || ': ' === \substr($q[1], 0, 2))) {
                    // …
                } else if (':' === \substr($w, -1) || false !== \strpos($w, ': ')) {
                    // …
                } else {
                    if ("" !== $w && "-\0" !== \substr($w, 0, 2)) {
                        $r .= ': ~';
                    }
                }
                continue;
            }
            $r .= \ltrim($v);
            break;
        }
        if ($stack) {
            return ""; // Broken :(
        }
        if ("" === \trim($r)) {
            return '[' === $b ? '[]' : '{}';
        }
        return d(\trim(\rtrim($r), "\n"));
    }
    function q(string $v) {
        // `""…`
        // `''…`
        if (0 === \strpos($v, '""') || 2 === \strspn($v, "'")) {
            return [$v[0] . $v[1], \substr($v, 2)];
        }
        if ("" === $v || false === \strpos('"' . "'", $v[0])) {
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
    function v(?string $value, $array = false, array &$lot = []) {
        $from = \strtr(\trim($value ?? "", "\n"), [
            "\r\n" => "\n",
            "\r" => "\n"
        ]);
        if ("" === ($from = d($from))) {
            return null;
        }
        $i = -1;
        $r = [];
        foreach (\explode("\n", $from) as $v) {
            $d = \strspn($v, ' ');
            // Part of a block…
            if ($w = $r[$i] ?? 0) {
                $w = \rtrim(\strstr($w . "\n", "\n", true), " \t");
                if (false !== \strpos('>|', \trim(\rtrim(c($w), '+-0123456789')))) {
                    if ($d || "" === $v) {
                        $r[$i] .= "\n" . $v;
                        continue;
                    }
                    if ('#' === $v[0]) {
                        $r[$i] .= "\n";
                        continue;
                    }
                    $r[++$i] = $v;
                    continue;
                }
                if (('"' === ($c = $w[0] ?? 0) || "'" === $c)) {
                    if ("" === $v || "" === q($r[$i])) {
                        $r[$i] .= "\n" . $v;
                        continue;
                    }
                }
                if ("" === c($v)) {
                    continue;
                }
                if ("-\0" === \substr($w, 0, 2)) {
                    $last = \trim(\substr($r[$i], \strrpos("\n" . $r[$i], "\n-\0") + 2), " \n\t");
                    if (('"' === ($c = $last[0] ?? 0) || "'" === $c) && "" === q($last)[0]) {
                        $r[$i] .= "\n" . $v;
                        continue;
                    }
                    if ('-' === \trim(c($v))) {
                        $r[$i] .= "\n-\0";
                        continue;
                    }
                    if ('-' === ($v[0] ?? 0) && false !== \strpos(" \t", \substr($v, 1, 1))) {
                        $r[$i] .= "\n-\0" . \substr($v, 2);
                        continue;
                    }
                    if ($d) {
                        $r[$i] .= "\n" . $v;
                        continue;
                    }
                }
                if ("?\0" === \substr($w, 0, 2)) {
                    $last = \trim(\substr($r[$i], \strrpos("\n" . $r[$i], "\n?\0") + 2), " \n\t");
                    if (('"' === ($c = $last[0] ?? 0) || "'" === $c) && "" === q($last)[0]) {
                        $r[$i] .= "\n" . $v;
                        continue;
                    }
                    if (':' === \trim(c($v))) {
                        $r[$i] .= "\n:";
                        continue;
                    }
                    if (':' === ($v[0] ?? 0) && false !== \strpos(" \t", \substr($v, 1, 1))) {
                        $r[$i] .= "\n: " . \substr($v, 2);
                        continue;
                    }
                    if ('?' === ($v[0] ?? 0) && false !== \strpos(" \t", \substr($v, 1, 1))) {
                        $r[++$i] = "?\0" . \substr($v, 2);
                        continue;
                    }
                    if ($d) {
                        $r[$i] .= "\n" . $v;
                        continue;
                    }
                    $r[++$i] = $v;
                    continue;
                }
                if ("" !== ($q = q($w))[0] && ':' === (\trim($q[1])[0] ?? 0)) {
                    if ($d) {
                        $r[$i] .= "\n" . $v;
                        continue;
                    }
                    if ('?' === ($v[0] ?? 0) && false !== \strpos(" \t", \substr($v, 1, 1))) {
                        $v = "?\0" . \substr($v, 2);
                    }
                    $r[++$i] = $v;
                    continue;
                }
                $w = \trim(c($w));
                if ('[' === $w || ('[' === \substr($w, -1) && false !== \strpos(" \t", \substr($w, -2, 1)) && ':' === \trim(\substr($w, -3, 1)))) {
                    if (']' === \trim(c($v))) {
                        $v = ']';
                    }
                    $r[$i] .= "\n" . $v;
                    if ("" !== b(\substr($r[$i], \strlen($w) - 1))[0]) {
                        // echo '<pre style="border:1px solid">'.$r[$i].'</pre>';
                        $i += 1;
                    }
                    continue;
                }
                if ('{' === $w || ('{' === \substr($w, -1) && false !== \strpos(" \t", \substr($w, -2, 1)) && ':' === \trim(\substr($w, -3, 1)))) {
                    if ('}' === \trim(c($v))) {
                        $v = '}';
                    }
                    $r[$i] .= "\n" . $v;
                    if ("" !== b(\substr($r[$i], \strlen($w) - 1))[0]) {
                        // echo '<pre style="border:1px solid">'.$r[$i].'</pre>';
                        $i += 1;
                    }
                    continue;
                }
                if (':' === \substr(\trim(\strtok($w, '!&*')), -1)) {
                    if ($d) {
                        $r[$i] .= "\n" . $v;
                        continue;
                    }
                    if ('?' === ($v[0] ?? 0) && false !== \strpos(" \t", \substr($v, 1, 1))) {
                        $v = "?\0" . \substr($v, 2);
                    }
                    $r[++$i] = $v;
                    continue;
                }
                if (false !== ($n = \strpos($w, ":\t") ?: \strpos($w, ': '))) {
                    if (('"' === ($c = \trim(\substr($w, $n + 2))[0] ?? 0) || "'" === $c) && "" === q(\trim(\substr($r[$i], $n + 2)))[0]) {
                        $r[$i] .= "\n" . $v;
                        continue;
                    }
                    if (false !== \strpos('>|', \trim(\rtrim(c(\substr($w, $n + 2)), '+-0123456789')))) {
                        if ($d || "" === $v) {
                            $r[$i] .= "\n" . $v;
                            continue;
                        }
                        if ('#' === $v[0]) {
                            $r[$i] .= "\n";
                            continue;
                        }
                    }
                    if ('?' === ($v[0] ?? 0) && false !== \strpos(" \t", \substr($v, 1, 1))) {
                        $v = "?\0" . \substr($v, 2);
                    }
                    $r[++$i] = $v;
                    continue;
                }
                $r[$i] .= "\n" . $v;
                continue;
            }
            // Start of a block…
            if ("" === c($v)) {
                continue;
            }
            if ("" !== ($q = q($v))[0]) {
                if (':' === (($q[1] = \ltrim($q[1]))[0] ?? 0) && false !== \strpos(" \n\t", $s = \substr($q[1], 1, 1))) {
                    $qq = q(\ltrim(\substr($q[1], 1)));
                    $v = $q[0] . ':' . $s . $qq[0] . c($qq[1]);
                } else {
                    $v = $q[0] . c($q[1]);
                }
            } else {
                $v = c($v);
            }
            if ('-' === $v || '?' === $v) {
                $r[++$i] = $v . "\0";
                continue;
            }
            if ("" !== $v && false !== \strpos('-?', $v[0]) && false !== \strpos(" \t", \substr($v, 1, 1))) {
                $r[++$i] = $v[0] . "\0" . \substr($v, 2);
                continue;
            }
            // <https://yaml.org/spec/1.2.2#741-flow-sequences>
            if ('[' === $v || ('[' === \substr($v, -1) && false !== \strpos(" \t", \substr($v, -2, 1)) && ':' === \trim(\substr($v, -3, 1)))) {
                $r[++$i] = $v;
                continue;
            }
            // <https://yaml.org/spec/1.2.2#742-flow-mappings>
            if ('{' === $v || ('{' === \substr($v, -1) && false !== \strpos(" \t", \substr($v, -2, 1)) && ':' === \trim(\substr($v, -3, 1)))) {
                $r[++$i] = $v;
                continue;
            }
            $r[++$i] = $v;
        }
        $to = [];
        foreach ($r as $v) {
            // `!asdf asdf`
            // `&asdf asdf`
            // `*asdf asdf`
            if (0 !== ($c = $v[0] ?? 0) && false !== \strpos('!&*', $c) && $c !== \strtok($v, " \n\t")) {
                return e($v, $array, $lot);
            }
            // `>\n asdf…`
            // `[asdf…`
            // `{asdf…`
            // `|\n asdf…`
            if (0 !== $c && false !== \strpos('>[{|', $c)) {
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
                    if (':' === (($q[1] = \ltrim($q[1]))[0] ?? 0) && false !== \strpos(" \n\t", \substr($q[1], 1, 1))) {
                        // <https://github.com/nodeca/js-yaml/issues/189>
                        if (false !== \strpos($q[0], "\n")) {
                            $object = true;
                            continue; // Broken :(
                        }
                        $k = e($q[0]);
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
                        if ("" !== $v && false === \strpos('!&[{', $v[0]) && false !== ($n = \strpos($v, ':')) && false !== \strpos(" \t", \substr($v, $n + 1, 1))) {
                            $to[$k] = null; // Broken :(
                            continue;
                        }
                        $to[$k] = e($v, $array, $lot);
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
                    if ("" !== ($q = q($vv = \strtr(d(\ltrim($vv, "\n")), ["\n  " => "\n"])))[0]) {
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
                if (false !== ($n = \strpos($v, "\n:")) && false !== \strpos(" \n\t", \substr($v, $n + 2, 1))) {
                    $k = v(\strtr(\substr($v, 2, $n - 2), ["\n  " => "\n"]), $array, $lot);
                    $v = v(\strtr(\substr($v, $n + 3), ["\n  " => "\n"]), $array, $lot);
                } else if (false !== ($n = \strpos($v, ':')) && false !== \strpos(" \n\t", \substr($v, $n + 1, 1))) {
                    $k = v(\substr($v, 2, $n - 2), $array, $lot);
                    $v = v(\substr($v, $n + 2), $array, $lot);
                } else {
                    $k = v(\substr($v, 2), $array, $lot);
                    $v = null;
                }
                if (-\INF === $k || -\NAN === $k || \INF === $k || \NAN === $k || \is_array($k) || \is_object($k) || false === $k || null === $k || true === $k) {
                    $k = "\0" . \serialize($k) . "\0";
                }
                $to[$k] = $v;
                continue;
            }
            // `asdf:`
            if (':' === \substr($v, -1)) {
                $to[\trim(\substr($v, 0, -1))] = null;
                continue;
            }
            // `asdf: …`
            if (false !== ($n = \strpos($w = \strstr($v . "\n", "\n", true), ":\n") ?: \strpos($w, ":\t") ?: \strpos($w, ': '))) {
                // <https://github.com/nodeca/js-yaml/issues/189>
                if (false !== \strpos($k = \trim(\substr($v, 0, $n)), "\n")) {
                    $object = true;
                    continue; // Broken :(
                }
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
                if ("" !== $v && false === \strpos('!&[{', $v[0]) && false !== ($n = \strpos($v, ':')) && false !== \strpos(" \t", \substr($v, $n + 1, 1))) {
                    $to[$k] = null; // Broken :(
                    continue;
                }
                $to[$k] = v($v, $array, $lot);
                continue;
            }
            return e(d($v), $array, $lot);
        }
        return $to || isset($object) ? ($array ? $to : (object) $to) : null;
    }
}