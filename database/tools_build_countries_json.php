<?php

declare(strict_types=1);

$url = 'https://raw.githubusercontent.com/lukes/ISO-3166-Countries-with-Regional-Codes/master/all/all.json';
$raw = @file_get_contents($url);
if ($raw === false) {
    fwrite(STDERR, "Could not download country list from GitHub.\n");
    exit(1);
}
$j = json_decode($raw, true);

if (!is_array($j)) {
    fwrite(STDERR, "Invalid JSON.\n");
    exit(1);
}

$out = [];
foreach ($j as $r) {
    if (!is_array($r)) {
        continue;
    }
    $code = (string) ($r['alpha-2'] ?? '');
    $name = (string) ($r['name'] ?? '');
    if ($code !== '' && $name !== '') {
        $out[] = ['code' => $code, 'name' => $name];
    }
}

$dir = dirname(__DIR__) . '/public/data';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$dest = $dir . '/countries.json';
file_put_contents($dest, json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
echo 'Wrote ' . count($out) . ' countries to ' . $dest . PHP_EOL;
