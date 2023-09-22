<?php

namespace x\y_a_m_l {
    function from(?string $value, $array = false) {
        if ("" === ($value = \trim($value ?? ""))) {
            return null;
        }
        // Normalize line break(s)
        $value = \strtr($value, [
            "\r\n" => "\n",
            "\r" => "\n"
        ]);
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
        if (\is_numeric($value)) {
            if (\strlen($value) > 1 && '0' === $value[0] && false === \strpos($value, '.')) {
                return $value; // TODO
            }
            return false !== \strpos($value, '.') ? (float) $value : (int) $value;
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
                if (0 === \strpos($current, '- ')) {
                    $blocks[$block] .= "\n" . $current;
                    continue;
                }
                if ($dent > 0) {
                    $blocks[$block] .= "\n" . $current;
                    continue;
                }
            }
            $blocks[++$block] = $current;
        }
    }
}

namespace x\y_a_m_l\from {}