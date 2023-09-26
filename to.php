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
            $value = (string) $value;
            return false === \strpos($value, '.') ? $value . '.0' : $value;
        }
        if (\is_int($value)) {
            return (string) $value;
        }
        if (\is_int($dent)) {
            $dent = \str_repeat(' ', $dent);
        } else if (true === $dent || !\is_string($dent)) {
            $dent = \str_repeat(' ', 4);
        }
        if (\is_string($value)) {
            $fold = false;
            $prefix = false === \strpos(\trim($value), "\n") ? '>' : '|';
            if ('>' === $prefix && \strlen($value) > 120) {
                $fold = true;
                $value = \wordwrap($value, 120, "\n");
            }
            $value = \preg_replace('/^[ \t]+$/m', "", \strtr($value, [
                "\n" => "\n" . $dent
            ]));
            if ("\n" === \substr($value, -1)) {
                if (false !== \strpos(" \n\t", \substr($value, -2, 1))) {
                    return $prefix . "+\n" . $dent . $value;
                }
                return $prefix . "\n" . $dent . $value;
            }
            if ($fold || '|' === $prefix) {
                return $prefix . "-\n" . $dent . $value;
            }
            return to\q($value);
        }
        if (\is_array($value)) {
            if ([] === $value) {
                return '[]';
            }
            if (to\l($value)) {
                $out = [];
                foreach ($value as $v) {
                    $out[] = \strtr(to($v, $dent), [
                        "\n" => "\n  " // Hard-coded
                    ]);
                }
                return '- ' . \implode("\n- ", $out);
            }
        }
        if (\is_object($value) && $value instanceof \stdClass && [] === (array) $value) {
            return '{}';
        }
        if (\is_iterable($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                if (\is_iterable($v)) {
                    $out[] = to\q($k, true) . ":\n" . $dent . \strtr(to($v, $dent), [
                        "\n" => "\n" . $dent
                    ]);
                    continue;
                }
                $out[] = to\q($k, true) . ': ' . to($v, $dent);
            }
            return \implode("\n", $out);
        }
        return to\q((string) $value);
    }
}

namespace x\y_a_m_l\to {
    function l(array $value) {
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
    function q(string $value, $key = false): string {
        if ("" === $value) {
            return '""';
        }
        if (\is_numeric($value)) {
            return "'" . $value . "'";
        }
        if (false !== \strpos(',+.INF,+.Inf,+.NAN,+.Nan,+.inf,+.nan,-.INF,-.Inf,-.NAN,-.Nan,-.inf,-.nan,.INF,.Inf,.NAN,.Nan,.inf,.nan,FALSE,False,NULL,Null,TRUE,True,false,null,true,~,', ',' . $value . ',')) {
            return "'" . $value . "'";
        }
        if (\strlen($value) !== \strcspn($value, '!"#%&\'*,-:<=>?@[\\]`{|}')) {
            return "'" . \strtr($value, [
                "'" => "''"
            ]) . "'";
        }
        // <https://symfony.com/doc/7.0/reference/formats/yaml.html>
        // TODO: \L\N\P\_\a\b\e
        if (\strlen($value) !== \strcspn($value, "\0\f\n\r\t\v\x01\x02\x03\x04\x05\x06\x0e\x0f\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1a\x1c\x1d\x1e\x1f")) {
            return \json_encode($value);
        }
        return $value;
    }
}