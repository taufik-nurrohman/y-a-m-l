<?php

if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    exit;
}

error_reporting(E_ALL | E_STRICT);

ini_set('display_errors', true);
ini_set('display_startup_errors', true);
ini_set('html_errors', 1);

define('D', DIRECTORY_SEPARATOR);
define('P', "\u{001A}");
define('PATH', __DIR__);

date_default_timezone_set('Asia/Jakarta');

require __DIR__ . D . '..' . D . 'from.php';

$test = basename($_GET['test'] ?? 'scalar');
$view = $_GET['view'] ?? 'php';

$files = glob(__DIR__ . D . 'from' . D . $test . D . '*.yaml', GLOB_NOSORT);
usort($files, static function ($a, $b) {
    $a = dirname($a) . D . basename($a, '.yaml');
    $b = dirname($b) . D . basename($b, '.yaml');
    return strnatcmp($a, $b);
});

$out = '<!DOCTYPE html>';
$out .= '<html dir="ltr">';
$out .= '<head>';
$out .= '<meta charset="utf-8">';
$out .= '<title>';
$out .= 'YAML to Data';
$out .= '</title>';
$out .= '</head>';
$out .= '<body>';

$out .= '<form method="get">';

$out .= '<fieldset>';
$out .= '<legend>';
$out .= 'Navigation';
$out .= '</legend>';
$out .= '<a href="to.php">Data to YAML</a>';
$out .= '</fieldset>';

$out .= '<fieldset>';
$out .= '<legend>';
$out .= 'Filter';
$out .= '</legend>';
$out .= '<button' . ('*' === $test ? ' disabled' : "") . ' name="test" type="submit" value="*">';
$out .= '*';
$out .= '</button>';
foreach (glob(__DIR__ . D . 'from' . D . '*', GLOB_ONLYDIR) as $v) {
    $out .= ' ';
    $out .= '<button' . ($test === ($n = basename($v)) ? ' disabled' : "") . ' name="test" type="submit" value="' . htmlspecialchars($n) . '">';
    $out .= htmlspecialchars($n);
    $out .= '</button>';
}
$out .= '</fieldset>';

$out .= '<fieldset>';
$out .= '<legend>';
$out .= 'Preview';
$out .= '</legend>';
$out .= '<select name="view">';
$out .= '<option' . ('json' === $view ? ' selected' : "") . ' value="json">JSON</option>';
$out .= '<option' . ('php' === $view ? ' selected' : "") . ' value="php">PHP</option>';
$out .= '</select>';
$out .= ' ';
$out .= '<button name="test" type="submit" value="' . $test . '">';
$out .= 'Update';
$out .= '</button>';
$out .= '</fieldset>';

$out .= '</form>';

foreach ($files as $v) {
    $raw = file_get_contents($v);
    $out .= '<h1 id="' . ($n = basename(dirname($v)) . ':' . basename($v, '.yaml')) . '"><a aria-hidden="true" href="#' . $n . '">&sect;</a> ' . strtr($v, [PATH . D => '.' . D]) . '</h1>';
    $out .= '<div style="display:flex;gap:1em;margin:1em 0 0;">';
    $out .= '<pre style="background:#ccc;border:1px solid rgba(0,0,0,.25);color:#000;flex:1;font:normal normal 100%/1.25 monospace;margin:0;padding:.5em;tab-size:4;white-space:pre-wrap;word-wrap:break-word;">';
    $out .= htmlspecialchars($raw);
    $out .= '</pre>';
    $out .= '<pre style="background:#cfc;border:1px solid rgba(0,0,0,.25);color:#000;flex:1;font:normal normal 100%/1.25 monospace;margin:0;padding:.5em;tab-size:4;white-space:pre-wrap;word-wrap:break-word;">';
    $test_data = [
        // Custom tag
        '!php/const' => static function ($value) {
            if (is_string($value) && defined($value)) {
                return constant($value);
            }
            return null;
        }
    ];
    $start = microtime(true);
    $data = x\y_a_m_l\from($raw, false, $test_data);
    $end = microtime(true);
    $out .= htmlspecialchars('php' === $view ? preg_replace(['/=>\s*\n\s*/', '/\barray\s+\(/'], ['=> ', 'array('], var_export($data, true)) : strtr(json_encode($data, JSON_PRESERVE_ZERO_FRACTION | JSON_PRETTY_PRINT), ['    ' => '  ']));
    $out .= '</pre>';
    $out .= '</div>';
    $time = round(($end - $start) * 1000, 2);
    $out .= '<p style="color:#' . ($time >= 1 ? '800' : '080') . ';">Parsed in ' . $time . ' ms.</p>';
}

$out .= '</body>';
$out .= '</html>';

echo $out;