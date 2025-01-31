<?php

namespace x\y_a_m_l {
    function from(?string $value, $array = false, array &$lot = []) {
        return from\v($value, $array, $lot);
    }
}

namespace x\y_a_m_l\from {
    function v(?string $value, $array = false, array &$lot = []) {
        $from = \strtr(\trim($value ?? "", "\n"), [
            "\r\n" => "\n",
            "\r" => "\n"
        ]);
        if ($dent = \strspn($from, ' ')) {
            $from = \substr(\strtr($from, [
                "\n" . \str_repeat(' ', $dent) => "\n"
            ]), $dent);
        }
        if ("" === $from) {
            return null;
        }
        $i = -1;
        $to = [];
        foreach (\explode("\n", $from) as $v) {
            if (isset($to[$i])) {
                $d = \strspn($v, " \t");
                $test = \rtrim(\strstr($to[$i], "\n", true) ?: $to[$i]);
                if ('-' === $test || '- ' === \substr($test, 0, 2)) {
                    if ('-' === \trim(\strtok($v, " \t#"))) {
                        $to[$i] .= "\n" . (('-' === \trim(\strstr($v, '#', true) ?: $v)) ? '-' : $v);
                        continue;
                    }
                    if ($d) {
                        $to[$i] .= "\n" . $v;
                        continue;
                    }
                }
                if (':' === \substr(\trim(\strtok($to[$i], '#&*')), -1)) {
                    if ($d) {
                        $to[$i] .= "\n" . $v;
                        continue;
                    }
                    $to[++$i] = $v;
                    continue;
                }
                if ('[' === $test || ('[' === \substr($test, -1) && false !== \strpos(" \n\t", \substr($test, -2, 1)) && ':' === \trim(\substr($test, -3, 1)))) {
                    if (']' === \trim(\strstr($v, '#', true) ?: $v)) {
                        $to[$i] .= "\n]";
                        continue;
                    }
                    if ($d) {
                        $to[$i] .= "\n" . $v;
                        continue;
                    }
                }
                if ('{' === $test || ('{' === \substr($test, -1) && false !== \strpos(" \n\t", \substr($test, -2, 1)) && ':' === \trim(\substr($test, -3, 1)))) {
                    if ('}' === \trim(\strstr($v, '#', true) ?: $v)) {
                        $to[$i] .= "\n}";
                        continue;
                    }
                    if ($d) {
                        $to[$i] .= "\n" . $v;
                        continue;
                    }
                }
            }
            $test = \rtrim(\strstr($v, '#', true) ?: $v);
            if ('-' === $test) {
                $to[++$i] = $test;
                continue;
            }
            // `[` or `asdf: [`
            if ('[' === $test || ('[' === \substr($test, -1) && false !== \strpos(" \t", \substr($test, -2, 1)) && ':' === \trim(\substr($test, -3, 1)))) {
                $to[++$i] = $test;
                continue;
            }
            // `{` or `asdf: {`
            if ('{' === $test || ('{' === \substr($test, -1) && false !== \strpos(" \t", \substr($test, -2, 1)) && ':' === \trim(\substr($test, -3, 1)))) {
                $to[++$i] = $test;
                continue;
            }
            $to[++$i] = $v;
        }
        echo '<pre style="border:1px solid">';
        echo htmlspecialchars($value);
        echo "\n\n";
        echo htmlspecialchars(var_export($to, true));
        echo '</pre>';
    }
}