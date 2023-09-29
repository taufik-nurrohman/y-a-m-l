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
        if ($value instanceof \DateTime) {
            return $value->format('c');
        }
        if (\is_int($dent)) {
            $dent = \str_repeat(' ', $dent);
        } else if (true === $dent || !\is_string($dent)) {
            $dent = \str_repeat(' ', 4);
        }
        if (\is_string($value)) {
            if ("" !== $value && \preg_match('/[\x80-\xFF]/', $value)) {
                return '!!binary ' . \base64_encode($value);
            }
            $d = 0;
            $flow = false;
            $prefix = false === \strpos(\trim($value), "\n") ? '>' : '|';
            foreach (\explode("\n", $value) as $v) {
                if ("" === $v) {
                    continue;
                }
                if ($test = \strspn($v, ' ')) {
                    $d = $test > $d && $d > 0 ? $d : $test;
                } else {
                    $d = 0;
                }
            }
            if ($d > 0) {
                $value = \substr(\strtr($value, [
                    "\n" . \str_repeat(' ', $d) => "\n"
                ]), $d);
                $d = (string) $d;
            } else {
                $d = "";
            }
            if ('>' === $prefix && \strlen($value) > 120) {
                $flow = true;
                $value = \wordwrap($value, 120, "\n");
            }
            $value = \preg_replace('/^[ \t]+$/m', "", \strtr($value, [
                "\n" => "\n" . $dent
            ]));
            if ("\n" === \substr($value, -1)) {
                if (false !== \strpos(" \n\t", \substr($value, -2, 1))) {
                    return $prefix . $d . "+\n" . $dent . $value;
                }
                return $prefix . $d . "\n" . $dent . $value;
            }
            if ($flow || '|' === $prefix) {
                return $prefix . $d . "-\n" . $dent . $value;
            }
            return to\q($value);
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
            return \implode("\n", $out);
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
        if (false !== \strpos(' !"#&\'*+-.0123456789?', $value[0])) {
            return "'" . \strtr($value, [
                "'" => "''"
            ]) . "'";
        }
        if (' ' === \substr($value, -1)) {
            return "'" . $value . "'";
        }
        if (false !== \strpos(',false,null,true,~,', ',' . \strtolower($value) . ',')) {
            return "'" . $value . "'";
        }
        if (\strlen($value) !== \strcspn($value, ',<=>[\\]`{|}')) {
            return "'" . $value . "'";
        }
        if (false !== \strpos($value, ":\n") || false !== \strpos($value, ":\t") || false !== \strpos($value, ': ')) {
            return "'" . $value . "'";
        }
        if ($value !== \addcslashes($value, "\\")) {
            return \json_encode($value);
        }
        return $value;
    }
}