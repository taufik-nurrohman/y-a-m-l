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
    function q(string $v, $e = false) {
        if ("" === $v || false === \strpos('"' . "'", $v[0])) {
            return ["", $v];
        }
        $r = [$v[0], ""];
        $v = \substr($v, 1);
        while ("" !== (string) $v) {
            $n = \strcspn($v, '"' . "'");
            if ('"' === \substr($v, $n, 1)) {
                if ("\\" === \substr($v, $n - 1, 1)) {
                    $r[0] .= \substr($v, 0, $n + 1);
                    $v = \substr($v, $n + 1);
                    continue;
                }
                $r[0] .= \substr($v, 0, $n += 1);
                $r[1] = \substr($v, $n);
                break;
            }
            if ("'" === \substr($v, $n, 1)) {
                if ("'" === \substr($v, $n + 1, 1)) {
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
            if ($w = $r[$i] ?? 0) {
                $d = \strspn($v, ' ');
                $w = \rtrim(\strstr($w, "\n", true) ?: $w, " \t");
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
                if (':' === \substr(\trim(\strtok($w, '!&*')), -1)) {
                    if ($d) {
                        $r[$i] .= "\n" . $v;
                        continue;
                    }
                    $r[++$i] = $v;
                    continue;
                }
                $w = \trim(c($w));
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
            if ("" === c($v)) {
                continue;
            }
            if ("" !== ($q = q($v))[0]) {
                if (':' === (($v = \trim($q[1]))[0] ?? 0)) {
                    $qq = q(\trim(\substr($v, 1)));
                    $v = $q[0] . ': ' . $qq[0] . c($qq[1]);
                } else {
                    $v = \rtrim($q[0] . c($q[1]));
                }
            } else {
                $v = \rtrim(c($v));
            }
            if ('-' === $v) {
                $r[++$i] = $v . "\0";
                continue;
            }
            if ('-' === ($v[0] ?? 0) && false !== \strpos(" \t", \substr($v, 1, 1))) {
                $r[++$i] = "-\0" . \substr($v, 2);
                continue;
            }
            // `[` or `asdf: [`
            if ('[' === $v || ('[' === \substr($v, -1) && false !== \strpos(" \t", \substr($v, -2, 1)) && ':' === \trim(\substr($v, -3, 1)))) {
                $r[++$i] = $v;
                continue;
            }
            // `{` or `asdf: {`
            if ('{' === $v || ('{' === \substr($v, -1) && false !== \strpos(" \t", \substr($v, -2, 1)) && ':' === \trim(\substr($v, -3, 1)))) {
                $r[++$i] = $v;
                continue;
            }
            $r[++$i] = $v;
        }
        $to = [];
        foreach ($r as $v) {
            if (false !== \strpos('[{', ($v[0] ?? "\0"))) {
                $to[] = $v; // TODO
                continue;
            }
            if ("" !== ($q = q($v))[0] && ':' === (\trim($q[1])[0] ?? 0)) {
                $q[1] = \ltrim(\substr($q[1], 1));
                $to[$q[0]] = $q[1];
                continue;
            }
            if (false !== ($n = \strpos($v, ':')) && false !== \strpos(" \n\t", \substr($v, $n + 1, 1))) {
                $to[\trim(\substr($v, 0, $n))] = \ltrim(\substr($v, $n + 1));
                continue;
            }
            $to[] = $v;
        }
        echo '<pre style="border:2px solid green">';
        echo htmlspecialchars($value);
        echo '</pre>';
        echo '<pre style="border:2px solid red">';
        echo htmlspecialchars(json_encode($to, JSON_PRETTY_PRINT));
        echo '</pre>';
    }
}