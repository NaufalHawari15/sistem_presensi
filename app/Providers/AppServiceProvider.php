<?php
// File: app/Providers/AppServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament; // <-- Tambahkan ini
use Filament\Navigation\NavigationItem; // <-- Tambahkan ini
use App\Models\User; // <-- Tambahkan ini
use App\Filament\Resources\UserResource; // <-- Tambahkan ini

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Filament::serving(function () {
            // Hitung jumlah user yang belum aktif
            $inactiveUsersCount = User::where('is_active', false)->count();

            // Hanya tampilkan notifikasi jika ada user yang belum aktif
            if ($inactiveUsersCount > 0) {
                Filament::registerNavigationItems([
                    NavigationItem::make('Users Menunggu Aktivasi')
                        // Arahkan ke halaman UserResource saat diklik
                        ->url(UserResource::getUrl('index'))
                        ->icon('heroicon-o-bell-alert')
                        // Tampilkan jumlah user sebagai badge
                        ->badge($inactiveUsersCount, 'warning')
                        // Urutkan agar muncul di posisi paling kanan
                        ->sort(99),
                ]);
            }
        });
    }
}