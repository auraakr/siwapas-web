<?php

namespace App\Providers\Filament;

use App\Http\Middleware\AuthenticateAdminPanel;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\SpatieLaravelTranslatablePlugin;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('SIWAPAS')
            ->userMenuItems([
                MenuItem::make()
                    ->label('Lihat situs')
                    ->url('/')
                    ->icon('heroicon-o-globe-alt'),
            ])
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->navigationGroups(['Konten', 'Layanan', 'Acara', 'Transaksi', 'Situs Publik'])
            ->navigationItems([
                NavigationItem::make('Beranda')
                    ->group('Situs Publik')
                    ->icon('heroicon-o-home')
                    ->url(fn (): string => route('home'), shouldOpenInNewTab: true)
                    ->sort(1),
                NavigationItem::make('Halaman Fasilitas')
                    ->group('Situs Publik')
                    ->icon('heroicon-o-building-office')
                    ->url(fn (): string => route('facilities.index'), shouldOpenInNewTab: true)
                    ->sort(2),
                NavigationItem::make('Halaman Acara')
                    ->group('Situs Publik')
                    ->icon('heroicon-o-calendar-days')
                    ->url(fn (): string => route('events.index'), shouldOpenInNewTab: true)
                    ->sort(3),
            ])
            ->plugin(SpatieLaravelTranslatablePlugin::make()->defaultLocales(['id', 'en']))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                AuthenticateAdminPanel::class,
            ]);
    }
}
