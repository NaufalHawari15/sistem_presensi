<?php

namespace App\Filament\Resources\LeaveResource\Pages;

use App\Filament\Resources\LeaveResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLeave extends CreateRecord
{
    protected static string $resource = LeaveResource::class;

    // Mengatur nilai default secara otomatis saat data dibuat
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = 'Pending'; // Setiap pengajuan baru otomatis berstatus Pending
        return $data;
    }
}
