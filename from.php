<?php

namespace x\y_a_m_l {
    function from(?string $value, $array = false) {
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
        // List-style value
        if (0 === \strpos($value, '- ')) {
            $out = [];
            foreach (\explode("\n- ", \substr($value, 2) as $v) {
                $out[] = from($v, $array);
            }
            return $out;
        }
        // Fold-style or literal-style value
        if (false !== \strpos('>|', $value[0])) {
            [$a, $b] = \explode("\n", $value, 2);
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
                $out .= false !== \strpos(',:[]{}', $v) ? $v : \json_encode($v, false, 1);
            }
            return \json_decode($out) ?? $value;
        }
        if ("'" === $value[0] && "'" === \substr($value, -1)) {
            return \substr($value, 1, -1);
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
        $block = 0;
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
                //if ('-' === \trim(\strstr($current, '#', true))) {
                //    $blocks[$block] .= "\n- ";
                //    continue;
                //}
                //if (0 === \strpos($current, '- ')) {
                //    $blocks[$block] .= "\n" . $current;
                //    continue;
                //}
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
    }
}

namespace x\y_a_m_l\from {}