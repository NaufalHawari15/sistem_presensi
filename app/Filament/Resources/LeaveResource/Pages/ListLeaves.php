<?php

namespace App\Filament\Resources\LeaveResource\Pages;

use App\Filament\Resources\LeaveResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab; // <-- perbaikan namespace

class ListLeaves extends ListRecords
{
    protected static string $resource = LeaveResource::class;

    protected function getHeaderActions(): array
    {
        return []; // Hilangkan tombol create
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua'),
            'izin' => Tab::make('Izin')
                ->modifyQueryUsing(fn ($query) => $query->where('leave_type', 'Izin')),
            'sakit' => Tab::make('Sakit')
                ->modifyQueryUsing(fn ($query) => $query->where('leave_type', 'Sakit')),
            'cuti' => Tab::make('Cuti / Lainnya')
                ->modifyQueryUsing(fn ($query) => $query->where('leave_type', 'Lainnya')),
        ];
    }
}
