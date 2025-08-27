<?php

namespace App\Filament\Resources\LeaveResource\Pages;

use App\Filament\Resources\LeaveResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLeave extends EditRecord
{
    protected static string $resource = LeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(), // Tambahkan tombol View di halaman edit
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Memastikan data relasi (user) dimuat dengan benar sebelum form ditampilkan.
     * Ini untuk mengatasi masalah data yang tidak muncul di form edit.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->load('user');
 
        return $data;
    }
}
