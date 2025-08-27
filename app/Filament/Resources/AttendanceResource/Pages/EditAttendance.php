<?php
// File: app/Filament/Resources/AttendanceResource/Pages/EditAttendance.php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAttendance extends EditRecord
{
    protected static string $resource = AttendanceResource::class;

    // Halaman ini biasanya tidak digunakan karena data absensi bersifat read-only.
    // Namun, jika diperlukan, Anda bisa menambahkan aksi di sini.
    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
