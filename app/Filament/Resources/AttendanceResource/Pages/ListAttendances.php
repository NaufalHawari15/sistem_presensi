<?php
// File: app/Filament/Resources/AttendanceResource/Pages/ListAttendances.php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tombol ini tidak lagi relevan karena absensi dibuat dari aplikasi mobile
            // Actions\CreateAction::make(),
        ];
    }
}
