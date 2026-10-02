<?php session_start();

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

if ('POST' === $_SERVER['REQUEST_METHOD']) {
    $_SESSION['batch'] = $batch = !empty($_POST['batch']);
    if (($token = $_POST['token'] ?? 0) !== ($_SESSION['token'][filemtime(__FILE__)] ?? 1)) {
        $_SESSION['alert'] = 'Invalid token.';
        header('location: to.php');
        exit;
    }
    if (strlen($content = $_POST['content'] ?? "") > 102400) {
        $_SESSION['alert'] = 'For security reasons, the maximum content size has been limited to 100 KiB.';
        header('location: to.php');
        exit;
    }
    $_SESSION['r'][0] = $content;
    $t = hrtime(true);
    $content = x\y_a_m_l\to(json_decode($content, true), ['batch' => $batch]);
    $t = (hrtime(true) - $t) / 1e6;
    $_SESSION['r'][1] = $content;
    $_SESSION['t'][0] = 0;
    $_SESSION['t'][1] = $t;
    header('location: to.php');
    exit;
}

$s = '<!DOCTYPE html>';
$s .= '<html dir="ltr">';
$s .= '<head>';
$s .= '<meta charset="utf-8">';
$s .= '<title>';
$s .= 'JSON to YAML';
$s .= '</title>';
$s .= '</head>';
$s .= '<body>';

$s .= '<form method="post">';

if ($alert = $_SESSION['alert'] ?? "") {
    $s .= '<p role="alert" style="color:#f00;">';
    $s .= $alert;
    $s .= '</p>';
}

$s .= '<div role="group" style="display:flex;gap:1em;margin:1em 0 0;">';

$s .= '<textarea name="content" placeholder="{&quot;asdf&quot;: [1, 2, 3, 4]}" style="box-sizing:border-box;display:block;flex:1;min-height:12em;resize:vertical;width:100%;">' . htmlspecialchars($_SESSION['r'][0] ?? '{"asdf": [1, 2, 3, 4]}') . '</textarea>';

if ("" !== trim($_SESSION['r'][1] ?? "")) {
    $s .= '<pre style="flex:1;overflow:auto;">';
    $s .= htmlspecialchars($_SESSION['r'][1] ?? "");
    $s .= '</pre>';
}

$s .= '</div>';

if ("" !== trim($_SESSION['r'][1] ?? "")) {
    $slow = ($time = $_SESSION['t'][1] ?? 0) >= 1;
    $s .= '<p style="color:#' . ($slow ? '800' : '080') . ';">Parsed in ' . round($time, 2) . ' ms.</p>';
}

$s .= '<p>';
$s .= '<button type="submit">';
$s .= 'Test';
$s .= '</button>';
$s .= ' ';
$s .= '<label>';
$s .= '<input' . (empty($_SESSION['batch']) ? "" : ' checked') . ' name="batch" type="checkbox" value="1">';
$s .= ' ';
$s .= 'Multiple documents';
$s .= '</label>';
$s .= '</p>';

$s .= '<input name="token" type="hidden" value="' . ($_SESSION['token'][filemtime(__FILE__)] = bin2hex(random_bytes(16))) . '">';

$s .= '</form>';

$s .= '</body>';
$s .= '</html>';

unset($_SESSION['alert'], $_SESSION['batch'], $_SESSION['r'], $_SESSION['t']);

echo $s;