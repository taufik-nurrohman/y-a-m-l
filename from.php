<?php

namespace x\y_a_m_l {
    function from(?string $value, $array = false, array &$lot = []) {
        return from\v($value, $array, $lot);
    }
}

namespace x\y_a_m_l\from {
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
                return $v;
            }
            $r = "";
            foreach (\explode("\n", \strtr($v, [
                "\\ " => "",
                "\\\n" => "",
                "\\\t" => ""
            ])) as $v) {
                if ("" === ($v = \trim($v))) {
                    $r .= "\\n";
                    continue;
                }
                $r .= "" === $r || "\\n" === \substr($r, -2) ? $v : ' ' . $v;
            }
            return \json_decode($r) ?? $r;
        }
        if ("'" === $v[0] && "'" === \substr($v, -1)) {
            if (false !== \strpos($v, "\\'")) {
                return $v;
            }
            $r = "";
            foreach (\explode("\n", \strtr(\substr($v, 1, -1), [
                "''" => "'"
            ])) as $v) {
                if ("" === ($v = \trim($v))) {
                    $r .= "\n";
                    continue;
                }
                $r .= "" === $r || "\n" === \substr($r, -1) ? $v : ' ' . $v;
            }
            return $r;
        }
        if (false !== \strpos('[{', $v[0])) {
            if (0 === \strpos($v = o($v), "-\0")) {
                $list = [];
                foreach (\explode("\n-\0", \substr($v, 2)) as $v) {
                    $list[] = v(d(\ltrim($v, "\n")), $array, $lot);
                }
                return $list;
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
        echo '<pre style="background:black;color:white">'.htmlspecialchars($k).'</pre>';
        echo '<pre style="background:blue;color:white">'.htmlspecialchars($v).'</pre>';
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
        echo '<pre style="background:green;color:white">'.htmlspecialchars($d?:'#').'</pre>';
        echo '<pre style="background:green;color:white">'.htmlspecialchars($e?:'#').'</pre>';
        return $v;
    }
    function o(string $v) {
        $d = $r = "";
        $list = false;
        while ("" !== (string) $v) {
            if ($n = \strcspn($v, '"' . "'" . '#,:[]{}')) {
                $r .= \trim(\substr($v, 0, $n));
                $v = \trim(\substr($v, $n));
            }
            if (('"' === ($c = $v[0] ?? 0) || "'" === $c) && "" !== ($q = q($v))[0]) {
                $r .= $q[0];
                $v = \substr($v, \strlen($q[0]));
                continue;
            }
            if ('#' === $c) {
                if (false === ($x = \strpos($v, "\n"))) {
                    break;
                }
                $v = \trim(\substr($v, $x + 1));
                continue;
            }
            if (',' === $c) {
                if ("" !== ($q = q($w = \trim(\strrchr($r, "\n"), " \n\t")))[0] && (':' === \substr($q[1] = \trim($q[1]), -1) || ': ' === \substr($q[1], 0, 2))) {
                    // …
                } else if (':' === \substr($w, -1) || false !== \strpos($w, ': ')) {
                    // …
                } else {
                    if ("" !== $w && "-\0" !== \substr($w, 0, 2)) {
                        $r .= ': ~';
                    }
                }
                $r .= "\n" . $d . ($list ? "-\0" : "");
                $v = \trim(\substr($v, 1));
                continue;
            }
            if (':' === $c) {
                $r .= ': ';
                $v = \trim(\substr($v, 1));
                continue;
            }
            if ('[' === $c) {
                $d .= ' ';
                $list = true;
                $r .= "\n" . $d . "-\0";
                $v = \trim(\substr($v, 1));
                continue;
            }
            if (']' === $c) {
                $d = \substr($d, 0, -1);
                $list = false;
                if ("" !== ($q = q($w = \trim(\strrchr($r, "\n"), " \n\t")))[0] && (':' === \substr($q[1] = \trim($q[1]), -1) || ': ' === \substr($q[1], 0, 2))) {
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
                $v = \trim(\substr($v, 1));
                continue;
            }
            if ('{' === $c) {
                $d .= ' ';
                $r .= "\n" . $d;
                $v = \trim(\substr($v, 1));
                continue;
            }
            if ('}' === $c) {
                $d = \substr($d, 0, -1);
                if ("" !== ($q = q($w = \trim(\strrchr($r, "\n"), " \n\t")))[0] && (':' === \substr($q[1] = \trim($q[1]), -1) || ': ' === \substr($q[1], 0, 2))) {
                    // …
                } else if (':' === \substr($w, -1) || false !== \strpos($w, ': ')) {
                    // …
                } else {
                    if ("" !== $w && "-\0" !== \substr($w, 0, 2)) {
                        $r .= ': ~';
                    }
                }
                $v = \trim(\substr($v, 1));
                continue;
            }
            $r .= \trim($v);
            break;
        }
        return d(\trim($r, "\n"));
    }
    function q(string $v) {
        if ("" === $v || false === \strpos('"' . "'", $v[0])) {
            return ["", $v];
        }
        $r = [$v[0], ""];
        $v = \substr($v, 1);
        while ("" !== (string) $v) {
            $n = \strcspn($v, '"' . "'");
            // <https://yaml.org/spec/1.2.2#731-double-quoted-style>
            if ('"' === ($c = \substr($v, $n, 1))) {
                if ("\\" === \substr($v, $n - 1, 1)) {
                    $r[0] .= \substr($v, 0, $n + 1);
                    $v = \substr($v, $n + 1);
                    continue;
                }
                $r[0] .= \substr($v, 0, $n += 1);
                $r[1] = \substr($v, $n);
                break;
            }
            // <https://yaml.org/spec/1.2.2#732-single-quoted-style>
            if ("'" === $c) {
                if ($c === \substr($v, $n + 1, 1)) {
                    $r[0] .= \substr($v, 0, $n += 1);
                    $v = \substr($v, $n);
                    continue;
                }
                $r[0] .= \substr($v, 0, $n += 1);
                $r[1] = \substr($v, $n);
                break;
            }
            $r = ["", $r[0] . $v];
            break;
        }
        return $r;
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
    function v(?string $value, $array = false, array &$lot = [], $deep = 0) {
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
                $w = \rtrim(\strstr($w, "\n", true) ?: $w, " \t");
                if ('"' === ($w[0] ?? 0) && "" === q($w)[0]) {
                    $r[$i] .= "\n" . $v;
                    continue;
                }
                if ("'" === ($w[0] ?? 0) && "" === q($w)[0]) {
                    $r[$i] .= "\n" . $v;
                    continue;
                }
                if ("" === c($v)) {
                    continue;
                }
                if ("-\0" === \substr($w, 0, 2)) {
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
                if (false !== \strpos('>|', \trim(\rtrim(\rtrim($w, '0123456789'), '+-')))) {
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
                    $r[++$i] = $v;
                    continue;
                }
                $w = \trim(c($w));
                if (':' === \substr(\trim(\strtok($w, '!&*')), -1)) {
                    if ($d) {
                        $r[$i] .= "\n" . $v;
                        continue;
                    }
                    $r[++$i] = $v;
                    continue;
                }
                if ('[' === $w || ('[' === \substr($w, -1) && false !== \strpos(" \t", \substr($w, -2, 1)) && ':' === \trim(\substr($w, -3, 1)))) {
                    if (']' === \trim(c($v))) {
                        $r[$i++] .= "\n]";
                        continue;
                    }
                    if ($d) {
                        $r[$i] .= "\n" . $v;
                        continue;
                    }
                }
                if ('{' === $w || ('{' === \substr($w, -1) && false !== \strpos(" \t", \substr($w, -2, 1)) && ':' === \trim(\substr($w, -3, 1)))) {
                    if ('}' === \trim(c($v))) {
                        $r[$i++] .= "\n}";
                        continue;
                    }
                    if ($d) {
                        $r[$i] .= "\n" . $v;
                        continue;
                    }
                }
                if (false !== ($n = \strpos($w, ':')) && false !== \strpos(" \t", \substr($w, $n + 1, 1))) {
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
                if (':' === (($v = \trim($q[1]))[0] ?? 0)) {
                    $qq = q(\trim(\substr($v, 1)));
                    $v = $q[0] . ': ' . \rtrim($qq[0] . c($qq[1]));
                } else {
                    $v = \rtrim($q[0] . c($q[1]));
                }
            } else {
                $v = \rtrim(c($v));
            }
            if ('-' === $v) {
                $r[++$i] = "-\0";
                continue;
            }
            if ('-' === ($v[0] ?? 0) && false !== \strpos(" \t", \substr($v, 1, 1))) {
                $r[++$i] = "-\0" . \substr($v, 2);
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
        $batch = false;
        $to = [];
        foreach ($r as $v) {
            if ('!' === ($c = $v[0] ?? 0) && '!' !== \strtok($v, " \n\t")) {
                $to[] = e($v, $array, $lot);
                continue;
            }
            if ('[' === $c) {
                $to[] = e($v, $array, $lot);
                continue;
            }
            if ('{' === $c) {
                $to[] = e($v, $array, $lot);
                continue;
            }
            if ("-\0" === \substr($v, 0, 2)) {
                $list = [];
                foreach (\explode("\n-\0", \substr($v, 2)) as $vv) {
                    $list[] = v(d(\ltrim($vv, "\n")), $array, $lot);
                }
                $to[] = $list;
                continue;
            }
            if (0 !== $c && false !== \strpos('>!', $c)) {
                $to[] = f($v);
                continue;
            }
            if ("" !== ($q = q($v))[0] && ':' === (\trim($q[1])[0] ?? 0)) {
                $batch = true;
                $k = e($q[0]);
                $v = \substr(\ltrim($q[1]), 1);
                if ("\n" === ($v[0] ?? 0)) {
                    $to[$k] = v(d(\substr($v, 1)), $array, $lot, $deep + 1);
                    continue;
                }
                $to[$k] = e(d($v), $array, $lot);
                continue;
                continue;
            }
            if (false !== ($n = \strpos($v, ':')) && false !== \strpos(" \n\t", \substr($v, $n + 1, 1))) {
                $batch = true;
                $k = \trim(\substr($v, 0, $n));
                $v = \substr($v, $n + 1);
                if ("\n" === ($v[0] ?? 0)) {
                    $to[$k] = v(d(\substr($v, 1)), $array, $lot, $deep + 1);
                    continue;
                }
                $to[$k] = e(d($v), $array, $lot);
                continue;
            }
            $to[] = e(d($v), $array, $lot);
        }
        // echo '<pre style="border:2px solid green">';
        // echo htmlspecialchars($value);
        // echo '</pre>';
        // echo '<pre style="border:2px solid red">';
        // echo htmlspecialchars(json_encode($to, JSON_PRETTY_PRINT));
        // echo '</pre>';
        if (!$batch && 0 === $deep && ($count = \count($to)) < 2) {
            return 1 === $count ? \reset($to) : null;
        }
        return $array ? $to : (\array_is_list($to) ? $to : (object) $to);
    }
}