<?php

require __DIR__ . '/../../to.php';

$header = static function (?string $value) {
    if ($value) {
        if (0 !== strpos($value, '---')) {
            $value = "---\n" . $value;
        }
        return "%YAML 1.2\n" . $value;
    }
    return $value;
};

echo '<pre>';
echo htmlspecialchars(x\y_a_m_l\to(json_decode(file_get_contents(__DIR__ . '/header.json'), true), ['with' => [$header]]));
echo '</pre>';