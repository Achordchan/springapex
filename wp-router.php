<?php
// Router for PHP's built-in server so WordPress pretty permalinks, /wp-admin/
// directory requests, and theme assets all resolve correctly.
//
// Theme/plugin assets are streamed by this router (instead of being handed back
// to the built-in server) so we can send no-store headers: during development
// the browser must never keep a stale copy of a CSS/JS/image file.
$root = rtrim(str_replace('\\', '/', __DIR__), '/');
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$path = rawurldecode($path);

// Reject traversal instead of relying on realpath(); defense in depth.
$safe = $path !== '' && strpos($path, "\0") === false && !preg_match('#(^|/)\.\.(/|$)#', $path);

// The docroot doubles as the project workspace (git metadata, docs, customer
// files, QA artifacts), so only WordPress' own directories and root entry
// scripts may serve or execute files directly. Everything else falls through
// to the front controller and 404s; wp-config.php stays unreachable.
$servable = preg_match('#^/(wp-admin|wp-includes)(/|$)#', $path) === 1
    || preg_match('#^/wp-content/(themes/springapex|uploads|uploads-webpc|plugins)/#', $path) === 1
    // Converter for Media drops a pass-through loader at the wp-content root and
    // probes it over HTTP; the built-in server has no rewrite engine, so we let
    // this one file execute to satisfy the loader self-test during local dev.
    || $path === '/wp-content/webpc-passthru.php'
    || preg_match('#^/(wp-login|wp-signup|wp-activate|wp-cron|wp-mail|wp-trackback|wp-links-opml|wp-comments-post|xmlrpc)\.php$#', $path) === 1;

if ($safe && $servable) {
    $target = $root . $path;

    if (is_file($target)) {
        $ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));
        $types = [
            'css'  => 'text/css; charset=UTF-8',
            'js'   => 'application/javascript; charset=UTF-8',
            'mjs'  => 'application/javascript; charset=UTF-8',
            'json' => 'application/json; charset=UTF-8',
            'svg'  => 'image/svg+xml',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'avif' => 'image/avif',
            'ico'  => 'image/x-icon',
            'mp4'  => 'video/mp4',
            'webm' => 'video/webm',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf'  => 'font/ttf',
            'pdf'  => 'application/pdf',
        ];

        if (isset($types[$ext])) {
            header('Content-Type: ' . $types[$ext]);
            header('Content-Length: ' . filesize($target));
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            readfile($target);
            return true;
        }

        // .php and anything else: let the built-in server handle it.
        return false;
    }

    // Directory without a trailing slash: redirect, the way Apache/nginx would.
    // Without this, wp-admin's own menu links (which are relative, e.g.
    // "admin.php?page=…") resolve against / instead of /wp-admin/ and 404.
    if (substr($path, -1) !== '/' && is_dir($target) && is_file($target . '/index.php')) {
        $query = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        header('Location: ' . $path . '/' . ($query !== '' ? '?' . $query : ''), true, 301);
        return true;
    }

    // Directory: serve its index.php if present (e.g. /wp-admin/).
    if (is_dir($target) && is_file(rtrim($target, '/') . '/index.php')) {
        $script = rtrim($target, '/') . '/index.php';
        $_SERVER['SCRIPT_FILENAME'] = $script;
        $_SERVER['SCRIPT_NAME'] = rtrim($path, '/') . '/index.php';
        $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
        require $script;
        return true;
    }
}

// Everything else goes through the WordPress front controller.
$_SERVER['SCRIPT_FILENAME'] = $root . '/index.php';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';
require $root . '/index.php';
