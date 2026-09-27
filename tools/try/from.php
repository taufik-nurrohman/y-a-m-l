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

require __DIR__ . D . '..' . D . '..' . D . 'from.php';

if ('POST' === $_SERVER['REQUEST_METHOD']) {
    if (($token = $_POST['token'] ?? 0) !== ($_SESSION['token'] ?? 1)) {
        $_SESSION['alert'] = 'Invalid token.';
        header('location: from.php');
        exit;
    }
    if (strlen($content = $_POST['content'] ?? "") > 102400) {
        $_SESSION['alert'] = 'For security reasons, the maximum content size has been limited to 100 KiB.';
        header('location: from.php');
        exit;
    }
    $_SESSION['r'][0] = $content;
    $t = hrtime(true);
    $content = x\y_a_m_l\from($content);
    $t = (hrtime(true) - $t) / 1e6;
    $_SESSION['r'][1] = json_encode($content, JSON_PRETTY_PRINT);
    $_SESSION['t'][0] = 0;
    $_SESSION['t'][1] = $t;
    header('location: from.php');
    exit;
}


$s = '<!DOCTYPE html>';
$s .= '<html dir="ltr">';
$s .= '<head>';
$s .= '<meta charset="utf-8">';
$s .= '<title>';
$s .= 'YAML to JSON';
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

$s .= '<textarea name="content" placeholder="asdf: [1, 2, 3, 4]" style="box-sizing:border-box;display:block;flex:1;min-height:12em;resize:vertical;width:100%;">' . htmlspecialchars($_SESSION['r'][0] ?? 'asdf: [1, 2, 3, 4]') . '</textarea>';

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
$s .= '</p>';

$s .= '<input name="token" type="hidden" value="' . ($_SESSION['token'] = bin2hex(random_bytes(16))) . '">';

$s .= '</form>';

$s .= '</body>';
$s .= '</html>';

unset($_SESSION['alert'], $_SESSION['r'], $_SESSION['t']);

echo $s;