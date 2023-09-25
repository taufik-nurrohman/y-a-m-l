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
            $prefix = false === \strpos(\trim($value), "\n") ? '>' : '|';
            if ("\n" === \substr($value, -1)) {
                $value = \strtr(\strtr($value, [
                    "\n" => "\n" . $dent
                ]), [
                    "\n" . $dent . "\n" => "\n\n"
                ]);
                if (false !== \strpos(" \n\t", \substr($value, -2, 1))) {
                    return $prefix . "+\n" . $value;
                }
                return $prefix . "\n" . $value;
            }
            if ('|' === $prefix) {
                return $prefix . "\n" . $value;
            }
            return q($value);
        }
        if (\is_array($value)) {
            if ([] === $value) {
                return '[]';
            }
            if (l($value)) {
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
                    $out[] = $k . ":\n" . $dent . \strtr(to($v, $dent), [
                        "\n" => "\n" . $dent
                    ]);
                    continue;
                }
                $out[] = q($k, true) . ': ' . to($v);
            }
            return \implode("\n", $out);
        }
        return q((string) $value);
    }
}

namespace x\y_a_m_l\to {
    function l(array $v) {
        // PHP >=8.1
        if (\function_exists("\\array_is_list")) {
            return \array_is_list($v);
        }
        $key = -1;
        foreach ($v as $kk => $vv) {
            if ($kk !== ++$key) {
                return false;
            }
        }
        return true;
    }
    // <https://symfony.com/doc/7.0/reference/formats/yaml.html>
    function q(string $v, $key = false): string {
        if ("" === $v) {
            return '""';
        }
        if (\is_numeric($v)) {
            return "'" . $v . "'";
        }
        if (false !== \strpos(',+.INF,+.Inf,+.NAN,+.Nan,+.inf,+.nan,-.INF,-.Inf,-.NAN,-.Nan,-.inf,-.nan,.INF,.Inf,.NAN,.Nan,.inf,.nan,FALSE,False,NULL,Null,TRUE,True,false,null,true,~,', ',' . $v . ',')) {
            return "'" . $v . "'";
        }
        if (\strlen($v) !== \strcspn($v, '!"#%&\'*,' . ($key ? "" : '-') . ':<=>?@[\\]`{|}')) {
            return "'" . \strtr($v, [
                "'" => "''"
            ]) . "'";
        }
        if (\strlen($v) !== \strcspn($v, "\0\L\N\P\_\a\b\e\f\n\r\t\v\x01\x02\x03\x04\x05\x06\x0e\x0f\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1a\x1c\x1d\x1e\x1f")) {
            return \json_encode($v);
        }
        return $v;
    }
}