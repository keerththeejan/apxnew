<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FooterGallery;
use App\Models\FooterLink;
use App\Models\HeroSection;
use App\Models\NavItem;

final class PublicLayout
{
    /**
     * Shared variables for all public pages (navbar, footer, settings).
     *
     * @return array<string, mixed>
     */
    public static function shared(): array
    {
        $settings = SiteConfig::all();
        $tz = trim(SiteConfig::get('app_timezone', (string) env('APP_TIMEZONE', 'UTC')));
        if ($tz !== '') {
            try {
                date_default_timezone_set($tz);
            } catch (\Throwable $e) {
            }
        }

        return [
            'settings' => $settings,
            'socialLinks' => SiteConfig::socialLinks(),
            'defaultLocale' => SiteConfig::get('default_locale', 'en'),
            'defaultTheme' => SiteConfig::get('default_theme', 'light'),
            'themeCssVars' => SiteConfig::themeCssVars(),
            'navMenu' => NavItem::publicMenu(),
            'navCurrentKey' => NavItem::currentRequestNavKey(),
            'footerLinksByGroup' => FooterLink::groupedActive(),
            'footerGallery' => FooterGallery::activeOrdered(),
            'heroHome' => HeroSection::findByPageKey('home'),
        ];
    }
}
