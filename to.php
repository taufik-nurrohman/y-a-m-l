<?php

namespace x\y_a_m_l {
    function to($value, $dent = true): ?string {
        if (\is_int($dent)) {
            $dent = \str_repeat(' ', $dent > 0 ? $dent : 4);
        } else if (true === $dent || !\is_string($dent)) {
            $dent = \str_repeat(' ', 4);
        }
        return to\v($value, $dent);
    }
}

namespace x\y_a_m_l\to {
    function l(array $v) {
        if ([] === $v) {
            return true;
        }
        // PHP >=8.1
        if (\function_exists("\\array_is_list")) {
            return \array_is_list($v);
        }
        $k = -1;
        foreach ($v as $kk => $vv) {
            if ($kk !== ++$k) {
                return false;
            }
        }
        return true;
    }
    function q(string $v): string {
        if ("" === $v) {
            return '""';
        }
        if (\is_numeric($v)) {
            return "'" . $v . "'";
        }
        if (
            // ` asdf` or `asdf `
            ' ' === $v[0] || ' ' === \substr($v, -1) ||
            // `asdf:`
            ':' === \substr($v, -1) ||
            // `asdf #asdf`
            false !== ($n = \strpos($v, '#')) && false !== \strpos(" \n\t", \substr($v, $n - 1, 1)) ||
            // `asdf: asdf`
            false !== ($n = \strpos($v, ':')) && false !== \strpos(" \n\t", \substr($v, $n + 1, 1)) ||
            // <https://yaml.org/spec/1.2.2#56-miscellaneous-characters>
            // <https://yaml.org/spec/1.2.2#example-invalid-use-of-reserved-indicators>
            false !== \strpos('!"#%&*+,-.:>?@[]`{|}' . "'\\", $v[0]) ||
            false !== \strpos(',false,null,true,~,', ',' . \strtolower($v) . ',') ||
            \strlen($v) !== \strcspn($v, "<=>[\\]{|}")
        ) {
            return "'" . \strtr($v, [
                "'" => "''"
            ]) . "'";
        }
        if (false !== \strpos("\n\t", $v[0]) || false !== \strpos("\n\t", \substr($v, -1)) || $v !== \addcslashes($v, "\\")) {
            return \json_encode($v);
        }
        return $v;
    }
    function r(string $v, string $c = ""): string {
        $r = [];
        foreach (\explode("\n", $v) as $vv) {
            $r[] = "" === \trim($vv) ? \rtrim($c) : $c . $vv;
        }
        return \implode("\n", $r);
    }
    function v($value, string $dent) {
        if (false === $value) {
            return 'false';
        }
        if (null === $value) {
            return '~';
        }
        if (true === $value) {
            return 'true';
        }
        if (\is_float($value)) {
            if (\is_infinite($value)) {
                return '.INF';
            }
            if (\is_nan($value)) {
                return '.NAN';
            }
            $value = (string) $value;
            return false === \strpos($value, '.') ? $value . '.0' : $value;
        }
        if (\is_int($value)) {
            return (string) $value;
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('c');
        }
        if (\is_string($raw = $value)) {
            if ("" !== $value && false !== \strpos($value, "\0")) {
                $value = \base64_encode($value);
                // `120 - strlen('!!binary ')`
                if (\strlen($value) <= 111) {
                    return '!!binary ' . $value;
                }
                return "!!binary |\n" . $dent . \rtrim(\chunk_split($value, 120, "\n" . $dent));
            }
            $d = 0;
            $flow = false;
            $style = false === \strpos(\trim($value), "\n") ? '>' : '|';
            foreach (\explode("\n", $value) as $v) {
                if ("" === $v) {
                    continue;
                }
                if (0 === ($test = \strspn($v, ' '))) {
                    break;
                }
                $d = $test > $d && $d > 0 ? $d : $test;
            }
            if ($d > 0) {
                $value = \substr(\strtr($value, [
                    "\n" . \str_repeat(' ', $d) => "\n"
                ]), $d);
                $d = (string) $d;
            } else {
                $d = "";
            }
            if ('>' === $style && \strlen($value) > 120) {
                $flow = true;
                $value = \wordwrap($value, 120, "\n");
            }
            $v = "" !== $d ? \str_repeat(' ', (int) $d) : "";
            $value = $v . r(\strtr($value, [
                "\n" => "\n" . $dent . $v
            ]));
            if ("\n" === \substr($value, -1)) {
                if (false !== \strpos(" \n\t", \substr($value, -2, 1))) {
                    return $style . '+' . $d . "\n" . $dent . $value;
                }
                return $style . $d . "\n" . $dent . $value;
            }
            if ($flow || '|' === $style) {
                return $style . '-' . $d . "\n" . $dent . $value;
            }
            return q($raw);
        }
        if (\is_array($value) && l($value)) {
            if ([] === $value) {
                return '[]';
            }
            $r = [];
            $short = 0;
            foreach ($value as $v) {
                if (\is_string($v) && ("" === $v || \strlen($v) < 41)) {
                    $short += 1;
                } else if (\is_float($v) || \is_int($v) || \in_array($v, [-\INF, -\NAN, \INF, \NAN, false, null, true], true)) {
                    $short += 1;
                } else {
                    $short = 6; // Disable flow style value!
                }
                $v = v($v, $dent);
                if (false !== \strpos('>|', $v[0])) {
                    $short = 6; // Disable flow style value!
                }
                $r[] = r(\strtr($v, [
                    "\n" => "\n  "
                ]));
            }
            // Prefer flow style value?
            if ($short < 6) {
                return '[ ' . \implode(', ', $r) . ' ]';
            }
            return '- ' . \implode("\n- ", $r);
        }
        if (\is_object($value)) {
            if ($value instanceof \stdClass && [] === (array) $value) {
                return '{}';
            }
            if (\method_exists($value, '__toString')) {
                return "|-\n" . $dent . r(\trim(\strtr($value->__toString(), [
                    "\n" => "\n" . $dent
                ]), "\n"));
            }
        }
        if (\is_iterable($value)) {
            $r = [];
            $short = 0;
            foreach ($value as $k => $v) {
                $k = "\0" === $k ? "? ~\n" : (\is_string($k) && false !== \strpos($k, "\n") ? '? ' . v($k, '  ') . "\n" : q((string) $k));
                if (\is_string($v) && ("" === $v || \strlen($v) < 41)) {
                    $short += 1;
                } else if (\is_float($v) || \is_int($v) || \in_array($v, [-\INF, -\NAN, \INF, \NAN, false, null, true], true)) {
                    $short += 1;
                } else {
                    $short = 4; // Disable flow style value!
                }
                if (\is_iterable($v)) {
                    $v = v($v, $dent);
                    if (false !== \strpos('[{', $v[0])) {
                        $v = ' ' . $v;
                    } else {
                        $v = r("\n" . $dent . \strtr($v, [
                            "\n" => "\n" . $dent
                        ]));
                    }
                    $r[] = $k . ':' . $v;
                    continue;
                }
                if ('~' === ($v = v($v, $dent)) && '?' === ($k[0] ?? 0)) {
                    $r[] = \substr($k, 0, -1);
                    continue;
                }
                $r[] = $k . ': ' . $v;
            }
            // Prefer flow style value?
            if ($short < 4 && '?' !== ($k[0] ?? 0)) {
                return '{ ' . \implode(', ', $r) . ' }';
            }
            return "" !== ($value = \implode("\n", $r)) ? $value : null;
        }
        try {
            return q((string) $value);
        } catch (\Throwable $e) {
            return "~\n" . r((string) $e, '#! ');
        }
    }
}