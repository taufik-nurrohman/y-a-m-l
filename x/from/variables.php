<?php

require __DIR__ . '/../../from.php';
require __DIR__ . '/../../to.php';

$variables = [
    // Pre-parse
    static function (?string $value, $state) {
        if (!$value || false === strpos($value, '{{')) {
            return $value;
        }
        $variables = (array) ($state['variables'] ?? []);
        return preg_replace_callback('/"\{\{\s*[a-z]\w*\s*\}\}"|\'\{\{\s*[a-z]\w*\s*\}\}\'|\{\{\s*[a-z]\w*\s*\}\}/', static function ($m) use ($variables) {
            $variable = $m[0];
            // `"{{ var }}"`
            if ('"' === $variable[0] && '"' === substr($variable, -1)) {
                $variable = substr($variable, 1, -1);
            }
            // `'{{ var }}'`
            if ("'" === $variable[0] && "'" === substr($variable, -1)) {
                $variable = substr($variable, 1, -1);
            }
            // Trim variable from `{{` and `}}`
            $variable = trim(substr($variable, 2, -2));
            // Get the variable value if available, default to `null`
            $variable = $variables[$variable] ?? null;
            // Return the variable value as YAML string
            return x\y_a_m_l\to($variable);
        }, $value);
    },
    // Post-parse
    null
];

echo '<pre>';
echo htmlspecialchars(var_export(x\y_a_m_l\from(file_get_contents(__DIR__ . '/variables.yaml'), [
    'variables' => [
        'var_1' => 'asdf',
        'var_2' => true,
        'var_3' => 1,
        'var_4' => 1.5
    ],
    'with' => [$variables]
]), true));
echo '</pre>';