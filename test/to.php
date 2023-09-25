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

require __DIR__ . D . '..' . D . 'to.php';

$test = basename($_GET['test'] ?? 'scalar');

$files = glob(__DIR__ . D . 'to' . D . $test . D . '*.php', GLOB_NOSORT);
usort($files, static function ($a, $b) {
    $a = dirname($a) . D . basename($a, '.php');
    $b = dirname($b) . D . basename($b, '.php');
    return strnatcmp($a, $b);
});

$out = '<!DOCTYPE html>';
$out .= '<html dir="ltr">';
$out .= '<head>';
$out .= '<meta charset="utf-8">';
$out .= '<title>';
$out .= 'Data to YAML';
$out .= '</title>';
$out .= '</head>';
$out .= '<body>';

$out .= '<form method="get">';

$out .= '<fieldset>';
$out .= '<legend>';
$out .= 'Navigation';
$out .= '</legend>';
$out .= '<a href="from.php">YAML to Data</a>';
$out .= '</fieldset>';

$out .= '<fieldset>';
$out .= '<legend>';
$out .= 'Filter';
$out .= '</legend>';
$out .= '<button' . ('*' === $test ? ' disabled' : "") . ' name="test" type="submit" value="*">';
$out .= '*';
$out .= '</button>';
foreach (glob(__DIR__ . D . 'to' . D . '*', GLOB_ONLYDIR) as $v) {
    $out .= ' ';
    $out .= '<button' . ($test === ($n = basename($v)) ? ' disabled' : "") . ' name="test" type="submit" value="' . htmlspecialchars($n) . '">';
    $out .= htmlspecialchars($n);
    $out .= '</button>';
}
$out .= ' ';
$out .= '<button' . ('TEST' === $test ? ' disabled' : "") . ' name="test" type="submit" value="TEST">';
$out .= 'TEST';
$out .= '</button>';
$out .= '</fieldset>';

$out .= '</form>';

foreach ($files as $v) {
    $content = "";
    $data = require $v;
    $out .= '<h1 id="' . ($n = basename(dirname($v)) . ':' . basename($v, '.php')) . '"><a aria-hidden="true" href="#' . $n . '">&sect;</a> ' . strtr($v, [PATH . D => '.' . D]) . '</h1>';
    $out .= '<div style="display:flex;gap:1em;margin:1em 0 0;">';
    $out .= '<pre style="background:#ccc;border:1px solid rgba(0,0,0,.25);color:#000;flex:1;font:normal normal 100%/1.25 monospace;margin:0;padding:.5em;tab-size:4;white-space:pre-wrap;word-wrap:break-word;">';
    $out .= htmlspecialchars(var_export($data, true));
    $out .= '</pre>';
    $start = microtime(true);
    $out .= '<pre style="background:#cfc;border:1px solid rgba(0,0,0,.25);color:#000;flex:1;font:normal normal 100%/1.25 monospace;margin:0;padding:.5em;tab-size:4;white-space:pre-wrap;word-wrap:break-word;">';
    $out .= htmlspecialchars(x\y_a_m_l\to($data, 2));
    $out .= '</pre>';
    $end = microtime(true);
    $out .= '</div>';
    $time = round(($end - $start) * 1000, 2);
    $out .= '<p style="color:#' . ($time >= 1 ? '800' : '080') . ';">Parsed in ' . $time . ' ms.</p>';
}

$out .= '</body>';
$out .= '</html>';

echo $out;