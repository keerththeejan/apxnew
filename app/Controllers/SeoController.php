<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Destination;
use App\Models\BlogPost;

final class SeoController extends BaseController
{
    public function sitemap(): void
    {
        header('Content-Type: application/xml; charset=utf-8');

        $base = rtrim(env('APP_BASE_URL', ''), '/');
        if ($base === '') {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $base = $scheme . '://' . $host;
        }

        $urls = [
            '/', '/about', '/contact', '/visas', '/flights', '/hotels', '/insurance', '/destinations', '/blog'
        ];

        $dest = Destination::search('', 500);
        foreach ($dest as $d) {
            $urls[] = '/destinations/' . $d['slug'];
        }

        $posts = BlogPost::latest(500);
        foreach ($posts as $p) {
            $urls[] = '/blog/' . $p['slug'];
        }

        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        foreach ($urls as $u) {
            $loc = htmlspecialchars($base . $u, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            echo "  <url><loc>{$loc}</loc></url>\n";
        }
        echo "</urlset>\n";
    }
}
