<?php
// File: app/Filament/Resources/AttendanceResource/Pages/CreateAttendance.php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAttendance extends CreateRecord
{
    protected static string $resource = AttendanceResource::class;
    
    // Resource absensi tidak dapat dibuat melalui panel admin.
    // Metode ini akan menolak setiap upaya pembuatan record.
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
