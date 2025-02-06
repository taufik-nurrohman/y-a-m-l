<?php

namespace x\y_a_m_l {
    function to($value, $dent = true): ?string {
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
        if (\is_int($dent)) {
            $dent = \str_repeat(' ', $dent);
        } else if (true === $dent || !\is_string($dent)) {
            $dent = \str_repeat(' ', 4);
        }
        if (\is_string($raw = $value)) {
            if ("" !== $value && false !== \strpos($value, "\0")) {
                $value = \base64_encode($value);
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
            $value = $v . \preg_replace('/^[ \t]+$/m', "", \strtr($value, [
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
            return to\q($raw);
        }
        if (\is_array($value) && to\l($value)) {
            if ([] === $value) {
                return '[]';
            }
            $out = [];
            $short = 0;
            foreach ($value as $v) {
                if (\is_string($v) && ("" === $v || \strlen($v) < 41)) {
                    $short += 1;
                } else if (\is_float($v) || \is_int($v) || \in_array($v, [-\INF, -\NAN, \INF, \NAN, false, null, true], true)) {
                    $short += 1;
                } else {
                    $short = 6; // Disable flow style value!
                }
                $out[] = \strtr(to($v, $dent), [
                    "\n" => "\n  "
                ]);
            }
            // Prefer flow style value?
            if ($short < 6) {
                return '[ ' . \implode(', ', $out) . ' ]';
            }
            return '- ' . \implode("\n- ", $out);
        }
        if (\is_iterable($value)) {
            if (\is_object($value) && $value instanceof \stdClass && [] === (array) $value) {
                return '{}';
            }
            $out = [];
            $short = 0;
            foreach ($value as $k => $v) {
                if (\is_string($v) && ("" === $v || \strlen($v) < 41)) {
                    $short += 1;
                } else if (\is_float($v) || \is_int($v) || \in_array($v, [-\INF, -\NAN, \INF, \NAN, false, null, true], true)) {
                    $short += 1;
                } else {
                    $short = 4; // Disable flow style value!
                }
                if (\is_iterable($v)) {
                    $v = to($v, $dent);
                    if (false !== \strpos('[{', $v[0])) {
                        $v = ' ' . $v;
                    } else {
                        $v = "\n" . $dent . \strtr($v, [
                            "\n" => "\n" . $dent
                        ]);
                    }
                    $out[] = to\q($k) . ':' . $v;
                    continue;
                }
                $out[] = to\q($k) . ': ' . to($v, $dent);
            }
            // Prefer flow style value?
            if ($short < 4) {
                return '{ ' . \implode(', ', $out) . ' }';
            }
            return "" !== ($value = \implode("\n", $out)) ? $value : null;
        }
        return to\q((string) $value);
    }
}

namespace x\y_a_m_l\to {
    function l(array $value) {
        if ([] === $value) {
            return true;
        }
        // PHP >=8.1
        if (\function_exists("\\array_is_list")) {
            return \array_is_list($value);
        }
        $key = -1;
        foreach ($value as $k => $v) {
            if ($k !== ++$key) {
                return false;
            }
        }
        return true;
    }
    function q(string $value): string {
        if ("" === $value) {
            return '""';
        }
        if (
            (false !== ($n = \strpos($value, '#')) && false !== \strpos(" \n\t", \substr($value, $n - 1, 1))) ||
            ' ' === $value[0] ||
            ' ' === \substr($value, -1) ||
            ':' === \substr($value, -1) ||
            false !== \strpos($value, ":\n") ||
            false !== \strpos($value, ":\t") ||
            false !== \strpos($value, ': ') ||
            false !== \strpos('!"#&\'*+-.0123456789?', $value[0]) ||
            false !== \strpos(',false,null,true,~,', ',' . \strtolower($value) . ',') ||
            \strlen($value) !== \strcspn($value, '<=>[\\]`{|}')
        ) {
            return "'" . \strtr($value, [
                "'" => "''"
            ]) . "'";
        }
        if ($value !== \addcslashes($value, "\\")) {
            return \json_encode($value);
        }
        return $value;
    }
}