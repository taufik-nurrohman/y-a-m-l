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
        $block = 0;
        $blocks = [];
        $rows = \explode("\n", $value);
        foreach ($rows as $row) {}
    }
}

namespace x\y_a_m_l\from {}