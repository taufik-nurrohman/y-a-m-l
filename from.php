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
        if ('&' === ($v[0] ?? 0)) {
            $k = \strtok($v, " \n\t");
            $v = \substr($v, \strlen($k) + 1);
            $lot[$k] = $v = v(d($v), $array, $lot);
            return $v;
        }
        if ('*' === ($v[0] ?? 0)) {
            return $lot['&' . \strtok(\substr($v, 1), " \n\t")] ?? null;
        }
        if ("" === $v) {
            return null;
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
        if ('"' === ($v[0] ?? 0) && '"' === \substr($v, -1)) {
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
        if ("'" === ($v[0] ?? 0) && "'" === \substr($v, -1)) {
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
        if (\strlen($v) > 2 && '0' === $v[0]) {
            // Hex
            if (\preg_match('/^0x[a-f\d]+$/i', $v)) {
                return \hexdec($v);
            }
            // Octal
            if (\preg_match('/^0o?[0-7]+$/i', $v)) {
                if (false !== \strpos('Oo', $v[1])) {
                    // PHP < 8.1
                    $v = \substr($v, 2);
                }
                return \octdec($v);
            }
        }
        // <https://yaml.org/spec/1.2.2#10214-floating-point>
        if (\preg_match('/^-?(0|[1-9][0-9]*)(\.[0-9]*)?([eE][-+]?[0-9]+)$/', $v)) {
            return (float) $v;
        }
        // <https://yaml.org/type/timestamp.html>
        if (\is_numeric($v[0]) && \preg_match('/^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]|[0-9][0-9][0-9][0-9]-[0-9][0-9]?-[0-9][0-9]?([Tt]|[ \t]+)[0-9][0-9]?:[0-9][0-9]:[0-9][0-9](\.[0-9]*)?(([ \t]*)Z|[-+][0-9][0-9]?(:[0-9][0-9])?)?$/', $v)) {
            return new \DateTime($v);
        }
        if (\is_numeric($v)) {
            return 0 + $v;
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
            if ('[' === ($v[0] ?? 0)) {
                $to[] = $v;
                continue;
            }
            if ('{' === ($v[0] ?? 0)) {
                $to[] = $v;
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
            if ("" !== ($q = q($v))[0] && ':' === (\trim($q[1])[0] ?? 0)) {
                $batch = true;
                $q[1] = \ltrim(\substr($q[1], 1));
                $to[e($q[0])] = $q[1];
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
        echo '<pre style="border:2px solid green">';
        echo htmlspecialchars($value);
        echo '</pre>';
        echo '<pre style="border:2px solid red">';
        echo htmlspecialchars(json_encode($to, JSON_PRETTY_PRINT));
        echo '</pre>';
        if (0 === $deep && 1 === \count($to) && !$batch) {
            return \reset($to);
        }
        return $to;
    }
}