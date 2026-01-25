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

require __DIR__ . D . '..' . D . 'from.php';

$test = basename($_GET['test'] ?? 'scalar');
$view = $_GET['view'] ?? 'php';

$files = glob(__DIR__ . D . 'from' . D . $test . D . '*.yaml', GLOB_NOSORT);

usort($files, static function ($a, $b) {
    $a = dirname($a) . D . basename($a, '.yaml');
    $b = dirname($b) . D . basename($b, '.yaml');
    return strnatcmp($a, $b);
});

// <https://github.com/mecha-cms/mecha/blob/v3.2.0/engine/f.php#L20-L35>
if (!function_exists('array_is_list')) {
    // PHP < 8.1
    function array_is_list(array $array): bool {
        if (!$array) {
            return true;
        }
        $key = -1;
        foreach ($array as $k => $v) {
            if ($k !== ++$key) {
                return false;
            }
        }
        return true;
    }
}

// <https://github.com/mecha-cms/mecha/blob/v3.2.0/engine/f.php#L1606-L1671>
function php_export($value, $d = "", $key_as_string = false, $is_object = null) {
    if (is_object($value)) {
        if ($value instanceof stdClass) {
            return '(object) ' . php_export((array) $value, $d, true, true);
        }
        return strtr(var_export($value, true), [
            "\n " . $d => "\n" . $d,
            ",\n" . $d . ')' => "\n" . $d . ')'
        ]);
    }
    if (is_array($value)) {
        $r = [];
        if (!$is_object && array_is_list($value)) {
            foreach ($value as $k => $v) {
                $r[] = php_export($v, $d . '  ', $key_as_string);
            }
        } else {
            foreach ($value as $k => $v) {
                $k = php_export($k);
                if ($key_as_string && is_numeric($k)) {
                    $k = "'" . $k . "'";
                }
                $r[] = $k . ' => ' . php_export($v, $d . '  ', $key_as_string);
            }
        }
        if (!$r) {
            return 'array()';
        }
        return "array(\n  " . $d . implode(",\n" . $d . '  ', $r) . "\n" . $d . ')';
    }
    $value = var_export($value, true);
    if ("''" === $value) {
        return '""';
    }
    if ('NULL' === $value) {
        return 'null';
    }
    return $value;
}

$out = '<!DOCTYPE html>';
$out .= '<html dir="ltr">';
$out .= '<head>';
$out .= '<meta charset="utf-8">';
$out .= '<title>';
$out .= 'YAML to Data';
$out .= '</title>';
$out .= '<style>';
if (!empty($_GET['c'])) {
    $out .= <<<'CSS'
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
$out .= '</style>';
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
$out .= '<label>';
$out .= '<input' . (empty($_GET['c']) ? "" : ' checked') . ' name="c" type="checkbox" value="1">';
$out .= ' ';
$out .= 'Show control characters';
$out .= '</label>';
$out .= '<br>';
$out .= '<br>';
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

$error_count = 0;
foreach ($files as $v) {
    $error = false;
    $raw = file_get_contents($v);
    $out .= '<h1 id="' . ($n = basename(dirname($v)) . ':' . basename($v, '.yaml')) . '"><a aria-hidden="true" href="#' . $n . '">&sect;</a> ' . strtr($v, [PATH . D => '.' . D]) . '</h1>';
    $out .= '<div style="display:flex;gap:1em;margin:1em 0 0;">';
    $out .= '<pre style="background:#ccc;border:1px solid rgba(0,0,0,.25);color:#000;flex:1;font:normal normal 100%/1.25 monospace;margin:0;min-width:0;padding:.5em;tab-size:4;white-space:pre-wrap;word-wrap:break-word;">';
    $out .= strtr(htmlspecialchars($raw), [
        "\n" => '<span class="c-n">' . "\n" . '</span>',
        "\t" => '<span class="c-t">' . "\t" . '</span>',
        ' ' => '<span class="c-s"> </span>'
    ]);
    $out .= '<span class="c-e">' . "\n" . '</span></pre>';
    if ('json' === $view) {
        $out .= '<pre style="background:#cfc;border:1px solid rgba(0,0,0,.25);color:#000;flex:1;font:normal normal 100%/1.25 monospace;margin:0;min-width:0;padding:.5em;tab-size:4;white-space:pre-wrap;word-wrap:break-word;">';
        $start = microtime(true);
        $content = x\y_a_m_l\from($raw);
        $end = microtime(true);
        $content = strtr(json_encode($content, JSON_PRETTY_PRINT), ['    ' => '  ']);
        $out .= strtr(htmlspecialchars($content), [
            "\n" => '<span class="c-n">' . "\n" . '</span>',
            "\t" => '<span class="c-t">' . "\t" . '</span>',
            ' ' => '<span class="c-s"> </span>'
        ]);
        $out .= '<span class="c-e">' . "\n" . '</span></pre>';
    } else if ('php' === $view) {
        $out .= '<div style="flex:1;min-width:0;">';
        $a = $b = "";
        $a .= '<pre style="background:#cfc;border:1px solid rgba(0,0,0,.25);color:#000;font:normal normal 100%/1.25 monospace;margin:0;padding:.5em;tab-size:4;white-space:pre-wrap;word-wrap:break-word;">';
        $start = microtime(true);
        $lot = [
            '!php/const' => function ($v) {
                return is_string($v) && defined($v) ? constant($v) : null;
            }
        ];
        $content = x\y_a_m_l\from($raw, false, $lot);
        $end = microtime(true);
        $content = '<?' . "php\n\nreturn " . php_export($content) . ';';
        $a .= strtr(htmlspecialchars($content), [
            "\n" => '<span class="c-n">' . "\n" . '</span>',
            "\t" => '<span class="c-t">' . "\t" . '</span>',
            ' ' => '<span class="c-s"> </span>'
        ]);
        $a .= '<span class="c-e">' . "\n" . '</span></pre>';
        if (is_file($f = dirname($v) . D . pathinfo($v, PATHINFO_FILENAME) . '.php')) {
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
        $out .= ($error ? strtr($a, [':#cfc;' => ':#fcc;']) : $a) . $b;
        // $out .= '<pre><code>' . json_encode($lot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</code></pre>';
        $out .= '</div>';
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