<?php

if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    exit;
}

error_reporting(E_ALL);

ini_set('display_errors', true);
ini_set('display_startup_errors', true);
ini_set('html_errors', 1);

date_default_timezone_set('Asia/Jakarta');

define('D', DIRECTORY_SEPARATOR);
define('PATH', __DIR__);

require __DIR__ . D . '..' . D . '..' . D . 'to.php';

$test = basename($_GET['test'] ?? 'scalar');

$files = glob(__DIR__ . D . 'to' . D . $test . D . '*.php', GLOB_NOSORT);

usort($files, static function ($a, $b) {
    $a = dirname($a) . D . basename($a, '.php');
    $b = dirname($b) . D . basename($b, '.php');
    return strnatcmp($a, $b);
});

$s = '<!DOCTYPE html>';
$s .= '<html dir="ltr">';
$s .= '<head>';
$s .= '<meta charset="utf-8">';
$s .= '<title>';
$s .= 'Data to YAML';
$s .= '</title>';
$s .= '<style>';
if (!empty($_GET['c'])) {
    $s .= <<<'CSS'
.c-e,
.c-n,
.c-s,
.c-t {
  opacity: 0.5;
  position: relative;
}
.c-e {
  opacity: 1;
  color: #f00;
}
.c-n {
  opacity: 1;
  color: #090;
}
.c-e::before {
  bottom: 0;
  content: '␄';
  left: 0;
  position: absolute;
  right: 0;
  text-align: center;
  top: 0;
}
.c-n::before {
  bottom: 0;
  content: '␤';
  left: 0;
  position: absolute;
  right: 0;
  text-align: center;
  top: 0;
}
.c-s::before {
  bottom: 0;
  content: '·';
  left: 0;
  position: absolute;
  right: 0;
  text-align: center;
  top: 0;
}
.c-t::before {
  bottom: 0;
  content: '→';
  left: 0;
  position: absolute;
  right: 0;
  text-align: center;
  top: 0;
}
CSS;
}
$s .= '</style>';
$s .= '</head>';
$s .= '<body>';

$s .= '<form method="get">';

$s .= '<fieldset>';
$s .= '<legend>';
$s .= 'Navigation';
$s .= '</legend>';
$s .= '<a href="from.php">YAML to Data</a>';
$s .= '</fieldset>';

$s .= '<fieldset>';
$s .= '<legend>';
$s .= 'Filter';
$s .= '</legend>';
$s .= '<button' . ('*' === $test ? ' disabled' : "") . ' name="test" type="submit" value="*">';
$s .= '*';
$s .= '</button>';
foreach (glob(__DIR__ . D . 'to' . D . '*', GLOB_ONLYDIR) as $v) {
    $s .= ' ';
    $s .= '<button' . ($test === ($n = basename($v)) ? ' disabled' : "") . ' name="test" type="submit" value="' . htmlspecialchars($n) . '">';
    $s .= htmlspecialchars($n);
    $s .= '</button>';
}
$s .= '</fieldset>';

$s .= '<fieldset>';
$s .= '<legend>';
$s .= 'Preview';
$s .= '</legend>';
$s .= '<label>';
$s .= '<input' . (empty($_GET['c']) ? "" : ' checked') . ' name="c" type="checkbox" value="1">';
$s .= ' ';
$s .= 'Show control characters';
$s .= '</label>';
$s .= '<br>';
$s .= '<br>';
$s .= '<button name="test" type="submit" value="' . $test . '">';
$s .= 'Update';
$s .= '</button>';
$s .= '</fieldset>';

$s .= '</form>';

$error_count = 0;
foreach ($files as $v) {
    $error = false;
    $raw = file_get_contents($v);
    $s .= '<h1 id="' . ($n = basename(dirname($v)) . ':' . basename($v, '.php')) . '"><a aria-hidden="true" href="#' . $n . '">&sect;</a> ' . strtr($v, [PATH . D => '.' . D]) . '</h1>';
    $s .= '<div style="display:flex;gap:1em;margin:1em 0 0;">';
    $s .= '<pre style="background:#ccc;border:1px solid rgba(0,0,0,.25);color:#000;flex:1;font:normal normal 100%/1.25 monospace;margin:0;min-width:0;padding:.5em;tab-size:4;white-space:pre-wrap;word-wrap:break-word;">';
    $s .= strtr(htmlspecialchars($raw), [
        "\n" => '<span class="c-n">' . "\n" . '</span>',
        "\t" => '<span class="c-t">' . "\t" . '</span>',
        ' ' => '<span class="c-s"> </span>'
    ]);
    $s .= '<span class="c-e">' . "\n" . '</span></pre>';
    if (true) {
        $s .= '<div style="flex:1;min-width:0;">';
        $a = $b = "";
        $a .= '<pre style="background:#cfc;border:1px solid rgba(0,0,0,.25);color:#000;font:normal normal 100%/1.25 monospace;margin:0;padding:.5em;tab-size:4;white-space:pre-wrap;word-wrap:break-word;">';
        $start = hrtime(true);
        $content = x\y_a_m_l\to((function ($v) {
            try {
                $r = require $v;
            } catch (Throwable $e) {
                $r = (string) $e;
            }
            return $r;
        })($v), [
            'batch' => 'document' === basename(dirname($v)),
            'tab' => 2
        ]);
        $end = hrtime(true);
        $a .= strtr(htmlspecialchars($content), [
            "\n" => '<span class="c-n">' . "\n" . '</span>',
            "\t" => '<span class="c-t">' . "\t" . '</span>',
            ' ' => '<span class="c-s"> </span>'
        ]);
        $a .= '<span class="c-e">' . "\n" . '</span></pre>';
        if (is_file($f = dirname($v) . D . pathinfo($v, PATHINFO_FILENAME) . '.yaml')) {
            $test = strtr(file_get_contents($f), [
                "\r\n" => "\n",
                "\r" => "\n"
            ]);
            if ($error = $content !== $test) {
                $b .= '<pre style="background:#cff;border:1px solid rgba(0,0,0,.25);color:#000;font:normal normal 100%/1.25 monospace;margin:1em 0 0;padding:.5em;tab-size:4;white-space:pre-wrap;word-wrap:break-word;">';
                $b .= strtr(htmlspecialchars($test), [
                    "\n" => '<span class="c-n">' . "\n" . '</span>',
                    "\t" => '<span class="c-t">' . "\t" . '</span>',
                    ' ' => '<span class="c-s"> </span>'
                ]);
                $b .= '<span class="c-e">' . "\n" . '</span></pre>';
            }
        } else {
            // file_put_contents($f, $content);
            $error = false; // No test file to compare
        }
        $s .= ($error ? strtr($a, [':#cfc;' => ':#fcc;']) : $a) . $b . '</div>';
    }
    $s .= '</div>';
    $time = round(($end - $start) / 1e6, 2);
    if ($error) {
        $error_count += 1;
    }
    $slow = $time >= 1;
    $s .= '<p style="color:#' . ($slow ? '800' : '080') . ';">Parsed in ' . $time . ' ms.</p>';
}

$s .= '</body>';
$s .= '</html>';

if ($error_count) {
    $s = strtr($s, ['</title>' => ' (' . $error_count . ')</title>']);
}

echo $s;