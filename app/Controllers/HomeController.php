<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\BlogPost;
use App\Models\CtaSection;
use App\Models\Destination;
use App\Models\Service;
use App\Models\ApplicationFormField;
use App\Models\Setting;
use App\Models\Testimonial;

final class HomeController extends BaseController
{
    public function index(): void
    {
        $settings = Setting::allKeyed();
        $featuredDestinations = Destination::featured(6);
        $testimonials = Testimonial::latest(6);
        $posts = BlogPost::latest(3);
        $services = Service::active();
        $ctaMid = CtaSection::findByKey('home_mid');
        $ctaNews = CtaSection::findByKey('home_news');
        $formFields = ApplicationFormField::activeOrDefault();

        view('pages.home', [
            'title' => ($settings['site_name'] ?? 'APX') . ' - Home',
            'metaDescription' => (string) ($settings['home_meta_description'] ?? $settings['home_hero_subtitle'] ?? ''),
            'featuredDestinations' => $featuredDestinations,
            'testimonials' => $testimonials,
            'posts' => $posts,
            'services' => $services,
            'ctaMid' => $ctaMid,
            'ctaNews' => $ctaNews,
            'formFields' => $formFields,
        ]);
    }
}
