<?php

if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    exit;
}

error_reporting(E_ALL | E_STRICT);

ini_set('display_errors', true);
ini_set('display_startup_errors', true);
ini_set('html_errors', 1);

define('D', DIRECTORY_SEPARATOR);
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
$out .= '<style>';
$out .= <<<CSS
.char-end,
.char-enter,
.char-space,
.char-tab {
  opacity: 0.5;
  position: relative;
}
.char-end::before {
  bottom: 0;
  content: '␄';
  left: 0;
  position: absolute;
  right: 0;
  text-align: center;
  top: 0;
}
.char-enter::before {
  bottom: 0;
  content: '␤';
  left: 0;
  position: absolute;
  right: 0;
  text-align: center;
  top: 0;
}
.char-space::before {
  bottom: 0;
  content: '·';
  left: 0;
  position: absolute;
  right: 0;
  text-align: center;
  top: 0;
}
.char-tab::before {
  bottom: 0;
  content: '→';
  left: 0;
  position: absolute;
  right: 0;
  text-align: center;
  top: 0;
}
CSS;
$out .= '</style>';
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
$out .= '</fieldset>';

$out .= '</form>';

$error_count = 0;
foreach ($files as $v) {
    $error = false;
    $raw = file_get_contents($v);
    $out .= '<h1 id="' . ($n = basename(dirname($v)) . ':' . basename($v, '.php')) . '"><a aria-hidden="true" href="#' . $n . '">&sect;</a> ' . strtr($v, [PATH . D => '.' . D]) . '</h1>';
    $out .= '<div style="display:flex;gap:1em;margin:1em 0 0;">';
    $out .= '<pre style="background:#ccc;border:1px solid rgba(0,0,0,.25);color:#000;flex:1;font:normal normal 100%/1.25 monospace;margin:0;min-width:0;padding:.5em;tab-size:4;white-space:pre-wrap;word-wrap:break-word;">';
    $out .= strtr(htmlspecialchars($raw), [
        "\n" => '<span class="char-enter">' . "\n" . '</span>',
        "\t" => '<span class="char-tab">' . "\t" . '</span>',
        ' ' => '<span class="char-space"> </span>'
    ]);
    $out .= '<span class="char-end">' . "\n" . '</span></pre>';
    if (true) {
        $out .= '<div style="flex:1;min-width:0;">';
        $a = $b = "";
        $a .= '<pre style="background:#cfc;border:1px solid rgba(0,0,0,.25);color:#000;font:normal normal 100%/1.25 monospace;margin:0;padding:.5em;tab-size:4;white-space:pre-wrap;word-wrap:break-word;">';
        $start = microtime(true);
        $content = x\y_a_m_l\to(require $v, 2);
        $end = microtime(true);
        $a .= strtr(htmlspecialchars($content), [
            "\n" => '<span class="char-enter">' . "\n" . '</span>',
            "\t" => '<span class="char-tab">' . "\t" . '</span>',
            ' ' => '<span class="char-space"> </span>'
        ]);
        $a .= '<span class="char-end">' . "\n" . '</span></pre>';
        if (is_file($f = dirname($v) . D . pathinfo($v, PATHINFO_FILENAME) . '.yaml')) {
            $test = strtr(file_get_contents($f), [
                "\r\n" => "\n",
                "\r" => "\n"
            ]);
            if ($error = $content !== $test) {
                $b .= '<pre style="background:#cff;border:1px solid rgba(0,0,0,.25);color:#000;font:normal normal 100%/1.25 monospace;margin:1em 0 0;padding:.5em;tab-size:4;white-space:pre-wrap;word-wrap:break-word;">';
                $b .= strtr(htmlspecialchars($test), [
                    "\n" => '<span class="char-enter">' . "\n" . '</span>',
                    "\t" => '<span class="char-tab">' . "\t" . '</span>',
                    ' ' => '<span class="char-space"> </span>'
                ]);
                $b .= '<span class="char-end">' . "\n" . '</span></pre>';
            }
        } else {
            // file_put_contents($f, $content);
            $error = false; // No test file to compare
        }
        $out .= ($error ? strtr($a, [':#cfc;' => ':#fcc;']) : $a) . $b . '</div>';
    }
    $out .= '</div>';
    $time = round(($end - $start) * 1000, 2);
    if ($error) {
        $error_count += 1;
    }
    $slow = $time >= 1;
    $out .= '<p style="color:#' . ($slow ? '800' : '080') . ';">Parsed in ' . $time . ' ms.</p>';
}

$out .= '</body>';
$out .= '</html>';

if ($error_count) {
    $out = strtr($out, ['</title>' => ' (' . $error_count . ')</title>']);
}

echo $out;