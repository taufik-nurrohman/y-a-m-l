<?php

namespace x\y_a_m_l {
    function from(?string $value, $array = false, array &$lot = []) {
        if ("" === ($value = \trim($raw = $value ?? ""))) {
            return null;
        }
        // Normalize line break(s)
        $value = \strtr($value, [
            "\r\n" => "\n",
            "\r" => "\n"
        ]);
        if ('""' === $value || "''" === $value) {
            return "";
        }
        if ('[]' === $value) {
            return [];
        }
        if ('false' === $value || 'FALSE' === $value) {
            return false;
        }
        if ('~' === $value || 'null' === $value || 'NULL' === $value) {
            return null;
        }
        if ('true' === $value || 'TRUE' === $value) {
            return true;
        }
        if ('{}' === $value) {
            return $array ? [] : (object) [];
        }
        // A comment
        if ('#' === $value[0]) {
            return null;
        }
        // <https://yaml.org/spec/1.2.2#692-node-anchors>
        if (false !== \strpos('&*', $value[0]) && \preg_match('/^([&*])([^\s,\[\]{}]+)(\s+|$)/', $value, $m)) {
            if ('&' === $m[1]) {
                $lot[0][$m[2]] = from($value = \substr($value, \strlen($m[0])), $array, $lot);
                $value = &$lot[0][$m[2]];
                return $value;
            }
            return $lot[0][$m[2]] ?? null;
        }
        // List-style value
        if ('-' === $value[0] && \strlen($value) > 2 && false !== \strpos(" \n\t", $value[1])) {
            $out = [];
            foreach (\preg_split('/\n-[ \n\t]/', \substr($value, 2)) as $v) {
                $out[] = from($v, $array, $lot);
            }
            return $out;
        }
        // Fold-style or literal-style value
        if (false !== \strpos('>|', $value[0])) {
            [$a, $b] = \explode("\n", $raw, 2);
            $a = \trim(\strstr($a, '#', true) ?: $a);
            $b = \preg_replace('/^#.*$/m', "", $b);
            $dent = \strspn(\trim($b, "\n"), ' ');
            $b = \substr("\n" . \strtr($b, [
                "\n" . \str_repeat(' ', $dent) => "\n"
            ]), 1);
            if (isset($a[1])) {
                $chomp = \substr($a, -1);
                if (\is_numeric($chomp)) {
                    $chomp = "";
                    $dent = (int) \substr($a, 1);
                } else {
                    $dent = (int) \substr($a, 1, -1);
                }
                if ($dent > 0) {
                    $b = \substr(\strtr("\n" . ($d = \str_repeat(' ', $dent)) . \strtr($b, [
                        "\n" => "\n" . $d
                    ]), [
                        "\n" . $d . "\n" => "\n\n"
                    ]), 1);
                }
            } else {
                $chomp = "";
            }
            if ("" !== $chomp && false === \strpos('+-', $chomp)) {
                return null; // :(
            }
            if ('+' !== $chomp) {
                $b = \rtrim($b) . ("" === $chomp ? "\n" : "");
            }
            if ('>' === $a[0]) {
                // TODO
            }
            return $b;
        }
        if (\is_numeric($value)) {
            if (\strlen($value) > 1 && '0' === $value[0] && false === \strpos($value, '.')) {
                return $value; // TODO
            }
            return false !== \strpos($value, '.') ? (float) $value : (int) $value;
        }
        $str = '"(?>[^"\\\\]|\\\\.)*"|\'(?>\'\'|[^\'])*\'';
        if ('[' === $value[0] && ']' === \substr($value, -1) || '{' === $value[0] && '}' === \substr($value, -1)) {
            $out = "";
            // Validate to JSON
            foreach (\preg_split('/\s*(' . $str . '|[\[\]\{\}:,])\s*/', $value, -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY) as $v) {
                if ('~' === $v) {
                    $out .= 'null';
                    continue;
                }
                if ('FALSE' === $v || 'NULL' === $v || 'TRUE' === $v || 'false' === $v || 'null' === $v || 'true' === $v) {
                    $out .= \strtolower($v);
                    continue;
                }
                if (\is_numeric($v)) {
                    $out .= $v;
                    continue;
                }
                $out .= false !== \strpos(',:[]{}', $v) ? $v : \json_encode(from($v, $array, $lot), false, 1);
            }
            return \json_decode($out) ?? $value;
        }
        if ("'" === $value[0] && "'" === \substr($value, -1)) {
            return \strtr(\substr($value, 1, -1), [
                "''" => "'"
            ]);
        }
        if ('"' === $value[0] && '"' === \substr($value, -1)) {
            try {
                $value = \json_decode($value, false, 1, \JSON_THROW_ON_ERROR);
            } catch (\Throwable $e) {
                $value = \strtr(\substr($value, 1, -1), [
                    '\"' => '"'
                ]);
            }
            return $value;
        }
        if (false === ($n = \strpos($value, ':')) || false === \strpos(" \t", \substr($value, $n + 1, 1))) {
            return \trim(\preg_replace('/\s+/', ' ', \strstr($value, '#', true) ?: $value));
        }
        $block = -1;
        $blocks = [];
        $rows = \explode("\n", $value);
        foreach ($rows as $row) {
            $dent = \strspn($row, ' ');
            $current = $dent > 0 ? \substr($row, $dent) : $row;
            if ($prev = $blocks[$block] ?? 0) {
                // A blank line
                if ("" === $current) {
                    $blocks[$block] .= "\n";
                    continue;
                }
                // A comment
                if ('#' === $current[0]) {
                    continue;
                }
                // A list
                if ('-' === \trim(\strstr($current, '#', true) ?: $current)) {
                    $blocks[$block] .= "\n- ";
                    continue;
                }
                if ('-' === $current[0] && false !== \strpos(" \t", $current[1])) {
                    $blocks[$block] .= "\n- " . \substr($current, 2);
                    continue;
                }
                if ($dent > 0) {
                    if ("\n- " === \substr($prev, -3)) {
                        $blocks[$block] .= $current;
                        continue;
                    }
                    $blocks[$block] .= "\n" . $current;
                    continue;
                }
            }
            $blocks[++$block] = $current;
        }
        $out = [];
        foreach ($blocks as $block) {
            if (false !== \strpos('\'"', $block[0]) && \preg_match('/^(' . $str . '):\s+/', $block, $m)) {
                $out[from($m[1])] = from(\substr($block, \strlen($m[0])), $array, $lot);
                continue;
            }
            $any = \preg_split('/:\s+/', $block, 2);
            $out[$any[0]] = isset($any[1]) ? from($any[1], $array, $lot) : null;
        }
        return $out;
    }
}

namespace x\y_a_m_l\from {}