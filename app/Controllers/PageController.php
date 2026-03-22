<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\BlogPost;
use App\Models\Destination;
use App\Models\Flight;
use App\Models\Hotel;
use App\Models\InsurancePackage;
use App\Models\Page;
use App\Models\Setting;
use App\Models\Visa;

final class PageController extends BaseController
{
    public function about(): void
    {
        $page = Page::findByKey('about');
        if ($page === null) {
            $s = Setting::allKeyed();
            $page = [
                'title' => 'About',
                'content' => (string) ($s['about_text'] ?? ''),
                'meta_title' => null,
                'meta_description' => null,
            ];
        }

        view('pages.about', [
            'title' => (string) ($page['meta_title'] ?? $page['title'] ?? 'About'),
            'metaDescription' => (string) ($page['meta_description'] ?? ''),
            'page' => $page,
        ]);
    }

    public function contact(): void
    {
        $page = Page::findByKey('contact');
        if ($page === null) {
            $page = [
                'title' => 'Contact',
                'content' => '',
                'meta_title' => null,
                'meta_description' => null,
            ];
        }

        view('pages.contact', [
            'title' => (string) ($page['meta_title'] ?? $page['title'] ?? 'Contact'),
            'metaDescription' => (string) ($page['meta_description'] ?? ''),
            'page' => $page,
        ]);
    }

    public function visas(): void
    {
        $page = Page::findByKey('visas');
        view('pages.visas', [
            'title' => $page !== null ? (string) ($page['meta_title'] ?? $page['title'] ?? 'Visa Services') : 'Visa Services',
            'metaDescription' => $page !== null ? (string) ($page['meta_description'] ?? '') : '',
            'page' => $page,
            'visas' => Visa::active(),
            'destinations' => Destination::allActive(),
        ]);
    }

    public function flights(): void
    {
        $page = Page::findByKey('flights');
        view('pages.flights', [
            'title' => $page !== null ? (string) ($page['meta_title'] ?? $page['title'] ?? 'Flight Tickets') : 'Flight Tickets',
            'metaDescription' => $page !== null ? (string) ($page['meta_description'] ?? '') : '',
            'page' => $page,
            'deals' => Flight::deals(6),
            'destinations' => Destination::allActive(),
        ]);
    }

    public function hotels(): void
    {
        $page = Page::findByKey('hotels');
        view('pages.hotels', [
            'title' => $page !== null ? (string) ($page['meta_title'] ?? $page['title'] ?? 'Hotels') : 'Hotels',
            'metaDescription' => $page !== null ? (string) ($page['meta_description'] ?? '') : '',
            'page' => $page,
            'hotels' => Hotel::featured(9),
            'destinations' => Destination::allActive(),
        ]);
    }

    public function insurance(): void
    {
        $page = Page::findByKey('insurance');
        view('pages.insurance', [
            'title' => $page !== null ? (string) ($page['meta_title'] ?? $page['title'] ?? 'Insurance') : 'Insurance',
            'metaDescription' => $page !== null ? (string) ($page['meta_description'] ?? '') : '',
            'page' => $page,
            'packages' => InsurancePackage::active(),
        ]);
    }

    public function destinations(): void
    {
        $q = (string) ($_GET['q'] ?? '');
        $page = Page::findByKey('destinations');
        view('pages.destinations', [
            'title' => $page !== null ? (string) ($page['meta_title'] ?? $page['title'] ?? 'Destinations') : 'Destinations',
            'metaDescription' => $page !== null ? (string) ($page['meta_description'] ?? '') : '',
            'page' => $page,
            'q' => $q,
            'destinations' => Destination::search($q, 30),
        ]);
    }

    public function destinationShow(string $slug): void
    {
        $destination = Destination::findBySlug($slug);
        if ($destination === null) {
            http_response_code(404);
            view('errors.404', ['path' => '/destinations/' . $slug]);
            return;
        }

        view('pages.destination_show', [
            'title' => (string) ($destination['name'] ?? 'Destination'),
            'metaDescription' => (string) ($destination['description'] ?? ''),
            'destination' => $destination,
        ]);
    }

    public function blog(): void
    {
        $page = Page::findByKey('blog');
        view('pages.blog', [
            'title' => $page !== null ? (string) ($page['meta_title'] ?? $page['title'] ?? 'News & Blog') : 'News & Blog',
            'metaDescription' => $page !== null ? (string) ($page['meta_description'] ?? '') : '',
            'page' => $page,
            'posts' => BlogPost::latest(20),
        ]);
    }

    public function blogShow(string $slug): void
    {
        $post = BlogPost::findBySlug($slug);
        if ($post === null) {
            http_response_code(404);
            view('errors.404', ['path' => '/blog/' . $slug]);
            return;
        }

        view('pages.blog_show', [
            'title' => (string) ($post['title'] ?? 'Article'),
            'metaDescription' => (string) ($post['excerpt'] ?? ''),
            'post' => $post,
        ]);
    }

    /** Redirect legacy /visa.html bookmark to the dynamic admin visa console. */
    public function visaHtmlRedirect(): void
    {
        $this->redirect('/admin/visa');
    }

    /** Redirect /settings.html to the admin site settings (CMS has no public settings page). */
    public function settingsHtmlRedirect(): void
    {
        $this->redirect('/admin/settings');
    }
}
